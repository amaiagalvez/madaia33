# Requirements Document

## Introduction

Sistema de envío de mensajes multicanal (email, SMS, WhatsApp, Telegram) dirigido a propietarias de la comunidad. Permite al administrador componer un mensaje con asunto, cuerpo de texto y documentos adjuntos opcionales, seleccionar el canal de envío y filtrar los destinatarios por portal, planta de garaje o enviar a todos. Los mensajes admiten variables de personalización (p. ej. `**nombre**`) que se sustituyen por los datos reales de cada propietaria antes del envío. Todos los envíos quedan registrados con tracking completo de aperturas, clics en enlaces y descargas de documentos.

## Glosario

- **Messaging_System**: El sistema de envío de mensajes multicanal descrito en este documento.
- **Campaign**: Un envío concreto creado por un administrador, con asunto, cuerpo, canal, filtro de destinatarios y documentos adjuntos opcionales.
- **Recipient**: Cada propietaria (Owner) a la que se envía un mensaje dentro de una Campaign. Una propietaria puede generar hasta dos Recipient (coprop1 y coprop2) si ambas tienen email o teléfono registrado.
- **Channel**: El medio de entrega del mensaje. Valores posibles: `email`, `sms`, `whatsapp`, `telegram`.
- **Document**: Fichero subido por el administrador y adjuntado a una Campaign. Puede ser público o privado.
- **Tracking_Event**: Registro de una interacción de un Recipient con un mensaje: apertura, clic en enlace o descarga de documento.
- **Variable**: Marcador de posición en el cuerpo del mensaje con formato `**nombre_variable**` que el Messaging_System sustituye por el valor real de la propietaria antes del envío.
- **Owner**: Modelo existente que representa a una propietaria. Contiene `coprop1_name`, `coprop1_email`, `coprop1_phone`, `coprop1_telegram_id`, `coprop2_name`, `coprop2_email`, `coprop2_phone`, `coprop2_telegram_id`. Los campos `telegram_id` son nuevos y deben añadirse al modelo. Se añadirán también campos de validez de contacto y contadores de errores consecutivos por canal.
- **Preferred_Locale**: Idioma preferido de la propietaria para recibir comunicaciones. Valores posibles: `eu` (euskera), `es` (castellano), o `null` (sin preferencia). Almacenado en el Owner.
- **Location**: Modelo existente que representa un portal o planta de garaje.
- **PropertyAssignment**: Modelo existente que relaciona Owner con Property y, a través de ésta, con Location.
- **Admin**: Usuario con acceso al panel de administración que puede crear y enviar Campaigns. Existen tres variantes con permisos distintos:
    - `superadmin`: puede crear y enviar Campaigns a cualquier destinatario (`all`, cualquier portal, cualquier garaje), sin restricción alguna.
    - `admin_general`: puede crear y enviar Campaigns a todos los destinatarios (`all`), a cualquier portal y a cualquier garaje, sin restricción de asignación.
    - `admin_comunidad`: solo puede crear y enviar Campaigns a los portales o garajes que tenga asignados en `managedLocations`. No puede seleccionar el filtro `all` ni portales/garajes fuera de su asignación.

---

## Requisitos

### Requisito 1: Composición de una Campaign

**User Story:** Como administrador, quiero componer un mensaje con asunto, cuerpo de texto y documentos adjuntos opcionales, para poder comunicarme con las propietarias de forma estructurada.

#### Criterios de aceptación

1. THE Messaging_System SHALL permitir al Admin crear una Campaign con los campos: asunto en euskera y castellano (obligatorio al menos uno, máx. 255 caracteres cada uno), cuerpo de texto en euskera y castellano (obligatorio al menos uno), canal de envío (obligatorio, uno de: `email`, `sms`, `whatsapp`, `telegram`) y filtro de destinatarios (obligatorio, uno de: `all`, `portal:{code}`, `garage:{code}`).
2. THE Messaging_System SHALL permitir al Admin adjuntar uno o más Documents a una Campaign durante la composición.
3. WHEN el Admin sube un Document, THE Messaging_System SHALL almacenar el fichero y registrar si es público o privado.
4. IF el asunto supera 255 caracteres, THEN THE Messaging_System SHALL mostrar un error de validación e impedir el envío.
5. IF el cuerpo del mensaje está vacío, THEN THE Messaging_System SHALL mostrar un error de validación e impedir el envío.
6. THE Messaging_System SHALL permitir al Admin previsualizar el mensaje con las Variables sustituidas por valores de ejemplo antes de confirmar el envío.

