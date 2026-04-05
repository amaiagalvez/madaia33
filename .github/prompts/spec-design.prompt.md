---
description: "Genera design.md para un spec existente, documentando la arquitectura técnica de la feature"
name: "Spec: Diseño Técnico"
argument-hint: "Nombre de la feature (debe tener requirements.md ya creado)"
agent: "agent"
---

Crea el archivo `design.md` para la feature `$ARGUMENTS` en `.github/specs/$ARGUMENTS/design.md`.

Lee primero el documento de requisitos en `.github/specs/$ARGUMENTS/requirements.md` y el diseño existente del proyecto en [design.md](./../specs/community-web/design.md) como referencia de convenciones.

Sigue **exactamente** la misma estructura:

- `# Documento de Diseño Técnico — $ARGUMENTS`
- `## Visión General` — descripción de la arquitectura de la feature y cómo encaja en el proyecto
- `### Decisiones de diseño clave` — lista de decisiones arquitectónicas importantes (bullet points)
- Secciones específicas según la feature: modelos, componentes Livewire, rutas, vistas, servicios, etc.

Para cada componente importante incluye:

- Nombre de clase y ubicación en el proyecto
- Responsabilidades principales
- Relaciones con otros componentes
- Fragmentos de código o estructura de tablas cuando ayuden a la comprensión

Contexto técnico del proyecto:

- **Stack**: Laravel 13, PHP 8.4, Livewire 4, Flux UI v2, TailwindCSS v4, Pest v4
- **Docker**: comandos del proyecto dentro del contenedor `madaia33`
- **DB**: SQLite en tests, MariaDB en desarrollo
- **i18n**: archivos en `lang/eu/` y `lang/es/`, helpers `__()` /`trans()`
- **SoftDeletes**: todos los modelos lo usan
- **Tests**: Unit para lógica pura, Feature para HTTP/DB/Livewire, Browser (Dusk) para flujos E2E críticos

Usa #codebase para explorar el código relacionado antes de escribir el documento.
Crea el archivo solo cuando tengas suficiente contexto del código existente.
