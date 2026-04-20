# Plan de Implementación: Gestión de Obras y Etiquetas en Avisos

## Visión general

Implementación incremental del sistema de etiquetas en avisos, el rol `construction_manager`, la gestión de obras, el sistema de consultas, los documentos adjuntos y el tracking de descargas. Las capas se construyen de abajo hacia arriba: esquema de base de datos → modelos y observer → políticas → UI Livewire → mails → rutas públicas → tests de propiedad.

## Tareas

- [x] 1. Rol y modificaciones a modelos existentes
    - [x] 1.1 Añadir constante `CONSTRUCTION_MANAGER` al modelo `Role`
    - Añadir `public const CONSTRUCTION_MANAGER = 'construction_manager';`
    - Registrar en `Role::names()` junto a los roles existentes
    - _Requisitos: 5.1, 5.2_

    - [x] 1.2 Actualizar `User` con los nuevos métodos y relación
    - Añadir `canManageConstructions(): bool` (roles: superadmin, admin_general, construction_manager)
    - Modificar `canManageNotices(): bool` para incluir `CONSTRUCTION_MANAGER`
    - Modificar `canAccessAdminPanel(): bool` para incluir `CONSTRUCTION_MANAGER`
    - Añadir relación `constructions(): BelongsToMany` (tabla pivote `construction_user`)
    - _Requisitos: 5.3, 5.4, 5.6, 10.6_

    - [x] 1.3 Añadir relaciones al modelo `Notice`
    - Añadir `tag(): BelongsTo` → `NoticeTag` (nullable)
    - Añadir `documents(): HasMany` → `NoticeDocument`
    - _Requisitos: 2.1, 2.6, 11.1_

    - [x]\* 1.4 Escribir tests unitarios para los métodos de `User` y relaciones de `Notice`
    - Verificar que `canManageConstructions()` retorna `true` solo para los tres roles correctos
    - Verificar que `canManageNotices()` incluye `construction_manager`
    - Verificar que `canAccessAdminPanel()` incluye `construction_manager`
    - _Requisitos: 5.3, 5.4, 5.6_

- [x] 2. Migraciones y modelos nuevos
    - [x] 2.1 Crear migración y modelo `NoticeTag`
    - Tabla `notice_tags`: `slug` (unique), `name_eu`, `name_es` (nullable), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, relación `notices(): HasMany`, accessor bilingüe `getNameAttribute()` (patrón `ResolvesLocalizedAttributes`)
    - Factory con datos de ejemplo
    - _Requisitos: 1.1, 1.2_

    - [x] 2.2 Crear migración `add_notice_tag_id_to_notices_table`
    - Añadir columna `notice_tag_id` (nullable, FK a `notice_tags`) a la tabla `notices`
    - _Requisitos: 2.6_

    - [x] 2.3 Crear migración y modelo `Construction`
    - Tabla `constructions`: `title`, `slug` (unique), `description` (nullable), `starts_at` (date), `ends_at` (date, nullable), `is_active` (boolean, default true), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `$casts`, relaciones `managers(): BelongsToMany`, `inquiries(): HasMany`, `tag(): HasOne`
    - Scope `scopeActive()`: `starts_at <= today AND (ends_at >= today OR ends_at IS NULL)`
    - Factory con datos de ejemplo
    - _Requisitos: 6.1, 6.2, 6.7, 7.1, 7.7_

    - [x] 2.4 Crear migración de tabla pivote `construction_user`
    - Campos: `construction_id` (FK), `user_id` (FK), timestamps
    - _Requisitos: 10.1_

    - [x] 2.5 Crear migración y modelo `ConstructionInquiry`
    - Tabla `construction_inquiries`: `construction_id` (FK), `user_id` (FK nullable), `name`, `email`, `subject`, `message`, `reply` (nullable), `replied_at` (nullable), `is_read` (boolean, default false), `read_at` (nullable), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `$casts`, relaciones `construction(): BelongsTo`, `user(): BelongsTo`
    - Factory con datos de ejemplo
    - _Requisitos: 8.1, 9.1_

    - [x] 2.6 Crear migración y modelo `NoticeDocument`
    - Tabla `notice_documents`: `notice_id` (FK), `token` (unique, UUID), `filename`, `path`, `mime_type`, `size_bytes`, `is_public` (boolean), softDeletes
    - Modelo con `SoftDeletes`, `$fillable`, `$casts`, relaciones `notice(): BelongsTo`, `downloads(): HasMany`
    - Factory con datos de ejemplo
    - _Requisitos: 11.1, 11.5_

    - [x] 2.7 Crear migración y modelo `NoticeDocumentDownload`
    - Tabla `notice_document_downloads`: `notice_document_id` (FK), `user_id` (FK nullable), `ip_address`, `downloaded_at` — **sin** softDeletes, `$timestamps = false`
    - Modelo con `$fillable`, `$casts`, relaciones `document(): BelongsTo`, `user(): BelongsTo`
    - _Requisitos: 12.1_

    - [x]\* 2.8 Escribir tests unitarios para los nuevos modelos y sus relaciones
    - Verificar que `NoticeDocumentDownload` no usa SoftDeletes y tiene `$timestamps = false`
    - Verificar que `Construction::scopeActive()` filtra correctamente por fechas
    - Verificar que `NoticeTag` tiene el accessor bilingüe con fallback
    - _Requisitos: 1.1, 6.1, 7.1, 12.1_

- [x] 3. ConstructionObserver
    - [x] 3.1 Crear `ConstructionObserver` en `app/Observers/ConstructionObserver.php`
    - Evento `created`: crear automáticamente `NoticeTag` con `slug = 'obra-' . $construction->slug` y `name_eu = name_es = 'Obra: ' . $construction->title`
    - Registrar el observer en `AppServiceProvider`
    - _Requisitos: 6.7_

    - [x]\* 3.2 Escribir test de feature para `ConstructionObserver`
    - Verificar que al crear una `Construction` se crea exactamente una `NoticeTag` con el slug correcto
    - _Requisitos: 6.7_

