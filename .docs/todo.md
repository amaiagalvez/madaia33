- [ ] components field text
- [ ] components tables
- [ ] datatables
- [ ] seo
- [ ] acceesibilidad
- [ ] traducciones repetidas
- [ ] añadir el MCP para que consulte la documentación de laravel


- [ ] dentro de la carpeta madaia33 buscar todos los ficheros y carpetas que sean del usuario root y ponerles amaia:amaia



# Copilot
- [ ] repasar agente lamia
- [ ] agente para crear manuales de usuario
- [ ] agente auditor de ENS e 27001

# Code
- [ ] crear un componente, si todavía no lo hay, para que todas los listados tablas tengan la missma estructura
- [ ] el formato de todos los formularios tanto de crear como editar tienen que tener el mismo aspecto que el de crear un nuevo anuncio, crear un componente si no lo hay

- [ ] user hizkuntza, besdin du front-ekoa
- [ ] template de email compatible con los diferntes gestores de correo, que se use en el envio de correos. Incluido el texto legal que está configurado en la configuración del email
- [ ] configurar el sentry
- [ ] añadir debugbar y algo para ver los logs
- [ ] componente para inputs del formulario
- [ ] registro sessions en bd
- [ ] listado users y sus sessions

# Home
- [ ] Osatu pribatutasun-politika eta lege-oharrak testuak
- [ ] Cookies sartu

# Panela
- [ ] Mezuak. Al abrir el mensaje, añade un botón para responderle. Guarda la respuesta en la base de datos y enviale el email. Añade una nueva columna en la taula que indique con iconos si está repondido o no.

- [ ] Iragarkiak. Gehitu hasiera data eta bukaera data eremuak, gehitu zutabea zerrendan eta front-ean kontrolatu eta bakarrik erakutsi indarrean daudenak (aldatu dezakezu migrazioa, ez dago indarrean)

- [ ] jarraitu garbitzen auth blade-ak (erabiltzen ez direnak kendu)
- [ ] ante una auditoría, cómo le explico al auditor/a la calidad de las votaciones?
- [ ] añadir hizkuntza a la ficha de propietaria y al user, mantener sincronizados tanto el idioma como el nombre y el email con el nombre y el email del koop1. Al loguearse por defecto se cargará el idioma del user logeado
- [ ] en el skill db-schema separar las tablas de las votoaciones en otro bloque

- [ ] Bidalketak. Todos los mensajes que se envían guardalos en una tabla "receivers" con el user_id de a quien se le ha enviado, el email al que se ha enviado, el asunto y el texto y la fecha de envío y si lo hubiere el usario que estaba logeado cuando se envio el mensaje
- [ ] Trackea los mensajes para que se pueda saber quien lo ha habierto y quien ha pinchado en los enlaces del mensaje si los hubiera

- [ ] Añadir espacio Obra (info, formulario, doocumentacion)


# profila
- [ ] usa el mismo componente para mostrar en el menu del front y del aginte-panela el nombre de usuario y el logout, usa como referencia el del aginte panela que está mejor
- [ ] 
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

## Hobekuntzak
- [ ] en settings en la seccion email_configuration, ocultar todos los campos menos el nombre (ponerlos como si fueran tipo password), añadir botón para modificarlos y que pida contraseña antes de modicarlos
- [ ] la opción de doble factor. Pasa lo que ya está echo del doble factor a esta nueva pantalla
- [ ] añadir espacio para comercios
- [ ] en el aginte-panela añade estadísticas de las tablas que falta