---
description: "Genera requirements.md para un nuevo spec de feature siguiendo las convenciones del proyecto"
name: "Spec: Requisitos"
argument-hint: "Nombre y descripción breve de la feature"
agent: "agent"
---

Crea el archivo `requirements.md` para una nueva feature en `.github/specs/$ARGUMENTS/requirements.md`.

Sigue **exactamente** la misma estructura del spec existente en [requirements.md](./../specs/community-web/requirements.md):

- Empezar con `# Documento de Requisitos`
- Sección `## Introducción` describiendo qué hace la feature y su contexto dentro de la aplicación
- Sección `## Glosario` con los términos clave específicos de esta feature (términos del proyecto ya definidos no hace falta repetirlos)
- Sección `## Requisitos` con subsecciones numeradas `### Requisito N: <Nombre>`

Cada requisito debe tener:

- **User Story**: `Como <rol>, quiero <acción>, para <beneficio>.`
- **Criterios de Aceptación** en formato SHALL/WHEN/IF-THEN:
  - `THE <Sistema> SHALL <comportamiento>.`
  - `WHEN <condición>, THE <Sistema> SHALL <comportamiento>.`
  - `IF <condición>, THEN THE <Sistema> SHALL <comportamiento>.`

Contexto del proyecto:

- Laravel 13 + Livewire 4 + Flux UI + TailwindCSS v4
- Bilingüe: Euskera (principal) y Castellano (secundario)
- Panel de administración protegido por autenticación
- Parte pública sin autenticación
- Todos los modelos usan SoftDeletes

La feature a documentar es: **$ARGUMENTS**

Antes de crear el archivo, usa #codebase para explorar el código existente relevante para esta feature.
Crea la carpeta `.github/specs/$ARGUMENTS/` si no existe.
