# Guia rapida: Spec-Driven Development con Spec Kit en VS Code

## 1) Que te he configurado

En este repositorio ya esta inicializado Spec Kit para GitHub Copilot:

- Carpeta `.specify/` con scripts y plantillas.
- Comandos de Copilot en `.github/prompts/speckit.*.prompt.md`.
- Agentes de Spec Kit en `.github/agents/speckit.*.agent.md`.

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
