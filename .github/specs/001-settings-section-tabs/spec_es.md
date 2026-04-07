# Feature Specification: Campo Section en Settings y Panel con Pestañas

**Feature Branch**: `001-settings-section-tabs`  
**Created**: 2026-04-07  
**Estado**: Borrador  
**Input**: Descripción del usuario: "en la tabla de settings quiero añadir un nuevo campo section, su valores entre otros serán front, contact_form, galery, ... a cada uno de los registros le añadiré su sección y en el panel de control en la vista settings habrá una pestaña para cada section."

## Escenarios de Usuario y Pruebas *(obligatorio)*

### Historia de Usuario 1 - Navegar Ajustes por Pestaña de Sección (Priority: P1)

Una persona administradora abre el panel de Settings en el panel de control y ve un conjunto de pestañas, una por sección (por ejemplo, "Front", "Contact Form", "Gallery"). Al pulsar una pestaña, solo se muestran los ajustes que pertenecen a esa sección. La persona administradora puede editar los valores de la sección activa y guardarlos de forma independiente.

**Por qué esta prioridad**: Este es el cambio principal visible para quien administra. Sin pestañas por sección, la funcionalidad no aporta valor práctico.

**Prueba independiente**: Se puede probar navegando a la página de settings de administración, verificando que existen pestañas de sección, pulsando cada pestaña y confirmando que solo se muestran y editan los ajustes de esa sección.

**Escenarios de Aceptación**:

1. **Given** la página de settings de administración está cargada, **When** la página se renderiza, **Then** se muestra una pestaña para cada sección que tenga al menos un registro de setting activo.
2. **Given** la persona administradora está en la página de settings, **When** pulsa una pestaña de sección, **Then** solo son visibles en el formulario los settings que pertenecen a esa sección.
3. **Given** la persona administradora edita un valor dentro de la sección activa, **When** envía el formulario, **Then** solo se guardan los settings de esa sección y se muestra una confirmación de éxito.
4. **Given** la persona administradora está en una pestaña de sección, **When** navega a otra pestaña, **Then** los cambios no guardados de la pestaña anterior se descartan (sin contaminación entre secciones).

---

### Historia de Usuario 2 - Todos los Settings Existentes Asignados a una Sección (Priority: P2)

Todos los registros de settings existentes en base de datos se asignan a una sección adecuada para que la vista por pestañas sea funcional inmediatamente tras el despliegue, sin pérdida de datos ni huecos.

**Por qué esta prioridad**: La UI con pestañas depende de que cada setting tenga un valor de section válido. Si faltan asignaciones, algunos settings quedarían ocultos en la interfaz de administración.

**Prueba independiente**: Se puede probar verificando que cada fila de la tabla de settings tiene un valor de section no nulo tras la migración de datos y que cada key conocida aparece en su pestaña esperada.

**Escenarios de Aceptación**:

1. **Given** se aplica la migración y la asignación de datos, **When** se consultan todos los settings, **Then** cada registro tiene un valor de `section` no nulo.
2. **Given** la persona administradora abre el panel de settings, **When** recorre todas las pestañas de sección, **Then** cada setting existente previamente es accesible desde alguna pestaña.

---

### Historia de Usuario 3 - Nuevos Settings Incluyen Asignación de Sección (Priority: P3)

Cuando se añade un nuevo setting en el futuro, debe incluir una asignación de section para que siempre aparezca en la pestaña correcta sin arreglos manuales.

**Por qué esta prioridad**: Garantiza mantenibilidad a largo plazo y evita settings huérfanos que no aparecerían en ninguna pestaña.

**Prueba independiente**: Se puede probar verificando que el campo section se aplica a nivel de datos (no nulo y limitado a valores conocidos).

**Escenarios de Aceptación**:

1. **Given** se crea un nuevo setting, **When** no se especifica section, **Then** el sistema impide la persistencia y devuelve un error de validación.
2. **Given** se crea un nuevo setting con una section válida, **When** la persona administradora abre el panel de settings, **Then** aparece bajo la pestaña de sección correspondiente.

---

### Casos Límite

- ¿Qué ocurre cuando una sección no tiene settings asignados? La pestaña de esa sección no debe aparecer.
- ¿Qué ocurre si un setting tiene un valor de `section` que todavía no está representado por una pestaña? Debe normalizarse a `general` antes del renderizado para que siga siendo accesible en la pestaña `general`.
- ¿Qué pasa si una persona administradora guarda un formulario de sección que no contiene cambios? El guardado debe completarse correctamente y sin errores.
- ¿Qué pasa con settings que tengan `null` en section tras la migración? Deben asignarse antes o durante la migración; la funcionalidad no debe dejar settings sin sección.

