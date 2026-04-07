# Feature Specification: Settings Section Field & Tabbed Panel

**Feature Branch**: `001-settings-section-tabs`  
**Created**: 2026-04-07  
**Status**: Draft  
**Input**: User description: "en la tabla de settings quiero añadir un nuevo campo section, su valores entre otros serán front, contact_form, galery, ... a cada uno de los registros le añadiré su sección y en el panel de control en la vista settings habrá una pestaña para cada section."

## User Scenarios & Testing *(mandatory)*

### User Story 1 - Navigate Settings by Section Tab (Priority: P1)

An admin user opens the Settings panel in the control panel and sees a set of tabs, one per section (e.g., "Front", "Contact Form", "Gallery"). Clicking a tab shows only the settings belonging to that section. The admin can edit the values in the active section and save them independently.

**Why this priority**: This is the principal user-facing change. Without section-based tabs, no part of the feature delivers value to the admin.

**Independent Test**: Can be fully tested by navigating to the admin settings page, verifying that section tabs are present, clicking each tab, and confirming only settings for that section are displayed and editable.

**Acceptance Scenarios**:

1. **Given** the admin settings page is loaded, **When** the page renders, **Then** a tab is displayed for each section that has at least one active setting record.
2. **Given** the admin is on the settings page, **When** they click a section tab, **Then** only the settings belonging to that section are visible in the form.
3. **Given** the admin edits a setting value within the active section, **When** they submit the form, **Then** only the settings of that section are saved and a success confirmation is shown.
4. **Given** the admin is on a section tab, **When** they navigate to another tab, **Then** unsaved changes in the previous tab are discarded (no cross-section pollution).

---

### User Story 2 - All Existing Settings Assigned to a Section (Priority: P2)

All existing setting records in the database are assigned to an appropriate section so the tabbed view is immediately functional after deployment without data loss or gaps.

**Why this priority**: The tabbed UI depends on every setting having a valid section value. Missing assignments would hide settings from the admin interface.

**Independent Test**: Can be tested by verifying that every row in the settings table has a non-null section value after the data migration, and that each known setting key appears in its expected tab.

**Acceptance Scenarios**:

1. **Given** the migration and data assignment are applied, **When** querying all settings, **Then** every record has a non-null `section` value.
2. **Given** the admin opens the settings panel, **When** they browse all section tabs, **Then** every previously existing setting is reachable through one of the tabs.

---

### User Story 3 - New Settings Include a Section Assignment (Priority: P3)

When a new setting is added to the system in future, it includes a section assignment so it always appears in the correct tab without requiring manual fixes.

**Why this priority**: Ensures long-term maintainability and prevents orphaned settings that would not appear in any tab.

**Independent Test**: Can be tested by verifying that the section field is enforced (non-null, bounded to known values) at the data level.

**Acceptance Scenarios**:

1. **Given** a new setting is created, **When** no section is specified, **Then** the system prevents persistence and reports a validation error.
2. **Given** a new setting is created with a valid section, **When** the admin opens the settings panel, **Then** it appears under the corresponding section tab.

---

### Edge Cases

- What happens when a section has no settings assigned? The tab for that section should not appear.
- What happens if a setting has a `section` value not yet represented by a tab? It must be normalized to `general` before rendering so it remains reachable in the `general` tab.
- What if an admin saves a section form that contains no changed values? The save operation should complete gracefully without errors.
- What happens to settings with a `null` section after migration? They must be assigned before or during migration; the feature should not leave any setting without a section.

## Requirements *(mandatory)*

### Functional Requirements

- **FR-001**: The `settings` table MUST include a `section` field that groups each setting into a named category.
- **FR-002**: The `section` field MUST only accept a predefined set of values (e.g., `front`, `contact_form`, `gallery`); the set is extensible but bounded for data integrity.
- **FR-003**: The `section` field MUST be non-nullable; every setting record MUST have a section assigned.
- **FR-004**: All existing setting records MUST be assigned to an appropriate section as part of the migration.
- **FR-005**: The admin settings view MUST display one tab per section that has at least one setting.
- **FR-006**: Each section tab MUST show only the settings that belong to that section.
- **FR-007**: Each section tab MUST allow the admin to edit and save settings independently of other sections.
- **FR-008**: The tab labels MUST be human-readable and consistent across languages supported by the application (Basque and Spanish).
- **FR-009**: The section field MUST be included in the `$fillable` list of the `Setting` model and reflected in the factory.
- **FR-010**: The settings seeder or migration MUST assign each known setting key to its correct section.
- **FR-011**: Settings whose `section` value is outside the allowed set at runtime MUST be reassigned to `general` before tab rendering and save operations.

### Key Entities

- **Setting**: Represents a single application configuration key-value pair. Key attributes: `key` (unique identifier), `value` (content), `section` (grouping category). Supports soft deletes. The new `section` attribute determines which tab the setting appears under.
- **Section**: A logical grouping of related settings (e.g., `front`, `contact_form`, `gallery`). Not a separate database entity — represented as a constrained string value on the `Setting` model. Drives tab generation in the admin panel.

## Success Criteria *(mandatory)*

### Measurable Outcomes

- **SC-001**: An admin can locate and edit any setting within 2 tab clicks from the settings page, without scrolling through unrelated settings.
- **SC-002**: After deployment, 100% of existing setting records have a non-null `section` value; no settings are unreachable via the tabbed interface.
- **SC-003**: The admin settings page renders all section tabs without additional page loads (single-page tab switching).
- **SC-004**: Adding a new section requires only adding a new allowed value and creating setting records; no structural UI changes are needed.

## Assumptions

- The initial set of sections is: `front`, `contact_form`, `gallery`. Additional sections may be added in future iterations.
- The six currently existing settings (`admin_email`, `recaptcha_site_key`, `recaptcha_secret_key`, `legal_checkbox_text_eu`, `legal_checkbox_text_es`, `legal_url`) are all assigned to the `contact_form` section, as they relate to the contact form and legal compliance configuration.
- The admin panel is accessible only to authenticated users with admin privileges; no new authorization layer is needed for this feature.
- Tab order follows alphabetical order (sorted by section name).
- The application supports two locales (Basque and Spanish); tab labels must have translations in both.
- The `gallery` section does not yet have associated settings; its tab will not appear until settings are assigned to it.
- Settings with ambiguous or missing section assignments during migration will be assigned to a "general" section with logging for post-deployment review.

## Clarifications

### Session 2026-04-07

- Q: Should the admin panel remember which tab was last viewed? → A: No; always show first tab (alphabetically) on page load for consistency and simplicity.
- Q: In what order should section tabs appear? → A: Alphabetically by section name for predictability and ease of finding settings.
- Q: What happens if a setting's section cannot be determined during migration? → A: Assign to a "general" default section and log the assignment for post-deployment audit and manual correction if needed.

## Constitution Alignment Checklist

- [x] No implementation details leaked into requirements
- [x] Security/privacy constraints captured where relevant
- [x] Accessibility expectations captured where relevant
- [x] Localization expectations captured where relevant
- [x] All user stories have independent test intent

