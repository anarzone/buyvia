# System Design — Learning Track

Build-first system design study, targeting senior backend roles. Open-ended timeline,
full topic coverage, hands-on implementation in this repo.

## How this track works

Every phase follows the same loop:

```
TEACH  →  BUILD  →  VERBALISE  →  DESIGN PROBLEM
```

1. **Teach** — the concept, properly, with tradeoffs. Not a survey.
2. **Build** — implement it by hand in `buyvia`. Small and sharp: one concept, a few
   hours. Specs live in `07-build-exercises/`.
3. **Verbalise** — explain what you built in interview language, out loud. This is the
   step people skip, and it's the one the interview actually tests.
4. **Design problem** — a full 45-minute problem exercising the concept at scale.

Building is the vehicle, not the goal. The point of implementing pessimistic locking is
to be able to *talk* about concurrency with authority, not to ship a reservation service.

## Structure

| File | Purpose |
|------|---------|
| `00-framework.md` | The 6-step interview structure. Reread most. |
| `01-estimation.md` | Back-of-envelope math + 15 drills. Use as warm-up every session. |
| `02-concepts/` | One file per topic, written as we cover it. |
| `03-experience-inventory.md` | Honest story bank from 7 years of real work. |
| `04-problems/` | Design problems with model answers and follow-ups. |
| `05-scoring-rubric.md` | Self-score every practice attempt. |
| `06-mock-log.md` | Running record of scores and weak spots. |
| `07-build-exercises/` | Hands-on implementation specs. |
| `08-local-setup.md` | Getting `buyvia` running on your machine. |

---

## Phase 0 — Readiness floor

**Goal: survive a surprise interview.** You're applying daily, so an interview could land
before the deep work is done. This phase buys insurance, fast. Everything after it is
depth with no deadline.

- The 6-step framework (`00-framework.md`)
- Estimation math to the 12/15 target (`01-estimation.md`)
- Story mining — extract 3–4 defensible war stories (`03-experience-inventory.md`)
- Fast survey pass of core concepts — enough to not be blank on any of them
- Two untimed problems, then one timed mock

**Exit criteria:** score 19+ on a timed mock using `05-scoring-rubric.md`.

---

## Phase 1 — Data & storage foundations

Relational modelling, normalisation and when to break it. How indexes actually work
(B-trees, selectivity, covering indexes, composite index column order). Reading query
plans. Storage engine basics. SQL vs document vs key-value vs wide-column, and how to
choose without hand-waving.

**Build:** seed `buyvia` with millions of rows, find the slow queries, fix them with
indexes, measure the difference. Read an `EXPLAIN` plan and understand every column.

---

## Phase 2 — Concurrency & data integrity

Transactions and isolation levels (and what each one actually prevents). Race conditions
on shared resources. Pessimistic vs optimistic locking. Deadlocks. Idempotency as a
design principle.

**Build:** implement `InventoryReservationService` with `SELECT ... FOR UPDATE`. Then
*prove* it works — write a concurrent load test that oversells without the lock and
doesn't with it. Implement an optimistic-locking variant and compare under contention.

---

## Phase 3 — Caching & performance

Cache-aside, write-through, write-behind, refresh-ahead. TTL strategy. Invalidation, the
genuinely hard part. Stampedes and thundering herds. Hot keys. Redis data structures
beyond `GET`/`SET`. Cache as performance optimisation vs cache as availability dependency.

**Build:** a caching layer for the product catalogue. Deliberately trigger a stampede
under load, then fix it with locking and jitter. Measure both.

---

## Phase 4 — Async & messaging

Queues and workers. At-least-once vs at-most-once, and why exactly-once is mostly a lie.
Dead-letter queues, poison messages, backpressure. Ordering guarantees. The dual-write
problem and the outbox pattern. Event-driven architecture and its costs.

**Build:** an outbox drainer plus a genuinely idempotent consumer. Break it on purpose —
duplicate delivery, out-of-order arrival, consumer crash mid-processing.

> Cross-links to the Symfony internals track: Messenger's transport, retry and failure
> handling are worth reading in source alongside this phase.

---

## Phase 5 — Scale & distribution

Replication topologies. Replication lag and everything it breaks — especially
read-your-own-writes. Sharding: choosing a shard key, hotspotting, cross-shard queries,
and the pain of resharding. Partitioning strategies. Consistency models, CAP stated
correctly, and PACELC.

**Build:** configure Laravel's read/write connection split, then *demonstrate* the
read-your-writes bug and fix it. Implement tenant-based partitioning using the existing
`Tenant` model.

---

## Phase 6 — Delivery & edge

Load balancing: L4 vs L7, algorithms, health checks, connection draining. Stateless
application tiers and where session state actually goes. CDNs, edge caching, and cache
headers that work. Reverse proxies and API gateways. TLS termination. DNS-level routing.

**Build:** nginx in front of two app instances. Demonstrate what breaks with sticky
sessions, then make the tier genuinely stateless.

---

## Phase 7 — Search & feeds

Inverted indexes and why databases are bad at text search. Elasticsearch/OpenSearch
fundamentals: analysis, relevance scoring, index design. Autocomplete and typeahead.
Feed generation: fan-out on write vs fan-out on read, and the celebrity problem. Ranking.

**Build:** product search with a real inverted index. A simple activity feed implemented
both ways, with a measured comparison.

---

## Phase 8 — Reliability & operations

Rate limiting: token bucket, leaky bucket, sliding window. Retries, exponential backoff,
jitter. Circuit breakers and bulkheads. Graceful degradation and load shedding. Timeouts
and cascading failure. Observability: metrics, logs, traces, and what an SLO actually is.

**Build:** a token-bucket rate limiter in Redis. A circuit breaker around a flaky
dependency. Then instrument something and answer "how would I know this broke at 3am?"

---

## Phase 9 — Security & multi-tenancy

Authentication vs authorisation at scale. Sessions vs tokens vs JWTs and their tradeoffs.
Tenant isolation models — shared table, schema-per-tenant, database-per-tenant. Noisy
neighbours. Blast radius. Secrets handling.

**Build:** tenant scoping with per-tenant quotas and rate limits.

---

## Running throughout

- **Estimation warm-up** — 10 minutes of drills at the start of every session. Spaced
  repetition beats one dedicated day.
- **One design problem per phase**, written up in `04-problems/`.
- **A mock interview every two phases**, scored in `06-mock-log.md`.

## Full problem set

No cuts. Roughly ascending difficulty:

1. URL shortener
2. E-commerce checkout with inventory
3. Rate limiter
4. Payment processing with idempotency and webhooks
5. Notification / email delivery
6. Multi-tenant SaaS data isolation
7. Product search with autocomplete
8. Activity / news feed
9. Chat and messaging
10. File storage (Dropbox-style)
11. Ride-sharing / geospatial matching
12. Video streaming

## Two standing rules

1. **Say it out loud.** The dominant failure mode is knowing the material but freezing
   when speaking. Silent reading builds false confidence.

2. **The `buyvia` schema is a textbook, not a credential.** The existing migrations and
   models were AI-assisted and don't represent your design reasoning — keep them out of
   interview answers. **Code you write yourself in the build exercises is different:**
   that you own, and it belongs in your story bank.

## Related tracks

Run these as separate conversations, writing into this same repo:

- `docs/framework-internals/` — Symfony and Laravel internals. Complementary prep, but
  aimed at coding and technical-depth rounds rather than system design. Overlaps this
  track at Messenger (Phase 4) and cache/queue layers (Phase 3).
- `docs/react/` — frontend. No meaningful overlap with this track.
