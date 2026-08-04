# Build Plan — Event Ticketing

**Approach: build first, deep dives on demand.** We build the system step by step. When
something is worth understanding — Laravel internals, request lifecycle, SQL, Eloquent,
Redis, AWS, React — we stop and go deep right there, in context. No fixed syllabus.

Deep dives are triggered by curiosity, or by Claude flagging *"this step touches X, worth
a look?"*

## Build sequence

| # | Step | Deep dives available | AWS |
|---|------|---------------------|-----|
| 1 | Laravel + Docker (MySQL 8, Redis, LocalStack) | Project structure, service providers, bootstrapping, container | LocalStack |
| 2 | Migrations: `events`, `ticket_types` + CHECK constraints | Schema design, InnoDB storage, constraints | — |
| 3 | Models, factories, seeder at realistic volume | Eloquent internals, hydration, N+1, builder → SQL | — |
| 4 | `GET /events`, `GET /events/{id}` | **Request lifecycle end to end**, routing, middleware | — |
| 5 | `POST /holds` — naive, no locking | Transactions, ACID | — |
| 6 | Concurrent test proving oversell | Race conditions, isolation levels | — |
| 7 | Fix with `SELECT … FOR UPDATE` | Row locking internals, gap locks, deadlocks, optimistic alternative | — |
| 8 | Hold expiry: reaper + scheduler | Cron vs workers, index usage, clock skew | EventBridge, Lambda |
| 9 | `POST /purchase` + idempotency key | Idempotency, dual-write, payment patterns | DynamoDB |
| 10 | Outbox table + drainer worker | Outbox pattern, at-least-once, DLQs, queue internals | **SQS**, SNS |
| 11 | Confirmation emails | Async delivery, bounces, retries | **SES** |
| 12 | Event images / ticket PDFs | Object storage, presigned URLs, why not the DB | **S3** |
| 13 | Redis caching for browse | Cache-aside, invalidation, stampedes, Redis internals | ElastiCache |
| 14 | Rate limiting holds | Token bucket, sliding window, Redis atomicity, Lua | API Gateway |
| 15 | Event search | Inverted indexes, why `LIKE` fails, relevance | **OpenSearch** |
| 16 | Organizer dashboard + read replica | Replication, lag, read-your-own-writes | RDS replicas |
| 17 | Multi-tenancy | Tenant isolation, noisy neighbours, sharding by tenant | — |
| 18 | Observability | Metrics, structured logs, traces, SLOs | **CloudWatch** |
| 19 | Real AWS deploy (once) | VPC, security groups, IAM, cost | ALB, ECS, RDS, CloudFront |
| 20 | *(optional)* React frontend | Components, hooks, server vs client state | CloudFront + S3 |

**Steps 5–7 are the centrepiece.** Build it broken, prove it oversells under real
concurrency, then fix it. That sequence teaches more than any explanation of locking.

**Steps 15–17** deliberately cover what the project wouldn't otherwise need at its
designed scale — search, replication, sharding. All known interview gaps.

## AWS track

**Local first, real AWS once.** Everything runs against LocalStack or plain containers so
iteration is free. Step 19 is a single real deployment to make it concrete.

**LocalStack free tier covers:** S3, SQS, SNS, SES, DynamoDB, EventBridge, Lambda,
CloudWatch Logs.

**Not free tier:** RDS, ElastiCache, OpenSearch — run these as ordinary Docker containers
locally (MySQL, Redis, OpenSearch), which is closer to real development anyway. Managed
service differences are taught as concepts and met for real at step 19.

**The interview value is the mapping, not the console.** SQS *is* at-least-once delivery
with visibility timeouts and DLQs. S3 *is* object storage with its own consistency
characteristics. CloudFront *is* a CDN with cache-key and invalidation tradeoffs. Knowing
the concept underneath the brand name is what gets scored.

## Working rules

- Anar writes some of the code and asks Claude for the rest — not homework-and-report.
- Claude moderates: drives the sequence, decides when a step is done, flags what's worth
  pausing on.
- Deep dives on demand, at whatever depth is asked, in simple language.
- Anar says when to skip something he knows, and when something isn't landing.
- Deep dive notes land in `02-concepts/` so they survive the conversation.

## Definition of done

A step is complete when it runs **and** Anar can explain what it does and why.

Steps 5–7 specifically: verified by a concurrent test that oversells before the fix and
doesn't after. Measured, not asserted.
