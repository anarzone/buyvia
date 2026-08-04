# Handoff — Start Here

Paste the prompt below into a new Claude Code session running inside this project. It
gives the session everything it needs; the rest of the context lives in these docs.

---

## Opening prompt

> I'm continuing a system design interview prep project. Read `docs/` before responding —
> start with `docs/system-design/07-build-plan.md`, then `06-project-design.md`.
>
> **About me:** backend engineer, 7 years PHP/Laravel/Symfony, plus Redis, Symfony
> Messenger, e-commerce and SaaS. Preparing for senior system design interviews — I've
> never sat one. Applying to roles daily, timeline open-ended. Rusty and refreshing
> quickly.
>
> **What we're building:** an event ticketing platform, designed from scratch. Customers
> browse events, hold tickets for 10 minutes, then pay. Holds expire and return inventory.
> Requirements, estimation, API, architecture and data model are all in
> `06-project-design.md`. The core problem is inventory contention under on-sale spikes.
>
> **How we work:**
> - Build first, deep dives on demand. No fixed syllabus, no bite-sized lessons.
> - You're the moderator — drive the sequence, decide when a step is done, tell me when
>   something is worth pausing on.
> - I write some of the code and ask you for the rest. Don't assign homework and wait.
> - When something's worth understanding (Laravel internals, request lifecycle, SQL,
>   Eloquent, Redis, AWS, React), stop and teach it properly, in simple language, until I
>   say I've got it.
> - Write deep dive notes into `docs/system-design/02-concepts/` so they survive the
>   conversation.
>
> **Where we are:** build plan step 1 — project setup with MySQL, Redis and LocalStack.
> AWS is developed locally against LocalStack, with one real deployment at the end.
>
> Read the docs first, then tell me what you understand the current state to be, so I can
> correct you if anything's off.

---

## What's in here

```
docs/
├── HANDOFF.md                    ← this file
├── README.md                     ← the three learning tracks and how they relate
├── system-design/
│   ├── README.md                 ← how the track works
│   ├── 07-build-plan.md          ← START HERE: 20-step build sequence + AWS track
│   ├── 06-project-design.md      ← the architecture and the reasoning behind it
│   ├── 00-framework.md           ← the 6-step interview framework
│   ├── 01-estimation.md          ← back-of-envelope math + 15 drills
│   ├── 02-concepts/              ← deep dive notes, added as topics come up
│   ├── 03-experience-inventory.md ← story bank from real work history
│   ├── 04-scoring-rubric.md      ← senior-level self-scoring rubric
│   └── 05-mock-log.md            ← mock scores and weak spots
├── framework-internals/          ← Laravel + Symfony internals track
└── react/                        ← React track, capped scope
```

## Project setup (build plan step 1)

```bash
php artisan sail:install --with=mysql,redis
```

Add LocalStack to `docker-compose.yml` alongside `mysql` and `redis`:

```yaml
    localstack:
        image: 'localstack/localstack:3'
        ports:
            - '${LOCALSTACK_PORT:-4566}:4566'
        environment:
            SERVICES: 's3,sqs,sns,ses,dynamodb,events'
            AWS_DEFAULT_REGION: 'us-east-1'
            DEBUG: '0'
        volumes:
            - 'sail-localstack:/var/lib/localstack'
        networks:
            - sail
        healthcheck:
            test: ["CMD", "curl", "-f", "http://localhost:4566/_localstack/health"]
            retries: 5
            timeout: 10s
```

Add `sail-localstack:` under the existing `volumes:` block, matching how `sail-mysql` is
declared.

Add to `.env`:

```env
AWS_ACCESS_KEY_ID=test
AWS_SECRET_ACCESS_KEY=test
AWS_DEFAULT_REGION=us-east-1
AWS_ENDPOINT=http://localstack:4566
AWS_URL=http://localhost:4566
AWS_USE_PATH_STYLE_ENDPOINT=true
```

LocalStack ignores credentials, but the AWS SDK refuses to start without them — hence the
dummy values. `AWS_ENDPOINT` is used container-to-container; `AWS_URL` is for access from
your host.

Bring it up and verify:

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan migrate

./vendor/bin/sail mysql -e "SELECT VERSION();"
./vendor/bin/sail redis redis-cli ping
curl http://localhost:4566/_localstack/health
```

## Architecture in one paragraph

Stateless Laravel behind a load balancer. **MySQL is the sole source of truth** for holds,
inventory and bookings. **Redis is a derived cache and fail-fast gate, never
authoritative** — that distinction is what stops it becoming a dual-write problem. Queue
workers drain an outbox for email and integration events. A scheduled reaper releases
expired holds. Payment calls carry an idempotency key. Inventory is a counter on
`ticket_types`, chosen knowingly as the hot row so contention can be measured before it's
optimised away.
