# Self-Scoring Rubric (Senior Level)

Use this after every practice problem and mock. Score honestly — inflated scores waste
the little time we have. Record results in `06-mock-log.md`.

This mirrors how mid-size product companies actually evaluate. At 7 years experience you
are being measured against a **senior** bar: less about exotic scale, more about
judgment, tradeoffs, and whether you've clearly operated real systems.

## Dimensions

Score each 1–4.

### 1. Problem framing

| Score | Behaviour |
|-------|-----------|
| 1 | Started designing immediately. No questions asked. |
| 2 | Asked a few questions but didn't use the answers. |
| 3 | Gathered functional and non-functional requirements, restated scope. |
| 4 | Also actively cut scope, and the answers visibly shaped the design. |

### 2. Estimation

| Score | Behaviour |
|-------|-----------|
| 1 | Skipped it, or froze. |
| 2 | Produced numbers but never used them. |
| 3 | Reasonable numbers, correct units, referenced later. |
| 4 | Numbers directly drove a design decision — including deciding *not* to add complexity. |

### 3. High-level design

| Score | Behaviour |
|-------|-----------|
| 1 | Disorganised; no clear request flow. |
| 2 | Components listed but flow unclear, or dived into detail too early. |
| 3 | Clean component diagram, traced a request end to end, stayed appropriately shallow. |
| 4 | Also explained why each component exists and what it would cost to remove. |

### 4. Depth on demand

| Score | Behaviour |
|-------|-----------|
| 1 | Could not go deeper when asked. |
| 2 | Described a solution but not its cost. |
| 3 | Named the problem, gave a solution, stated its tradeoff. |
| 4 | Also gave the alternative and the conditions under which you'd switch. |

### 5. Tradeoff articulation *(highest weight)*

| Score | Behaviour |
|-------|-----------|
| 1 | Presented choices as obviously correct, no alternatives acknowledged. |
| 2 | Mentioned alternatives but no clear reasoning for the choice. |
| 3 | Explained why, including what was given up. |
| 4 | Explicitly framed decisions as reversible/irreversible and cheap/expensive to change. |

### 6. Production judgment

| Score | Behaviour |
|-------|-----------|
| 1 | No mention of failure, monitoring, or operations. |
| 2 | Mentioned failure modes only when prompted. |
| 3 | Volunteered what breaks first and what happens when components die. |
| 4 | Also covered observability — "how would I know at 3am" — and blast radius. |

### 7. Communication

| Score | Behaviour |
|-------|-----------|
| 1 | Long silences, or rambling without structure. |
| 2 | Understandable but hard to follow; interviewer had to steer constantly. |
| 3 | Clear narration, signposted transitions between steps. |
| 4 | Also handled pushback well — updated the design without becoming defensive or collapsing. |

## Bands

Total out of 28:

| Total | Band |
|-------|------|
| 24–28 | Strong hire (senior) |
| 19–23 | Hire |
| 14–18 | Borderline — likely mid-level offer, not senior |
| < 14 | No hire |

**Phase 0 exit target: a single timed mock at 19+.** Longer term: consistent 22+, since
you have time to get there.

## The two disqualifiers

Regardless of total score, these sink an interview on their own:

1. **Never asked what you were building.** Reads as inability to handle ambiguity.
2. **Bluffed and got caught.** Once an interviewer finds one bluff, they stop trusting
   everything else you said. Always prefer "I haven't done that — here's how I'd reason
   about it."

## Known personal risk areas

Track these specifically, since they're predictable given your background:

- **Diving too deep too early.** Strong hands-on experience makes schema and index talk
  tempting in step 4. Check: did you stay shallow until invited?
- **Under-claiming.** First-time interviewees with real experience often hedge more than
  warranted. If you've genuinely operated it, say so plainly.
- **Estimation freeze.** The newest skill under the most pressure. Check: did you do the
  math out loud without long silence?
