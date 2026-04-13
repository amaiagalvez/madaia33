- After each correction response, include `Aprendizajes:` with root cause + prevention, and save that learning in memory for the next correction.
- Before finishing a correction, check VS Code Problems and fix warnings/errors in touched files.
- If Problems include unrelated pre-existing items, report them explicitly instead of silently ignoring.
- Mirror rule: every new or updated entry in `/memories/correction-workflow.md` must also be written to `.docs/repo-corrections.md`.

- Accessibility: never use `<label for>` with `div[contenteditable]`; use `aria-labelledby` (or a real form control id) to avoid browser autofill/a11y warnings.

- Pest in this repo: prefer `test()->actingAs($user)` in closures when static analysis flags `$this->actingAs(...)` and avoid unsupported global helpers.

- For bilingual settings UX, place the field title on the same row as language tabs and group each language-specific editor inside a bordered card to reduce visual clutter.

- When a section-specific UI change affects parent form layout, check for unmatched closing tags in included Blade partials because they can hide the submit button.

- To avoid visual spacing jumps when switching language tabs with rich-text content, use a fixed editor height plus internal scroll (`h-*` + `overflow-y-auto`).

- If language-tab spacing still jumps after fixed editor heights, also set fixed height + `overflow-hidden` on the whole language panel and reserve error-space equally in each tab.

- When bilingual rich-text fields repeat across sections, extract an anonymous Blade component and assert a stable `data-*` marker in tests instead of internal Alpine variable names.
- In isolated Blade component tests, `$errors` may be undefined; either pass an empty `ViewErrorBag` in the test or guard `@error` blocks with `@isset($errors)` so the component can render outside a full HTTP request.


- Promote recurring root-cause/prevention insights into AGENTS.md conventions so they are enforced in future coding sessions.

- Rich-text multilingual tabs can still shift due to stored HTML default margins (`<p>`, `<div>`). Normalize inner margins in the editor container and use fixed grid rows for pane layout.

- If EUS/CAS pane spacing still differs, normalize stored rich-text HTML by trimming leading/trailing empty `<p>/<div><br></div>` blocks before rendering.

- If a corrective change proves ineffective, revert only that last experimental layer instead of stacking more speculative fixes on top.

- For persistent UI spacing disputes, add a Browser/Dusk test that measures stable element geometry via `data-*` selectors instead of relying only on screenshots or manual comparison.

- In Docker coverage runs, set `XDEBUG_MODE=coverage` explicitly; otherwise `php artisan test --coverage` can fail even when Xdebug is installed.
- For Livewire components that call `validate($customRules)`, avoid forcing impossible branch tests via invalid `activeSection` values because they can trigger `MissingRulesException`; prefer deterministic tests on reachable behavior and direct method-level coverage where needed.

- For locale-prefixed routes, set URL defaults in `tests/TestCase::setUp()` (not only in `tests/Pest.php`) so `route()` calls inside views/Livewire rendering always receive `locale` during Feature tests.
- If only one public route needs locale-specific slugs, keep route names stable and localize via route parameters + URL defaults; this avoids mass refactors while preserving canonical URLs and SEO consistency.
- When frontend copy blocks are intentionally removed, update Feature tests to assert stable structural markers (`data-*`, layout classes) instead of deleted translation strings; this prevents brittle regressions after UI copy refactors.

- For ownership fixes, run `chown` from a Docker root container and then verify with a root-container `find` count, so host-side permission denials do not hide remaining root-owned paths.

- For asymmetric split layouts on home pages, make the parent grid `lg:items-stretch` and the side panel `lg:flex lg:h-full`; this keeps the right column truly full-height while preserving mobile stacking.
- When adding new locale-aware Pest tests in this repo, always append `->with('supported_locales')` for closures with a `$locale` argument; otherwise tests fail with `DatasetMissing`.
- In `routes/web.php`, avoid relying on outer variables inside `Route::prefix(...)->group(function () { ... })` unless explicitly imported with `use (...)`; prefer controller actions for non-trivial page data preparation.