- [x] 4. Políticas de autorización
    - [x] 4.1 Crear `ConstructionPolicy` en `app/Policies/ConstructionPolicy.php`
    - `viewAny`, `create`: `canManageConstructions()`
    - `update`: `canManageConstructions()`
    - `delete`: solo superadmin/admin_general
    - Registrar en `AppServiceProvider`
    - _Requisitos: 6.8, 10.4_

    - [x] 4.2 Crear `NoticeTagPolicy` en `app/Policies/NoticeTagPolicy.php`
    - `create`, `update`, `delete`: solo superadmin/admin_general
    - Registrar en `AppServiceProvider`
    - _Requisitos: 1.3, 1.6, 1.7_

    - [x]\* 4.3 Escribir tests de feature para las políticas
    - Verificar que `construction_manager` puede crear/editar obras pero no eliminarlas
    - Verificar que solo superadmin/admin_general pueden crear/editar/eliminar `NoticeTag`
    - _Requisitos: 6.8, 10.4, 1.3_

- [x] 5. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [x] 6. Modificación de `AdminNoticeManager`
    - [x] 6.1 Añadir soporte de etiquetas al formulario de avisos
    - Añadir propiedad `$selectedTagId` (nullable int)
    - En `rules()`: validar `selectedTagId`; para `construction_manager`, solo permitir las `Construction_Tag` de sus obras asignadas
    - En `saveNotice()`: autorizar que el `construction_manager` no use etiquetas ajenas (HTTP 403 si viola la restricción)
    - En `render()`: cargar `noticeTags` disponibles según rol
    - Actualizar `resetForm()`, `editNotice()`, `saveNotice()` para incluir `selectedTagId`
    - _Requisitos: 2.2, 2.3, 2.4, 2.5, 3.1_

    - [x] 6.2 Añadir gestión de documentos adjuntos al formulario de avisos
    - Métodos `uploadDocument()`, `removeDocument()`, `toggleDocumentPublic()`
    - Validar tipo MIME (pdf, docx, xlsx, jpg, png) y tamaño máximo 20 MB
    - Generar `token` UUID al crear cada `NoticeDocument`
    - _Requisitos: 11.2, 11.3, 11.4, 11.5_

    - [x] 6.3 Mostrar etiqueta y contador de descargas en el listado de avisos
    - En `render()`: añadir `withCount` o subquery para total de descargas por aviso
    - En la vista: badge de etiqueta junto al título; total de descargas si hay documentos; desglose por documento al expandir
    - _Requisitos: 3.1, 3.2, 3.3, 12.3, 12.4, 12.5_

    - [x]\* 6.4 Escribir tests de feature Livewire para las modificaciones de `AdminNoticeManager`
    - Verificar que `construction_manager` solo ve sus `Construction_Tag` en el selector
    - Verificar que guardar con etiqueta ajena lanza HTTP 403
    - Verificar que subir un fichero con MIME no permitido retorna error de validación
    - Verificar que el contador de descargas se muestra correctamente
    - _Requisitos: 2.3, 2.4, 11.2, 11.3, 12.3_

- [x] 7. `AdminConstructionManager` (CRUD de obras)
    - [x] 7.1 Crear `AdminConstructionManager` en `app/Livewire/AdminConstructionManager.php`
    - Lista paginada de obras con columnas: título, fechas, estado activo, nº managers asignados
    - Formulario inline: título, descripción, `starts_at`, `ends_at` (nullable), `is_active`
    - Validar que `ends_at`, si está presente, sea posterior o igual a `starts_at`
    - Acciones: crear, editar, eliminar (solo superadmin/admin_general), marcar activa/inactiva
    - Selector de `Construction_Manager` asignados (visible solo para superadmin/admin_general)
    - _Requisitos: 6.1, 6.3, 6.4, 6.5, 6.6, 6.8, 10.2, 10.3, 10.4_

    - [x] 7.2 Crear la vista Blade `resources/views/livewire/admin/construction-manager.blade.php`
    - Usar componentes Flux UI v2 para tabla, formulario y modales de confirmación
    - Seguir el patrón visual de `notice-manager.blade.php`
    - _Requisitos: 6.8_

    - [x] 7.3 Registrar ruta admin `/obras` en `routes/private.php`
    - Middleware `role:superadmin,admin_general,construction_manager`
    - _Requisitos: 6.8_

    - [x]\* 7.4 Escribir tests de feature Livewire para `AdminConstructionManager`
    - Verificar CRUD completo para superadmin
    - Verificar que `construction_manager` no puede eliminar obras
    - Verificar que `ends_at` anterior a `starts_at` retorna error de validación
    - Verificar que el selector de managers solo es visible para superadmin/admin_general
    - _Requisitos: 6.3, 6.4, 6.8, 10.3, 10.4_

- [x] 8. `NoticeDocumentController` y rutas de descarga
    - [x] 8.1 Crear `NoticeDocumentController` en `app/Http/Controllers/NoticeDocumentController.php`
    - `GET /notice-documents/{token}`: buscar `NoticeDocument` por token (404 si no existe)
    - Si `is_public = false` y usuario no autenticado: redirigir a login
    - Registrar `NoticeDocumentDownload` con `user_id` (o null), `ip_address` y `downloaded_at`
    - Servir el fichero con el `mime_type` y `filename` correctos
    - _Requisitos: 11.6, 11.7, 11.8, 12.1, 12.2_

    - [x] 8.2 Registrar ruta `notice-documents.download` en `routes/web.php`
    - Ruta pública `GET /notice-documents/{token}` sin middleware de auth (el controlador gestiona el acceso)
    - _Requisitos: 11.6, 11.7_

    - [x]\* 8.3 Escribir tests de feature para `NoticeDocumentController`
    - Verificar que documento público se sirve sin autenticación y registra la descarga
    - Verificar que documento privado redirige a login si no autenticado
    - Verificar que usuario autenticado descarga cualquier documento y registra la descarga con `user_id`
    - Verificar que token inválido retorna 404
    - _Requisitos: 11.6, 11.7, 11.8, 12.1, 12.2_

