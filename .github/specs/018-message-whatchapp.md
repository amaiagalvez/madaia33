
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

## Implementation Plan

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

- [ ] kontrolar qué pasa al pinchar en el botón enviar. Si da error marcar en en registro del owner y al tercer intento 