- If a Lightbox/overlay opens shifted (too low/high), teleport it to `body` (`x-teleport="body"`) so `fixed` positioning is viewport-based and not affected by ancestor layout/transform contexts.

- In bilingual rich-text tabs, avoid `x-if` for language panes because it destroys/recreates editors and can drop unsynced content on tab switch; prefer `x-show` (or explicit buffer syncing) to preserve in-progress text.

- In docker-compose.yml, if repeated service blocks make partial patches unstable, rewrite the whole file and validate with docker compose config --quiet to catch misplaced lines early.

- Traducción de claves en tests: Usar notación con punto para las claves de traducción (`admin.test_email.button` no `admin.test_email_button`). Flux UI y otros componentes aceptan directamente `__('key.with.dots')`.

- Modales en Livewire: No usar `flux:modal wire:model="property"` porque Flux UI no maneja bien el binding con propiedades Livewire. En su lugar, usar `<dialog open>` HTML nativo con condición `@if ($propertyBoolean)` y botones `wire:click`, patrón validado en admin-notice-manager, admin-message-inbox.

- In admin image grids, show EU/ES alt texts and tag as always-visible card metadata (not hover-only), and add stable `data-*` selectors so Feature tests can assert content without relying on Tailwind classes.

- Cuando un test de Livewire cubre filtros por valor válido, añade también un caso de valor inválido que verifique el reset de estado (por ejemplo `activeTag=''`) para cubrir ramas de guard clause y evitar bajadas de cobertura.

- Cuando se fusionan migrations derivadas en una migration base de un proyecto no productivo, verifica con el directorio real que los archivos redundantes se han borrado físicamente; si siguen presentes, los tests con SQLite en memoria seguirán ejecutándolos y fallarán con errores de columna/índice duplicados.

- En layouts Blade del panel, cada `<nav>` debe llevar `aria-label` explícito; al tocar el archivo, revisar de inmediato PROBLEMS para corregir avisos de accesibilidad del bloque editado.
- En este proyecto, antes de editar un componente Livewire, verifica cuál es la implementación realmente montada: el nombre puede resolver a un SFC Volt en `resources/views/components/⚡*.blade.php` aunque exista una vista/clase paralela en `resources/views/livewire/`; editar el archivo equivocado hace perder tiempo y deja la UI sin cambios.
- Si `APP_LOCALE=eu` y faltan `lang/eu/validation.php` u otras traducciones base de Laravel, las validaciones caerán en inglés por `fallback_locale`; cuando aparezcan mensajes de error en inglés en formularios del panel, comprueba primero la existencia del archivo de idioma antes de tocar reglas o componentes.
- En la bandeja de mensajes tipo tabla, mover una columna en <thead> sin mover su celda equivalente en <tbody> rompe la percepción visual aunque el código parezca correcto; siempre validar cabecera-celdas por orden real y revisar con captura/UI antes de cerrar.
- En este proyecto, para nuevas claves de settings no hace falta crear migrations si la tabla `settings` ya es key/value; el cambio real está en sección/validación/UI/seeders. En correos Laravel, el remitente configurable debe declararse en `Envelope` con `Address` y conviene leer `admin_email`, `from_address` y textos legales en lote para evitar consultas repetidas.
- Si tras crear una clase nueva con Artisan los tests siguen resolviendo una versión antigua o incompleta, ejecuta `composer dump-autoload` dentro de Docker antes de seguir depurando. En tests Pest, evita declarar clases auxiliares top-level dentro del archivo porque Composer avisará por PSR-4; usa una excepción anónima o una clase real en su ruta.
- Mermaid parse hardening: avoid subgraph labels with mixed punctuation/diacritics when portability matters; prefer quoted ASCII-safe labels (`subgraph ID["Label"]`) and keep symbols like `/` or `⚡` inside node text, not structural headers.

