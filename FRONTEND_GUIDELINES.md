<!-- GENERATED_BY_CODEX_YOLO_FRONTEND_GUIDELINES_V1 -->
# FRONTEND_GUIDELINES.md

## Purpose
Defines engineering standards for UI implementation.

---

# Framework Standards

- Bootstrap 5 is the primary UI framework.
- Use Bootstrap utilities before custom CSS.
- Avoid overriding Bootstrap core classes unless necessary.

---

# Markup Rules

- Use semantic HTML elements.
- Avoid div-only structures when semantic elements exist.
- Maintain consistent indentation.

---

# CSS Rules

- Use token values from DESIGN_SYSTEM.md
- Avoid hardcoded spacing or color values
- Group component styles logically

---

# JavaScript UI Behavior

- Avoid heavy UI frameworks unless required
- Use progressive enhancement
- Ensure accessibility in dynamic UI

---

# WordPress Admin Styling

- Respect WP admin UI patterns
- Avoid breaking native admin experience
- Maintain consistency with WordPress design language

---

# Component Structure

- Each UI component should be reusable
- Avoid duplicating markup patterns
- Maintain consistent naming conventions

---

# Accessibility Engineering

- Ensure ARIA labels exist
- Maintain keyboard navigation
- Avoid hover-only interactions

---

# Responsiveness

- Validate layouts across:
  - Mobile
  - Tablet
  - Desktop

---

# Testing UI Changes

Before shipping:
- Verify accessibility
- Validate responsive layout
- Confirm token compliance
