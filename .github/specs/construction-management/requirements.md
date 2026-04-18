# Documento de Requisitos: Gestión de Obras y Etiquetas en Avisos

## Introducción

Esta feature introduce dos capacidades relacionadas:

1. **Sistema de etiquetas en avisos**: permite categorizar los avisos (`Notice`) con etiquetas (`NoticeTag`). Solo el administrador puede crear etiquetas nuevas. Los avisos con etiqueta "obra" se muestran visualmente destacados en el frontend público. En el listado de avisos añadir una columna para mostrar las equitsas de cada aviso.

2. **Nuevo rol "director de obra" (`construction_manager`)**: usuario con acceso restringido al panel de administración, capaz de crear y gestionar avisos pero limitado a usar únicamente la etiqueta "obra". En conversaciones posteriores se irán añadiendo más funcionalidades propias de este rol.

3. **Gestión de obras (`Construction`)**: modelo base que representa una obra activa. Puede haber varias obras simultáneas. El director de obra estará asociado a una o varias obras.

El sistema existente usa Laravel 13, Livewire 4, Flux UI v2, Pest v4 y PHP 8.4. Los modelos relevantes ya existentes son `Notice`, `NoticeLocation`, `Role` y `User`. Los componentes Livewire existentes son `AdminNoticeManager` y `PublicNotices`.

---

## Glosario

- **System**: la aplicación web de gestión comunitaria.
- **Admin**: usuario con rol `superadmin` o `admin_general`.
- **Community_Admin**: usuario con rol `admin_comunidad`.
- **Construction_Manager**: usuario con el nuevo rol `construction_manager` (director de obra).
- **NoticeTag**: entidad que representa una etiqueta asignable a un aviso (p. ej. "reunión", "obra").
- **Notice**: aviso existente en el sistema, con soporte bilingüe (euskera/castellano).
- **Construction**: entidad que representa una obra activa en la comunidad.
- **AdminNoticeManager**: componente Livewire que gestiona los avisos en el panel de administración.
- **PublicNotices**: componente Livewire que muestra los avisos en el frontend público.
- **Tag_Obra**: la etiqueta con slug `obra`, reservada para avisos relacionados con obras.
- **Construction_Tag**: la etiqueta `NoticeTag` creada automáticamente al crear una obra, con slug `obra-{slug-obra}` (p. ej. `obra-rehabilitacion-fachada`) y nombre `Obra: {title}`. Es la etiqueta específica que identifica los avisos de esa obra concreta.

---

## Requisitos

### Requisito 1: Gestión de etiquetas (NoticeTag)

**User Story:** Como admin, quiero crear y gestionar etiquetas para los avisos, para poder categorizar el contenido de forma estructurada.

#### Criterios de aceptación

1. THE System SHALL almacenar las etiquetas (`NoticeTag`) con los campos: `id`, `slug` (único, generado automáticamente desde el nombre), `name_eu` (nombre en euskera), `name_es` (nombre en castellano, opcional), `created_at`, `updated_at`, `deleted_at`.
2. THE System SHALL incluir en el seeder inicial las etiquetas con slugs `reunion` y `obra`.
3. WHEN un Admin crea una nueva etiqueta, THE System SHALL validar que el `slug` resultante sea único antes de persistirla.
4. IF el `slug` generado ya existe, THEN THE System SHALL retornar un error de validación indicando que el nombre ya está en uso.
5. THE System SHALL permitir a un Admin listar todas las etiquetas existentes.
6. THE System SHALL permitir a un Admin editar el nombre de una etiqueta existente.
7. WHEN un Admin elimina una etiqueta, THE System SHALL aplicar soft delete y desvincularla de todos los avisos que la tuvieran asignada.

---

### Requisito 2: Asignación de etiquetas a avisos

**User Story:** Como usuario con permisos de gestión de avisos, quiero asignar una etiqueta a un aviso, para que los vecinos puedan identificar fácilmente el tipo de contenido.

#### Criterios de aceptación

1. THE System SHALL permitir asignar como máximo una etiqueta por aviso.
2. WHEN un Admin o Community_Admin crea o edita un aviso, THE System SHALL mostrar todas las etiquetas disponibles como opciones seleccionables.
3. WHEN un Construction_Manager crea o edita un aviso, THE System SHALL mostrar únicamente las `Construction_Tag` de las obras que tiene asignadas como opciones seleccionables.
4. IF un Construction_Manager intenta guardar un aviso con una etiqueta que no corresponde a una obra que tiene asignada, THEN THE System SHALL rechazar la operación con un error de autorización (HTTP 403).
5. WHEN se guarda un aviso sin etiqueta seleccionada, THE System SHALL persistir el aviso con `notice_tag_id` nulo.
6. THE System SHALL añadir la columna `notice_tag_id` (nullable, foreign key a `notice_tags`) a la tabla `notices`.

