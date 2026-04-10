# Reusable Correction Playbook

## Objetivo

Evitar regresiones repetidas tras correcciones y convertir hallazgos técnicos en reglas generales reutilizables para próximas implementaciones.

## Principios generales

- Implementar reglas de negocio en dos capas: aplicación y base de datos.
- Preferir seguridad por defecto: si algo es ambiguo, fallar de forma explícita.
- Validar coherencia temporal y de estado antes de persistir cambios.
- Evitar dependencias ocultas en tests (seeders concretos, datos fijos del entorno).
- Mantener consistencia lingüística e internacionalización en toda UI nueva.

## Checklist técnico reutilizable

### 1) Autenticación y acceso

- Si existe un estado de activación/desactivación de usuario, debe aplicarse en el flujo de autenticación, no solo en el modelo.
- Añadir test de acceso permitido y denegado según estado.

### 2) Escrituras críticas y concurrencia

- Encapsular escrituras relacionadas en transacciones.
- Para exclusividad (por ejemplo, un único registro activo), combinar:
  - validación de aplicación con bloqueo transaccional, y
  - restricción en base de datos.

### 3) Auditoría y trazabilidad

- Diseñar auditoría resistente a contextos sin usuario autenticado.
- Evitar que la auditoría rompa operaciones legítimas por su propia rigidez.

### 4) Validación de dominio

- Validar orden lógico de fechas y estados en operaciones de cierre/cambio.
- No inferir silenciosamente tipos desconocidos: tratar entradas no reconocidas de forma explícita (error controlado o validación previa).

### 5) Diseño de componentes

- Inyectar dependencias de acciones/servicios en componentes para mejorar testabilidad y mantener SRP.
- Evitar instanciación manual repetida dentro de handlers de UI.

### 6) Internacionalización

- Todo texto nuevo visible en UI debe ir por claves de traducción.
- Añadir claves en todos los idiomas soportados en el proyecto.

### 7) Testing robusto

- Evitar tests acoplados a datos semilla concretos cuando se pueda usar factory.
- Añadir tests para cubrir:
  - caminos felices,
  - restricciones de seguridad,
  - validaciones de borde,
  - escenarios de regresión detectados.

### 8) Calidad y verificación

- Revisar errores de IDE en los archivos tocados antes de cerrar.
- Ejecutar formateo y tests mínimos afectados; ampliar alcance si hay impacto transversal.

## Plantilla de cierre de corrección

- Qué se corrigió (resumen corto).
- Qué riesgo se eliminó.
- Qué regla general se añade para no repetir el problema.
- Qué pruebas verifican la corrección.

## Uso recomendado

Aplicar este playbook al inicio y al final de cualquier corrección relevante, y actualizarlo cuando aparezca un nuevo patrón de fallo repetible.
