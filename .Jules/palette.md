## PALETTE'S JOURNAL

## 2024-05-22 - [Bootstrap 4 Input Group Accessibility]
**Learning:** Bootstrap 4's `input-group-text` inside a `span` or `div` is not automatically associated with the input it describes, unlike a proper `<label>`. This creates accessibility issues for screen reader users who won't know what the input field is for.
**Action:** Replace `span.input-group-text` with `label.input-group-text` and add a `for` attribute pointing to the input's `id`. This maintains the visual styling while providing semantic meaning.
## 2024-05-22 - Improving Form Accessibility in Twig Templates
**Learning:** Bootstrap input groups often use `span.input-group-text` as visual labels. Changing these to `label.input-group-text` with a `for` attribute is a valid and safe way to improve accessibility without breaking the layout.
**Action:** When auditing forms, check input groups for missing label associations and use `label.input-group-text` as a direct fix.
## 2024-03-01 - Form Label Accessibility in Input Groups
**Learning:** AdminLTE/Bootstrap `input-group-text` elements are often implemented as `<span>` tags next to inputs. While visually appearing as labels, they are not read by screen readers as associated with the input.
**Action:** When using `input-group-text`, convert the `<span>` to a `<label>` and explicitly link it to the adjacent input using a `for` attribute that matches the input's `id`. If the input lacks an `id` (like `assets_notes`), add one to complete the association.
