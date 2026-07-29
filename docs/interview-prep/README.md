# System Design Interview Prep

A 3-week sprint (compressible to 2), ~2.5 hrs/day, targeting senior backend roles at
mid-size product companies.

## Files

| File | Use it for |
|------|-----------|
| `00-framework.md` | The 6-step interview structure. Reread this most. |
| `01-estimation.md` | Back-of-envelope math, numbers to memorise, 15 drills. |
| `02-concepts/` | One file per core topic (added as we cover them). |
| `03-experience-inventory.md` | Your honest story bank from 7 years of real work. |
| `04-problems/` | Practice problems with model answers and follow-ups. |
| `05-scoring-rubric.md` | Self-score every practice attempt. |
| `06-mock-log.md` | Running record of scores and recurring weak spots. |

## Schedule

**Week 1 — framework, estimation, storage core**

1. Framework + story mining + guided untimed walkthrough
2. Estimation math (largest gap)
3. Estimation drills + storage selection and indexing
4. Caching
5. Replication & sharding (second-largest gap)
6. Sharding continued + consistency/CAP
7. Consolidation + first full untimed problem

**Week 2 — async, scaling, timed practice**

8. Queues and async (mostly relabeling existing Symfony Messenger experience)
9. Load balancing, stateless tiers, CDN
10. Failure modes, rate limiting, retries, idempotency
11–13. Three timed 45-minute problems with review
14. Weak-spot repair

**Week 3 — mocks and polish**

15–19. One timed mock per day with adversarial follow-ups
20. War story rehearsal
21. Light review only

## Two standing rules

1. **Say it out loud.** The dominant failure mode is knowing the material but freezing
   when speaking. Reading these files silently builds a false sense of readiness.

2. **The `buyvia` codebase is a textbook, not a credential.** It is AI-assisted work.
   We read it to see concepts in concrete code, and it stays out of every interview
   answer. Your stories come from your actual 7 years — see
   `03-experience-inventory.md`.
