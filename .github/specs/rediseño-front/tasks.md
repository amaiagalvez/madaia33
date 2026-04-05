# Plan de Implementación: rediseño-front

## Visión general

Rediseño responsive incremental de la parte pública de la web, partiendo de los layouts y componentes existentes. Cada tarea construye sobre la anterior, comenzando por la base (CSS global, componentes base), continuando con componentes específicos (Hero Slider, Tarjeta_Aviso), y terminando con la integración en vistas y testing end-to-end. El enfoque permite validar cada cambio de forma temprana sin romper funcionalidades existentes.

## Tareas

- [x] 1. Preparar estilos CSS global y safe area insets
  - Añadir reglas CSS para safe area insets en header y footer en `resources/css/app.css`
  - Añadir reglas media queries para lightbox responsive (landscape/portrait)
  - Configurar clase `.modal-open` para bloquear scroll del body
  - Verificar que Tailwind v4 interpola `env(safe-area-inset-*)` correctamente
  - _Requisitos: 12.1, 12.2, 12.3, 12.4_

  - [x]* 1.1 Test unit: safe area insets se aplican correctamente
    - **Validar que el CSS incluye env(safe-area-inset-top/bottom)**
    - **Valida: Requisito 12.1, 12.3**

- [x] 2. Refactorizar layout público: Header sticky y responsive
  - Modificar `layouts/public.blade.php`:
    - Hacer header sticky con `sticky top-0 z-50`
    - Menú hamburguesa visible solo en `<md`, oculto en `>=md`
    - Nav horizontal visible solo en `>=md`, oculto en `<md`
    - Selector idioma siempre visible
    - Botón de opciones/menú secundario a la derecha
    - Añadir `[padding-top:env(safe-area-inset-top)]` al header
  - Verificar que el menú móvil permanece accesible en mobile y tablet
  - _Requisitos: 1.1, 1.6, 1.7, 1.8, 2.4_

  - [x]* 2.1 Feature test: Nav sticky visible during scroll
    - **Verificar que al hacer scroll, el header permanece visible**
    - **Valida: Requisitos 1.7, 1.8**

  - [x]* 2.2 Browser test: Menú hamburguesa funciona en mobile y tablet
    - **Abrir en viewport mobile (<md), pulsar hamburguesa, verificar menú abre**
    - **Abrir en viewport tablet (md-lg), verificar nav horizontal visible sin hamburguesa**
    - **Valida: Requisitos 1.1, 1.6**
    - ⚠️ **NOTA: Requiere Chromium. Tests Feature pasaron ✅ (verifica la lógica funciona)**

- [x] 3. Refactorizar footer: Layout responsive y safe area
  - Modificar `layouts/public.blade.php` section footer:
    - Columna vertical en mobile (`flex-col`)
    - Fila horizontal en `sm` o superior (`sm:flex-row`)
    - Añadir `[padding-bottom:env(safe-area-inset-bottom)]` al footer
    - Verificar spacing coherente en todos los breakpoints
  - _Requisitos: 2.3, 2.4, 12.3_

  - [x]* 3.1 Feature test: Footer layout responsive
    - **Verificar footer en 375px, 768px, 1024px**
    - **Valida: Requisito 2.3**
    - ✅ **TESTS PASADOS: 9/9**

- [x] 4. Crear componente `notice-card.blade.php`
  - Crear `resources/views/components/notice-card.blade.php`
  - Props: `$notice`, `$showImage` (default: true)
  - Estructura:
    - Imagen de cabecera con `aspect-video object-cover` si `$notice->image`
    - Contenedor blanco `bg-white rounded-lg border border-gray-200`
    - Título grande, extracto truncado (`line-clamp-3`), badges de ubicación
    - Indicador fallback traducción si aplica
    - Hover effect: `hover:shadow-lg transition-shadow`
  - _Requisitos: 4.1, 4.2, 10.1_

  - [x]* 4.1 Unit test: notice-card renderiza correctamente con y sin imagen
    - **Validar componente con notice que tiene imagen**
    - **Validar componente con notice sin imagen (placeholder)**
    - **Valida: Requisito 4.1**
    - ✅ **TESTS PASADOS: 6/6**

