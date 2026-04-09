# Persistent Memories

## correction-workflow
- Cuando un test de Livewire cubre filtros por valor válido, añade también un caso de valor inválido que verifique el reset de estado (por ejemplo `activeTag=''`) para cubrir ramas de guard clause y evitar bajadas de cobertura.
- Cuando se fusionan migrations derivadas en una migration base de un proyecto no productivo, verifica con el directorio real que los archivos redundantes se han borrado físicamente; si siguen presentes, los tests con SQLite en memoria seguirán ejecutándolos y fallarán con errores de columna/índice duplicados.
- En este proyecto, antes de editar un componente Livewire, verifica cuál es la implementación realmente montada: el nombre puede resolver a un SFC Volt en `resources/views/components/⚡*.blade.php` aunque exista una vista/clase paralela en `resources/views/livewire/`; editar el archivo equivocado hace perder tiempo y deja la UI sin cambios.
- Si `APP_LOCALE=eu` y faltan `lang/eu/validation.php` u otras traducciones base de Laravel, las validaciones caerán en inglés por `fallback_locale`; cuando aparezcan mensajes de error en inglés en formularios del panel, comprueba primero la existencia del archivo de idioma antes de tocar reglas o componentes.

## preferences
- Usuario prefiere que cualquier escritura en /memories/ se refleje tambien en /home/amaia/Webguneak/Packages/.github/memories.md para trazabilidad en repo.
- En este workspace, mantener sincronizado memoria persistente y espejo en .github/memories.md.
