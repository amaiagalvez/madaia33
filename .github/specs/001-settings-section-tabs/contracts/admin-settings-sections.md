# Contract: Admin Settings Sections

## Scope

Defines behavior contract for the admin settings interface after introducing `section` grouping.

## Inputs

- Persisted `settings` rows with required attributes:
  - `key`
  - `value`
  - `section`

## Section Rules

1. Valid section identifiers are constrained to approved values.
2. Every settings row must have a non-null valid `section`.
3. Any ambiguous migrated row must be assigned to `general` and logged.

## Rendering Contract

1. The settings page must render one tab per section that has at least one non-deleted setting.
2. Tabs are sorted alphabetically by section identifier.
3. On page load, active tab = first tab in alphabetical order.
4. Only settings from active section are visible/editable in the active view.

## Save Contract

1. Submitting settings on an active tab updates only keys belonging to that tab's section.
2. Save operation returns user-visible success feedback when update succeeds.
3. Section A save must not mutate Section B values.

## Localization Contract

1. Section labels must be translatable in Euskera and Spanish.
2. Missing translation keys are considered contract violations for user-facing tabs.

## Test Traceability

- FR-001..FR-004: Section persistence and migration validity
- FR-005..FR-007: Tab rendering and section-scoped save behavior
- FR-008: Localized labels
- FR-009..FR-010: Model/factory/seeding alignment