- [x] 9. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [x] 10. Mails de consultas de obras
    - [x] 10.1 Crear `ConstructionInquiryNotificationMail` en `app/Mail/ConstructionInquiryNotificationMail.php`
    - Constructor: `ConstructionInquiry $inquiry`, `Construction $construction`
    - Notifica al `Construction_Manager` de una nueva consulta recibida
    - Vista Blade `resources/views/mail/construction-inquiry-notification.blade.php`
    - _Requisitos: 8.5_

    - [x] 10.2 Crear `ConstructionInquiryReplyMail` en `app/Mail/ConstructionInquiryReplyMail.php`
    - Constructor: `ConstructionInquiry $inquiry`
    - Envía la respuesta al vecino a la dirección `$inquiry->email`
    - Vista Blade `resources/views/mail/construction-inquiry-reply.blade.php`
    - _Requisitos: 9.8, 9.9_

    - [x]\* 10.3 Escribir tests unitarios para los mailables
    - Verificar que `ConstructionInquiryNotificationMail` contiene el asunto y el nombre de la obra
    - Verificar que `ConstructionInquiryReplyMail` contiene el texto de la respuesta
    - _Requisitos: 8.5, 9.8_

- [x] 11. `PublicConstructionController` y rutas `/obrak`
    - [x] 11.1 Crear `PublicConstructionController` en `app/Http/Controllers/PublicConstructionController.php`
    - `index()`: lista obras con `Construction::active()->get()`, retorna vista
    - `show(string $slug)`: busca obra activa por slug (404 si no existe o inactiva), carga avisos con la `Construction_Tag` de esa obra, retorna vista
    - Patrón idéntico a `PublicVotingController`
    - _Requisitos: 7.1, 7.2, 7.4, 7.6_

    - [x] 11.2 Crear vistas Blade para el frontend público
    - `resources/views/public/constructions/index.blade.php` — listado de obras activas
    - `resources/views/public/constructions/show.blade.php` — detalle de obra con avisos y formulario de consulta
    - _Requisitos: 7.4, 7.5_

    - [x] 11.3 Registrar rutas públicas en `routes/public.php`
    - Grupos `eu` (`/obrak`, `/obrak/{slug}`) y `es` (`/obras`, `/obras/{slug}`) con middleware `auth`
    - Nombres: `constructions.eu`, `constructions.show.eu`, `constructions.es`, `constructions.show.es`
    - _Requisitos: 7.1, 7.2, 7.3_

    - [x]\* 11.4 Escribir tests de feature para `PublicConstructionController`
    - Verificar que `/obrak` lista solo obras activas y requiere autenticación
    - Verificar que `/obrak/{slug}` muestra los avisos de la `Construction_Tag` correcta
    - Verificar que obra inactiva o inexistente retorna 404
    - _Requisitos: 7.1, 7.2, 7.3, 7.6_

- [x] 12. `PublicConstructionInquiryForm` (Livewire)
    - [x] 12.1 Crear `PublicConstructionInquiryForm` en `app/Livewire/PublicConstructionInquiryForm.php`
    - Propiedades: `$constructionId`, `$name`, `$email`, `$subject`, `$message`
    - Pre-rellenar `name` y `email` del usuario autenticado en `mount()`
    - Validar que `name`, `email`, `subject`, `message` no estén vacíos y que `email` tenga formato válido
    - Al enviar: persistir `ConstructionInquiry`, despachar `ConstructionInquiryNotificationMail` a cada manager de la obra, mostrar mensaje de confirmación
    - _Requisitos: 8.2, 8.3, 8.4, 8.5, 8.6_

    - [x]\* 12.2 Escribir tests de feature Livewire para `PublicConstructionInquiryForm`
    - Verificar que campos obligatorios vacíos muestran errores de validación
    - Verificar que email inválido muestra error de validación
    - Verificar que envío correcto persiste la consulta y despacha el mail
    - _Requisitos: 8.2, 8.3, 8.4, 8.5_

- [x] 13. `AdminConstructionInquiryInbox` (Livewire)
    - [x] 13.1 Crear `AdminConstructionInquiryInbox` en `app/Livewire/AdminConstructionInquiryInbox.php`
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

    - [x]\* 13.2 Escribir tests de feature Livewire para `AdminConstructionInquiryInbox`
    - Verificar que `construction_manager` solo ve consultas de sus obras
    - Verificar que abrir una consulta no leída la marca como leída
    - Verificar que enviar respuesta persiste `reply` y despacha el mail
    - Verificar que responder de nuevo sobrescribe `reply` y actualiza `replied_at`
    - _Requisitos: 9.1, 9.2, 9.4, 9.7, 9.8, 9.10_