---

### Requisito 3: Visualización de etiquetas en el panel de administración

**User Story:** Como admin, quiero ver la etiqueta de cada aviso en el listado del panel, para identificar rápidamente el tipo de cada aviso.

#### Criterios de aceptación

1. WHEN el AdminNoticeManager renderiza el listado de avisos, THE System SHALL mostrar la etiqueta de cada aviso junto a su título.
2. WHEN un aviso tiene la etiqueta `obra`, THE System SHALL aplicar un estilo visual destacado (p. ej. badge de color diferenciado) en la fila correspondiente del listado de administración.
3. WHEN un aviso no tiene etiqueta asignada, THE System SHALL mostrar la fila sin badge de etiqueta.

---

### Requisito 4: Visualización de etiquetas en el frontend público

**User Story:** Como vecino, quiero ver la etiqueta de cada aviso en el listado público, para saber de un vistazo si un aviso es sobre una obra u otro tema.

#### Criterios de aceptación

1. WHEN el PublicNotices renderiza el listado de avisos, THE System SHALL mostrar la etiqueta de cada aviso junto a su título, usando el nombre en el idioma activo de la sesión.
2. WHEN un aviso tiene la etiqueta `obra`, THE System SHALL aplicar un estilo visual destacado (p. ej. borde o badge de color diferenciado) en la tarjeta del aviso en el frontend público.
3. WHEN un aviso no tiene etiqueta asignada, THE System SHALL mostrar la tarjeta sin badge de etiqueta.
4. IF el nombre de la etiqueta no existe en el idioma activo, THEN THE System SHALL mostrar el nombre en el idioma alternativo disponible como fallback.

---

### Requisito 5: Nuevo rol "director de obra"

**User Story:** Como superadmin, quiero asignar el rol "director de obra" a un usuario, para que pueda gestionar avisos relacionados con obras sin acceso a otras funcionalidades de administración.

#### Criterios de aceptación

1. THE System SHALL incluir la constante `Role::CONSTRUCTION_MANAGER = 'construction_manager'` en el modelo `Role` y registrarla en `Role::names()`.
2. THE System SHALL incluir el rol `construction_manager` en el seeder de roles.
3. WHEN un usuario tiene el rol `construction_manager`, THE System SHALL permitirle acceder al panel de administración.
4. WHEN un usuario tiene el rol `construction_manager`, THE System SHALL permitirle crear, editar y eliminar avisos (método `canManageNotices()` retorna `true`).
5. WHEN un usuario tiene el rol `construction_manager`, THE System SHALL restringir su acceso a las secciones de gestión de usuarios, propietarios, localizaciones y votaciones.
6. THE System SHALL exponer el método `User::canManageConstructions(): bool` que retorna `true` únicamente para usuarios con rol `construction_manager`, `admin_general` o `superadmin`.

---

### Requisito 6: Gestión de obras (Construction)

**User Story:** Como director de obra o admin, quiero registrar y consultar las obras activas en la comunidad, para tener un registro centralizado de las intervenciones en curso.

#### Criterios de aceptación

1. THE System SHALL almacenar las obras (`Construction`) con los campos: `id`, `title` (string, requerido), `description` (text, nullable), `starts_at` (date, requerido), `ends_at` (date, nullable), `is_active` (boolean, default `true`), `created_at`, `updated_at`, `deleted_at`.
2. THE System SHALL permitir que existan varias obras con `is_active = true` de forma simultánea.
3. WHEN un Admin o Construction_Manager crea una obra, THE System SHALL validar que `title` no esté vacío y que `starts_at` sea una fecha válida.
4. IF `ends_at` está presente y es anterior a `starts_at`, THEN THE System SHALL retornar un error de validación indicando que la fecha de fin debe ser posterior a la de inicio.
5. THE System SHALL permitir a un Admin o Construction_Manager listar todas las obras, ordenadas por `starts_at` descendente.
6. THE System SHALL permitir a un Admin o Construction_Manager marcar una obra como inactiva (`is_active = false`) sin eliminarla.
7. WHEN un Admin crea una nueva obra, THE System SHALL crear automáticamente una `Construction_Tag` con slug `obra-{slug}` y nombre `Obra: {title}` vinculada a esa obra.
8. THE System SHALL permitir a un Admin crear, editar, eliminar y listar obras desde el panel de administración (CRUD completo).

---

### Requisito 7: Página pública de obras (obrak)

**User Story:** Como vecino autenticado, quiero acceder a una página pública con el listado de obras activas y ver el detalle de cada una, para estar informado sobre las intervenciones en curso en mi comunidad.

#### Criterios de aceptación

