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
