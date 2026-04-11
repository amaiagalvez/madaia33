hay varios roles

- superadmin, solo el user con id=1 tiene este rol, no hay opción de asignarselo a nadie más
tendrá acceso a todo
en ningún caso puede votar
- admin general, para la administradora general y la persona presidente
tendrá acceso a la lista de avisos, pero solo podrá crear avisos para todos, no tendrá la opción de elegir uno concreto en kokalekuak
tendrá acceso a la lista de kokapenak donde podrá creear, editar
en ningún caso puede votar si no también el rol de propietaria
- admin comunidad, para los administradores de cada portal y planta de garaje
tendrá acceso a la lista de avisos, pero solo podrá crear avisos para el garaje o portal que tenga asignado
en ningún caso puede votar  si no también el rol de propietaria
- propietaria para el comun de los usarios
cuando se crea una nueva propietaria, automaticamente se crea su usuario, asignarle automáticamente este rol
podrán votar siempre que cumplan las condiciones de las votaciones
podrán su perfil
- voto delegado 
solo para poder usar la funcionalidad de voto delegado
en ningún caso puede votar  si no también el rol de propietaria

necesito un CRUD para los usarios y para poder asignarles permisos, en el listado no debe de aparecer el user con id 1

## Inplementazio plana

### Helburua

- Rol eta baimen sistema sinple eta sendoa ezartzea, `superadmin`, `admin_general`, `admin_comunidad`, `propietaria` eta `voto_delegado` rolekin.
- Erabiltzaileen CRUD admin panel berria eskaintzea, rolekin lotutako murrizketa funtzional guztiak betez.
- Segurtasun arauak aplikatzea bai UI mailan bai backend mailan (route, Livewire eta negozio-logika), bypass arriskua saihesteko.

### Erabaki teknikoak

- Rolak datu-basean gordeko dira eta `users` taularekin erlazionatuko dira (many-to-many), hardcode inplizituak saihesteko.
- `superadmin` rola ezin da CRUD bidez esleitu edo kendu; `user id=1` erabiltzailearentzat bakarrik egongo da eskuragarri.
- Baimen-kontrola middleware/policy/guard mailan ezarriko da, eta UI-ko ezkutatzea bigarren geruza izango da.
- `admin_comunidad` rolaren kasuan, baimendutako `location` multzoa esplizituki mapatuko da (erabiltzaile-lokazio erlazioa), iragarkiak eta zerrendak iragazi ahal izateko; erabiltzaile bakoitzak kokapen bat baino gehiago izan ahal izango du.
- `admin_comunidad` rolak kokapenen kudeaketa egin ahal izango du, baina bere esleitutako kokapenen barruan soilik.
- `propietaria` rola `CreateOwnerAction` fluxuan automatikoki esleituko da erabiltzailea sortzean.
- Bozketetan, parte hartzeko baldintza berria izango da: `propietaria` edukitzea; bestela botoa ukatu.
- Erabiltzaileen CRUD-ean edozein eremu editagarri izango da negozio-arauak errespetatuta.
- `user id=1` erabiltzailea immutable izango da: ezin editatu eta ezin ezabatu (eta listan ere ez da erakutsiko).

### Exekuzio urratsak

- [x] 1. Egungo autentikazio eta admin sarbide puntuak rolekin lotzeko oinarrizko datu-eredua sortu (migraketak + modelo erlazioak).
- [x] 2. Rol check geruza bateratua sortu (adibidez middleware/ability helper), route eta Livewire sarreretan aplikatzeko.
- [x] 3. `superadmin` arau berezia inplementatu (`id=1` bakarrik) eta CRUD UI-n blokeatu.
- [x] 4. Erabiltzaileen CRUD admin atala sortu (listatu, sortu, editatu, ezabatu soft-delete bidez), `id=1` ezkutatuz listetan eta edit/delete ekintzak blokeatuz.
- [x] 5. Rol-esleipena CRUD-etik kudeatu eta negozio-arau bereziak inplementatu (`admin_general`, `admin_comunidad`, `voto_delegado`, `propietaria`).
- [x] 6. `admin_comunidad` baimenak lotu: iragarkiak sortzean lokazio iragazkia, eta lokazio-edizio/sarbide murrizketak.
- [x] 7. Bozketa-fluxuan rolen baldintzak ezarri: `superadmin` inoiz ez bozkatu; `admin_general`/`admin_comunidad`/`voto_delegado` bakarrik bozkatu `propietaria` ere badute.
- [x] 8. Nabigazio/menu bistetan rol araberako aukerak erakutsi/ezkutatu, backend baimenekin koherente.
- [x] 9. i18n testu berriak gehitu (`lang/eu`, `lang/es`) eta UI mezuak eguneratu.
- [x] 10. Dagokion dokumentazioa sinkronizatu (route/view erlazioak aldatuz gero `views-structure-mermaid`, DB egitura aldatuz gero `database-schema-mermaid`).

