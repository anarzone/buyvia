# Lessons 1–2 — MySQL Storage and Indexes

Phase 1. Everything about locking, contention and query performance rests on this.

---

# Lesson 1 — How MySQL stores your data

## Disk is read in pages, not rows

MySQL never reads a single row from disk. It reads a **page** — 16 KB by default in
InnoDB. Even a 100-byte row costs a full 16 KB page read.

```
┌──────────────────── one 16 KB page ────────────────────┐
│  row 12 │ row 13 │ row 14 │ row 15 │ row 16 │  ...     │
└────────────────────────────────────────────────────────┘
```

Disk cost is dominated by *finding* data, not transferring it. So **rows near each other
are effectively free to read together** — which is why the physical ordering of data
matters enormously.

## The table IS the index (clustered index)

In InnoDB the table doesn't exist separately from its primary key index. Rows are stored
inside a B+ tree sorted by primary key.

```
                ┌─────────────────┐
                │ root: <500│>=500│        ← navigation only
                └────┬────────┬───┘
           ┌─────────┘        └─────────┐
     ┌─────▼─────┐               ┌──────▼────┐
     │<250│>=250 │               │<750│>=750 │        ← navigation only
     └──┬─────┬──┘               └──┬─────┬──┘
   ┌────▼──┐ ┌▼──────┐        ┌─────▼─┐ ┌─▼─────┐
   │rows   │ │rows   │        │rows   │ │rows   │     ← ACTUAL DATA
   │1-249  │ │250-499│        │500-749│ │750-999│
   └───────┘ └───────┘        └───────┘ └───────┘
```

Upper levels are signposts. **Leaf pages hold the real rows** — that bottom row of boxes
*is* the table.

`WHERE id = 847` costs **3 page reads**, not a million. The tree stays shallow as it
grows: 100M rows is typically only ~4 levels, because each page holds hundreds of
signposts. 10x the data ≈ one extra hop.

## Secondary indexes cost an extra hop

An index on a non-primary-key column creates a **second tree**, whose leaves contain
**primary keys**, not rows.

```
secondary index on user_id          clustered index (the table)
┌──────────────────────┐            ┌──────────────────────┐
│ user_id 42 → id 847  │ ─────────▶ │ id 847 → the row     │
└──────────────────────┘            └──────────────────────┘
     find the PK                       then fetch the row
```

Two tree walks per lookup.

## Consequences

- **The primary key determines physical layout.** Sequential keys append cleanly. Random
  keys (UUIDv4) scatter inserts across the tree, forcing page splits. This is the real
  argument for ULIDs — they're sortable, so they insert in order.
- **Locking a row means locking a place in this tree.** Contention is physical.
- **A missing index means scanning every leaf page** — all 16 KB of each, every time.

---

# Lesson 2 — Indexes in depth

## A composite index is a phone book

`INDEX (last_name, first_name)` sorts by `last_name` first, then `first_name` within it:

```
Anderson, Alice
Anderson, Bob
Brown, Adam
Smith, John          ← sorted by last_name FIRST
Smith, Karen
```

Find "Smith" instantly. Find "Smith, John" instantly. **Cannot** find everyone named
John — they're scattered throughout.

## The leftmost prefix rule

An index on `(a, b, c)` can be used for:

| Query | Works? |
|---|---|
| `WHERE a = 1` | ✅ |
| `WHERE a = 1 AND b = 2` | ✅ |
| `WHERE a = 1 AND b = 2 AND c = 3` | ✅ |
| `WHERE b = 2` | ❌ skipped `a` |
| `WHERE b = 2 AND c = 3` | ❌ skipped `a` |
| `WHERE a = 1 AND c = 3` | ⚠️ uses `a` only, filters `c` slowly |

**Start from the left, don't skip.** Three single-column indexes are *not* equivalent to
one composite index — MySQL usually picks one and ignores the rest.

## Equality first, range last

The rule most people never learn.

```sql
WHERE status = 'active' AND expires_at < NOW()
```

`status` is equality, `expires_at` is a range. The naive instinct — "most selective
column first" — points at `expires_at` and **is wrong**.

**Once an index hits a range, every column after it is useless for seeking.** Phone book
analogy: `last_name > 'S' AND first_name = 'John'` means scanning all surnames S–Z, and
within that range the first names are scattered again.

Correct order, `(status, expires_at)`:

```
status='active'    │ expires_at: 10:00, 10:01, 10:02 ... ← tight contiguous scan
status='expired'   │ ...
status='converted' │ ...
```

Jump to the `active` block, then scan a contiguous range.

## Selectivity

How well a column narrows results.

| Column | Selectivity | Verdict |
|---|---|---|
| `email` | near-unique | excellent |
| `status` (4 values) | poor alone | fine as *first* column of a composite |
| `is_deleted` boolean | ~50% | nearly useless alone |

If an index doesn't eliminate enough rows, **MySQL ignores it and scans the table** — a
sequential scan beats half a million random jumps back to the clustered index.

## Covering indexes

If the index already contains every column requested, the second tree walk is skipped
entirely.

```sql
INDEX (status, expires_at)
SELECT id, expires_at FROM holds WHERE status = 'active';
```

`id` (the PK) is already in the index leaf; `expires_at` is in the index. The table is
never touched. `EXPLAIN` shows **"Using index."**

This is why `SELECT *` costs more than it looks — asking for unneeded columns turns a
covering index into a two-hop lookup.

## Indexes are not free

Every index is another tree to keep in sync. One insert into a table with five indexes is
six tree modifications. Indexes trade **write cost for read speed** — an easy trade on a
read-heavy system, a bad one on a write-heavy log. Unused indexes are pure loss.

## Reading `EXPLAIN`

| Column | Look for |
|---|---|
| `type` | `const`/`eq_ref`/`ref`/`range` good. **`ALL` = full table scan.** |
| `key` | Index chosen. `NULL` = none. |
| `rows` | Estimated rows examined — compare to rows returned. |
| `Extra` | `Using index` = covering (good). `Using filesort`/`Using temporary` = expensive. |

Quick health check: **`type: ALL` or `key: NULL` on a large table is a problem.**

## Applied to the ticketing project

```sql
holds    INDEX (status, expires_at)   -- equality then range, for the reaper
         INDEX (user_id)              -- "my holds"

bookings UNIQUE (hold_id)             -- correctness AND speed:
                                      -- one hold → one booking, enforced by the DB
```

A unique index is an index *and* a constraint — it enforces idempotency and accelerates
lookups in one object.

## Carry forward

1. **Leftmost prefix** — can't skip columns
2. **Equality before range** — ordering rule that decides fast vs table scan
3. **Selectivity** decides whether an index gets used at all
4. **Covering indexes** skip the second hop
