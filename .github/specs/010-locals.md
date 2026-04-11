## Implementation Plan - locations type `local`

### Goal

- Gehitu `local` mota `locations.type` eremuan, eta `portal` motaren portaera bera izan dezala nonahi (seeders, iragazkiak, hautaketa-zerrendak, bozketa hautagarritasuna, admin/public bistak eta testak).

### Technical Decisions

- `local` gehituko da dagoen fluxua hautsita ez uzteko, `portal` ordezkatu gabe.
- `portal` + `local` batera tratatuko dira `residential`/etxebizitza multzo gisa behar den tokietan, eta `garage`/`storage` logika bereiziak mantenduko dira.
- Eragin zuzena duen leku bakoitzean eguneratuko da (ez da abstrakzio berririk gehituko YAGNI/KISS jarraituz).

### Execution Steps

- [ ] 1. Datu-eredua egokitu: `locations.type` enum-era `local` gehitu, `Location` model scope/laguntzaileak zabaldu, eta factory state berria gehitu.
- [ ] 2. Seeders egokitu: `LocationSeeder`, `PropertySeeder`, `DevSeeder` eta lotutako bootstrap datuak `local` onartzera zabaldu (portalarekin pareko jokabidea).
- [ ] 3. Erabilera-filtro guztiak egokitu: `whereIn(['portal', ...])` edo `type === 'portal'` duten query eta iragazkiak `local` ere kontuan hartzera aldatu (Owners, Users, Notice manager, Public notices, Votings eligibility, etab.).
- [ ] 4. UI eta route testuingurua egokitu: admin locations nabigazioa/routak, taulak eta etiketa/itzulpenak (`eu` + `es`) `local` motari eusteko.
- [ ] 5. Test suite eguneratu/gehitu: ukitutako jokabideetan `local` kasuak gehitu (seeders, filtroak, eligibility eta bistak), eta gutxieneko test espezifikoak exekutatu Docker bidez.

### Work Items

- [ ] `database/migrations/*locations*`
- [ ] `app/Models/Location.php`
- [ ] `database/factories/LocationFactory.php`
- [ ] `database/seeders/LocationSeeder.php`
- [ ] `database/seeders/PropertySeeder.php`
- [ ] `database/seeders/DevSeeder.php`
- [ ] `app/Support/VotingEligibilityService.php`
- [ ] `app/Livewire/Admin/Owners.php`
- [ ] `app/Livewire/Admin/Users.php`
- [ ] `app/Livewire/Admin/Votings.php`
- [ ] `app/Livewire/PublicNotices.php`
- [ ] `app/Livewire/AdminNoticeManager.php`
- [ ] `resources/views/livewire/admin/owners/index.blade.php`
- [ ] `resources/views/layouts/admin/main.blade.php`
- [ ] `routes/private.php`
- [ ] `lang/eu/admin.php` eta `lang/es/admin.php`
- [ ] Lotutako `tests/Feature/**` (eta beharrezkoa den `tests/Unit/**`)

### Validation

- [ ] TDD moduan ahal den tokian: testak eguneratu lehenik (gutxienez kasu kritikoetan)
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact [ukitutako fitxategiak]`
- [ ] VS Code Problems panelean ukitutako fitxategietako errore/abisuak berrikusi


## Moldaketak
- [x] los trasteros también tienen porcentajes