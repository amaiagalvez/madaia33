# Documento de Requisitos: Quality DB Query Guard

## Introducción

Esta feature añade controles automáticos para detectar regresiones de consultas a base de datos (consultas duplicadas, patrones N+1 y sobrecoste de queries) dentro del flujo de calidad del proyecto.

El objetivo es que el comando `quality` falle cuando una ruta o flujo crítico supere su presupuesto de consultas o introduzca una regresión evidente.

El stack existente usa Laravel 13, Livewire 4, Pest v4 y PHP 8.4. El pattern de conteo de queries ya existe en `tests/Feature/SettingHelpersTest.php` con `DB::enableQueryLog` + `DB::getQueryLog`.

---

## Glosario

- **System**: la aplicación.
- **Query Budget**: número máximo de consultas permitidas para un flujo concreto.
- **Flujo crítico**: ruta/pantalla prioritaria del panel o frontend con mayor riesgo de N+1.
- **Query Guard Test**: test de Pest que ejecuta un flujo y valida su coste SQL.
- **Regresión SQL**: incremento no esperado de consultas o repetición evitable de la misma sentencia.
- **Sentencia normalizada**: SQL con valores reemplazados por `?` para comparación entre llamadas.

---

## Requisitos

### Requisito 1: Suite dedicada de rendimiento SQL

**User Story:** Como equipo de desarrollo, quiero una suite enfocada en consultas SQL para detectar regresiones de rendimiento temprano.

#### Criterios de aceptación

1. THE System SHALL incluir una suite de tests dedicada a Query Guard bajo `tests/Feature/Performance/`.
2. THE System SHALL permitir ejecutar dicha suite de forma independiente para feedback rápido.
3. IF un test de Query Guard falla, THEN THE System SHALL marcar el estado global de calidad como fallido.

---

### Requisito 2: Presupuesto de consultas por flujo crítico

**User Story:** Como equipo de desarrollo, quiero definir máximos de consultas por flujo para bloquear regresiones N+1 y duplicadas.

#### Criterios de aceptación

1. THE System SHALL medir las consultas ejecutadas durante cada flujo crítico cubierto.
2. THE System SHALL permitir definir un umbral máximo de consultas por flujo.
3. IF el número de consultas supera el umbral, THEN THE System SHALL fallar el test correspondiente con un mensaje que indique el número real y el máximo esperado.
4. THE System SHALL documentar el motivo del umbral elegido para cada flujo con un comentario en el test.

---

### Requisito 3: Detección de sentencias SQL repetidas

**User Story:** Como equipo de desarrollo, quiero detectar repeticiones SQL sospechosas para identificar puntos de optimización rápida.

#### Criterios de aceptación

1. THE System SHALL permitir agrupar queries por sentencia SQL normalizada (valores reemplazados por `?`).
2. THE System SHALL permitir definir un máximo de repeticiones permitidas por sentencia por test.
3. IF una sentencia supera el límite de repetición permitido, THEN THE System SHALL fallar el test con un resumen legible de sentencias repetidas y su conteo.

---

### Requisito 4: Integración en el comando quality

**User Story:** Como equipo de desarrollo, quiero que el guard SQL forme parte del quality estándar para evitar olvidos.

#### Criterios de aceptación

1. THE System SHALL añadir un script `quality:queries` en `composer.json` que ejecute solo la suite `tests/Feature/Performance/`.
2. THE System SHALL incluir `quality:queries` dentro del script `quality` principal.
3. WHEN se ejecute `quality`, THE System SHALL ejecutar lint:check, analyse, quality:phpmd y quality:queries en ese orden.
4. IF `quality:queries` falla, THEN THE System SHALL devolver código de salida no exitoso.

---

### Requisito 5: Flujo Docker-first

**User Story:** Como equipo, quiero ejecutar Query Guard con el mismo flujo Docker del proyecto.

#### Criterios de aceptación

1. THE System SHALL ejecutar `quality:queries` dentro del contenedor Docker como usuario no root.
2. THE System SHALL ser compatible con `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 ...`.

---

### Requisito 6: Flujos críticos iniciales cubiertos

**User Story:** Como equipo de desarrollo, quiero que los 3 flujos con mayor riesgo histórico de N+1 estén cubiertos desde el primer día.

#### Criterios de aceptación

1. THE System SHALL cubrir el listado admin de propietarios (`Admin\Owners`).
2. THE System SHALL cubrir el listado admin de anuncios (`AdminNoticeManager`).
3. THE System SHALL cubrir el listado admin de mensajes (`AdminMessageInbox`).
4. EACH flujo SHALL incluir: presupuesto de consultas máximo y límite de repetición por sentencia.

---

### Requisito 7: Mantenibilidad y velocidad

**User Story:** Como equipo de desarrollo, quiero que el guard de consultas sea rápido y fácil de mantener.

#### Criterios de aceptación

1. THE System SHALL mantener los helpers de Query Guard en un único archivo reutilizable `tests/Feature/Performance/QueryGuardHelpers.php`.
2. THE System SHALL permitir ampliar cobertura gradualmente añadiendo nuevos tests en `tests/Feature/Performance/` sin cambiar el helper.
3. THE System SHALL mantener el tiempo de `quality:queries` por debajo de 30 segundos en condiciones normales.
