# 002-refactor-app

## Objetivo

Revisar `app/` para detectar duplicación o estructura mejorable, ejecutar refactors de bajo riesgo y validar que todo sigue funcionando con tests y dusk-tests.

## Hallazgos iniciales

- Duplicación de lectura de settings (`where('key'...)`, `whereIn(...)->pluck(...)`) en múltiples clases de `app/`.
- Duplicación de resolución de valores localizados desde settings (misma idea implementada en varios sitios).
- Estructura mejorable en sincronización de ubicaciones de avisos: inserciones fila a fila tras borrado completo.

## Plan

- [x] Auditar `app/` y localizar duplicación/estructura mejorable.
- [x] Extraer helpers de acceso a settings en `App\\Models\\Setting`.
- [x] Reemplazar lecturas duplicadas en controladores/componentes por helpers compartidos.
- [x] Optimizar sincronización de ubicaciones en `AdminNoticeManager` para minimizar consultas.
- [x] Añadir/ajustar tests para cubrir el refactor.
- [x] Ejecutar tests de aplicación.
- [x] Ejecutar dusk-tests.

## Progreso de ejecución

- [x] Auditoría inicial completada y hotspots identificados.
- [x] Refactors aplicados.
- [x] Test suite validada.
- [x] Dusk suite validada.
