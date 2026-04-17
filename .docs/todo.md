# nohizbehinka egiteko 2026-04-11
- [ ] Repasa todos los feature test y los que se puedan convertir en unit test sin acceso a la base de datos pasalos
- [ ] Repasa la suit de test y completa los que falten
- [ ] con el agente sorgina repasar el código y proponer mejoras (código repetido, test que faltan, consultas a la base de datos repetidas, ...)
- [ ] con chatgpt pedirle que haga una auditoría de accesibilidad y pasarsela @lamia

- [ ] sudo find /home/amaia/Dokumentuak/madaia33 -user root -exec chown amaia:amaia {} +
- [ ] bash scripts/enforce-indent-4.sh 
- [ ] docker => reformat eta quality
- [ ] docker => pentest
- [ ] dusk-test

# Despues de publicar master
- [ ] configurar el sentry
- [ ] añalizar cookiena chatgpt-rekin
- [ ] probar envio whatcapp en el movil

# Code
- votaciones resultados (suma de porcentajes)
- spec kiro pendientes (obra)
- [ ] reepasar estatistikak home
- [ ] unificar terminología
  - locations: Ubicaciones => Comunidades
  - properties: Propiedades => Fincas
  - property_assignments => Propiedades
  - owners => Propietarias

- [ ] En el listado de bozketak añadir un botón para Enviar un email. Crear una nueva campaña. Los recipients serán los owners que puedan votar en esa votación. Debe de abrir el formulario de la campaña creada para que pueda añadir el texto a enviar. Es decir, las locations que hay en la bozketa y las que se deben crear para la campaña deben de ser las mismas.
En el texto de la campaña poner un aviso "añadir orden de día y documentos para delegar el voto y votar presenciamelte"

En el listado de admin/campanas
- al pinchar en el botón programar, modal para pedir cuando programarlo
- añadir nueva columna con el número de mensajes de abiertos

- cuando se pulsa en el botón de enviar en un envío de whatcapp, actualizar la fecha de envió de esa campaña, ahora no se actualiza

# Panela
- [ ] Estatutos de la comunidad y de cada portal o planta de garaje. Permisos, quien ve qué
- Aktak
- Deialdiak sartzeko formularioa + pdf + emailez bidali + iragarkia sortu

- [ ] Mezuak. Al abrir el mensaje, añade un botón para responderle. Guarda la respuesta en la base de datos y enviale el email. Añade una nueva columna en la taula que indique con iconos si está repondido o no.

- [ ] Iragarkiak. Gehitu hasiera data eta bukaera data eremuak, gehitu zutabea zerrendan eta front-ean kontrolatu eta bakarrik erakutsi indarrean daudenak (aldatu dezakezu migrazioa, ez dago indarrean)
  
- [ ] Evios de mesajes. Repasa dónde se evian mensajes (a excepción de las campañas, esos ya están controlados) (formulario perfil, nuevo owner, alguno más?)
- [ ] Crea una campaña especial con el id = 1 con el asunto "Web-etik Bidalitako Mezuak" o "Mensajes enviados desde la web" según el idioma. El seeder tiene que valer para producción.
- [ ] Todos esos los mensajes que se envían directamente guardalos en una tabla "recipients" con el campaing_id = 1 y con el owner_id de a quien se le ha enviado, el asunto y el texto y la fecha de envío y si lo hubiere el usario que estaba logeado cuando se envio el mensaje. Marcarlo como enviado.
- [ ] Trackea los mensajes para que se pueda saber quien lo ha habierto y quien ha pinchado en los enlaces del mensaje si los hubiera.

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
- [ ] añadir papelera para poder reestaurar los borrrados
- [ ] añadir tipo votaciones, una persona un voto / por porcentajes de participación
- [ ] en todos los sitios en lo que debe salir un modal, crear un dusk-test para que lo compruebe y así no tener riesgo de perder esa funcionalidad.
- [ ] repasar queries duplicadas que indica la debugbar
- examinar la pestaña Queries del debugbar y analizar las consultas duplicadas
  - Front
    - iragarkiak
    - argazki-bilduma
    - kontaktua
    - pribatua
  - Front logeado
    - profila y las diferentes tabs que tiene
    - bozketak
  - Aginte-panela
    - admin
    - admin/avisos
    - admin/imagenes
    - admin/mensajes
    - admin/campanas
    - admin/campanas/{id}
    - admin/portales
    - admin/propietarias
    - admin/votaciones
    - admin/configuracion
    - admin/usuarios
- [ ] ante una auditoría, cómo le explico al auditor/a la calidad de las votaciones?
- [ ] config recaptcha, analitics