# Build Exercises

Hands-on implementation. This is the primary learning vehicle for this track.

## Rules

**1. You write the code. Not AI.**

This is the whole point. The `buyvia` schema was AI-generated, which is exactly why it
can't go in your story bank. Code you write by hand can. If you get stuck, ask me to
explain the concept or review what you wrote — but don't ask me to write it.

**2. Small and sharp.**

Each exercise is one concept and a few hours. Not "build a system." If an exercise is
sprawling, it's badly specified — say so and we'll cut it down.

**3. Break it on purpose.**

Every exercise has a "prove it" step: demonstrate the failure the technique prevents,
*then* prevent it. Implementing a lock teaches you syntax. Watching inventory oversell
without one, then watching it hold with one, teaches you the concept. The second is what
you'll remember under interview pressure.

**4. Measure.**

Numbers beat intuition. Before/after timings, rows affected, queries executed. "It felt
faster" is not a result.

**5. Finish with words.**

Every exercise ends by explaining what you built, out loud, in interview vocabulary —
including the tradeoff you accepted. If you can't articulate it, you haven't finished.

## Exercise format

Each spec contains:

- **Concept** — what this teaches and why it matters in interviews
- **Task** — what to implement, and where in the repo
- **Prove it** — the failure to demonstrate first
- **Measure** — what numbers to collect
- **Stretch** — optional deeper variant
- **Verbalise** — the specific question to answer out loud when done
- **Interview mapping** — the questions this prepares you for

## Where code goes

`app/Services/` is currently empty and is the natural home for most of this. Tests go in
`tests/Feature/` and `tests/Unit/`. Concurrency tests need real parallelism — separate
processes or a load tool, not a `for` loop.

Keep exercise code on the branch, committed as you go. It doubles as evidence for your
story bank.

## Index

Exercises are written as we reach each phase.

| # | Phase | Exercise | Status |
|---|-------|----------|--------|
| — | 0 | *(no build — readiness floor is concepts and practice)* | — |
| 01 | 1 | Seed, profile and index the catalogue | not yet written |
| 02 | 2 | Inventory reservation with pessimistic locking | not yet written |
| 03 | 2 | Optimistic locking variant + contention comparison | not yet written |
| 04 | 3 | Product cache layer, stampede and fix | not yet written |
| 05 | 4 | Outbox drainer + idempotent consumer | not yet written |
| 06 | 5 | Read/write split and the read-your-writes bug | not yet written |
| 07 | 6 | Two app instances behind nginx, stateless tier | not yet written |
| 08 | 7 | Product search with an inverted index | not yet written |
| 09 | 8 | Token-bucket rate limiter in Redis | not yet written |
| 10 | 9 | Tenant scoping with per-tenant quotas | not yet written |
