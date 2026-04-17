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

# nohizbehinka egiteko 2026-04-11
- [ ] Repasa todos los feature test y los que se puedan convertir en unit test sin acceso a la base de datos pasalos
- [ ] Repasa la suit de test y completa los que falten
- [ ] con el agente sorgina repasar el código y proponer mejoras (código repetido, test que faltan, consultas a la base de datos repetidas, ...)
- [ ] con chatgpt pedirle que haga una auditoría de accesibilidad y pasarsela @lamia

- [ ] sudo find /home/amaia/Dokumentuak/madaia33 -user root -exec chown amaia:amaia {} +
- [ ] bash scripts/enforce-indent-4.sh 
- [ ] docker => reformat eta quality
- [ ] docker => pentest
- [ ] dusk-test

# Despues de publicar master
- [ ] configurar el sentry
- [ ] añalizar cookiena chatgpt-rekin
- [ ] probar envio whatcapp en el movil

# Code
- votaciones resultados (suma de porcentajes)
- spec kiro pendientes (obra)
- [ ] repasar estatistikak home
- [ ] unificar terminología
  - locations: Ubicaciones => Comunidades
  - properties: Propiedades => Fincas
  - property_assignments => Propiedades
  - owners => Propietarias

# Panela
- [ ] controlar mensaje welcome al crear un nuevo owner o al pinchar en el botón reenviar del listado de owners del panel. Si el owner no tiene email, no enviar mensaje de bienvenida
- [ ] en el listado admin/bidalketak/1, cuando campaing_id = 1, añadir encima del email el asunto (message_subject)
- Añadir otro canal para las bidalketak. "Eskuz". Al elegir este canal, como recipients se añadiran los coop1 que no tengan, ni telefono, ni email, o si tiene telefono que no tengan whatchapp ni email

- [ ] Estatutos de la comunidad y de cada portal o planta de garaje. Permisos, quien ve qué
- Aktak
- Deialdiak sartzeko formularioa + pdf + emailez bidali + iragarkia sortu

- [ ] Mezuak. Al abrir el mensaje, añade un botón para responderle. Guarda la respuesta en la base de datos y enviale el email. Añade una nueva columna en la taula que indique con iconos si está repondido o no.

- [ ] Iragarkiak. Gehitu hasiera data eta bukaera data eremuak, gehitu zutabea zerrendan eta front-ean kontrolatu eta bakarrik erakutsi indarrean daudenak (fecha fin sin pasar o null)

- [ ] Añadir espacio Obra (info, formulario, doocumentacion)

# Copilot
- [ ] repasar agente lamia
- [ ] agente para crear manuales de usuario
- [ ] agente auditor de ENS e 27001
- [ ] cómo le digo al agente "amalurra" que primero siempre haga caso a lo que hay en el agent.md y luego con lo suyo propio?

# Code Refactor && Hobekuntzak
- [ ] crear un componente, si todavía no lo hay, para que todas los listados tablas tengan la missma estructura
- [ ] el formato de todos los formularios tanto de crear como editar tienen que tener el mismo aspecto que el de crear un nuevo anuncio, crear un componente si no lo hay
- [ ] Ordena los ficheros dentro de la carpeta Livewire en subcarpetas Admin y Front
- [ ] componente para inputs del formulario
- [ ] datatables
- [ ] añadir el MCP para que consulte la documentación de laravel
- [ ] traducciones repetidas
- [ ] seo
- [ ] twilio https://www.twilio.com/docs/whatsapp
- [ ] en el skill db-schema separar las tablas de las votoaciones en otro bloque
- [ ] añadir papelera para poder reestaurar los borrrados
- [ ] añadir tipo votaciones, una persona un voto / por porcentajes de participación
- [ ] en todos los sitios en lo que debe salir un modal, crear un dusk-test para que lo compruebe y así no tener riesgo de perder esa funcionalidad.
- [ ] repasar queries duplicadas que indica la debugbar. examinar la pestaña Queries del debugbar y analizar las consultas duplicadas en todas las rutas
- [ ] ante una auditoría, cómo le explico al auditor/a la calidad de las votaciones?
- [ ] config recaptcha, analitics
- [ ] manual de usuaria
crar una miniweb en html con las instrucciones para usar la aplicación, añade texto y pantallazos para que los usuarios que se logueen tengan claro cómo usar la aplicación añadir una ruta al menú del panel tiene que estar en dos idiomas eu y es
añadir una regla al agente amalur para que lo mantenga actualizado


6-B Jose Meléndez         630856088 jose.melendez.amado@gmail.com
3-B Juanjo Ortega López   659054060 ortegalopez33@gmail.com 
1-B Jon Urbizu Etxabarri  685757583 