## Requisitos *(obligatorio)*

### Requisitos Funcionales

- **FR-001**: La tabla `settings` MUST incluir un campo `section` que agrupe cada setting en una categoría nombrada.
- **FR-002**: El campo `section` MUST aceptar únicamente un conjunto predefinido de valores (por ejemplo, `front`, `contact_form`, `gallery`); el conjunto es extensible pero acotado para integridad de datos.
- **FR-003**: El campo `section` MUST ser no nulo; cada registro de setting MUST tener una sección asignada.
- **FR-004**: Todos los registros existentes de settings MUST asignarse a una sección adecuada como parte de la migración.
- **FR-005**: La vista de settings de administración MUST mostrar una pestaña por sección que tenga al menos un setting.
- **FR-006**: Cada pestaña de sección MUST mostrar únicamente los settings que pertenecen a esa sección.
- **FR-007**: Cada pestaña de sección MUST permitir editar y guardar settings de forma independiente al resto de secciones.
- **FR-008**: Las etiquetas de pestaña MUST ser legibles y consistentes en los idiomas soportados por la aplicación (euskera y español).
- **FR-009**: El campo `section` MUST incluirse en la lista `$fillable` del modelo `Setting` y reflejarse en la factory.
- **FR-010**: El seeder o la migración de settings MUST asignar cada key conocida a su sección correcta.
- **FR-011**: Los settings cuyo valor de `section` esté fuera del conjunto permitido en runtime MUST reasignarse a `general` antes del renderizado de pestañas y de operaciones de guardado.

### Entidades Clave

- **Setting**: Representa un par key-value de configuración de la aplicación. Atributos clave: `key` (identificador único), `value` (contenido), `section` (categoría de agrupación). Soporta soft deletes. El nuevo atributo `section` determina en qué pestaña aparece el setting.
- **Section**: Agrupación lógica de settings relacionados (por ejemplo, `front`, `contact_form`, `gallery`). No es una entidad separada de base de datos: se representa como valor string restringido en el modelo `Setting`. Conduce la generación de pestañas en el panel de administración.

## Criterios de Éxito *(obligatorio)*

### Resultados Medibles

- **SC-001**: Una persona administradora puede localizar y editar cualquier setting en un máximo de 2 clics de pestaña desde la página de settings, sin desplazarse por configuraciones no relacionadas.
- **SC-002**: Tras el despliegue, el 100% de los registros existentes de settings tiene un valor de `section` no nulo; ningún setting queda inaccesible en la interfaz por pestañas.
- **SC-003**: La página de settings de administración renderiza todas las pestañas de sección sin cargas de página adicionales (cambio de pestaña en una sola página).
- **SC-004**: Añadir una nueva sección requiere solo añadir un nuevo valor permitido y crear registros de settings; no se necesitan cambios estructurales en la UI.

## Suposiciones

- El conjunto inicial de secciones es: `front`, `contact_form`, `gallery`. Se podrán añadir secciones adicionales en iteraciones futuras.
- Los seis settings existentes actualmente (`admin_email`, `recaptcha_site_key`, `recaptcha_secret_key`, `legal_checkbox_text_eu`, `legal_checkbox_text_es`, `legal_url`) se asignan a la sección `contact_form`, ya que están relacionados con el formulario de contacto y la configuración de cumplimiento legal.
- El panel de administración solo es accesible para personas autenticadas con privilegios de administración; no hace falta una nueva capa de autorización para esta funcionalidad.
- El orden de pestañas sigue orden alfabético (ordenado por nombre de sección).
- La aplicación soporta dos locales (euskera y español); las etiquetas de pestaña deben tener traducciones en ambos.
- La sección `gallery` todavía no tiene settings asociados; su pestaña no aparecerá hasta que se asignen settings a dicha sección.
- Los settings con asignación de section ambigua o ausente durante la migración se asignarán a una sección "general" con logging para revisión posterior al despliegue.

## Aclaraciones

### Sesión 2026-04-07

- Q: Should the admin panel remember which tab was last viewed? → A: No; mostrar siempre la primera pestaña (alfabéticamente) en la carga de página para consistencia y simplicidad.
- Q: In what order should section tabs appear? → A: Orden alfabético por nombre de sección para previsibilidad y facilidad de localización.
- Q: What happens if a setting's section cannot be determined during migration? → A: Asignar a una sección por defecto "general" y registrar la asignación para auditoría post-despliegue y corrección manual si fuera necesaria.

## Checklist de Alineación Constitucional

- [x] No implementation details leaked into requirements
- [x] Security/privacy constraints captured where relevant
- [x] Accessibility expectations captured where relevant
- [x] Localization expectations captured where relevant
- [x] All user stories have independent test intent
