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
- [ ] user hizkuntza, besdin du front-ekoa

# Copilot
- [ ] repasar agente lamia
- [ ] agente para crear manuales de usuario

# Code
- [ ] test en ingles
- [ ] comments en ingles
- [ ] dividir la carpeta Actions por features o modelos, lo que creas más conveniente
- [ ] para las partes de las views  que sean sensibles, crear dusk-test
- [ ] Ordena Livewire en subcarpetas Admin y Front
- [ ] crear un componente para que todas las tablas tengan la missma estructura
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
- [ ] settings texto argazkiak eskatzeko en section front, reemplazarlo
- [ ] settings email principal en section front Reemplazar por este todo los info@madaia33.eus
- [ ] settigns nombre web en section front. Reemplazar donde se utilice APP_NAME por este nuevo campo
- [ ] settings cookien testu legala n section front, crea otra ruta como con lege-oharrra y añadela en eel footer junto con las otas dos.
- [ ] jarraitu garbitzen auth blade-ak (erabiltzen ez direnak kendu)
- [ ] añadir al menu un enlace debajo del menu aginte-panela para ir a la web publica
- [ ] ante una auditoría, cómo le explico al auditor/a la calidad de las votaciones?
- [ ] añadir hizkuntza a la ficha de propietaria y al user, mantener sincronizados tanto el idioma como el nombre y el email con el nombre y el email del koop1. Al loguearse por defecto se cargará el idioma del user logeado
- [ ] en el skill db-schema separar las tablas de las votoaciones en otro bloque
- [ ] traducciones en la tabla Erabiltzaileak
- [ ] moverlo al apartado configución del menu
- [ ] Bidalketak. Todos los mensajes que se envían guardalos en una tabla con el user_id, el asunto y el texto y la fecha de envío y si lo hubiere el usario que estaba logeado cuando se envio el mensaje
- [ ] Trackea los mensajes para que se pueda saber quien lo ha habierto y quien ha pinchado en los enlaces del mensaje si los hubiera
- [ ] la opción de doble factor. Pasa lo que ya está echo del doble factor a esta nueva pantalla
- [ ] usa el mismo componente para mostrar en el menu del front y del aginte-panela el nombre de usuario y el logout, usa como referencia el del aginte panela que está mejor

# profila
un usuario tiene que tener un perfil desde el que pueda ver:

organizar la información en diferentes pestañas
- las votaciones en las que ha tomado parte y cuando
- las sesiones que ha abierto, su inicio y su fin y el tiempo de conexión
- un enlace a cambiar contraseña que vaya al de cambiar la contraseña
- su ficha de propietaria y sus propiedades para poder validarlas (asegurate de que no puede la ficha de ningua otra propietaria)
- la primera vez que un usuario se loguee, pidele que acepte las condiciones de uso (texto que se almacenará en settings en el section owners en dos idiomas) y luego muestrale una pantalla con sus propiedades asignadas para que las valide, si no las tiene validadas ya (oweer validation)

- añadir el icono para accedeer al perfil junto al nombre del usuario, tanto en el menú del front como en el menu del aginte-panaela

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

## Txuletak
/dusk-test pasar los dusk test
pasar los test con coverage
