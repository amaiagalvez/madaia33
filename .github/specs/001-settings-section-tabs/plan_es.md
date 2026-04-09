# Plan de Implementación: Campo Section en Settings y Panel con Pestañas

**Branch**: `001-settings-section-tabs` | **Fecha**: 2026-04-07 | **Spec**: [.github/specs/001-settings-section-tabs/spec.md](.github/specs/001-settings-section-tabs/spec.md)
**Input**: Especificación de feature desde `/.github/specs/001-settings-section-tabs/spec.md`

## Resumen

Añadir un atributo obligatorio `section` a settings, migrar los registros existentes a secciones válidas y reestructurar la experiencia de settings de administración en pestañas por sección (orden alfabético, sin persistencia de pestaña entre cargas de página). La implementación mantiene convenciones Laravel, usa lecturas/escrituras en lote y conserva localización y validación.

## Contexto Técnico

**Lenguaje/Versión**: PHP 8.4, Blade, Alpine.js (mínimo), Livewire 4  
**Dependencias Primarias**: Laravel 13, Livewire 4, Tailwind CSS v4, Fortify  
**Almacenamiento**: MySQL (tabla `settings`) con SoftDeletes  
**Testing**: Pest 4 (Feature + Unit; Browser opcional si el comportamiento UI se vuelve frágil)  
**Plataforma Objetivo**: Contenedores Linux mediante Docker Compose  
**Tipo de Proyecto**: Aplicación web Laravel (interfaz admin + persistencia backend)  
**Objetivos de Rendimiento**: Mantener interacción fluida en la página de settings y accesos DB en lote (una lectura `whereIn` + escrituras agrupadas por guardado)  
**Restricciones**: Ejecución solo en Docker, sin comandos PHP/Composer/NPM en host, preservar keys de settings existentes y traducciones  
**Escala/Alcance**: Alcance actual de settings admin, secciones iniciales (`front`, `contact_form`, `gallery`, más fallback de migración `general`)

## Verificación de Constitución

*GATE: Debe pasar antes de investigación de Phase 0. Revalidar tras diseño de Phase 1.*

- [x] Ejecución Docker-first definida para build, calidad y test
- [x] Convenciones Laravel priorizadas frente a abstracciones custom
- [x] Estrategia de test definida (Unit/Feature/Browser cuando aplique)
- [x] Quality gate incluido: `composer quality` en Docker
- [x] Modelo de datos respeta SoftDeletes y eficiencia de consultas
- [x] Trazabilidad spec-plan explícita para decisiones principales

## Estructura del Proyecto

### Documentación (esta feature)

```text
.github/specs/001-settings-section-tabs/
├── plan.md
├── research.md
├── data-model.md
├── quickstart.md
├── contracts/
│   └── admin-settings-sections.md
└── tasks.md
```

### Código Fuente (raíz del repositorio)

```text
app/
├── Livewire/
│   └── AdminSettings.php
├── Models/
│   └── Setting.php
├── Validations/
│   └── AdminSettingsValidation.php

database/
├── factories/
│   └── SettingFactory.php
├── migrations/
│   └── *_settings*.php
└── seeders/

resources/views/
└── livewire/
   └── admin-settings.blade.php

lang/
├── eu/
└── es/

tests/
├── Feature/
└── Unit/
```

**Decisión de Estructura**: Usar la estructura monolítica Laravel existente. No se requieren carpetas top-level nuevas.

## Phase 0: Resumen de Investigación

Ver [.github/specs/001-settings-section-tabs/research.md](.github/specs/001-settings-section-tabs/research.md).

- Los valores de section restringidos deben representarse como allow-list explícita (o mapa central equivalente) para evitar agrupaciones inválidas.
- Los settings existentes se migran en un único paso determinista; las keys no resueltas van a `general` y se registran.
- Las pestañas UI son alfabéticas y se reinician a la primera pestaña en la carga por decisión explícita de producto.
- Las keys de localización para etiquetas de sección son obligatorias en euskera y español.

## Phase 1: Entregables de Diseño

- Modelo de datos: [.github/specs/001-settings-section-tabs/data-model.md](.github/specs/001-settings-section-tabs/data-model.md)
- Contrato: [.github/specs/001-settings-section-tabs/contracts/admin-settings-sections.md](.github/specs/001-settings-section-tabs/contracts/admin-settings-sections.md)
- Quickstart: [.github/specs/001-settings-section-tabs/quickstart.md](.github/specs/001-settings-section-tabs/quickstart.md)

## Estrategia de Testing

1. Tests Unit:
   - Comportamiento de allow-list de secciones y utilidades de mapeo (si se introducen).
   - Lógica de mapeo de migración (incluyendo fallback a `general`).
2. Tests Feature:
   - Pantalla de settings admin renderiza pestañas por sección.
   - Pestaña activa muestra solo settings de su sección.
   - Guardado actualiza solo keys de la sección activa.
3. Test Browser opcional:
   - Comportamiento de cambio de pestañas si la interacción server/client no queda cubierta de forma fiable por Feature tests.

## Comandos de Quality Gate (Docker, non-root)

- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --filter=AdminSettings`

## Verificación de Constitución Post-Diseño

- [x] Docker-first sigue aplicado en quickstart e instrucciones de test
- [x] Enfoque Laravel-first mantenido (componente Livewire + actualizaciones Eloquent)
- [x] Enfoque de testing explícito y acotado al comportamiento impactado
- [x] Comando de quality gate incluido y compatible con ejecución non-root
- [x] Estrategia de datos preserva SoftDeletes y usa acceso DB en lote
- [x] Trazabilidad spec-plan preservada (FR-001..FR-010 mapeados en contrato y modelo)

## Seguimiento de Complejidad

No se identifican violaciones de constitución; no se requieren excepciones de complejidad.
