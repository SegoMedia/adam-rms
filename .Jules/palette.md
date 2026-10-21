## PALETTE'S JOURNAL

## 2024-05-22 - [Bootstrap 4 Input Group Accessibility]
**Learning:** Bootstrap 4's `input-group-text` inside a `span` or `div` is not automatically associated with the input it describes, unlike a proper `<label>`. This creates accessibility issues for screen reader users who won't know what the input field is for.
**Action:** Replace `span.input-group-text` with `label.input-group-text` and add a `for` attribute pointing to the input's `id`. This maintains the visual styling while providing semantic meaning.
## 2024-05-22 - Improving Form Accessibility in Twig Templates
**Learning:** Bootstrap input groups often use `span.input-group-text` as visual labels. Changing these to `label.input-group-text` with a `for` attribute is a valid and safe way to improve accessibility without breaking the layout.
**Action:** When auditing forms, check input groups for missing label associations and use `label.input-group-text` as a direct fix.
## 2026-02-27 - Missing ARIA labels on icon-only buttons in Twig templates
**Learning:** Found that Twig templates like `src/manufacturers.twig` make heavy use of AdminLTE and FontAwesome for UI but frequently omit `aria-label` attributes on icon-only `<button>` and `<a>` elements, rendering them inaccessible to screen readers.
**Action:** Always verify that icon-only interactive elements in Twig templates include descriptive `aria-label` attributes (and optionally `title` attributes for visual tooltips) to improve accessibility and user experience.