# Plan de Implementación: Gestión de Obras y Etiquetas en Avisos

## Visión general

Implementación incremental del sistema de etiquetas en avisos, el rol `construction_manager`, la gestión de obras, el sistema de consultas, los documentos adjuntos y el tracking de descargas. Las capas se construyen de abajo hacia arriba: esquema de base de datos → modelos y observer → políticas → UI Livewire → mails → rutas públicas → tests de propiedad.

## Tareas

- [ ] 1. Rol y modificaciones a modelos existentes
    - [ ] 1.1 Añadir constante `CONSTRUCTION_MANAGER` al modelo `Role`
    - Añadir `public const CONSTRUCTION_MANAGER = 'construction_manager';`
    - Registrar en `Role::names()` junto a los roles existentes
    - _Requisitos: 5.1, 5.2_

    - [ ] 1.2 Actualizar `User` con los nuevos métodos y relación
    - Añadir `canManageConstructions(): bool` (roles: superadmin, admin_general, construction_manager)
    - Modificar `canManageNotices(): bool` para incluir `CONSTRUCTION_MANAGER`
    - Modificar `canAccessAdminPanel(): bool` para incluir `CONSTRUCTION_MANAGER`
    - Añadir relación `constructions(): BelongsToMany` (tabla pivote `construction_user`)
    - _Requisitos: 5.3, 5.4, 5.6, 10.6_

    - [ ] 1.3 Añadir relaciones al modelo `Notice`
    - Añadir `tag(): BelongsTo` → `NoticeTag` (nullable)
    - Añadir `documents(): HasMany` → `NoticeDocument`
    - _Requisitos: 2.1, 2.6, 11.1_

    - [ ]\* 1.4 Escribir tests unitarios para los métodos de `User` y relaciones de `Notice`
    - Verificar que `canManageConstructions()` retorna `true` solo para los tres roles correctos
    - Verificar que `canManageNotices()` incluye `construction_manager`
    - Verificar que `canAccessAdminPanel()` incluye `construction_manager`
    - _Requisitos: 5.3, 5.4, 5.6_

- [ ] 2. Migraciones y modelos nuevos
    - [ ] 2.1 Crear migración y modelo `NoticeTag`
    - Tabla `notice_tags`: `slug` (unique), `name_eu`, `name_es` (nullable), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, relación `notices(): HasMany`, accessor bilingüe `getNameAttribute()` (patrón `ResolvesLocalizedAttributes`)
    - Factory con datos de ejemplo
    - _Requisitos: 1.1, 1.2_

    - [ ] 2.2 Crear migración `add_notice_tag_id_to_notices_table`
    - Añadir columna `notice_tag_id` (nullable, FK a `notice_tags`) a la tabla `notices`
    - _Requisitos: 2.6_

    - [ ] 2.3 Crear migración y modelo `Construction`
    - Tabla `constructions`: `title`, `slug` (unique), `description` (nullable), `starts_at` (date), `ends_at` (date, nullable), `is_active` (boolean, default true), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `$casts`, relaciones `managers(): BelongsToMany`, `inquiries(): HasMany`, `tag(): HasOne`
    - Scope `scopeActive()`: `starts_at <= today AND (ends_at >= today OR ends_at IS NULL)`
    - Factory con datos de ejemplo
    - _Requisitos: 6.1, 6.2, 6.7, 7.1, 7.7_

    - [ ] 2.4 Crear migración de tabla pivote `construction_user`
    - Campos: `construction_id` (FK), `user_id` (FK), timestamps
    - _Requisitos: 10.1_

    - [ ] 2.5 Crear migración y modelo `ConstructionInquiry`
    - Tabla `construction_inquiries`: `construction_id` (FK), `user_id` (FK nullable), `name`, `email`, `subject`, `message`, `reply` (nullable), `replied_at` (nullable), `is_read` (boolean, default false), `read_at` (nullable), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `$casts`, relaciones `construction(): BelongsTo`, `user(): BelongsTo`
    - Factory con datos de ejemplo
    - _Requisitos: 8.1, 9.1_

    - [ ] 2.6 Crear migración y modelo `NoticeDocument`
    - Tabla `notice_documents`: `notice_id` (FK), `token` (unique, UUID), `filename`, `path`, `mime_type`, `size_bytes`, `is_public` (boolean), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `$casts`, relaciones `notice(): BelongsTo`, `downloads(): HasMany`
    - Factory con datos de ejemplo
    - _Requisitos: 11.1, 11.5_

    - [ ] 2.7 Crear migración y modelo `NoticeDocumentDownload`
    - Tabla `notice_document_downloads`: `notice_document_id` (FK), `user_id` (FK nullable), `ip_address`, `downloaded_at` — **sin** softDeletes, `$timestamps = false`
    - Modelo con `$fillable`, `$casts`, relaciones `document(): BelongsTo`, `user(): BelongsTo`
    - _Requisitos: 12.1_

    - [ ]\* 2.8 Escribir tests unitarios para los nuevos modelos y sus relaciones
    - Verificar que `NoticeDocumentDownload` no usa SoftDeletes y tiene `$timestamps = false`
    - Verificar que `Construction::scopeActive()` filtra correctamente por fechas
    - Verificar que `NoticeTag` tiene el accessor bilingüe con fallback
    - _Requisitos: 1.1, 6.1, 7.1, 12.1_

