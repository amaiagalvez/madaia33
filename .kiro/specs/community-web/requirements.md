# Documento de Requisitos

## Introducción

Web pública para la comunidad de vecinos. La web contará con una parte pública accesible a cualquier visitante, con imágenes y avisos públicos, y una parte privada reservada para vecinas y vecinos (a desarrollar en el futuro). La web estará disponible en dos idiomas: Euskera (idioma principal) y Castellano (idioma secundario). La comunidad está compuesta por 10 portales (33-A–33-J) y 3 plantas de garaje.

## Glosario

- **Web**: La aplicación web de la comunidad de vecinos.
- **Parte_Pública**: Sección de la web accesible sin autenticación.
- **Parte_Privada**: Sección de la web accesible únicamente con autenticación de vecino/a (desarrollo futuro).
- **Aviso**: Comunicado o notificación publicada por la administración de la comunidad.
- **Imagen**: Fotografía o recurso visual publicado en la parte pública.
- **Idioma_Principal**: Euskera (EU).
- **Idioma_Secundario**: Castellano (ES).
- **Portal**: Cada una de las 10 entradas de la comunidad, identificadas como 33-A, 33-B, 33-C, 33-D, 33-E, 33-F, 33-G, 33-H, 33-I, 33-J.
- **Planta_Garaje**: Cada uno de los 3 niveles de aparcamiento de la comunidad (P-1, P-2, P-3).
- **Visitante**: Cualquier persona que accede a la Parte_Pública sin autenticación.
- **Vecino**: Residente de la comunidad con acceso a la Parte_Privada (rol futuro).
- **Administrador**: Persona responsable de gestionar contenidos y configuración de la web.
- **Formulario_Contacto**: Formulario de la Parte_Pública que permite a los visitantes enviar mensajes a la administración.
- **Panel_Administración**: Sección protegida de la web accesible solo por el Administrador para gestionar contenidos y configuración.

---

## Requisitos

### Requisito 1: Idioma y localización

**User Story:** Como visitante, quiero navegar la web en Euskera o Castellano, para poder entender el contenido en mi idioma preferido.

#### Criterios de Aceptación

1. THE Web SHALL mostrar todos los textos de la interfaz en Euskera por defecto.
2. WHEN el visitante selecciona Castellano, THE Web SHALL mostrar todos los textos de la interfaz en Castellano.
3. WHEN el visitante selecciona Euskera, THE Web SHALL mostrar todos los textos de la interfaz en Euskera.
4. THE Web SHALL mantener la selección de idioma del visitante durante la sesión de navegación activa, sin persistir la selección entre sesiones distintas.
5. IF el idioma del navegador del visitante no coincide con ninguno de los idiomas disponibles, THEN THE Web SHALL mostrar la interfaz en Euskera.
6. WHEN el navegador del visitante declara Euskera (EU) o Castellano (ES) como idioma preferido y el visitante no ha seleccionado idioma explícitamente, THE Web SHALL usar automáticamente ese idioma, manteniendo Euskera como idioma por defecto si ninguno de los dos está declarado.

---

### Requisito 2: Parte pública — Avisos

**User Story:** Como visitante, quiero ver los avisos públicos de la comunidad, para estar informado de novedades y comunicados relevantes.

#### Criterios de Aceptación

1. THE Parte_Pública SHALL mostrar una lista de avisos marcados como públicos por el Administrador.
2. WHEN el Administrador publica un aviso como público, THE Parte_Pública SHALL mostrar dicho aviso a todos los visitantes.
3. WHEN el Administrador despublica o elimina un aviso, THE Parte_Pública SHALL dejar de mostrar dicho aviso.
4. THE Parte_Pública SHALL mostrar para cada aviso: título, fecha de publicación y contenido, en el idioma seleccionado.
5. IF un aviso no tiene traducción al idioma seleccionado, THEN THE Web SHALL mostrar el aviso en el idioma disponible e indicar que no hay traducción.
6. THE Parte_Pública SHALL mostrar los avisos ordenados por fecha de publicación de forma descendente, mostrando primero el más reciente.
7. THE Parte_Pública SHALL paginar la lista de avisos mostrando un máximo de 10 avisos por página.
8. IF no hay avisos públicos disponibles, THEN THE Parte_Pública SHALL mostrar un mensaje indicando que no hay avisos en este momento.

