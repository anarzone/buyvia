# Lesson Index

**Organising principle: curriculum-led, project-anchored.** Lessons follow the phase
order so coverage is guaranteed. The event ticketing project is the worked example
wherever it fits. For topics the project doesn't need at its size, we scale it up
hypothetically — *"traffic grew 50x, now what?"* — so nothing is ever taught purely in
the abstract.

Status: ✅ done · 🔄 partial · ⬜ not started

---

## Phase 1 — Data & storage

| # | Lesson | Status |
|---|--------|--------|
| 1 | How MySQL stores data — pages, clustered index, B+ trees | ⬜ |
| 2 | Indexes in depth — composite, column order, selectivity, covering, `EXPLAIN` | ⬜ |
| 3 | Choosing a data store — relational vs document vs key-value vs wide-column | ⬜ |

## Phase 2 — Concurrency & data integrity

| # | Lesson | Status |
|---|--------|--------|
| 4 | Transactions and ACID — what each letter actually guarantees | ⬜ |
| 5 | Isolation levels — dirty reads, non-repeatable reads, phantoms | ⬜ |
| 6 | Locking internals — shared vs exclusive, row vs gap, `SELECT ... FOR UPDATE` | ⬜ |
| 7 | Optimistic vs pessimistic concurrency, and when each wins | ⬜ |
| 8 | Deadlocks — how they form, how InnoDB resolves them | ⬜ |

## Phase 3 — Caching & performance

| # | Lesson | Status |
|---|--------|--------|
| 9 | Cache strategies — aside, write-through, write-behind, refresh-ahead | ⬜ |
| 10 | Invalidation and TTL — the genuinely hard part | ⬜ |
| 11 | Stampedes, thundering herds, hot keys | ⬜ |
| 12 | Redis internals — single-threaded event loop, data structures, persistence | ⬜ |

## Phase 4 — Async & messaging

| # | Lesson | Status |
|---|--------|--------|
| 13 | Queues and delivery guarantees — at-most/at-least/exactly-once | ⬜ |
| 14 | Idempotency — four techniques and their limits | ⬜ |
| 15 | Dual-write and the outbox pattern | ⬜ |
| 16 | Queue internals — Symfony Messenger and Laravel queues underneath | ⬜ |

## Phase 5 — Scale & distribution

| # | Lesson | Status |
|---|--------|--------|
| 17 | Replication — topologies, how it actually works | ⬜ |
| 18 | Replication lag and read-your-own-writes | ⬜ |
| 19 | Sharding and partitioning — shard keys, hotspots, resharding | ⬜ |
| 20 | Consistency models, CAP stated correctly, PACELC | ⬜ |

## Phase 6 — Delivery & edge

| # | Lesson | Status |
|---|--------|--------|
| 21 | Load balancing — L4 vs L7, algorithms, health checks | ⬜ |
| 22 | Stateless tiers and where session state goes | ⬜ |
| 23 | CDNs, edge caching, cache headers | ⬜ |

## Phase 7 — Search & feeds

| # | Lesson | Status |
|---|--------|--------|
| 24 | Inverted indexes — why databases are bad at text search | ⬜ |
| 25 | Feed generation — fan-out on write vs read, the celebrity problem | ⬜ |

## Phase 8 — Reliability & operations

| # | Lesson | Status |
|---|--------|--------|
| 26 | Rate limiting — token bucket, leaky bucket, sliding window | ⬜ |
| 27 | Retries, backoff, jitter, circuit breakers | ⬜ |
| 28 | Observability — metrics, logs, traces, SLOs | ⬜ |

## Phase 9 — Multi-tenancy & security

| # | Lesson | Status |
|---|--------|--------|
| 29 | Tenant isolation models and noisy neighbours | ⬜ |
| 30 | Auth at scale — sessions vs tokens vs JWTs | ⬜ |

---

## Notes

**Starting from zero.** Nothing is marked complete. Some material was discussed before
the curriculum was agreed — idempotency and delivery guarantees came up while mining a
production incident, and storage/indexes were covered in a first pass. Those notes are
kept in `02-concepts/` as reference, but they don't count as lessons delivered. We run
the sequence properly from Lesson 1.

A lesson is only marked ✅ after its task is completed, not after the theory is
delivered.

Build exercises attach to lessons rather than existing separately. Concurrency is where
the first real code lands: implement a hold, prove it oversells under concurrent load,
then fix it with locking.
