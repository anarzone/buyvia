# Back-of-Envelope Estimation

Your largest genuine gap, and the most learnable. This is arithmetic with aggressive
rounding, done out loud. It is not a maths test — nobody checks your answer. They check
whether you can reason about magnitude without freezing.

## The mindset

You are trying to answer one question: **is this problem hard?**

If the answer is "1,000 writes/sec and 50GB/year," the honest design is one database and
a cache. If it's "5 million writes/sec and 40PB," you need a fundamentally different
system. That's the whole point of the exercise.

Round brutally. 86,400 seconds in a day becomes 100,000. 365 days becomes 400. Nobody
cares, and it makes the mental arithmetic possible.

---

## Numbers to memorise

### Time

```
1 day       = 86,400 sec   → round to 100,000 (10^5)
1 month     = 2.5M sec
1 year      = 31.5M sec    → round to 30M
```

**The single most useful shortcut:**

```
1 million requests/day  ≈ 12 req/sec     (just over 10)
100 million/day         ≈ 1,200 req/sec  (just over 1,000)
1 billion/day           ≈ 12,000 req/sec
```

Memorise the middle one and scale from it. 100M/day ≈ 1k QPS covers a huge fraction of
interview problems.

### Data sizes

```
1 KB = 10^3 bytes    Small JSON payload, a tweet with metadata
1 MB = 10^6 bytes    A photo (compressed), ~500 pages of text
1 GB = 10^9 bytes
1 TB = 10^12 bytes
1 PB = 10^15 bytes
```

Handy rules of thumb:

```
1 million rows × 1 KB each  = 1 GB
1 billion rows × 1 KB each  = 1 TB
UUID/ULID                   = 16 bytes
Timestamp                   = 8 bytes
Typical order row           ≈ 500 bytes
Typical user record         ≈ 1 KB
```

### Latency (order of magnitude, not precise)

```
L1/memory reference           ~1 ns
Main memory read              ~100 ns
Redis GET (same datacentre)   ~0.5 ms      ← round trip dominates, not the lookup
SSD random read               ~100 µs (0.1 ms)
Indexed DB query (warm)       ~1 ms
Network round trip, same DC   ~0.5 ms
Disk seek (spinning)          ~10 ms
Network round trip, US→EU     ~150 ms
```

**The two comparisons that actually matter in interviews:**

1. Memory is roughly **100,000x faster** than a network call. This is why you cache.
2. Cross-continent round trips are ~150ms. This is why you use CDNs and put data near
   users. A user in Sydney hitting a Virginia database will feel it.

### Throughput ceilings (rough, defensible)

```
Single well-indexed SQL database:   ~5,000-10,000 writes/sec before it hurts
Single Redis instance:              ~100,000 ops/sec
Single app server:                  ~1,000-5,000 req/sec (depends heavily on work done)
```

These are approximate and you should say so. Their value is letting you conclude "one
database is enough" or "one is not enough" with a straight face.

---

## The recipe

Work in this order, every time:

**1. Users → requests per second**

```
Daily active users × actions per user per day = requests/day
requests/day ÷ 100,000 = requests/sec
```

**2. Average → peak**

Multiply by 2–3x. Traffic is never flat. Say "I'll assume peak is 3x average" and move
on — nobody will argue.

**3. Split reads and writes**

Most consumer systems are read-heavy, often 100:1 or 1000:1. E-commerce browsing versus
buying is a good example: enormous read volume, comparatively tiny write volume. State
your assumed ratio out loud.

**4. Storage**

```
writes/day × bytes per write × 365 × retention years
```

Then add a multiplier for indexes and replication — roughly 2x for indexes, ×3 if you
keep three replicas.

**5. Bandwidth (only if media is involved)**

```
requests/sec × payload size = bytes/sec
```

Usually only worth doing when images or video are in play.

---

## Worked example

> **Design a product catalogue for an e-commerce site with 10M daily active users.**

**Requests:**
- 10M DAU, each viewing ~20 product pages → 200M reads/day
- 200M ÷ 100,000 = **2,000 reads/sec average**
- Peak 3x → **6,000 reads/sec**
- Writes: maybe 100k catalogue updates/day → ~1/sec. Negligible.

**Conclusion out loud:**

> "Read:write is about 200,000:1. This is an overwhelmingly read-heavy system, so the
> design should be dominated by caching, not by write scaling. 6,000 reads/sec is well
> within reach of a cache layer plus a couple of read replicas. I would not shard this."

**Storage:**
- 10M products × 2 KB = 20 GB
- Plus indexes (~2x) = 40 GB
- Plus images, which dominate: 10M × 5 photos × 200 KB = **10 TB** → object storage + CDN

**Conclusion out loud:**

