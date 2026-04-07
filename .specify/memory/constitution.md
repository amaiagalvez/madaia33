<!--
Sync Impact Report
- Version change: 0.0.0-template -> 1.0.0
- Modified principles:
	- [PRINCIPLE_1_NAME] -> I. Docker-First Execution
	- [PRINCIPLE_2_NAME] -> II. Laravel-First Architecture
	- [PRINCIPLE_3_NAME] -> III. Test and Quality Gates (Non-Negotiable)
	- [PRINCIPLE_4_NAME] -> IV. Data Safety and Query Efficiency
	- [PRINCIPLE_5_NAME] -> V. Spec Traceability and Sync
- Added sections:
	- Technical Baseline and Constraints
	- Workflow and Delivery Rules
- Removed sections:
	- None
- Templates requiring updates:
	- ✅ updated: .specify/templates/plan-template.md
	- ✅ updated: .specify/templates/spec-template.md
	- ✅ updated: .specify/templates/tasks-template.md
	- ✅ no files found: .specify/templates/commands/*.md
	- ✅ updated: .docs/spec-kit-guia-vscode.md
- Follow-up TODOs:
	- None
-->

# madaia33 Constitution

## Core Principles

### I. Docker-First Execution
All project commands MUST run inside Docker containers. Running `php`, `composer`,
`npm`, `artisan`, `pint`, or tests directly on host is not allowed. This keeps
runtime behavior and dependencies consistent across contributors.

### II. Laravel-First Architecture
Implementation MUST follow Laravel conventions before custom abstractions. New code
MUST reuse existing project structure and components, and MUST avoid introducing new
top-level architectural patterns without explicit approval.

### III. Test and Quality Gates (Non-Negotiable)
Every change MUST be validated with automated tests scoped to impacted behavior.
Before completion, code MUST pass formatting and project quality checks. Coverage runs
MUST be included when required by the task plan.

### IV. Data Safety and Query Efficiency
All Eloquent models MUST use `SoftDeletes` unless an explicit exception is approved.
Database access MUST minimize round-trips, avoid N+1 query patterns, and select only
required fields. Batch operations SHOULD be preferred for settings and similar
key-based writes.

### V. Spec Traceability and Sync
Work MUST follow the Spec Kit lifecycle: constitution -> specify -> clarify -> plan ->
tasks -> implement. When behavior changes, related specification artifacts MUST be
updated to preserve alignment between requirements, plan, and implementation.

## Technical Baseline and Constraints

- Backend stack: Laravel 13, PHP 8.4, Fortify, Livewire 4, Flux UI v2.
- Frontend stack: Blade + Tailwind CSS v4 + Alpine.js for lightweight interactions.
- Testing stack: Pest 4 (Unit/Feature), Dusk 8 (Browser) when E2E is affected.
- Static quality tools: Pint, Larastan/PHPStan, PHPMD.
- Localization baseline: Euskera (primary) and Spanish.
- Security baseline: validate user input, prefer reusable rules, and keep escaped output
	in rendered views.

## Workflow and Delivery Rules

1. Create or update specs before implementation for feature work.
2. Keep tasks independently testable and ordered by dependency.
3. Run quality gate in Docker before final tests:
	 `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.
4. Run focused tests first, then broader verification if needed.
5. For public UI changes, include browser-level verification and Dusk when applicable.
6. Mark tasks done only after validation passes for that task scope.

## Governance

This constitution overrides local workflow preferences when conflicts appear.

- Amendment process:
	- Propose change with rationale and impacted principles.
	- Update dependent templates and guidance in the same change set.
	- Record change in the Sync Impact Report header.
- Versioning policy:
	- MAJOR: Principle removal or incompatible governance change.
	- MINOR: New principle/section or materially expanded rule.
	- PATCH: Clarifications, wording, or non-semantic cleanup.
- Compliance review expectations:
	- Each spec workflow phase MUST pass constitution checks before moving forward.
	- Violations MUST be documented with justification and explicit approval.

**Version**: 1.0.0 | **Ratified**: 2026-04-07 | **Last Amended**: 2026-04-07
