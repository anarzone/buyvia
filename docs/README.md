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

## Workflow — decided

**All three tracks run in a single conversation.** Splitting them across separate chats
was considered and rejected: the logistics cost more than the structural benefit, and the
substance lives in these repo docs rather than in conversation history anyway.

Practical consequences:

- **The docs are the memory.** Long conversations get their context summarized over
  time. Anything worth keeping gets written to `docs/`, not left in chat.
- **One track at a time gets real attention.** System design is primary. Framework
  internals is the change of pace. React waits for fullstack interviews to appear.
- **Say which track you're switching to** when you switch, so the thread reorients.

### Branches

Work happens on a `claude/...` branch and merges to `main` when a chunk is done. Folder
ownership still applies as a matter of tidiness:

| Track | Owns |
|-------|------|
| System design | `docs/system-design/` |
| Framework internals | `docs/framework-internals/` |
| React | `docs/react/` |

If you later decide to spin a track into its own conversation, each track README carries
a ready-made opening prompt for exactly that.

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