1. THE System SHALL exponer una ruta pública `/obrak` que liste todas las obras activas, donde una obra está activa cuando `starts_at <= fecha_actual` y (`ends_at >= fecha_actual` o `ends_at` es null).
2. THE System SHALL exponer una ruta pública `/obrak/{slug}` para el detalle de cada obra activa.
3. WHEN un usuario no autenticado accede a `/obrak` o `/obrak/{slug}`, THE System SHALL redirigirle a la página de login (middleware `auth`).
4. WHEN un usuario autenticado accede a `/obrak/{slug}`, THE System SHALL mostrar en la parte derecha de la página los avisos que tienen asignada la `Construction_Tag` específica de esa obra (slug `obra-{slug}`).
5. WHEN un usuario autenticado accede a `/obrak/{slug}`, THE System SHALL mostrar en la parte superior un formulario para enviar una consulta sobre esa obra concreta.
6. IF la obra solicitada no existe o no está activa, THEN THE System SHALL retornar un error HTTP 404.
7. THE Construction SHALL almacenar un campo `slug` (string, único, generado automáticamente desde `title`) para construir las URLs de detalle.

---

### Requisito 8: Formulario de consultas sobre obras (ConstructionInquiry)

**User Story:** Como vecino autenticado, quiero enviar una consulta sobre una obra concreta, para resolver mis dudas directamente con el director de obra responsable.

#### Criterios de aceptación

1. THE System SHALL almacenar las consultas (`ConstructionInquiry`) con los campos: `id`, `construction_id` (foreign key a `constructions`), `user_id` (foreign key a `users`, nullable), `name` (string, requerido), `email` (string, requerido), `subject` (string, requerido), `message` (text, requerido), `reply` (text, nullable), `replied_at` (datetime, nullable), `is_read` (boolean, default `false`), `read_at` (datetime, nullable), `created_at`, `updated_at`, `deleted_at`.
2. WHEN un usuario autenticado envía el formulario de consulta, THE System SHALL validar que `name`, `email`, `subject` y `message` no estén vacíos y que `email` tenga formato válido.
3. IF algún campo obligatorio está vacío o `email` tiene formato inválido, THEN THE System SHALL mostrar los errores de validación correspondientes sin enviar el formulario.
4. WHEN el formulario se envía correctamente, THE System SHALL persistir la consulta vinculada a la obra y al usuario autenticado.
5. WHEN el formulario se envía correctamente, THE System SHALL enviar un email de notificación a cada Construction_Manager asignado a esa obra.
6. WHEN el formulario se envía correctamente, THE System SHALL mostrar un mensaje de confirmación al usuario.

---

### Requisito 9: Gestión de consultas en el panel de control

**User Story:** Como director de obra o superadmin, quiero gestionar las consultas recibidas sobre las obras desde el panel de administración, para poder responder a los vecinos de forma organizada.

#### Criterios de aceptación

1. WHEN un usuario con rol `construction_manager` accede al listado de consultas, THE System SHALL mostrar únicamente las consultas de las obras que tiene asignadas.
2. WHEN un usuario con rol `superadmin` accede al listado de consultas, THE System SHALL mostrar todas las consultas de todas las obras.
3. THE System SHALL presentar el listado de consultas con el mismo patrón visual que el componente `AdminMessageInbox` (búsqueda, filtro leído/no leído, ordenación, paginación).
4. WHEN un Construction_Manager o superadmin abre una consulta no leída, THE System SHALL marcarla automáticamente como leída (`is_read = true`, `read_at = now()`).
5. THE System SHALL permitir a un Construction_Manager o superadmin alternar manualmente el estado leído/no leído de cada consulta.
6. THE System SHALL indicar visualmente en el listado si cada consulta ha sido respondida (campo `reply` no nulo) o no.
7. WHEN un Construction_Manager o superadmin envía una respuesta a una consulta, THE System SHALL persistir el texto en `reply` y la fecha en `replied_at`.
8. WHEN se persiste una respuesta, THE System SHALL enviar un email con el contenido de la respuesta a la dirección `email` de la consulta original.
9. THE System SHALL enviar la respuesta únicamente por email al vecino. No existe ninguna página pública donde el vecino pueda consultar la respuesta.
10. IF una consulta ya tiene respuesta y se intenta responder de nuevo, THEN THE System SHALL sobrescribir `reply` y actualizar `replied_at` con la nueva fecha.

---

### Requisito 10: Asignación de directores de obra a una obra

**User Story:** Como admin, quiero asignar uno o varios directores de obra a cada obra, para que reciban las consultas de los vecinos y tengan acceso a la gestión de esa obra concreta.

#### Criterios de aceptación