---

### Requisito 2: Soporte bilingüe (euskera / castellano)

**User Story:** Como administrador, quiero redactar los mensajes en euskera y castellano, para que cada propietaria los reciba en su idioma preferido.

#### Criterios de aceptación

1. THE Messaging_System SHALL permitir al Admin introducir asunto y cuerpo del mensaje en euskera (`eu`) y en castellano (`es`), siendo obligatorio rellenar al menos uno de los dos idiomas.
2. WHEN el Messaging_System envía un mensaje a un Recipient, THE Messaging_System SHALL seleccionar la versión del idioma que coincida con el `preferred_locale` del Owner asociado.
3. IF el Owner no tiene `preferred_locale` definido, THEN THE Messaging_System SHALL enviar el mensaje en ambos idiomas en el mismo envío, con el contenido en euskera primero.
4. IF el Admin solo ha redactado el mensaje en un idioma y el `preferred_locale` del Owner es el otro idioma, THEN THE Messaging_System SHALL enviar la versión disponible sin interrumpir el envío.
5. THE Owner SHALL almacenar un campo `preferred_locale` con los valores posibles `eu`, `es` o `null`.

---

### Requisito 3: Variables de personalización

**User Story:** Como administrador, quiero usar variables como `**nombre**` en el cuerpo del mensaje, para que cada propietaria reciba el mensaje personalizado con sus datos.

#### Criterios de aceptación

1. THE Messaging_System SHALL reconocer Variables con el formato `**nombre_variable**` dentro del asunto y el cuerpo del mensaje.
2. WHEN el Messaging_System envía un mensaje a un Recipient, THE Messaging_System SHALL sustituir cada Variable por el valor correspondiente del Owner asociado antes de la entrega.
3. THE Messaging_System SHALL soportar al menos las Variables: `**nombre**` (nombre de la propietaria), `**propiedad**` (nombre o nombres de las propiedades activas), `**portal**` (código o códigos de Location de las propiedades activas).
4. WHEN un Owner tiene una sola PropertyAssignment activa, THE Messaging_System SHALL sustituir `**propiedad**` por el nombre de esa propiedad y `**portal**` por el código de su Location.
5. WHEN un Owner tiene múltiples PropertyAssignments activas, THE Messaging_System SHALL sustituir `**propiedad**` por la lista de nombres de todas sus propiedades activas separados por coma.
6. WHEN un Owner tiene múltiples PropertyAssignments activas, THE Messaging_System SHALL sustituir `**portal**` por la lista de códigos de Location únicos de todas sus propiedades activas separados por coma.
7. IF una Variable no tiene valor disponible para un Recipient concreto, THEN THE Messaging_System SHALL sustituirla por una cadena vacía sin interrumpir el envío.
8. FOR ALL Owners con datos completos, el proceso de sustitución de Variables SHALL producir un mensaje sin marcadores `**...**` residuales.

---

### Requisito 4: Filtrado de destinatarios

**User Story:** Como administrador, quiero filtrar los destinatarios por portal, planta de garaje o enviar a todos, para segmentar las comunicaciones según la ubicación de las propiedades.

#### Criterios de aceptación

1. WHEN el Admin selecciona el filtro `all`, THE Messaging_System SHALL incluir como Recipients a todas las Owners con al menos un contacto válido (email o teléfono según el canal) en sus asignaciones activas.
2. WHEN el Admin selecciona el filtro `portal:{code}`, THE Messaging_System SHALL incluir únicamente a las Owners cuya propiedad activa pertenezca al Location con ese código y tipo `portal`.
3. WHEN el Admin selecciona el filtro `garage:{code}`, THE Messaging_System SHALL incluir únicamente a las Owners cuya propiedad activa pertenezca al Location con ese código y tipo `garage`.
4. THE Messaging_System SHALL generar un Recipient por cada contacto válido de la Owner (coprop1 y coprop2) que disponga del dato requerido por el canal seleccionado.
5. WHEN el filtro no produce ningún Recipient, THE Messaging_System SHALL mostrar una advertencia al Admin e impedir el envío.

---

### Requisito 5: Envío multicanal

**User Story:** Como administrador, quiero enviar mensajes por email, SMS, WhatsApp o Telegram, para alcanzar a las propietarias a través del canal que mejor se adapte a cada situación.

