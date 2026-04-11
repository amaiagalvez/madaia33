- [x] Contact: subtitulua testua txukundu. cambia el subtitulo del formulario de contacto del front, algo asi como Envia tu aportación, consulta, duda... te responderemos lo antes posible o algo así, como a ti mejor te parezca
- [x] Slider del front menos alto, es importante que si hay anuncios se vean lo más rápido posible
- [x] settings añadir nuevo para guardar imagen para el logo en el section "front" (sustituir donde se usa madaia33.png por este campo)
- [x] en settings, en la section "email_configuration" el botón "enviar prueba" solo se debe activar cuando no haya cambios sin guardar, al modificar algo descativarlo
- [x] text areas en los formularios, al editar, que se adapte al contenido automaticamente para que loos forrmularios sean más pequeños
- [x] Aginte-paneela Mezuak zerrenda: añadir un campo de texto cmoo buscador que busque en cualquier campo de la tabla menssajes
- [x] en settings, en la sección "contac_form" añadir dos nuevos para el asunto del email (eu y es). Al enviar el mensaje cuando se rellena el formulario, tiene que usar lo que hay en estos campos. El idioma que se usará para enviarr el email será el idioma actual
- [x] settings nuevo registro en "front" para guardar el texto de argazkiak eskatzeko en section front, reemplazarlo
- [x] settings nuevo registro para guardar el  email principal en section front Reemplazar por este todo los info@madaia33.eus
- [x] settigns nuevo registro para guardar nombre web en section front. Reemplazar donde se utilice APP_NAME por este nuevo campo
- [x] añadir al menu un enlace debajo del menu aginte-panela para ir a la web publica
- [x] traducciones en la tabla Erabiltzaileak
- [x] mover le menú Erabiltzaileak al apartado configución del menu y ponerle icono de users
- [x] en la pantalla de los settings al cambiar de tab de section, si no se han guardado los cambios avisar

## Inplementazio plana

### Helburua

- [x] Front/admin UX hobekuntza txikiak bateratuta ezarri, konfigurazio dinamiko berriak gehitu, eta UI/portaera koherentzia mantendu regressiorik gabe.

### Erabaki teknikoak

- [x] Settings gako berriak `settings` taulan kudeatu, eta dagoen eredua berrerabili (`whereIn`/`pluck` + batch write) query kopurua minimizatzeko.
- [x] Front testu/irudi ordezkapenak hardcodeatik settings-era migratu, Blade-n DB kontsultarik egin gabe (datuak aurrez prestatuta).
- [x] Admin formularioetarako estilo bateratua osagai partekatu baten bidez egin (DRY), eta create/edit pantailen drift bisuala saihestu.
- [x] Livewire interakzioetan (dirty-state, botoi desaktibazioa, tab warning, bilaketa/filtroak) egoera-iturri bakarra mantendu.
- [x] Test estrategia minimala baina nahikoa: logika purua Unit-en, fluxu/integrazioa Feature/Livewire-n.

### Exekuzio urratsak

- [x] 1. Scope-a zatitu eta ordenatu (settings berriak + ordezkapen globalak + admin UX + front UX + estatistikak).
- [x] 2. Front slider altuera eta kontaktu azpititulua eguneratu, i18n gakoekin.
- [x] 3. Front section-eko settings berriak gehitu: logo irudia, argazkiak eskatzeko testua, email nagusia, web izena, cookie testu legala.
- [x] 4. Route/public orri berria sortu cookie testu legalerako, footerrean estekatu, eta i18n osatu.
- [x] 5. `info@madaia33.eus` eta `APP_NAME` erabilera puntualak settings berriekin ordezkatu.
- [x] 6. Contact form settings-era email subject eu/es gehitu, eta bidalketan locale aktiboko subject-a aplikatu.
- [x] 7. Settings `email_configuration` section-ean “enviar prueba” botoiaren aktibazio araua ezarri (dirty dagoenean desaktibatuta).
- [x] 8. Settings section-tab aldaketan unsaved-change abisua gehitu.
- [x] 9. Formularioetako textarea autosize portaera ezarri create/edit fluxuetan.
- [x] 10. Admin Mezuak: testu-bilatzaile globala + irakurri/ez-irakurri filtro botoiak (default: irakurriak).
- [x] 11. Admin menuan esteka berria gehitu web publikora, eta `Erabiltzaileak` sarrera Konfiguraziora mugitu (users ikonoarekin) + itzulpenak osatu.
- [x] 12. Aginte-paneleko falta diren estatistikak gehitu query agregatu eraginkorrekin.
- [x] 13. Formulario estilo bateratua osagai partekatura eraman (create/edit denetan).
- [x] 14. Testak eta balidazioa: Unit + Feature/Livewire minimoak, Pint/quality, eta ukitutako fitxategien Problems egoera.