---

### Requisito 3: Parte pública — Imágenes

**User Story:** Como visitante, quiero ver imágenes de la comunidad, para conocer las instalaciones y espacios comunes.

#### Criterios de Aceptación

1. THE Parte_Pública SHALL mostrar una galería de imágenes publicadas por el Administrador.
2. WHEN el Administrador sube una imagen, THE Parte_Pública SHALL incluir dicha imagen en la galería.
3. WHEN el Administrador elimina una imagen, THE Parte_Pública SHALL retirar dicha imagen de la galería.
4. THE Parte_Pública SHALL mostrar cada imagen con un texto alternativo descriptivo para garantizar la accesibilidad.
6. THE Parte_Pública SHALL mostrar las imágenes de la galería ordenadas por fecha de subida de forma descendente, mostrando primero la más reciente.
7. IF no hay imágenes disponibles en la galería, THEN THE Parte_Pública SHALL mostrar un mensaje indicando que no hay imágenes en este momento.

---

### Requisito 4: Estructura de la comunidad

**User Story:** Como visitante, quiero ver información organizada por portales y plantas de garaje, para identificar fácilmente la información relevante a mi zona.

#### Criterios de Aceptación

1. THE Web SHALL reconocer los 10 portales de la comunidad identificados como 33-A, 33-B, 33-C, 33-D, 33-E, 33-F, 33-G, 33-H, 33-I y 33-J.
2. THE Web SHALL reconocer las 3 plantas de garaje de la comunidad identificadas como P-1, P-2 y P-3.
3. WHERE el contenido esté asociado a un portal específico, THE Parte_Pública SHALL indicar el portal correspondiente junto al contenido.
4. WHERE el contenido esté asociado a una planta de garaje específica, THE Parte_Pública SHALL indicar la planta correspondiente junto al contenido.
5. THE Web SHALL tratar el contenido no asociado a ningún portal ni planta como contenido de ámbito general y mostrarlo a todos los visitantes sin etiqueta de ubicación.
6. THE Parte_Pública SHALL permitir al visitante filtrar los avisos por portal o planta de garaje.

---

### Requisito 5: Parte privada (reservada para desarrollo futuro)

**User Story:** Como vecino, quiero acceder a una zona privada de la web, para consultar información exclusiva de la comunidad.

#### Criterios de Aceptación

1. THE Web SHALL mostrar un acceso visible a la Parte_Privada desde la navegación principal.
2. WHEN un visitante intenta acceder a la Parte_Privada sin autenticación, THE Web SHALL mostrar el placeholder de la zona privada con una llamada a la acción hacia la página de inicio de sesión.
3. THE Parte_Privada SHALL estar deshabilitada funcionalmente hasta que se implemente el sistema de autenticación.

---

### Requisito 6: Administración de contenidos

**User Story:** Como administrador, quiero gestionar los contenidos de la parte pública, para mantener la web actualizada sin necesidad de conocimientos técnicos.

#### Criterios de Aceptación

1. THE Administrador SHALL poder crear, editar y eliminar avisos públicos desde un panel de administración.
2. THE Web SHALL reservar la ruta `/admin/imagenes` como punto de acceso a la futura gestión de imágenes desde el panel de administración.
3. THE Administrador SHALL poder asociar un aviso a uno o varios portales o plantas de garaje.
4. THE Administrador SHALL poder publicar o despublicar un aviso sin necesidad de eliminarlo.
5. THE Web SHALL requerir autenticación para acceder al panel de administración.
6. IF el Administrador introduce credenciales incorrectas, THEN THE Web SHALL mostrar un mensaje de error y no conceder acceso al panel de administración.
7. WHEN el Administrador intenta eliminar un aviso o imagen, THE Panel_Administración SHALL solicitar confirmación antes de proceder con la eliminación.
8. THE Web SHALL permitir la creación de cuentas de Administrador únicamente mediante herramientas de línea de comandos (Artisan) o seeders, sin ofrecer registro público de administradores.
9. THE Administrador SHALL poder configurar desde el Panel_Administración el texto bilingüe (EU/ES) del bloque de historia mostrado en la página principal.

