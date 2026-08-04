# System Design — Event Ticketing Project

Build-first system design study for senior backend interviews. We build an event
ticketing platform and go deep on whatever it touches.

## How this works

**Build first, deep dives on demand.** We build the project step by step. When something
is worth understanding — Laravel internals, request lifecycle, SQL, Eloquent, Redis, AWS,
React — we stop and go deep right there, in context. No fixed syllabus, no bite-sized
lessons.

Deep dives are triggered by curiosity, or by Claude flagging *"this step touches X, worth
a look?"*

## Files

| File | Purpose |
|------|---------|
| `07-build-plan.md` | **Start here.** The 20-step build sequence and AWS track. |
| `06-project-design.md` | The architecture: requirements, estimation, API, design, data model — and the reasoning behind each decision. |
| `00-framework.md` | The 6-step interview structure. Reread before any interview. |
| `01-estimation.md` | Back-of-envelope math, numbers to memorise, 15 drills with answers. |
| `02-concepts/` | Deep dive notes, written as topics come up. |
| `03-experience-inventory.md` | Honest story bank from 7 years of real work. |
| `04-scoring-rubric.md` | Senior-level rubric for self-scoring practice attempts. |
| `05-mock-log.md` | Running record of mock scores and recurring weak spots. |

## Working rules

- **Anar writes some of the code and asks Claude for the rest.** Not homework-and-report.
- **Claude is the moderator** — drives the sequence, decides when a step is done, flags
  what's worth pausing on.
- **Deep dives on demand**, at whatever depth is asked for, in simple language. Continue
  at that depth until Anar says "skip it, I know this."
- **Anar says when something isn't landing.** Silent agreement is the one failure mode
  Claude can't detect.
- **Deep dive notes go in `02-concepts/`** so they survive the conversation.

## Definition of done

A build step is complete when it runs **and** Anar can explain what it does and why.

The concurrency work (build plan steps 5–7) is verified by a concurrent test that
oversells before the fix and doesn't after — measured, not asserted.

## Two standing rules

1. **Say it out loud.** The dominant failure mode is knowing the material but freezing
   when speaking. Silent reading builds false confidence.

2. **Only claim work you actually did.** Code written by hand in this project belongs in
   the story bank and can be defended in an interview. AI-generated code cannot — an
   interviewer's follow-up questions will find the gap, and that's far worse than having
   a smaller story.

## Related tracks

- `../framework-internals/` — Laravel and Symfony internals. Complementary prep, aimed at
  technical depth rounds rather than design rounds.
- `../react/` — frontend, capped scope. Only matters for fullstack roles.
