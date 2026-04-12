# 004 — Code Review: commit gabe sortutako aldaketak

**Data**: 2026-04-09
**Esparrua**: app/, database/, resources/views/, routes/, tests/

## Aurkitutako arazoak

### 🔴 Kritikoak

- [x] `is_active` ez da autentikazioan aplikatzen — `app/Providers/FortifyServiceProvider.php`
- [x] Esleipen aktibo bikoitza race-condition bidez sor daiteke — `app/Actions/AssignPropertyAction.php`, `database/migrations/*property_assignments*`
- [x] Audit log-ek `changed_by_user_id` nullable ez denez huts egin dezake — `app/Observers/OwnerAuditObserver.php`, `database/migrations/*owner_audit_logs*`
- [x] Owner/User sorrera ez da transakzionala — `app/Actions/CreateOwnerAction.php`

### 🟡 Garrantzitsuak

- [x] `typeForCode`-k kode ezezagunak storage gisa markatzen ditu — `app/CommunityLocations.php`
- [x] `end_date` eta `start_date` kronologia ez da balidatzen — `app/Actions/UnassignPropertyAction.php`
- [x] Admin UI testu berrien i18n estaldura hobetzea — `resources/views/**`, `lang/eu/**`, `lang/es/**`
- [x] Dusk test menpekotasun gogorra seed-eko admin erabiltzaileari — `tests/Browser/AdminSensitiveViewsTest.php`
- [x] Livewire osagaian action instantziazio zuzena — `app/Livewire/Admin/OwnerDetail.php`

### 🟢 Txikiak

- [x] Kode-estilo koherentzia (indentazioa/formatua) — ukitutako fitxategiak

## Aldaketen egoera

- [x] 1. Fortify login egiaztapena `is_active` baldintzarekin gehitu + testak
- [x] 2. Esleipen aktibo bakarra transakzio + DB murriztapenarekin blindatu
- [x] 3. Owner audit log nullable/fallback segurua ezarri
- [x] 4. Owner sortzea transakzioan bildu
- [x] 5. Desasignazio data balidazio kronologikoa gehitu
- [x] 6. `CommunityLocations::typeForCode` ezezagunetarako portaera esplizitua
- [x] 7. i18n giltzak osatu admin UI berrian (eu/es)
- [x] 8. Dusk test egonkortasuna hobetu (factory admin)
- [x] 9. Livewire action-en DI aplikatu

## Emaitzak

- [ ] `composer quality` pasatu
- [x] `php artisan test --compact` (ukitutako testak + osorik)
- [x] Dusk testak pasatu (`tests/Browser/AdminSensitiveViewsTest.php`)

Oharra: `composer quality` geratzen da exekutatzeko.