### Egin beharreko lanak

- [x] `database/migrations/*`:
    - rol taulak/pibot taula berriak.
    - `user_location` (edo baliokidea) `admin_comunidad` kasurako, erabiltzaile bakoitzak kokapen anitz izan ditzan.
- [x] `app/Models/User.php` eta erlazionatutako modeloak:
    - rol erlazioak eta helper metodoak.
- [x] `routes/web.php`:
    - admin/users route berriak eta role middleware aplikazioa.
- [x] `resources/views/layouts/admin/main.blade.php`:
    - menu aukeren role-based ikusgarritasuna.
- [x] `app/Livewire/AdminNoticeManager.php`:
    - lokazio aukera eta gordetze murrizketak rolez.
- [x] `app/Livewire/Admin/Locations.php` eta lotutako klaseak:
    - komunitate-adminen sarbide eta kudeaketa murrizketak (esleitutako kokapenetara mugatuta).
- [x] `app/Livewire/PublicVotings.php` eta/edo bozketa action/service:
    - boto baimen baldintza berriak.
- [x] `app/Actions/CreateOwnerAction.php`:
    - `propietaria` rol automatikoa.
- [x] `tests/Feature/**` eta beharrezkoa denean `tests/Unit/**`:
    - rolak, CRUD baimenak, bozketa murrizketak eta regresio testak.
- [x] `lang/eu/**`, `lang/es/**`:
    - etiketa eta errore mezu berriak.

### Balidazioa

- [x] TDD bidezko inplementazioa, ahal denean.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`.
- [x] Gutxieneko test multzo eraginkorra exekutatu (`php artisan test --compact` fitxategi/filter zehatzekin).
- [ ] Dagokion quality gate-a: `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.
- [x] VS Code Problems panela berrikusi ukitutako fitxategietan.
- [ ] Frontend ukituak badaude: Dusk pasatu ondoren Lighthouse audit proposamenak.

### Argitutako erabaki lotesleak

- `admin_comunidad` erabiltzaile batek kokapen bat baino gehiago izan ditzake.
- `admin_comunidad` rolak kokapenak kudeatu behar ditu, baina bere esleitutako kokapenetara mugatuta.
- Erabiltzaileen CRUD-ean edozein eremu editatu ahal izango da, aplikazioaren negozio-arauak errespetatuta.
- `user id=1` ezin da ez aldatu ez ezabatu.

# Zuzenketak 1
- [x] en la clase Role, las constantes en ingles, por favor
- [x] el super admin si puede ver la ruta bozketak del front, pero no puede votar
- [x] erabiltzaileen zerrenda loginAs botoiarekin
- [x] los que tengan el rol de propietaria, see pueden logear con el dni, el email del koop1 o el email del koop2
- [x] al pedir cambiar contraseña, se pide un email, ese email puede ser el que esté en la tabla user, o el email del koop1 o el email del koop2
- [x] en el menu del front, si se está logueado que enseñe el nombre y el botón de saioa itxi, como en el aginte-panela

# Kritikotasuna
- [x] ahora necesito que me asegures que todo este flujo está bien validado con test y con dusk-test para el front y que es completamente auditable para no tener ningún problema cuando lo ponga en marcha
- [x] vuleve a repasarlo es un código MUY CRITICO

# Zuzenketak 2
- [x] superadmin (`id=1`) erabiltzaileak [admin/votaciones](admin/votaciones) sarbidea izatea konpondu da
- [x] admin bozketen Livewire osagaiko baimen-checkak route middlewarearekin lerrokatu dira
- [x] regresio testa gehitu da: superadmin `id=1` erabiltzaileak admin bozketen routea ireki dezake
