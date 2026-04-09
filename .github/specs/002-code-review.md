# 002 — Code Review: Proiektu osoa

**Data**: 2026-04-09
**Esparrua**: proiektu osoa (fokua: `app/` eta `resources/views/`)

## Aurkitutako arazoak

### 🔴 Kritikoak

- [x] Blade barruan query/business logika (`⚡public-notices`) — `resources/views/components/⚡public-notices.blade.php`

### 🟡 Garrantzitsuak

- [x] Public notices-en inplementazio bikoitza (class-based + view-based) — `app/Livewire/PublicNotices.php`, `resources/views/components/⚡public-notices.blade.php`
- [x] Admin inbox eta notice manager karga osoa paginaziorik gabe — `app/Livewire/AdminMessageInbox.php`, `app/Livewire/AdminNoticeManager.php`
- [x] ContactForm-en settings irakurketa sakabanatua — `app/Livewire/ContactForm.php`
- [x] Mail from eraikuntza errepikatua — `app/Mail/AbstractContactMail.php`, `app/Mail/TestEmail.php`

### 🟢 Txikiak

- [ ] Naming/irakurgarritasun hobekuntzak inbox metodoetan — `app/Livewire/AdminMessageInbox.php`

## Aldaketen egoera

- [x] 1. Public notices inplementazio bakarra utzi eta Clean Blade araua bete
- [x] 2. Admin inbox-ean paginazioa gehitu
- [x] 3. Admin notice manager-en paginazioa gehitu
- [x] 4. ContactForm settings karga bateratu/memoizatu
- [x] 5. Mail from eraikuntza DRY egin

## Emaitzak

- [-] `composer quality` exekutatzeke
- [-] `php artisan test --compact` exekutatzeke
- [-] Dusk testak exekutatzeke (`dusk-testing` skill-a jarraitu)

Oharra: egiaztapen komando hauek exekutatu behar dira Docker barruan workflow ofizialarekin:
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`
- `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact`
- Dusk: `.github/skills/dusk-testing/SKILL.md` fitxategiko Selenium + `dusk-app` workflow-a jarraitu
