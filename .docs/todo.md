# Copilot
- [ ] repasar agente lamia
- [ ] agente para crear manuales de usuario
- [ ] agente auditor de ENS e 27001

# Code Refactor
- [ ] crear un componente, si todavía no lo hay, para que todas los listados tablas tengan la missma estructura
- [ ] el formato de todos los formularios tanto de crear como editar tienen que tener el mismo aspecto que el de crear un nuevo anuncio, crear un componente si no lo hay
- [ ] Ordena los ficheros dentro de la carpeta Livewire en subcarpetas Admin y Front
- [ ] componente para inputs del formulario

# Code
- [ ] reepasar estatistikak home
- [ ] añadir papelera para poder reestaurarr los borrrados
- [ ] tipo votaciones, una persona un voto / por poorcentajes de participación
- [ ] 
- [ ] template de email compatible con los diferntes gestores de correo, que se use en el envio de correos. Incluido el texto legal que está configurado en la configuración del email
- [ ] configurar el sentry
- [ ] añadir debugbar y algo para ver los logs
- [ ] datatables
- [ ] seo
- [ ] acceesibilidad
- [ ] traducciones repetidas
- [ ] añadir el MCP para que consulte la documentación de laravel

Igandea
=======
- Testak Rolak Panela y Front
- votaciones pdf
- votaciones resultados (suma de porcentajes)
- azalpena bozketak front-ean gehitzeko
- spec kiro pendientes
- unificar terminologia

# unificar terminología

locations: Ubicaciones => Comunidades
properties: Propiedades => Fincas
property_assignments => Propiedades
owners => Propietarias

# Home
- [ ] Osatu pribatutasun-politika eta lege-oharrak testuak
- [ ] Cookies sartu
- [ ] legeak.html begiratu

https://chatgpt.com/c/69d78d8d-dd40-832a-a8c7-3144bb109696

Para que todo esto sea legal de verdad, asegúrate de:
✔ Checkbox de privacidad en formularios
✔ No mostrar datos de vecinos públicamente
✔ Control de acceso a actas
✔ Logs de acceso (recomendado para votaciones)

# Panela
- en el profil, añadir un pequeño formulario con un textarea para que si alguien tiene propblmas con sus datos mande un mensaje, darle el mismo tratamiento que se le da a ls mensajes que se envía desde eu/harremana, enviar respuesta al mail del usuario logeado y al del admin que está en settings. Cuando se envie un mensaje desde el proofil, en el asunto añadir el texto "PERFIL".


- testo legal que tiene que aceptar los que tiene rol "Voto delegado", al entrar a la vista bozketak del front, si no lo tienen aceptado que les salga un modal, como el de los propietarios, y hasta que no lo acepten que no les deje hacer nada más.

- [ ] Estatutos de la comunidad y de cada portal o planta de garaje
- [ ] Mezuak. Al abrir el mensaje, añade un botón para responderle. Guarda la respuesta en la base de datos y enviale el email. Añade una nueva columna en la taula que indique con iconos si está repondido o no.

- [ ] Iragarkiak. Gehitu hasiera data eta bukaera data eremuak, gehitu zutabea zerrendan eta front-ean kontrolatu eta bakarrik erakutsi indarrean daudenak (aldatu dezakezu migrazioa, ez dago indarrean)

- [ ] ante una auditoría, cómo le explico al auditor/a la calidad de las votaciones?

- [ ] en el skill db-schema separar las tablas de las votoaciones en otro bloque

- [ ] Bidalketak. Todos los mensajes que se envían guardalos en una tabla "receivers" con el user_id de a quien se le ha enviado, el email al que se ha enviado, el asunto y el texto y la fecha de envío y si lo hubiere el usario que estaba logeado cuando se envio el mensaje
- [ ] Trackea los mensajes para que se pueda saber quien lo ha habierto y quien ha pinchado en los enlaces del mensaje si los hubiera

- [ ] Añadir espacio Obra (info, formulario, doocumentacion)

