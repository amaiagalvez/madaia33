---
description: Translate feature spec and related documents to Spanish, keeping translations in sync with source.
---

## Task

Find the most recently created or modified feature directory in `.github/specs/*/`.

1. **Translate every generated spec artifact in the feature directory**.

Treat these English source files as translation candidates when they exist:

- `spec.md`
- `plan.md`
- `research.md`
- `data-model.md`
- `quickstart.md`
- `tasks.md`
- Any `.md` file inside `contracts/`
- Any `.md` file inside `checklists/` that does **not** already end with `_es.md`

2. **Output naming rule**:

- For each `name.md`, create or update `name_es.md` in the same folder.
- Examples:
  - `spec.md` -> `spec_es.md`
  - `plan.md` -> `plan_es.md`
  - `contracts/admin-settings-sections.md` -> `contracts/admin-settings-sections_es.md`
  - `checklists/requirements.md` -> `checklists/requirements_es.md`

3. **Synchronization behavior (mandatory)**:

- If the Spanish file already exists, compare it with the current English source and overwrite it when the source changed.
- Do not skip files because a previous `_es` file exists.
- Keep all translated files synchronized with their English source in every run.

4. **Translation rules**:

- Keep headings, markdown structure, checkboxes, links, tables, and frontmatter intact.
- Keep code identifiers, key names, enum values and requirement IDs untranslated (e.g., `front`, `contact_form`, `gallery`, `FR-001`, `SC-001`).
- Translate prose, explanations, notes, labels, and narrative text into natural Spanish.
- In `spec_es.md`, change `**Status**: Draft` to `**Estado**: Borrador`.

5. **Confirmation output**:

- Report all files created or updated in this run, one path per line.
