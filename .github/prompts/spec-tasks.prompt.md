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
- Si una tarea accede a base de datos, incluir pasos explícitos para:
  - reducir el número de consultas siempre que mejore el rendimiento
  - evitar problemas N+1 siempre que no empeore la velocidad global
  - traer solo los campos necesarios en cada consulta (evitar sobrecarga de datos)
- Al final de cada tarea definida, añadir un paso explícito de verificación que revise:
  - errores/advertencias en la consola del navegador de las páginas afectadas
  - errores/advertencias nuevos en logs de Laravel relacionados con la tarea
- Antes de cerrar el conjunto final de tareas del spec, añadir un quality gate obligatorio **antes** de documentación y **antes** de tests con este comando:
  - `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`
- La última tarea del plan debe ser siempre ejecutar los tests con coverage.

Convenciones del proyecto:

- SoftDeletes obligatorio en todos los modelos nuevos
- Factories y seeders para cada modelo nuevo
- Pint para formatear código: `vendor/bin/pint --dirty`
- Ejecutar dentro del contenedor: `docker compose exec madaia33 <comando>`

Usa #codebase para verificar lo que ya existe antes de incluir pasos de creación.
