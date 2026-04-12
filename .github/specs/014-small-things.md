- [x] Hay que marcar quien es la propietaria que es jefa de portal o de planta de garaje, en el formulario de locations, los que tengan alguna propiedad de ese location
Cuando se marca como jefa de portalo de garaje, automáticamente se le asigna el rol COMMUNITY_ADMIN y cuando se cambia se le quita el rol de COMMUNITY_ADMIN a la anterior y se le pone a la nueva

## Implementation Plan

### Goal

- [Implementar en el detalle/formulario de locations la selección de propietaria jefa (portal o garaje), limitada a propietarias con propiedad activa en ese location, y sincronizar automáticamente rol COMMUNITY_ADMIN + managed location al cambiar la jefa]

### Technical Decisions

- Reutilizar la relación existente `users <-> locations` (`managedLocations`) como fuente de verdad de “jefa” para cada `location` de tipo `portal` y `garage`.
- Mantener la lógica de asignación/revocación en backend (Livewire + Action/servicio dedicado) para garantizar consistencia y evitar reglas solo en UI.
- Resolver cambio de jefa en transacción: desasignar anterior, asignar nueva, y recalcular rol `COMMUNITY_ADMIN` únicamente cuando corresponda (no quitarlo si la usuaria sigue siendo jefa en otro location).
- Limitar opciones de selector a `owners` con asignación activa en propiedades del `location` actual (`property_assignments.end_date IS NULL`).
- Añadir textos i18n en `lang/eu` y `lang/es` para etiquetas/avisos de “jefa de portal/garaje”.

### Execution Steps

- [x]   1. Escribir tests (Feature/Livewire) que fallen para: listado de candidatas válidas por location, cambio de jefa, asignación automática de rol, revocación a la anterior, y no-revocación si conserva otra jefatura.
- [x]   2. Implementar en `LocationDetail` estado y acción para seleccionar/guardar jefa en locations `portal` y `garage`, ocultándolo en otros tipos.
- [x]   3. Extraer y aplicar lógica de sincronización de jefatura/roles en clase reusable (Action/Service) con transacción y consultas agrupadas.
- [x]   4. Ajustar vista `livewire.admin.locations.detail` para mostrar selector con candidatas filtradas y marca actual.
- [x]   5. Actualizar traducciones `lang/eu/*` y `lang/es/*` para nuevos textos.
- [x]   6. Ejecutar formateo y tests mínimos afectados.

### Work Items

- [x] `app/Livewire/Admin/LocationDetail.php`
- [x] `resources/views/livewire/admin/locations/detail.blade.php`
- [x] `app/Actions/...` (nueva acción de sincronización de jefatura si no existe equivalente reutilizable)
- [ ] `app/Models/User.php` (si requiere helper pequeño para rol COMMUNITY_ADMIN condicionado)
- [x] `tests/Feature/AdminOwnersAndLocationsTest.php`
- [x] `lang/eu/*.php` y `lang/es/*.php` (claves nuevas)

### Validation

- [x] TDD-based implementation when possible
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminUsersManagementTest.php`

- [x] Votaciones. En la pantalla bozketak el front, si ya he votado, no mostrar las opciones, solo un aviso de que ya he votado.
- [x] en el listtado de admin/portales se ha perdido el estilo de los botones de las locations, aplícale el mismo que tiene los botones de leido y no leido del listado de mensajes
- [x] en el listado admin/ubicaciones/{id} también se han perdido los formatos
- [x] en el listtado de admin/portales cambia el icono del eye por el de bars con el título "Ver fincas" y añade un botón edit para abrir el formulario del location.
- [ ] en el formulario de propietarias se han perdido los estilos
- [ ] en el listado de propietarias se han perdido los estilos de los filtros
- [ ] en el sublistado de propiedades debajo de cada propietario, también se han perdido los estilos