---

### Requisito 7: Rendimiento y disponibilidad

**User Story:** Como visitante, quiero que la web cargue con rapidez, para tener una experiencia de navegación fluida.

#### Criterios de Aceptación

1. WHEN un visitante accede a la página principal, THE Web SHALL cargar el contenido visible inicial en menos de 3 segundos en una conexión estándar de banda ancha.
2. WHEN un visitante accede a las páginas de avisos, galería o contacto, THE Web SHALL cargar el contenido visible inicial en menos de 3 segundos en una conexión estándar de banda ancha.
3. THE Web SHALL ser accesible desde dispositivos móviles, tabletas y ordenadores de escritorio mediante un diseño adaptable (responsive).
4. THE Web SHALL incluir metadatos SEO básicos (título de página y meta description) en todas las páginas públicas.

---

### Requisito 8: Accesibilidad

**User Story:** Como visitante con diversidad funcional, quiero que la web sea accesible, para poder utilizarla con tecnologías de asistencia.

#### Criterios de Aceptación

1. THE Web SHALL cumplir con las pautas de accesibilidad WCAG 2.1 nivel AA.
2. THE Web SHALL proporcionar texto alternativo en todas las imágenes.
3. THE Web SHALL ser navegable mediante teclado sin necesidad de ratón.
4. THE Web SHALL garantizar un contraste de color suficiente entre el texto y el fondo, cumpliendo una relación de contraste mínima de 4.5:1 para texto normal y 3:1 para texto grande, según WCAG 2.1 criterio 1.4.3.
5. THE Web SHALL asociar una etiqueta descriptiva a todos los campos de formulario mediante el atributo `for`/`id` o el atributo `aria-label`.
6. THE Web SHALL incluir roles y atributos ARIA en los componentes interactivos donde el HTML semántico nativo no sea suficiente para describir su función o estado.

---

### Requisito 9: Stack tecnológico

**User Story:** Como equipo de desarrollo, quiero que la web se construya con un stack tecnológico definido, para garantizar coherencia, mantenibilidad y alineación con las tecnologías elegidas por el proyecto.

#### Criterios de Aceptación

1. THE Web SHALL estar desarrollada utilizando Laravel 13 como framework backend.
2. THE Web SHALL utilizar TailwindCSS como sistema de estilos para todos los componentes de la interfaz.
3. THE Web SHALL utilizar Livewire para la implementación de componentes dinámicos en el frontend.
4. IF se requiere interactividad en el frontend sin recarga de página, THEN THE Web SHALL implementar dicha interactividad mediante componentes Livewire.
5. THE Web SHALL no introducir frameworks o librerías de estilos adicionales que entren en conflicto con TailwindCSS.

---

### Requisito 10: Formulario de contacto — Parte pública

**User Story:** Como visitante, quiero poder enviar un mensaje a la comunidad a través de un formulario de contacto, para comunicarme con la administración sin necesidad de conocer su dirección de correo electrónico.

#### Criterios de Aceptación

