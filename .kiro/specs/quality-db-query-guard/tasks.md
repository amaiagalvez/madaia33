# Plan de Implementación: Quality DB Query Guard

## Visión general

Implementación incremental del sistema de guardia SQL. Se construye de abajo hacia arriba: helper reutilizable → tests de flujos críticos → integración en quality.

## Tareas

- [x] 1. Crear spec (requirements.md, design.md, tasks.md)
    - Completado.

- [x] 2. Helper reutilizable `QueryGuardHelpers`
    - [x] 2.1 Crear `tests/Feature/Performance/QueryGuardHelpers.php`
        - Métodos: `capture()`, `normalize()`, `groupByStatement()`, `assertMaxQueries()`, `assertMaxDuplicates()`
        - _Requisitos: 1.1, 2.1, 2.2, 2.3, 3.1, 3.2, 3.3, 7.1_

- [x] 3. Tests de flujos críticos (fase 1)
    - [x] 3.1 `tests/Feature/Performance/AdminOwnersQueryGuardTest.php`
        - Flujo: render de `Admin\Owners` con propietarios + asignaciones
        - Presupuesto de consultas + límite de duplicadas
        - _Requisitos: 2.1–2.4, 3.1–3.3, 6.1_

    - [x] 3.2 `tests/Feature/Performance/AdminNoticeManagerQueryGuardTest.php`
        - Flujo: render de `AdminNoticeManager` con anuncios + ubicaciones
        - Presupuesto de consultas + límite de duplicadas
        - _Requisitos: 2.1–2.4, 3.1–3.3, 6.2_

    - [x] 3.3 `tests/Feature/Performance/AdminMessageInboxQueryGuardTest.php`
        - Flujo: render de `AdminMessageInbox` con mensajes leídos/no leídos
        - Presupuesto de consultas + límite de duplicadas
        - _Requisitos: 2.1–2.4, 3.1–3.3, 6.3_

- [x] 4. Integrar en scripts de Composer
    - [x] 4.1 Añadir script `quality:queries` en `composer.json`
    - [x] 4.2 Incluir `quality:queries` al final de `quality`
    - _Requisitos: 4.1–4.4_

- [x] 5. Validación en Docker
    - [x] 5.1 Ejecutar suite Performance en contenedor con usuario no root — 6 passed, 1.48s
    - [x] 5.2 Ajustar umbrales al baseline real:
        - `AdminMessageInbox` duplicates: excluidas queries de `role_user` (framework overhead), límite 2x
        - `AdminNoticeManager` duplicates: excluidas queries de `role_user`, límite 3x
        - `AdminOwners` duplicates: excluidas queries de `role_user`, límite 5x (4 tipos de location esperados)
    - [ ] 5.3 Ejecutar `quality` completo en contenedor y confirmar código de salida
    - _Requisitos: 5.1, 5.2, 7.3_

- [ ] 6. Ajuste fino y ampliación
    - [ ] 6.1 Revisar umbrales tras primer baseline estable
    - [ ] 6.2 Añadir flujos adicionales según riesgo detectado en debugbar
    - [ ] 6.3 Documentar criterio para añadir nuevos flujos (comentario en cada test)

## Notas

- Los umbrales iniciales son estimaciones razonables; deben ajustarse con el baseline real (tarea 5.2).
- Para ampliar cobertura basta con añadir un nuevo archivo `*QueryGuardTest.php` en `tests/Feature/Performance/`.
- Cada test debe incluir un comentario `// Budget: X queries — motivo` para trazabilidad.
- Ejecución Docker de referencia:
    ```bash
    docker compose run --rm --user "${DC_UID:-1000}:${DC_GID:-1000}" madaia33 composer quality:queries
    ```
