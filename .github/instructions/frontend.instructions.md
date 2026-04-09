---
applyTo: "resources/views/**"
---

# Convenciones de Frontend

## Stack

- **TailwindCSS v4** — clases de utilidad, sin CSS personalizado salvo en `resources/css/app.css`
- **Flux UI v2** — componentes `<flux:*>` para el panel de administración (botones, inputs, modales, tablas, badges, etc.)
- **Livewire 4** — componentes en `resources/views/livewire/` con directivas `wire:model`, `wire:click`, `wire:loading`, etc.
- **Blade** — layouts en `resources/views/layouts/`, componentes en `resources/views/components/`
- **Alpine.js** — para interacciones client-side ligeras sin necesitar un componente Livewire completo

## Layouts

Hay dos layouts principales:

- `<x-layouts::public>` — parte pública (visitantes sin autenticación). Acepta `:title`.
- `<x-layouts::admin>` — panel de administración. Acepta `:title` y `:heading`.

Usar siempre el layout correcto según la sección. No crear layouts nuevos sin aprobación.

## Paleta de colores y estilo

Paleta de marca activa (fuente de verdad para frontend):

- `#edd2c7` (base cálida clara)
- `#f1bd4d` (acento dorado)
- `#b9a7a5` (tono neutro medio)
- `#d9755b` (acento terracota)
- `#793d3d` (acento principal oscuro)

La parte pública mantiene base neutra, pero debe usar estos acentos para mejorar jerarquía:

- Base recomendada: `bg-white`, `text-gray-900`, `text-gray-600`, `border-gray-200`
- Se permiten fondos de sección con gradientes suaves (`bg-gradient-to-*`) y contraste AA
- Tarjetas/paneles: `bg-white border border-gray-200 rounded-lg/rounded-2xl shadow-sm`
- Estado activo en nav: `text-gray-900 underline underline-offset-4` o equivalente accesible
- Hover y focus siempre visibles (`hover:*` + `focus-visible:ring-*`)

Guardar y mantener estos colores en `resources/css/app.css` (tokens de marca) y consumirlos desde clases/components. Evitar introducir nuevas paletas sin actualizar primero ese archivo.

El panel de administración usa `bg-gray-100` como fondo y `bg-white` para sidebar y tarjetas.

## Dirección visual (público)

- Cada página pública debe tener un bloque de cabecera claro (hero o encabezado de sección)
- Evitar pantallas "planas": usar capas sutiles (gradiente, borde, sombra suave)
- Priorizar tipografía expresiva en títulos: `tracking-tight`, escala responsiva (`text-3xl md:text-4xl`)
- Mantener consistencia entre páginas con patrones reutilizables

## Contenedor principal

En vistas públicas usar siempre:

```html
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12"></div>
```

## Textos e i18n

- **Siempre** usar `{{ __('clave.traduccion') }}` para textos visibles al usuario. Nunca texto hardcodeado.
- Los archivos de traducciones están en `lang/eu/` (Euskera, principal) y `lang/es/` (Castellano).
- El idioma activo se obtiene con `app()->getLocale()`.

## Iconos

Usar SVG inline con clases Tailwind. Ejemplo estándar:

```html
<svg
  class="h-5 w-5 shrink-0"
  fill="none"
  viewBox="0 0 24 24"
  stroke-width="1.5"
  stroke="currentColor"
  aria-hidden="true"
>
  <path stroke-linecap="round" stroke-linejoin="round" d="..." />
</svg>
```

No usar librerías de iconos externas. Los paths SVG son de Heroicons.

## Componentes Livewire

- Las vistas de componentes Livewire están en `resources/views/livewire/`.
- Deben tener un único elemento raíz (`<div>`).
- Estados de carga con `wire:loading` y `wire:loading.remove`.
- Para formularios en la parte pública, evitar `<flux:*>` y usar HTML semántico con clases Tailwind.
- En el panel de administración, usar `<flux:*>` para formularios, tablas, modales y botones.

## Accesibilidad

- Añadir `aria-label` en elementos de navegación: `<nav aria-label="{{ __('...') }}">`.
- Los SVGs decorativos deben llevar `aria-hidden="true"`.
- Los elementos interactivos deben tener etiquetas `<label>` asociadas.
- Usar `focus:outline-none focus:ring-1 focus:ring-gray-500` en inputs.
- Añadir `aria-current="page"` en navegación activa.
- Incluir enlace de salto a contenido principal (skip link) en layout público.

## SEO (vistas públicas)

Cada vista pública debe incluir:

```blade
@push('meta')
    <meta name="description" content="{{ __('...descripcion...') }}">
@endpush
```

El `<title>` lo gestiona automáticamente el layout mediante el prop `:title`.

- Incluir un único `<h1>` por página (puede ser `sr-only` si visualmente no conviene mostrarlo).
- Priorizar descripciones específicas de página; evitar metadescriptions genéricas.

## Selectores estables para tests

- Para zonas críticas usar atributos `data-*` estables (`data-page`, `data-page-hero`, `data-*-grid`).
- No acoplar tests solo a cadenas largas de clases Tailwind cuando exista alternativa semántica.

## Estado vacío

Para listas sin resultados, usar este patrón:

```html
<div
  class="rounded-lg border border-gray-200 bg-gray-50 px-6 py-12 text-center"
>
  <p class="text-gray-500 text-sm">{{ __('...mensaje...') }}</p>
</div>
```

## Badges / etiquetas de estado

```html
<span
  class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800"
>
  texto
</span>
```

Colores disponibles: `amber` (advertencia), `green` (éxito), `red` (error), `gray` (neutro).

## Paginación

Usar el paginador de Livewire/Laravel. No usar paginación personalizada.

## Imágenes

- Almacenadas en `storage/app/public`, accedidas vía `Storage::url($path)`.
- Usar `loading="lazy"` en todas las imágenes de galería.
- Siempre incluir `alt="{{ $image->alt_text }}"` (accessor bilingüe del modelo).