1. THE Parte_Pública SHALL mostrar un formulario de contacto accesible desde la navegación principal.
2. THE Formulario_Contacto SHALL incluir los campos: nombre completo, dirección de correo electrónico, asunto y mensaje.
3. WHEN el visitante intenta enviar el formulario con algún campo obligatorio vacío, THE Formulario_Contacto SHALL mostrar un mensaje de error indicando los campos pendientes de completar y no enviar el formulario.
4. WHEN el visitante introduce una dirección de correo electrónico con formato inválido, THE Formulario_Contacto SHALL mostrar un mensaje de error y no enviar el formulario.
5. WHEN el visitante envía el formulario correctamente, THE Formulario_Contacto SHALL mostrar un mensaje de confirmación indicando que el mensaje ha sido enviado con éxito.
6. WHEN el visitante envía el formulario correctamente, THE Formulario_Contacto SHALL limpiar todos los campos del formulario.
7. THE Formulario_Contacto SHALL limitar el campo "mensaje" a un máximo de 5000 caracteres.
8. THE Formulario_Contacto SHALL mostrar todas las etiquetas, mensajes de error y mensajes de confirmación en el idioma activo seleccionado por el visitante.
9. WHEN el visitante introduce etiquetas `<script>` en el asunto o en el mensaje, THE Formulario_Contacto SHALL rechazar el envío, mostrar un mensaje de validación y no guardar el mensaje.

---

### Requisito 11: Formulario de contacto — Envío de correos electrónicos

**User Story:** Como visitante y como administrador, quiero recibir una confirmación por correo electrónico al enviarse un mensaje de contacto, para tener constancia del envío y poder gestionar la consulta.

#### Criterios de Aceptación

1. WHEN el visitante envía el formulario de contacto correctamente, THE Web SHALL enviar un correo electrónico de confirmación a la dirección de correo electrónico indicada por el visitante en el formulario.
2. WHEN el visitante envía el formulario de contacto correctamente, THE Web SHALL enviar un correo electrónico de notificación a la dirección de correo electrónico configurada por el Administrador en el panel de administración.
3. THE correo electrónico de confirmación al visitante SHALL incluir: nombre del visitante, asunto y contenido del mensaje enviado.
4. THE correo electrónico de notificación al Administrador SHALL incluir: nombre del visitante, dirección de correo electrónico del visitante, asunto y contenido del mensaje recibido.
5. IF el envío del correo electrónico falla, THEN THE Web SHALL registrar el error internamente y mostrar al visitante un mensaje indicando que el mensaje ha sido recibido pero que puede haber un problema con la confirmación por correo.
6. THE Administrador SHALL poder configurar la dirección de correo electrónico destinataria de las notificaciones de contacto desde el panel de administración.

---

### Requisito 12: Formulario de contacto — Protección anti-spam

**User Story:** Como administrador, quiero que el formulario de contacto esté protegido contra el envío automatizado de mensajes, para evitar el spam y el abuso del sistema.

#### Criterios de Aceptación

1. THE Formulario_Contacto SHALL incorporar un mecanismo de verificación anti-spam (como reCAPTCHA v3 o tecnología equivalente) antes de permitir el envío.
2. WHEN el mecanismo anti-spam determina que el envío es automatizado o sospechoso, THE Formulario_Contacto SHALL rechazar el envío y mostrar un mensaje de error al visitante.
3. WHEN el mecanismo anti-spam no puede verificarse por un error externo, THE Formulario_Contacto SHALL rechazar el envío y mostrar un mensaje de error al visitante.
4. THE mecanismo anti-spam SHALL ser configurable por el Administrador desde el panel de administración (clave del servicio utilizado).
5. IF la clave pública de reCAPTCHA (`recaptcha_site_key`) no está configurada en los ajustes, THEN THE Formulario_Contacto SHALL omitir la verificación anti-spam y permitir el envío directamente, sin requerir token. Esto garantiza que el formulario funcione en entornos de desarrollo o staging sin claves configuradas.

---

### Requisito 13: Formulario de contacto — Aceptación legal

**User Story:** Como administrador, quiero que el visitante acepte explícitamente la política de privacidad antes de enviar el formulario, para cumplir con la normativa de protección de datos.

#### Criterios de Aceptación

1. THE Formulario_Contacto SHALL incluir un checkbox de aceptación legal que el visitante deba marcar obligatoriamente para poder enviar el formulario.
2. THE checkbox de aceptación legal SHALL mostrar un texto con un enlace a la política de privacidad y/o aviso legal de la comunidad.
3. WHEN el visitante intenta enviar el formulario sin haber marcado el checkbox de aceptación legal, THE Formulario_Contacto SHALL mostrar un mensaje de error indicando que la aceptación es obligatoria y no enviar el formulario.
4. THE Administrador SHALL poder configurar el texto del checkbox de aceptación legal y la URL del documento legal enlazado desde el panel de administración.