1. THE System SHALL almacenar la relación entre obras y directores de obra en una tabla pivote `construction_user` con los campos: `construction_id`, `user_id`, `created_at`, `updated_at`.
2. THE System SHALL permitir asignar más de un Construction_Manager a la misma obra de forma simultánea.
3. WHEN un Admin gestiona una obra desde el panel de administración, THE System SHALL mostrar un selector para asignar o desasignar Construction_Managers a esa obra.
4. IF un usuario sin rol `admin_general` o `superadmin` intenta modificar las asignaciones de directores de obra, THEN THE System SHALL rechazar la operación con un error de autorización (HTTP 403).
5. WHEN se desasigna un Construction_Manager de una obra, THE System SHALL mantener las consultas (`ConstructionInquiry`) ya existentes vinculadas a esa obra sin modificación.
6. THE System SHALL actualizar el método `User::canManageConstructions(): bool` para que retorne `true` únicamente para usuarios con rol `construction_manager`, `admin_general` o `superadmin` (sin cambios respecto al Requisito 6, criterio 6).

---

### Requisito 11: Documentos adjuntos en avisos (NoticeDocument)

**User Story:** Como admin o director de obra, quiero adjuntar documentos a un aviso, para que los vecinos puedan descargar la información relevante directamente desde el aviso.

#### Criterios de aceptación

1. THE System SHALL almacenar los documentos adjuntos (`NoticeDocument`) con los campos: `id`, `notice_id` (foreign key a `notices`), `filename`, `path`, `mime_type`, `size_bytes`, `is_public` (boolean), `created_at`, `updated_at`, `deleted_at`.
2. THE System SHALL aceptar únicamente ficheros con tipo MIME correspondiente a PDF, DOCX, XLSX, JPG o PNG.
3. IF el fichero subido supera 20 MB, THEN THE System SHALL retornar un error de validación indicando que el tamaño máximo permitido es 20 MB.
4. IF el tipo de fichero no está entre los permitidos, THEN THE System SHALL retornar un error de validación indicando los formatos aceptados.
5. WHEN un Admin o Construction_Manager adjunta un documento a un aviso, THE System SHALL permitir marcar cada documento como público (`is_public = true`) o privado (`is_public = false`).
6. WHEN un visitante no autenticado solicita descargar un `NoticeDocument` con `is_public = true`, THE System SHALL servir el fichero sin requerir autenticación.
7. WHEN un visitante no autenticado solicita descargar un `NoticeDocument` con `is_public = false`, THE System SHALL redirigirle a la página de login.
8. WHEN un usuario autenticado solicita descargar cualquier `NoticeDocument`, THE System SHALL servir el fichero independientemente del valor de `is_public`.
9. WHEN el PublicNotices renderiza un aviso que tiene documentos adjuntos, THE System SHALL mostrar los enlaces de descarga de cada documento, ocultando los documentos privados a los visitantes no autenticados.
10. WHEN la página de detalle público de un aviso renderiza los documentos adjuntos, THE System SHALL mostrar los enlaces de descarga, ocultando los documentos privados a los visitantes no autenticados.

---

### Requisito 12: Tracking de descargas de documentos de avisos (NoticeDocumentDownload)

**User Story:** Como admin, quiero saber cuántas veces se ha descargado cada documento adjunto a un aviso, para medir el interés de los vecinos en la documentación publicada.

#### Criterios de aceptación

1. THE System SHALL almacenar cada descarga de un documento (`NoticeDocumentDownload`) con los campos: `id`, `notice_document_id` (foreign key a `notice_documents`), `user_id` (foreign key a `users`, nullable), `ip_address`, `downloaded_at`. Este modelo no usará SoftDeletes.
2. WHEN cualquier usuario (autenticado o no) descarga un `NoticeDocument`, THE System SHALL registrar un `NoticeDocumentDownload` con el `user_id` del usuario autenticado (o null si no está autenticado), la IP de la petición y la fecha y hora de la descarga.
3. WHEN el AdminNoticeManager renderiza el listado de avisos, THE System SHALL mostrar, para cada aviso que tenga al menos un documento adjunto, el número total de descargas sumando todos sus documentos.
4. WHEN un Admin expande o accede al detalle de un aviso en el AdminNoticeManager, THE System SHALL mostrar el desglose de descargas por documento: nombre del fichero (`filename`) y número de descargas de ese documento.
5. WHEN un aviso no tiene documentos adjuntos, THE System SHALL omitir la columna o sección de descargas para ese aviso en el listado de administración.

---

## Nota sobre el alcance futuro

Este documento cubre la base del rol "director de obra", el modelo `Construction`, la página pública de obras, el sistema de consultas, la asignación de directores de obra, los documentos adjuntos en avisos y el tracking de descargas. Funcionalidades adicionales se definirán en conversaciones posteriores.

---

¿Hay algo más que quieras ajustar antes de pasar al diseño técnico?