- [ ] 3. ConstructionObserver
    - [ ] 3.1 Crear `ConstructionObserver` en `app/Observers/ConstructionObserver.php`
    - Evento `created`: crear automáticamente `NoticeTag` con `slug = 'obra-' . $construction->slug` y `name_eu = name_es = 'Obra: ' . $construction->title`
    - Registrar el observer en `AppServiceProvider`
    - _Requisitos: 6.7_

    - [ ]\* 3.2 Escribir test de feature para `ConstructionObserver`
    - Verificar que al crear una `Construction` se crea exactamente una `NoticeTag` con el slug correcto
    - _Requisitos: 6.7_

- [ ] 4. Políticas de autorización
    - [ ] 4.1 Crear `ConstructionPolicy` en `app/Policies/ConstructionPolicy.php`
    - `viewAny`, `create`: `canManageConstructions()`
    - `update`: `canManageConstructions()`
    - `delete`: solo superadmin/admin_general
    - Registrar en `AppServiceProvider`
    - _Requisitos: 6.8, 10.4_

    - [ ] 4.2 Crear `NoticeTagPolicy` en `app/Policies/NoticeTagPolicy.php`
    - `create`, `update`, `delete`: solo superadmin/admin_general
    - Registrar en `AppServiceProvider`
    - _Requisitos: 1.3, 1.6, 1.7_

    - [ ]\* 4.3 Escribir tests de feature para las políticas
    - Verificar que `construction_manager` puede crear/editar obras pero no eliminarlas
    - Verificar que solo superadmin/admin_general pueden crear/editar/eliminar `NoticeTag`
    - _Requisitos: 6.8, 10.4, 1.3_

- [ ] 5. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 6. Modificación de `AdminNoticeManager`
    - [ ] 6.1 Añadir soporte de etiquetas al formulario de avisos
    - Añadir propiedad `$selectedTagId` (nullable int)
    - En `rules()`: validar `selectedTagId`; para `construction_manager`, solo permitir las `Construction_Tag` de sus obras asignadas
    - En `saveNotice()`: autorizar que el `construction_manager` no use etiquetas ajenas (HTTP 403 si viola la restricción)
    - En `render()`: cargar `noticeTags` disponibles según rol
    - Actualizar `resetForm()`, `editNotice()`, `saveNotice()` para incluir `selectedTagId`
    - _Requisitos: 2.2, 2.3, 2.4, 2.5, 3.1_

    - [ ] 6.2 Añadir gestión de documentos adjuntos al formulario de avisos
    - Métodos `uploadDocument()`, `removeDocument()`, `toggleDocumentPublic()`
    - Validar tipo MIME (pdf, docx, xlsx, jpg, png) y tamaño máximo 20 MB
    - Generar `token` UUID al crear cada `NoticeDocument`
    - _Requisitos: 11.2, 11.3, 11.4, 11.5_

    - [ ] 6.3 Mostrar etiqueta y contador de descargas en el listado de avisos
    - En `render()`: añadir `withCount` o subquery para total de descargas por aviso
    - En la vista: badge de etiqueta junto al título; total de descargas si hay documentos; desglose por documento al expandir
    - _Requisitos: 3.1, 3.2, 3.3, 12.3, 12.4, 12.5_

    - [ ]\* 6.4 Escribir tests de feature Livewire para las modificaciones de `AdminNoticeManager`
    - Verificar que `construction_manager` solo ve sus `Construction_Tag` en el selector
    - Verificar que guardar con etiqueta ajena lanza HTTP 403
    - Verificar que subir un fichero con MIME no permitido retorna error de validación
    - Verificar que el contador de descargas se muestra correctamente
    - _Requisitos: 2.3, 2.4, 11.2, 11.3, 12.3_