# nohizbehinka egiteko 2026-04-11
- [ ] Repasa los feature test y los que se pueedan convertir en unit pasalos
- [ ] Repasa la suit de test y completa los que falten
- [ ] con el agente sorgina repasar el código y proponer mejoras

- [ ] sudo find /home/amaia/Dokumentuak/madaia33 -user root -exec chown amaia:amaia {} +
- [ ] bash scripts/enforce-indent-4.sh 
- [ ] docker => reformat eta quality
- [ ] docker => pentest
- [ ] dusk-test

## manual de usuaria
crear una miniweb en html con las instrucciones para usar la aplicación, añade texto y pantallazos para que los usuarios que se logueen tengan claro cómo usar la aplicación
añadir una ruta al menú del panel 
tiene que estar en dos idiomas eu y es
- [ ] añadir una regla al agente amalur para que lo mantenga actualizado

## Txuletak
/dusk-test pasar los dusk test
pasar los test con coverage

## Hobekuntzak
- [ ] en settings en la seccion email_configuration, ocultar todos los campos menos el nombre (ponerlos como si fueran tipo password), añadir botón para modificarlos y que pida contraseña antes de modicarlos
- [ ] la opción de doble factor. Pasa lo que ya está echo del doble factor a esta nueva pantalla
- [ ] añadir espacio para comercios
- [ ] jarraitu garbitzen auth blade-ak (erabiltzen ez direnak kendu)

## Implementation Plan - Admin bozketak: galdera testua galtzen da gordetzean

### Goal

- Admin bozketen sortze/edizio formularioan `questionEu` eta `questionEs` balioak modu egonkorrean sinkronizatzea, gorde ostean testua ez galtzeko eta bozketa berria normaltasunez sortu ahal izateko.

### Technical Decisions

- Errorearen jatorria osagai partekatuan dagoela tratatu: [resources/views/components/admin/bilingual-tabs.blade.php](resources/views/components/admin/bilingual-tabs.blade.php) rich-text moduan erabiltzen denean `sync/format/link` metodoak falta dira.
- Konponketa KISS/DRY bidez egingo da: rich-text sinkronizazio metodoak osagai partekatuan bertan inplementatuta, parent osagai bakoitzean kode bikoiztua saihesteko.
- Erregresioa saihesteko, bozketa sortze-fluxua estaltzen duen Pest test bat gehitu/eguneratuko da [tests/Feature/AdminVotingsTest.php](tests/Feature/AdminVotingsTest.php) fitxategian, EU/ES galdera testua datu-basean gordetzen dela egiaztatuz.

### Execution Steps

- [ ] 1. [resources/views/components/admin/bilingual-tabs.blade.php](resources/views/components/admin/bilingual-tabs.blade.php) eguneratu, rich-text eremuetarako `sync`, `format` eta `link` Alpine metodoak gehituz.
- [ ] 2. [tests/Feature/AdminVotingsTest.php](tests/Feature/AdminVotingsTest.php) testa gehitu/eguneratu, bozketa berriaren `question_eu` eta `question_es` persistitzen direla ziurtatzeko.
- [ ] 3. Docker barruan test minimoa exekutatu (`php artisan test --compact` fitxategi espezifikoarekin).
- [ ] 4. Docker barruan `vendor/bin/pint --dirty` exekutatu ukitutako PHP fitxategi bakoitzerako.
- [ ] 5. VS Code Problems berrikusi ukitutako fitxategietan eta agertutako arazo berriak konpondu.

### Work Items

- [ ] [resources/views/components/admin/bilingual-tabs.blade.php](resources/views/components/admin/bilingual-tabs.blade.php)
- [ ] [tests/Feature/AdminVotingsTest.php](tests/Feature/AdminVotingsTest.php)

### Validation

- [ ] TDD oinarritutako eguneraketa (testa idatzi/eguneratu, gero inplementazioa balidatu)
- [ ] Pint formateoa (`vendor/bin/pint --dirty`)
- [ ] Eragindako test suite minimoa (`tests/Feature/AdminVotingsTest.php`)
