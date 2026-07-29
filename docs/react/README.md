# React

**Status: not started.** This is a scaffold. The curriculum gets built in its own
conversation.

## Read this before starting

React has the weakest return of the three tracks **if you're applying to backend roles**.
It has essentially no overlap with system design and doesn't help in a backend technical
depth round.

It's worth real time in exactly two cases:

1. You're targeting **fullstack** positions, where it's not optional.
2. You want it for its own sake, independent of the job search.

If neither applies, deprioritise it until you've landed something. Splitting attention
three ways while applying daily is how all three tracks end up shallow.

## Scope, if you do it

Your background is backend, so the useful framing is "what's genuinely different here"
rather than a from-scratch web tutorial.

1. **The mental model** — declarative UI, why re-rendering isn't redrawing, one-way data
   flow. The main conceptual jump from server-rendered PHP.
2. **Components, props, state** — composition, lifting state, controlled vs uncontrolled.
3. **Hooks properly** — `useState`, `useEffect` and its dependency array (the biggest
   source of bugs), `useMemo`, `useCallback`, `useRef`, custom hooks.
4. **Rendering behaviour** — when React re-renders, why it's usually fine, and how to
   find the cases where it isn't.
5. **Data fetching** — client state vs server state, and why React Query or similar
   exists. This is where backend intuition transfers well.
6. **Forms and validation.**
7. **Routing** — React Router, or Next.js if targeting that.
8. **Testing** — React Testing Library.
9. **Build tooling** — Vite (already a dependency in this repo), bundling, code splitting.

## How to start that thread

Open a new conversation and say something like:

> I want to learn React. I'm a backend developer with 7 years of PHP/Laravel/Symfony
> experience and limited frontend background. Read `docs/react/README.md` in this repo
> for scope. I learn best by building.

## Note on this repo

`buyvia` has `vite.config.js` and `resources/js/`, so it's wired for a frontend, but
there's nothing meaningful there yet. It could host a React frontend against the
Laravel API — which would have the side benefit of being a fullstack project you built
end to end.
