<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Quick Checklist

- Work inside Docker only (`docker compose run ...` / `docker compose exec ...`).
- Always run Docker commands as non-root (`--user ${DC_UID:-1000}:${DC_GID:-1000}`) when creating or modifying project files.
- Do not run `php`, `composer`, `npm`, `artisan`, `pint`, or tests directly on host.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.4
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- livewire/flux (FLUXUI_FREE) - v2
- livewire/livewire (LIVEWIRE) - v4
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/dusk (DUSK) - v8
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- pestphp/pest (PEST) - v4
- phpmd/phpmd (PHPMD) -v2
- phpstan/phpstan (PHPSTAN)  -v2
- phpunit/phpunit (PHPUNIT) - v12
- tailwindcss (TAILWINDCSS) - v4

## Skills Activation

This project has domain-specific skills available. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

- `laravel-best-practices` — Apply this skill whenever writing, reviewing, or refactoring Laravel PHP code. This includes creating or modifying controllers, models, migrations, form requests, policies, jobs, scheduled commands, service classes, and Eloquent queries. Triggers for N+1 and query performance issues, caching strategies, authorization and security patterns, validation, error handling, queue and job configuration, route definitions, and architectural decisions. Also use for Laravel code reviews and refactoring existing Laravel code to follow best practices. Covers any task involving Laravel backend PHP code patterns.
- `fluxui-development` — Use this skill for Flux UI development in Livewire applications only. Trigger when working with <flux:*> components, building or customizing Livewire component UIs, creating forms, modals, tables, or other interactive elements. Covers: flux: components (buttons, inputs, modals, forms, tables, date-pickers, kanban, badges, tooltips, etc.), component composition, Tailwind CSS styling, Heroicons/Lucide icon integration, validation patterns, responsive design, and theming. Do not use for non-Livewire frameworks or non-component styling.
- `livewire-development` — Use for any task or question involving Livewire. Activate if user mentions Livewire, wire: directives, or Livewire-specific concepts like wire:model, wire:click, wire:sort, or islands, invoke this skill. Covers building new components, debugging reactivity issues, real-time form validation, drag-and-drop, loading states, migrating from Livewire 3 to 4, converting component formats (SFC/MFC/class-based), and performance optimization. Do not use for non-Livewire reactive UI (React, Vue, Alpine-only, Inertia.js) or standard Laravel forms without Livewire.
- `pest-testing` — Use this skill for Pest PHP testing in Laravel projects only. Trigger whenever any test is being written, edited, fixed, or refactored — including fixing tests that broke after a code change, adding assertions, converting PHPUnit to Pest, adding datasets, and TDD workflows. Always activate when the user asks how to write something in Pest, mentions test files or directories (tests/Feature, tests/Unit, tests/Browser), or needs browser testing, smoke testing multiple pages for JS errors, or architecture tests. Covers: it()/expect() syntax, datasets, mocking, browser testing (visit/click/fill), smoke testing, arch(), Livewire component tests, RefreshDatabase, and all Pest 4 features. Do not use for factories, seeders, migrations, controllers, models, or non-test PHP code.
- `dusk-test` — Use this skill when running, fixing, or debugging Browser tests under `tests/Browser/` in this project, especially in Docker. Trigger when Dusk fails with missing ChromeDriver path, missing Chromium binary, or `net::ERR_CONNECTION_REFUSED`. Follow its reproducible workflow: install browser, install matching driver, prepare database with seeds, start in-container app server, run only affected Browser tests, and clean up background processes.
- `lighthouse-frontend-audit` — Use this skill after frontend changes once relevant Dusk tests pass. Trigger on requests mentioning Lighthouse, frontend audit, or performance/accessibility/SEO/best-practices checks. Follow its Docker-first workflow for Chrome + Lighthouse execution, analyze measured results, and iteratively improve proposals until they are implementation-ready and measurable.
- `tailwindcss-development` — Always invoke when the user's message includes 'tailwind' in any form. Also invoke for: building responsive grid layouts (multi-column card grids, product grids), flex/grid page structures (dashboards with sidebars, fixed topbars, mobile-toggle navs), styling UI components (cards, tables, navbars, pricing sections, forms, inputs, badges), adding dark mode variants, fixing spacing or typography, and Tailwind v3/v4 work. The core use case: writing or fixing Tailwind utility classes in HTML templates (Blade, JSX, Vue). Skip for backend PHP logic, database queries, API routes, JavaScript with no HTML/CSS component, CSS file audits, build tool configuration, and vanilla CSS.
- `laravel-specialist` — Build and configure Laravel 10+ applications, including creating Eloquent models and relationships, implementing Sanctum authentication, configuring Horizon queues, designing RESTful APIs with API resources, and building reactive interfaces with Livewire. Use when creating Laravel models, setting up queue workers, implementing Sanctum auth flows, building Livewire components, optimizing Eloquent queries, or writing Pest/PHPUnit tests for Laravel features.
- `php-best-practices` — PHP 8.x modern patterns, PSR standards, and SOLID principles. Use when reviewing PHP code, checking type safety, auditing code quality, or ensuring PHP best practices. Trigger on requests like "review PHP", "check PHP code", "audit PHP", or "PHP best practices".

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- **Docker-first workflow**: Always run project commands inside Docker (`docker compose run ...` / `docker compose exec ...`). Do not run `php`, `composer`, `npm`, `artisan`, `pint`, or tests directly on the host.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.
- **Bilingual UI dedup rule**: If bilingual logic/UI is repeated across multiple partials, encapsulate it in a reusable Blade component to avoid inconsistencies and layout bugs; in tests, prefer stable `data-*` selectors over internal implementation details (for example Alpine variable names).
- **Brand palette source of truth**: Store the active frontend palette in `resources/css/app.css` theme tokens and mirror the same palette in `.github/instructions/frontend.instructions.md`. For new UI changes, prefer these tokens/colors instead of introducing ad-hoc color scales.
- **Query minimization**: Prioritize speed by reducing database round-trips whenever possible. Batch related reads/writes (e.g., one `whereIn`/`upsert` instead of repeated single-key queries) and avoid repeated queries in the same request lifecycle.
- **Clean Blade rule (mandatory)**: Never run database queries inside Blade views (`resources/views/**`). Prepare all view data in routes/controllers/Livewire components/view composers and pass only ready-to-render variables to templates.
- **Strict DRY rule (mandatory)**: Never duplicate business logic or mapping blocks across files/classes. If the same logic appears twice (or would appear a second time), extract it immediately into a shared class/trait/component/helper and replace all existing duplicates in the touched scope. Ship no intentional copy-paste logic.
- **KISS rule (mandatory)**: Always choose the simplest solution that fulfils the requirement. Do not introduce extra layers of abstraction (base classes, traits, service containers, pipelines) unless two or more concrete use cases already exist in the codebase. Prefer a plain method over a class, a named route closure over a controller when the handler is trivial, and a single Eloquent query over a repository pattern. If a simpler alternative exists and passes the same tests, use it.
- **YAGNI rule (mandatory)**: Never build functionality for a hypothetical future requirement. Only implement what is explicitly requested in the current task. Do not add optional parameters, configuration flags, generic abstractions, or extension points "just in case". If a requirement is not in the current spec, leave it out entirely; adding it speculatively creates dead code and increases maintenance burden.
- **SOLID — Single Responsibility (mandatory)**: Each class and each public method must do exactly one thing. Controllers only handle HTTP input/output — no business logic, no DB queries beyond what Eloquent provides. Livewire components only manage UI state and delegate persistence to models or service classes. Migration files only alter schema. If a method needs a second paragraph to describe what it does, split it.
- **SOLID — Open/Closed (mandatory)**: Design classes so that adding a new variant (new locale, new section type, new mail template) requires adding a new class or extending existing configuration — not editing existing class internals. Use polymorphism, strategy objects, or data-driven config (arrays, enums) instead of growing `if/switch` chains. When a `match`/`switch` block would need a new branch for every new case, replace it with a data map or a registry pattern.
- **SOLID — Liskov Substitution (mandatory)**: Any subclass or implementation of an interface must be fully substitutable for its parent without altering behaviour. Never override a method to throw an exception or return a different type than declared. If a subclass cannot honour a parent contract, extract a new interface instead of overriding.
- **SOLID — Interface Segregation (mandatory)**: Keep interfaces narrow and role-specific. Do not add methods to an interface that some implementors will have to leave empty or throw on. Prefer multiple focused interfaces over one fat contract. In Laravel context: keep Form Requests scoped to a single form, keep Policies scoped to a single model.
- **SOLID — Dependency Inversion (mandatory)**: High-level classes (controllers, Livewire components, service classes) must depend on abstractions (interfaces, type-hinted contracts) not on concrete implementations. Inject dependencies through constructor or method injection — never instantiate collaborators with `new` inside business logic. Bind concrete implementations in `AppServiceProvider` when needed.
- **Settings batch access**: For multiple settings keys, prefer explicit in-context queries using `whereIn('key', [...])->get(['key', 'value'])->pluck('value', 'key')` and batch writes with `upsert(...)`; avoid introducing generic model helpers like `getMany`/`setMany` unless explicitly requested.
- **SoftDeletes**: Every Eloquent model in this application uses `SoftDeletes`. When creating a new model, always add `use SoftDeletes;` and add `$table->softDeletes()` to its migration. Never use hard deletes unless explicitly requested.
- **Unit vs Feature tests**: Prefer Unit tests (`tests/Unit/`) for logic that does not require the database: model accessors, pure classes, value objects, static helpers. Only use Feature tests (which receive `RefreshDatabase` automatically via `Pest.php`) when DB, HTTP, or Livewire is genuinely needed. Never add `uses(RefreshDatabase::class)` inside individual test files.
- **Test split policy (general)**: When a Feature test validates both end-to-end flow and internal pure logic, keep only the end-to-end assertions in Feature and move the pure logic checks to Unit tests (mailables, value objects, transformers, helper methods, locale mapping, formatting rules). Apply this split progressively to all test files touched in future changes.
- **Validation testing policy**: When form/component validation rules are deterministic and independent from persistence, extract rules/messages into a reusable class under `app/Validations/` and test them in `tests/Unit/` with `Validator::make(...)`. Keep Feature tests for submission flow, side effects, and integration behaviour.
- **Input hardening policy (future forms)**: For user-controlled free-text fields (contact, settings text, admin notes, etc.), use reusable validation rules under `app/Rules/` (for example, `NoScriptTags`) instead of repeating inline regex rules. Keep Blade escaped output (`{{ }}`), reject unsafe payloads at validation time when applicable, and cover the rule with focused Unit tests plus one integration test in the affected flow.
- **Test readability for settings**: In tests, avoid repeating `Setting::where('key', ...)->value('value')` and ad-hoc settings seeding; use shared Pest helpers (for example `settingValue(...)` and `createSetting(...)`) to keep assertions and setup concise and consistent.
- **Specs sync after code changes**: After any code change that affects documented behaviour, review `.kiro/specs/community-web/` and update `design.md`, plus `requirements.md` or `tasks.md` when affected, so the specs stay aligned with the implemented code before finishing.
- **Spec completion quality gate**: At the end of each spec task set, before updating documentation and before running tests, execute quality checks inside Docker with a non-root user. Minimum required gate: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.
- **`->repeat()` in tests**: Only use `->repeat()` on property-based tests that randomise inputs with `rand()` or `fake()`. Never use on deterministic tests. Keep the count at 2 or below.
- **Post-correction learning rule**: At the end of every correction response, include a short `Aprendizajes:` section with the concrete root cause and how to avoid repeating it, and persist that learning in memory so it can be consulted in the next correction.
- **Correction memory mirroring rule**: Every time content is added or updated in `/memories/correction-workflow.md`, mirror the same content in `.docs/repo-corrections.md` in the repository.
- **Frontend Lighthouse follow-up rule**: After any frontend change, once the relevant Dusk tests pass, run Lighthouse in Chrome, review the report (Performance, Accessibility, Best Practices, SEO), and include concrete improvement proposals based on the measured results.
- **VS Code Problems rule**: Before finalizing any correction, always review the VS Code PROBLEMS panel and fix the warnings/errors found in the files touched by that correction. If there are unrelated pre-existing problems outside the scope, report them explicitly.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `npm run build`, `npm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.
- At the end of each correction response, add a final line starting with `Explicit rule applied:` followed by the concrete rule used for that correction.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- In this workspace (`madaia33`), run Artisan inside Docker using `docker compose run --rm madaia33 php artisan ...`.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.
- To check environment variables, read the `.env` file directly.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.
- When the user asks to run tests with coverage, run them and append one line to `.docs/test_coverage.md` with: date and time, number of tests, execution time, and coverage percentage.
- If coverage drops compared to the previous line in `.docs/test_coverage.md`, warn the user explicitly and include a short analysis of how to improve it.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

=== livewire/core rules ===

# Livewire

- Livewire allow to build dynamic, reactive interfaces in PHP without writing JavaScript.
- You can use Alpine.js for client-side interactions instead of JavaScript frameworks.
- Keep state server-side so the UI reflects it. Validate and authorize in actions as you would in HTTP requests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

</laravel-boost-guidelines>

```
Reduce the number of examples, and re-run the tests, so that it runs faster.
```
