# Changelog — customfield_tipmultiselect

All notable changes to this plugin are documented here.

## 0.1.0 — 2026-08-12 (`2026081200`)

Initial release: multiselect custom field (course fields) fed by the shared
options catalogue. Requires Moodle 4.5+ and local_tipmultiselect.

### Added

- **Multiselect custom field type** — course editors pick one or more
  options with an autocomplete search; option names render in each user's
  language.
- **Category binding** — each field is bound to one option category of
  local_tipmultiselect; the binding is locked once the field has stored
  data, so existing selections can never be invalidated.
- **Selection integrity** — submitted ids are validated against the bound
  category; hidden options keep displaying and survive re-saves; selections
  exceeding the storage limit are rejected with a clear validation message.

### Notes

- Requires the local_tipmultiselect plugin (hard dependency).
- Option ids are site-local: course backups restored on a different site
  will not resolve selections against that site's catalogue.