- [ ] 7. `AdminConstructionManager` (CRUD de obras)
    - [ ] 7.1 Crear `AdminConstructionManager` en `app/Livewire/AdminConstructionManager.php`
    - Lista paginada de obras con columnas: título, fechas, estado activo, nº managers asignados
    - Formulario inline: título, descripción, `starts_at`, `ends_at` (nullable), `is_active`
    - Validar que `ends_at`, si está presente, sea posterior o igual a `starts_at`
    - Acciones: crear, editar, eliminar (solo superadmin/admin_general), marcar activa/inactiva
    - Selector de `Construction_Manager` asignados (visible solo para superadmin/admin_general)
    - _Requisitos: 6.1, 6.3, 6.4, 6.5, 6.6, 6.8, 10.2, 10.3, 10.4_

    - [ ] 7.2 Crear la vista Blade `resources/views/livewire/admin/construction-manager.blade.php`
    - Usar componentes Flux UI v2 para tabla, formulario y modales de confirmación
    - Seguir el patrón visual de `notice-manager.blade.php`
    - _Requisitos: 6.8_

    - [ ] 7.3 Registrar ruta admin `/obras` en `routes/private.php`
    - Middleware `role:superadmin,admin_general,construction_manager`
    - _Requisitos: 6.8_

    - [ ]\* 7.4 Escribir tests de feature Livewire para `AdminConstructionManager`
    - Verificar CRUD completo para superadmin
    - Verificar que `construction_manager` no puede eliminar obras
    - Verificar que `ends_at` anterior a `starts_at` retorna error de validación
    - Verificar que el selector de managers solo es visible para superadmin/admin_general
    - _Requisitos: 6.3, 6.4, 6.8, 10.3, 10.4_

- [ ] 8. `NoticeDocumentController` y rutas de descarga
    - [ ] 8.1 Crear `NoticeDocumentController` en `app/Http/Controllers/NoticeDocumentController.php`
    - `GET /notice-documents/{token}`: buscar `NoticeDocument` por token (404 si no existe)
    - Si `is_public = false` y usuario no autenticado: redirigir a login
    - Registrar `NoticeDocumentDownload` con `user_id` (o null), `ip_address` y `downloaded_at`
    - Servir el fichero con el `mime_type` y `filename` correctos
    - _Requisitos: 11.6, 11.7, 11.8, 12.1, 12.2_

    - [ ] 8.2 Registrar ruta `notice-documents.download` en `routes/web.php`
    - Ruta pública `GET /notice-documents/{token}` sin middleware de auth (el controlador gestiona el acceso)
    - _Requisitos: 11.6, 11.7_

    - [ ]\* 8.3 Escribir tests de feature para `NoticeDocumentController`
    - Verificar que documento público se sirve sin autenticación y registra la descarga
    - Verificar que documento privado redirige a login si no autenticado
    - Verificar que usuario autenticado descarga cualquier documento y registra la descarga con `user_id`
    - Verificar que token inválido retorna 404
    - _Requisitos: 11.6, 11.7, 11.8, 12.1, 12.2_

- [ ] 9. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 10. Mails de consultas de obras
    - [ ] 10.1 Crear `ConstructionInquiryNotificationMail` en `app/Mail/ConstructionInquiryNotificationMail.php`
    - Constructor: `ConstructionInquiry $inquiry`, `Construction $construction`
    - Notifica al `Construction_Manager` de una nueva consulta recibida
    - Vista Blade `resources/views/mail/construction-inquiry-notification.blade.php`
    - _Requisitos: 8.5_

    - [ ] 10.2 Crear `ConstructionInquiryReplyMail` en `app/Mail/ConstructionInquiryReplyMail.php`
    - Constructor: `ConstructionInquiry $inquiry`
    - Envía la respuesta al vecino a la dirección `$inquiry->email`
    - Vista Blade `resources/views/mail/construction-inquiry-reply.blade.php`
    - _Requisitos: 9.8, 9.9_

    - [ ]\* 10.3 Escribir tests unitarios para los mailables
    - Verificar que `ConstructionInquiryNotificationMail` contiene el asunto y el nombre de la obra
    - Verificar que `ConstructionInquiryReplyMail` contiene el texto de la respuesta
    - _Requisitos: 8.5, 9.8_

