# React

**Status: not started.** This is a scaffold. The curriculum gets built in its own
conversation.

## Scope decision: "credible, not blank"

Target roles are **mostly backend, some fullstack**. So this track is deliberately
**capped at roughly 15–20 hours** rather than run as a full curriculum. The goal is to
handle the React portion of a fullstack interview honestly and work in a React codebase
without being lost — not to present as a frontend developer.

**What "credible" means concretely.** You should be able to:

- Explain the mental model — declarative UI, one-way data flow, why re-rendering isn't
  redrawing
- Read an existing component and know what it does
- Use `useState` and `useEffect` correctly, including the dependency array
- Explain why you'd reach for React Query rather than hand-rolling fetch-and-cache
- Say plainly "I'm backend-primary, I work in React when needed" — and have that hold up

**What's explicitly out of scope for now:** performance optimisation, advanced patterns,
SSR/Next.js, state management libraries beyond the basics, animation, and build tooling
depth. Add them only if fullstack roles start converting into offers.

The minimum path is items 1–5 below. Items 6–9 are the "later" pile.

## Full scope, for reference

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
