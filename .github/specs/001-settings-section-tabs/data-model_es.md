# Modelo de Datos: Secciones de Settings

## Entidad: Setting

Representa una entrada de configuración persistida de la aplicación.

### Campos

- `id` (bigint, PK)
- `key` (string, único)
- `value` (text, nullable)
- `section` (string, requerido)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable; SoftDeletes)

### Reglas de Validación (alcance de la feature)

- `section` es obligatorio para cada setting activo.
- `section` debe ser uno de los valores permitidos:
  - `front`
  - `contact_form`
  - `gallery`
  - `general` (fallback/seguridad de migración)
- `key` permanece única.

### Relaciones

- No se introducen tablas relacionales nuevas en esta feature.
- `section` es un atributo restringido en `settings`, no una foreign key.

### Notas de Estado/Transición

- Las filas existentes pasan de agrupación implícita a agrupación explícita por `section` durante la migración.
- Las filas con agrupación indeterminada durante la migración pasan a `general`.

## Entidad Lógica: SettingsSection (derivada)

No se persiste como tabla. Representa metadatos de agrupación usados por la UI de administración.

### Atributos

- `id` (string identificador; uno de los valores de section permitidos)
- `label` (string de visualización localizada)
- `order` (alfabético por identificador para esta feature)

### Restricciones de Comportamiento UI

- Se muestra una pestaña de sección solo si existe al menos un setting en esa sección.
- La primera pestaña (alfabéticamente) se selecciona en cada carga de página.
