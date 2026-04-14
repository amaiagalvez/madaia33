# Plan de Implementación: Mass Messaging

## Visión general

Implementación incremental del sistema de mensajería masiva multicanal. Las capas se construyen de abajo hacia arriba: esquema de base de datos → contratos e interfaces → servicios de lógica pura → jobs de cola → autorización → tracking HTTP → UI Livewire → tests de propiedad.

## Tareas

- Nota obligatoria transversal para todas las tareas y subtareas: mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).

- [ ] 1. Migraciones y modelos base
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 1.1 Crear migración de modificación de `owners` (campos `coprop1_telegram_id`, `coprop2_telegram_id`, `preferred_locale`, contadores y flags de validez de contacto)
    - Añadir `coprop1_telegram_id`, `coprop2_telegram_id`, `preferred_locale` nullable
    - Añadir `coprop1_email_error_count`, `coprop1_email_invalid`, `coprop1_phone_error_count`, `coprop1_phone_invalid` (y equivalentes coprop2) con defaults 0/false
    - Añadir `last_contact_error_at` nullable timestamp
    - Actualizar `$fillable` y `casts()` en `app/Models/Owner.php`
    - _Requisitos: 2.5, 5.4, 10.1, 10.2, 10.3_

    - [ ] 1.2 Crear modelo `Campaign` con su migración y factory
    - Tabla `campaigns` con todos los campos del ERD (status, channel, recipient_filter, scheduled_at, sent_at, softDeletes)
    - Estado `scheduled` añadido al enum: `draft | scheduled | sending | completed | failed`
    - Relaciones: `recipients()`, `documents()`, `createdBy()`, `template()` (nullable)
    - _Requisitos: 1.1, 8.1, 12.1, 12.2_

    - [ ] 1.3 Crear modelo `CampaignRecipient` con su migración y factory
    - Tabla `campaign_recipients` (slot, contact, tracking_token, status, error_message, softDeletes)
    - Relaciones: `campaign()`, `owner()`, `trackingEvents()`
    - _Requisitos: 4.4, 9.7_

    - [ ] 1.4 Crear modelo `CampaignDocument` con su migración y factory
    - Tabla `campaign_documents` (filename, path, mime_type, size_bytes, is_public, softDeletes)
    - Relación: `campaign()`
    - _Requisitos: 1.2, 1.3, 6.6_

    - [ ] 1.5 Crear modelo `CampaignTrackingEvent` con su migración y factory
    - Tabla `campaign_tracking_events` (event_type, url, ip_address, sin softDeletes)
    - Relaciones: `recipient()`, `document()` (nullable)
    - _Requisitos: 7.1, 7.2, 7.3_

    - [ ]\* 1.6 Escribir tests unitarios para los modelos y sus relaciones
    - Verificar que `Campaign` tiene las relaciones correctas
    - Verificar que `CampaignRecipient` tiene las relaciones correctas
    - Verificar que `CampaignTrackingEvent` no usa SoftDeletes
    - _Requisitos: 8.1, 7.1_

    - [ ] 1.7 Añadir reset automático de errores de contacto en el Observer de Owner
    - En `OwnerAuditObserver` (o nuevo `OwnerContactObserver`), evento `updating`: detectar cambios en `coprop1_email`, `coprop2_email`, `coprop1_phone`, `coprop2_phone` con `isDirty()` y resetear el contador e invalidez del campo correspondiente
    - _Requisitos: 10.7, 10.8_

    - [ ]\* 1.8 Escribir tests de feature para el reset automático de errores
    - Verificar que cambiar `coprop1_email` resetea `coprop1_email_error_count` y `coprop1_email_invalid`
    - Verificar que cambiar `coprop1_phone` resetea `coprop1_phone_error_count` y `coprop1_phone_invalid`
    - Verificar que cambiar un campo no resetea los contadores de otros campos
    - _Requisitos: 10.7, 10.8_

