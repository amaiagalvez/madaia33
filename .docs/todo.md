[ ] components field text
[ ] components tables
[ ] datatables
[ ] seo
[ ] acceesibilidad
[ ] traducciones repetidas
[ ] añadir el MCP para que consulte la documentación de laravel


[ ] dentro de la carpeta madaia33 buscar todos los ficheros y carpetas que sean del usuario root y ponerles amaia:amaia
[ ] añadir espacio para comercios
[ ] en los test todos los $this-> dan error en PROBLEMS
[ ] pasar pain no en modo test, dejarle a él que lo corrija
[ ] user hizkuntza, besdin du front-ekoa

# Copilot

# Code
[ ] test en ingles
[ ] comments en ingles
[ ] cachear los settings para que reducir el número de consultas a la base de datos, cuando se modifica algun setting, borrar la cache y volver a crearla
[ ] template de email compatible con los diferntes gestores de correo, que se use en el envio de correos. Incluido el texto legal que está configurado en la configuración del email
[ ] configurar el sentry
[ ] añadir debugbar y algo para ver los logs
[ ] componente para inputs del formulario
[ ] pasahitza aldatu funtzionalitatea
[ ] registro sessions en bd
[ ] listado users y sus sessions

# Home
[ ] Contact: subtitulua testua txukundu. 
cambia el subtitulo del formulario de contacto del front, algo asi como Envia tu aportación, consulta, duda... te responderemos lo antes posible o algo así, como a ti mejor te parezca
[ ] Osatu pribatutasun-politika eta lege-oharrak testuak
[ ] Cookies sartu

# Panela
[ ] en le panel separa un poco el header del menú superior
[ ] Mezuak. Al abrir el mensaje, añade un botón para responderle. Guarda la respuesta en la base de datos y enviale el email. Añade una nueva columna en la taula que indique con iconos si está repondido o no.
[ ] En el panel MezuakFiltro mensajes con botoncitos, como en el front de Iragarkiak. Leidos y no Leidos. Por defecto, al entrar mostrar solo los leidos
[ ] Iragarkiak. Gehitu hasiera data eta bukaera data eremuak, gehitu zutabea zerrendan eta front-ean kontrolatu eta bakarrik erakutsi indarrean daudenak (aldatu dezakezu migrazioa, ez dago indarrean)
[ ] settings al cambiar de tab de section, si no se han guardado los cambios avisar
[ ] settings imagen para el logo (sustituir donde se usa madaia33.png por este campo)
[ ] en settings, el botón "enviar prueba" solo se debe activar cuando no haya cambios sin guardar, al modificar algo descativarlo
[ ] en settings en la seccion email_configuration, ocultar todos los campos menos el nombre (ponerlos como si fueran tipo password) y al 
[ ] text area, que se adapte al contenido automaticamente para que loos forrmularios sean más pequeños
[ ] Mezuak: bilatzailea en cualquier campo

# Egiten ...

## Comprobar

https://chatgpt.com/c/69d78d8d-dd40-832a-a8c7-3144bb109696

Para que todo esto sea legal de verdad, asegúrate de:
✔ Checkbox de privacidad en formularios
✔ No mostrar datos de vecinos públicamente
✔ Control de acceso a actas
✔ Logs de acceso (recomendado para votaciones)

## Txuletak
/dusk-test pasar los dusk test
pasar los test con coverage
