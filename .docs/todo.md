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
[ ] Crear un nuevo agente, nombre sorgina con el icono de una brujita. Es una experta en PHP, LARAVEL y MYSQL, clean code, buenas prácticas, patrones de diseño DRY, YAGNI, KISS, SOLID... Su labor será revisar el código y buscar incongruencias, detectar código repetido, número de consultas repetidas, consultas lentas, ... y proponer soluciones con lo que encuentre. Funciona en modo "Plan" y luego pide confirmación para realizar los cambios. 
[ ] Crear una regla que guade en specs numeradas lo que le voy pidiendo y el plan de acción. Si hay errores, que añada los errores detectados y las correcciones.

# Code
[ ] test en ingles
[ ] comments en ingles
[ ] cachear los settings para que reducir el número de consultas a la base de datos, cuando se modifica algun setting, borrar la cache y volver a crearla
[ ] template de email compatible con los diferntes gestores de correo, que se use en el envio de correos. Incluido el texto legal que está configurado en la configuración del email
[ ] oganiza las carpetas views/components, views/livewire, view/layouts en admin y front y mueve los ficheros cada uno a su sitio. mira la carpeta partials y reorganizala, lo mismo con el dashboard.
[ ] configurar el sentry
[ ] añadir debugbar y algo para ver los logs
[ ] componente para inputs del formulario

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

# Egiten ...

[ ] en settings añade una nueva section "email_configuration" con los campos necesarios para configurar el email desde el que se enviarán los mensajes con su texto legal. Modificar el envio de los mensajes del formulario de contacto para que use el que está en settings configurado y no lo que hay en el .env. Si es en local, en los devSeeders eliminar lo que haya en esos campos y rellenalos con lo necesario parar usar mailhog

Problemas detectados:
- ✅ RESUELTO: con lo añadido en settings para configurar el correo no es suficiente para poder enviar correos via smtp (campos que necesito: email, email_user, email_password, host, port, secure, ... no se si alguno más)
- ✅ RESUELTO: al pulsar en el boton bidali proba, no sale el modal para pedirme el email al que enviar la prueba (cambié de flux:modal a dialog HTML nativo)
- Ahora sale el modal, escribo el email, pero al puslar bidali no hace nada

## Comprobar

https://chatgpt.com/c/69d78d8d-dd40-832a-a8c7-3144bb109696

Para que todo esto sea legal de verdad, asegúrate de:
✔ Checkbox de privacidad en formularios
✔ HTTPS activo
✔ Contraseñas encriptadas (Laravel ✔)
✔ No mostrar datos de vecinos públicamente
✔ Control de acceso a actas
✔ Logs de acceso (recomendado para votaciones)

## Txuletak
/dusk-test pasar los dusk test
pasar los test con coverage
