# 🎯 TASK: UP-009 - Task 04: High Contrast Theme (F-ACC-04)
- **Status:** 🔴 PENDING
- **Dependencies:** None

## 1. Objective
Add a High Contrast theme option with fully opaque surfaces and contrast ratios meeting WCAG AAA.

## 2. Files to Create/Modify
- `resources/css/app.css`
- `resources/js/Components/NxThemeSwitcher.vue`

## 3. Implementation Steps
1. Add a `[data-theme="high-contrast"]` CSS variable block in `app.css`.
2. Update `NxThemeSwitcher.vue` to add the High Contrast option.
3. Apply `data-theme="high-contrast"` to `<html>` and persist it.

## ✅ Final Verification
- [ ] High Contrast theme option is available
- [ ] Surfaces are opaque and high contrast
- [ ] Theme persists on page reload
