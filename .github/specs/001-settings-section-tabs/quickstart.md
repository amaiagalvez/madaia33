# Quickstart: Implement Settings Sections

## Prerequisites

- Docker Compose available
- Run all commands in Docker as non-root user

## 1. Prepare/refresh environment

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer install
```

## 2. Create and apply database changes

- Add migration to introduce `section` to `settings` and backfill values.
- Ensure fallback to `general` for ambiguous keys.

Run migrations:

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan migrate
```

## 3. Update backend and UI behavior

- Update `Setting` model fillable attributes to include `section`.
- Update relevant factory/seeding behavior.
- Refactor `AdminSettings` Livewire component to:
  - Load settings grouped by section
  - Render alphabetical tabs
  - Default to first tab on page load
  - Save only active section settings
- Update Blade view to show section tabs and section-scoped forms.
- Add/update translation keys for section labels in `lang/eu` and `lang/es`.

## 4. Validate quality and tests

Run quality gate first:

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality
```

Run focused tests:

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --filter=AdminSettings
```

## 5. Manual verification checklist

- Settings page shows one tab per non-empty section.
- Tabs are alphabetical.
- First tab is selected on every page load.
- Editing/saving in one tab does not modify other sections.
- Existing setting keys remain accessible.
- Unknown migrated keys appear under `general`.