#### Criterios de aceptación

1. WHEN el canal es `email`, THE Messaging_System SHALL enviar el mensaje al `coprop1_email` y, si existe, al `coprop2_email` de cada Owner destinataria.
2. WHEN el canal es `sms`, THE Messaging_System SHALL enviar el mensaje al `coprop1_phone` y, si existe, al `coprop2_phone` de cada Owner destinataria.
3. WHEN el canal es `whatsapp`, THE Messaging_System SHALL enviar el mensaje al `coprop1_phone` y, si existe, al `coprop2_phone` de cada Owner destinataria a través de la API de WhatsApp configurada.
4. WHEN el canal es `telegram`, THE Messaging_System SHALL enviar el mensaje al `coprop1_telegram_id` y, si existe, al `coprop2_telegram_id` de cada Owner destinataria a través del proveedor de Telegram configurado.
5. THE Messaging_System SHALL delegar el envío por SMS y WhatsApp a una abstracción (interfaz `SmsProvider` / `WhatsAppProvider`) cuya implementación concreta se definirá en el diseño técnico.
6. THE Messaging_System SHALL encolar los envíos como jobs de Laravel Queue para no bloquear la interfaz de usuario.
7. IF un envío individual falla, THEN THE Messaging_System SHALL registrar el error en el Tracking_Event correspondiente y continuar con el resto de Recipients.
8. WHEN todos los jobs de una Campaign han sido procesados, THE Messaging_System SHALL actualizar el estado de la Campaign a `completed`.

---

### Requisito 6: Documentos adjuntos y control de acceso

**User Story:** Como administrador, quiero adjuntar documentos a los mensajes y controlar si son públicos o privados, para compartir información relevante con las propietarias de forma segura.

#### Criterios de aceptación

1. THE Messaging_System SHALL generar una URL única y rastreable por Recipient para cada Document adjunto a una Campaign.
2. WHEN un Recipient accede a la URL de un Document público, THE Messaging_System SHALL servir el fichero sin requerir autenticación.
3. WHEN un Recipient accede a la URL de un Document privado, THE Messaging_System SHALL requerir que el usuario esté autenticado antes de servir el fichero.
4. IF un usuario no autenticado intenta acceder a un Document privado, THEN THE Messaging_System SHALL redirigirle a la página de login.
5. THE Messaging_System SHALL registrar un Tracking_Event de tipo `download` cuando un Recipient descarga un Document.
6. THE Messaging_System SHALL aceptar ficheros de tipo PDF, DOCX, XLSX, JPG y PNG con un tamaño máximo de 20 MB por fichero.

---

### Requisito 7: Tracking de interacciones

**User Story:** Como administrador, quiero ver estadísticas de apertura, clics y descargas de cada envío, para evaluar el alcance e impacto de las comunicaciones.

#### Criterios de aceptación

1. WHEN un Recipient abre un mensaje de email, THE Messaging_System SHALL registrar un Tracking_Event de tipo `open` asociado al Recipient y a la Campaign.
2. WHEN un Recipient hace clic en un enlace del cuerpo del mensaje, THE Messaging_System SHALL registrar un Tracking_Event de tipo `click` con la URL destino y redirigir al Recipient a dicha URL.
3. WHEN un Recipient descarga un Document adjunto, THE Messaging_System SHALL registrar un Tracking_Event de tipo `download` con el identificador del Document.
4. THE Messaging_System SHALL mostrar al Admin, por Campaign, un panel de métricas con: total enviados, aperturas únicas, clics únicos, descargas únicas y fallos de entrega.
5. THE Messaging_System SHALL calcular y mostrar el porcentaje de aperturas únicas respecto al total de enviados, el porcentaje de clics únicos respecto al total de enviados, el porcentaje de descargas únicas respecto al total de enviados, y el porcentaje de fallos respecto al total de enviados.
6. THE Messaging_System SHALL mostrar al Admin una tabla de detalle por Recipient con columnas: nombre, contacto, estado de entrega, abierto (sí/no), número de clics, número de descargas y fecha de última actividad.
7. THE Messaging_System SHALL permitir al Admin expandir cada fila de Recipient para ver el listado completo de sus Tracking_Events con: tipo de evento, URL o documento, fecha y hora, e IP.
8. IF el mismo Recipient genera el mismo tipo de Tracking_Event más de una vez, THE Messaging_System SHALL registrar cada ocurrencia pero contabilizar solo una en las métricas únicas y porcentajes.
9. THE Messaging_System SHALL permitir al Admin reenviar la Campaign a todos los Recipients que no hayan abierto el mensaje (sin evento `open` registrado).
10. WHEN el Admin ejecuta el reenvío a no abiertos, THE Messaging_System SHALL crear una nueva Campaign en estado `draft` con el mismo contenido, canal y documentos, con el filtro de destinatarios restringido a los Recipients sin apertura de la Campaign original, para que el Admin pueda revisarla y confirmar el envío.

