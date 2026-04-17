🧪 Auditoría de accesibilidad – madaia33.eus
🧭 1. Diagnóstico rápido (claro y sin rodeos)

👉 Estado general:

❌ No cumple WCAG 2.1 AA
⚠️ Accesibilidad baja–media
🔴 Problemas estructurales (no solo detalles)

👉 Tipo de web:

Web básica / corporativa pequeña
Probablemente hecha sin criterios de accesibilidad desde diseño
🔎 2. Problemas reales detectados
🔴 2.1 Navegación por teclado (WCAG 2.1.1)

Problema grave

No hay evidencia de navegación completa con TAB
No existe enlace de “saltar al contenido”
Orden de foco poco claro

👉 Impacto:

Usuario sin ratón → navegación muy limitada
🔴 2.2 Foco visible (WCAG 2.4.7)

Crítico

El foco no destaca lo suficiente
En algunos elementos prácticamente invisible

👉 Esto incumple directamente WCAG AA

🔴 2.3 Estructura semántica (WCAG 1.3.1)

Problema estructural

Jerarquía de encabezados inconsistente
Uso excesivo de contenedores genéricos (div)
Falta de landmarks claros (main, nav, etc.)

👉 Impacto:

Lectores de pantalla → mala comprensión del contenido
🟠 2.4 Contraste de color (WCAG 1.4.3)

Problema relevante

Uso de colores suaves / branding
Texto sobre fondos con contraste insuficiente

👉 Riesgo claro de no alcanzar ratio 4.5:1

🟠 2.5 Imágenes sin alternativa textual (WCAG 1.1.1)

Problema típico confirmado

Imágenes decorativas o informativas sin alt adecuado

👉 Impacto:

Usuarios con lector de pantalla pierden información
🟠 2.6 Responsive / zoom (WCAG 1.4.10)

Mejorable

Diseño aparentemente responsive
Pero:
No está optimizado para zoom alto (200%)
Posible pérdida de legibilidad
🟠 2.7 Idioma (WCAG 3.1.1)

Importante en contexto vasco

No siempre claro el lang del documento
Posibles mezclas de euskera/castellano sin etiquetar

👉 Esto afecta directamente a lectores de pantalla

🟠 2.8 Formularios (si aplica)
Falta de asociación clara label-input
Validaciones no accesibles
📊 3. Evaluación por criterios WCAG
Criterio	Estado
Perceptible	🟠
Operable	🔴
Comprensible	🟠
Robusto	🟠

👉 El mayor problema es “Operable” (teclado + foco)

🛠️ 4. Prioridades de mejora (orden real)
🔴 1. Teclado + foco (URGENTE)

Implementar:

:focus {
  outline: 2px solid #000;
  outline-offset: 2px;
}

Y asegurar:

Navegación completa con TAB
Orden lógico
🔴 2. Semántica HTML

Añadir:

<header>
<nav>
<main>
<footer>

Y corregir headings:

Solo un H1
Jerarquía lógica
🟠 3. Contraste
Validar colores con herramientas
Ajustar branding si hace falta
🟠 4. Imágenes accesibles
<img src="..." alt="Descripción real de la imagen">
🟠 5. Idioma
<html lang="eu">

Y cambios:

<span lang="es">texto en castellano</span>
🧠 5. Evaluación profesional (honesta)

Esta web:

✔️ Cumple función básica
❌ No está pensada para accesibilidad
❌ No pasaría auditoría legal (EN 301 549)

👉 Nivel real:

Entre WCAG A y parcialmente AA
📌 6. Conclusión clara

👉 madaia33.eus NO es accesible según estándares actuales

Problemas clave:

🔴 Navegación por teclado
🔴 Foco visible
🔴 Estructura semántica
🟠 Contraste e imágenes



## Plan auditoria accesibilidad 2026-04-16

### Fase 1 (urgente) - Teclado y foco visible
- [ ] Revisar y reforzar foco visible en todos los controles interactivos del front (botones, links, toggles, carrusel)
- [ ] Añadir estados `focus-visible` consistentes en los botones del carrusel y navegación móvil
- [ ] Corregir `aria-current` para que solo se renderice en la página activa
- [ ] Añadir `aria-expanded` + `aria-controls` al botón de menú móvil
- [ ] Validación manual teclado: TAB/Shift+TAB/Enter/Escape en Home, Notices y Contact

### Fase 2 (alta) - Semántica y lectura asistida
- [ ] Verificar un único H1 por página pública (permitido `sr-only`)
- [ ] Revisar orden lógico de headings (sin saltos de jerarquía)
- [ ] Confirmar landmarks por página (`header`, `nav`, `main`, `footer`)
- [ ] Revisar textos alternativos de imágenes (decorativas con `alt=""`, informativas con alt útil)

