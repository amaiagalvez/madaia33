# Research: Settings Sections and Tabbed Admin UI

## Decision 1: Use a constrained section allow-list

- Decision: Use an explicit set of valid section identifiers (`front`, `contact_form`, `gallery`, `general`) enforced in model/service-level logic and migration defaults.
- Rationale: Prevents invalid or inconsistent section values and makes tab generation deterministic.
- Alternatives considered:
  - Free-form section strings in DB: rejected due to drift and typo risk.
  - Separate `settings_sections` table: rejected for current scope; adds complexity without immediate value.

## Decision 2: Migrate existing settings in a deterministic batch

- Decision: Update all existing settings in a single migration strategy that maps known keys to `contact_form`; unknown/ambiguous keys fallback to `general` and are logged.
- Rationale: Guarantees FR-003 and FR-004 in one deploy step and avoids null sections.
- Alternatives considered:
  - Manual post-deploy assignment: rejected due to operational risk.
  - Failing migration on unknown keys: rejected to avoid deploy blocking.

## Decision 3: Keep tab ordering alphabetical and non-persistent

- Decision: Render tabs alphabetically and always open the first tab on page load.
- Rationale: Matches clarified product decisions and keeps behavior predictable.
- Alternatives considered:
  - Persist last active tab in storage: rejected by clarification.
  - Custom priority ordering: rejected for now to avoid extra configuration.

## Decision 4: Keep saves scoped to active section

- Decision: Save operation should update only keys belonging to the active tab section.
- Rationale: Reduces accidental cross-section mutations and aligns with user story expectations.
- Alternatives considered:
  - Save all sections at once: rejected for higher risk of unintended writes.

## Decision 5: Localized section labels are required

- Decision: Add translation keys for each section label in Euskera and Spanish.
- Rationale: FR-008 requires consistency across both supported locales.
- Alternatives considered:
  - Raw identifiers as labels: rejected for poor UX and localization non-compliance.
