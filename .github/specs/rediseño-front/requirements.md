# Documento de Requisitos

## Introducción

Mejora de la experiencia responsive de la parte pública de la web de la comunidad de vecinos. El objetivo es que el layout, la navegación, la página de inicio, la página de avisos, la galería y el formulario de contacto ofrezcan una experiencia óptima en dispositivos móviles, tablets y escritorio, con transiciones fluidas entre los distintos breakpoints y sin alterar la funcionalidad existente.

## Glosario

- **Parte_Pública_Móvil**: La parte pública de la web vista desde un dispositivo con pantalla estrecha (< 768 px).
- **Parte_Pública_Tablet**: La parte pública de la web vista desde un dispositivo con pantalla entre 768 px y 1023 px.
- **Menú_Móvil**: Menú de navegación desplegable que aparece en pantallas menores a `md` en lugar de la barra de navegación horizontal.
- **Breakpoint**: Punto de ruptura de Tailwind CSS (sm=640px, md=768px, lg=1024px, xl=1280px).
- **Contenedor**: El `<div>` con `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8` que centra y limita el ancho del contenido principal.
- **Lightbox**: Modal de visualización de imagen a pantalla completa en la galería.
- **Hero_Slider**: Componente de la página de inicio que muestra las últimas imágenes subidas como carrusel de ancho completo con texto superpuesto y paginación.
- **Tarjeta_Aviso**: Representación visual de un aviso con imagen de cabecera (si existe), título, extracto de texto y badges de ubicación.
- **Nav_Sticky**: Barra de navegación que permanece visible fijada en la parte superior de la pantalla durante el scroll.

---

## Requisitos

### Requisito 1: Navegación móvil

**User Story:** Como visitante que accede desde un móvil, quiero un menú de navegación accesible y usable, para poder navegar entre las secciones sin dificultad.

#### Criterios de Aceptación

1. WHEN el ancho de la pantalla es inferior al breakpoint `md` (768px), THE Layout_Público SHALL ocultar la barra de navegación horizontal y mostrar un botón de menú hamburguesa.
2. WHEN el visitante pulsa el botón hamburguesa, THE Menú_Móvil SHALL desplegarse mostrando todos los enlaces de navegación en columna.
3. WHEN el visitante pulsa de nuevo el botón hamburguesa o navega a otra página, THE Menú_Móvil SHALL cerrarse.
4. THE Menú_Móvil SHALL mostrar cada enlace con área táctil mínima de 44×44px para facilitar la interacción con el dedo.
5. THE Layout_Público SHALL mostrar el selector de idioma visible en todo momento tanto en escritorio como en móvil y tablet.
6. WHEN el ancho de la pantalla está entre `md` (768px) y `lg` (1023px), THE Layout_Público SHALL mostrar la barra de navegación horizontal con todos los enlaces visibles sin truncarse.
7. THE Nav_Sticky SHALL permanecer visible y pegada al tope de la pantalla en todo momento durante el scroll, en cualquier breakpoint.
8. THE Nav_Sticky SHALL mostrar el logo o nombre de la comunidad a la izquierda, el menú hamburguesa a la izquierda del logo en mobile, y un menú de acción secundaria (opciones) a la derecha.

---

### Requisito 2: Layout público — estructura responsive

**User Story:** Como visitante móvil, quiero que el contenido se adapte al ancho de mi pantalla, para leer y navegar sin necesidad de hacer zoom o scroll horizontal.

#### Criterios de Aceptación

1. THE Contenedor principal SHALL usar padding horizontal mínimo de `px-4` en mobile y aumentarlo progresivamente en breakpoints superiores (`sm:px-6 lg:px-8`).
2. THE Layout_Público SHALL evitar desbordamiento horizontal (`overflow-x: hidden`) en cualquier breakpoint.
3. THE Footer SHALL reorganizarse en columna en mobile (copyright encima, enlaces de navegación debajo) y en fila en `sm` o superior.
4. THE Header SHALL mantener altura mínima de `h-16` en todos los breakpoints y permanecer pegado al tope (`sticky top-0`).
5. WHEN el ancho de la pantalla está en el rango tablet (`md` a `lg`), THE Contenedor principal SHALL limitar su ancho máximo correctamente sin dejar márgenes laterales vacíos excesivos.

---

### Requisito 3: Página de inicio responsive

**User Story:** Como visitante móvil, quiero que la página de inicio sea legible, visual y estructurada en pantalla pequeña, para obtener una primera impresión atractiva de la web.

#### Criterios de Aceptación

