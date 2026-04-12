# Documento de Requisitos: Presupuesto de Obra (Construction Budget)

## Introducción

Esta feature añade un sistema de presupuesto a cada obra (`Construction`). El presupuesto se compone de líneas (`ConstructionBudgetLine`), cada una con fecha, descripción, documentos privados adjuntos, subtotal, IVA (%) y total calculado. El panel de administración muestra el reparto del presupuesto por propietario (`Owner`) y propiedad (`Property`) según el porcentaje de participación en la comunidad (`community_pct`). El frontend `/obrak/{slug}` muestra al propietario autenticado su parte proporcional del presupuesto.

El sistema existente usa Laravel 13, Livewire 4, Flux UI v2, Pest v4 y PHP 8.4. Los modelos relevantes ya existentes son `Construction`, `Owner`, `Property`, `PropertyAssignment` y `User`. El patrón de documentos privados sigue el modelo `NoticeDocument` de la feature `construction-management`.

---

## Glosario

- **System**: la aplicación web de gestión comunitaria.
- **Admin**: usuario con rol `superadmin` o `admin_general`.
- **Construction_Manager**: usuario con rol `construction_manager`.
- **Owner**: propietario registrado en el sistema, con asignaciones activas a propiedades.
- **Property**: propiedad con campo `community_pct` (decimal:4) que representa el porcentaje de participación en la comunidad.
- **PropertyAssignment**: relación entre `Owner` y `Property`; una asignación es activa cuando `end_date` es null.
- **Construction**: obra registrada en el sistema (modelo existente).
- **ConstructionBudgetLine**: línea de presupuesto asociada a una obra, con fecha, descripción, subtotal, IVA y total calculado.
- **BudgetLineDocument**: documento privado adjunto a una `ConstructionBudgetLine`. Solo accesible con autenticación.
- **Budget_Total**: suma de los `line_total` de todas las `ConstructionBudgetLine` de una obra.
- **Owner_Share**: porción del `Budget_Total` que corresponde a un `Owner` por una `Property` concreta, calculada como `Budget_Total * community_pct / 100`.
- **Owner_Total**: suma de todas las `Owner_Share` de un `Owner` para todas sus propiedades activas asignadas a la obra.

---

## Requisitos

### Requisito 1: Modelo ConstructionBudgetLine

**User Story:** Como admin o director de obra, quiero añadir líneas de presupuesto a una obra, para registrar los costes detallados de la intervención.

#### Criterios de aceptación

1. THE System SHALL almacenar las líneas de presupuesto (`ConstructionBudgetLine`) con los campos: `id`, `construction_id` (foreign key a `constructions`), `line_date` (date, requerido), `description` (string, requerido), `subtotal` (decimal:2, requerido, ≥ 0), `vat_pct` (decimal:2, requerido, ≥ 0), `line_total` (decimal:2, calculado), `created_at`, `updated_at`, `deleted_at`.
2. THE System SHALL calcular `line_total` como `subtotal * (1 + vat_pct / 100)` y persistirlo en base de datos en cada creación o actualización de la línea.
3. WHEN un Admin o Construction_Manager crea una línea de presupuesto, THE System SHALL validar que `line_date` sea una fecha válida, `description` no esté vacío, `subtotal` sea un número decimal mayor o igual a 0, y `vat_pct` sea un número decimal mayor o igual a 0.
4. IF algún campo obligatorio está vacío o tiene un valor inválido, THEN THE System SHALL mostrar los errores de validación correspondientes sin persistir la línea.
5. THE System SHALL permitir a un Admin o Construction_Manager editar cualquier campo de una línea existente, recalculando `line_total` automáticamente tras cada edición.
6. WHEN un Admin o Construction_Manager elimina una línea de presupuesto, THE System SHALL aplicar soft delete a la línea y a todos sus `BudgetLineDocument` asociados.
7. THE System SHALL ordenar las líneas de presupuesto de una obra por `line_date` ascendente en todas las vistas.

---

### Requisito 2: Documentos adjuntos a líneas de presupuesto (BudgetLineDocument)

**User Story:** Como admin o director de obra, quiero adjuntar documentos privados a cada línea de presupuesto, para que los propietarios autenticados puedan descargar la documentación justificativa.

#### Criterios de aceptación

