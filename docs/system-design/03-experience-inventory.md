# Experience Inventory — The Honest Story Bank

## Why this file exists

"Tell me about a system you designed or worked on" is close to guaranteed at a mid-size
product company, and it is usually the highest-leverage question in the whole loop. It's
also the one place where 7 years of real work beats any amount of studying.

**Ground rule:** everything in this file must come from work you actually did and can
defend under three follow-up questions. AI-generated work does not qualify — you cannot
defend design decisions you didn't make, and getting caught there is far worse than
telling a smaller story well.

A modest system you understand completely beats an impressive one you don't.

**One exception, and it grows over time:** code you write yourself in the ticketing
project qualifies. "I implemented ticket holds with pessimistic locking and load-tested it
until it oversold" is fully defensible — you made the decisions and watched it fail. As
that work accumulates it becomes a second story bank alongside your seven years.

## What makes a good story

Interviewers are listening for four things:

1. **A real constraint.** Not "we built an API" but "we had 40 minutes of nightly
   downtime and needed it gone."
2. **A decision you made,** with alternatives you rejected.
3. **A tradeoff you accepted knowingly.** This is the senior signal. "We chose X, which
   cost us Y, and that was acceptable because Z."
4. **What you learned or would change.** Reflection reads as maturity.

Stories where something **broke** are usually the best material. A war story about
duplicate message processing at 2am is worth more than a clean greenfield build.

## Raw material to mine

From your background — Redis, Symfony Messenger consumers, databases, e-commerce, SaaS —
these are the veins most likely to contain gold. Fill in what you actually experienced.

### Async / message consumers (Symfony Messenger)

Prompts to jog memory:

- Did a consumer ever process the same message twice? What broke? How did you fix it?
- Did you ever have a queue back up badly? What did you do?
- How did you handle messages that kept failing — retries, dead-letter queues, manual
  replay?
- Did you ever have ordering problems, where message B was processed before message A?
- What happened when a consumer crashed mid-processing?

> **Interview vocabulary this maps to:** at-least-once delivery, idempotent consumers,
> dead-letter queues, backpressure, poison messages, exactly-once as an illusion.

**Your story:**
_(to fill in)_

---

### Redis / caching

Prompts:

- What did you actually cache, and how did you decide the TTL?
- Did you ever serve stale data to a user and have to deal with it?
- How did you invalidate cache when the underlying data changed?
- Did Redis ever go down? What happened to the app — slow, or dead?
- Did you use Redis for anything other than caching (locks, sessions, rate limiting,
  queues)?

> **Interview vocabulary:** cache-aside, TTL-based invalidation, cache stampede,
> thundering herd, hot keys, cache as availability dependency vs performance optimisation.

**Your story:**
_(to fill in)_

---

### E-commerce

Prompts:

- Did you deal with overselling, or two users buying the last item?
- How did you handle payment callbacks/webhooks arriving twice, or out of order?
- Did you ever have to reconcile a payment that succeeded at the provider but failed in
  your system?
- How were carts stored, and what happened when they expired?
- Any experience with order state machines going wrong?

> **Interview vocabulary:** race conditions on shared inventory, pessimistic vs optimistic
> locking, idempotency keys, the dual-write problem, eventual consistency between systems
> of record.

**Your story:**
_(to fill in)_

---

### SaaS / multi-tenancy

Prompts:

- How was tenant data isolated — shared tables with a tenant_id, separate schemas,
  separate databases?
- Did one large tenant ever degrade performance for everyone (noisy neighbour)?
- How did you handle migrations across tenants?
- Any per-tenant rate limiting or quotas?

> **Interview vocabulary:** tenant isolation models, noisy neighbour problem, shard key
> selection (tenant_id is a natural one), blast radius.

**Your story:**
_(to fill in)_

---

### Database work

Prompts:

- Did you ever fix a slow query? What was actually wrong?
- Have you added an index to a large table in production? How did you avoid locking it?
- Any experience with read replicas — and did you ever read stale data from one?
- Ever had a migration go wrong?

> **Interview vocabulary:** index selectivity, query plans, replication lag,
> read-your-own-writes consistency, online schema change.

**Your story:**
_(to fill in)_

---

## Story template

For each of your top 3–4 stories, fill this in. Aim to deliver in **under 5 minutes.**

```
TITLE:

CONTEXT (2 sentences — what the system did, rough scale):

PROBLEM (what actually went wrong, or what constraint forced a decision):

OPTIONS I CONSIDERED:
  - Option A —
  - Option B —

WHAT I CHOSE AND WHY:

WHAT IT COST (the tradeoff — do not skip this):

OUTCOME:

WHAT I'D DO DIFFERENTLY:

LIKELY FOLLOW-UPS AND MY ANSWERS:
  Q:
  A:
  Q:
  A:
```

## Handling the limits of your experience

You will be asked about things you haven't done. The correct move is never to bluff.

> "I haven't operated at that scale directly. The closest I've dealt with is X. Reasoning
> from that, I'd expect the first thing to break to be Y — is that the direction you're
> interested in?"

This is a strong answer. It's honest, it demonstrates transferable reasoning, and it
invites the interviewer to guide you. Bluffing invites them to dig until you collapse.

## If asked about AI-assisted work

If a project you list was substantially AI-assisted and it comes up, the safe framing is
about what you genuinely own: the requirements, the review, the debugging, the
integration decisions. Don't claim architectural reasoning you didn't do. Interviewers
respect the distinction far more than they respect the inflated version — and they find
out either way in the deep dive.
