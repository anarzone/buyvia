# Project: Event Ticketing Platform — Design Record

A fresh Laravel project, designed from zero. This file records the design decisions and,
more importantly, **why** each one was made — the reasoning is the part worth rereading.

> Every decision below was made deliberately and can be defended. That's the point of
> designing it from scratch rather than inheriting a schema.

Sequence followed: **Requirements → Estimation → API → High-level design → Data model.**

---

## Step 1 — Requirements

### Functional

1. Organizers create events (venue, date, ticket types, quantities)
2. Customers browse and search events
3. Customers place a **hold** on tickets while checking out
4. Customers pay, converting the hold into a confirmed booking
5. Unpaid holds expire and return inventory
6. Confirmation email on booking

### Out of scope (deliberately)

Seat maps and seat selection — general admission only. Refunds, cancellations, discount
codes, waitlists, mobile apps.

Cutting scope early is a habit, not a shortcut. Everything above can be added later.

### Non-functional

| Decision | Consequence |
|---|---|
| Availability counts may be **approximate** | Browse path can be served entirely from cache |
| Hold window: **10 minutes** | Needs an expiry mechanism; caps locked inventory |
| Scale: **~1M users**, spiky on-sales | Sharding almost certainly unnecessary |
| Read-heavy, ~1000:1 | Design is dominated by caching, not write scaling |
| Correctness > availability **at purchase** | Checkout can afford locking; browsing cannot |

The "approximate availability" choice is the most consequential one on this page. Had it
been "must be exact," the browse path would be uncacheable and the entire architecture
would be organized around surviving that instead.

---

## Step 2 — Estimation

**Assumptions:** 1M registered users, ~10% daily active → 100k DAU, ~10 page views each.
On-sale spike of 50,000 people in the first minute, ~8 requests each.

```
Average:  1,000,000 views/day ÷ 100,000 sec  ≈    10 req/sec
Spike:      400,000 requests ÷ 60 sec        ≈ 6,700 req/sec   (~700x)
  of which reads                             ≈ 6,500/sec
  attempted holds                            ≈   800/sec
Storage:  a few GB/year
```

### What the numbers concluded

- **No sharding, no partitioning.** The storage math says so plainly. Being able to
  conclude "this doesn't need to be distributed" is a senior judgement.
- **Reads must be cache-served.** Not an optimisation — a requirement at 6,500/sec.
- **Writes are bounded by inventory; reads are not.** If an event has 10,000 tickets, at
  most 10,000 holds can ever succeed. Excess attempts are doomed and can be shed cheaply.
- **The bottleneck is hot-row contention, not throughput.** 800 writes/sec is
  unremarkable — but all of them contend for *one event's inventory row*. Locking
  serialises them. This is a latency problem, and adding capacity makes it worse.

Sizing this system for its average would put it offline the first time a real event went
on sale.

---

## Step 3 — API

```
GET    /events?q=&city=&from=&to=       browse, cached
GET    /events/{id}                      detail + approximate availability

POST   /events/{id}/holds                { ticket_type_id, quantity }
                                         → { hold_id, expires_at }
                                         → 409 if sold out (served from cache)
DELETE /holds/{hold_id}                  release early

POST   /holds/{hold_id}/purchase         { payment_token }
       Idempotency-Key: <client uuid>    → { booking_id }

POST   /events                           organizer
GET    /events/{id}/sales                organizer dashboard

POST   /webhooks/payments                payment provider
```

### Decisions worth remembering

**A hold is a resource, not a flag.** `POST /holds` creates something with its own ID and
expiry rather than setting `reserved = true`. This gives the hold a lifecycle — expire,
extend, release, audit. A boolean can do none of that.

**Purchase carries an idempotency key.** Client-generated, stable across retries. Without
it, a dropped connection plus a retry charges the customer twice.

**Sold-out returns 409 from cache**, never touching the database. This is the fail-fast
decision made concrete.

**Nothing mutates inventory directly.** Inventory changes only as a side effect of holds
and bookings. An inventory endpoint would be an invitation to oversell.

---

## Step 4 — High-level design

```
                    ┌─────────┐
   Browser ────────▶│   CDN   │  static assets
                    └─────────┘
        │
        ▼
   ┌─────────────┐
   │Load Balancer│
   └─────────────┘
        │
        ▼
   ┌──────────────────┐        ┌───────────────┐
   │  App servers     │◀──────▶│     Redis     │  availability cache
   │  (stateless)     │        │ sold-out gate │  NOT authoritative
   └──────────────────┘        └───────────────┘
        │         │
        │         └──────────────▶ ┌──────────────┐
        │                          │ Queue workers│──▶ email / outbox
        ▼                          └──────────────┘
   ┌──────────────┐
   │ MySQL primary│  holds, inventory, bookings ──▶ read replica (dashboards)
   └──────────────┘
        ▲
        │
   ┌──────────┐                    ┌──────────────────┐
   │  Reaper  │ expired holds      │ Payment provider │ ◀── webhooks
   └──────────┘                    └──────────────────┘
```