> "The relational data is trivially small — 40GB fits in memory on a decent box. The real
> storage problem is images, and those don't belong in the database at all. S3 plus a CDN."

Notice that the numbers **changed the design**. That's what the step is for. A candidate
who computes numbers and then designs the same thing regardless has wasted five minutes.

---

## Drills

Do these out loud, timed, 90 seconds each. Target: within one order of magnitude.
Answers below — don't look first.

1. Twitter has 200M DAU. Each reads 100 tweets/day. Reads per second?
2. Each of those users posts 2 tweets/day. Writes per second? Read:write ratio?
3. A tweet is 300 bytes. Storage per year for tweet text alone?
4. A SaaS app has 50,000 companies averaging 20 users each. Each user makes 200 API calls
   per working day. Peak QPS?
5. An e-commerce site does 100,000 orders/day, average 3 items each. Order + item rows
   per year?
6. Same site: each order row is 500 bytes, each item row 200 bytes. Storage per year
   including 2x for indexes?
7. A video platform stores 500 hours of video uploaded per minute at 100 MB per minute of
   video. Storage per day?
8. You have 10M users with 1 KB profiles. Can the whole thing fit in a single Redis
   instance with 64 GB RAM?
9. A payment webhook endpoint receives 5M events/day with 3x peak. Can one app server
   handle peak?
10. A notification system sends 20M emails/day. QPS? Does it need a queue?
11. 1M product pages, each cached at 50 KB. How much cache memory for a full warm cache?
12. A logging system writes 10,000 events/sec at 1 KB each. Storage per day? Per month?
13. Your database holds 2 TB and you can read 200 MB/sec from disk. How long for a full
    table scan?
14. A user in Sydney queries a database in Virginia, 6 sequential queries per page load.
    Roughly what latency does the page load carry from network alone?
15. You cache 95% of reads at 8,000 reads/sec. How many reads/sec still reach the database?

---

### Answers

1. 200M × 100 = 20B/day ÷ 100k = **200,000 reads/sec**
2. 200M × 2 = 400M/day ÷ 100k = **4,000 writes/sec**. Ratio **50:1** read-heavy.
3. 400M × 300 bytes = 120 GB/day × 365 ≈ **44 TB/year**. Text is cheap; media wouldn't be.
4. 50,000 × 20 = 1M users × 200 = 200M calls/day ÷ 100k = 2,000/sec × 3 peak =
   **6,000 QPS**. (Bonus: working-day concentration makes real peak higher — worth saying.)
5. 100k orders + 300k items = 400k rows/day × 365 ≈ **146M rows/year**. Modest for MySQL.
6. (100k × 500) + (300k × 200) = 50 MB + 60 MB = 110 MB/day × 365 ≈ 40 GB × 2 =
   **80 GB/year**. One database is fine.
7. 500 hours = 30,000 minutes of video arriving per minute of wall clock.
   30,000 × 100 MB = 3 TB per minute. × 1,440 minutes/day = **~4.3 PB/day**.
   This is a genuinely hard storage problem — and the point of the drill is recognising
   that instantly, not the exact figure.
8. 10M × 1 KB = **10 GB**. Yes, comfortably — with room for overhead.
9. 5M ÷ 100k = 50/sec × 3 = 150/sec peak. **Yes, easily** — one server handles ~1,000/sec.
   Webhooks are rarely a throughput problem; they're a *correctness* problem (duplicates,
   ordering, retries).
10. 20M ÷ 100k = **200/sec**. Yes, queue it — not for throughput but because SMTP is slow,
    fails, and must be retried without blocking the request.
11. 1M × 50 KB = **50 GB**. Too big for one modest Redis box; cache hot subset only
    (Pareto: 20% of products get 80% of traffic).
12. 10,000 × 1 KB = 10 MB/sec = **864 GB/day**, ~**26 TB/month**. Needs retention policy,
    compression, and partitioning by date.
13. 2 TB ÷ 200 MB/sec = 10,000 sec ≈ **~3 hours**. This is why you never full-scan in
    production and why migrations on large tables need care.
14. ~150 ms × 6 sequential round trips = **~900 ms** of pure network. This is why you
    batch queries and put read replicas near users.
15. 5% of 8,000 = **400 reads/sec** hit the database. Easy. Note also: this is why cache
    *misses* during a cold start or eviction storm are dangerous — losing the cache means
    going from 400 to 8,000 instantly.

---

## Scoring yourself

Target by end of Week 1: **12 out of 15 within one order of magnitude, spoken aloud,
under 90 seconds each.** Speed matters — in a real interview you're doing this while
also thinking about design.

If you're consistently off by more than 10x, the usual culprit is unit conversion
(mixing per-day and per-second). Slow down on step 1 of the recipe.