- [ ] 2. Interfaces de canal y EmailProvider
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 2.1 Crear las interfaces de canal en `app/Contracts/Messaging/`
    - `ChannelProvider` con método `send(CampaignRecipient $recipient, string $subject, string $body): void`
    - `EmailProvider`, `SmsProvider`, `WhatsAppProvider`, `TelegramProvider` extendiendo `ChannelProvider`
    - _Requisitos: 5.5_

    - [ ] 2.2 Implementar `LaravelMailEmailProvider` en `app/Services/Messaging/`
    - Crear `CampaignMail` (Mailable) que recibe asunto, cuerpo y documentos adjuntos
    - Implementar `EmailProvider` delegando en `CampaignMail`
    - Incluir pixel de tracking 1×1 y enlaces de tracking en el cuerpo HTML
    - Registrar binding `EmailProvider → LaravelMailEmailProvider` en `AppServiceProvider`
    - _Requisitos: 5.1, 7.1, 7.2_

    - [ ]\* 2.3 Escribir tests unitarios para `CampaignMail`
    - Verificar que el asunto y el cuerpo se renderizan correctamente
    - Verificar que el pixel de tracking está presente en el HTML
    - _Requisitos: 5.1, 7.1_

- [ ] 3. Servicios: RecipientResolver y MessageVariableResolver
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 3.1 Implementar `RecipientResolver` en `app/Services/Messaging/RecipientResolver.php`
    - Filtro `all`: Owners con `activeAssignments` y contacto válido para el canal
    - Filtro `portal:{code}`: Owners cuya propiedad activa pertenece al Location con ese código y tipo `portal`
    - Filtro `garage:{code}`: ídem con tipo `garage`
    - Generar hasta dos entradas por Owner (coprop1, coprop2) según contacto disponible
    - _Requisitos: 4.1, 4.2, 4.3, 4.4_

    - [ ]\* 3.2 Escribir tests unitarios para `RecipientResolver`
    - Verificar filtro `all` con Owners con y sin contacto válido
    - Verificar filtro `portal:{code}` excluye Owners de otros portales
    - Verificar filtro `garage:{code}` excluye Owners de otros garajes
    - Verificar generación de 0, 1 o 2 Recipients según contactos disponibles
    - _Requisitos: 4.1, 4.2, 4.3, 4.4_

    - [ ] 3.3 Implementar `MessageVariableResolver` en `app/Services/Messaging/MessageVariableResolver.php`
    - Sustituir `**nombre**`, `**propiedad**`, `**portal**` con los valores del Owner/slot
    - Variables sin valor → cadena vacía
    - Garantizar que no queden marcadores `**...**` residuales
    - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.5, 3.6, 3.7, 3.8_

    - [ ]\* 3.4 Escribir tests unitarios para `MessageVariableResolver`
    - Verificar sustitución de cada variable con datos completos
    - Verificar sustitución con múltiples propiedades activas
    - Verificar que variables sin valor producen cadena vacía
    - _Requisitos: 3.3, 3.4, 3.5, 3.6, 3.7_

- [ ] 4. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 5. Jobs: DispatchCampaignJob y SendCampaignMessageJob
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 5.1 Implementar `DispatchCampaignJob` en `app/Jobs/Messaging/DispatchCampaignJob.php`
    - Cambiar `Campaign.status` a `sending`
    - Resolver Recipients con `RecipientResolver`
    - Crear `CampaignRecipient` por cada uno con token único (32 bytes de entropía, hex 64 chars)
    - Encolar `SendCampaignMessageJob` por cada `CampaignRecipient`
    - _Requisitos: 5.6, 8.1, 9.7_

    - [ ] 5.2 Implementar `SendCampaignMessageJob` en `app/Jobs/Messaging/SendCampaignMessageJob.php`
    - Cargar `CampaignRecipient` con `Campaign` y `Owner`
    - Seleccionar idioma según `preferred_locale` (con fallback según requisito 2.3 y 2.4)
    - Sustituir variables con `MessageVariableResolver`
    - Delegar en el `ChannelProvider` correspondiente al canal
    - WHEN éxito: registrar `CampaignTrackingEvent` de entrega y resetear contador de errores del contacto en Owner
    - WHEN fallo: registrar error, incrementar contador de errores consecutivos del contacto en Owner; si llega a 3, marcar contacto como inválido
    - Actualizar `Campaign.status = 'completed'` cuando todos los recipients estén procesados
    - _Requisitos: 2.2, 2.3, 2.4, 5.7, 5.8, 10.1, 10.2, 10.3_

    - [ ]\* 5.3 Escribir tests de feature para los jobs con `Queue::fake()`
    - Verificar que `DispatchCampaignJob` encola un `SendCampaignMessageJob` por recipient
    - Verificar que `SendCampaignMessageJob` registra `CampaignTrackingEvent` en caso de fallo
    - Verificar que el contador de errores se incrementa en el Owner tras un fallo
    - Verificar que el contador se resetea a 0 tras un envío exitoso
    - Verificar que el contacto se marca como inválido al llegar a 3 errores consecutivos
    - Verificar que `Campaign.status` pasa a `completed` cuando todos los recipients están procesados
    - _Requisitos: 5.6, 5.7, 5.8, 10.1, 10.2, 10.3_