---

### Requisito 8: Gestión y estado de Campaigns

**User Story:** Como administrador, quiero ver el historial de envíos y su estado, para hacer seguimiento de las comunicaciones realizadas.

#### Criterios de aceptación

1. THE Messaging_System SHALL mantener un estado para cada Campaign con los valores: `draft`, `sending`, `completed`, `failed`.
2. THE Messaging_System SHALL mostrar al Admin un listado de Campaigns ordenado por fecha de creación descendente, con columnas: asunto, canal, filtro, estado, número de Recipients, fecha de envío.
3. WHEN una Campaign está en estado `draft`, THE Messaging_System SHALL permitir al Admin editarla o eliminarla.
4. WHEN una Campaign está en estado `sending` o `completed`, THE Messaging_System SHALL impedir la edición del contenido y mostrar las métricas de tracking.
5. THE Messaging_System SHALL permitir al Admin duplicar una Campaign existente para reutilizar su configuración en un nuevo envío.

---

### Requisito 10: Validez de contactos y detección de errores consecutivos

**User Story:** Como administrador, quiero que el sistema detecte automáticamente los contactos que fallan repetidamente, para mantener la calidad de los datos y saber qué propietarias no están recibiendo los mensajes.

#### Criterios de aceptación

1. WHEN un envío individual falla para un Recipient, THE Messaging_System SHALL incrementar el contador de errores consecutivos del canal correspondiente en el Owner (`coprop1_email_error_count`, `coprop1_phone_error_count`, `coprop2_email_error_count`, `coprop2_phone_error_count`).
2. WHEN un envío individual tiene éxito para un Recipient, THE Messaging_System SHALL resetear a cero el contador de errores consecutivos del canal y slot correspondiente en el Owner.
3. IF el contador de errores consecutivos de un contacto alcanza 3, THEN THE Messaging_System SHALL marcar ese contacto como no válido (`coprop1_email_invalid`, `coprop1_phone_invalid`, `coprop2_email_invalid`, `coprop2_phone_invalid`) en el Owner.
4. WHEN un contacto está marcado como no válido, THE Messaging_System SHALL excluirlo de futuros envíos como si no tuviera ese dato de contacto.
5. THE Messaging_System SHALL mostrar al Admin una lista de Owners con al menos un contacto marcado como no válido, con columnas: nombre, contacto inválido, canal, número de errores consecutivos y fecha del último error.
6. THE Messaging_System SHALL permitir al Admin marcar manualmente un contacto como válido de nuevo (por ejemplo, tras corregir el email o teléfono), reseteando el contador de errores a cero.
7. WHEN el Admin modifica el email de coprop1 o coprop2 en la ficha del Owner, THE Messaging_System SHALL resetear automáticamente el contador de errores de email y el flag de invalidez del slot correspondiente.
8. WHEN el Admin modifica el teléfono de coprop1 o coprop2 en la ficha del Owner, THE Messaging_System SHALL resetear automáticamente el contador de errores de teléfono y el flag de invalidez del slot correspondiente.

---

### Requisito 11: Autorización y seguridad

**User Story:** Como administrador, quiero que solo los usuarios con permisos adecuados puedan crear y enviar mensajes, para proteger la privacidad de las propietarias.

#### Criterios de aceptación

