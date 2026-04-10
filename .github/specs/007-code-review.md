# 007 — Code Review: 006-votings uncommitted

**Data**: 2026-04-10
**Esparrua**: app/, database/, lang/, resources/views/, routes/, tests/

## Aurkitutako arazoak

### 🔴 Kritikoak

- [x] Bozka bakoitzak aukera bakarra DB mailan bermatu gabe — `database/migrations/2026_04_10_140000_create_votings_tables.php`

### 🟡 Garrantzitsuak

- [x] Admin census kalkuluan N+1 patroia — `app/Livewire/Admin/Votings.php`
- [x] Delegazio pending kalkulua eskalagarritasun-arriskuarekin — `app/Support/VotingEligibilityService.php`
- [x] 4 espazioko indentazio araua hautsia fitxategi berrietan — `app/**`, `lang/**`

### 🟢 Txikiak

- [x] Return type falta `startDelegatedVote` metodoan — `app/Livewire/Admin/Votings.php`
- [x] Amaierako newline falta spec fitxategian — `.github/specs/006-votings.md`

## Aldaketen egoera

- [x] 1. DB muga zuzendu bozka bakoitzeko aukera bakarra bermatzeko
- [x] 2. Admin census kalkulua optimizatu query bakarrera
- [x] 3. Delegazio pending kalkulua optimizatu
- [x] 4. Indentazioa normalizatu 4 espaziorekin
- [x] 5. Return type eta style zuzenketak

## Emaitzak

- [ ] `composer quality` pasatu (repo osoan aurretik zeuden lint/style arazoengatik huts egin du, scope honetatik kanpo ere bai)
- [x] `php artisan test --compact` pasatu
- [x] Dusk testak pasatu (`dusk-testing` skill-a jarraitu)
