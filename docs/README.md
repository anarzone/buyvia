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

## Priority — decided

Target roles: **mostly backend, some fullstack.** That fixes the order:

1. **System design** — primary. Largest gap relative to 7 years of experience, and the
   round most likely to decide a senior offer.
2. **Framework internals** — secondary. Compounding value: helps technical depth rounds
   *and* daily work.
3. **React** — capped, not skipped. Target "credible, not blank" (~15–20 hours), not
   fluency. See `react/README.md`. Revisit properly only if fullstack roles start
   converting.

Run one and a half tracks at a time, not three. System design gets the real hours;
framework internals fills the gaps when you want a break from design practice.

## Workflow — how to run multiple threads

Each track runs in its own Claude Code conversation. A new conversation has **no memory**
of the others; context transfers through this repo, which is why these README files
exist.

### Starting a track

1. Make sure `main` is current: `git checkout main && git pull origin main`
2. Open a new conversation.
3. Paste the opening prompt from that track's README (`framework-internals/README.md` or
   `react/README.md`). It tells the new thread what to read.

### Branches

Each session gets its own `claude/...` branch tied to its session ID — threads cannot
share one. That's fine, because **each track owns a separate folder**, so conflicts are
unlikely:

| Track | Owns |
|-------|------|
| System design | `docs/system-design/` |
| Framework internals | `docs/framework-internals/` |
| React | `docs/react/` |
| *(shared)* | `docs/README.md` — edit rarely, mention it if you do |

Code written for build exercises (`app/`, `tests/`) is shared ground. If two tracks are
touching application code at once, merge to `main` often.

### Keeping threads in sync

- **Merge to `main` when a chunk of work is done**, not at the very end.
- **Pull `main` before starting a session** so a thread doesn't work from a stale tree.
- If a thread has been idle a while, tell it to `git pull origin main` before it writes
  anything.

### Practical advice

Don't run all three at once. Start framework internals when you want a break from design
practice; leave React until fullstack interviews actually start appearing.

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