### Request flows

**Browse** — Redis hit returns cached event and approximate availability. Miss reads the
replica and repopulates. At 6,500 req/sec this never touches the primary.

**Hold** — Redis gate first: sold out → `409` immediately. Otherwise a MySQL transaction
locks the inventory row, verifies stock, inserts the hold, decrements. Redis refreshed
after. The only contended path, deliberately the narrowest.

**Purchase** — Verify hold is valid and unexpired. Call payment provider *with an
idempotency key*. On success, convert hold to booking and write an outbox event **in the
same transaction**. A worker drains the outbox and sends email — so a failed email can
never roll back a paid booking.

**Expiry** — Scheduled reaper releases holds past `expires_at` and returns inventory.

### The central decision: where holds live

**MySQL is the sole source of truth. Redis is a derived cache and a fail-fast gate,
never authoritative.**

The reasoning matters more than the conclusion:

- *Redis only* — fast, and TTL makes expiry free. But it isn't durable; a failover loses
  holds, and if inventory lives there too you've lost the record of what's sold.
- *MySQL only* — one source of truth, fully transactional, durable. But every hold attempt
  hits the hot row, and expiry needs a reaper job that can silently die.
- *Both* — the trap. Two **authoritative** stores can disagree, which is the dual-write
  problem all over again.

**"Both" is only safe when one side is clearly a derived cache.** If Redis is stale or
lost, we rebuild it from MySQL and the worst outcome is an inaccurate number on a browse
page. A source of truth that's allowed to be wrong isn't a source of truth — and Step 1
already declared browse availability *may* be wrong. That settles which one it is.

Cost accepted knowingly: expiry needs a reaper rather than being free via Redis TTL.
That's the price of durability.

### Properties this buys

- App tier is **stateless** — add servers during an on-sale
- Primary is protected on reads by cache, and on writes by the fail-fast gate
- Payment side effects go through the **outbox**, not inline calls

### Known open weakness

**Hot-row contention on inventory.** ~800 hold attempts/sec all locking one event's
inventory row. Named deliberately, not overlooked — it's the first thing to attack in
Step 5 and the build that follows.

---

## Step 5 — Data model

```sql
events           id, organizer_id, title, venue, starts_at,
                 status ENUM(draft, on_sale, closed)

ticket_types     id, event_id, name, price_cents,
                 quantity_total, quantity_held, quantity_sold
                 CHECK (quantity_held + quantity_sold <= quantity_total)
                 CHECK (quantity_held >= 0 AND quantity_sold >= 0)

holds            id, ticket_type_id, user_id, quantity,
                 status ENUM(active, converted, expired, released),
                 expires_at DATETIME(6), created_at
                 INDEX (status, expires_at)      ← the reaper's query

bookings         id, hold_id UNIQUE, user_id, ticket_type_id,
                 quantity, total_cents,
                 status ENUM(pending_payment, confirmed, failed)

payments         id, booking_id, provider, provider_ref UNIQUE,
                 amount_cents, status, raw_payload JSON

outbox_events    id, event_type, aggregate_type, aggregate_id,
                 payload JSON, occurred_at, published_at NULL
                 INDEX (published_at, occurred_at)

idempotency_keys key PK, user_id, response JSON, created_at
```

### Inventory representation — decided

**A counter on `ticket_types`** (`quantity_total / held / sold`), chosen *knowing it's
the hot row*. Available = total − held − sold.

The alternatives, for when we outgrow it:

- **Row per ticket** — one row per seat; contention spreads across rows. Natural path to
  seat selection. Costs millions of rows and a trickier "find N available" query.
- **Bucketed counters** — split inventory into N buckets, hold picks one at random,
  dividing contention by N. Used in production; needs fallback when a bucket empties.

We start with the counter deliberately so the contention can be **measured** before it's
optimised away. Building it, load-testing it, and watching requests serialise teaches
more than being handed the sophisticated version.

### Load-bearing schema details

| Detail | Why |
|---|---|
| `CHECK (held + sold <= total)` | Oversell backstop. If locking logic is wrong, the database refuses the write. |
| `bookings.hold_id UNIQUE` | One hold → one booking. A retried purchase fails at the DB, not in application code. |
| `payments.provider_ref UNIQUE` | Webhook replays collapse into one payment row. |
| `holds INDEX (status, expires_at)` | The reaper's sweep. Without it, a full scan every minute that degrades as the system gets busier. |

Three of those four are **idempotency guarantees enforced by constraints rather than
logic** — the same class of problem as the production incident in
`03-experience-inventory.md`, solved structurally instead of procedurally.
