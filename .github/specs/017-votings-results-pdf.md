
## Implementation Plan

### Goal

- [Admin bozketa zerrendan emaitzen PDF botoia gehitu eta hautatutako bozketen emaitza agregatuen txostena deskargatzea: bozkatu duten pertsonak, aukera bakoitzeko votes_count eta pct_total, eta grafiko bisual lagungarriak]

### Technical Decisions

- [Emaitzen deskarga admin-eko uneko PDF botoien lerro berean gehituko da, Livewire ekintza berri batekin (`downloadResultsPdf`) eta route berri batekin.]
- [PDF sorkuntza uneko arkitekturarekin lerrokatuko da: controller + builder + Blade PDF txantiloia (`VotingPdfController` + `VotingPdfBuilder` + `resources/views/pdf/votings/...`).]
- [Agregazio datuak `voting_option_totals` taulatik hartuko dira (`votes_count`, `pct_total`) eta fallback seguru batekin osatuko dira datu historikoetan hutsunerik balego.]
- [Grafikoak DOMPDF-rekin bateragarriak diren elementu estatikoekin egingo dira (barra horizontal proportzionalak + legenda), JS gabeko ikuspegia bermatzeko.]
- [Bozketa bakoitzean summary gehigarria sartuko da: boto-emaile kopurua (`VotingBallot`), aukera irabazlea/parekotasuna, eta `%` total metatua aukera bakoitzeko.]
- [I18n kate berriak `lang/eu/votings.php` eta `lang/es/votings.php` fitxategietan gehituko dira.]

### Execution Steps

- [x]   1. Emaitzen PDF deskarga-fluxua gehitu: admin botoia, Livewire metodoa, route eta controller endpoint berria.
- [x]   2. Builderrean emaitza-DTO/egitura prestatu hautatutako bozketentzat (votantes, votes_count, pct_total, totalak eta ratioak).
- [x]   3. PDF txantiloi berria sortu emaitza ikusgarriekin eta grafiko estatikoekin.
- [x]   4. Testak gehitu/eguneratu (Livewire redirect + Feature PDF edukia/headers + i18n fitxategi izenak beharrezkoa bada).
- [x]   5. Formateatu eta balidatu Docker barruan (`vendor/bin/pint --dirty`, test selektiboak, quality check behar denean).

### Work Items

- [x] app/Livewire/Admin/Votings.php
- [x] resources/views/livewire/admin/votings/index.blade.php
- [x] routes/private.php
- [x] app/Http/Controllers/VotingPdfController.php
- [x] app/Services/VotingPdfBuilder.php
- [x] resources/views/pdf/votings/results.blade.php (berria)
- [x] lang/eu/votings.php
- [x] lang/es/votings.php
- [x] tests/Feature/AdminVotingsTest.php
- [x] tests/Feature/VotingPdfDownloadTest.php

### Validation

- [ ] TDD-based implementation when possible
- [x] Required formatting/lint checks (`vendor/bin/pint --dirty`)
- [x] Relevant test suite (`php artisan test --compact` targeted files)
- [ ] Dusk tests when frontend/flow changes exist (gutxienez botoi berria + deskarga fluxua)