- [ ] 11. `PublicConstructionController` y rutas `/obrak`
    - [ ] 11.1 Crear `PublicConstructionController` en `app/Http/Controllers/PublicConstructionController.php`
    - `index()`: lista obras con `Construction::active()->get()`, retorna vista
    - `show(string $slug)`: busca obra activa por slug (404 si no existe o inactiva), carga avisos con la `Construction_Tag` de esa obra, retorna vista
    - Patrón idéntico a `PublicVotingController`
    - _Requisitos: 7.1, 7.2, 7.4, 7.6_

    - [ ] 11.2 Crear vistas Blade para el frontend público
    - `resources/views/public/constructions/index.blade.php` — listado de obras activas
    - `resources/views/public/constructions/show.blade.php` — detalle de obra con avisos y formulario de consulta
    - _Requisitos: 7.4, 7.5_

    - [ ] 11.3 Registrar rutas públicas en `routes/public.php`
    - Grupos `eu` (`/obrak`, `/obrak/{slug}`) y `es` (`/obras`, `/obras/{slug}`) con middleware `auth`
    - Nombres: `constructions.eu`, `constructions.show.eu`, `constructions.es`, `constructions.show.es`
    - _Requisitos: 7.1, 7.2, 7.3_

    - [ ]\* 11.4 Escribir tests de feature para `PublicConstructionController`
    - Verificar que `/obrak` lista solo obras activas y requiere autenticación
    - Verificar que `/obrak/{slug}` muestra los avisos de la `Construction_Tag` correcta
    - Verificar que obra inactiva o inexistente retorna 404
    - _Requisitos: 7.1, 7.2, 7.3, 7.6_

- [ ] 12. `PublicConstructionInquiryForm` (Livewire)
    - [ ] 12.1 Crear `PublicConstructionInquiryForm` en `app/Livewire/PublicConstructionInquiryForm.php`
    - Propiedades: `$constructionId`, `$name`, `$email`, `$subject`, `$message`
    - Pre-rellenar `name` y `email` del usuario autenticado en `mount()`
    - Validar que `name`, `email`, `subject`, `message` no estén vacíos y que `email` tenga formato válido
    - Al enviar: persistir `ConstructionInquiry`, despachar `ConstructionInquiryNotificationMail` a cada manager de la obra, mostrar mensaje de confirmación
    - _Requisitos: 8.2, 8.3, 8.4, 8.5, 8.6_

    - [ ]\* 12.2 Escribir tests de feature Livewire para `PublicConstructionInquiryForm`
    - Verificar que campos obligatorios vacíos muestran errores de validación
    - Verificar que email inválido muestra error de validación
    - Verificar que envío correcto persiste la consulta y despacha el mail
    - _Requisitos: 8.2, 8.3, 8.4, 8.5_

- [ ] 13. `AdminConstructionInquiryInbox` (Livewire)
    - [ ] 13.1 Crear `AdminConstructionInquiryInbox` en `app/Livewire/AdminConstructionInquiryInbox.php`
    - Patrón visual idéntico a `AdminMessageInbox`
    - `construction_manager`: solo ve consultas de sus obras asignadas; `superadmin`: ve todas
    - Al abrir una consulta no leída: marcar `is_read = true`, `read_at = now()`
    - Acción de alternar leído/no leído manualmente
    - Indicador visual de respondida/no respondida
    - Campo `reply` con botón de envío: persistir en `reply` y `replied_at`, despachar `ConstructionInquiryReplyMail`
    - Sobrescribir `reply` y actualizar `replied_at` si ya existe respuesta
    - Filtro por obra
    - Registrar ruta admin `/consultas-obras` en `routes/private.php`
    - _Requisitos: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6, 9.7, 9.8, 9.9, 9.10_

    - [ ]\* 13.2 Escribir tests de feature Livewire para `AdminConstructionInquiryInbox`
    - Verificar que `construction_manager` solo ve consultas de sus obras
    - Verificar que abrir una consulta no leída la marca como leída
    - Verificar que enviar respuesta persiste `reply` y despacha el mail
    - Verificar que responder de nuevo sobrescribe `reply` y actualiza `replied_at`
    - _Requisitos: 9.1, 9.2, 9.4, 9.7, 9.8, 9.10_

