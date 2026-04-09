# Documento de Diseño Técnico — rediseño-front

## Visión General

Rediseño responsive de la parte pública de la web de la comunidad de vecinos, enfocado en mejorar la experiencia en dispositivos móviles, tablets y escritorio. El proyecto mantiene la arquitectura Laravel 13 + Livewire actual, pero reestructura los layouts Blade, los componentes Livewire y la estrategia de CSS para garantizar una experiencia fluida en todos los breakpoints. Se introduce un Hero Slider en la página de inicio, se rediseñan las tarjetas de avisos para incluir imagen de cabecera, se mejora la accesibilidad con gestión de foco y safe areas, y se implementa una navegación sticky que permanece visible durante todo el scroll.

### Decisiones de diseño clave

- **Breakpoints de referencia**: Se mantienen los breakpoints estándar de Tailwind CSS v4 (sm=640px, md=768px, lg=1024px, xl=1280px) sin cambios.
- **Nav sticky mejorada**: La barra de navegación (`<header>` en `layouts/public.blade.php`) ahora usa `sticky top-0 z-50` y respeta `env(safe-area-inset-top)` en iOS mediante CSS personalizado en `resources/css/app.css`.
- **Contenedores responsivos**: El contenedor principal mantiene `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`, pero se añaden validaciones CSS para evitar márgenes excesivos en tabletas.
- **Hero Slider**: Componente Livewire nuevo o mejorado (si existe) que maneja autoplay, paginación manual y gestos. Se posiciona antes de cualquier contenido de la homepage.
- **Hero Slider**: El autoplay queda coordinado desde Alpine en la vista y mediante eventos de browser (`start-autoplay`, `autoplay-reset`) disparados por el componente Livewire. No se usa `wire:init` para iniciar autoplay porque provocaba errores de método inexistente durante los tests de navegador y en renderizados iniciales lentos.
- **Tarjeta_Aviso rediseñada**: Las tarjetas ahora incluyen:
  - Imagen de cabecera (si existe): `aspect-video` con `object-cover`
  - Texto superpuesto con gradiente oscuro opcional
  - Título, extracto y badges de ubicación dentro de un contenedor blanco `bg-white rounded-lg`
  - Layout grid responsive: 1 col (mobile) → 2 col (tablet) → 3 col (escritorio)
- **Lightbox mejorado**: Ahora detecta orientación landscape y ajusta el alto a `max-h-[85vh]` en landscape, `max-h-[90vh]` en portrait. Soporta swipe hacia abajo.
- **Gestión de foco (focus management)**: Implementado con Alpine.js o JavaScript vanilla en componentes Livewire. Cuando se abre un modal (menú móvil, lightbox), el foco se ve atrapado dentro (`focus trap`) y se restaura al cerrar.
- **Smoke hooks para Dusk**: Se añaden atributos `data-*` estables (`data-hero-slider`, `data-latest-notices`, `data-notices-grid`) en vistas públicas para validar estructura responsive sin depender de textos traducidos ni del marcado interno de Livewire.
- **Menú móvil en landscape**: El menú móvil usa `x-cloak`, `overflow-y-auto` y `max-h-[calc(100vh-4rem-env(safe-area-inset-top))]` para evitar flashes antes de inicializar Alpine y permitir scroll cuando el alto disponible es pequeño.
- **Safe area insets**: Se usa `env(safe-area-inset-*)` en CSS para respetar las zonas seguras en iOS (notch, Dynamic Island, barra de gestos).
- **Tipografía escalable**: Tamaños base: `text-sm` (14px) en mobile para cuerpo, ampliable a `text-base` (16px) en `md+`. Interlineado mínimo `leading-relaxed`.

---

## Estructura de archivos modificados

