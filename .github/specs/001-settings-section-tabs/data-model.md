# Data Model: Settings Sections

## Entity: Setting

Represents a single persisted application configuration entry.

### Fields

- `id` (bigint, PK)
- `key` (string, unique)
- `value` (text, nullable)
- `section` (string, required)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable; SoftDeletes)

### Validation Rules (Feature Scope)

- `section` is required for every active setting.
- `section` must be one of allowed values:
  - `front`
  - `contact_form`
  - `gallery`
  - `general` (fallback/migration safety)
- `key` remains unique.

### Relationships

- No new relational tables introduced in this feature.
- `section` is a constrained attribute on `settings`, not a foreign key.

### State/Transition Notes

- Existing rows transition from implicit grouping to explicit `section` grouping during migration.
- Rows with undetermined grouping during migration transition to `general`.

## Logical Entity: SettingsSection (derived)

Not persisted as a table. Represents grouping metadata used by the admin UI.

### Attributes

- `id` (identifier string; one of allowed section values)
- `label` (localized display string)
- `order` (alphabetical by identifier for this feature)

### UI Behavior Constraints

- A section tab is shown only if at least one setting exists for that section.
- First tab (alphabetically) is selected on each page load.
