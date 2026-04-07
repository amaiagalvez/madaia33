# Contrato: Secciones de Settings de Administración

## Alcance

Define el contrato de comportamiento de la interfaz de settings de administración tras introducir agrupación por `section`.

## Entradas

- Filas persistidas de `settings` con atributos requeridos:
  - `key`
  - `value`
  - `section`

## Reglas de Sección

1. Los identificadores válidos de section están restringidos a valores aprobados.
2. Cada fila de settings debe tener un `section` válido no nulo.
3. Cualquier fila migrada ambigua debe asignarse a `general` y registrarse.

## Contrato de Renderizado

1. La página de settings debe renderizar una pestaña por sección que tenga al menos un setting no eliminado.
2. Las pestañas se ordenan alfabéticamente por identificador de sección.
3. En carga de página, pestaña activa = primera pestaña en orden alfabético.
4. Solo settings de la sección activa son visibles/editables en la vista activa.

## Contrato de Guardado

1. Enviar settings en una pestaña activa actualiza solo keys que pertenecen a la section de esa pestaña.
2. La operación de guardado devuelve feedback visible de éxito cuando finaliza correctamente.
3. Guardar la sección A no debe mutar valores de la sección B.

## Contrato de Localización

1. Las etiquetas de sección deben ser traducibles en euskera y español.
2. Las keys de traducción faltantes se consideran violaciones de contrato para pestañas visibles al usuario.

## Trazabilidad de Tests

- FR-001..FR-004: Persistencia de section y validez de migración
- FR-005..FR-007: Renderizado de pestañas y guardado acotado por sección
- FR-008: Etiquetas localizadas
- FR-009..FR-010: Alineación de modelo/factory/seeding