- [ ] 6. CampaignPolicy y autorización
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 6.1 Crear `CampaignPolicy` en `app/Policies/CampaignPolicy.php`
    - `viewAny`, `create`: roles `superadmin`, `admin_general`, `admin_comunidad`
    - `update`, `delete`: solo en estado `draft`
    - `send`: para `admin_comunidad`, filtro `all` → 403; filtro portal/garaje fuera de `managedLocations` → 403
    - `duplicate`: cualquier admin autorizado
    - Registrar la policy en `AppServiceProvider`
    - _Requisitos: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6_

    - [ ]\* 6.2 Escribir tests de feature para `CampaignPolicy`
    - Verificar que `superadmin` y `admin_general` pueden usar filtro `all`
    - Verificar que `admin_comunidad` recibe 403 con filtro `all`
    - Verificar que `admin_comunidad` recibe 403 con portal/garaje fuera de sus `managedLocations`
    - Verificar que `update` y `delete` solo funcionan en estado `draft`
    - _Requisitos: 9.1, 9.2, 9.3, 9.4, 9.5, 9.6_

- [ ] 7. TrackingController y rutas de tracking
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 7.1 Crear `TrackingController` en `app/Http/Controllers/Messaging/TrackingController.php`
    - `GET /track/open/{token}` → registra evento `open`, devuelve pixel 1×1 transparente
    - `GET /track/click/{token}` → registra evento `click`, redirige a `?url=`
    - `GET /track/doc/{token}/{document}` → registra evento `download`, sirve fichero (auth requerida si privado)
    - Token inválido → HTTP 404 sin revelar información de la Campaign
    - _Requisitos: 6.1, 6.2, 6.3, 6.4, 6.5, 7.1, 7.2, 7.3, 9.8_

    - [ ] 7.2 Registrar las rutas de tracking en `routes/web.php`
    - Rutas públicas para `open` y `click`
    - Ruta con middleware `auth` para `doc` cuando el documento es privado
    - _Requisitos: 6.2, 6.3, 6.4_

    - [ ]\* 7.3 Escribir tests de feature para `TrackingController`
    - Verificar que `open` devuelve imagen 1×1 y registra el evento
    - Verificar que `click` redirige a la URL y registra el evento
    - Verificar que `doc` sirve el fichero público sin autenticación
    - Verificar que `doc` privado redirige a login si no autenticado
    - Verificar que token inválido devuelve 404
    - _Requisitos: 6.2, 6.3, 6.4, 7.1, 7.2, 7.3, 9.8_

