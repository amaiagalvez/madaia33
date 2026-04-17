- [ ] Evios de mesajes. Repasa dónde se evian mensajes (a excepción de las campañas, esos ya están controlados) (formulario perfil, nuevo owner, alguno más?)
- [ ] Crea una campaña especial con el id = 1 con el asunto "Web-etik Bidalitako Mezuak" o "Mensajes enviados desde la web" según el idioma. El seeder tiene que valer para producción.
- [ ] Todos esos los mensajes que se envían directamente guardalos en una tabla "recipients" con el campaing_id = 1 y con el owner_id de a quien se le ha enviado, el asunto y el texto y la fecha de envío y si lo hubiere el usario que estaba logeado cuando se envio el mensaje. Marcarlo como enviado.
- [ ] Trackea los mensajes para que se pueda saber quien lo ha habierto y quien ha pinchado en los enlaces del mensaje si los hubiera.

## Implementation Plan

### Goal

- `eu/profila?tab=received` pantailan, `campaign_id = 1` denean, `Mezua` zutabean benetan bidalitako edukia erakustea (`message_subject` + `message_body`), ez kanpainaren testu generikoa.

### Technical Decisions

- `ProfileController::receivedMessages()` metodoan egingo da mapaketa baldintzatua: `campaign_id = 1` kasuan `campaign_recipients` eremuen lehentasuna, eta gainerako kasuetan egungo jokabidea mantendu.
- Blade-an (`resources/views/public/profile.blade.php`) egitura bera mantenduko da; datu prestatua bakarrik aldatuko da controller-ean (Clean Blade araua).
- Fallback segurua mantenduko da: balioa hutsik badago, `'—'` erakutsi.

### Execution Steps

- [ ]   1. `receivedMessages()` mapaketa egokitu `campaign_id = 1` detektatuta `subject/message` balioak `message_subject`/`message_body`-tik eraikitzeko.
- [ ]   2. Test fokal bat gehitu/eguneratu profileko `received` fitxan `campaign_id = 1` mezuaren edukia agertzen dela frogatzeko.
- [ ]   3. Pint + test fokalak exekutatu Docker barruan.

### Work Items

- [ ] `app/Http/Controllers/ProfileController.php`
- [ ] `tests/Feature/ProfilePageTest.php`

### Validation

- [ ] TDD-based implementation when possible
- [ ] Required formatting/lint checks (`vendor/bin/pint --dirty`)
- [ ] Relevant test suite (`tests/Feature/ProfilePageTest.php` fokalizatua)
- [ ] Dusk tests when frontend/flow changes exist (ez da beharrezkoa kasu honetan: backend mapaketa + test funtzionala)
