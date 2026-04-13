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

- [ ] configurar el sentry
- [ ] añadir debugbar y algo para ver los logs
- [ ] datatables
- [ ] seo
- [ ] acceesibilidad
- [ ] traducciones repetidas
- [ ] añadir el MCP para que consulte la documentación de laravel

Igandea
=======
- votaciones resultados (suma de porcentajes)
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

- [ ] deploy
    - [ ] se boorran las carpetas del storage
    - [ ] no se sube la carpeta build
    - [ ] se pierde el storage link
- [ ] config recaptcha, analitics

https://chatgpt.com/c/69d78d8d-dd40-832a-a8c7-3144bb109696

Para que todo esto sea legal de verdad, asegúrate de:
✔ Checkbox de privacidad en formularios
✔ No mostrar datos de vecinos públicamente
✔ Control de acceso a actas
✔ Logs de acceso (recomendado para votaciones)

# Panela
- [ ] Estatutos de la comunidad y de cada portal o planta de garaje
- Aktak
- Deialdiak sartzeko formularioa + pdf + emailez bidali

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
- [ ] con el agente sorgina repasar el código y proponer mejoras (código repetido, test que faltan, ...)

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

## Implementation Plan

### Goal

- [Balidazio-mezuak hizkuntza-tabs kanpoan erakustea eremu eleaniztunetan, input sinpleetan zein mini-editorrean, beti ikusgarri izan daitezen.]

### Technical Decisions

- [Konponketa `resources/views/components/admin/bilingual-tabs.blade.php` osagai partekatuan egingo da, inpaktu bera izan dezan `x-admin.bilingual-field-tabs` eta `x-admin.bilingual-rich-text-tabs` erabilera guztietan.]
- [Tab bakoitzaren pane barruko errore-mezuen ordez, errore laburpen/errendatze komun bat jarriko da tabs bloke nagusiaren azpian, dagozkion locale-field guztietako lehen erroreak ikusgai mantenduz.]
- [Balidazio-estilo bisualak pane barruko input/editoreetan mantenduko dira (`border-red-*`), baina mezu testuala kanpora aterako da irisgarritasuna eta ikusgarritasuna ez galtzeko.]

### Execution Steps

- [x]   1. `bilingual-tabs` osagaiaren markupa berrantolatu errore-mezuak tabs edukitik kanpo renderizatzeko.
- [x]   2. Eguneratu edo gehitu Unit test bat osagai partekatuarentzat, errorea tabs kanpoan agertzen dela egiaztatzeko input eta rich-text moduetarako.
- [x]   3. Exekutatu ukitutako test minimoak eta beharrezko formateoa/egiaztapenak Docker barruan.

### Work Items

- [x] `resources/views/components/admin/bilingual-tabs.blade.php`
- [x] `tests/Unit/BilingualRichTextTabsComponentTest.php`
- [ ] Baliteke test osagarri bat behar izatea `bilingual-field-tabs` erabilerarako

### Validation

- [ ] TDD-based implementation when possible
- [x] Required formatting/lint checks
- [x] Relevant test suite
- [ ] Dusk tests when frontend/flow changes exist

## Txuletak
/dusk-test pasar los dusk test
pasar los test con coverage

## Hobekuntzak
- [ ] en settings en la seccion email_configuration, ocultar todos los campos menos el nombre (ponerlos como si fueran tipo password), añadir botón para modificarlos y que pida contraseña antes de modicarlos
- [ ] la opción de doble factor. Pasa lo que ya está echo del doble factor a esta nueva pantalla
- [ ] añadir espacio para comercios
- [ ] jarraitu garbitzen auth blade-ak (erabiltzen ez direnak kendu)