- [ ] 8. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 9. Componente Livewire AdminCampaignManager
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 9.1 Crear `AdminCampaignManager` en `app/Livewire/AdminCampaignManager.php`
    - Lista paginada de Campaigns con columnas: asunto, canal, filtro, estado, nº recipients, fecha envío, fecha programada
    - Formulario inline: asunto eu/es, cuerpo eu/es, canal, filtro, adjuntos, fecha/hora programada (opcional), selector de plantilla
    - Contador en tiempo real de recipients válidos al cambiar canal o filtro, desglosado por coprop1/coprop2
    - Acciones: crear, editar (solo draft/scheduled), eliminar (solo draft/scheduled), duplicar, enviar, programar, cancelar programación
    - Validación: asunto máx. 255 chars, al menos un idioma en asunto y cuerpo, canal y filtro obligatorios
    - Validación de adjuntos: tipos permitidos PDF, DOCX, XLSX, JPG, PNG; tamaño máximo 20 MB por fichero
    - Selector de plantilla opcional que autocompleta asunto, cuerpo y canal al seleccionar plantilla
    - Filtro de destinatarios restringido según rol (`admin_comunidad` solo ve sus Locations)
    - Previsualización con variables sustituidas por valores de ejemplo
    - Advertencia cuando el filtro no produce ningún recipient
    - _Requisitos: 1.1, 1.2, 1.4, 1.5, 1.6, 4.5, 8.2, 8.3, 8.4, 8.5, 9.1, 9.5, 12.1, 12.4, 12.5, 13.3, 14.1, 14.2, 14.3, 14.4, 14.5_

    - [ ] 9.2 Crear la vista Blade `resources/views/livewire/admin/campaign-manager.blade.php`
    - Usar componentes Flux UI v2 para tabla, formulario, modales de confirmación
    - Seguir el patrón visual de `notice-manager.blade.php`
    - _Requisitos: 1.1, 8.2_

    - [ ] 9.3 Añadir la ruta y el enlace de navegación al panel de administración
    - Registrar la ruta en `routes/web.php` con middleware de autenticación y rol
    - _Requisitos: 9.1, 9.2_

    - [ ]\* 9.4 Escribir tests de feature Livewire para `AdminCampaignManager`
    - Verificar que la lista muestra las Campaigns correctamente
    - Verificar que el formulario valida asunto, cuerpo, canal y filtro
    - Verificar que editar y eliminar solo funcionan en estado `draft`
    - Verificar que duplicar crea una nueva Campaign en estado `draft`
    - Verificar que `admin_comunidad` no ve la opción `all` en el filtro
    - _Requisitos: 1.1, 1.4, 1.5, 8.2, 8.3, 8.4, 8.5, 9.5_

- [ ] 10. Componente Livewire AdminCampaignDetail
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 10.1 Crear `AdminCampaignDetail` en `app/Livewire/AdminCampaignDetail.php`
    - Panel de métricas agregadas: total enviados, aperturas únicas + %, clics únicos + %, descargas únicas + %, fallos + %
    - Tabla de detalle por Recipient: nombre, contacto, estado, abierto (sí/no), nº clics, nº descargas, última actividad
    - Fila expandible por Recipient con listado de Tracking_Events (tipo, URL/documento, fecha/hora, IP)
    - Contabilizar métricas únicas (un mismo tipo de evento por Recipient cuenta como 1 en los porcentajes)
    - Botón "Reenviar a no abiertos": crea una nueva Campaign en `draft` con mismo contenido/canal/documentos y destinatarios filtrados a los Recipients sin evento `open`
    - _Requisitos: 7.4, 7.5, 7.6, 7.7, 7.8, 7.9, 7.10_

    - [ ] 10.2 Crear la vista Blade `resources/views/livewire/admin/campaign-detail.blade.php`
    - Usar componentes Flux UI v2 para el panel de métricas (tarjetas con número + porcentaje) y tabla de detalle
    - Seguir el patrón visual y de interacción de `notice-manager.blade.php`
    - Botón "Reenviar a no abiertos" visible solo cuando la Campaign está en estado `completed` y hay Recipients sin apertura
    - _Requisitos: 7.4, 7.5, 7.6, 7.7, 7.9_

    - [ ]\* 10.3 Escribir tests de feature Livewire para `AdminCampaignDetail`
    - Verificar que las métricas y porcentajes se calculan correctamente
    - Verificar que múltiples eventos del mismo tipo por Recipient cuentan como 1 en los porcentajes
    - Verificar que el detalle expandible muestra todos los eventos del Recipient
    - Verificar que "Reenviar a no abiertos" crea una nueva Campaign en `draft` solo con los Recipients sin apertura
    - Verificar que el botón no aparece si todos los Recipients han abierto el mensaje
    - _Requisitos: 7.4, 7.5, 7.6, 7.7, 7.8, 7.9, 7.10_

