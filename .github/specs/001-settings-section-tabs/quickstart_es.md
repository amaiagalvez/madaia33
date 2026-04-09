# Quickstart: Implementar Secciones de Settings

## Prerrequisitos

- Docker Compose disponible
- Ejecutar todos los comandos en Docker como usuario non-root

## 1. Preparar/refrescar entorno

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer install
```

## 2. Crear y aplicar cambios de base de datos

- Añadir migración para introducir `section` en `settings` y rellenar valores existentes.
- Asegurar fallback a `general` para keys ambiguas.

Ejecutar migraciones:

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan migrate
```

## 3. Actualizar comportamiento backend y UI

- Actualizar atributos fillable del modelo `Setting` para incluir `section`.
- Actualizar behavior de factory/seeding relevante.
- Refactorizar componente Livewire `AdminSettings` para:
  - Cargar settings agrupados por sección
  - Renderizar pestañas alfabéticas
  - Seleccionar por defecto la primera pestaña en carga de página
  - Guardar solo settings de la sección activa
- Actualizar vista Blade para mostrar pestañas de sección y formularios acotados por sección.
- Añadir/actualizar keys de traducción para etiquetas de sección en `lang/eu` y `lang/es`.

## 4. Validar calidad y tests

Ejecutar primero quality gate:

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality
```

Ejecutar tests focalizados:

```bash
docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --filter=AdminSettings
```

## 5. Checklist de verificación manual

- La página de settings muestra una pestaña por sección no vacía.
- Las pestañas están en orden alfabético.
- La primera pestaña se selecciona en cada carga de página.
- Editar/guardar en una pestaña no modifica otras secciones.
- Las keys existentes de settings siguen siendo accesibles.
- Las keys migradas desconocidas aparecen bajo `general`.
