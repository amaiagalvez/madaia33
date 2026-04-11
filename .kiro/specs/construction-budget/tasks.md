# Plan de Implementación: Presupuesto de Obra (Construction Budget)

## Visión general

Implementación incremental del sistema de presupuesto por obra. Las capas se construyen de abajo hacia arriba: migraciones y modelos → servicio de cálculo → política de autorización → controlador de descarga → UI Livewire (admin y frontend) → tests de propiedad.

## Tareas

- [ ] 1. Migraciones y modelos
  - [ ] 1.1 Crear migración y modelo `ConstructionBudgetLine`
    - Tabla `construction_budget_lines`: `construction_id` (FK a `constructions`), `line_date` (date), `description` (string), `subtotal` (decimal:2), `vat_pct` (decimal:2), `line_total` (decimal:2), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `casts()` (line_date → date, subtotal/vat_pct/line_total → decimal:2)
    - Relaciones: `construction(): BelongsTo`, `documents(): HasMany → BudgetLineDocument`
    - Factory con datos de ejemplo
    - _Requisitos: 1.1, 1.2, 1.7_

  - [ ] 1.2 Crear migración y modelo `BudgetLineDocument`
    - Tabla `budget_line_documents`: `construction_budget_line_id` (FK), `token` (unique, UUID), `filename`, `path`, `mime_type`, `size_bytes` (integer), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `casts()` (size_bytes → integer)
    - Generar `token` UUID automáticamente en `boot()` al crear
    - Relaciones: `budgetLine(): BelongsTo`, `downloads(): HasMany → BudgetLineDocumentDownload`
    - Factory con datos de ejemplo
    - _Requisitos: 2.1, 2.7_

  - [ ] 1.3 Crear migración y modelo `BudgetLineDocumentDownload`
    - Tabla `budget_line_document_downloads`: `budget_line_document_id` (FK), `user_id` (FK nullable a `users`), `ip_address`, `downloaded_at` — **sin** softDeletes, `$timestamps = false`
    - Modelo con `$fillable`, `casts()` (downloaded_at → datetime), relaciones `document(): BelongsTo`, `user(): BelongsTo`
    - _Requisitos: 8.1_

  - [ ]\* 1.4 Escribir tests unitarios para los nuevos modelos
    - Verificar que `BudgetLineDocumentDownload` no usa SoftDeletes y tiene `$timestamps = false`
    - Verificar que `BudgetLineDocument` genera token UUID automáticamente al crear
    - Verificar que `ConstructionBudgetLine` tiene los casts correctos
    - _Requisitos: 1.1, 2.1, 8.1_

- [ ] 2. Servicio `ConstructionBudgetCalculator`
  - [ ] 2.1 Crear `ConstructionBudgetCalculator` en `app/Support/ConstructionBudgetCalculator.php`
    - `lineTotal(float $subtotal, float $vatPct): float` — `round(subtotal * (1 + vatPct / 100), 2)`
    - `budgetTotal(Construction $construction): float` — suma de `line_total` de líneas no eliminadas
    - `ownerShare(float $budgetTotal, float $communityPct): float` — `round(budgetTotal * communityPct / 100, 2)`
    - `ownerTotal(float $budgetTotal, Collection $properties): float` — suma de `ownerShare` por cada propiedad
    - _Requisitos: 7.1, 7.2, 7.3, 7.4_

  - [ ]\* 2.2 Escribir tests unitarios para `ConstructionBudgetCalculator`
    - Verificar `lineTotal` con valores conocidos (subtotal 100, vat_pct 21 → 121.00)
    - Verificar `budgetTotal` excluye líneas con soft delete
    - Verificar `ownerShare` y `ownerTotal` con community_pct conocidos
    - _Requisitos: 7.1, 7.2, 7.3, 7.4_

- [ ] 3. Política `ConstructionBudgetPolicy`
  - [ ] 3.1 Crear `ConstructionBudgetPolicy` en `app/Policies/ConstructionBudgetPolicy.php`
    - `manage(User $user, Construction $construction): bool` — `canManageConstructions()` + para `construction_manager` verificar que la obra está asignada al usuario
    - `viewReparto(User $user): bool` — solo `superadmin` o `admin_general`
    - Registrar la policy en `AppServiceProvider`
    - _Requisitos: 6.1, 6.2, 6.3, 6.6_

  - [ ]\* 3.2 Escribir tests de feature para `ConstructionBudgetPolicy`
    - Verificar que `superadmin` y `admin_general` pasan `manage` para cualquier obra
    - Verificar que `construction_manager` pasa `manage` solo para obras asignadas
    - Verificar que `construction_manager` recibe 403 para obras no asignadas
    - Verificar que solo `superadmin`/`admin_general` pasan `viewReparto`
    - _Requisitos: 6.1, 6.3, 6.6_