1. THE Messaging_System SHALL restringir la creación, edición y envío de Campaigns a usuarios con rol `superadmin`, `admin_general` o `admin_comunidad`.
2. WHEN un usuario sin los roles requeridos intenta acceder a la gestión de Campaigns, THE Messaging_System SHALL devolver una respuesta HTTP 403.
3. WHEN un Admin con rol `superadmin` crea una Campaign, THE Messaging_System SHALL permitir seleccionar cualquier filtro de destinatarios: `all`, cualquier `portal:{code}` y cualquier `garage:{code}`.
4. WHEN un Admin con rol `admin_general` crea una Campaign, THE Messaging_System SHALL permitir seleccionar cualquier filtro de destinatarios: `all`, cualquier `portal:{code}` y cualquier `garage:{code}`.
5. WHEN un Admin con rol `admin_comunidad` crea una Campaign, THE Messaging_System SHALL mostrar en el selector de filtro únicamente los Locations asignados en `managedLocations` de ese Admin, excluyendo la opción `all`.
6. IF un Admin con rol `admin_comunidad` intenta enviar una Campaign con el filtro `all` o con un `portal:{code}` / `garage:{code}` no incluido en sus `managedLocations`, THEN THE Messaging_System SHALL rechazar la operación con HTTP 403.
7. THE Messaging_System SHALL generar tokens de tracking únicos e impredecibles (mínimo 32 bytes de entropía) para cada Recipient y Campaign.
8. IF un token de tracking no corresponde a ningún Recipient activo, THEN THE Messaging_System SHALL devolver HTTP 404 sin revelar información sobre la Campaign.
9. THE Messaging_System SHALL registrar en el log de auditoría el Admin que creó y envió cada Campaign, junto con la marca de tiempo.

---

### Requisito 12: Programación de envíos

**User Story:** Como administrador, quiero programar una campaña para que se envíe en una fecha y hora concreta, para que los mensajes lleguen en el momento más adecuado.

#### Criterios de aceptación

1. THE Messaging_System SHALL permitir al Admin establecer una fecha y hora de envío programado (`scheduled_at`) al crear o editar una Campaign en estado `draft`.
2. IF una Campaign tiene `scheduled_at` definido, THEN THE Messaging_System SHALL cambiar su estado a `scheduled` y no enviarla hasta que llegue esa fecha y hora.
3. THE Messaging_System SHALL procesar las Campaigns en estado `scheduled` mediante un comando programado de Laravel Scheduler que se ejecute cada minuto y despache `DispatchCampaignJob` para las campañas cuyo `scheduled_at` haya llegado.
4. WHEN una Campaign está en estado `scheduled`, THE Messaging_System SHALL permitir al Admin cancelar el envío programado, devolviendo la Campaign a estado `draft`.
5. THE Messaging_System SHALL mostrar en el listado de Campaigns la fecha y hora de envío programado cuando corresponda.
6. IF el Admin no establece `scheduled_at`, THEN THE Messaging_System SHALL enviar la Campaign inmediatamente al confirmar el envío, como hasta ahora.

---

### Requisito 13: Plantillas de mensaje

**User Story:** Como administrador, quiero guardar mensajes como plantillas reutilizables, para no tener que reescribir comunicados habituales como convocatorias de junta o avisos de mantenimiento.

#### Criterios de aceptación

1. THE Messaging_System SHALL permitir al Admin guardar una Campaign como plantilla, con un nombre descriptivo.
2. THE Messaging_System SHALL mostrar al Admin un listado de plantillas disponibles con columnas: nombre, canal, fecha de creación.
3. WHEN el Admin selecciona una plantilla al crear una nueva Campaign, THE Messaging_System SHALL rellenar automáticamente el asunto (eu/es), el cuerpo (eu/es) y el canal con los valores de la plantilla.
4. THE Messaging_System SHALL permitir al Admin editar y eliminar plantillas existentes.
5. Una plantilla no tiene destinatarios ni estado de envío — es únicamente contenido reutilizable.

---

### Requisito 14: Contador de recipients antes de enviar

**User Story:** Como administrador, quiero ver cuántos recipients recibirán el mensaje antes de confirmar el envío, para evitar sorpresas y verificar que el filtro es correcto.

#### Criterios de aceptación

1. WHEN el Admin selecciona un canal y un filtro de destinatarios en el formulario de composición, THE Messaging_System SHALL calcular y mostrar en tiempo real el número de recipients que recibirán el mensaje.
2. THE Messaging_System SHALL desglosar el contador por slot: cuántos son coprop1 y cuántos son coprop2.
3. THE Messaging_System SHALL excluir del contador los contactos marcados como inválidos para el canal seleccionado.
4. IF el contador es cero, THE Messaging_System SHALL mostrar una advertencia e impedir el envío.
5. THE Messaging_System SHALL actualizar el contador automáticamente cada vez que el Admin cambie el canal o el filtro, sin necesidad de recargar la página.