- [x] 14. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [x] 15. Tests de propiedad (property-based testing con Pest)
    - [x]\* 15.1 P1: Unicidad de slug en `NoticeTag` — `tests/Unit/NoticeTagSlugUniquenessTest.php`
    - Usar `fake()` para generar nombres que produzcan slugs duplicados, verificar rechazo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 1: Unicidad de slug en NoticeTag`
    - _Requisitos: 1.3, 1.4_

    - [x]\* 15.2 P2: Etiquetas disponibles según rol — `tests/Unit/ConstructionManagerTagsTest.php`
    - Usar `fake()` para generar managers con N obras, verificar que el conjunto de etiquetas seleccionables es exactamente las N `Construction_Tag` de esas obras con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 2: Etiquetas disponibles según rol`
    - _Requisitos: 2.3_

    - [x]\* 15.3 P3: Autorización de etiqueta en aviso — `tests/Feature/ConstructionManagerNoticeAuthTest.php`
    - Usar `fake()` para generar etiquetas no asignadas al manager, verificar HTTP 403 al guardar aviso con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 3: Autorización de etiqueta en aviso`
    - _Requisitos: 2.4_

    - [x]\* 15.4 P4: `canManageConstructions` por rol — `tests/Unit/UserCanManageConstructionsTest.php`
    - Usar `fake()->randomElement(Role::names())` para generar usuarios con roles aleatorios, verificar retorno correcto con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 4: canManageConstructions por rol`
    - _Requisitos: 5.6, 10.6_

    - [x]\* 15.5 P5: Validación de fechas de obra — `tests/Unit/ConstructionDateValidationTest.php`
    - Usar `fake()` para generar pares de fechas válidos e inválidos, verificar que `ends_at < starts_at` falla y el resto pasa con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 5: Validación de fechas de obra`
    - _Requisitos: 6.4_

    - [x]\* 15.6 P6: Observer crea `Construction_Tag` automáticamente — `tests/Feature/ConstructionObserverPropertyTest.php`
    - Usar `fake()` para generar obras con títulos aleatorios, verificar que existe exactamente una `NoticeTag` con slug `obra-{slug}` con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 6: Observer crea Construction_Tag automáticamente`
    - _Requisitos: 6.7_

    - [x]\* 15.7 P7: Filtro de obras activas — `tests/Unit/ConstructionActiveScopeTest.php`
    - Usar `fake()` para generar obras con fechas variadas, verificar que `scopeActive()` retorna exactamente las obras en rango con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 7: Filtro de obras activas`
    - _Requisitos: 7.1_

    - [x]\* 15.8 P8: Validación del formulario de consulta — `tests/Unit/ConstructionInquiryValidationTest.php`
    - Usar `fake()` para generar combinaciones de campos válidos e inválidos, verificar aceptación/rechazo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 8: Validación del formulario de consulta`
    - _Requisitos: 8.2, 8.3_

    - [x]\* 15.9 P9: Validación de documentos adjuntos — `tests/Unit/NoticeDocumentValidationTest.php`
    - Usar `fake()->randomElement()` para generar tipos MIME permitidos y no permitidos, verificar aceptación/rechazo con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 9: Validación de documentos adjuntos`
    - _Requisitos: 11.2, 11.3_

    - [x]\* 15.10 P10: Tracking de descargas — `tests/Feature/NoticeDocumentDownloadTrackingTest.php`
    - Usar `fake()` para generar descargas autenticadas y anónimas, verificar que existe exactamente un `NoticeDocumentDownload` con los campos correctos con `->repeat(2)`
    - Etiquetar: `// Feature: construction-management, Property 10: Tracking de descargas`
    - _Requisitos: 12.1, 12.2_

- [x] 16. Checkpoint final — Verificar calidad completa
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

- [x] 1. Ejecutar tarea 1 completa (1.1 -> 1.4*), validar y marcar.
- [x] 2. Ejecutar tarea 2 completa (2.1 -> 2.8*), validar y marcar.
- [x] 3. Ejecutar tarea 3 completa (3.1 -> 3.2*), validar y marcar.
- [x] 4. Ejecutar tarea 4 completa (4.1 -> 4.3*), validar y marcar.
- [x] 5. Ejecutar checkpoint 5 (tests hasta aquí) antes de seguir.
- [x] 6. Ejecutar tarea 6 completa (6.1 -> 6.4*), validar y marcar.
- [x] 7. Ejecutar tarea 7 completa (7.1 -> 7.4*), validar y marcar.
- [x] 8. Ejecutar tarea 8 completa (8.1 -> 8.3*), validar y marcar.
- [x] 9. Ejecutar checkpoint 9 (tests hasta aquí) antes de seguir.
- [x] 10. Ejecutar tarea 10 completa (10.1 -> 10.3*), validar y marcar.
- [x] 11. Ejecutar tarea 11 completa (11.1 -> 11.4*), validar y marcar.
- [x] 12. Ejecutar tarea 12 completa (12.1 -> 12.2*), validar y marcar.
- [x] 13. Ejecutar tarea 13 completa (13.1 -> 13.2*), validar y marcar.
- [x] 14. Ejecutar checkpoint 14 (tests hasta aquí) antes de seguir.
- [x] 15. Ejecutar tarea 15 (opcionales de property-based según prioridad acordada), validar y marcar.
- [x] 16. Ejecutar checkpoint final 16 con `composer quality` y cierre.

### Work Items
    [x] Al crear una obra se crea una etiqueta, el slug de esa etiqueta tiene que ser único, pero simple, el nombre que se le ha dado a la obra y si está repetido no dejar crear la obra, si se edita la obra, modificar el nombre de la etiqueta y el slug.
    [x] Si hay una obra en activo, añadir en el menú del front una entrada a esa obra
    [x] en el front de una obra activa, quitar la parte de las fechas, quitar la parte del formulario y moverlo a un modal, como en la parte superior del perfil de usario, un encabezado con el nombre de la obra y el botóon de Mezua bidali que saca el modal, no hace falta nombre ni email en el formulario, cogelos del usuario logueado, ya que para ver todo esto hay que estar logueado.
    [x] Si hay una obra en activo, en el front añadir otro bloque junto a bozketak y profila, cada uno de los tres debe ocupar un tercio de la pantalla, si solo hay dos, cada uno la mitad, y si solo se muestra uno, completo.
- [ ] Documentos y descargas: `app/Http/Controllers/NoticeDocumentController.php`, rutas en `routes/web.php`.
    [x] Asegurar que el flujo de obras activas sea coherente en backend y frontend: slug de etiqueta simple y único, navegación visible cuando aplica, cabecera + modal de contacto en detalle de obra y distribución responsive correcta de bloques públicos.
- [ ] Testing progresivo: `tests/Unit/**`, `tests/Feature/**`, `tests/Browser/**` (si se tocan flujos sensibles en Blade).
    [x] Mantener el patrón de creación automática de etiqueta en el observer de obras, pero aplicar slug simple derivado del título y unicidad estricta al crear/editar.
    [x] Resolver la entrada de menú del front mostrando todas las obras activas (no solo una), reutilizando el layout/componente de navegación existente y sin duplicar menús.
    [x] En la vista pública de obra activa, copiar el mismo header usado en perfil (estructura visual y CTA), y usar el botón Mezua bidali para abrir modal.
    [x] Aplicar el layout de bloques (obras/bozketak/profila) únicamente en home, con reglas 1-2-3 columnas según elementos visibles.