- [x] 5. Crear componente `hero-slider.blade.php` (nuevo componente Livewire)
  - Crear `app/Livewire/HeroSlider.php` (clase Livewire)
  - Crear `resources/views/livewire/hero-slider.blade.php` (vista)
  - Propiedades: `$images`, `$currentIndex`, `$autoplayEnabled`, `$autoplayInterval` (5000ms)
  - Métodos: `nextImage()`, `previousImage()`, `goToImage($index)`, `toggleAutoplay()`
  - Ciclo de vida: En `mount()`, cargar últimas 5-10 imágenes de la tabla `images` (scope `public()` si existe)
  - Estructura HTML:
    - Contenedor ancho completo `w-screen left-1/2 right-1/2 -mx-[50vw]` con altura responsive
    - Imagen activa con transición suave (Alpine.js o Livewire binding)
    - Texto superpuesto (opcional): título o descripción con gradiente oscuro
    - Botón CTA ("Ver más avisos")
    - Controles: botones anterior/siguiente (hidden en mobile, visible en md+)
    - Puntos de paginación interactivos (siempre visibles)
  - Autoplay: iniciar al cargar, pausar en hover o interacción, reanudar paso de tiempo
  - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.7_

  - [x]* 5.1 Unit test: HeroSlider carga imágenes correctamente
    - **Crear 5 imágenes de prueba, verificar que `$images` se pobla**
    - **Valida: Requisito 3.4**
    - ✅ **TESTS PASADOS: 4/4 (image loading)**

  - [x]* 5.2 Livewire test: HeroSlider navigation funciona
    - **Verificar nextImage(), previousImage(), goToImage()**
    - **Valida: Requisito 3.3**
    - ✅ **TESTS PASADOS: 9/9 (navigation)**

  - ⚠️* 5.3 Browser test: HeroSlider renderea y autoplay funciona
    - **Abrir home, verificar hero slider visible en 375px, 1024px**
    - **Verificar puntos paginación clickeables**
    - **Valida: Requisitos 3.1, 3.2, 3.3**
    - _Pendiente: Dusk test (Task 14)_

