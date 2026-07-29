# The Framework

This is the spine of every system design interview. Learn this before anything else.
When you feel lost mid-interview, the answer is always "go back to the framework."

## Why this matters more than knowledge

Interviewers are usually following a rubric. They are not testing whether you know
Kafka. They are testing whether you behave like a senior engineer when handed an
underspecified problem. A candidate with average knowledge and excellent structure
outperforms a candidate with deep knowledge and no structure, almost every time.

## The 45-minute shape

| # | Step | Time | Purpose |
|---|------|------|---------|
| 1 | Requirements | 5 min | Turn a vague prompt into a bounded problem |
| 2 | Scale estimation | 5 min | Find out whether this problem is even hard |
| 3 | API sketch | 5 min | Agree on what you're actually building |
| 4 | High-level design | 12 min | Boxes and arrows, deliberately shallow |
| 5 | Deep dive | 12 min | Where the interview is won or lost |
| 6 | Bottlenecks & failure | 6 min | Demonstrate production judgment |

Times are approximate. The order is not.

---

## Step 1 — Requirements (5 min)

The prompt will be one sentence: "Design a ride-sharing app." That vagueness is
deliberate. Your first move is never to draw.

**Split requirements into two kinds:**

- **Functional** — what the system does. "Riders can request a ride. Drivers can accept."
- **Non-functional** — how well it does it. Scale, latency, availability, consistency.

**Questions worth asking, roughly in order:**

1. Who are the users, and how many?
2. What are the two or three core features? (Deliberately push to *narrow* scope.)
3. Is this read-heavy or write-heavy?
4. How fresh does the data need to be? Is stale data acceptable for a few seconds?
5. Is this global or single-region?
6. Are we designing for today's scale or 10x?

**Say this:**

> "Before I design anything, I want to nail down scope. Let me ask a few questions, then
> I'll state back what I think we're building."

**And close the step with:**

> "So to confirm: we're building X and Y, for roughly N users, read-heavy, and we can
> tolerate a few seconds of staleness. I'm explicitly leaving Z out of scope. Sound right?"

That closing restatement is a strong senior signal. It shows you can bound a problem.

**Mistakes that cost you here:**

- Drawing boxes before asking anything. This is the single most common failure.
- Accepting the full scope. Real senior engineers cut scope. Saying "I'll leave payments
  out unless you want it" is a *positive* signal, not a dodge.
- Asking questions you don't use. Every question should change your design.

---

## Step 2 — Scale estimation (5 min)

See `01-estimation.md` for the actual math. The purpose of this step is not precision —
it's to discover whether you need one database or two hundred.

**What to produce:**

- Requests per second (average and peak)
- Storage per year
- Rough read:write ratio

**Say this:**

> "Let me do some rough math to see how hard this actually is. I'll round aggressively —
> I care about order of magnitude, not precision."

**Then use the result out loud:**

> "So that's about 1,000 writes per second and 50GB a year. That's genuinely modest — a
> single well-indexed Postgres instance handles this. I'm not going to shard, and I'd
> push back on anyone who wanted to."

That last sentence is worth a lot. Knowing when *not* to add complexity is senior
behavior, and mid-size product companies specifically look for it.

**Mistakes:**

- Skipping it because it feels uncomfortable. Do it badly rather than not at all.
- Computing numbers and then never referencing them again.
- Reaching for distributed systems when the math says you don't need them.

---

## Step 3 — API sketch (5 min)

Cheap, fast, and it forces agreement on what you're building.

Four or five endpoints, no more:

```
POST   /rides          { pickup, dropoff }  -> { ride_id, eta }
GET    /rides/{id}                          -> { status, driver, eta }
POST   /rides/{id}/cancel
```

Mention auth once ("assume every request carries a bearer token") and move on. Do not
design an auth system unless asked.

**Say this:**

> "Let me sketch the main API surface so we agree on the contract before I go internal."

---

## Step 4 — High-level design (12 min)

Boxes and arrows. Stay shallow **on purpose.**

A reasonable default skeleton:

```
Client → Load Balancer → App Servers (stateless) → Database
                              ↓
                        Cache (Redis)
                              ↓
                        Queue → Workers
```

Walk the main flow end to end — follow a single request from click to storage and back.
Narrate as you go.

**Say this:**

> "I'll sketch the high-level components first and keep it shallow, then we can go deep
> wherever you're most interested."

That sentence explicitly hands the interviewer the steering wheel, which they like, and
it protects you from rat-holing.

**Your specific risk here:** with 7 years of hands-on backend work, you'll be tempted to
jump straight to schema design, index choices, or framework specifics. Resist it in this
step. Going deep unprompted reads as "can't operate at architecture level" — which is
the main thing that separates senior from mid at this stage.

---

## Step 5 — Deep dive (12 min)

The interviewer picks something: *"How do you handle two users buying the last item?"*
or *"What happens when a consumer fails halfway?"*

This is where the interview is decided, and it's where your production experience is
worth the most. You have actually debugged these situations. Most candidates have only
read about them.

**Structure your answer:**

1. Name the problem. ("This is a race condition on a shared resource.")
2. Give the simple solution first. ("Pessimistic lock — `SELECT ... FOR UPDATE`.")
3. State its cost. ("That serialises access to hot rows; under contention throughput drops.")
4. Give the alternative and when you'd switch. ("Optimistic concurrency with a version
   column is better under low contention and worse under high.")

Steps 3 and 4 are what earn the senior rating. Anyone can name a solution. Naming its
cost is the differentiator.

**Say this when you're unsure:**

> "I haven't built exactly this, but the closest thing I've worked on is X, and I'd
> reason about it this way..."

Honest and confident beats bluffing. Interviewers probe bluffs relentlessly.

---

## Step 6 — Bottlenecks and failure (6 min)

Do this unprompted if there's time left. It's the clearest senior signal available and
most candidates never get to it.

Cover:

- **What breaks first as traffic grows?** Usually the database, then the cache.
- **What happens when each component dies?** Cache down — do you fall over, or just get
  slow? Queue backed up — do you shed load or drop data?
- **How would you know?** Metrics, alerts, dashboards. "How would you know this broke at
  3am" is a very common mid-size-company follow-up.

**Say this:**

> "Let me spend the last few minutes on where this breaks and how I'd know."

---

## Fast reference

Before the interview, reread only this:

1. Ask before drawing. Restate scope.
2. Do the math, round hard, then *use the number*.
3. Sketch the API.
4. Stay shallow. Offer to go deep.
5. On deep dive: name it, simple solution, its cost, the alternative.
6. Finish on failure modes and observability.

If you blank at any point, say: *"Let me step back to the high-level picture for a
moment."* It buys you time and reads as composure rather than panic.
