# nohizbehinka egiteko 2026-04-11
- [ ] Repasa los feature test y los que se pueedan convertir en unit pasalos
- [ ] Repasa la suit de test y completa los que falten
- [ ] con el agente sorgina repasar el código y proponer mejoras (código repetido, test que faltan, ...)

- [ ] sudo find /home/amaia/Dokumentuak/madaia33 -user root -exec chown amaia:amaia {} +
- [ ] bash scripts/enforce-indent-4.sh 
- [ ] docker => reformat eta quality
- [ ] docker => pentest
- [ ] dusk-test

# Code
- [ ] reepasar estatistikak home
- [ ] añadir papelera para poder reestaurar los borrrados
- [ ] añadir tipo votaciones, una persona un voto / por porcentajes de participación

- [ ] configurar el sentry
- [ ] añadir debugbar y algo para ver los logs logviewer
- [ ] accesibilidad

- votaciones resultados (suma de porcentajes)
- spec kiro pendientes

# unificar terminología
locations: Ubicaciones => Comunidades
properties: Propiedades => Fincas
property_assignments => Propiedades
owners => Propietarias

- añadir en el formulario de owners el campo whathapp con el "Tienes whachapp" o "Whatcapp-a daukazu"? Añadelo en el formulario de los owners del panel y en perfil tanto al koop1 como al koop2
- Cuando se haga un envio de campaña por whatchapp enviar solo a los que tengan ese nuevo campo activo

# Home
- [ ] Cookies sartu
- [ ] config recaptcha, analitics
- [ ] probar envio whatcapp en el movil

- [ ] En el listado de bozketak añadir un botón para Enviar un email con los pdf delegado y presencial. Crear una nueva campaña y añadir los pdf y los recipients. Los recipients serán los owners que puedan votar en esa votación. Debe de abrir el formulario de la campaña creada para que pueda añadir el texto a enviar.

En el listado de admin/campanas
- al pinchar en el botón programar, modal para pedir cuando programarlo
- añadir nueva columna con el número de mensajes de abiertos

- cuando se pulsa en el botón de enviar en un envío de whatcapp, actualizar la fecha de envió de esa campaña, ahora no se actualiza
- 

- [ ] campaña: añadir un botón, enviar prueba (como tenenmos en el formulario de los settings email_configuration) mostrar un modal para pedir un email y enviar a dicho email una prueba de como queda la campaña. Se enviarán dos emails de prueba, uno en euskera y otro en castellano al email indicado.



# Panela
- [ ] Estatutos de la comunidad y de cada portal o planta de garaje
- Aktak
- Deialdiak sartzeko formularioa + pdf + emailez bidali + iragarkia sortu

- [ ] Mezuak. Al abrir el mensaje, añade un botón para responderle. Guarda la respuesta en la base de datos y enviale el email. Añade una nueva columna en la taula que indique con iconos si está repondido o no.

- [ ] Iragarkiak. Gehitu hasiera data eta bukaera data eremuak, gehitu zutabea zerrendan eta front-ean kontrolatu eta bakarrik erakutsi indarrean daudenak (aldatu dezakezu migrazioa, ez dago indarrean)

- [ ] ante una auditoría, cómo le explico al auditor/a la calidad de las votaciones?

- [ ] Bidalketak. Todos los mensajes que se envían guardalos en una tabla "receivers" con el user_id de a quien se le ha enviado, el email al que se ha enviado, el asunto y el texto y la fecha de envío y si lo hubiere el usario que estaba logeado cuando se envio el mensaje
- [ ] Trackea los mensajes para que se pueda saber quien lo ha habierto y quien ha pinchado en los enlaces del mensaje si los hubiera

- [ ] Añadir espacio Obra (info, formulario, doocumentacion)



## manual de usuaria
crear una miniweb en html con las instrucciones para usar la aplicación, añade texto y pantallazos para que los usuarios que se logueen tengan claro cómo usar la aplicación
añadir una ruta al menú del panel 
tiene que estar en dos idiomas eu y es
- [ ] añadir una regla al agente amalur para que lo mantenga actualizado

# Copilot
- [ ] repasar agente lamia
- [ ] agente para crear manuales de usuario
- [ ] agente auditor de ENS e 27001
- [ ] cómo le digo al agente "amalurra" que primero siempre haga caso a lo que hay en el agent.md y luego con lo suyo propio?

# Code Refactor && Hobekuntzak
- [ ] crear un componente, si todavía no lo hay, para que todas los listados tablas tengan la missma estructura
- [ ] el formato de todos los formularios tanto de crear como editar tienen que tener el mismo aspecto que el de crear un nuevo anuncio, crear un componente si no lo hay
- [ ] Ordena los ficheros dentro de la carpeta Livewire en subcarpetas Admin y Front
- [ ] componente para inputs del formulario
- [ ] datatables
- [ ] añadir el MCP para que consulte la documentación de laravel
- [ ] traducciones repetidas
- [ ] seo
- [ ] twilio https://www.twilio.com/docs/whatsapp
- [ ] en el skill db-schema separar las tablas de las votoaciones en otro bloque