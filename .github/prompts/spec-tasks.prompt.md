---
description: "Genera tasks.md con el plan de implementación para un spec existente (requiere requirements.md y design.md)"
name: "Spec: Tareas"
argument-hint: "Nombre de la feature (debe tener requirements.md y design.md ya creados)"
agent: "agent"
---

Crea el archivo `tasks.md` para la feature `$ARGUMENTS` en `.github/specs/$ARGUMENTS/tasks.md`.

Lee primero:

- `.github/specs/$ARGUMENTS/requirements.md`
- `.github/specs/$ARGUMENTS/design.md`
- El plan de implementación existente en [tasks.md](./../specs/community-web/tasks.md) como referencia de estructura y nivel de detalle.

Sigue **exactamente** la misma estructura:

```markdown
# Plan de Implementación: $ARGUMENTS

## Visión general

<descripción del enfoque incremental>

## Tareas

- [ ] 1. <Nombre de la primera tarea>
  - <paso concreto de implementación>
  - <paso concreto de implementación>
  - _Requisitos: N.M, N.M, ..._

  - [ ]\* 1.1 <Subtarea de test>
    - **<Descripción del test>**
    - **Valida: Requisitos N.M**
```

Reglas para las tareas:

- Ordenadas de menor a mayor dependencia: migraciones → modelos → lógica → componentes → vistas → tests
- Cada tarea principal termina con `_Requisitos: N.M, ..._` referenciando los criterios de aceptación
- Subtareas marcadas con `*` son siempre de tests (Unit, Feature, o Browser)
- Los pasos son acciones concretas y ejecutables, no descripciones vagas
- Incluir los comandos Artisan necesarios (p.ej. `php artisan make:model X --migration --factory`)
- Tests Unit para lógica pura; Feature para rutas, Livewire, y DB; Browser (Dusk) para flujos E2E críticos

Convenciones del proyecto:

- SoftDeletes obligatorio en todos los modelos nuevos
- Factories y seeders para cada modelo nuevo
- Pint para formatear código: `vendor/bin/pint --dirty`
- Ejecutar dentro del contenedor: `docker compose exec madaia33 <comando>`

Usa #codebase para verificar lo que ya existe antes de incluir pasos de creación.
