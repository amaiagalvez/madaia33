---

## 023-owner-whatsapp-eta-invalid-oharra — Implementation Plan

### Goal

- Telefono/email invalid denean testu gorria erakutsi owner formularioen azpian (admin + front).
- `coprop1_has_whatsapp` / `coprop2_has_whatsapp` eremua owners taulan, admin eta front formetan erakutsi; WhatsApp bidalketetan soilik `has_whatsapp = true` dutenen hartzaileak gehitu.

### Technical Decisions

- `owner-shared-fields.blade.php` shared component-ean props berrien bidez kudeatuko dira invalid flagak wire modean.
- `coprop1_has_whatsapp` + `coprop2_has_whatsapp` bi zutabe bereiz (coprop bakoitzak berea).
- Front profile `updateOwner` ere eguneratu `has_whatsapp` gordetzeko.
- `RecipientResolver::resolveOwnerContactsForChannel()` filtroa `whatsapp` kanalean.

### Execution Steps

- [ ] 1. Migrazioa sortu: `coprop1_has_whatsapp`, `coprop2_has_whatsapp` (boolean, default false) `owners` taulan
- [ ] 2. Owner model eguneratu: `$fillable`, `$attributes`, `$casts`; OwnerFactory eguneratu
- [ ] 3. Translations gehitu: `lang/eu/admin.php` eta `lang/es/admin.php` — `has_whatsapp` eta invalid warning keys
- [ ] 4. `owner-shared-fields.blade.php` eguneratu: invalid warning (wire + http), has_whatsapp checkbox (wire + http)
- [ ] 5. `Owners.php` Livewire eguneratu: `editCoprop*Invalid` props (display-only), `editCoprop*HasWhatsapp` props (r/w); `openEditOwnerForm()` eta `saveEditOwner()` eguneratu
- [ ] 6. Admin create form (`index.blade.php`) eguneratu: has_whatsapp checkboxes gehitu; `coprop*HasWhatsapp` props eta `InteractsWithAdminOwners` concern eguneratu; `CreateOwnerAction`-era pasatu
- [ ] 7. `ProfileController::updateOwner()` eguneratu: `coprop*_has_whatsapp` gorde; `OwnerFormValidation` eguneratu
- [ ] 8. `RecipientResolver` eguneratu: `whatsapp` kanalean `has_whatsapp = false` bada, kontaktua `null` itzuli
- [ ] 9. Unit testa: `RecipientResolver` — `has_whatsapp = false` duten jabeak WhatsApp recipients-etik kanpo
- [ ] 10. Feature testa: admin edit owner saves `has_whatsapp`; profile updateOwner saves `has_whatsapp`
- [ ] 11. Quality gate: `composer quality` Docker-ean + Pint

### Work Items

- [ ] `database/migrations/2026_04_17_*_add_has_whatsapp_to_owners_table.php` (berria)
- [ ] `app/Models/Owner.php`
- [ ] `database/factories/OwnerFactory.php`
- [ ] `lang/eu/admin.php`, `lang/es/admin.php`
- [ ] `resources/views/components/admin/owner-shared-fields.blade.php`
- [ ] `resources/views/livewire/admin/owners/index.blade.php`
- [ ] `app/Livewire/Admin/Owners.php`
- [ ] `app/Concerns/InteractsWithAdminOwners.php`
- [ ] `app/Actions/Owners/CreateOwnerAction.php`
- [ ] `app/Http/Controllers/ProfileController.php`
- [ ] `app/Validations/OwnerFormValidation.php`
- [ ] `app/Services/Messaging/RecipientResolver.php`
- [ ] `tests/Unit/RecipientResolverWhatsappFilterTest.php` (berria)
- [ ] `tests/Feature/AdminOwnerHasWhatsappTest.php` (berria)

### Validation

- [ ] Unit tests pass (RecipientResolver whatsapp filter)
- [ ] Feature tests pass (admin edit + profile update)
- [ ] `vendor/bin/pint --dirty`
- [ ] `composer quality` exitcode 0

---