1. THE System SHALL almacenar los documentos adjuntos (`BudgetLineDocument`) con los campos: `id`, `construction_budget_line_id` (foreign key a `construction_budget_lines`), `token` (string, único, UUID generado al crear), `filename` (string), `path` (string), `mime_type` (string), `size_bytes` (integer), `created_at`, `updated_at`, `deleted_at`.
2. THE System SHALL aceptar únicamente ficheros con tipo MIME correspondiente a PDF, DOCX, XLSX, JPG o PNG.
3. IF el fichero subido supera 20 MB, THEN THE System SHALL retornar un error de validación indicando que el tamaño máximo permitido es 20 MB.
4. IF el tipo de fichero no está entre los permitidos, THEN THE System SHALL retornar un error de validación indicando los formatos aceptados.
5. THE System SHALL tratar todos los `BudgetLineDocument` como privados: únicamente los usuarios autenticados podrán descargarlos.
6. WHEN un visitante no autenticado solicita descargar un `BudgetLineDocument`, THE System SHALL redirigirle a la página de login.
7. WHEN un usuario autenticado solicita descargar un `BudgetLineDocument`, THE System SHALL servir el fichero mediante un token UUID en la URL, sin exponer el `id` ni la ruta interna del fichero.
8. IF el token de descarga no corresponde a ningún `BudgetLineDocument` existente, THEN THE System SHALL retornar HTTP 404.
9. WHEN un Admin o Construction_Manager elimina un `BudgetLineDocument`, THE System SHALL aplicar soft delete al registro y dejar el fichero físico en el almacenamiento (la limpieza física es responsabilidad de un proceso separado).

---

### Requisito 3: Gestión del presupuesto en el panel de administración

**User Story:** Como admin o director de obra, quiero gestionar las líneas de presupuesto de cada obra desde el panel de administración, para mantener actualizado el coste total de la intervención.

#### Criterios de aceptación

1. THE System SHALL mostrar en el panel de administración, dentro del detalle de cada obra, una sección de presupuesto con el listado de sus `ConstructionBudgetLine` ordenadas por `line_date` ascendente.
2. THE System SHALL mostrar para cada línea: `line_date`, `description`, `subtotal`, `vat_pct` (%), `line_total`, y los documentos adjuntos con enlace de descarga.
3. THE System SHALL mostrar el `Budget_Total` (suma de todos los `line_total`) de la obra en la cabecera de la sección de presupuesto.
4. THE System SHALL permitir a un Admin o Construction_Manager añadir, editar y eliminar líneas de presupuesto desde esta sección.
5. THE System SHALL permitir a un Admin o Construction_Manager subir y eliminar `BudgetLineDocument` para cada línea desde esta sección.
6. WHEN un Admin o Construction_Manager accede al detalle de una obra, THE System SHALL mostrar la sección de presupuesto únicamente si el usuario tiene permiso para gestionar esa obra (`canManageConstructions()` retorna `true`).

---

### Requisito 4: Vista admin del reparto del presupuesto por Owner/Property

**User Story:** Como admin, quiero ver el reparto del presupuesto de cada obra entre los propietarios y sus propiedades, para conocer cuánto debe pagar cada uno.

#### Criterios de aceptación

1. THE System SHALL mostrar en el panel de administración, dentro del detalle de cada obra, una sección de reparto del presupuesto con el listado de todos los `Owner` que tienen al menos una `Property` con asignación activa (`end_date` null) a esa obra.
2. WHEN el System renderiza la sección de reparto, THE System SHALL mostrar para cada `Owner`: nombre del propietario, listado de sus propiedades activas asignadas a la obra con su `community_pct` y su `Owner_Share` individual, y el `Owner_Total`.
3. THE System SHALL calcular cada `Owner_Share` como `Budget_Total * community_pct / 100`, redondeado a 2 decimales.
4. THE System SHALL calcular el `Owner_Total` de cada `Owner` como la suma de todas sus `Owner_Share` para esa obra, redondeado a 2 decimales.
5. WHILE el `Budget_Total` de la obra es 0, THE System SHALL mostrar `Owner_Share` y `Owner_Total` como 0,00 para todos los propietarios.
6. THE System SHALL actualizar los valores de reparto en tiempo real cada vez que se añada, edite o elimine una `ConstructionBudgetLine`.
7. IF una obra no tiene ninguna `Property` con asignación activa, THEN THE System SHALL mostrar un mensaje indicando que no hay propiedades asignadas a esta obra.

---

### Requisito 5: Vista frontend del presupuesto para el Owner autenticado

**User Story:** Como propietario autenticado, quiero ver en la página de detalle de una obra cuánto me corresponde pagar del presupuesto, para conocer mi aportación económica a la intervención.

#### Criterios de aceptación

