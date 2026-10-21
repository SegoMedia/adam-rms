## PALETTE'S JOURNAL

## 2024-05-22 - [Bootstrap 4 Input Group Accessibility]
**Learning:** Bootstrap 4's `input-group-text` inside a `span` or `div` is not automatically associated with the input it describes, unlike a proper `<label>`. This creates accessibility issues for screen reader users who won't know what the input field is for.
**Action:** Replace `span.input-group-text` with `label.input-group-text` and add a `for` attribute pointing to the input's `id`. This maintains the visual styling while providing semantic meaning.
## 2024-05-22 - Improving Form Accessibility in Twig Templates
**Learning:** Bootstrap input groups often use `span.input-group-text` as visual labels. Changing these to `label.input-group-text` with a `for` attribute is a valid and safe way to improve accessibility without breaking the layout.
**Action:** When auditing forms, check input groups for missing label associations and use `label.input-group-text` as a direct fix.
## 2024-05-22 - Icon-only Button Accessibility
**Learning:** Many icon-only buttons (like those using FontAwesome classes inside `<button>` tags) lack explicit `aria-label` and `title` attributes. This makes them completely inaccessible to screen reader users and confusing to those without tooltips.
**Action:** When auditing components or templates, ensure all icon-only interactive elements (like `button` and `a` tags) include both a descriptive `aria-label` for screen readers and a `title` tooltip for visual users.