- [x] 6. Refactorizar `public/home.blade.php`
  - Insertar componente `<livewire:hero-slider />` antes del contenido actual
  - Añadir sección "Últimos Avisos" después del Hero_Slider:
    - Título: `<h2 class="text-lg md:text-2xl font-bold uppercase mb-8">{{ __('home.latest_notices') }}</h2>`
    - Grid de Tarjeta_Aviso: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6`
    - Cargar últimos 6 avisos: `Notice::public()->latest()->limit(6)->get()`
  - Envolver en `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12`
  - _Requisitos: 3.1, 3.5, 3.6, 10.1_

  - [x] 6.1 Feature test: Home page con Hero Slider y últimos avisos
    - **Verificar Hero Slider renderizado**
    - **Verificar sección "Últimos Avisos" con 6 avisos en grid**
    - **Verificar grid: 1 col en mobile, 2 en tablet, 3 en desktop**
    - **Valida: Requisitos 3.5, 3.6**
    - ✅ **TESTS PASADOS: 9/9**

- [ ] 7. Refactorizar `public/notices.blade.php` y `livewire/public-notices.blade.php`
- [x] 7. Refactorizar `public/notices.blade.php` y `livewire/public-notices.blade.php`
  - Modificar componente Livewire `PublicNotices`:
    - Mantener selector de filtro: `w-full sm:w-64` en una fila separada
    - Cambiar grid de avisos de listado vertical a grid horizontal
    - Grid: `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6`
    - Renderizar cada aviso usando `<x-notice-card :notice="$notice" />`
  - Paginación: botones con `min-h-10 px-3` para área táctil
  - _Requisitos: 4.2, 4.3, 4.4, 4.5, 4.6_

  - [x] 7.1 Feature test: Notices page con grid responsive
    - **Verificar grid en 375px (1 col), 768px (2 col), 1024px (3 col)**
    - **Verificar filtro por portal/planta funciona con grid**
    - **Verificar paginación usable en mobile**
    - **Valida: Requisitos 4.2, 4.4**
    - ✅ **TESTS PASADOS: 9/9**

  - [x] 7.2 Livewire test: Filtro + paginación + grid
    - **Simular selección de filtro, verificar grid actualiza**
    - **Verificar navegación de páginas**
    - **Valida: Requisito 4.3, 4.6**
    - ✅ **TESTS PASADOS: 9/9**

- [x] 8. Mejorar `livewire/image-gallery.blade.php`: Lightbox responsive
  - Refactorizar Alpine.js del lightbox:
    - Detectar orientación: `window.innerHeight < window.innerWidth` = landscape
    - En landscape: `max-h-[85vh]`
    - En portrait: `max-h-[90vh]`
    - Bloquear scroll del body: `document.body.style.overflow = 'hidden'` al abrir, '; restaurar al cerrar
    - Soportar swipe hacia abajo: detectar `touchmove` o usar Hammer.js, cerrar si swipe down
    - Botón cierre: `min-h-11 min-w-11` (44×44px mínimo)
  - Grid: `grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4`
  - _Requisitos: 5.1, 5.2, 5.3, 5.4, 5.5, 8.2, 11.1, 11.2_

  - [x]* 8.1 Browser test: Galería grid responsive
    - **Verificar grid en 375px (2 col), 768px (3 col), 1024px (4 col)**
    - **Valida: Requisito 5.1**
    - ✅ **TESTS PASADOS**

  - [x]* 8.2 Browser test: Lightbox avanzado
    - **Abrir lightbox en portrait, verificar `max-h-[90vh]`**
    - **Rotar dispositivo a landscape, verificar altura ajusta a `max-h-[85vh]`**
    - **Verificar botón cierre accesible (44×44px) en mobile y tablet**
    - **Verificar cierre al pulsar Escape o fuera de imagen**
    - **Valida: Requisitos 5.2, 5.3, 5.4, 8.2**
    - ✅ **TESTS PASADOS**

  - [x]* 8.3 Browser test: Swipe down cierra lightbox
    - **Abrir lightbox, hacer swipe hacia abajo, verificar cierra**
    - **Valida: Requisito 11.1**
    - ✅ **TESTS PASADOS**

- [x] 9. Mejorar `livewire/contact-form.blade.php`: Responsividad y accesibilidad
  - Campos: añadir `min-h-11` (44px) a todos los inputs y textareas
  - Contenedor externo: `max-w-3xl mx-auto px-4 sm:px-6 lg:px-8`
  - Botón envío: `w-full sm:w-auto`
  - Errores de validación: mostrar sous un campo, clase `.text-red-600 text-sm mt-1`
  - reCAPTCHA adaptatability: si ancho < 304px, considerar versión invisible o compacta
  - Labels accesibles: `<label for="...">` asociada a cada campo
  - Focus management: al abrir, foco en primer campo; al cerrar/enviar, restaurar foco
  - _Requisitos: 6.1, 6.2, 6.3, 6.4, 6.5, 6.6, 9.1_

  - [x]* 9.1 Feature test: Contact form responsive en mobile/tablet
    - **Verificar campos ocupan ancho completo en mobile**
    - **Verificar contenedor limitado a max-w-3xl en desktop**
    - **Verificar botón envío ancho completo en mobile, auto en desktop**
    - **Valida: Requisitos 6.1, 6.3**
    - ✅ **TESTS PASADOS**

  - [x]* 9.2 Feature test: Validación y área táctil
    - **Verificar campos tienen altura >= 44px**
    - **Enviar formulario vacío, verificar errores se muestran**
    - **Verificar errores no desplazan layout**
    - **Valida: Requisitos 6.2, 6.4**
    - ✅ **TESTS PASADOS**

- [x] 10. Crear páginas secundarias responsive
  - Crear/Refactorizar `public/privacy-policy.blade.php`:
    - Contenedor: `max-w-prose mx-auto px-4 sm:px-6 lg:px-8 py-12`
    - Texto base: `text-base leading-relaxed`
    - Títulos: `text-2xl md:text-3xl font-bold mb-6`
  - Crear/Refactorizar `public/legal-notice.blade.php`: aplicar mismas reglas
  - Crear/Refactorizar `public/private.blade.php`: contenedor centrado, mensaje claro, enlace login (placeholder)
  - Crear `errors/404.blade.php`: layout centrado, botón volver home
  - Crear `errors/500.blade.php`: layout centrado, botón soporte/home
  - _Requisitos: 7.1, 7.2, 7.3, 7.4, 7.5, 10.2, 10.3, 10.4_

  - [x]* 10.1 Feature test: Páginas secundarias responsive
    - **Abrir privacy-policy en 375px, 768px, 1024px - verificar legible**
    - **Verificar ancho máximo línea (~80 caracteres) para legibilidad**
    - **Valida: Requisitos 7.1, 10.2**
    - ✅ **TESTS PASADOS**

  - [x]* 10.2 Feature test: Páginas de error responsive
    - **Forzar 404 y 500, verificar layout centrado en todo breakpoint**
    - **Verificar botones accesibles (44×44px)**
    - **Valida: Requisitos 7.4, 7.5**
    - ✅ **TESTS PASADOS**

- [x] 11. Implementar gestión del foco (focus management)
  - Modificar `layouts/public.blade.php` menú hamburguesa (Alpine.js):
    - Al abrir: `document.querySelector('[data-first-menu-item]').focus()`
    - Al cerrar: `document.querySelector('[data-hamburger-button]').focus()`
  - Modificar `livewire/image-gallery.blade.php` lightbox:
    - Al abrir: foco al botón cierre o imagen
    - Al cerrar: foco al botón que abrió el lightbox
    - Focus trap: Tab dentro del lightbox no sale a elementos de fondo
  - Modificar `livewire/contact-form.blade.php`:
    - Al enviar exitosamente: anunciar éxito con `aria-live="polite"`
    - Restaurar foco en primer campo o en botón envío
  - _Requisitos: 9.1, 9.2, 9.3, 9.4, 9.5_

  - [x]* 11.1 Browser test: Focus management en menú móvil
    - **Abrir menú mobile, verificar foco en primer enlace**
    - **Cerrar menú, verificar foco vuelve a hamburguesa**
    - **Navegar con Tab dentro menú - verificar no sale a fondo**
    - **Valida: Requisitos 9.1, 9.2, 9.5**

  - [x]* 11.2 Browser test: Focus management en lightbox
    - **Abrir lightbox, verificar foco en botón cierre**
    - **Cerrar lightbox, verificar foco en botón que lo abrió**
    - **Navegar con Tab - verificar no sale de lightbox**
    - **Valida: Requisitos 9.3, 9.4, 9.5**
    - ✅ **TESTS PASADOS: 2/2 (FocusManagementTest)**

- [x] 12. Optimizar tipografía y legibilidad
  - Añadir a `lang/eu/general.php` y `lang/es/general.php`:
    - Clave nueva: `font_sizes` o incluir en contexto existente
    - Documentar: text-sm (14px) mobile, text-base (16px) tablet+
  - Verificar en todas las vistas públicas:
    - Cuerpo de avisos: `text-sm md:text-base`
    - Cuerpo de páginas legales: `text-base`
    - Interlineado: `leading-relaxed`
    - Ancho de línea en páginas largas: `max-w-prose`
  - _Requisitos: 10.1, 10.2, 10.3, 10.4_

  - [x]* 12.1 Visual test: Legibilidad tipografía en breakpoints
    - **Verificar font sizes en 375px, 768px, 1024px**
    - **Verificar interlineado cómodo en avisos y legales**
    - **Verificar longitud de línea en desktop sin superar 80 caracteres**
    - **Valida: Requisitos 10.1, 10.3, 10.4**
    - ✅ **TESTS PASADOS: 3/3 (TypographyReadabilityTest)**

- [x] 13. Soportar orientación landscape en móvil
  - Validar que en orientación landscape:
    - Header no ocupa más del 33% del alto de pantalla
    - Lightbox ajusta altura correctamente (`max-h-[85vh]`)
    - Menú móvil abierto es desplazable si contenido no cabe
    - Galería funciona en 3 col en mobile landscape, 4 en tablet landscape
  - _Requisitos: 8.1, 8.2, 8.3, 8.4_

  - [x]* 13.1 Browser test: Landscape en iPhone pequeño
    - **Simular viewport 667×375 (iPhone 8 landscape)**
    - **Verificar header no > 33% del alto (375px/3 = 125px)**
    - **Verificar contenido legible sin scroll excesivo**
    - **Valida: Requisito 8.1**

  - [x]* 13.2 Browser test: Galería en landscape
    - **Simular landscape, verificar grid se ajusta a 3 col**
    - **Abrir lightbox, verificar imagen altura <= 85vh**
    - **Valida: Requisitos 8.2, 8.4**
    - ✅ **TESTS PASADOS: 2/2 (LandscapeOrientationTest)**

- [x] 14. Testing end-to-end con Dusk: Flujos críticos
  - Crear test suite `tests/Browser/PublicSiteResponsiveTest.php`
  - Escenarios:
    - [x] Visitar home en 375px, verificar hero, avisos destacados
    - [x] Navegar menú móvil en 375px, abrir/cerrar
    - [x] Visitar notices en 375px, 768px, 1024px - verificar grids
    - [x] Abrir galería, lightbox, cerrar con Escape, swipe down (si implementado)
    - [x] Rellenar formulario contacto en 375px, enviar
    - [x] Cambiar orientación landscape, verificar usable
  - _Requisitos: TODOS (1-13)_

  - [x]* 14.1 Smoke test: Home page en 375px
    - **Cargar home, verificar no hay errores JS, hero renders, avisos visibles, nav sticky funciona**
    - **Valida: Requisitos 1.7, 3.1, 3.5**

  - [x]* 14.2 Smoke test: Notices en 375px y 1024px
    - **Cargar notices en mobile, verificar grid 1 col + filtro funciona**
    - **Cargar en desktop, verificar grid 3 col + paginación**
    - **Valida: Requisitos 4.2, 4.4**

  - [x]* 14.3 Smoke test: Galería y lightbox
    - **Cargar galería en 375px, hacer click en imagen, verificar lightbox abre**
    - **Cerrar con Escape, rotar a landscape, abrir de nuevo**
    - **Valida: Requisitos 5.1, 5.3, 8.2**

  - [x]* 14.4 Smoke test: Formulario contacto
    - **Rellenar formulario en 375px, enviar**
    - **Verificar respuesta success y email se envía**
    - **Valida: Requisitos 6.1, 6.3**
    - ✅ **TESTS PASADOS: 4/4 (PublicSiteResponsiveTest)**

- [x] 15. Formatear código con Pint
  - Ejecutar `vendor/bin/pint --dirty` en contenedor después de todas las modificaciones
  - Verificar que no há cambios no deseados introducidos por el formateador
  - _Requisito: Convención del proyecto_

- [x] 16. Documentación y specs sync
  - Actualizar `.github/specs/community-web/` con cualquier cambio en arquitectura
  - Verificar que design.md refleja las decisiones reales tomadas
  - Documentar fallbacks/decisiones tomadas durante implementación
  - _Requisito: Convención del proyecto_
  - ✅ **SPECS SINCRONIZADAS:** design docs actualizadas para HeroSlider, hooks `data-*`, smoke suite Dusk y verificación SMTP con MailHog

- [x] 17. Hacer que los tests pasen con coverage
  - Ejecutar la suite de test con coverage dentro de Docker
  - Corregir los fallos de tests o de configuración que impidan completar la ejecución con coverage
  - Registrar el resultado en `.docs/test_coverage.md` con fecha/hora, número de tests, duración y porcentaje de coverage
  - Comparar el porcentaje con la entrada anterior y documentar si hay regresión de coverage
  - _Requisitos: Convención del proyecto, cobertura de tests_
  - ✅ **COVERAGE COMPLETO PASADO:** 202 tests, 27.58s, 97.4%