- If full-page Livewire routes fail with `View [app] not found` after layout refactors, verify `config/livewire.php` `component_layout` points to an existing namespaced view (in this repo `layouts::shared.app`).
- For opaque Feature test 500s that hide stack traces, inspect dated Laravel logs in `storage/logs/laravel-YYYY-MM-DD.log`; the exception there is usually faster than rerunning tests blindly.

- For duplicated Blade components already referenced across views/tests, consolidate with a new shared base component and keep old component names as thin wrappers to preserve compatibility and stable `data-*` selectors.
- Before switching row-by-row inserts to bulk insert, verify the table schema for timestamps (`created_at`/`updated_at`); include only real columns to avoid SQL errors in tests.
- Before deleting a duplicate Livewire/Blade implementation, search actual usages first; keep only the mounted path and remove the orphan to avoid maintaining divergent logic.
- If a Pest test uses factories/DB tables, place it in tests/Feature (or add RefreshDatabase explicitly); tests in tests/Unit here run without DB migrations and can fail with "no such table".
- In Dusk/Browser tests with seeded data, avoid hardcoded unique keys (e.g., location `code`); use factory-generated unique values or explicitly non-conflicting values to prevent intermittent `UNIQUE constraint failed` errors.
- If `is_active` is added to users for deactivation, enforce it in Fortify authentication (`authenticateUsing`) and add a Feature test; toggling the flag alone does not block login.
- To prevent duplicate active ownership assignments, combine transactional `lockForUpdate()` checks with a DB-level uniqueness strategy for active rows.
- When extracting a shared Blade table component, avoid wrapping an already-existing <table> with another <table>; pass table classes via component props and keep a single table element to prevent invalid markup.
- To truly standardize table UI from a base view, align wrapper and inner table semantics together (`thead`, `tbody`, `th`, `td` classes); changing only the container component leaves visual drift.
- In owners list UX, keep all filters in one horizontal row and use horizontal overflow when needed; reserve vertical space for table rows and pair it with pagination to keep dense admin listings usable.
- For configurable owner-onboarding emails, keep subject/body in settings by locale and replace a stable placeholder like ##info## server-side with assignment data; cover both settings persistence and email rendering in Feature tests.
- In admin forms, avoid mixed primary button palettes (Flux default + brand). Reuse a shared brand-primary style/component so create/save actions keep a consistent visual hierarchy.
- Browser test files under tests/Browser in this repo may fail with "no such table" if run as plain php artisan test; use the dedicated Dusk Docker workflow (serve + migrate/seed + selenium APP_URL) for reliable execution.
- If a UI action is explicitly removed by requirement (e.g., owner deactivation from detail page), remove both the button and the Livewire handler/dependency injection to avoid dead callable paths.
- When asserting owners index content in Feature tests, remember default filter is active-only; seed at least one active assignment or switch filter state explicitly to avoid false negatives.
- For admin list create flows, replacing inline forms with a fixed right-side panel (`fixed inset-0` + backdrop + `right-0` container) preserves Livewire state while clearly indicating context switch without changing backend logic.
- In right-side create panels, avoid mixing tight 12-column spans with inline action buttons in the same row; move actions to a separate footer row and use simpler responsive grids to prevent overlap/clipping.
- For admin detail pages with wide tables, keep the page header container at full width (`w-full`) and reduce vertical padding to avoid visual height bloat; style breadcrumbs as a bordered inline bar to keep alignment and hierarchy consistent.
- Keep owner user activation state synchronized from assignment actions (assign/unassign), and delegate Livewire close flows to those actions to avoid duplicated lifecycle side effects.
- For decimal fields that must accept comma input, use text inputs with `inputmode="decimal"` and normalize `,` to `.` in Livewire before numeric validation and persistence.
- In admin listings, keep edit/delete actions visually consistent with the notice-manager icon-button pattern (`rounded-full` action buttons with matching hover semantics) to prevent UI drift between tables.
- For large Mermaid schema diagrams, split documentation into domain-focused diagrams plus a small overview graph; a single monolithic ERD becomes unreadable in chat/UI even if syntax is valid.
- For large Mermaid view-architecture diagrams, split into focused maps (public, admin, auth/settings, shared partials) plus one high-level overview to keep labels readable at normal zoom.
- Fortify with `lowercase_usernames=true` lowercases the login identifier before `authenticateUsing`; if DNI login is allowed, normalize email and DNI separately (`Str::lower` for email, `Str::upper` for DNI) or owner-based DNI auth will fail silently in tests and production.
- Password reset email subject localization in Laravel 13 uses the exact JSON key `Reset your password` (not `Reset Password Notification`); add that key per locale and assert `MailMessage->subject` in Feature tests to avoid partially localized mails.