1. WHEN un Owner autenticado accede a `/obrak/{slug}`, THE System SHALL mostrar una sección de presupuesto con el `Budget_Total` de la obra.
2. WHEN un Owner autenticado accede a `/obrak/{slug}`, THE System SHALL mostrar el listado de sus propiedades activas asignadas a esa obra, con el `community_pct` de cada una y su `Owner_Share` individual (`Budget_Total * community_pct / 100`, redondeado a 2 decimales).
3. WHEN un Owner autenticado accede a `/obrak/{slug}`, THE System SHALL mostrar el `Owner_Total` (suma de todas sus `Owner_Share` para esa obra, redondeado a 2 decimales).
4. WHILE el `Budget_Total` de la obra es 0, THE System SHALL mostrar `Owner_Share` y `Owner_Total` como 0,00.
5. IF el Owner autenticado no tiene ninguna propiedad activa asignada a la obra, THEN THE System SHALL mostrar únicamente el `Budget_Total`, las líneas de presupuesto y un mensaje indicando que no tiene propiedades asignadas a esta obra.
6. THE System SHALL mostrar las líneas de presupuesto individuales (`line_date`, `description`, `line_total`) ordenadas por `line_date` ascendente, con los enlaces de descarga de sus `BudgetLineDocument`.
7. WHEN un usuario autenticado accede a `/obrak/{slug}`, THE System SHALL mostrar únicamente los `BudgetLineDocument` que no han sido eliminados (soft delete), con enlace de descarga por token.
8. IF un usuario autenticado no tiene propiedades activas asignadas a la obra, THE System SHALL mostrar el `Budget_Total` y las líneas de presupuesto, pero omitir la sección de reparto individual.

---

### Requisito 6: Autorización

**User Story:** Como administrador del sistema, quiero que solo los usuarios autorizados puedan crear, editar y ver las líneas de presupuesto y sus documentos, para proteger la información económica de la comunidad.

#### Criterios de aceptación

1. THE System SHALL permitir crear, editar y eliminar `ConstructionBudgetLine` únicamente a usuarios cuyo método `canManageConstructions()` retorne `true` (roles: `superadmin`, `admin_general`, `construction_manager`).
2. THE System SHALL permitir subir y eliminar `BudgetLineDocument` únicamente a usuarios cuyo método `canManageConstructions()` retorne `true`.
3. WHEN un Construction_Manager intenta gestionar el presupuesto de una obra que no tiene asignada, THE System SHALL rechazar la operación con un error de autorización (HTTP 403).
4. THE System SHALL permitir ver el presupuesto (líneas, totales y documentos) en el frontend `/obrak/{slug}` únicamente a usuarios autenticados (middleware `auth`).
5. IF un usuario no autenticado intenta acceder a `/obrak/{slug}`, THE System SHALL redirigirle a la página de login.
6. THE System SHALL permitir ver la sección de reparto por Owner/Property en el panel de administración únicamente a usuarios con rol `superadmin` o `admin_general`.

---

### Requisito 7: Cálculos de presupuesto

**User Story:** Como sistema, quiero garantizar que todos los cálculos de totales y repartos sean consistentes y precisos, para que los propietarios reciban información económica fiable.

#### Criterios de aceptación

1. THE System SHALL calcular `line_total` de cada `ConstructionBudgetLine` como `subtotal * (1 + vat_pct / 100)`, redondeado a 2 decimales, y persistirlo en base de datos.
2. THE System SHALL calcular el `Budget_Total` de una obra como la suma de los `line_total` de todas sus `ConstructionBudgetLine` no eliminadas (sin soft delete), redondeado a 2 decimales.
3. THE System SHALL calcular cada `Owner_Share` como `Budget_Total * community_pct / 100`, redondeado a 2 decimales.
4. THE System SHALL calcular el `Owner_Total` de cada `Owner` como la suma de todas sus `Owner_Share` para esa obra, redondeado a 2 decimales.
5. WHEN una `ConstructionBudgetLine` es eliminada (soft delete), THE System SHALL excluirla del cálculo del `Budget_Total` y de todos los repartos derivados.
6. FOR ALL combinaciones válidas de `subtotal` ≥ 0 y `vat_pct` ≥ 0, THE System SHALL producir un `line_total` ≥ 0.

---

### Requisito 8: Tracking de descargas de documentos de líneas de presupuesto

**User Story:** Como admin, quiero saber cuántas veces se ha descargado cada documento adjunto a una línea de presupuesto, para medir el interés de los propietarios en la documentación económica.

#### Criterios de aceptación

1. THE System SHALL almacenar cada descarga de un `BudgetLineDocument` (`BudgetLineDocumentDownload`) con los campos: `id`, `budget_line_document_id` (foreign key a `budget_line_documents`), `user_id` (foreign key a `users`, nullable), `ip_address`, `downloaded_at`. Este modelo no usará SoftDeletes.
2. WHEN un usuario autenticado descarga un `BudgetLineDocument`, THE System SHALL registrar un `BudgetLineDocumentDownload` con el `user_id` del usuario autenticado, la IP de la petición y la fecha y hora de la descarga.
3. WHEN el System renderiza el listado de líneas de presupuesto en el panel de administración, THE System SHALL mostrar para cada línea el número total de descargas sumando todos sus documentos.
4. WHEN un Admin o Construction_Manager expande el detalle de una línea en el panel de administración, THE System SHALL mostrar el desglose de descargas por documento: nombre del fichero (`filename`) y número de descargas de ese documento.
5. WHEN una línea no tiene documentos adjuntos, THE System SHALL omitir la sección de descargas para esa línea en el listado de administración.
