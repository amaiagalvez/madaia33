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
- Resultado votaciones
- [ ] Estatutos de la comunidad y de cada portal o planta de garaje
- Aktak
- Deialdiak sartzeko formularioa + pdf

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

## Spec: Bozketak PDF presencial/delegado

### Implementation Plan

### Goal
- [x] Bi PDF deskarga sortzea (delegatua + presentziala), bozketa aktiboekin eta EU/ES edukiarekin, settings-eko testuak erabilita.

### Technical Decisions
- [x] Settings section berria: `votings`; giltzak: `votings_pdf_delegated_text_eu/es` eta `votings_pdf_in_person_text_eu/es`.
- [x] PDF logika backendean zentralizatzea service + controller batean, Blade txantiloi espezifikoekin.
- [x] Goiburukoa: ezkerrean `front_site_name + " Jabeen Erkidegoa"`, erdian favicon irudia, eskuinean `"Comunidad de Propietarios/a " + front_site_name`.
- [x] Orriko lehen blokea bi zutabekoa: ezker EU, eskuin ES, dagokion settings testuarekin (delegated/in-person).
- [x] Behean, zabalera osoan, bozketa bakoitza behin: galdera EU (lodian) + ES (normalean), eta aukera bakoitzaren ondoan laukitxo markagarriak.

### Execution Steps
- [x] 1. `Setting` + `AdminSettings` + validazioak + itzulpenak eguneratu `votings` section berrirako.
- [x] 2. Admin settings-etan `votings` tab berria gehitu (4 rich text eremu: delegated EU/ES, in-person EU/ES).
- [x] 3. PDF service/controller/view geruza eraiki, bozketa aktiboak eta branding datuak kargatuta.
- [x] 4. Deskarga route-ak eta botoiak gehitu admin bozketen zerrendan eta front bozketen pantailan.
- [x] 5. Testak gehitu/eguneratu (Feature: PDF payload eraikuntza + route sarbidea + edukia).
- [x] 6. Docker bidez pint + test minimoak exekutatu.

### Work Items
- [x] app/Models/Setting.php
- [x] app/Livewire/AdminSettings.php
- [x] app/Validations/AdminSettingsValidation.php
- [x] resources/views/livewire/admin/settings.blade.php
- [x] resources/views/livewire/admin/settings/partials/votings-tab.blade.php (berria)
- [x] lang/eu/admin.php
- [x] lang/es/admin.php
- [x] app/Http/Controllers/VotingPdfController.php (berria)
- [x] app/Services/VotingPdfBuilder.php (berria)
- [x] resources/views/pdf/votings/ballot.blade.php (berria)
- [x] routes/private.php
- [x] routes/public.php
- [x] resources/views/livewire/admin/votings/index.blade.php
- [x] resources/views/livewire/front/public-votings.blade.php
- [x] tests/Feature/VotingPdfBuilderTest.php
- [x] tests/Feature/VotingPdfDownloadTest.php
- [x] tests/Feature/AdminSettingsTest.php

### Validation
- [x] TDD printzipioarekin: integrazioa behar zuten kasuak Feature testetan estali dira.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/VotingPdfBuilderTest.php tests/Feature/VotingPdfDownloadTest.php tests/Feature/AdminSettingsTest.php`
- [ ] Front aldaketek UI fluxua ukitzen badute, Dusk estaldura osagarria exekutatu.
