## PALETTE'S JOURNAL

## 2024-05-22 - [Bootstrap 4 Input Group Accessibility]
**Learning:** Bootstrap 4's `input-group-text` inside a `span` or `div` is not automatically associated with the input it describes, unlike a proper `<label>`. This creates accessibility issues for screen reader users who won't know what the input field is for.
**Action:** Replace `span.input-group-text` with `label.input-group-text` and add a `for` attribute pointing to the input's `id`. This maintains the visual styling while providing semantic meaning.
## 2024-05-22 - Improving Form Accessibility in Twig Templates
**Learning:** Bootstrap input groups often use `span.input-group-text` as visual labels. Changing these to `label.input-group-text` with a `for` attribute is a valid and safe way to improve accessibility without breaking the layout.
**Action:** When auditing forms, check input groups for missing label associations and use `label.input-group-text` as a direct fix.

## 2026-03-12 - Convert input-group-text spans to labels
**Learning:** In AdminLTE/Bootstrap templates used in this app, input group prepend texts are often implemented as `<span class="input-group-text">`. This makes form fields inaccessible as they lack proper programmatic labels, and screen readers won't associate the preceding text with the input field. Also, when generating IDs in Twig loops or contexts where multiple forms might appear (like vacant roles), we must append the entity ID to prevent duplicate ID violations.
**Action:** When working with `.input-group-text` components, convert the `span` to a `label`, add a `for` attribute, and ensure the corresponding input has a matching unique `id` (typically appending the entity ID like `{{ role.projectsVacantRoles_id }}`).
