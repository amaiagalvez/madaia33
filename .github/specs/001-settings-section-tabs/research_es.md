# Investigación: Secciones de Settings y UI de Administración con Pestañas

## Decisión 1: Usar allow-list restringida de section

- Decisión: Usar un conjunto explícito de identificadores válidos de section (`front`, `contact_form`, `gallery`, `general`) aplicado en lógica de modelo/servicio y defaults de migración.
- Razonamiento: Evita valores de section inválidos o inconsistentes y hace determinista la generación de pestañas.
- Alternativas consideradas:
  - Strings libres para section en DB: descartado por riesgo de deriva y errores tipográficos.
  - Tabla separada `settings_sections`: descartada por el alcance actual; añade complejidad sin valor inmediato.

## Decisión 2: Migrar settings existentes en lote determinista

- Decisión: Actualizar todos los settings existentes en una única estrategia de migración que mapea keys conocidas a `contact_form`; keys desconocidas/ambiguas usan fallback a `general` y se registran.
- Razonamiento: Garantiza FR-003 y FR-004 en un solo despliegue y evita sections nulas.
- Alternativas consideradas:
  - Asignación manual post-despliegue: descartada por riesgo operativo.
  - Fallar migración con keys desconocidas: descartada para evitar bloqueo de despliegue.

## Decisión 3: Orden alfabético de pestañas y sin persistencia

- Decisión: Renderizar pestañas en orden alfabético y abrir siempre la primera pestaña en cada carga de página.
- Razonamiento: Alinea las decisiones de producto aclaradas y mantiene un comportamiento predecible.
- Alternativas consideradas:
  - Persistir la última pestaña activa en storage: descartado por aclaración.
  - Orden por prioridad custom: descartado por ahora para evitar configuración adicional.

## Decisión 4: Guardados acotados a sección activa

- Decisión: El guardado debe actualizar solo keys que pertenecen a la sección de la pestaña activa.
- Razonamiento: Reduce mutaciones cruzadas accidentales entre secciones y alinea con historias de usuario.
- Alternativas consideradas:
  - Guardar todas las secciones a la vez: descartado por mayor riesgo de escrituras no deseadas.

## Decisión 5: Etiquetas de sección localizadas son obligatorias

- Decisión: Añadir keys de traducción por cada etiqueta de sección en euskera y español.
- Razonamiento: FR-008 exige consistencia en ambos locales soportados.
- Alternativas consideradas:
  - Usar identificadores crudos como etiquetas: descartado por UX pobre e incumplimiento de localización.
