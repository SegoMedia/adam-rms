## PALETTE'S JOURNAL

## 2024-05-22 - [Bootstrap 4 Input Group Accessibility]
**Learning:** Bootstrap 4's `input-group-text` inside a `span` or `div` is not automatically associated with the input it describes, unlike a proper `<label>`. This creates accessibility issues for screen reader users who won't know what the input field is for.
**Action:** Replace `span.input-group-text` with `label.input-group-text` and add a `for` attribute pointing to the input's `id`. This maintains the visual styling while providing semantic meaning.
## 2024-05-22 - Improving Form Accessibility in Twig Templates
**Learning:** Bootstrap input groups often use `span.input-group-text` as visual labels. Changing these to `label.input-group-text` with a `for` attribute is a valid and safe way to improve accessibility without breaking the layout.
**Action:** When auditing forms, check input groups for missing label associations and use `label.input-group-text` as a direct fix.
## 2024-05-23 - [Input Group Label and Duplicate "for" Cleanup]
**Learning:** Bootstrap 4 forms often duplicate the `for` attribute in copy-pasted HTML (e.g. `for="username"` repeatedly for passwords) and separate visual labels from the actual input group text, creating unassociated fields or duplicate associations for screen readers. Using `label.input-group-text` with a matching `for` attribute successfully pairs the visual text to the input id, but existing adjacent `<label>` headers should either be removed if redundant or also linked properly to avoid multiple floating visual labels. For password confirm fields, adding a `<label class="sr-only">` restores screen reader context without disrupting UI.
**Action:** Audit and assign unique `id`s and `for` associations to every input and visually adjacent label. Add `sr-only` labels to inputs that rely solely on placeholders to indicate their purpose (like password confirmation fields).