- [ ] Formato obligatorio tras cambios PHP: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`.
    [x] 1. Slug de etiqueta en obras: actualizar observer + validación de creación/edición para impedir colisiones y sincronizar nombre/slug de etiqueta al editar título de obra.
    [x] 2. Menú front condicional: añadir entradas para todas las obras activas cuando existan.
    [x] 3. Vista pública de obra activa: quitar bloque de fechas, sustituir cabecera por la del perfil, mover formulario a modal con botón Mezua bidali y usar datos del usuario autenticado sin inputs manuales de nombre/email.
    [x] 4. Bloques front (obras/bozketak/profila): aplicar distribución dinámica 1/2/3 columnas solo en home según número de bloques activos.
    [x] 5. Validación integral: tests Unit/Feature/Browser afectados + quality gate completa.

    [x] Backend obras/etiquetas: [app/Observers/ConstructionObserver.php](app/Observers/ConstructionObserver.php), [app/Livewire/AdminConstructionManager.php](app/Livewire/AdminConstructionManager.php) (si aplica), modelos/validaciones relacionadas.
    [x] Navegación front: layout/componente activo de menú público en [resources/views](resources/views).
    [x] Detalle obra front: [resources/views/public/constructions/show.blade.php](resources/views/public/constructions/show.blade.php), [app/Livewire/PublicConstructionInquiryForm.php](app/Livewire/PublicConstructionInquiryForm.php).
    [x] Bloques front portada/zona objetivo: vistas públicas donde conviven obras, bozketak y profila.
    [x] Tests: Unit/Feature en [tests/Unit](tests/Unit), [tests/Feature](tests/Feature) y Dusk focalizado en [tests/Browser](tests/Browser).

    [x] Test de regresión para slug único y sincronización de etiqueta al editar obra.
    [x] Test de navegación front para visibilidad condicional de entrada de obra activa.
    [x] Dusk focalizado para modal Mezua bidali y layout 1/2/3 bloques.
    [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`.
    [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact <tests-afectados>`.
    [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.
- [x] 1. Añadir/actualizar test Dusk focalizado para bloquear regresiones visuales y de interacción del bloque de documentos en avisos.
- [x] 2. Refactorizar el bloque de documentos en [resources/views/livewire/admin/notice-manager.blade.php](resources/views/livewire/admin/notice-manager.blade.php) para igualarlo al patrón de bidalketak (contenedor, listas de pendientes/subidos, acciones).
- [x] 3. Ajustar textos/keys de traducción solo si el nuevo diseño lo requiere.
- [x] 4. Ejecutar validación mínima afectada (Feature + Dusk focalizado) y formateo necesario.

### Work Items

- [x] UI objetivo: [resources/views/livewire/admin/notice-manager.blade.php](resources/views/livewire/admin/notice-manager.blade.php).
- [x] Patrón de referencia: [resources/views/livewire/admin/campaign-manager.blade.php](resources/views/livewire/admin/campaign-manager.blade.php).
- [x] Tests: [tests/Browser/AdminNoticeTagAndDocumentsUiTest.php](tests/Browser/AdminNoticeTagAndDocumentsUiTest.php) y tests Feature de [tests/Feature/AdminNoticeManagerTest.php](tests/Feature/AdminNoticeManagerTest.php) si aplica.

### Validation

- [x] Dusk focalizado del flujo afectado antes y después del cambio.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty` (si hay cambios PHP).
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test tests/Feature/AdminNoticeManagerTest.php --compact`.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.



### Zuzenketak

- [x] Al crear una obra se crea una etiqueta, el slug de esa etiqueta tiene que ser único, pero simple, el nombre que se le ha dado a la obra y si está repetido no dejar crear la obra, si se edita la obra, modificar el nombre de la etiqueta y el slug.
- [x] Si hay una obra en activo, añadir en el menú del front una entrada a esa obra
- [x] en el front de una obra activa, quitar la parte de las fechas, quitar la parte del formulario y moverlo a un modal, como en la parte superior del perfil de usario, un encabezado con el nombre de la obra y el botóon de Mezua bidali que saca el modal, no hace falta nombre ni email en el formulario, cogelos del usuario logueado, ya que para ver todo esto hay que estar logueado.
- [x] Si hay una obra en activo, en el front añadir otro bloque junto a bozketak y profila, cada uno de los tres debe ocupar un tercio de la pantalla, si solo hay dos, cada uno la mitad, y si solo se muestra uno, completo.

## Implementation Plan - Zuzenketak

### Goal

- [ ] Asegurar que el flujo de obras activas sea coherente en backend y frontend: slug de etiqueta simple y único, navegación visible cuando aplica, cabecera + modal de contacto en detalle de obra y distribución responsive correcta de bloques públicos.

### Technical Decisions

- [ ] Mantener el patrón de creación automática de etiqueta en el observer de obras, pero aplicar slug simple derivado del título y unicidad estricta al crear/editar.
- [ ] Resolver la entrada de menú del front mostrando todas las obras activas (no solo una), reutilizando el layout/componente de navegación existente y sin duplicar menús.
- [ ] En la vista pública de obra activa, copiar el mismo header usado en perfil (estructura visual y CTA), y usar el botón Mezua bidali para abrir modal.
- [ ] Aplicar el layout de bloques (obras/bozketak/profila) únicamente en home, con reglas 1-2-3 columnas según elementos visibles.

### Execution Steps

- [ ] 1. Slug de etiqueta en obras: actualizar observer + validación de creación/edición para impedir colisiones y sincronizar nombre/slug de etiqueta al editar título de obra.
- [ ] 2. Menú front condicional: añadir entradas para todas las obras activas cuando existan.
- [ ] 3. Vista pública de obra activa: quitar bloque de fechas, sustituir cabecera por la del perfil, mover formulario a modal con botón Mezua bidali y usar datos del usuario autenticado sin inputs manuales de nombre/email.
- [ ] 4. Bloques front (obras/bozketak/profila): aplicar distribución dinámica 1/2/3 columnas solo en home según número de bloques activos.
- [ ] 5. Validación integral: tests Unit/Feature/Browser afectados + quality gate completa.

### Work Items

- [ ] Backend obras/etiquetas: [app/Observers/ConstructionObserver.php](app/Observers/ConstructionObserver.php), [app/Livewire/AdminConstructionManager.php](app/Livewire/AdminConstructionManager.php) (si aplica), modelos/validaciones relacionadas.
- [ ] Navegación front: layout/componente activo de menú público en [resources/views](resources/views).
- [ ] Detalle obra front: [resources/views/public/constructions/show.blade.php](resources/views/public/constructions/show.blade.php), [app/Livewire/PublicConstructionInquiryForm.php](app/Livewire/PublicConstructionInquiryForm.php).
- [ ] Bloques front portada/zona objetivo: vistas públicas donde conviven obras, bozketak y profila.
- [ ] Tests: Unit/Feature en [tests/Unit](tests/Unit), [tests/Feature](tests/Feature) y Dusk focalizado en [tests/Browser](tests/Browser).

### Validation

- [ ] Test de regresión para slug único y sincronización de etiqueta al editar obra.
- [ ] Test de navegación front para visibilidad condicional de entrada de obra activa.
- [ ] Dusk focalizado para modal Mezua bidali y layout 1/2/3 bloques.
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`.
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact <tests-afectados>`.
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.


### Zuzenketak 2
- [x] Obrak, al botón activo ponerle confirmación (como en la columna publikatuta de iragarkiak )

## Implementation Plan - Zuzenketak 2

### Goal

- [x] Gehitu baieztapen-modala `Obrak` kudeaketan aktibo/inaktibo botoiari, `Iragarkiak` ataleko `publikatuta` zutabeko UX berdinarekin, nahi gabeko egoera-aldaketak saihesteko.

### Technical Decisions

- [x] Erabili lehendik dagoen `confirmPublish` eredua (`AdminNoticeManager`) erreferentzia gisa: `confirming*Id` + `action` + `show*Modal` propietateak eta `confirm/do/cancel` metodoen triada.
- [x] Mantendu egungo taulako botoi osagaia (`x-admin.action-link-confirm`) eta lotu klik-ekintza zuzena `toggleActive(...)`-tik `confirmToggleActive(...)`-ra.
- [x] Ez aldatu rol-baimenen logika; baieztapena UI/UX geruza bat izango da soilik (`canManageConstructions()` bera mantenduz).

### Execution Steps

- [x] 1. Gehitu `AdminConstructionManager`-en egoera-propietate berriak (`confirmingActiveId`, `activeAction`, `showActiveModal`) eta metodoak (`confirmToggleActive`, `doToggleActive`, `cancelToggleActive`).
- [x] 2. Eguneratu `construction-manager.blade.php` aktibo botoiaren `wire:click` ekintza baieztapen-fluxura eramateko.
- [x] 3. Gehitu baieztapen-modala Bladean (`activate/deactivate` testu dinamikoekin), `Iragarkiak`-eko modalaren egitura bisuala erreferentzia hartuta.
- [x] 4. Eguneratu test fokalizatuak: gutxienez Feature test bat baieztapenik gabe ez dela toggle egiten eta `doToggleActive`-rekin bai.

### Work Items

- [x] Livewire klasea: [app/Livewire/AdminConstructionManager.php](app/Livewire/AdminConstructionManager.php)
- [x] Livewire bista: [resources/views/livewire/admin/construction-manager.blade.php](resources/views/livewire/admin/construction-manager.blade.php)
- [x] Erreferentzia eredua: [app/Livewire/AdminNoticeManager.php](app/Livewire/AdminNoticeManager.php), [resources/views/livewire/admin/notice-manager.blade.php](resources/views/livewire/admin/notice-manager.blade.php)
- [x] Testak: [tests/Feature/AdminConstructionManagerTest.php](tests/Feature/AdminConstructionManagerTest.php)

### Validation

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminConstructionManagerTest.php`
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`


### Zuzenketak 3
- [x] Obraren frontetik bidalitako mezuetan subject-a bereizi: owner-rentzat `APP IZENA (settings) - OBRAREN IZENA mezua`; admin-entzat aurretik `[KONTSULA. OBRAREN IZENA]` gehituta.

## Implementation Plan - Zuzenketak 3

### Goal

- [x] Doitu obra-kontsulten email subject formatua rol-hartzailearen arabera, erabiltzaileari (owner) eta administrazioari testu koherente eta bereizia bidaliz.

### Technical Decisions

- [x] App izena `front_site_name` ezarpenetik ebatzi `App\Support\EmailSiteName::resolve()` erabiliz (fallback seguruarekin), kode bikoizketa saihesteko.
- [x] `PublicConstructionInquiryForm`-en bi subject eraiki: `owner` subject oinarria eta `admin` subjecta (`[KONTSULA. <obra>] ` + owner subject).
- [x] `ContactMessage.subject` eremuan owner subjecta gorde eta `SendAuthenticatedContactMessageAction`-era bi subjectak pasa (`userMailSubject`, `adminMailSubject`) gaur egungo arkitekturari jarraituz.

### Execution Steps

- [x] 1. Gehitu/eguneratu subject builder metodoak `PublicConstructionInquiryForm`-en app izena + obra izena + `mezua` formatuarekin.
- [x] 2. Eguneratu `submit()` metodoa `ContactMessage` sortzerakoan owner subjecta gordetzeko eta admin subject prefijatua bidaltzeko.
- [x] 3. Eguneratu test fokalizatua `tests/Feature/PublicConstructionInquiryFormTest.php` subject berriak egiaztatzeko.
- [x] 4. Exekutatu formateoa eta test fokalizatua Docker barruan.

### Work Items

- [x] Livewire logika: [app/Livewire/PublicConstructionInquiryForm.php](app/Livewire/PublicConstructionInquiryForm.php)
- [x] Site name helper: [app/Support/EmailSiteName.php](app/Support/EmailSiteName.php)
- [x] Testak: [tests/Feature/PublicConstructionInquiryFormTest.php](tests/Feature/PublicConstructionInquiryFormTest.php)

### Validation

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/PublicConstructionInquiryFormTest.php`

### Zuzenketak 4
- [x] `locations.code` kendu, `properties.code` gehitu, `admin/finkak/{id}`-n hasierako taula berria gehitu kodearekin, owner sortze-formetik eta `admin/jabeak` + jabeen PDFtik `ZBKIA` kendu, jabetza-zutabeetan propietate-kodea aurretik erakutsi, eta `admin/jabeak` zerrenda `Atariak` zutabearen arabera gorantz ordenatu hutsak amaieran utzita.

## Implementation Plan - Zuzenketak 4

### Goal

- [ ] Kokaleku-kodearen iturburu nagusia `properties.code` bihurtu, administrazio UI/PDFetan `ZBKIA` kentzea koherente egin, jabetza-kodea ikusgai utzi jabeen eta finken fluxuetan, eta `admin/jabeak` zerrenda `Atariak` zutabearen arabera gorantz ordenatu baliorik gabeko errenkadak amaieran utzita.

### Technical Decisions

- [ ] Egin schema-migrazio esplizituak: `locations.code` kentzeko forward-fix bat eta `properties.code` gehitzeko beste bat, datu-migrazioarekin batera (`locations.code` -> `properties.code`) behar denean.
- [ ] `admin/finkak/{location}` detail-ean bi geruza bereizi: goiko summary taula kokaleku-datuetarako eta azpiko jabetzen taula `code`, `name`, ehunekoak eta egoerarekin.
- [ ] Owner sortze-fluxuan `ownerId`/`ZBKIA` kendu UI + balidazio + ekintza mailan; ondorioz, `owners.id` eskuz ez ezartzea izango da lehenetsia, baldin eta `owners.id = user_id` migrazio zabalagoa aparteko fasera eramaten bada.
- [ ] Jabeen zerrendan eta PDFan `admin.owners.columns.num` zutabea kendu, `Ko-jabea1` izen osoaren ondoan hizkuntza erakutsi, eta `Ataariak/Garajeak/Trastelekuak/Lokalak` zutabeetan formatua `[property.code] property.name` bihurtu.
- [ ] `admin/jabeak` zerrendaren ordenazio lehenetsia `Atariak` zutabearen balio agregatuaren arabera egin, gorantz, eta baliorik ez duten jabeak amaieran utzi; horretarako query-mailako ordenazioa behar da, ez Bladeko post-prozesamendua.

### Execution Steps

- [ ] 1. Aztertu eta eguneratu eskema: `properties.code` gehitu, `locations.code` erreferentzia guztiak mapatu, eta datu-trantsizio segurua definitu.
- [ ] 2. Eguneratu `Location`, `Property`, `LocationDetail`, jabeen Livewire osagaia eta lotutako Bladeak propietate-kode berria erabiltzeko.
- [ ] 3. Kendu `ZBKIA` owner sortze-formetik, admin jabe-zerrendatik eta jabeen PDFtik; gehitu hizkuntza `Ko-jabea1` izenaren ondoan.
- [ ] 4. Eguneratu `admin/finkak/{id}` detail view-a: hasierako summary taula berria kokalekuaren datuekin eta jabetzen taulan `code` zutabea lehen tokian.
- [ ] 5. Eguneratu propietate-zerrenden formatua jabeen taulan/PDFan eta behar diren beste presentazio-puntuetan `[code] name` modura.
- [ ] 6. Ezarri `admin/jabeak` query ordenazioa `Atariak` zutabearen arabera gorantz, `NULL`/hutsik dauden balioak amaieran geratzeko moduan.
- [ ] 7. Balidazioa: Feature test fokalizatuak, PDF testak, behar diren Location/Owners/ordenazio testak, eta ondoren quality gatea.

### Work Items

- [ ] Modeloak eta migrazioak: [app/Models/Location.php](app/Models/Location.php), [app/Models/Property.php](app/Models/Property.php), [database/migrations/2026_04_09_100002_create_locations_table.php](database/migrations/2026_04_09_100002_create_locations_table.php), [database/migrations/2026_04_09_100003_create_properties_table.php](database/migrations/2026_04_09_100003_create_properties_table.php)
- [ ] Finken detaila: [resources/views/admin/locations/show.blade.php](resources/views/admin/locations/show.blade.php), [app/Livewire/Admin/LocationDetail.php](app/Livewire/Admin/LocationDetail.php), [resources/views/livewire/admin/locations/detail.blade.php](resources/views/livewire/admin/locations/detail.blade.php)
- [ ] Jabeen admin UI eta ordenazioa: [app/Livewire/Admin/Owners.php](app/Livewire/Admin/Owners.php), [app/Concerns/InteractsWithAdminOwners.php](app/Concerns/InteractsWithAdminOwners.php), [app/Support/AdminOwnersFilters.php](app/Support/AdminOwnersFilters.php), [app/Validations/OwnerFormValidation.php](app/Validations/OwnerFormValidation.php), [app/Actions/Owners/CreateOwnerAction.php](app/Actions/Owners/CreateOwnerAction.php), [resources/views/livewire/admin/owners/index.blade.php](resources/views/livewire/admin/owners/index.blade.php)
- [ ] Jabeen PDFa: [resources/views/pdf/owners/list.blade.php](resources/views/pdf/owners/list.blade.php) eta controller/query lotua
- [ ] Erreferentzia osagarriak: [app/Services/Messaging/MessageVariableResolver.php](app/Services/Messaging/MessageVariableResolver.php), [app/Livewire/Admin/Concerns/HandlesVotingOwnerModals.php](app/Livewire/Admin/Concerns/HandlesVotingOwnerModals.php), eta `location.code` erabiltzen duten gainerako puntuak
- [ ] Testak: [tests/Feature/AdminOwnersAndLocationsTest.php](tests/Feature/AdminOwnersAndLocationsTest.php), [tests/Feature/AdminOwnersPdfDownloadTest.php](tests/Feature/AdminOwnersPdfDownloadTest.php), eta behar diren jabetza/finka/ordenazio test berriak

### Validation

- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersPdfDownloadTest.php`
- [ ] Gehitu/eguneratu finken detailerako test fokalizatua (`LocationDetail` / route show)
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`


### Zuzenketak 5
- [ ] puede que haya muchos avisos con muchos documentos en el front de una obra, muestra solo la fecha y el título, ordenados de forma descendente. Con una flecha en cada una que al clickar despliegue su contenido al volver a clikcar lo esconda.Trackear esos click de abrir para saber qué owner las han abierto y cuales no. En el listado de admin/iragakiak, en los que el anuncio esté unido a una obra, añadir un botón con el icono de bars que muestre que owner han abierto dicho anuncio
- [ ] actualiza el skill de database-schema-mermaid
- [ ] en el front de bozketak añadir en cada bozketa la fecha incio y fin a la derecha
- [ ] repasa el home del front, la página iragakiak, la de bozketak, la de obras y el profila. Las fechas en euskera son año mes y dia y en castellano dia mes y año. Corrígelas.
- [ ] busca en todo el código dónde se utiliza coprop1_name o coprop2_name y preguntame si quiero cambiarlo por fullName1/fullName2 o no.

## Implementation Plan - Zuzenketak 5

### Goal

- [ ] Obrako iragarkien frontend zerrenda arindu: lehenetsita data + titulua bakarrik erakutsi, xehetasuna (edukia + dokumentuak) desplegable bihurtu, eta irekitze-ekintzak owner mailan trazatu.
- [ ] Admin `iragarkiak` zerrendan, obra bati lotutako iragarkietan bars ikonodun ekintza gehitu irekitze-egoera (zein owner-ek ireki duen/ez duen) kontsultatzeko.
- [ ] Front `bozketak` txarteletan hasiera/amaiera datak eskuinean erakutsi eta data-formatu lokala bateratu home/notices/bozketak/obrak/profila pantailetan.
- [ ] `coprop1_name` / `coprop2_name` erabileren inbentario osoa osatu eta erabiltzaileari baieztapena eskatu izen-aldaketa (`fullName1` / `fullName2`) egin aurretik.

### Technical Decisions

- [ ] Obrako iragarkien irekiera-trackinga DB taula dedikatu batekin egin (`notice_id`, `owner_id`, `opened_at`, `ip_address`, `user_id`), gutxienez owner+notice bikote bakarra bermatuz (irekia/ez-irekia egoera kalkulatzeko).
- [ ] Fronteko desplegable-interakzioa Alpine bidez egin (KISS): klik bakoitzean ireki/itxi, eta lehen irekitze baliodunean backend endpoint/Livewire action bidez tracking erregistroa sortu.
- [ ] Admin zerrendarako lehendik dagoen osagai eredua berrerabili (`table-row-actions` bars ikonoa), obrarekin lotutako iragarkietan bakarrik aktibatuta.
- [ ] Data-formatua helper bakarrean zentralizatu (locale-aware), bikoizketak saihesteko eta Blade guztietan irizpide bera aplikatzeko: EU = `Y/m/d`, ES = `d/m/Y` (eta datetime kasuetan baliokidearekin).
- [ ] `database-schema-mermaid` skill fitxategian ERD eguneraketa egin aldaketa berean (taula berria + erlazioak).

### Execution Steps

- [ ] 1. Azpiegitura: notice-open tracking taula/migrazioa + modeloa + erlazioak (`Notice`, `Owner`, beharrezkoa bada `User`) eta indize/unikotasun arauak.
- [ ] 2. Obraren frontend detaila: iragarkiak `date+title` laburpen zerrendan renderizatu, ordena beherakorra mantendu, eta item bakoitzean togglable edukia/dokumentuak gehitu.
- [ ] 3. Tracking lotura: iragarki bat irekitzean owner-aren irekiera erregistratu (idempotentea) eta segurtasuna/baimenak egiaztatu (`auth` + owner ebazpena).
- [ ] 4. Admin `iragarkiak`: obra-tag lotura duten errenkadetan bars botoia gehitu eta modal/panelean owner ireki/ez-ireki zerrenda erakutsi.
- [ ] 5. Front `bozketak`: txartel bakoitzean hasiera/amaiera datak eskuineko blokera gehitu, responsive portaera zainduta.
- [ ] 6. Data-formatu bateratzea: home, iragarkiak, bozketak, obrak eta profila pantailetan helper berria edo osagai bateratua aplikatu.
- [ ] 7. `coprop1_name`/`coprop2_name` erabileren txostena prestatu eta erabiltzaileari galdetu izen-aldaketa exekutatu nahi duen ala ez (baieztapenik gabe ez aldatu).
- [ ] 8. `database-schema-mermaid` skill ERD eguneratu eta sintaxia balidatu.
- [ ] 9. Balidazioa: test Unit/Feature/Dusk fokalizatuak + pint + quality gate.

### Work Items

- [ ] Obrako frontend iragarkiak: [resources/views/public/constructions/show.blade.php](resources/views/public/constructions/show.blade.php), controller lotura [app/Http/Controllers/PublicConstructionController.php](app/Http/Controllers/PublicConstructionController.php).
- [ ] Admin iragarkiak: [app/Livewire/AdminNoticeManager.php](app/Livewire/AdminNoticeManager.php), [resources/views/livewire/admin/notice-manager.blade.php](resources/views/livewire/admin/notice-manager.blade.php), eta osagai laguntzailea [resources/views/components/admin/table-row-actions.blade.php](resources/views/components/admin/table-row-actions.blade.php).
- [ ] Bozketen frontend: [resources/views/livewire/front/public-votings.blade.php](resources/views/livewire/front/public-votings.blade.php).
- [ ] Data-formatu helburuak: [resources/views/components/front/notice-card.blade.php](resources/views/components/front/notice-card.blade.php), [resources/views/public/constructions/index.blade.php](resources/views/public/constructions/index.blade.php), [resources/views/public/constructions/show.blade.php](resources/views/public/constructions/show.blade.php), [resources/views/public/profile.blade.php](resources/views/public/profile.blade.php), eta lotutako helper/controller puntuak.
- [ ] Tracking datu-egitura berria: `database/migrations/**`, `app/Models/**`, eta beharrezko route/controller edo Livewire endpoint-a.
- [ ] Mermaid ERD skilla: [.github/skills/database-schema-mermaid/SKILL.md](.github/skills/database-schema-mermaid/SKILL.md).
- [ ] `coprop*` inbentarioa (jada bildua) eta erabaki-puntua: app/resources/tests/database/lang multzoan dauden erabilerak.

### Validation

- [ ] Unit/Feature test fokalak tracking eta admin zerrenda berriarentzat (ireki/ez-ireki kalkulua, baimenak, idempotentzia).
- [ ] Dusk test fokalizatua obrako iragarki desplegablearen UXerako eta admin bars ekintzarako (Blade aldaketa sentikorra denez).
- [ ] Dusk test fokalizatua bozketen txarteletako data-bloke berriarentzat eta frontend data-formatu lokaletarako smoke egiaztapenarekin.
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact <tests-afectados>`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`