1. THE Página_Inicio SHALL mostrar un Hero_Slider de ancho completo como elemento destacado, sin márgenes laterales, con texto superpuesto sobre la imagen y un botón de llamada a la acción.
2. THE Hero_Slider SHALL ocupar el ancho completo de la pantalla en todos los breakpoints y tener una altura proporcional (p.ej. `h-64` en mobile, `h-96` en `md`, `h-[500px]` en `lg`).
3. THE Hero_Slider SHALL mostrar puntos de paginación en la parte inferior para indicar el slide activo y permitir la navegación manual.
4. THE Hero_Slider SHALL avanzar automáticamente entre imágenes con un intervalo configurable, y pausarse al interactuar el visitante.
5. THE Página_Inicio SHALL incluir una sección de últimos avisos destacados (equivalente a "Popular Properties") con un título de sección en mayúsculas y negrita, mostrando las Tarjeta_Aviso en grid.
6. THE grid de Tarjeta_Aviso en la página de inicio SHALL mostrar 1 columna en mobile, 2 en `sm` (tablet) y 3 en `lg` (escritorio).
7. THE Página_Inicio SHALL usar tamaño de fuente `text-2xl` como máximo en mobile para el texto del Hero_Slider, ampliable a `text-4xl` en `md` o superior.
8. WHEN el ancho de la pantalla está en rango tablet, THE Página_Inicio SHALL aprovechar el espacio adicional mostrando el grid de avisos en 2 columnas sin saltos bruscos.

---

### Requisito 4: Página de avisos responsive

**User Story:** Como visitante móvil, quiero leer los avisos cómodamente en mi dispositivo, para estar informado sin necesidad de hacer zoom.

#### Criterios de Aceptación

1. THE listado de avisos SHALL mostrar cada aviso como una Tarjeta_Aviso con imagen de cabecera (si el aviso la tiene), título, extracto del contenido y badges de ubicación.
2. THE listado de avisos SHALL mostrar las Tarjeta_Aviso en 1 columna en mobile, 2 en `sm` (tablet) y 3 en `lg` (escritorio).
2. THE selector de filtro por portal/planta SHALL ocupar ancho completo (`w-full`) en mobile y limitarse a `sm:w-64` en pantallas más grandes.
3. THE badges de ubicación (portal/garaje) SHALL fluir en múltiples líneas (`flex-wrap`) cuando no quepan en una sola.
4. THE paginación SHALL ser legible y usable con el dedo en mobile y tablet (botones con área táctil suficiente).
5. THE indicador de fallback de traducción SHALL ser visible sin truncarse en pantallas estrechas.
6. WHEN el ancho de la pantalla está en rango tablet, THE tarjeta de aviso SHALL mostrar el título y el badge de fallback en la misma fila si el espacio lo permite.

---

### Requisito 5: Galería responsive

**User Story:** Como visitante móvil, quiero ver las imágenes en una cuadrícula adaptada a mi pantalla, para disfrutar del contenido visual sin dificultad.

#### Criterios de Aceptación

1. THE cuadrícula de imágenes SHALL mostrar 2 columnas en mobile, 3 en `sm` (tablet) y 4 en `lg` (escritorio).
2. THE Lightbox SHALL ocupar el 100% de la pantalla en mobile con la imagen centrada y ajustada al viewport (`max-h-[90vh]`).
3. THE botón de cierre del Lightbox SHALL estar claramente visible y accesible con el dedo (tamaño mínimo 44×44px) en mobile y tablet.
4. WHEN el Lightbox está abierto en mobile o tablet, THE scroll del body SHALL bloquearse para evitar confusión.
5. WHEN el ancho de la pantalla está en rango tablet, THE Lightbox SHALL aprovechar el espacio adicional mostrando la imagen más grande dentro de los límites `max-h-[90vh]`.

---

### Requisito 6: Formulario de contacto responsive

**User Story:** Como visitante móvil, quiero rellenar el formulario de contacto cómodamente desde mi dispositivo, para enviar mi consulta sin esfuerzo.

#### Criterios de Aceptación

1. THE formulario de contacto SHALL ocupar el ancho completo disponible en mobile, con el contenedor limitado a `max-w-3xl` en pantallas grandes.
2. THE campos del formulario SHALL tener altura de al menos 44px para facilitar la interacción táctil en mobile y tablet.
3. THE botón de envío SHALL ocupar ancho completo (`w-full`) en mobile y ajustarse al contenido (`w-auto`) en `sm` o superior.
4. THE mensajes de error de validación SHALL mostrarse debajo de cada campo sin desplazar el layout.
5. IF el formulario incluye reCAPTCHA, THEN THE widget SHALL redimensionarse o adaptarse cuando el ancho disponible sea inferior a su tamaño estándar (304px).
6. WHEN el ancho de la pantalla está en rango tablet, THE formulario SHALL usar el ancho completo disponible dentro del contenedor `max-w-3xl` sin mostrar espacios en blanco excesivos a los lados.

---

### Requisito 7: Páginas secundarias responsive

**User Story:** Como visitante móvil, quiero que todas las páginas de la web sean legibles en mi dispositivo, para acceder a cualquier contenido sin dificultad.

#### Criterios de Aceptación