- [ ] 14. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 15. Tests de propiedad (property-based testing con Pest)
    - [ ]\* 15.1 P1: Unicidad de slug en `NoticeTag` — `tests/Unit/NoticeTagSlugUniquenessTest.php`
    - Usar `fake()` para generar nombres que produzcan slugs duplicados, verificar rechazo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 1: Unicidad de slug en NoticeTag`
    - _Requisitos: 1.3, 1.4_

    - [ ]\* 15.2 P2: Etiquetas disponibles según rol — `tests/Unit/ConstructionManagerTagsTest.php`
    - Usar `fake()` para generar managers con N obras, verificar que el conjunto de etiquetas seleccionables es exactamente las N `Construction_Tag` de esas obras con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 2: Etiquetas disponibles según rol`
    - _Requisitos: 2.3_

    - [ ]\* 15.3 P3: Autorización de etiqueta en aviso — `tests/Feature/ConstructionManagerNoticeAuthTest.php`
    - Usar `fake()` para generar etiquetas no asignadas al manager, verificar HTTP 403 al guardar aviso con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 3: Autorización de etiqueta en aviso`
    - _Requisitos: 2.4_

    - [ ]\* 15.4 P4: `canManageConstructions` por rol — `tests/Unit/UserCanManageConstructionsTest.php`
    - Usar `fake()->randomElement(Role::names())` para generar usuarios con roles aleatorios, verificar retorno correcto con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 4: canManageConstructions por rol`
    - _Requisitos: 5.6, 10.6_

    - [ ]\* 15.5 P5: Validación de fechas de obra — `tests/Unit/ConstructionDateValidationTest.php`
    - Usar `fake()` para generar pares de fechas válidos e inválidos, verificar que `ends_at < starts_at` falla y el resto pasa con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 5: Validación de fechas de obra`
    - _Requisitos: 6.4_

    - [ ]\* 15.6 P6: Observer crea `Construction_Tag` automáticamente — `tests/Feature/ConstructionObserverTest.php`
    - Usar `fake()` para generar obras con títulos aleatorios, verificar que existe exactamente una `NoticeTag` con slug `obra-{slug}` con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 6: Observer crea Construction_Tag automáticamente`
    - _Requisitos: 6.7_

    - [ ]\* 15.7 P7: Filtro de obras activas — `tests/Unit/ConstructionActiveScopeTest.php`
    - Usar `fake()` para generar obras con fechas variadas, verificar que `scopeActive()` retorna exactamente las obras en rango con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 7: Filtro de obras activas`
    - _Requisitos: 7.1_

    - [ ]\* 15.8 P8: Validación del formulario de consulta — `tests/Unit/ConstructionInquiryValidationTest.php`
    - Usar `fake()` para generar combinaciones de campos válidos e inválidos, verificar aceptación/rechazo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 8: Validación del formulario de consulta`
    - _Requisitos: 8.2, 8.3_

    - [ ]\* 15.9 P9: Validación de documentos adjuntos — `tests/Unit/NoticeDocumentValidationTest.php`
    - Usar `fake()->randomElement()` para generar tipos MIME permitidos y no permitidos, verificar aceptación/rechazo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 9: Validación de documentos adjuntos`
    - _Requisitos: 11.2, 11.3_

    - [ ]\* 15.10 P10: Tracking de descargas — `tests/Feature/NoticeDocumentDownloadTrackingTest.php`
    - Usar `fake()` para generar descargas autenticadas y anónimas, verificar que existe exactamente un `NoticeDocumentDownload` con los campos correctos con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 10: Tracking de descargas`
    - _Requisitos: 12.1, 12.2_

- [ ] 16. Checkpoint final — Verificar calidad completa
    - Asegurarse de que todos los tests pasan. Ejecutar `composer quality` dentro de Docker. Consultar al usuario si surgen dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Los tests de propiedad (tarea 15) usan Pest nativo con `fake()` y `->repeat(2)` — sin dependencias adicionales
- Cada tarea referencia los requisitos específicos para trazabilidad
- Los checkpoints garantizan validación incremental antes de avanzar a la siguiente capa

