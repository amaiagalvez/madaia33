# Guia rapida: Spec-Driven Development con Spec Kit en VS Code

## 1) Que te he configurado

En este repositorio ya esta inicializado Spec Kit para GitHub Copilot:

- Carpeta `.specify/` con scripts y plantillas.
- Comandos de Copilot en `.github/prompts/speckit.*.prompt.md`.
- Agentes de Spec Kit en `.github/agents/speckit.*.agent.md`.

Tambien tienes una constitucion base ya rellenada en `.specify/memory/constitution.md`
con lo que se conoce hoy de tu repo.

Comandos principales disponibles:

- `/speckit.constitution` para fijar principios del proyecto.
- `/speckit.specify` para definir la feature (que y por que).
- `/speckit.clarify`  para cerrar ambiguedades.
- `/speckit.plan`  para bajar a plan tecnico.
- `/speckit.tasks` para generar tareas ejecutables.
- `/speckit.analyze` para validar coherencia.
- `/speckit.implement` para ejecutar por fases.


## 2) Flujo recomendado (corto)

1. Define reglas del proyecto:
   - `/speckit.constitution Define principios de calidad, testing, rendimiento y seguridad para este proyecto Laravel`

2. Crea la especificacion funcional (que/por que, no como):
   - `/speckit.specify Quiero implementar ...`

3. Aclara ambiguedades antes del plan:
   - `/speckit.clarify`

4. Genera el plan tecnico:
   - `/speckit.plan Usar Laravel 13, Livewire 4, Tailwind v4, MariaDB y Pest`

5. Genera tareas ejecutables:
   - `/speckit.tasks`

6. Revisa consistencia entre spec/plan/tareas:
   - `/speckit.analyze`

7. Implementa por fases:
   - `/speckit.implement`

## 3) Estructura que vas a ver

Para cada feature, Spec Kit creara artefactos bajo `specs/<numero-feature>/`:

- `spec.md`
- `plan.md`
- `research.md` (si aplica)
- `data-model.md` (si aplica)
- `contracts/` (si aplica)
- `quickstart.md`
- `tasks.md`

## 4) Buenas practicas para que funcione bien

- En `/speckit.specify`, describe problema, usuarios y criterios de aceptacion.
- Evita detalles de implementacion en la spec inicial.
- Mantene historias pequenas e independientes para entregar valor incremental.
- Antes de implementar, valida consistencia con `/speckit.analyze`.
- Si cambias requisitos, vuelve a `specify/clarify/plan/tasks` antes de seguir con codigo.

## 5) Troubleshooting rapido

- Si no te aparecen comandos `/speckit.*`, recarga ventana de VS Code (`Developer: Reload Window`).
- Si una fase falla por prerequisitos, revisa que existan los artefactos previos (`spec.md`, `plan.md`, `tasks.md`).
- Si trabajas fuera de rama de feature, Spec Kit puede pedir contexto de feature; crea o cambia a una rama de feature.

## 6) Que se sabe ya de tu proyecto

Datos inferidos automaticamente desde el repo (AGENTS.md, composer.json, estructura y reglas):

- Stack: Laravel 13, PHP 8.4, Livewire 4, Flux UI v2, Tailwind v4.
- Testing: Pest (Unit/Feature) y Dusk para Browser.
- Flujo de ejecucion: Docker-first.
- Reglas de datos: SoftDeletes por defecto + evitar N+1 + minimizar consultas.
- Reglas de calidad: quality gate y tests antes de cerrar tareas.
- i18n: Euskera y Espanol.

Esto ya esta pasado a la constitucion y a las plantillas de plan/spec/tasks.

## 7) Como rellenar la constitucion con lo que ya tienes

Usa este enfoque para no inventar:

1. Fuente de verdad tecnica:
   - AGENTS.md
   - composer.json y package.json
   - tests/ y herramientas de calidad configuradas

2. Pasa esas reglas a principios declarativos (MUST/SHOULD):
   - Ejemplo: "Docker-first" -> "All project commands MUST run in Docker".

3. Mueve requisitos operativos a "Workflow and Delivery Rules":
   - quality gate
   - orden de validacion
   - cobertura al final

4. Si cambias un principio:
   - actualiza `.specify/memory/constitution.md`
   - revisa plantillas `.specify/templates/*.md`
   - deja trazabilidad en el comentario "Sync Impact Report"
