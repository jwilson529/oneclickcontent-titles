<!-- GENERATED_BY_CODEX_YOLO_UI_AUDIT_V1 -->
# UI Audit

## Audit Date
2026-02-10

## Overall Assessment
The plugin now delivers a strong v1 UX for one-click title generation: clear workflow, useful scoring, actionable previews, and built-in onboarding via Title Help. The experience is close to release-ready with a short polish pass.

## What Is Working
- Clear core flow in editor: generate, compare, apply.
- Goal-aware scoring with grade + signal breakdown increases trust.
- SERP/Discover preview with pixel meter supports practical decisions.
- Title Help page provides strong onboarding and label glossary for teams.

## PHASE 1 — Critical Fixes (Before/At Launch)
- [ ] Component: Results row information density
- Problem: Too many chips visible at once can overwhelm editors.
- Recommended fix: Show top four chips by default and place remaining chips behind a `More details` disclosure.
- Reason: Reduces scan fatigue and speeds decision making.

- [ ] Component: Action hierarchy in each row
- Problem: `Apply`, `Undo`, and iterate controls compete visually.
- Recommended fix: Keep `Apply` as dominant action; demote others to lower visual weight.
- Reason: Emphasizes intended next step and reduces misclicks.

- [ ] Component: Scoring interpretation
- Problem: Users may treat score as absolute truth instead of comparative guidance.
- Recommended fix: Add concise microcopy near score/grade: "Score ranks options relative to selected goal."
- Reason: Improves trust calibration and prevents misuse.

## PHASE 2 — Refinement
- [ ] Component: Empty and edge states
- Problem: First-use or missing-config states rely on generic messaging.
- Recommended fix: Add explicit contextual prompts for missing API key, no goal, no keyword targets, and no generated rows.
- Reason: Lowers onboarding friction and support burden.

- [ ] Component: Copy consistency
- Problem: Terms are mostly aligned but can drift across settings/help/editor.
- Recommended fix: Standardize terminology across all surfaces: `Goal`, `Style`, `Keyword targets`, `Grade`, `Pass/Needs work`.
- Reason: Improves learnability and team training consistency.

## PHASE 3 — Polish
- [ ] Component: Winner explanation
- Problem: Highest-ranked row lacks a compact "why it won" message.
- Recommended fix: Add one-line rationale for rank #1 using top signal contributors.
- Reason: Adds confidence and shortens review time.

- [ ] Component: Iteration affordances
- Problem: Rewrite action labels are clear but not guided.
- Recommended fix: Add subtle helper text under iterate buttons describing expected rewrite behavior.
- Reason: Improves predictability and reduces trial-and-error.

## Design Scorecard
| Category | Score (10) |
|---|---:|
| Hierarchy | 8 |
| Consistency | 8 |
| Accessibility | 7 |
| Responsiveness | 8 |
| Visual Polish | 8 |

## Release Recommendation
Ship v1 after completing Phase 1 or accepting those items as immediate post-release patch work.