---

### Requisito 14: Panel de administración — Bandeja de mensajes de contacto

**User Story:** Como administrador, quiero consultar todos los mensajes de contacto recibidos desde el panel de administración, para gestionar y responder las consultas de los visitantes.

#### Criterios de Aceptación

1. THE Panel_Administración SHALL mostrar una lista con todos los mensajes de contacto recibidos a través del Formulario_Contacto.
2. THE lista de mensajes SHALL mostrar para cada mensaje: nombre del visitante, dirección de correo electrónico, asunto, fecha y hora de recepción.
3. WHEN el Administrador selecciona un mensaje de la lista, THE Panel_Administración SHALL mostrar el contenido completo del mensaje.
4. THE Panel_Administración SHALL permitir al Administrador marcar un mensaje como leído o no leído.
5. THE Panel_Administración SHALL mostrar de forma diferenciada los mensajes no leídos respecto a los ya leídos.
6. THE Panel_Administración SHALL permitir al Administrador eliminar mensajes de la bandeja de entrada.
7. IF el Administrador elimina un mensaje, THEN THE Panel_Administración SHALL solicitar confirmación antes de proceder con la eliminación.
8. THE lista de mensajes SHALL poder ordenarse por fecha de recepción y por estado de lectura.

---

### Requisito 15: Páginas legales

**User Story:** Como visitante, quiero acceder a la política de privacidad y al aviso legal de la comunidad, para conocer cómo se tratan mis datos y las condiciones de uso de la web.

#### Criterios de Aceptación

1. THE Web SHALL incluir una página de política de privacidad accesible desde el footer y desde el Formulario_Contacto.
2. THE Web SHALL incluir una página de aviso legal accesible desde el footer.
3. THE Administrador SHALL poder editar el contenido de la página de política de privacidad y de la página de aviso legal desde el Panel_Administración.
4. THE Web SHALL mostrar las páginas de política de privacidad y aviso legal en Euskera y en Castellano según el idioma activo seleccionado por el visitante.

---

### Requisito 16: SEO y metadatos

**User Story:** Como administrador, quiero que la web tenga metadatos SEO básicos, para mejorar su visibilidad en buscadores.

#### Criterios de Aceptación

1. THE Web SHALL asignar a cada página pública un título único y descriptivo en el idioma activo.
2. THE Web SHALL incluir en cada página pública una meta description en el idioma activo.
3. THE Web SHALL utilizar URLs amigables y descriptivas en todas las rutas públicas, sin parámetros numéricos en la ruta.
4. THE Web SHALL publicar un archivo `sitemap.xml` accesible públicamente que liste las URLs de la parte pública.
5. THE Web SHALL incluir un archivo `robots.txt` que permita la indexación de la Parte_Pública y bloquee la indexación del Panel_Administración.

---

### Requisito 17: Seguridad básica

**User Story:** Como administrador, quiero que la web implemente medidas de seguridad básicas, para proteger los datos de los vecinos y la integridad del sistema.

#### Criterios de Aceptación

1. THE Web SHALL realizar todas las comunicaciones entre el navegador y el servidor sobre HTTPS.
2. THE Web SHALL incluir protección CSRF en todos los formularios de la Parte_Pública y del Panel_Administración.
3. THE Web SHALL enviar las cabeceras de seguridad HTTP `X-Frame-Options`, `X-Content-Type-Options` y `Referrer-Policy` en todas las respuestas.
4. THE Web SHALL expirar automáticamente la sesión del Administrador tras un período de inactividad configurable, con un valor por defecto de 120 minutos.
5. WHEN el Administrador cierra sesión explícitamente, THE Web SHALL invalidar la sesión activa y redirigir al Administrador a la página de inicio de sesión.