### Egin beharreko lanak

- [x] Settings konfigurazio-gako berriak definitu eta hasieratu (migration/seed edo dagoen mekanismoarekin bateragarri).
- [x] Front ikuspegiak egokitu settings balio berriak kontsumitzeko.
- [x] Contact mail eraikuntza egokitu locale bidezko subject dinamikoarekin.
- [x] Admin Mezuak osagaia (query + UI) bilaketa/filtro berriekin eguneratu.
- [x] Admin menu eta itzulpen fitxategiak egokitu.
- [x] Formulario base osagaia identifikatu/sortu eta create/edit pantailak migratu.
- [x] Estatistika txartelak osatu panel nagusian.
- [x] Cookie orri berria eta ruta berria gehitu.

### Arriskuak eta anbiguotasunak

- [ ] “falta diren estatistikak” zehatz-mehatz definitu behar da (zein taula/metrika).
- [ ] “formularios guztiak” scope handia da: lehenetsi beharreko pantailak adostu behar dira lehen iterazioan.
- [ ] Cookie testu legalaren edukia/itzulpenak falta badira, fallback estrategia adostu.
- [ ] “leidos/no leidos” default baldintza baieztatu: sarrerako egoera irakurriak soilik.

### Balidazioa

- [x] TDD bidezko inplementazioa, ahal denean.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality` (blokeatuta: aurretik zeuden PHPStan erroreak)
- [x] Dagokion test multzoa (`php artisan test --compact` fitxategi/filter zehatzekin, Docker barruan).
- [ ] Front/admin flow ukituetan Dusk test egokiak, beharrezkoa denean.


# Moldaketak
- [x] en settings / logo del front se tiene que poder subir una imagen y es la que se mostrará.
- [x] Separar las rutas, un fichero para las públicas y otro para las privadad
- [x] dividir la carpeta Actions por features o modelos, lo que creas más conveniente
- [ ] traducir todos los test al ingles, si hay explicaciones de código que sean en euskera
- [ ] Ordena los ficheros dentro de la carpeta Livewire en subcarpetas Admin y Front
- [x] cachear los settings para que reducir el número de consultas a la base de datos, cuando se modifica algun setting, borrar la cache y volver a crearla
- [x] En el panel Mezuak por defecto se tienen que mostrar los mensajes no leidos

## Inplementazio plana (Moldaketak)

### Helburua

- [x] `Moldaketak` ataleko hobekuntza funtzionalak eta egitura-refaktore selektiboak modu seguruan ezarri: front logorako upload errealarekin, route publiko/pribatu banaketarekin, `Actions` karpetaren antolaketarekin, settings cache bateratuarekin, eta mezuen panelaren default egoera zuzendurik.

### Erabaki teknikoak

- [x] Front logorako dagoen `front_logo_image_path` gakoa berrerabili, baina string input soila ordezkatu Livewire fitxategi-upload bidez; fitxategia `public` diskoan gorde eta balioan path erlatiboa persistitu.
- [x] Route banaketa `routes/web.php` guztiz ordezkatu gabe egin: fitxategi nagusiak bootstrap sarrera izaten jarraituko du, baina barrutik `routes/public.php` eta `routes/private.php` kargatuko ditu, `routes/settings.php` bereizita mantenduz edo private multzoan integratuz, bikoiztasunik gabe.
- [x] Settings cachea `Setting` ereduaren mailan zentralizatu behar da (`stringValue`, `stringValues`, `localizedString`), ez composer bakoitzean cache independenteak sortuz; invalidazioa settings idazketaren puntuan egingo da.
- [x] `Actions` karpeta domeinuka berrantolatuko da (adibidez, `Owners`, `Votings`, `Fortify`), baina `Livewire` karpetaren egungo antolaketa ez da ukituko, alias/mount erresoluzioan arriskurik ez sartzeko.
- [x] Test guztiak ingelesera migratzea plan honetatik kanpo geratzen da, gero aparteko urrats mekaniko gisa egiteko.
- [x] Admin Mezuen inboxean default filtroa `unread` izango da, eta bilaketa/markaketa portaerak horrekin koherente mantenduko dira.

### Exekuzio urratsak

- [x] 1. Settings cachearen diseinua ixtea eta gaur egun `Setting` kontsumitzen duten puntu guztiak identifikatzea (`AppServiceProvider`, composer-ak, kontrolagailuak, Livewire osagaiak).
- [x] 2. Front logorako upload fluxua prestatzea: balidazioa, preview/oraingo balioaren bistaratzea, persistitutako path-a eta front/admin logo kontsumitzaileen egokitzapena.
- [x] 3. Route egitura zatitzea `public` eta `private` fitxategietan, admin + auth/settings + voting endpoint babestuak private multzoan kontuan hartuta, baina `routes/settings.php` bere tokian utzita.
- [x] 4. `Actions` karpeta domeinuka antolatzea eta namespace/import guztiak eguneratzea.
- [x] 5. `AdminMessageInbox` default iragazkia `unread` gisa doitzea eta dagokion UI/test estaldura eguneratzea.
- [x] 6. Pint, ukitutako test minimoak, eta beharrezko kasuetan Dusk/Feature egiaztapenak exekutatzea.

### Egin beharreko lanak

- [x] `app/Models/Setting.php` cache-aware bihurtzea eta invalidazio API txiki bat ematea.
- [x] `app/Livewire/AdminSettings.php` egokitzea save ondoko cache flush/rebuild logikarekin eta front logo upload egoerarekin.
- [x] Settings front tab-eko Blade zatia fitxategi-upload osagaiarekin ordezkatzea.
- [x] `app/Http/Composers/BrandingSettingsComposer.php`, `app/Providers/AppServiceProvider.php`, kontrolagailu publikoak eta lotutako Livewire osagaiak settings cache zentralizatura eramatea.
- [x] Route fitxategi berriak sortzea eta `routes/web.php`-tik kargatzea.
- [x] `app/Actions/**` namespace berrietara mugitzea, erreferentzia guztiak eguneratuta.
- [x] `app/Livewire/AdminMessageInbox.php` eta haren test/UI lotuak eguneratzea.

### Arriskuak eta anbiguotasunak

- [x] Front logorako upload-ean aurreko fitxategia ez da automatikoki ezabatuko; beraz, inplementazioak overwrite-rik gabe persistitu beharko du path berria, storage historikoa ukitu gabe.
- [x] Route “pribatuak” plan honetan admin + auth/settings + voting endpoint babestuak dira; `routes/settings.php` bere horretan utziko da.
- [x] Test guztiak ingelesera migratzea plan honetatik kanpo uzten da, gero aparteko urrats mekaniko gisa egiteko.
- [x] `Livewire` fitxategien antolaketa ez da ukituko plan honetan, alias/mount erresoluzio arriskuak saihesteko.
- [x] Settings cache berriak boot garaiko mail konfigurazioa eta view composer erabilerak sinkronizatuta mantentzen dira request berrietan; focused test eta integratutako settings kontsumitzaileek ez dute stale baliorik erakutsi.

### Balidazioa

- [x] TDD ahal den neurrian: Unit testak cache/setting helper logikarako eta Feature/Livewire testak upload, route eta inbox aldaketetarako.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] Ukitutako test multzo minimoa Docker barruan (`php artisan test --compact` fitxategi/filter zehatzekin).
- [ ] Route banaketak frontend edo auth flow-ak ukitzen baditu, dagokion Browser/Dusk egiaztapena exekutatzea.