- [ ] 11. Componente Livewire AdminInvalidContactsList
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 11.1 Crear `AdminInvalidContactsList` en `app/Livewire/AdminInvalidContactsList.php`
    - Lista de Owners con al menos un contacto marcado como inválido (`_invalid = true`)
    - Columnas: nombre, slot (coprop1/coprop2), contacto, canal, errores consecutivos, fecha último error
    - Acción "Marcar como válido": resetea el flag `_invalid` y el contador `_error_count` del contacto
    - _Requisitos: 10.4, 10.5, 10.6_

    - [ ] 11.2 Crear la vista Blade `resources/views/livewire/admin/invalid-contacts-list.blade.php`
    - Usar componentes Flux UI v2 para la tabla y botón de acción por fila
    - Seguir el patrón visual y de interacción de `notice-manager.blade.php`
    - _Requisitos: 10.5, 10.6_

    - [ ] 11.3 Añadir la ruta y enlace de navegación al panel de administración
    - _Requisitos: 10.5_

    - [ ]\* 11.4 Escribir tests de feature Livewire para `AdminInvalidContactsList`
    - Verificar que solo aparecen Owners con al menos un contacto inválido
    - Verificar que "Marcar como válido" resetea el flag y el contador
    - _Requisitos: 10.5, 10.6_

- [ ] 12. Plantillas de mensaje y envíos programados
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ] 12.1 Crear modelo `CampaignTemplate` con su migración y factory
    - Tabla `campaign_templates` (name, subject_eu, subject_es, body_eu, body_es, channel, created_by_user_id, softDeletes)
    - _Requisitos: 13.1, 13.2_

    - [ ] 12.2 Crear `AdminCampaignTemplateManager` en `app/Livewire/AdminCampaignTemplateManager.php`
    - Lista de plantillas: nombre, canal, fecha de creación
    - Formulario crear/editar: nombre, asunto eu/es, cuerpo eu/es, canal
    - Acción eliminar
    - _Requisitos: 13.1, 13.2, 13.4_

    - [ ] 12.3 Crear la vista Blade `resources/views/livewire/admin/campaign-template-manager.blade.php`
    - Usar componentes Flux UI v2 para tabla y formulario CRUD
    - Seguir el patrón visual y de interacción de `notice-manager.blade.php`
    - _Requisitos: 13.2, 13.4_

    - [ ] 12.4 Añadir la ruta y el enlace de navegación del gestor de plantillas al panel de administración
    - Registrar la ruta en `routes/web.php` con middleware de autenticación y rol
    - _Requisitos: 9.1, 9.2, 13.2_

    - [ ] 12.5 Crear el comando `campaigns:dispatch-scheduled` y registrarlo en el Scheduler
    - Buscar Campaigns con `status = 'scheduled'` y `scheduled_at <= now()`
    - Despachar `DispatchCampaignJob` para cada una
    - Registrar en `routes/console.php` con `->everyMinute()`
    - _Requisitos: 12.3_

    - [ ]\* 12.6 Escribir tests para plantillas y envíos programados
    - Verificar que aplicar una plantilla rellena asunto, cuerpo y canal en el formulario
    - Verificar que el comando despacha solo las campañas cuyo `scheduled_at` ha llegado
    - Verificar transición de estado `scheduled` a `sending` cuando el comando inicia el despacho de campañas vencidas
    - Verificar que cancelar una programación devuelve la Campaign a `draft`
    - _Requisitos: 12.3, 12.4, 13.3_