1. THE página de política de privacidad SHALL mostrar el contenido en una columna de ancho completo en mobile, con el contenedor limitado a `max-w-3xl` en pantallas grandes.
2. THE página de aviso legal SHALL aplicar las mismas reglas de layout que la página de política de privacidad.
3. THE página `/privado` (placeholder de área privada) SHALL mostrar su contenido centrado y legible en mobile, tablet y escritorio.
4. THE página de error 404 SHALL mostrar el mensaje de error y el enlace de vuelta a inicio de forma visible y usable en cualquier breakpoint.
5. THE página de error 500 SHALL aplicar las mismas reglas de layout que la página de error 404.

---

### Requisito 8: Orientación landscape en móvil

**User Story:** Como visitante que usa el móvil en horizontal, quiero que la web sea usable en esa orientación, para no tener que rotar el dispositivo.

#### Criterios de Aceptación

1. WHEN el dispositivo está en orientación landscape y el ancho es inferior a `lg` (1024px), THE Header SHALL permanecer visible sin ocupar más de un tercio del alto disponible de la pantalla.
2. WHEN el Lightbox está abierto en orientación landscape, THE imagen SHALL ajustarse al alto disponible del viewport con `max-h-[85vh]` para evitar recorte.
3. WHEN el Menú_Móvil está abierto en orientación landscape, THE lista de enlaces SHALL ser desplazable verticalmente si no cabe en el alto de la pantalla.
4. THE cuadrícula de galería SHALL funcionar correctamente en landscape, usando 3 columnas en mobile landscape (`sm`) y 4 en tablet landscape (`lg`).

---

### Requisito 9: Gestión del foco (accesibilidad)

**User Story:** Como visitante que navega con teclado o tecnología asistiva, quiero que el foco se gestione correctamente al abrir y cerrar elementos interactivos, para no perder mi posición en la página.

#### Criterios de Aceptación

1. WHEN el Menú_Móvil se abre, THE foco SHALL moverse automáticamente al primer enlace del menú.
2. WHEN el Menú_Móvil se cierra, THE foco SHALL volver al botón hamburguesa que lo abrió.
3. WHEN el Lightbox se abre, THE foco SHALL moverse al botón de cierre o a la imagen.
4. WHEN el Lightbox se cierra, THE foco SHALL volver al botón de la galería que lo abrió.
5. WHEN el Menú_Móvil o el Lightbox están abiertos, THE foco SHALL quedar atrapado dentro del componente (focus trap) y no poder salir con Tab hacia elementos del fondo.

---

### Requisito 10: Tipografía y legibilidad del cuerpo de texto

**User Story:** Como visitante móvil, quiero que el texto sea legible sin esfuerzo, para leer el contenido cómodamente sin hacer zoom.

#### Criterios de Aceptación

1. THE cuerpo de texto de los avisos SHALL usar un tamaño mínimo de `text-sm` (14px) en mobile y `text-base` (16px) en `md` o superior.
2. THE cuerpo de texto de las páginas legales (privacidad, aviso legal) SHALL usar `text-base` (16px) como mínimo en todos los breakpoints.
3. THE interlineado del cuerpo de texto SHALL ser al menos `leading-relaxed` en todos los breakpoints para mejorar la legibilidad.
4. THE ancho de línea del cuerpo de texto en páginas de contenido largo (legales, aviso individual) SHALL limitarse a `max-w-prose` o equivalente para evitar líneas excesivamente largas en pantallas anchas.

---

### Requisito 11: Gestos táctiles en el lightbox

**User Story:** Como visitante que usa el móvil con el dedo, quiero poder cerrar el lightbox con un gesto, para no depender exclusivamente del botón de cierre.

#### Criterios de Aceptación

1. WHEN el visitante hace swipe hacia abajo sobre el Lightbox en mobile o tablet, THE Lightbox SHALL cerrarse.
2. WHEN el visitante pulsa fuera de la imagen en el Lightbox, THE Lightbox SHALL cerrarse.
3. WHEN el visitante pulsa la tecla `Escape`, THE Lightbox SHALL cerrarse (comportamiento ya existente, debe mantenerse).

---

### Requisito 12: Safe area insets en iOS

**User Story:** Como visitante con un iPhone con notch o Dynamic Island, quiero que el contenido no quede tapado por la muesca o la barra de sistema, para ver toda la interfaz correctamente.

#### Criterios de Aceptación

1. THE Header sticky SHALL respetar el safe area superior del dispositivo usando `env(safe-area-inset-top)` en su padding o posición.
2. THE Menú_Móvil desplegable SHALL respetar el safe area superior e inferior del dispositivo.
3. THE Footer SHALL respetar el safe area inferior del dispositivo usando `env(safe-area-inset-bottom)` para no quedar tapado por la barra de gestos de iOS.
4. THE Lightbox SHALL respetar los safe area insets en todos los lados para que el botón de cierre y la imagen no queden bajo elementos de sistema.