```
resources/
├── css/
│   └── app.css                    ← Añadir reglas CSS para safe areas, sticky positioning
├── views/
│   ├── layouts/
│   │   ├── public.blade.php       ← Refactorizar header sticky, footer responsive
│   │   └── admin.blade.php        ← (sin cambios esperados)
│   ├── public/
│   │   ├── home.blade.php         ← Añadir Hero_Slider, sección avisos destacados
│   │   ├── notices.blade.php      ← Integrar grid de Tarjeta_Aviso
│   │   ├── gallery.blade.php      ← Mantener, mejorar grid responsive
│   │   ├── contact.blade.php      ← Mejorar responsividad del formulario
│   │   ├── privacy-policy.blade.php   ← Nueva maquetación responsive con `max-w-prose`
│   │   └── legal-notice.blade.php     ← Nueva maquetación responsive
│   ├── livewire/
│   │   ├── public-notices.blade.php   ← Refactorizar para grid de Tarjeta_Aviso
│   │   ├── image-gallery.blade.php    ← Mejorar grid responsive, lightbox landscape
│   │   ├── contact-form.blade.php     ← Mejorar campos para touch (altura 44px)
│   │   ├── hero-slider.blade.php      ← NUEVO: Componente del carrusel de inicio
│   │   └── language-switcher.blade.php ← (sin cambios esperados)
│   ├── components/
│   │   ├── app-logo.blade.php     ← (sin cambios)
│   │   ├── notice-card.blade.php  ← NUEVO: Tarjeta_Aviso con imagen + opcionales
│   │   └── ... (otros componentes no afectados)
│   └── errors/
│       ├── 404.blade.php          ← NUEVO: Página de error responsive
│       └── 500.blade.php          ← NUEVO: Página de error responsive
``````

---

## Componentes principales y cambios

### 1. Header/Nav Sticky (`layouts/public.blade.php`)

**Responsabilidades:**
- Logo/nombre de comunidad alineado izquierda
- Menú hamburguesa visible en `< md`, oculto en `>= md`
- Barra de navegación horizontal visible solo en `>= md`
- Selector de idioma siempre visible
- Menú de opciones secundarias (puntos suspensivos) alineado derecha
- Sticky en la parte superior con `sticky top-0 z-50`
- Respeta safe area superior en iOS

**Clases Tailwind clave:**
```html
<header class="sticky top-0 z-50 bg-white border-b border-gray-200 [padding-top:env(safe-area-inset-top)]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Logo izquierda -->
    <!-- Menú hamburguesa: hidden md:hidden, mostrar solo en < md -->
    <!-- Nav horizontal desktop: hidden sm:hidden md:flex -->
    <!-- Selector idioma + menú opciones: siempre visible -->
  </div>
</header>
```

### 2. Hero_Slider (`livewire/hero-slider.blade.php` - NUEVO)

**Responsabilidades:**
- Mostrar último conjunto de imágenes (últimas 5-10 imágenes subidas a `Image`)
- Autoplay con intervalo configurable (ej: 5 segundos)
- Pausar autoplay al interactuar (click, swipe, hover)
- Puntos de paginación interactivos para navegar manualmente
- Texto superpuesto (título o descrición) sobre cada imagen
- Botón de CTA ("Ver más avisos" o similar)
- Altura responsive: `h-64 sm:h-80 md:h-96 lg:h-[500px]`
- Ancho completo de viewport, sin márgenes laterales

**Estructura:**
```blade
<div class="relative w-screen left-1/2 right-1/2 -mx-[50vw] h-64 sm:h-80 md:h-96 lg:h-[500px]">
  <!-- Imágenes rotativas con transiciones suaves -->
  <!-- Texto superpuesto con gradiente oscuro -->
  <!-- Botones: anterior/siguiente (hidden en mobile, visible en md+) -->
  <!-- Puntos de paginación: visible siempre -->
</div>
```

**Componente Livewire:**
- Propiedad: `currentImageIndex`, `images`, `autoplayInterval`
- Métodos: `nextImage()`, `previousImage()`, `goToImage($index)`, `toggleAutoplay()`
- Detecta inactividad del usuario y reanuda autoplay
- La vista expone `data-hero-slider` para tests smoke y coordina el temporizador en Alpine con `@start-autoplay.window` y `@autoplay-reset.window`

### 3. Tarjeta_Aviso (`components/notice-card.blade.php` - NUEVO)

**Responsabilidades:**
- Mostrar imagen de cabecera si el aviso la tiene (o placeholder)
- Título con tamaño escaldado
- Extracto truncado del contenido
- Badges de ubicación (portal/garaje)
- Indicador de fallback de traducción (si aplica)
- Responsive: funciona en cualquier columna del grid

**Estructura:**
```blade
<div class="rounded-lg border border-gray-200 bg-white overflow-hidden hover:shadow-lg transition-shadow">
  <!-- Imagen cabecera: aspect-video object-cover -->
  <div class="p-4 sm:p-6">
    <!-- Título -->
    <!-- Extracto text-sm sm:text-base -->
    <!-- Badges de ubicación con flex-wrap -->
    <!-- Indicador traducción -->
  </div>
</div>
```

### 4. Página de Inicio (`public/home.blade.php`)

**Cambios:**
1. Reemplazar/o complementar el contenido actual con Hero_Slider desde arriba
2. Añadir sección "Últimos avisos" con título en mayúsculas: `text-lg md:text-2xl font-bold uppercase`
3. Grid de Tarjeta_Aviso: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6`
4. Mantener el contenedor `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`
5. Exponer la rejilla principal con `data-latest-notices` para smoke tests Dusk orientados a layout

### 5. Página de Avisos (`public/notices.blade.php`, `livewire/public-notices.blade.php`)

