- [ ] components field text
- [ ] components tables
- [ ] datatables
- [ ] seo
- [ ] acceesibilidad
- [ ] traducciones repetidas
- [ ] añadir el MCP para que consulte la documentación de laravel


- [ ] dentro de la carpeta madaia33 buscar todos los ficheros y carpetas que sean del usuario root y ponerles amaia:amaia
- [ ] añadir espacio para comercios
- [ ] en los test todos los $this-> dan error en PROBLEMS
- [ ] pasar pain no en modo test, dejarle a él que lo corrija
- [ ] user hizkuntza, besdin du front-ekoa

# Copilot
- [ ] repasar agente lamia
- [ ] agente para crear manuales de usuario
- [ ] añadir en los agentes amalurra y sorgina una nueva regla para que usen siempre que se pueda test unitarios sin usar la base de datos para que los test sean lo más rápidos posibles
- [ ] 
- donde añado cuando se creen nuevos ficheros el ident sea de 4 espacios?
- dividir la carpeta Actions por features o modelos, lo que creas más conveniente
- para las partes de las views  que sean sensibles, crear dusk-test

- si la carpeta es Admin, no hace falta que se llame AdminLocations
- Ordena Livewire en subcarpetas Admin y Front
- crear un componente para que todas las tablas tengan la missma estructura
  

- Nola konpondu hau? Aldiro eskatzen dit:
dpkg: abisua: 'libpaper1:amd64' paketearen fitxategi-zerrendaren fitxategiak falta dira, paketeak ez duela fitxategirik instalatuta ondorioztatuko da


# Code
- [ ] test en ingles
- [ ] comments en ingles
- [ ] cachear los settings para que reducir el número de consultas a la base de datos, cuando se modifica algun setting, borrar la cache y volver a crearla
- [ ] template de email compatible con los diferntes gestores de correo, que se use en el envio de correos. Incluido el texto legal que está configurado en la configuración del email
- [ ] configurar el sentry
- [ ] añadir debugbar y algo para ver los logs
- [ ] componente para inputs del formulario
- [ ] pasahitza aldatu funtzionalitatea
- [ ] registro sessions en bd
- [ ] listado users y sus sessions

# Home
- [ ] Contact: subtitulua testua txukundu. 
cambia el subtitulo del formulario de contacto del front, algo asi como Envia tu aportación, consulta, duda... te responderemos lo antes posible o algo así, como a ti mejor te parezca
- [ ] Osatu pribatutasun-politika eta lege-oharrak testuak
- [ ] Cookies sartu
- [ ] Slider más pequeño, es importante que si hay anuncios se vean lo más rápido posible

# Panela
- [ ] en le panel separa un poco el header del menú superior
- [ ] Mezuak. Al abrir el mensaje, añade un botón para responderle. Guarda la respuesta en la base de datos y enviale el email. Añade una nueva columna en la taula que indique con iconos si está repondido o no.
- [ ] En el panel MezuakFiltro mensajes con botoncitos, como en el front de Iragarkiak. Leidos y no Leidos. Por defecto, al entrar mostrar solo los leidos
- [ ] Iragarkiak. Gehitu hasiera data eta bukaera data eremuak, gehitu zutabea zerrendan eta front-ean kontrolatu eta bakarrik erakutsi indarrean daudenak (aldatu dezakezu migrazioa, ez dago indarrean)
- [ ] settings al cambiar de tab de section, si no se han guardado los cambios avisar
- [ ] settings imagen para el logo (sustituir donde se usa madaia33.png por este campo)
- [ ] en settings, el botón "enviar prueba" solo se debe activar cuando no haya cambios sin guardar, al modificar algo descativarlo
- [ ] en settings en la seccion email_configuration, ocultar todos los campos menos el nombre (ponerlos como si fueran tipo password) y al 
- [ ] text area, que se adapte al contenido automaticamente para que loos forrmularios sean más pequeños
- [ ] Mezuak: bilatzailea en cualquier campo
- [ ] en settings, en la sección "contac_form" añadir dos nuevos para el asunto del email (eu y es). Al enviar el mensaje cuando se rellena el formulario, tiene que usar lo que hay en estos campos. El idioma que se usará para enviarr el email será el idioma actual
- [ ] el formato de todos los formularios tanto de crear como editar tienen que tener el mismo aspecto que el de crear un nuevo anuncio, crear un componente?
- [ ] en el aginte-panela añade estadísticas de las nuevas tablas


# Egiten ...

## Comprobar

https://chatgpt.com/c/69d78d8d-dd40-832a-a8c7-3144bb109696

Para que todo esto sea legal de verdad, asegúrate de:
✔ Checkbox de privacidad en formularios
✔ No mostrar datos de vecinos públicamente
✔ Control de acceso a actas
✔ Logs de acceso (recomendado para votaciones)

## manual de usuaria
crear una miniweb en html con las instrucciones para usar la aplicación, añade texto y pantallazos para que los usuarios que se logueen tengan claro cómo usar la aplicación
añadir una ruta al menú del panel 
tiene que estar en dos idiomas eu y es
añadir una regla al agente amalur para que lo mantenga actualizado 

## profile
arreglar la ruta profile

## login
la ruta login me tiene llevar a la ruta privado en el idioma en el que este navegando, si no hay idioma por defecto en euskera

en la ruta privado, el botón "Saioa hasi" tiene que hacer lo mismo que lo que hace el botón "Sartu" del login actual

adapta las vistas del /forgot-password al estilo del proyecto

si el user tiene un owner asignado, podrá logearse con el email o con el dni pero solo si is_active = 1, sino no se podrá logear
si el user no tiene un owner asignado solo podrá logearse con el email siempre que tenga is_active = 1


## limpieza
quita el blade login actual, no lo uso
quita el dashboard de laravel /dashboard, no lo uso
/settings/profile muevelo al menu superior
no se pueden crear usuarios nuevos desde fuera, quita las vistas correspondientes

## Txuletak
/dusk-test pasar los dusk test
pasar los test con coverage
