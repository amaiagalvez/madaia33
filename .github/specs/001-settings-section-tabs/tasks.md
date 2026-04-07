# Tasks: Settings Section Field & Tabbed Panel

**Input**: Design documents from `/.github/specs/001-settings-section-tabs/`
**Prerequisites**: plan.md (required), spec.md (required), research.md, data-model.md, contracts/

## Phase 1: Setup (Shared Infrastructure)

**Purpose**: Prepare baseline files and translation surface for section-based settings

- [ ] T001 Add and document canonical section label keys (including `general`) in `lang/eu/admin.php` and `lang/es/admin.php` for later UI consumption
- [ ] T002 Create feature test scaffold for section-tab flows in `tests/Feature/AdminSettingsTest.php`
- [ ] T003 [P] Create unit test scaffold for section constraints in `tests/Unit/AdminSettingsValidationTest.php`

---

## Phase 2: Foundational (Blocking Prerequisites)

**Purpose**: Core data/domain prerequisites required before implementing user stories

**⚠️ CRITICAL**: No user story work can begin until this phase is complete

- [ ] T004 Create migration to add `section` to settings with index in `database/migrations/*_add_section_to_settings_table.php`
- [ ] T005 Implement deterministic backfill mapping and `general` fallback logging in `database/migrations/*_add_section_to_settings_table.php`
- [ ] T006 Update `Setting` fillable and section allow-list guard in `app/Models/Setting.php`
- [ ] T007 [P] Update default section state in `database/factories/SettingFactory.php`
- [ ] T008 [P] Update default seed records with section values in `database/seeders/SettingsSeeder.php`
- [ ] T009 Add foundational migration/section integrity assertions in `tests/Feature/AdminSettingsTest.php`

**Checkpoint**: Data model and section constraints are stable for story work

---

## Phase 3: User Story 1 - Navigate Settings by Section Tab (Priority: P1) 🎯 MVP

**Goal**: Admin can switch section tabs, see only section-specific settings, and save active section independently

**Independent Test**: Open admin settings, switch tabs, verify section-scoped fields and section-only save behavior without touching other sections

### Tests for User Story 1

- [ ] T010 [P] [US1] Add tab rendering and alphabetical ordering assertions in `tests/Feature/AdminSettingsTest.php`
- [ ] T011 [P] [US1] Add section-scoped save assertions (no cross-section writes) in `tests/Feature/AdminSettingsTest.php`

### Implementation for User Story 1

- [ ] T012 [US1] Refactor section-aware state loading in `app/Livewire/AdminSettings.php`
- [ ] T013 [US1] Implement active-tab first-load selection (alphabetical, non-persistent) in `app/Livewire/AdminSettings.php`
- [ ] T014 [US1] Implement tabbed settings UI and section-scoped forms in `resources/views/livewire/admin-settings.blade.php`
- [ ] T015 [US1] Wire section tab rendering in `resources/views/livewire/admin-settings.blade.php` to consume the canonical translation keys prepared in T001
- [ ] T016 [US1] Ensure save flow writes only active section keys via batched updates in `app/Livewire/AdminSettings.php`

**Checkpoint**: US1 is functional and independently testable (MVP ready)

---

## Phase 4: User Story 2 - All Existing Settings Assigned to a Section (Priority: P2)

**Goal**: All existing settings are reachable through section tabs after deployment

**Independent Test**: Run migrations on existing data and verify every settings row has non-null valid section and is reachable from tabs

### Tests for User Story 2

- [ ] T017 [P] [US2] Add migration/backfill completeness assertions for existing keys in `tests/Feature/AdminSettingsTest.php`
- [ ] T018 [P] [US2] Add fallback-to-`general` behavior assertions in `tests/Feature/AdminSettingsTest.php`

### Implementation for User Story 2

- [ ] T019 [US2] Expand known key-to-section mapping coverage in `database/migrations/*_add_section_to_settings_table.php`
- [ ] T020 [US2] Align seeded legal/admin keys with explicit sections in `database/seeders/SettingsSeeder.php`
- [ ] T021 [US2] Ensure settings query paths remain batched (`whereIn`/`pluck`) in `app/Livewire/AdminSettings.php`

**Checkpoint**: US2 preserves existing data and reachability across tabs

---

## Phase 5: User Story 3 - New Settings Include a Section Assignment (Priority: P3)

**Goal**: Future settings cannot be created without a valid section assignment

**Independent Test**: Attempt creating settings without/with invalid section and confirm validation/guard behavior rejects invalid input

