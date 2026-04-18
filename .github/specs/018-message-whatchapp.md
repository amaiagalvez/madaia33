
para el envío de campañas por whatchapp usar "Click to Chat"
Es una URL oficial de WhatsApp que abre una conversación con un mensaje predefinido.

👉 Formato básico:

https://wa.me/<numero>?text=<mensaje>
🧩 1. Ejemplo real
https://wa.me/34612345678?text=Hola%20quiero%20información

📌 Importante:

Número en formato internacional (sin + ni espacios)
Mensaje codificado en URL

2. Cómo generar el mensaje correctamente

Debes hacer URL encoding del texto:

<a href="https://wa.me/34612345678?text=Hola%20quiero%20info" target="_blank">
    Escríbenos por WhatsApp
</a>

Cuando el canal sea whatchapp, en su listado admin/campanas/{id} añadir una columna con el teléfono y al lado del teléfono un botón para enviar el mensaje.

Despues de darle al botón, marcar el mensaje como enviado.

Hay alguna manera de que pueda trakear el mensje enviado?

Si hay enlaces en el teexto o documentos, cómo los puedo enviar?

## Implementation Plan: envío por lote WhatsApp

### Goal

- Implementar el envío manual por WhatsApp usando Click to Chat en el detalle de campaña.
- Añadir soporte para incluir enlaces del texto y enlaces de documentos de la campaña en el mensaje predefinido.
- Marcar el destinatario como "enviado" al pulsar el botón de WhatsApp y permitir trazabilidad mínima de ese clic administrativo.

### Technical Decisions

- Se usará `https://wa.me/<numero>?text=<mensaje>` con número normalizado en formato internacional sin `+` ni espacios.
- El mensaje predefinido se construirá con:
    - cuerpo de campaña (EU/ES según fallback existente)
    - enlaces detectados en el texto
    - enlaces de documentos de campaña como URL de descarga/seguimiento
- Click to Chat no permite confirmar entrega/lectura real en WhatsApp. Solo se puede trazar que el admin abrió el enlace de envío.
- La trazabilidad se guardará en `campaign_tracking_events` con un evento específico para WhatsApp (ej. `whatsapp_sent`).

### Execution Steps

- [x] 1. Extender `AdminCampaignDetail` para generar URL Click to Chat por destinatario.
- [x] 2. Incluir en el texto del mensaje los enlaces del contenido y los enlaces de documentos adjuntos.
- [x] 3. Añadir acción Livewire para registrar el evento de envío manual al hacer clic en el botón.
- [x] 4. Actualizar la tabla de destinatarios en `admin/campanas/{id}` para mostrar teléfono + botón WhatsApp.
- [x] 5. Añadir/actualizar traducciones EU/ES para etiquetas, acciones y nuevo tipo de evento.
- [x] 6. Cubrir con tests (Feature + Unit) la generación de URL, inclusión de enlaces/documentos y marcado como enviado.

### Work Items

- [x] `app/Livewire/AdminCampaignDetail.php`
- [x] `resources/views/livewire/admin/campaign-detail.blade.php`
- [x] `lang/eu/campaigns.php`
- [x] `lang/es/campaigns.php`
- [x] tests de campañas en `tests/Feature/` y `tests/Unit/`

### Validation

- [x] TDD-based implementation when possible
- [x] Run `vendor/bin/pint --dirty`
- [x] Run focused tests with `php artisan test --compact` for touched files
- [x] Run quality gate in Docker: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`

# Zuzenketak

- [x] controlar qué pasa al pinchar en el botón enviar: si falla, registrar el error en owner, bloquear el teléfono al tercer intento y no volver a enviar WhatsApp a ese contacto.
- [x] al enviar una campaña por whatsapp, `DispatchCampaignJob` ez du `SendCampaignMessageJob` jaurti behar: recipients sortu bakarrik, `pending` egoeran utzi, eta kanalak berak gidatuko du bidaltzea (zuzendu: `shouldDispatchSendJob` + `resetExistingRecipients` + `createAndDispatchRecipients` metodo laburtuak).
- [x] aplicar la opción 1 para mejorar el flujo actual: intentar primero `whatsapp://send?...` y mantener `https://wa.me/...` como fallback, reduciendo en algunos dispositivos la pantalla intermedia sin prometer automatización real.

- [x] necesito un botón en admin/campanas/{id} que en las campañas de whatchapp active el envío de los mensajes y que los vaya enviando uno por uno (es decir, que haga como si se pinchara el botón "WHatsApp bidez bidali" de uno en uno pero sin que el usuario de la aplicación tenga que hacerlo manualmente)

## Implementation Plan

### Goal

- Añadir en `admin/campanas/{id}` una acción de lote para campañas `whatsapp` que recorra las destinatarias pendientes una a una reutilizando el flujo actual de Click to Chat.
- Evitar duplicar reglas de negocio: validación de contacto, bloqueo tras errores, tracking `whatsapp_sent` y marcado de estado deberán seguir pasando por una única ruta de dominio.
- Mostrar progreso del envío por lote en la misma pantalla para que la persona administradora sepa cuántas destinatarias quedan por procesar.