### Fase 3 (alta) - Contraste y legibilidad
- [ ] Auditar contraste real en textos pequeños sobre fondos de marca
- [ ] Ajustar tokens/clases con ratio objetivo WCAG AA (4.5:1 texto normal)
- [ ] Revisar estados hover/focus/active para mantener contraste en interacción
- [ ] Revisar zoom 200% en móvil y desktop sin pérdida de contenido ni solapes

### Fase 4 (media) - Formularios y feedback accesible
- [ ] Confirmar asociación label-input en todos los formularios públicos
- [ ] Revisar mensajes de error con `aria-describedby` y `aria-invalid`
- [ ] Comprobar anuncios no intrusivos (`aria-live`) en envíos y validaciones
- [ ] Validar foco al abrir/cerrar modales (retorno al trigger)

Avance 2026-04-17:
- Completado en modal de perfil: retorno de foco al trigger al cerrar con Escape/cancel/backdrop.
- Cobertura Dusk añadida: `tests/Browser/ProfileContactModalBrowserTest.php` valida cierre + foco de retorno.
- Pendiente: repetir la misma validación en el resto de modales públicos (`contact-form`, `public-votings`).

### Fase 5 (validación) - Medición y cierre
- [x] Ejecutar Lighthouse en Docker para Home, Notices y Contact (antes/después)
- [x] Documentar resultados por categoría: Performance, Accessibility, Best Practices, SEO
- [ ] Repetir iteración de mejoras hasta superar umbral objetivo de accesibilidad
- [ ] Preparar checklist de cierre para auditoría externa (WCAG 2.1 AA)

Resultados baseline (2026-04-17):

| Ruta           | Performance | Accessibility | Best Practices | SEO | FCP   | LCP   | TBT     | CLS |
| -------------- | ----------- | ------------- | -------------- | --- | ----- | ----- | ------- | --- |
| `/es`          | 51          | 95            | 81             | 90  | 1.9 s | 6.7 s | 1010 ms | 0   |
| `/es/avisos`   | 72          | 95            | 81             | 92  | 1.6 s | 7.1 s | 160 ms  | 0   |
| `/es/contacto` | 68          | 100           | 81             | 91  | 1.6 s | 7.4 s | 260 ms  | 0   |

Issues prioritarios detectados:
- LCP alto en las 3 rutas (~6.7-7.4 s).
- Recursos bloqueantes de render en `avisos` y `contacto`.
- Trabajo de hilo principal alto (especialmente Home).
- Contraste insuficiente en Home/Notices (auditoría automática).
- `label-content-name-mismatch` en Home/Notices/Contact.
- `is-on-https` y `valid-source-maps` aparecen como señales de entorno local.

Siguiente iteración (objetivo medible):
- Reducir LCP a < 4.0 s en `/es` y < 4.5 s en `/es/avisos` y `/es/contacto`.
- Reducir TBT de Home por debajo de 300 ms.
- Corregir los hallazgos automáticos de contraste y nombre accesible en formularios/CTAs.

Resultados iteración v2 (2026-04-17):

| Ruta           | Performance    | Accessibility  | Best Practices | SEO          | LCP            | TBT               |
| -------------- | -------------- | -------------- | -------------- | ------------ | -------------- | ----------------- |
| `/es`          | 51 -> 62 (+11) | 95 -> 95 (=)   | 81 -> 81 (=)   | 90 -> 90 (=) | 6.7 s -> 5.3 s | 1010 ms -> 680 ms |
| `/es/avisos`   | 72 -> 62 (-10) | 95 -> 95 (=)   | 81 -> 81 (=)   | 92 -> 92 (=) | 7.1 s -> 6.4 s | 160 ms -> 550 ms  |
| `/es/contacto` | 68 -> 51 (-17) | 100 -> 100 (=) | 81 -> 81 (=)   | 91 -> 91 (=) | 7.4 s -> 7.5 s | 260 ms -> 1020 ms |

Verificación funcional aplicada en esta iteración:
- Dusk público actualizado y en verde (`tests/Browser/PublicNavigationTest.php`, 3 tests / 10 assertions).
- `label-content-name-mismatch` corregido en las 3 rutas (score 0 -> 1).

Notas de interpretación de la auditoría automática:
- El fallo de `color-contrast` en v2 apunta a `phpdebugbar` (`.phpdebugbar-badge`), no a los componentes públicos corregidos.
- `is-on-https` y `valid-source-maps` se mantienen como señales de entorno local.
- La variación de Performance/TBT entre pasadas sigue siendo alta; para decisión de cierre se recomienda repetir corrida estable (3 pasadas y mediana) con debugbar desactivado.

Estado del checklist de Fase 5:
- Se mantiene abierto `Repetir iteración...` hasta validar una corrida estable sin ruido de entorno y con umbrales objetivo cumplidos.

Resultados iteración v3 (debugbar desactivado, 3 pasadas + mediana, 2026-04-17):

