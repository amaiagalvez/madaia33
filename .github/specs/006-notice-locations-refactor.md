reorganizar notice-locations para que use las tablas locations y properties y modifica todos los sitios en los que se utiliza CommunityLocations. Cuando termines elimina CommunityLocations

## Inplementazio plana

### Helburua

- [x] `notice_locations` egitura berrantolatu, iragarkiak `locations` eta `properties` taula errealekin lotzeko, kode/mota konstanteetan oinarritu gabe.
- [x] `CommunityLocations` kendu eta haren erabilera guztiak datu-baseko iturri bakarrera migratu.

### Erabaki teknikoak

- [x] `notice_locations` taulak FK bidez lan egingo du: `location_id` edo `property_id` erabiliz, eta ez `location_type`/`location_code` testu gordinarekin.
- [x] Datu-migrazioak lehendik dauden erregistroak `locations.code` bidez backfill egingo ditu, datuak galdu gabe.
- [x] Admin eta front-eko hautatzaileek `Location` eta `Property` erregistroetatik eraikiko dituzte aukerak; ez dute gehiago `App\CommunityLocations` erabiliko.
- [x] `Notice`, `NoticeLocation`, `PublicNotices` eta admin notice manager-ek erlazio eager-loaded-ak erabiliko dituzte N+1 saihesteko.

### Exekuzio urratsak

- [x] 1. Benetako eskema berria definitu: `notice_locations` migrazio berria, `NoticeLocation` modeloa eta `Notice` erlazioak egokitu.
- [x] 2. Lehendik dauden `location_code`/`location_type` datuak `locations` taulara migratu eta bateragarritasuna egiaztatu.
- [x] 3. `AdminNoticeManager` eta bere Blade bistak eguneratu, kokalekuak eta propietateak DBtik kargatzeko.
- [x] 4. `PublicNotices` eta notice card bistak eguneratu, iragazkiak eta badge-ak erlazio berriekin funtziona dezaten.
- [x] 5. Seeders, factories eta notice-related testak berridatzi, `CommunityLocations` kendu eta `Location`/`Property` factory-ak erabil ditzaten.
- [x] 6. `CommunityLocations.php` eta lotutako unit test zaharrak ezabatu; repo-notes eta ERD dokumentazioa sinkronizatu.

### Egin beharreko lanak

- [x] `database/migrations/` barruan `notice_locations` berrantolaketa eta datu-migrazioa.
- [x] `app/Models/Notice.php` eta `app/Models/NoticeLocation.php` erlazio berriekin.
- [x] `app/Livewire/AdminNoticeManager.php` eta [resources/views/livewire/admin/notice-manager.blade.php](/home/amaia/Dokumentuak/madaia33/resources/views/livewire/admin/notice-manager.blade.php) hautatzaile berriekin.
- [x] `app/Livewire/PublicNotices.php`, [resources/views/livewire/front/public-notices.blade.php](/home/amaia/Dokumentuak/madaia33/resources/views/livewire/front/public-notices.blade.php) eta [resources/views/components/front/notice-card.blade.php](/home/amaia/Dokumentuak/madaia33/resources/views/components/front/notice-card.blade.php) iragazki/badge berriekin.
- [x] `database/seeders/*`, `tests/Feature/*Notice*`, `tests/Browser/LocationFilterTest.php` eta `tests/Unit/NoticeLocationModelTest.php` eguneratzea.
- [x] `.docs/repo-notes.md` eta `.github/skills/database-schema-mermaid/SKILL.md` eguneratzea, `CommunityLocations` jada ez delako iturri nagusia.

### Balidazioa

- [x] TDD bidezko eguneraketa notice-related Feature/Unit testetan.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact` notice-related fitxategiekin.
- [x] Front-eko iragazkia aldatzen bada, dagokion Dusk edo Browser test minimoa exekutatzea.

# Zuzenketak 1

- [x] iragarkia forrmularioan Kokapena(k) eremuan ez dira properties guztiak agertu behar, locatons agertu behar dira y no añadir los trasteros a las opciones del campo kokapena(k)