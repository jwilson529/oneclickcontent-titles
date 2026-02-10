<!-- GENERATED_BY_CODEX_YOLO_DESIGN_SYSTEM_V1 -->
# DESIGN_SYSTEM.md

## Purpose
Defines all visual tokens, component standards, and UI consistency rules.
All UI styling MUST reference values defined in this file. No hardcoded design values allowed.

---

# Core Philosophy

- Simplicity is architecture.
- Every element must justify its existence.
- Consistency is mandatory.
- Mobile-first design is the default.
- Accessibility is not optional.

---

# Typography

## Font Stack
Primary:
- System UI stack or project defined web font

Fallback:
- Arial, Helvetica, sans-serif

---

## Type Scale

| Token | Size |
|--------|----------|
| text-xs | 12px |
| text-sm | 14px |
| text-base | 16px |
| text-lg | 18px |
| text-xl | 20px |
| text-2xl | 24px |
| text-3xl | 32px |

---

## Font Weight Tokens

- weight-regular: 400
- weight-medium: 500
- weight-semibold: 600
- weight-bold: 700

---

# Spacing Scale

All margins, padding, and layout spacing MUST use these values.

| Token | Value |
|----------|-----------|
| space-1 | 4px |
| space-2 | 8px |
| space-3 | 12px |
| space-4 | 16px |
| space-5 | 24px |
| space-6 | 32px |
| space-7 | 48px |

---

# Border Radius

- radius-sm: 4px
- radius-md: 8px
- radius-lg: 12px
- radius-xl: 16px

---

# Shadows

- shadow-sm: subtle elevation for cards
- shadow-md: modals, dropdowns
- shadow-lg: overlays

---

# Color Tokens

## Semantic Colors

### Primary
Used for main CTAs and brand highlights.

### Secondary
Used for supporting elements.

### Success
Used for confirmations and positive states.

### Warning
Used for caution and validation warnings.

### Danger
Used for destructive or error actions.

### Neutral
Used for backgrounds, borders, and layout separation.

---

# Accessibility Requirements

- Minimum contrast ratio: WCAG AA
- Focus indicators must be visible
- Keyboard navigation must function fully
- Labels required for all form inputs

---

# Layout Rules

- Content should align to consistent grid
- Vertical rhythm must follow spacing scale
- Avoid nested card patterns when possible

---

# Component Standards

## Buttons

### Primary Button
- Used for single main action
- Must be visually dominant

### Secondary Button
- Supporting actions only

### Danger Button
- Destructive actions only

---

## Forms

- Labels must be visible
- Validation messages must be clear
- Inline help text allowed

---

## Cards

- Use consistent padding token
- Use defined radius token
- Avoid decorative styling without purpose

---

## Tables

- Use consistent row spacing
- Maintain readable density
- Provide hover feedback

---

# Responsiveness

- Mobile-first required
- Touch targets minimum 44px height
- Layout must adapt fluidly between breakpoints

---

# Motion Guidelines

- Motion must reinforce feedback
- Avoid decorative animation
- Transitions must feel natural and responsive

---

# Token Update Rules

If new values are needed:

1. Propose change
2. Get approval
3. Add token here
4. Implement using token
