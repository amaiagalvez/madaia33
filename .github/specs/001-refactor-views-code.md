## Objetivo

Detectar y reducir código duplicado en `resources/views` mediante extracción de componentes Blade reutilizables, manteniendo comportamiento funcional y cobertura de pruebas.

## Alcance

Incluido:
- Refactor de duplicación estructural en vistas admin/settings, admin/dashboard y auth.
- Creación de componentes Blade reutilizables para patrones repetidos.
- Actualización progresiva de este spec con checklist ejecutado.
- Verificación final con tests y Browser/Dusk tests en Docker sin root.

Excluido:
- Cambios de negocio o de reglas de validación.
- Rediseño visual amplio fuera de los bloques extraídos.
- Migraciones, modelos o rutas nuevas no necesarias para el refactor.

## Hotspots Detectados

1. Inputs repetidos (label + input + error) en:
- `resources/views/livewire/admin/settings/partials/email-configuration-tab.blade.php`
- `resources/views/livewire/admin/settings/partials/recaptcha-tab.blade.php`
- `resources/views/livewire/admin/settings/partials/contact-form-tab.blade.php`

2. Tarjetas de estadísticas duplicadas en:
- `resources/views/admin/dashboard/index.blade.php`

3. Estructura repetida de páginas auth (contenedor + header + session status) en:
- `resources/views/pages/auth/login.blade.php`
- `resources/views/pages/auth/register.blade.php`
- `resources/views/pages/auth/forgot-password.blade.php`
- `resources/views/pages/auth/confirm-password.blade.php`
- `resources/views/pages/auth/reset-password.blade.php`

4. Consolidación adicional de tabs bilingües (pendiente, riesgo medio):
- `resources/views/components/admin/bilingual-field-tabs.blade.php`
- `resources/views/components/admin/bilingual-rich-text-tabs.blade.php`

## Plan De Ejecución Con Seguimiento

### Fase 1: Base y extracción rápida
- [x] Crear spec de ejecución y seguimiento en `.github/specs/001-refactor-views-code.md`.
- [x] Crear componente reusable para input admin estándar.
- [x] Crear componente reusable para stat card de dashboard.
- [x] Reemplazar usos en settings admin.
- [x] Reemplazar usos en dashboard admin.

### Fase 2: Consolidación auth
- [x] Crear wrapper reusable para estructura común de auth.
- [x] Migrar `login` al wrapper.
- [x] Migrar `register` al wrapper.
- [x] Migrar `forgot-password` al wrapper.
- [x] Migrar `confirm-password` al wrapper.
- [x] Migrar `reset-password` al wrapper.

### Fase 3: Consolidación avanzada
- [x] Revisar y unificar tabs bilingües en componente común con modos.
- [x] Validar estabilidad visual/espaciado y selectores estables de tests.

### Fase 4: Verificación
- [x] Ejecutar format en cambios PHP/Blade requeridos.
- [x] Ejecutar tests focalizados en Docker non-root.
- [x] Ejecutar tests completos en Docker non-root.
- [x] Ejecutar Browser/Dusk tests en Docker non-root.
- [x] Documentar resultados en este spec.

## Resultados de Verificación

1. Formato aplicado:
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`

2. Tests focalizados (post-fix):
- `tests/Feature/Settings/ProfileUpdateTest.php`
- `tests/Feature/Settings/SecurityTest.php`
- `tests/Unit/ConfiguredMailSettingsTest.php`
- `tests/Feature/ContactFormTest.php`
- Resultado: 30 passed.

3. Suite completa de tests:
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact`
- Resultado: 338 passed.

4. Browser/Dusk tests:
- Flujo Selenium con contenedor nombrado `dusk-app`, `APP_URL=http://dusk-app:8000`, sqlite aislado.
- `php artisan test --compact tests/Browser`
- Resultado: 30 passed.

5. Validación específica Fase 3:
- Feature: `php artisan test --compact tests/Feature/BilingualRichTextTabsComponentTest.php tests/Feature/AdminSettingsTest.php tests/Feature/AdminNoticeManagerTest.php`
- Resultado: 49 passed.
- Browser: `php artisan test --compact tests/Browser/AdminSettingsBilingualLayoutTest.php`
- Resultado: 1 passed.

## Riesgos y mitigación

1. Riesgo: divergencias sutiles entre formularios al extraer componentes.
Mitigación: extraer solo patrones idénticos en la primera pasada.

2. Riesgo: romper estructura auth por diferencias de páginas especiales.
Mitigación: aplicar wrapper solo en páginas con patrón común; dejar fuera páginas especiales (por ejemplo 2FA/verify si no encajan).

3. Riesgo: regresiones no detectadas en frontend interactivo.
Mitigación: ejecutar Browser/Dusk tests tras tests normales.

## Estado Actual

Completado. Fase 1, Fase 2, Fase 3 y Fase 4 completadas.