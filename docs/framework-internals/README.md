# Framework Internals — Laravel & Symfony

**Status: not started.** This is a scaffold. The curriculum gets built in its own
conversation.

## Scope

Laravel and Symfony **together**, not separately. Laravel is built on Symfony components
— `HttpFoundation`, `Console`, `Routing`, `EventDispatcher`, `VarDumper` and others sit
underneath it. Studying them as one track is far more efficient than two, and the seam
between them is itself good interview material.

## What this track serves

Different rounds than system design:

- **Technical depth interviews** — "what happens between `index.php` and your
  controller?", "how does the container resolve a dependency?", "why is this query N+1?"
- **Live coding** — moving faster and debugging with intent rather than guesswork.
- **Daily work** — unlike system design study, this makes you immediately better at the
  job.

## Rough shape

To be refined in the dedicated thread, but a likely progression:

1. **Request lifecycle** — `public/index.php` through kernel, middleware pipeline,
   routing, controller dispatch, response. Where Symfony's `HttpFoundation` ends and
   Laravel begins.
2. **Service container** — binding, resolution, autowiring, contextual binding,
   singletons, deferred providers. The thing most Laravel developers use daily and
   understand least.
3. **Service providers & bootstrapping** — registration vs boot phases, deferred
   providers, and how a package wires itself in.
4. **Eloquent internals** — query builder to SQL, relationship loading, N+1 and eager
   loading, model events, hydration cost.
5. **Symfony components under Laravel** — `HttpFoundation`, `Console`, `EventDispatcher`,
   `Routing`. What Laravel wraps and what it replaces.
6. **Symfony Messenger** — transports, middleware stack, retry and failure handling,
   worker lifecycle. Directly relevant to your existing production experience.
7. **Cache, queue and session layers** — store abstractions, drivers, locking, atomic
   operations.
8. **Testing internals** — how the framework boots in tests, database transactions,
   what makes suites slow.

## How to start that thread

Open a new conversation and say something like:

> I want to learn Laravel and Symfony internals in depth. Read
> `docs/framework-internals/README.md` in this repo for scope, and
> `docs/system-design/README.md` for how my other track is structured — I'd like a
> similar build-first approach. I have 7 years of PHP experience. Let's start with the
> request lifecycle.

## Method

Same as the system design track, adapted:

```
READ SOURCE  →  TRACE  →  BREAK  →  EXPLAIN
```

- **Read source** — actual vendor code, not blog posts about it.
- **Trace** — follow one real request or command all the way through, with a debugger or
  `dd()`.
- **Break** — remove or misconfigure something and watch what fails. Fastest way to
  learn what a layer is actually for.
- **Explain** — out loud, as if in an interview.

The `buyvia` app is a usable subject: `vendor/` has the full Laravel 12 and Symfony
source once `composer install` has run. See `../system-design/08-local-setup.md`.

## Cross-links to system design

| This track | System design phase |
|-----------|--------------------|
| Symfony Messenger | Phase 4 — Async & messaging |
| Cache stores and locking | Phase 3 — Caching |
| Eloquent, query building | Phase 1 — Data & storage |
| HTTP kernel, middleware | Phase 6 — Delivery & edge |