### Tests for User Story 3

- [ ] T022 [P] [US3] Add section allow-list validation tests in `tests/Unit/AdminSettingsValidationTest.php`
- [ ] T023 [P] [US3] Add create/update guard tests for required section in `tests/Feature/AdminSettingsTest.php`

### Implementation for User Story 3

- [ ] T024 [US3] Enforce section allow-list in settings validation logic in `app/Validations/AdminSettingsValidation.php`
- [ ] T025 [US3] Ensure factory-generated settings always include valid section in `database/factories/SettingFactory.php`
- [ ] T026 [US3] Add safe defaults for any programmatic setting writes in `database/seeders/SettingsSeeder.php`

**Checkpoint**: US3 blocks orphan settings and keeps future data consistent

---

## Phase 6: Polish & Cross-Cutting Concerns

**Purpose**: Final hardening, quality checks, and release-ready validation

- [ ] T027 [P] Verify admin settings page browser console has no new warnings/errors in `resources/views/livewire/admin-settings.blade.php`
- [ ] T028 [P] Verify no new Laravel warnings/errors during settings flows in `storage/logs/laravel.log`
- [ ] T029 [P] Verify no new related VS Code PROBLEMS after changes in `app/Livewire/AdminSettings.php` and `resources/views/livewire/admin-settings.blade.php`
- [ ] T030 Run mandatory quality gate in Docker: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`
- [ ] T031 Sync affected spec docs in `.github/specs/001-settings-section-tabs/spec.md`, `.github/specs/001-settings-section-tabs/plan.md`, and `.kiro/specs/community-web/design.md` if behavior changed
- [ ] T032 Run focused regression tests in Docker: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminSettingsTest.php tests/Unit/AdminSettingsValidationTest.php`
- [ ] T033 Validate SC-004 explicitly by adding a new allowed section and confirming tab rendering without structural UI changes in `tests/Feature/AdminSettingsTest.php`
- [ ] T034 Run final coverage test in Docker and append result to `.docs/test_coverage.md`: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --coverage`

---

## Dependencies & Execution Order

### Phase Dependencies

- **Phase 1 (Setup)**: No dependencies, starts immediately
- **Phase 2 (Foundational)**: Depends on Phase 1, blocks all user stories
- **Phase 3 (US1)**: Depends on Phase 2
- **Phase 4 (US2)**: Depends on Phase 2; may proceed after US1 if team is sequential
- **Phase 5 (US3)**: Depends on Phase 2; may proceed after US1 if team is parallelized
- **Phase 6 (Polish)**: Depends on all targeted user stories completion

### User Story Dependencies

- **US1 (P1)**: MVP baseline after foundational completion
- **US2 (P2)**: Depends on foundational schema and complements US1 data completeness
- **US3 (P3)**: Depends on foundational schema and extends long-term data integrity

### Within Each User Story

- Add tests first, verify they fail against missing behavior
- Implement model/validation/domain changes before UI integration
- Validate story independently before moving forward

### Parallel Opportunities

- T003 can run in parallel with T001-T002
- T007 and T008 can run in parallel after T006
- T010 and T011 can run in parallel
- T017 and T018 can run in parallel
- T022 and T023 can run in parallel
- T027, T028, and T029 can run in parallel

---

## Parallel Example: User Story 1

```bash
# Parallel test authoring for US1
T010: tests/Feature/AdminSettingsTest.php (tab rendering/order)
T011: tests/Feature/AdminSettingsTest.php (section-scoped saves)

# Then implementation sequence
T012 -> T013 -> T014 -> T015 -> T016
```

---

## Implementation Strategy

### MVP First (US1)

1. Complete Phase 1 and Phase 2
2. Complete US1 (Phase 3)
3. Validate US1 independently via focused tests and manual tab flow
4. Demo/deploy MVP if accepted

### Incremental Delivery

1. Foundation ready (Phase 1-2)
2. Deliver US1 (navigation + scoped save)
3. Deliver US2 (migration/backfill completeness)
4. Deliver US3 (future-safe section enforcement)
5. Execute polish and final coverage

### Team Parallelization

1. One developer finalizes migration/model foundation
2. Another developer prepares UI tab rendering
3. Another developer prepares validation/factory constraints
4. Merge once story checkpoints pass

---

## Notes

- All implementation/validation commands must run in Docker as non-root user
- Keep DB access batched and avoid N+1 patterns in settings load/save paths
- Preserve SoftDeletes behavior and existing key semantics
- Keep translations in sync by running `/speckit.translate-es` after task generation