- [ ] 4. Checkpoint — Verificar que todos los tests pasan hasta aquí
  - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 5. `BudgetLineDocumentController` y ruta de descarga
  - [ ] 5.1 Crear `BudgetLineDocumentController` en `app/Http/Controllers/BudgetLineDocumentController.php`
    - `GET /budget-documents/{token}`: buscar `BudgetLineDocument` por token (404 si no existe)
    - Registrar `BudgetLineDocumentDownload` con `user_id`, `ip_address` y `downloaded_at = now()`
    - Servir el fichero con el `mime_type` y `filename` correctos
    - _Requisitos: 2.5, 2.7, 2.8, 8.2_

  - [ ] 5.2 Registrar ruta `budget-documents.download` en `routes/web.php`
    - `GET /budget-documents/{token}` con middleware `auth`
    - _Requisitos: 2.5, 2.6_

  - [ ]\* 5.3 Escribir tests de feature para `BudgetLineDocumentController`
    - Verificar que token inválido retorna 404
    - Verificar que usuario no autenticado es redirigido al login
    - Verificar que usuario autenticado descarga el fichero y se registra el download con `user_id` e IP correctos
    - _Requisitos: 2.5, 2.6, 2.7, 2.8, 8.2_

- [ ] 6. `AdminConstructionBudget` (Livewire — CRUD líneas + documentos + reparto)
  - [ ] 6.1 Crear `AdminConstructionBudget` en `app/Livewire/AdminConstructionBudget.php`
    - Propiedades: `$construction`, `$lines`, `$budgetTotal`, `$showReparto`
    - `mount(Construction $construction)`: cargar líneas ordenadas por `line_date` asc, calcular `budgetTotal`, evaluar `viewReparto` con la policy
    - CRUD de `ConstructionBudgetLine`: `addLine()`, `editLine(int $id)`, `saveLine()`, `deleteLine(int $id)`
    - Calcular y persistir `line_total` en `saveLine()` usando `ConstructionBudgetCalculator::lineTotal()`
    - Validar: `line_date` fecha válida, `description` no vacío, `subtotal` ≥ 0, `vat_pct` ≥ 0
    - Soft delete en cascada al eliminar línea: eliminar también sus `BudgetLineDocument`
    - Upload y eliminación de `BudgetLineDocument`: `uploadDocument(int $lineId)`, `removeDocument(int $docId)`
    - Validar MIME (pdf, docx, xlsx, jpg, png) y tamaño máximo 20 MB
    - Sección de reparto por Owner/Property (solo si `viewReparto`): calcular con `ConstructionBudgetCalculator`
    - Mostrar contador de descargas por línea y por documento
    - Autorizar cada acción con `ConstructionBudgetPolicy::manage()`
    - _Requisitos: 1.3, 1.4, 1.5, 1.6, 1.7, 2.2, 2.3, 2.4, 2.9, 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 6.6, 8.3, 8.4, 8.5_

  - [ ] 6.2 Crear la vista Blade `resources/views/livewire/admin-construction-budget.blade.php`
    - Usar componentes Flux UI v2 para tabla, formulario inline y modales de confirmación
    - Seguir el patrón visual de `notice-manager.blade.php`
    - _Requisitos: 3.1, 3.2, 3.3_

  - [ ] 6.3 Montar el componente en la vista de detalle de obra existente
    - Incluir `<livewire:admin-construction-budget :construction="$construction" />` en la vista de detalle de obra del panel de administración
    - _Requisitos: 3.6_

  - [ ]\* 6.4 Escribir tests de feature Livewire para `AdminConstructionBudget`
    - Verificar CRUD completo de líneas para `superadmin`
    - Verificar que `construction_manager` recibe 403 para obras no asignadas
    - Verificar que subir fichero con MIME no permitido retorna error de validación
    - Verificar que eliminar línea aplica soft delete a sus documentos
    - Verificar que la sección de reparto solo es visible para `superadmin`/`admin_general`
    - _Requisitos: 1.3, 1.4, 1.6, 2.2, 2.3, 3.6, 4.1, 6.3, 6.6_

