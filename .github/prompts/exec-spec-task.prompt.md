---
description: "Ejecuta tareas de un spec: una tarea concreta o todas las pendientes en secuencia automática"
name: "Ejecutar tarea spec"
argument-hint: "nombre-spec [número-tarea] (ej: rediseño-front 1 | rediseño-front)"
agent: "agent"
---

Lee el archivo `.github/specs/$ARGUMENTS_1/tasks.md`.

Modo de ejecución:

- Si se proporciona `$ARGUMENTS_2`, ejecuta **solo** la tarea número `$ARGUMENTS_2`.
- Si **no** se proporciona `$ARGUMENTS_2`, ejecuta automáticamente la **siguiente tarea pendiente** (la primera `- [ ]`) y, al completarla, continúa con la siguiente pendiente hasta terminar todas las tareas posibles del spec.
- Si no quedan tareas pendientes, informa que el spec ya está completo.

Para cada tarea a ejecutar:

- Crea archivos nuevos si es necesario
- Modifica archivos existentes
- Ejecuta comandos en Docker si es necesario
- Escribe tests o valida el código

**VALIDACIÓN ANTES DE FINALIZAR (OBLIGATORIO):**

1. Ejecuta la suite de tests relacionados con lo que modificaste:
   - Comando: `docker compose exec madaia33 php artisan test --compact`
   - Si los tests afectan navegación pública o vistas: ejecuta tanto Feature tests como Browser tests (Dusk)
   - **Para Browser tests (Dusk):** Debes seguir la skill `dusk-testing` para configurar correctamente Chromium y ChromeDriver
   - Si hay fallos: **NO** marques la tarea como completada, reporta los fallos y proporciona pasos para corregir
   - Tests que fallan por infraestructura (Chromium faltante, permisos) son aceptables si la lógica Feature pasó

2. Verifica que no hay errores de compilación:
   - Ejecuta `vendor/bin/pint --dirty` para formatear código PHP
   - Si hay errores de Pint, corrige y reporta

Después de validar tests (todos pasados o solo fallos de infraestructura), para cada tarea:

1. Marca la tarea como completada en el archivo `tasks.md` reemplazando `- [ ]` por `- [x]`
2. Marca cualquier subtarea de test como completada también si fue ejecutada
3. Proporciona un resumen de:
   - Qué se implementó
   - Resultado de tests (✅ pasados o ⚠️ solo infraestructura)
   - Cualquier nota importante

**Contexto del proyecto:**

- Laravel 13, PHP 8.4, TailwindCSS v4, Livewire 4, Flux UI v2
- Ejecutar comandos dentro del contenedor: `docker compose exec madaia33 <comando>`
- Convenciones: SoftDeletes, factories, tests Unit/Feature/Browser
- El proyecto está en `/home/amaia/Dokumentuak/madaia33/`

**Importante:**

- Lee primero el `requirements.md` y `design.md` del spec para tener contexto
- Valida que el código compilar/funcionar antes de marcar como completada
- Si hay errores durante la ejecución, reporta y proporciona pasos para corregir
- No hagas cambios que no esten en la tarea específica
- En modo automático (sin número), al finalizar una tarea debes volver a leer `tasks.md` actualizado antes de pasar a la siguiente
- Si una tarea no puede completarse por un bloqueo real (no infraestructura temporal), detén la secuencia, reporta el bloqueo y no marques esa tarea como completada

Comienza leyendo la(s) tarea(s) y indicando qué vas a hacer según el modo detectado.
