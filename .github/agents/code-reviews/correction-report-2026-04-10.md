# Correction Report — 2026-04-10

## Arazo-motak aurkituta

- UI koherentzia-arazoa (1): jabeen zerrendako edit ekintza-botoia ez zetorren bat admin oharren taulako estiloarekin.
- Spec jarraipen-desorekak (1): Zuzenketak 4 item bat irekita geratzen zen, aldaketa praktikan osatu arren.

## Arriskua murriztua

- Ekintza-botoien estilo bateratuak admin taulen erabilgarritasuna eta patroien ikasketa-kostua hobetzen ditu.
- Speceko egoera eguneratzeak false pending egoerak saihesten ditu eta entregaren trazabilitatea hobetzen du.

## Erregela orokorra

- Admin taulen ekintza nagusietan (edit/delete), mantendu ikono-botoi biribil eta hover semantika bera, batez ere `notice-manager` eredua dagoenean.
- Spec checklistak eguneratu berehala aldaketa benetan amaitutakoan, kodea eta dokumentua deslerrokatzea saihesteko.

## Zenbatean frogatua

- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php` ✅ (11 passed)
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality` ❌ (aurretik dauden lint/style arazoak, scope honetatik kanpo)
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact` ❌ (aurretik dagoen auth test failure)
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} -e APP_ENV=testing -e APP_URL=http://selenium-app:8000 -e DUSK_DRIVER_URL=http://selenium:4444/wd/hub madaia33 php artisan test --compact tests/Browser/AdminSensitiveViewsTest.php` ❌ (sqlite `users` taula prestatu gabe)

---

## Update — 006-votings uncommitted review (2026-04-10)

### Arazo-motak aurkituta

- Datu-osotasun kritikoa (1): `voting_selections` taulan muga ez zen nahikoa boto-paper bakoitzean aukera bakarra bermatzeko.
- Errendimendu arazoak (2): admin errolda kalkuluan N+1 eredua eta delegazio-pending kalkuluan iterazio gurutzatu astuna.
- Style/konbentzio arazoak (1): return type eta amaierako newline txikiak.

### Arriskua murriztua

- DB muga zorrotzagoak auditagarritasuna blindatzen du: boto-paper bakoitzak aukera bakarra izan dezake.
- Census/delegazio kalkulu berriek query kopurua eta latentzia murrizten dituzte karga handian.
- Tipatze eta estilo koherentziak mantentzeak etorkizuneko regressioak murrizten ditu.

### Erregela orokorra

- Bozketetan, negozio-araua kritikoa bada (adib. boto-paper bakoitzean aukera bakarra), aplikazio-balidazioa ez da nahikoa: DB mailako unique muga ere derrigorrezkoa da.
- Livewire admin zerrendetan, orriko erregistro bakoitzeko count/query patroia saihestu; kalkulu agregatuak edo mapaketa bakarreko datu-egiturak erabili.

### Zenbatean frogatua

- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact` ✅ (400 passed)
- `docker compose up -d db selenium mailhog && docker compose run --rm --name dusk-app --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 ... php artisan test --compact tests/Browser` ✅ (37 passed)
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality` ❌ (repo osoko lint/style arazo aurrekoak eta `safe.directory` abisua)