### Technical Decisions

- Click to Chat sigue siendo un flujo cliente-navegador, no una API de envío real. Por tanto, esta acción automatizará la apertura secuencial de URLs `wa.me` desde la UI, pero no podrá confirmar entrega/lectura real en WhatsApp.
- El comportamiento de “enviar una a una” se implementará reusando la lógica de `sendWhatsappMessage()` mediante una extracción interna común que devuelva la siguiente URL válida y aplique tracking/estado de forma consistente.
- La cola del lote se gestionará en `AdminCampaignDetail` con estado Livewire explícito (por ejemplo ids pendientes, contador procesado, bandera de ejecución) y un evento de navegador para abrir o reutilizar la ventana de WhatsApp de forma secuencial.
- Los contactos bloqueados o inválidos se saltarán automáticamente dentro del lote, manteniendo la misma política actual de errores para no reintroducir ramas divergentes.
- El botón de lote solo se mostrará en campañas `whatsapp` con recipients `pending` no bloqueados.

### Execution Steps

- [x]   1. Refactorizar la lógica actual de envío individual de WhatsApp para exponer una ruta común reutilizable por envío individual y envío por lote.
- [x]   2. Añadir en `AdminCampaignDetail` el estado y acciones Livewire del lote (`start`, `send next`, `finish/cancel`) preservando autorizaciones y evitando duplicar consultas innecesarias.
- [x]   3. Actualizar la vista `admin/campanas/{id}` con el nuevo botón, feedback de progreso y estados de carga, manteniendo los selectores `data-*` estables para tests.
- [x]   4. Añadir/ajustar traducciones EU/ES para la acción masiva, mensajes de progreso, finalización y casos sin destinatarias válidas.
- [x]   5. Cubrir el flujo con tests mínimos afectados: Feature/Livewire para lote completo, contactos bloqueados/omitidos y conservación del botón individual.
- [x]   6. Evaluar test Browser/Dusk focalizado si el flujo cliente secuencial necesita validar la interacción real del navegador para evitar regresiones de UI.

### Work Items

- [x] `app/Livewire/AdminCampaignDetail.php`
- [x] `app/Livewire/Concerns/HandlesCampaignDetailWhatsapp.php`
- [x] `resources/views/livewire/admin/campaign-detail.blade.php`
- [x] `lang/eu/campaigns.php`
- [x] `lang/es/campaigns.php`
- [x] `tests/Feature/AdminCampaignDetailTest.php`
- [x] `tests/Browser/` (no ha sido necesario añadirlo en esta iteración)

### Validation

- [x] TDD-based implementation when possible, priorizando tests Unit/Feature rápidos donde no haga falta navegador.
- [x] Run `vendor/bin/pint --dirty` dentro de Docker y con usuario no root.
- [x] Run focused tests with `php artisan test --compact` sobre los archivos tocados.
- [x] Run quality gate in Docker: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.
- [x] Revisar VS Code Problems en los archivos tocados antes de cerrar.

## Execution Notes: opción 1

### Goal

- Mejorar el botón actual de WhatsApp para intentar abrir primero la app nativa mediante `whatsapp://` y conservar `wa.me` como fallback web.

### Execution Steps

- [x]   1. Extender el generador de URLs para soportar esquema app + esquema web con la misma normalización de teléfono y codificación de mensaje.
- [x]   2. Ajustar el flujo Livewire para despachar ambos destinos manteniendo tracking y estado sin duplicar reglas.
- [x]   3. Actualizar la vista para intentar apertura nativa y caer a WhatsApp Web si la app no toma el foco.
- [x]   4. Añadir tests unitarios/feature mínimos y validar con Pint, tests focalizados y quality gate.

### Work Items

- [x] `app/Support/Messaging/WhatsappClickToChatUrl.php`
- [x] `app/Livewire/Concerns/HandlesCampaignDetailWhatsapp.php`
- [x] `resources/views/livewire/admin/campaign-detail.blade.php`
- [x] `tests/Unit/WhatsappClickToChatUrlTest.php`

### Validation

- [x] Run `vendor/bin/pint --dirty`
- [x] Run focused tests with `php artisan test --compact tests/Unit/WhatsappClickToChatUrlTest.php tests/Feature/AdminCampaignDetailTest.php`
- [x] Run quality gate in Docker: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`
- [x] Revisar VS Code Problems en los archivos tocados antes de cerrar.


# Zuzenketa
- [ ] Si se trackea un enlace o un documento, marcar también como abierto el mensaje, si aun no lo estñá
- [ ] en el envio de emails, asegurate de que no se envían más de 10 mensajes por minuto
- giggsey/libphonenumber-for-php
