# Implementation Plan: Settings Section Field & Tabbed Panel

**Branch**: `001-settings-section-tabs` | **Date**: 2026-04-07 | **Spec**: [.github/specs/001-settings-section-tabs/spec.md](.github/specs/001-settings-section-tabs/spec.md)
**Input**: Feature specification from `/.github/specs/001-settings-section-tabs/spec.md`

## Summary

Add a mandatory `section` attribute to settings, migrate existing records to valid sections, and restructure the admin settings experience into section-based tabs (alphabetical order, no tab persistence between page loads). The implementation keeps Laravel conventions, uses batch reads/writes, and preserves localization and validation behavior.

## Technical Context

**Language/Version**: PHP 8.4, Blade, Alpine.js (minimal), Livewire 4  
**Primary Dependencies**: Laravel 13, Livewire 4, Tailwind CSS v4, Fortify  
**Storage**: MySQL (`settings` table) with SoftDeletes  
**Testing**: Pest 4 (Feature + Unit; optional Browser if UI behavior becomes brittle)  
**Target Platform**: Linux containers via Docker Compose  
**Project Type**: Laravel web application (admin interface + backend persistence)  
**Performance Goals**: Keep settings page interaction responsive and keep DB interactions batched (single `whereIn` read + grouped writes per save)  
**Constraints**: Docker-only execution, no host PHP/Composer/NPM commands, preserve existing setting keys and translations  
**Scale/Scope**: Existing settings admin scope, initial sections (`front`, `contact_form`, `gallery`, plus migration fallback `general`)

## Constitution Check

*GATE: Must pass before Phase 0 research. Re-check after Phase 1 design.*

- [x] Docker-first execution defined for build, quality, and test commands
- [x] Laravel conventions preferred over custom abstraction layers
- [x] Test strategy defined (Unit/Feature/Browser where applicable)
- [x] Quality gate included: `composer quality` in Docker
- [x] Data model honors SoftDeletes and query-efficiency constraints
- [x] Spec-to-plan traceability is explicit for all major decisions

## Project Structure

### Documentation (this feature)

```text
.github/specs/001-settings-section-tabs/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── admin-settings-sections.md
└── tasks.md
```

### Source Code (repository root)

```text
app/
├── Livewire/
│   └── AdminSettings.php
├── Models/
│   └── Setting.php
├── Validations/
│   └── AdminSettingsValidation.php

database/
├── factories/
│   └── SettingFactory.php
├── migrations/
│   └── *_settings*.php
└── seeders/

resources/views/
└── livewire/
   └── admin-settings.blade.php

lang/
├── eu/
└── es/

tests/
├── Feature/
└── Unit/
```

**Structure Decision**: Use the existing Laravel monolith layout. No new top-level folders are required.

## Phase 0: Research Summary

See [.github/specs/001-settings-section-tabs/research.md](.github/specs/001-settings-section-tabs/research.md).

- Constrained section values should be represented as explicit allow-list constants (or equivalent central map) to prevent invalid grouping.
- Existing settings are migrated in one deterministic pass; unresolved keys go to `general` and are logged.
- UI tabs are alphabetical and reset to first tab on load by explicit product decision.
- Localization keys for section labels are mandatory for Euskera and Spanish.

## Phase 1: Design Outputs

- Data model: [.github/specs/001-settings-section-tabs/data-model.md](.github/specs/001-settings-section-tabs/data-model.md)
- Contract: [.github/specs/001-settings-section-tabs/contracts/admin-settings-sections.md](.github/specs/001-settings-section-tabs/contracts/admin-settings-sections.md)
- Quickstart: [.github/specs/001-settings-section-tabs/quickstart.md](.github/specs/001-settings-section-tabs/quickstart.md)

## Test Strategy

1. Unit tests:
   - Section allow-list behavior and mapping utilities (if introduced).
   - Migration mapping logic (including fallback to `general`).
2. Feature tests:
   - Admin settings screen renders tabs by section.
   - Active tab shows only section-specific settings.
   - Save updates only active section keys.
3. Optional browser test:
   - Tab switching behavior if server/client interaction is not reliably covered by Feature tests.

## Quality Gate Commands (Docker, non-root)

- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --filter=AdminSettings`

## Post-Design Constitution Check

- [x] Docker-first execution remains enforced in quickstart and test instructions
- [x] Laravel-first approach retained (Livewire component + Eloquent model updates)
- [x] Testing approach is explicit and scoped to impacted behavior
- [x] Quality gate command included and compatible with non-root execution
- [x] Data strategy preserves SoftDeletes and uses batched DB access
- [x] Spec-to-plan traceability preserved (FR-001..FR-010 mapped in contract and model)

## Complexity Tracking

No constitution violations identified; no complexity exceptions required.
