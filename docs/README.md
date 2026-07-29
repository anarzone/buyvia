# Learning Tracks

Study material for Anar's job search. Three tracks, run as **separate conversations**,
all writing docs into this repo.

| Track | Folder | Serves | Status |
|-------|--------|--------|--------|
| System design | `system-design/` | Senior backend design rounds | Active — Phase 0 |
| Framework internals | `framework-internals/` | Technical depth rounds, live coding, daily work | Not started |
| React | `react/` | Fullstack roles only | Not started |

## Why separate conversations

They're different modes of learning. System design is breadth-first architecture plus a
verbal performance skill. Framework internals is depth-first source code reading. Mixing
them in one thread means neither gets a coherent structure, and long threads lose context.

Keeping the docs in one repo means your notes stay together even though the
conversations don't.

## Suggested priority

Depends on the roles you're applying to, but as a default for backend positions:

1. **System design** — the largest gap relative to 7 years of experience, and the round
   most likely to be the deciding factor at senior level.
2. **Framework internals** — compounding value. Helps interviews *and* daily work.
3. **React** — only if targeting fullstack.

Running all three at once is possible but slow. Two is realistic alongside applying.

## Cross-links

The tracks touch in a few specific places. When you hit these, go deep in the other
thread rather than duplicating:

| Topic | System design phase | Framework internals |
|-------|--------------------|--------------------|
| Queues, retries, failure handling | Phase 4 — Async & messaging | Symfony Messenger transports and middleware |
| Caching layers | Phase 3 — Caching | Laravel cache stores and repository |
| Query building, N+1, eager loading | Phase 1 — Data & storage | Eloquent internals |
| Middleware, request lifecycle | Phase 6 — Delivery & edge | HTTP kernel and pipeline |

## Standing rule across all tracks

Code you write yourself counts as your own work and belongs in your story bank
(`system-design/03-experience-inventory.md`). AI-generated code — including the existing
`buyvia` schema — does not, and should stay out of interview answers.