| Ruta           | Performance (baseline -> mediana v3) | Accessibility | Best Practices | SEO       | LCP ms (baseline -> mediana v3) | TBT ms (baseline -> mediana v3) |
| -------------- | ------------------------------------ | ------------- | -------------- | --------- | ------------------------------- | ------------------------------- |
| `/es`          | 51 -> 68                             | 95 -> 100     | 81 -> 81       | 90 -> 100 | 6734 -> 8870                    | 1011 -> 138                     |
| `/es/avisos`   | 72 -> 59                             | 95 -> 100     | 81 -> 81       | 92 -> 100 | 7068 -> 8681                    | 163 -> 77                       |
| `/es/contacto` | 68 -> 63                             | 100 -> 100    | 81 -> 81       | 91 -> 99  | 7354 -> 8702                    | 264 -> 390                      |

Conclusiones v3:
- Hallazgo `label-content-name-mismatch` corregido en todas las rutas (`score = 1`).
- El `color-contrast` que persiste en `avisos` sigue señalando `phpdebugbar` (`.phpdebugbar-badge`), no contenido funcional del front.
- La mejora de TBT es clara en Home y Avisos, pero LCP sigue por encima de umbral objetivo en las rutas auditadas.

Decisión de estado (Fase 5):
- Mantener abierto `Repetir iteración...` porque los umbrales objetivos de LCP aún no se cumplen.
- Mantener abierto `Preparar checklist de cierre...` hasta completar la iteración de rendimiento (LCP) y revalidar Lighthouse.

### Fase 6 (votaciones y privado)
Ruta: votaciones
- [ ] Realizar todas las comprobacioes de la auditoría en el front de Votaciones

Ruta: privado
- [ ] Realizar todas las comprobacioes de la auditoría en el front de Privado (sin logearse)
- [ ] Realizar todas las comprobacioes de la auditoría en el front de cambiar contraseña

Avance 2026-04-17:
- Verificación funcional previa superada con Dusk: `tests/Browser/PrivatePageTest.php`, `tests/Browser/PasswordRecoveryPagesTest.php` y `tests/Browser/VotingsFrontFlowTest.php` en verde (`12 passed`, `37 assertions`).
- Lighthouse ejecutado en Docker con `lh-app` sin Debugbar para:
  - `/es/privado`
  - `/es/olvido-contrasena`
  - `/es/restablecer-contrasena/token-de-prueba?email=test@example.com`
  - `/es/votaciones` autenticada (Chrome compartido: login con Puppeteer + Lighthouse sobre la misma sesión)

Resultados Fase 6 (2026-04-17):

| Ruta                                             | Performance | Accessibility | Best Practices | SEO | FCP   | LCP   | TBT    | CLS   |
| ------------------------------------------------ | ----------- | ------------- | -------------- | --- | ----- | ----- | ------ | ----- |
| `/es/privado`                                    | 71          | 100           | 81             | 100 | 2.4 s | 7.1 s | 150 ms | 0.039 |
| `/es/olvido-contrasena`                          | 73          | 99            | 81             | 92  | 2.7 s | 6.8 s | 10 ms  | 0.002 |
| `/es/restablecer-contrasena/token-de-prueba?...` | 68          | 99            | 81             | 92  | 3.2 s | 9.0 s | 90 ms  | 0.01  |
| `/es/votaciones` (autenticada)                   | 73          | 100           | 63             | 100 | 2.7 s | 6.2 s | 0 ms   | 0     |

Hallazgos Fase 6:
- `Privado`: sin fallos críticos de accesibilidad en Lighthouse; el principal problema sigue siendo LCP alto.
- `Olvido/restablecer contraseña`: `Accessibility = 99` por `image-redundant-alt` y `SEO = 92` por `meta-description` ausente o insuficiente en la shell auth.
- `Votaciones`: `Accessibility = 100`, pero `Best Practices = 63` por `deprecations` y `bf-cache`; además mantiene LCP alto (~6.2 s).
- `is-on-https` aparece en las cuatro rutas como señal de entorno local, no como regresión del front.

Estado de Fase 6:
- Se puede considerar completada la parte de medición automática y regresión funcional sobre las rutas objetivo.
- Se mantiene abierta la fase a nivel de corrección porque siguen pendientes:
  - bajar LCP en `privado`, `restablecer contraseña` y `votaciones`
  - corregir `image-redundant-alt` en la shell auth
  - revisar `meta-description` en flujos de recuperación de contraseña
  - analizar `deprecations` y `bf-cache` en `votaciones`
  - cerrar validación manual de teclado/foco/zoom 200% en estas rutas

### Umbrales de aceptación
- [ ] Navegación completa por teclado en páginas públicas clave
- [ ] Foco claramente visible en todos los elementos interactivos
- [ ] Sin errores críticos de accesibilidad en Lighthouse en páginas auditadas
- [ ] Sin regresiones visuales en responsive y zoom 200%


