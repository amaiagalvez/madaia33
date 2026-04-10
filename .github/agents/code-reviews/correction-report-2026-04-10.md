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