- [ ] 7. `PublicConstructionBudget` (Livewire — vista frontend Owner)
  - [ ] 7.1 Crear `PublicConstructionBudget` en `app/Livewire/PublicConstructionBudget.php`
    - `mount(Construction $construction)`: cargar líneas no eliminadas ordenadas por `line_date` asc, calcular `budgetTotal`
    - Obtener propiedades activas del Owner autenticado asignadas a la obra (`end_date` null)
    - Calcular `ownerShare` por propiedad y `ownerTotal` con `ConstructionBudgetCalculator`
    - Si el Owner no tiene propiedades activas: mostrar solo `budgetTotal` y líneas con mensaje informativo
    - Mostrar documentos no eliminados con enlace de descarga por token
    - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7, 5.8_

  - [ ] 7.2 Crear la vista Blade `resources/views/livewire/public-construction-budget.blade.php`
    - Usar componentes Flux UI v2
    - Seguir el patrón visual del frontend público existente
    - _Requisitos: 5.1, 5.6_

  - [ ] 7.3 Montar el componente en la vista pública de detalle de obra
    - Incluir `<livewire:public-construction-budget :construction="$construction" />` en la vista `/obrak/{slug}`
    - _Requisitos: 5.1, 6.4, 6.5_

  - [ ]\* 7.4 Escribir tests de feature Livewire para `PublicConstructionBudget`
    - Verificar que Owner con propiedades activas ve `ownerShare` y `ownerTotal`
    - Verificar que Owner sin propiedades activas ve solo `budgetTotal` y líneas con mensaje informativo
    - Verificar que usuario no autenticado es redirigido al login
    - Verificar que los documentos eliminados (soft delete) no aparecen en la lista
    - _Requisitos: 5.1, 5.2, 5.3, 5.5, 5.7, 6.4, 6.5_

- [ ] 8. Checkpoint — Verificar que todos los tests pasan hasta aquí
  - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 9. Tests de propiedad (property-based testing con Pest)
  - [ ]\* 9.1 P1: Cálculo de `line_total` — `tests/Unit/ConstructionBudgetCalculatorLineTotalTest.php`
    - Usar `fake()->randomFloat(2, 0, 10000)` para `subtotal` y `vat_pct`, verificar `round(subtotal * (1 + vatPct / 100), 2)` y no-negatividad con `->repeat(2)`
    - Etiquetar: `// Feature: construction-budget, Property 1: Cálculo de line_total`
    - _Requisitos: 1.2, 7.1, 7.6_

  - [ ]\* 9.2 P2: `Budget_Total` excluye líneas con soft delete — `tests/Unit/ConstructionBudgetCalculatorBudgetTotalTest.php`
    - Usar factories para crear líneas con algunas eliminadas, verificar que `budgetTotal()` suma solo las no eliminadas con `->repeat(2)`
    - Etiquetar: `// Feature: construction-budget, Property 2: Budget_Total excluye soft-deleted`
    - _Requisitos: 7.2, 7.5_

  - [ ]\* 9.3 P3: Reparto proporcional `Owner_Share` y `Owner_Total` — `tests/Unit/ConstructionBudgetCalculatorRepartoTest.php`
    - Usar `fake()->randomFloat(4, 0, 100)` para `community_pct`, verificar `ownerShare` y `ownerTotal` con `->repeat(2)`
    - Etiquetar: `// Feature: construction-budget, Property 3: Reparto proporcional Owner_Share y Owner_Total`
    - _Requisitos: 4.3, 4.4, 7.3, 7.4_

  - [ ]\* 9.4 P4: Soft delete en cascada línea → documentos — `tests/Feature/ConstructionBudgetLineSoftDeleteTest.php`
    - Usar factories para crear líneas con N documentos, aplicar soft delete a la línea, verificar que todos los documentos tienen `deleted_at` no nulo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-budget, Property 4: Soft delete en cascada línea → documentos`
    - _Requisitos: 1.6_

  - [ ]\* 9.5 P5: Validación de tipo MIME — `tests/Unit/BudgetLineDocumentMimeValidationTest.php`
    - Usar `fake()->randomElement()` para generar MIMEs permitidos y no permitidos, verificar aceptación/rechazo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-budget, Property 5: Validación de tipo MIME de documentos`
    - _Requisitos: 2.2, 2.3, 2.4_

  - [ ]\* 9.6 P6: Tracking de descargas — `tests/Feature/BudgetLineDocumentDownloadTrackingTest.php`
    - Usar factories para generar descargas autenticadas, verificar que existe exactamente un `BudgetLineDocumentDownload` con `user_id`, IP y `downloaded_at` correctos con `->repeat(2)`
    - Etiquetar: `// Feature: construction-budget, Property 6: Tracking de descargas`
    - _Requisitos: 8.2_

  - [ ]\* 9.7 P7: Autorización de `Construction_Manager` por obra asignada — `tests/Feature/ConstructionBudgetAuthorizationTest.php`
    - Usar factories para generar `construction_manager` con obras asignadas y no asignadas, verificar HTTP 403 para obras no asignadas con `->repeat(2)`
    - Etiquetar: `// Feature: construction-budget, Property 7: Autorización de Construction_Manager por obra asignada`
    - _Requisitos: 6.3_

- [ ] 10. Checkpoint final — Verificar calidad completa
  - Asegurarse de que todos los tests pasan. Ejecutar `composer quality` dentro de Docker. Consultar al usuario si surgen dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Los tests de propiedad (tarea 9) usan Pest nativo con `fake()` y `->repeat(2)` — sin dependencias adicionales
- Cada tarea referencia los requisitos específicos para trazabilidad
- Los checkpoints garantizan validación incremental antes de avanzar a la siguiente capa