- [ ] 13. Checkpoint — Verificar que todos los tests pasan hasta aquí
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [ ] 14. Tests de propiedad (property-based testing con Pest)
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - [ ]\* 14.1 P1: Sustitución completa de variables — `tests/Unit/Messaging/MessageVariableResolverTest.php`
    - Usar `fake()` para generar Owners con datos aleatorios completos, ejecutar con `->repeat(2)`
    - Verificar ausencia de `**...**` en el resultado
    - Etiquetar: `// Feature: mass-messaging, Property 1: Sustitución completa de variables`
    - _Requisitos: 3.8_

    - [ ]\* 14.2 P2: Selección de idioma según preferred_locale — `tests/Unit/Messaging/LocaleSelectionTest.php`
    - Usar `it()->with(['eu', 'es'])` para cubrir ambos locales, verificar idioma seleccionado
    - Etiquetar: `// Feature: mass-messaging, Property 2: Selección de idioma según preferred_locale`
    - _Requisitos: 2.2_

    - [ ]\* 14.3 P3: Fallback de idioma cuando solo hay una versión — `tests/Unit/Messaging/LocaleSelectionTest.php`
    - Usar dataset con combinaciones de locale disponible vs preferido, verificar que se envía la versión disponible
    - Etiquetar: `// Feature: mass-messaging, Property 3: Fallback de idioma cuando solo hay una versión`
    - _Requisitos: 2.4_

    - [ ]\* 14.4 P4: Unicidad de tokens de tracking — `tests/Unit/Messaging/TrackingTokenTest.php`
    - Generar N tokens con `bin2hex(random_bytes(32))`, verificar unicidad y longitud ≥ 64 chars hex con `->repeat(2)`
    - Etiquetar: `// Feature: mass-messaging, Property 4: Unicidad de tokens de tracking`
    - _Requisitos: 9.7_

    - [ ]\* 14.5 P5: Resolución de destinatarios por filtro de portal/garaje — `tests/Unit/Messaging/RecipientResolverTest.php`
    - Usar factories para crear Owners en múltiples portales, verificar que el filtro `portal:{code}` excluye Owners de otros portales con `->repeat(2)`
    - Etiquetar: `// Feature: mass-messaging, Property 5: Resolución de destinatarios por filtro de portal/garaje`
    - _Requisitos: 4.2, 4.3_

    - [ ]\* 14.6 P6: Generación de Recipients por contactos válidos — `tests/Unit/Messaging/RecipientResolverTest.php`
    - Usar dataset con 0, 1 y 2 contactos válidos, verificar que se generan exactamente N Recipients
    - Etiquetar: `// Feature: mass-messaging, Property 6: Generación de Recipients por contactos válidos`
    - _Requisitos: 4.4_

    - [ ]\* 14.7 P7: Métricas únicas de tracking — `tests/Unit/Messaging/TrackingMetricsTest.php`
    - Usar `fake()->numberBetween(2, 10)` para generar K eventos del mismo tipo, verificar conteo único = 1 con `->repeat(2)`
    - Etiquetar: `// Feature: mass-messaging, Property 7: Métricas únicas de tracking`
    - _Requisitos: 7.6_

    - [ ]\* 14.8 P8: Autorización por rol en filtros de Campaign — `tests/Feature/Messaging/CampaignPolicyTest.php`
    - Usar factories para generar filtros fuera de `managedLocations` para `admin_comunidad`, verificar HTTP 403 con `->repeat(2)`
    - Etiquetar: `// Feature: mass-messaging, Property 8: Autorización por rol en filtros de Campaign`
    - _Requisitos: 9.5, 9.6_

    - [ ]\* 14.9 P9: Validación de longitud de asunto — `tests/Unit/Messaging/CampaignValidationTest.php`
    - Usar dataset con strings de longitud 255, 256 y aleatoria > 255, verificar que > 255 es rechazado y ≤ 255 es aceptado
    - Etiquetar: `// Feature: mass-messaging, Property 9: Validación de longitud de asunto`
    - _Requisitos: 1.4_

- [ ] 15. Checkpoint final — Verificar calidad completa
    - Mantener diseño del panel tomando como referencia el listado de avisos del panel (`notice-manager`).
    - Asegurarse de que todos los tests pasan. Ejecutar `composer quality` dentro de Docker. Consultar al usuario si surgen dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Los tests de propiedad (tarea 14) usan Pest nativo con `fake()`, datasets y `->repeat(2)` — sin dependencias adicionales
- Cada tarea referencia los requisitos específicos para trazabilidad
- Los checkpoints garantizan validación incremental antes de avanzar a la siguiente capa