## Implementation Plan

### Goal

- [ ] Entregar `construction-management` de forma incremental y segura, priorizando primero núcleo funcional (rol, etiquetas, obras, permisos, rutas públicas y formularios) y dejando los tests opcionales de propiedad para una fase posterior de endurecimiento.
- [ ] Ejecutar las tareas exactamente en el orden del documento, una por una, sin saltos y marcando avance solo tras validación.

### Technical Decisions

- [ ] Mantener arquitectura existente Laravel 13 + Livewire 4 + Flux UI v2 sin nuevas dependencias.
- [ ] Priorizar tests Unit para lógica pura y usar Feature/Livewire solo cuando haya integración real (DB/HTTP/UI).
- [ ] Aplicar enfoque de rendimiento: reducir ejemplos aleatorios opcionales y ejecutar primero suite mínima afectada para feedback rápido.
- [ ] Reutilizar patrones existentes (`AdminNoticeManager`, `AdminMessageInbox`, `PublicVotingController`, `NoticeDocument`) para evitar divergencias de diseño.
- [ ] Aplicar seguridad en doble capa en reglas críticas (autorización app + restricciones de BD cuando aplique).
- [ ] No cerrar ninguna tarea sin completar su validación local (tests afectados + formato) y sin respetar los checkpoints 5, 9, 14 y 16.

### Execution Steps

- [ ] 1. Ejecutar tarea 1 completa (1.1 -> 1.4*), validar y marcar.
- [ ] 2. Ejecutar tarea 2 completa (2.1 -> 2.8*), validar y marcar.
- [ ] 3. Ejecutar tarea 3 completa (3.1 -> 3.2*), validar y marcar.
- [ ] 4. Ejecutar tarea 4 completa (4.1 -> 4.3*), validar y marcar.
- [ ] 5. Ejecutar checkpoint 5 (tests hasta aquí) antes de seguir.
- [ ] 6. Ejecutar tarea 6 completa (6.1 -> 6.4*), validar y marcar.
- [ ] 7. Ejecutar tarea 7 completa (7.1 -> 7.4*), validar y marcar.
- [ ] 8. Ejecutar tarea 8 completa (8.1 -> 8.3*), validar y marcar.
- [ ] 9. Ejecutar checkpoint 9 (tests hasta aquí) antes de seguir.
- [ ] 10. Ejecutar tarea 10 completa (10.1 -> 10.3*), validar y marcar.
- [ ] 11. Ejecutar tarea 11 completa (11.1 -> 11.4*), validar y marcar.
- [ ] 12. Ejecutar tarea 12 completa (12.1 -> 12.2*), validar y marcar.
- [ ] 13. Ejecutar tarea 13 completa (13.1 -> 13.2*), validar y marcar.
- [ ] 14. Ejecutar checkpoint 14 (tests hasta aquí) antes de seguir.
- [ ] 15. Ejecutar tarea 15 (opcionales de property-based según prioridad acordada), validar y marcar.
- [ ] 16. Ejecutar checkpoint final 16 con `composer quality` y cierre.

### Work Items

- [ ] Backend dominio: `app/Models`, `database/migrations`, `app/Observers`, `app/Policies`.
- [ ] Admin Livewire/Blade: `app/Livewire/Admin*`, `resources/views/livewire/admin/**`.
- [ ] Frontend público: `app/Http/Controllers/PublicConstructionController.php`, `resources/views/public/constructions/**`, `app/Livewire/PublicConstructionInquiryForm.php`.
- [ ] Documentos y descargas: `app/Http/Controllers/NoticeDocumentController.php`, rutas en `routes/web.php`.
- [ ] Correos y notificaciones: `app/Mail/*ConstructionInquiry*`, vistas `resources/views/mail/**`.
- [ ] Testing progresivo: `tests/Unit/**`, `tests/Feature/**`, `tests/Browser/**` (si se tocan flujos sensibles en Blade).

### Validation

- [ ] TDD por bloque cuando sea viable (test rojo -> implementación mínima -> refactor).
- [ ] Formato obligatorio tras cambios PHP: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`.
- [ ] Verificación mínima por bloque: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact <tests-afectados>`.
- [ ] Si hay cambios en `resources/views/**`, cubrir con Dusk focalizado antes de cerrar.
- [ ] Puerta de calidad final obligatoria: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.