**Cambios:**
1. Refactorizar componente `public-notices` para mostrar grid en lugar de listado vertical
2. Grid de Tarjeta_Aviso: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6`
3. Mantener selector de filtro (`w-full sm:w-64`), pero ahora sobre una fila separada o inline con grid
4. Paginación legible: botones con `min-h-10 px-3` para área táctil mínima
5. Exponer la rejilla con `data-notices-grid` para comprobaciones de columnas en Dusk sin acoplarse al HTML del paginador

### 6. Galería (`public/gallery.blade.php`, `livewire/image-gallery.blade.php`)

**Cambios:**
1. Grid: `grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4`
2. Mejorar lightbox Alpine.js:
   - Detectar orientación: `window.innerHeight < 768` = landscape probable
   - En landscape: `max-h-[85vh]`
   - En portrait: `max-h-[90vh]`
   - Soportar swipe hacia abajo (librería `hammer.js` o Alpine.js gestures)
   - Bloquear scroll del body cuando está abierto: `document.body.style.overflow = 'hidden'`

### 7. Formulario de Contacto (`public/contact.blade.php`, `livewire/contact-form.blade.php`)

**Cambios:**
1. Contenedor: `max-w-3xl mx-auto px-4 sm:px-6 lg:px-8`
2. Campos: `min-h-11` (44px mínimo) en inputs y textareas
3. Botón envío: `w-full sm:w-auto`
4. Errores: mostrar bajo campo sin cambiar layout (usar `absolute` si es necesario)
5. reCAPTCHA: Si ancho < 304px, usar CSS media query para ocultar o usar versión compacta

### 8. Páginas Secundarias

**Política de privacidad, Aviso legal (`public/privacy-policy.blade.php`, `public/legal-notice.blade.php`):**
- Contenedor: `max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-12`
- Texto base: `text-base leading-relaxed`
- Títulos: `text-2xl md:text-3xl font-bold mb-6`

**Página `/privado` (`public/private.blade.php`):**
- Contenedor centrado responsivo
- Mensaje claro + enlace a login (futuro)

**Páginas de error (`errors/404.blade.php`, `errors/500.blade.php`):**
- Contenedor centrado: `flex items-center justify-center min-h-screen`
- Mensaje simple + botón volver a inicio

---

## Estilos CSS globales (`resources/css/app.css`)

Añadir al final del archivo:

```css
/* Safe area insets para iOS */
header {
  padding-top: max(env(safe-area-inset-top), 0.25rem);
}

footer {
  padding-bottom: max(env(safe-area-inset-bottom), 0.25rem);
}

/* Focus trap: cuando un modal está abierto, aplicar esto al body */
body.modal-open {
  overflow: hidden;
}

/* Lightbox responsive */
@media (max-height: 500px) and (orientation: landscape) {
  .lightbox-image {
    max-height: 85vh;
  }
}

@media (orientation: portrait) {
  .lightbox-image {
    max-height: 90vh;
  }
}

/* Tipografía responsiva para cuerpo */
@media (max-width: 768px) {
  body {
    font-size: 14px; /* text-sm */
  }
}

@media (min-width: 768px) {
  body {
    font-size: 16px; /* text-base */
  }
}
```

---

## Librerías y dependencias

- **Alpine.js** (ya incluida en Flux): Para gestión de menú móvil, lightbox, focus trap
- **Hammer.js** (opcional): Para detectar swipe hacia abajo en lightbox. Alternativa: implementar con Touch events nativo.
- **Livewire 4**: Manejo de estado del Hero Slider, contacto, galería
- **TailwindCSS v4**: Clases de utilidad responsivas, `env()` para safe areas

---

## Mejoras de accesibilidad

1. **Focus management:** Use `autofocus` en el primer input del menú móvil al abrirse, restaurar foco al modal al cerrarse
2. **ARIA labels:** `aria-label` en botones icónicos, `role="dialog"` en modal Lightbox
3. **Keyboard navigation:** Focus trap en modal (Tab no sale del modal), Escape para cerrar
4. **Color contrast:** Mantener contraste WCAG AA mínimo en todos los textos
5. **Textos alternativos:** `alt` en imágenes siempre presente (propiedad `alt_text` del modelo)

---

## Testing responsivo

- **Breakpoints mínimos a probar:** 375px (iPhone SE), 768px (iPad mini), 1024px (iPad), 1440px (desktop)
- **Orientaciones:** Portrait y landscape en todos los breakpoints
- **Navegadores:** Chrome, Safari (iOS), Firefox, Edge
- **Tools:** Chrome DevTools device emulation, Browser Stack, actual devices
- **Suite smoke Dusk implementada:** `tests/Browser/PublicSiteResponsiveTest.php` cubre home móvil, menú móvil, avisos con filtro/grid, galería con lightbox y rotación a landscape, y formulario de contacto con persistencia de mensaje y comprobación de envío real de emails vía MailHog en Docker.
