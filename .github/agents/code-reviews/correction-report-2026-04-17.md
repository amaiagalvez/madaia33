# Correction Report — 2026-04-17

## Arazo-motak aurkituta

- Test-arkitektura desoreka (2): DB gabeko render-only egiaztapenak `tests/Feature` barruan zeuden.
- Egiaztapen-ingurune arazoak (2): quality gate-k aurreko style arazoak ditu scope honetatik kanpo; Browser/Dusk suite-ak workflow berezia behar du eta run arruntak huts egiten du.

## Arriskua murriztua

- Feature suitearen kostu operatiboa murriztu da render-only testak Unit-era mugituta.
- HTTP integrazio-probak bereizita mantendu dira eta view-only egiaztapenak isolatu dira; horrek intentzioa argitzen du eta mantentzea errazten du.

## Erregela orokorra

- `tests/Feature` fitxategi batek Blade render hutsa bakarrik badu, lehenetsi `tests/Unit`era migrazioa eta saihestu persistitutako factory `create()` deialdiak.
- Browser testak ez exekutatu `php artisan test tests/Browser` run arruntean; erabili beti Selenium + `migrate:fresh --seed` + in-container `serve` workflowa.

## Zenbatean frogatua

- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Unit/NoticeCardComponentTest.php tests/Unit/PrivatePageAccessibilityViewTest.php tests/Feature/SecondaryPagesResponsiveTest.php` ✅ (15 passed)
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact` ✅ (677 passed)
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality` ❌ (5 pre-existing style issues, scope honetatik kanpo)
- Dusk Selenium workflowa (`dusk-app`, `APP_URL=http://dusk-app:8000`, `DUSK_DRIVER_URL=http://selenium:4444/wd/hub`) ❌ (ez da passing amaierara iritsi saio honetan)