- In voting flows, enforce one-choice-per-ballot invariants at DB level (unique key on ballot identifier) in addition to service-level validation.
- In Livewire admin listings, avoid per-row census/count queries; pre-aggregate with one query or a single in-memory map to prevent N+1 regressions.

- If Docker logs show `Xdebug: [Step Debug] Could not connect to debugging client` while requests return 200, treat it as debug-noise (not app failure); set `XDEBUG_START_WITH_REQUEST=trigger` to avoid log spam and keep on-demand debugging.

- In development seeders, do not rely only on nested factory side effects for critical auth entities; create Users explicitly and then create Owners linked by `user_id`, and test `User::whereHas('owner')` to prevent missing login accounts for seeded owners.

- In this repo, if static analysis flags `Seeder::call(...)` class resolution inside `database/seeders`, use a fully-qualified seeder class string (e.g., `\\Database\\Seeders\\VotingSeeder::class`) to clear IDE warnings.
- In this repo, `php artisan make:livewire ... --mfc` can scaffold into `resources/views/components/...` unexpectedly; verify the generated path against `config/livewire.php` before continuing and delete stray scaffold files immediately if they land in the wrong namespace.
- If `docker-compose` rejects boolean environment values in this repo, use `docker compose` (v2) for maintenance commands; for root-owned leftovers, run `docker compose run --rm --user root madaia33 chown ...` and then re-verify ownership with `ls -l`.
- If an admin route allows access but the page still returns 403, verify the mounted Livewire component `mount()`/action guards match route middleware; route-level role checks and component-level checks can drift and block valid superadmin sessions.
- When adding new variables used inside a DB::transaction closure, include them explicitly in the closure `use (...)`; otherwise runtime `Undefined variable` errors can pass static checks and break critical vote flows.
- In Livewire action methods that trigger redirects, avoid strict `RedirectResponse` return types; use `void` + `$this->redirectRoute(...)` (or Livewire redirect helpers) to prevent runtime type errors from Livewire Redirector.
- For delegated-role users without owner profile, do not force `resolveOwner()` on mount; allow entering the front page in selector mode and require explicit delegated owner selection before enabling vote actions.
- In non-root Docker coverage runs, pass `--cache-directory=/tmp/phpunit-cache` to avoid vendor write-permission failures (`pest/.temp/code-coverage`).
- If a Blade layout is rendered via namespaced component syntax (e.g., `x-layouts::...`), register view composers for the namespaced key too (e.g., `layouts::...`) and keep a safe default (`$var ?? false`) in the layout to prevent undefined-variable crashes.
- If admin Feature/Livewire tests start failing with 403 or “Invalid Livewire snapshot structure”, first verify the authenticated test user has the required admin role(s); use a shared helper (e.g., `adminUser()`) to avoid role-less `User::factory()->create()` in admin test suites.
- In Dusk admin tests, avoid `User::factory()->create()` for authenticated admin flows; login with the seeded admin account (or explicitly assign `SUPER_ADMIN`) to prevent false failures from 403 responses before DOM assertions.
- In `admin-message-inbox`, default `unread` filter can hide a message immediately after `openMessage()` marks it as read; in tests switch filter to `all` before asserting expanded detail content.
- If inbox default filter changes (e.g., `unread` -> `all`), update both behavior assertions and test naming to avoid false regressions from stale expectations.
- For broad test-comment translation tasks, first run an `rg` inventory, then apply controlled phrase replacements by file and finish with `php artisan test --compact` on touched files to guarantee behavior remains unchanged.
- When adding agent-policy rules in `AGENTS.md`, encode the requirement with explicit agent scope + mandatory wording (e.g., `amalurra priority rule (mandatory)`) so the instruction is unambiguous and easy to enforce.
- If the user asks to add rules in specific agent files, update `.github/agents/*.agent.md` directly (not only `AGENTS.md`) and keep the rule text symmetric across the requested agents.
- If a migration alters ENUM constraints, make it database-driver aware (MySQL/SQLite). In SQLite tests, rebuild table with updated CHECK constraint or equivalent compatible path to avoid `near "MODIFY"` failures.
- In this repo, user-model observer/hooks for mirrored owner fields may not trigger reliably in all test flows; keep synchronization deterministic by calling an explicit `User::syncOwnerIdentity()` right after user profile/admin saves, and cover it with focused Feature tests.
- In indentation normalization scripts, preserve PHPDoc/JSDoc block indentation (`/** ... */`) to avoid misaligned comment stars; skip those blocks while normalizing code indentation.
- For `HasFactory` generic warnings, use `@use HasFactory<FactoryClass>` with a short imported factory class (never fully-qualified in PHPDoc), ensure the factory file exists, and then run Pint on touched files to satisfy both PHPStan and style rules.
- If PHPStan reports non-covariant Collection return mismatches for mapped array-shapes, prefer returning plain arrays (`->values()->all()`) from private helpers instead of tightening Collection TValue shapes.
- After large `apply_patch` refactors in long Livewire classes, re-run `php -l` on each touched file immediately; duplicate method fragments or stray braces can survive a partial patch and are faster to catch with syntax checks before Pint/test runs.
- In Dusk tests for locale-aware auth redirects, assert a stable page selector such as `[data-test="login-button"]` instead of relying only on redirect paths or translated text; redirect chains and removed routes can make path/text waits flaky even when the correct private page is rendered.
- For location-chief assignment in admin locations, derive selectable owners from active property assignments in the same location and centralize chief transfer + COMMUNITY_ADMIN sync in one transactional action to prevent stale role/location links.
- For admin list style restorations, reuse the established rounded-pill filter/button patterns from `admin/message-inbox` and keep location row actions as rounded icon buttons (`bars-3` for properties, `pencil-square` for edit) to avoid UI drift.
- In admin message inbox listings, keep sortable header hover and delete action colors aligned with the notice-manager icon-button palette (`#793d3d` hover, brand-colored action icons) to avoid local visual drift.
- For table cells that change state (like read/unread), use text-link styling with brand hover/underline instead of pill badges when following the `Enlaces en las tablas` admin pattern.
- For admin table links that mutate state (`wire:click`), add `wire:confirm` with locale-aware copy so style-guide requirements ('confirm before change') are enforced in behavior, not only visuals.
- When matching an admin table action to an existing pattern (e.g., notices published status), mirror both utility classes and confirmation tone/text so interaction parity is complete.
- If a table action must match notices' published-column UX, replace `wire:confirm` with a dedicated modal flow (`confirmX`, `doX`, `cancelX`) to keep behavior and visuals identical.
- In admin locations listing, when showing COMMUNITY_ADMIN label, source the display name from the linked owner profile (owners.coprop1_name) tied to active assignment + managed location; this avoids mismatches where users.name is not the expected business-facing label.
- To centralize repeated bars row-actions in admin tables, keep x-admin.table-row-actions slot-compatible and add optional props (barsHref/barsTitle) so existing usages remain unchanged while removing inline duplicate markup.
- For location delete actions, block deletion when linked properties exist and return a user-facing flash error; this avoids FK constraint failures and matches a safe admin UX.
- Before adding tests for edge cases on assignment flows, verify DB unique constraints first; avoid writing impossible scenarios (e.g., two active assignments for the same property) and align tests with real invariants.
- If Flux inputs inside owner side-panels render washed-out/white, enforce scoped control colors under `[data-section="owner-create-form"]` and `[data-section="owner-edit-form"]` in `resources/css/app.css`, then rebuild assets with the Node service (`madaia33-npm`).
- When converting repeated table markup to a shared wrapper component, forward `$attributes` on the wrapper root; otherwise `data-*` selectors used by tests and JS hooks disappear silently.
- For long audit/history sections inside admin forms, prefer a collapsed `details` block with a fixed-height internal scroll area so the form stays scannable while preserving access to the latest entries.
- When reopening a closed property assignment (`end_date` -> null), run the same active-owner conflict validation as create flow inside a transaction (`lockForUpdate` + DB unique guard) to avoid duplicate active ownership and return a user-facing ValidationException.
- If owner inline date fields overlap after table/form refactors, verify Blade tag closure order around the expanded row (`@if/@forelse` blocks + `tbody/table` wrappers) before tweaking CSS; malformed markup can break grid layout. Also ensure `lang/es/validation.php` exists, otherwise ES locale falls back to English validation messages (e.g., unique email).
- For closed property assignments, keep `admin_validated` and `owner_validated` immutable server-side even when reopening (`end_date` -> null); UI disabled states alone are insufficient because Livewire payloads can be tampered with.
- In owner create-assignment rows, avoid mixing `md:grid-cols-2` with `xl:grid-cols-12`; use one consistent 12-col grid from `md` and explicit label color classes so date fields and headers stay readable without crowding.
- If owner create-assignment date labels still look hidden after color tweaks, replace Flux date fields with `x-admin.form-date-input` and use `grid-cols-1 sm:grid-cols-2 lg:grid-cols-12` so labels and errors keep reserved space at medium widths.
- If Blade layout fixes are correct but UI still appears unchanged, clear Laravel caches with `php artisan optimize:clear` in Docker before further CSS churn; stale compiled views can mask successful fixes.
- If a page-enter animation keeps a non-none transform at rest, fixed dialogs can render behind later siblings (like the footer); end the animation with transform: none to avoid persistent stacking-context overlap.
- En modales Livewire reutilizados por varias acciones (por ejemplo censo/votantes), cada método que carga filas debe hidratar todas las claves que la vista Blade consume; si la columna muestra la opción votada, cargar `selections.option` y mapear sus etiquetas en lugar de dejar placeholders vacíos.
- Si una tabla histórica de perfil debe mostrar la opción votada, no reutilices `voted_at` por inercia: carga `VotingBallot->selections.option` en el controlador y ajusta también la pestaña/ruta del test para validar la vista donde realmente se renderiza esa columna.
- Si el label de una opción de voto contiene día y hora (por ejemplo `Viernes a las 19:30`), en vistas resumen como el censo conviene mostrar una versión recortada del label sin sufijo horario para que no parezca la fecha del voto emitido.
- Si censo y "Ver votantes" comparten la misma columna de voto pero uno muestra nombres neutros de opción, aplica el mismo formateo en ambos cargadores (`openCensus` y `openVoters`) para evitar discrepancias visibles entre modales del panel.
- Para modales históricos de votaciones, no uses `activeAssignments` actuales directamente: calcula elegibilidad y porcentajes con un snapshot en `starts_at` (inicio de la votación) y, si hace falta reutilizar la vista, hidrata esa instantánea en la relación consumida por el modal.
- En admin votings, "Ver censo" y "Ver votantes" reutilizan el mismo bloque `showOwnersModal`; para ajustes de ancho/estructura del diálogo basta tocar una sola vez el contenedor compartido (`max-w-*`) para mantener consistencia entre ambos flujos.
- En tablas inline de propietarias, si ya se carga `property.location`, los porcentajes `community_pct` y `location_pct` deben renderizarse directamente en Blade con el mismo formato visible que validan los Feature tests (`1,25%`, `2,50%`) en lugar de añadir lógica extra al componente.
- En `openVoters`, construir `ownersModalRows` desde `VotingBallot` (votos emitidos) y no desde todo el censo elegible; así el modal "Ver votantes" no muestra propietarias que no votaron.
- Si dos modales Livewire comparten dataset (delegado/presencial), añade el `community_pct` en la capa de servicio que genera las filas (`ownersWithPendingDelegations`) para propagar el cambio en ambos sin duplicar lógica en Blade.
- Si una migración se convierte en no-op antes de producción, elimina también imports de Schema/Blueprint no usados y valida con un test funcional del flujo afectado para evitar deuda silenciosa.
- En restricciones de borrado de usuarias, aplicar guard doble en backend: bloquear si el owner asociado tiene cualquier voto emitido o cualquier asignación histórica (activa o cerrada), y mostrar un flash de error explícito en la UI.
- En filtros nuevos de listados Livewire, añade siempre `updating<Filter>() { resetPage(); }` para evitar páginas vacías cuando cambia el criterio con paginación activa.
- If Docker quality/lint depends on git dirty detection, `vendor/bin/pint --dirty` may skip changed files under dubious ownership; run Pint on explicit file paths and then re-run phpstan/tests.
- After moving methods into traits, re-check phpdoc type names inside the host class (`CarbonInterface`, etc.); missing leading namespace can create fake classes like `App\\Livewire\\Admin\\CarbonInterface` in PHPStan.
- In profile flows, keep controller/view data types aligned: if Blade uses `->count()`/`->isEmpty()`, return `Collection`; if returning arrays, use `count(...)`/`=== []` in Blade to avoid runtime errors for non-owner users.
- In Dusk tests, avoid brittle selectors tied to replaced form controls (e.g., old select IDs); target stable `data-*` markers from shared components and prefer DOM-presence waits for Flux dropdown items over strict visibility waits.
- Profile Mis Votaciones tests: when asserting tab-specific voting content, open `?tab=votings`; default tab resolution can switch to owner and hide rows, causing false negatives.
- In Dusk link assertions, prefer `endsWith('/locale/path')` over exact href equality because test env often renders absolute URLs (`http://dusk-app:8000/...`) and exact-path checks become flaky.
- Rol-guard berriak sartzean, Feature test zaharrek autentifikatutako erabiltzaile arruntak erabiltzen badituzte, helper egonkorra (`adminUser()`) erabiliz eguneratu; bestela baimen-check berriek test legitimoak apurtzen dituzte.
- In Dusk flows, verify selectors against the current Blade markup before asserting close/open actions; stale `data-*` hooks (e.g., missing modal close marker) cause false test failures unrelated to business logic.
- In `PublicVotings`, avoid leaving `canCastVotes` implicitly true after `resolveOwner()`: set it from `user->canVoteInVotings()` in default owner mode so DELEGATED/ADMIN users without `PROPERTY_OWNER` cannot cast direct votes.
- For admin visibility rules on scoped lists (notices/votings), enforce the same role filter directly in the listing query (`whereDoesntHave` / `whereHas`) and cover it with one positive+negative Feature assertion to prevent silent data leaks.
- When backend forbids admin actions (e.g., create/edit/delete locations), also hide corresponding row-action buttons in Blade via explicit permission flags from the Livewire component; test both UI absence and forbidden action calls.
- For read-only admin roles in location modules, enforce one shared permission flag for both list and detail property CRUD (`canManagePropertyCrud`) so "create/edit" buttons and Livewire action methods stay aligned and cannot be triggered directly.
- In front votings permissions, if a role must access the page but not cast votes (e.g., GENERAL_ADMIN without PROPERTY_OWNER), allow mount access explicitly and force `canCastVotes=false`; test both visibility and blocked vote action.
- If an admin section role policy is tightened at route level (e.g., users index superadmin-only), mirror the same rule in component/service guards (`canManageUsers`) and update existing Feature tests from old role fixtures to avoid hidden bypasses or stale expectations.
- For COMMUNITY_ADMIN list scoping, apply explicit `whereHas` on managed location IDs and return empty results when no managed locations exist; cover with one positive (managed) and one negative (other/global) assertion in Feature tests.
- If a shared bilingual rich-text Blade component uses `@input="sync(...)"` or toolbar actions, define `sync/format/link` inside the same component `x-data`; otherwise Livewire fields may stay empty on save and block create flows.
- For contact message user-linking across mixed migration states, keep the historical add-column migration and make it duplicate-tolerant (catch duplicate column on fresh DBs) while ensuring runtime code can persist `user_id` in both front contact and profile modal flows.
- If `contact_messages.user_id` exists in schema/fillable, include `user_id` explicitly in every `ContactMessage::create(...)` path (public form and profile modal); otherwise profile history tabs filtered by user will appear empty.
- In Feature POST tests to locale-prefixed form routes (`profile.terms.accept.*`), include a CSRF token (`withSession('_token')` + payload `_token`) to avoid false 419 failures unrelated to business logic.
- In bilingual `contenteditable` mini-editors, avoid caret jumps by isolating the editor DOM from Livewire diffing (`wire:ignore`) and syncing content with a small debounce on input/blur instead of immediate per-keystroke patching.
- PHPStan generic covariance can still fail when a method declares `Collection<shape>` and one branch returns bare `collect()`; prevent this by returning a typed array (`->values()->all()`) from the helper and collecting only at the presentation boundary when Blade needs collection helpers.
- For PHPMD complexity limits in controllers, keep the action method linear and move scope normalization/predicate checks into small private helpers; this usually lowers both Cyclomatic and NPath without behavior changes.
- In front-votings Dusk flows, delegated/in-person action clicks can be intercepted by the terms modal; accept `[data-votings-terms-accept-button]` and wait until `[data-votings-terms-modal]` disappears before clicking vote action buttons.
- In Livewire create forms with optional numeric inputs (like manual `id`), normalize empty values before casting; otherwise `null`/empty can become `0` and trigger unintended validation or persistence paths.
- In PHPStan fixes for mapped Eloquent collections, avoid ambiguous callback/value types: add explicit collection/array-shape PHPDoc and avoid nullsafe/date method calls on attributes inferred as strings by normalizing with `blank(...)` + `Carbon::parse(...)`.
- If focused `phpstan analyse <files>` reports `unused` members in a Livewire class that are consumed from a trait, verify with a full-project analyse before refactoring; file-scoped runs can produce misleading dead-code signals.
- For PHPMD `ExcessiveMethodLength` in Livewire/services, keep behavior unchanged and extract setup/mapping chunks into small private helpers; then validate with the same Docker `phpmd` command used by quality.
- In idempotent dev seeders, never `return` just because no unassigned property exists; for role-critical demo users first reuse their existing active assignment, then allocate free property, then create a fallback property so Feature assertions stay deterministic.
- In Tailwind v4 CSS, `@apply` cannot contain standalone `!` tokens; remove them (or use valid important utility syntax) because Vite build fails with `Cannot apply unknown utility class '!'`.
- For branding logos, avoid hardcoded `asset('storage/madaia33/madaia33.png')`; always render `$publicLogoUrl` resolved from `front_logo_image_path` in `BrandingSettingsComposer`, and cover it with a Feature test that sets `front_logo_image_path`.
- When removing hardcoded Blade fallbacks in shared layouts, use a safe null-coalescing access (`$var ?? ''`) unless the composer binding is guaranteed for every render path; otherwise Feature tests can fail with undefined-variable 500s.
- For Blade anonymous layouts rendered as `x-layouts::...`, do not assume the layout itself receives branding composer data; route admin/public logos through a child component with its own composer (for example `x-front.public-brand-link`) and cover the admin dashboard with a Feature test.
- For bilingual settings fields in `admin.settings_form`, define both root and language-specific keys (`*_eu`, `*_es`) in both `lang/eu/admin.php` and `lang/es/admin.php`; missing one variant causes visible fallback key strings in UI.
