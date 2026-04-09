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

- In admin image grids, show EU/ES alt texts and tag as always-visible card metadata (not hover-only), and add stable `data-*` selectors so Feature tests can assert content without relying on Tailwind classes.

- Cuando un test de Livewire cubre filtros por valor válido, añade también un caso de valor inválido que verifique el reset de estado (por ejemplo `activeTag=''`) para cubrir ramas de guard clause y evitar bajadas de cobertura.

- Cuando se fusionan migrations derivadas en una migration base de un proyecto no productivo, verifica con el directorio real que los archivos redundantes se han borrado físicamente; si siguen presentes, los tests con SQLite en memoria seguirán ejecutándolos y fallarán con errores de columna/índice duplicados.

- En layouts Blade del panel, cada `<nav>` debe llevar `aria-label` explícito; al tocar el archivo, revisar de inmediato PROBLEMS para corregir avisos de accesibilidad del bloque editado.
- En este proyecto, antes de editar un componente Livewire, verifica cuál es la implementación realmente montada: el nombre puede resolver a un SFC Volt en `resources/views/components/⚡*.blade.php` aunque exista una vista/clase paralela en `resources/views/livewire/`; editar el archivo equivocado hace perder tiempo y deja la UI sin cambios.
- Si `APP_LOCALE=eu` y faltan `lang/eu/validation.php` u otras traducciones base de Laravel, las validaciones caerán en inglés por `fallback_locale`; cuando aparezcan mensajes de error en inglés en formularios del panel, comprueba primero la existencia del archivo de idioma antes de tocar reglas o componentes.
