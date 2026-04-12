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

- Formulario para crear nueva propietaria, añadir campo id (zbkia / Num) comprobar que sea único. Mostrar el id en el listado de propietarias del panel.
- [ ] Estatutos de la comunidad y de cada portal o planta de garaje
- Aktak
- Resultado votaciones
- Deialdiak sartzeko formularioa
- bozketak pdf presencial/delegado
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

## Implementation Plan

### Goal

- [Konpondu textarea elebiduneko mini-editorean idaztean cursorra hasierara salto egitea, edukia galdu gabe eta UX egonkorrarekin.]

### Technical Decisions

- [Mini-editorearen sinkronizazioa berrikusiko da `resources/views/components/admin/bilingual-tabs.blade.php` osagaian, `contenteditable` + Livewire eguneratzeen arteko re-render zikloa mozteko.]
- [Mantendu egingo da egungo elebidun tab egitura (`x-show`, ez `x-if`) eta datu-fluxua zentralizatuko da `sync()` metodoan, cursor posizioa ez apurtzeko.]
- [Livewire eguneratze maiztasuna doituko da (defer/debounce edo ignore estrategiaren bidez), idazketa bakoitzean DOM osoa berreraiki ez dadin.]

### Execution Steps

- [x]   1. Erreproduzitu arazoa settings/admin mini-editorean eta identifikatu zein lotura (`@input` + `$wire.set`) ari den cursorra resetatzen.
- [x]   2. `bilingual-tabs` osagaian konponketa aplikatu (sinkronizazio estrategia egokia) eta rich-text + field moduak ez direla hausten egiaztatu.
- [x]   3. Dagokion test estaldura gehitu/eguneratu (gutxienez Feature test egonkorra `data-*` selectorrekin, eta beharrezkoa bada Browser test osagarria).
- [x]   4. Docker barruan format + test minimoak exekutatu eta emaitzak balioztatu.

### Work Items

- [x] resources/views/components/admin/bilingual-tabs.blade.php
- [x] tests/Feature/** (ukitutako elebidun editorearen fluxuari dagokiona)
- [ ] tests/Browser/** (beharrezkoa bada cursor/typing portaera bisuala egiaztatzeko)

### Validation

- [x] TDD-based implementation when possible
- [x] Required formatting/lint checks
- [x] Relevant test suite
- [ ] Dusk tests when frontend/flow changes exist
