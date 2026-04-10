## tengo varias locations: portales, plantas de garajes y trasteros
  los portales son 33-A, 33-B, ... 33-J
  los garajes son P-1, P-2 y P-3
  los trasteros son A, B, ... J

## portales
  dentro de cada portal hay varias propiedades 1A, 1B, .... 6C
  cada portal tiene las suyas
  cada propiedad tiene un porcentaje de participación por portal y otro por comunidad

## plantas de garajes
  dentro de cada planta de garajes hay varias propiedades 1, 2, 3, ... 190
  cada garaje tiene las suyas
  cada propiedad tiene un porcentaje de participación por planta de garaje y otro por comunidad

## trasteros
  dentro de cada trastero hay varias propiedades 1, 2, 3, ... 190
  cada trastero tiene las suyas
  los trasteros no tiene porcentaje de participación

## propietarias/os
  hay personas que son propietarias de una o varias propiedades
  una persona puede tener en el portal 33-A el 1A, en el garaje P-1 el 160 y en los trasteros el 30
  una persona puede vender sus propiedes, entonces la nueva persona pasa a ser propietaria, pero no se debe perder el historial, debo saber que la persona1 tubo el 33-A 1-A del 2020-01-01 al 2021-01-31 y que la persona2 tiene ahora el 33-A 1-A desde del 2021-02-01, sin fecha de fin. 
  puede ocurrir que la persona1 siga siendo propietara del P-1 160 auunque ya no lo sea del 33-A 1-A
  Las que no tengan fecha de fin son las propietarias actuales

  cada propietario tiene sus datos: nombre completo, dni, telefono, email para guardar datos de dos personas (coopropietaria1 y coopropietaria2)

  al dar de alta a una nueva propietaria, nos debe dar la opción de añadirle las propiedades de los portales, garajes y trasteros que sean necesarios.
  
  para poder reasignar una propiedad, hay que validar que no la tiene nadie, mientras haya una propietaria que tenga el 33-I 1-A, no se la podremos asignar a nadie mas
  
  las asignaciones de las propieedades, a parte de fecha inicio y fecha fin necesitan dos campos de validación, uno para el admin y otro para la propietaria 
  no puede haber dos asignaciones a una misma propiedad sin fehca de fin, para poder asignar una nueva propietaria, todas lass asignaciones anteriores tienen que tener fecha fin.

  hay que dar la opción de desasignar una propiedad añadiendole una fecha de fin, no borrar, para no perder el historial

  al crear una nueva propietaria hay que asignarle un nuevo user, con username = dni y password aleatorio, se le enviará por email para que confirme sus datos.

  al dar de baja a una propietaria, el user se desactivara (active=0) para que no tenga acceso al panel.

## listados que necesito con sus correspondientes CRUD
1. portales
    al entrar dentro de la ficha de un portal me aparecerá un formulario con su nombre y una lista con las propiedades
    en la lista de sus propiedades aparecerá el nombre (editable), porcentaje parcipación comunidad (editable), porcentaje participación portal(editable), asignado (si - cuando tenga alguna propietaria sin fecha fin/no - cuando no tenga propietaria o todas las propietarias que tenga tengan fecha fin), valdidado admin (si - asignado es si y la asignacion tiene validado admin a 1/no - en caso contrairo), valdidado propietaria (si - asignado es si y la asignacion tiene validado propietaria a 1/no - en caso contrairo)
    en la lsita de sus propiedades no tienen que aparecer los datos de las propietarias.
    tengo que tener la opción de añadir una nueva propiedad al portal.
2. plantas de garajes
   al entrar dentro de la ficha de una planta de garaje me aparecerá un formulario con su nombre y una lista con las propiedades
    en la lista de sus propiedades aparecerá el nombre (editable), porcentaje parcipación comunidad (editable), porcentaje participación garaje(editable), asignado (si - cuando tenga alguna propietaria sin fecha fin/no - cuando no tenga propietaria o todas las propietarias que tenga tengan fecha fin), valdidado admin (si - asignado es si y la asignacion tiene validado admin a 1/no - en caso contrairo), valdidado propietaria (si - asignado es si y la asignacion tiene validado propietaria a 1/no - en caso contrairo)
    en la lsita de sus propiedades no tienen que aparecer los datos de las propietarias.
    tengo que tener la opción de añadir una nueva propiedad al portal.
3. trasteros
    al entrar dentro de la ficha de una planta de garaje me aparecerá un formulario con su nombre y una lista con las propiedades
    en la lista de sus propiedades aparecerá el nombre (editable), asignado (si - cuando tenga alguna propietaria sin fecha fin/no - cuando no tenga propietaria o todas las propietarias que tenga tengan fecha fin), valdidado admin (si - asignado es si y la asignacion tiene validado admin a 1/no - en caso contrairo), valdidado propietaria (si - asignado es si y la asignacion tiene validado propietaria a 1/no - en caso contrairo)
    en la lsita de sus propiedades no tienen que aparecer los datos de las propietarias.
    tengo que tener la opción de añadir una nueva propiedad al portal.
4. propietarias
   por defecto se cargará el listado de propietarias actuales, todas aquellas que tengan una asignación sin fecha fin
   columnas de la tabla nombre1 | nombre2 | portales | garajes | trasteros
    en la columnna portales se mostrará las asignaciones que tiene esa propietaria en las propiedades de los portales (ejemplo: 33I 1A / 33J 6B), en rojo si ya no estan (fecha fin rellenada) y sino en verde 
    en la columnna garajes se mostrará las asignaciones que tiene esa propietaria en las propiedades de los garajes (ejemplo: P-1 160 / P-2 99), en rojo si ya no estan (fecha fin rellenada) y sino en verde
  puede ocurrir que una misma propietara tenga una propiedades cerradas con fecha fin y otras no 
  
    editar propietaria: se tienen que poder editar los datos personales de las dos coopropietarias (solo los datos de la primera son obligatorios, la segunda coopropietaria es opcional)
    se muestra una lista con sus propiedades y fecha incio (editable) y fecha fin (editable)
    hay la opción de añadir nuevas propiedades

    la lista tiene que tener varios filtros con diferentes selec
    - tipo: propietarios en activo / y no en activo
    - portales: listado de portales o todos por defecto
    - garajes: listado de garajes o todos por defecto
    - trasteros: listado de trasteros o todos por defecto

  al editar una propietaria y al modificar una propietaria, se tiene que guardar en una tabla aparte de la base de datos el usuario, la fecha y la hora y lo que se modifico (estado anterior y nuevo estado)

---

## Inplementazio plana

### Helburua

Komunitate-eraikinen (portales, garajes, trasteros) egitura eta jabetza-gestioa inplementatzea: lokalizazioak, propietateak, jabeeen kudeaketa historialarekin, esleipenen kontrolarekin eta erabiltzaile-sortze automatikoarekin.

### Erabakiak (argituta)

- **Login: DNI eta email biak** → `username` = DNI (`name` eremua), `email` presentzia mantendu. Fortify-rekin biak onartzeko `username` override bat egin daiteke (`FortifyServiceProvider`).
- **User baja**: `is_active = false` flag — SoftDelete ez, flag esplizitua.
- **Trasteroek**: ez `community_participation_pct` ez `location_participation_pct` — biak null.
- **`CommunityLocations.php`**: trasteros (`A`…`J`) gehitu.

### Erabaki teknikoak

- **SoftDeletes** modelo guztietan (konbentzioa)
- **Esleipenen baja**: `end_date` gehitu, ez ezabatu — historia galdu gabe
- **Owner sortzean**: `User` sortu automatikoki, `name = DNI`, `email = coprop1_email`, password ausazkoa → email bidali
- **Login**: Fortify `username` override → DNI edo email onar ditzake
- **User baja**: `is_active = false` flag; SoftDelete mantentzen da datu-osotasunerako
- **Audit log**: jabearen edozein aldaketak `owner_audit_logs` taulan erregistratu
- **Admin UI**: Livewire komponente bakoitza bere moduluan (`app/Livewire/Admin/`)
- **Trasteros participazioa**: `community_pct` eta `location_pct` null (ez dago)

### Eskema — Datu-basea

```
users (existente)    → + is_active (boolean, default true)

locations            → id, type(portal|garage|storage), code, name, timestamps, deleted_at
properties           → id, location_id, name,
                       community_pct(decimal 8,4, nullable — null trasteroetan),
                       location_pct(decimal 8,4, nullable — null trasteroetan),
                       timestamps, deleted_at
owners               → id, user_id,
                       coprop1_name, coprop1_dni, coprop1_phone, coprop1_email,
                       coprop2_name(null), coprop2_dni(null), coprop2_phone(null), coprop2_email(null),
                       timestamps, deleted_at
property_assignments → id, property_id, owner_id,
                       start_date(date), end_date(date, null),
                       admin_validated(bool, default false),
                       owner_validated(bool, default false),
                       timestamps, deleted_at
owner_audit_logs     → id, owner_id, changed_by_user_id,
                       field, old_value(text), new_value(text),
                       timestamps (created_at = aldaketa data)
```

### Exekuzio urratsak

#### Fase 1 — Datu-geruza (Migrazioak + Modeloak)

- [x] 1. `users` taula: `is_active` boolean eremua gehitu (migrazioa)
- [x] 2. `CommunityLocations.php` eguneratu: trasteros (`A`…`J`) gehitu
- [x] 3. Migrazioa: `locations` taula sortu
- [x] 4. Migrazioa: `properties` taula sortu
- [x] 5. Migrazioa: `owners` taula sortu
- [x] 6. Migrazioa: `property_assignments` taula sortu
- [x] 7. Migrazioa: `owner_audit_logs` taula sortu
- [x] 8. `Location` modeloa + fabrika + seeder (portales/garajes/trasteros hasieratu)
- [x] 9. `Property` modeloa + fabrika
- [x] 10. `Owner` modeloa + fabrika
- [x] 11. `PropertyAssignment` modeloa + fabrika (esklusibitate-baldintza: ezin bi aktibo)
- [x] 12. `OwnerAuditLog` modeloa
- [x] 13. `User` modeloari `is_active` eremua + `owner()` erlazioa gehitu

#### Fase 2 — Logika negozio-geruza

- [x] 14. `AssignPropertyAction` — esleipen berria sortu (baldintza egiaztatu: ez aktibo)
- [x] 15. `UnassignPropertyAction` — `end_date` jarri esleipenari
- [x] 16. `CreateOwnerAction` — jabea sortu + User auto-sortu (DNI/pass ausazkoa) + email bidali
- [x] 17. `DeactivateOwnerAction` — jabea bajan jarri + user `is_active=false`
- [x] 18. `AuditOwnerObserver` — jabea editatzean `owner_audit_logs` automatikoki betetzeko

#### Fase 3 — Admin UI (Livewire)

- [x] 19. Bide berriak `routes/web.php`-n (`admin.portales`, `admin.garajes`, `admin.trasteros`, `admin.propietarias`)
- [x] 20. `Locations` Livewire — lokalizazio-zerrenda (portales/garajes/trasteros motaren arabera)
- [x] 21. `LocationDetail` Livewire — lokalizazioaren fitxa + propietateen zerrenda (editagarria)
- [x] 22. `Owners` Livewire — jabeen zerrenda iragazkiekin (aktibo/ez, portal, garaje, trastero)
- [x] 23. `OwnerDetail` Livewire — jabea editatu + propietateak ikusi/gehitu/bajan jarri
- [x] 24. Blade ikuspegiak: zerrenda eta xehetasun txantiloiak (lamia agentearekin)

#### Fase 4 — Testak

- [x] 25. Unit testak: `AssignPropertyAction`, `UnassignPropertyAction`
- [x] 26. Feature testak: jabea sortzean user sortzen dela + email bidaltzen dela (`CreateOwnerAction`, `DeactivateOwnerAction`)
- [x] 27. Feature testak: esleipen bikoitzaren eragozpena (`AssignmentActionsTest`)
- [x] 28. `OwnerAuditObserver` Feature testak
- [x] 29. Feature testak: Admin UI zerrendak eta iragazkiak (`AdminOwnersAndLocationsTest`)
- [x] 30. Dusk testak: view sentikorrak (`AdminSensitiveViewsTest`)

#### Fase 5 — Kalitate-egiaztapena

- [x] 30. `composer quality` pasa Docker barruan (**saltatuta erabiltzailearen baimenarekin**)
- [x] 31. Pint formateoa: `vendor/bin/pint --dirty` (ondo)
- [x] 32. Test-suite osoa pasa: `php artisan test --compact` (359 passed)

### Egin beharreko fitxategiak

- [x] `database/migrations/2026_04_09_100001_add_is_active_to_users_table.php`
- [x] `database/migrations/2026_04_09_100002_create_locations_table.php`
- [x] `database/migrations/2026_04_09_100003_create_properties_table.php`
- [x] `database/migrations/2026_04_09_100004_create_owners_table.php`
- [x] `database/migrations/2026_04_09_100005_create_property_assignments_table.php`
- [x] `database/migrations/2026_04_09_100006_create_owner_audit_logs_table.php`
- [x] `app/Models/Location.php` + factory + seeder
- [x] `app/Models/Property.php` + factory
- [x] `app/Models/Owner.php` + factory
- [x] `app/Models/PropertyAssignment.php` + factory
- [x] `app/Models/OwnerAuditLog.php`
- [x] `app/Actions/AssignPropertyAction.php`
- [x] `app/Actions/UnassignPropertyAction.php`
- [x] `app/Actions/CreateOwnerAction.php`
- [x] `app/Actions/DeactivateOwnerAction.php`
- [x] `app/Observers/OwnerAuditObserver.php`
- [x] `app/CommunityLocations.php` (eguneratua: trasteros gehituta)
- [x] `tests/Feature/AssignmentActionsTest.php`
- [x] `tests/Feature/OwnerActionsTest.php`
- [x] `tests/Feature/OwnerAuditObserverTest.php`
- [x] `tests/Feature/AdminOwnersAndLocationsTest.php`
- [x] `app/Livewire/Admin/Locations.php`
- [x] `app/Livewire/Admin/LocationDetail.php`
- [x] `app/Livewire/Admin/Owners.php`
- [x] `app/Livewire/Admin/OwnerDetail.php`
- [x] `resources/views/admin/locations/` (index + show)
- [x] `resources/views/admin/owners/` (index + show)
- [x] `resources/views/livewire/admin/owners/detail.blade.php`
- [x] `tests/Browser/AdminSensitiveViewsTest.php`

### Balidazioa

- [x] TDD bidezko inplementazioa (Actions lehenik testatu)
- [x] `vendor/bin/pint --dirty --format agent`
- [x] `composer quality` (phpstan, phpmd) (**saltatuta erabiltzailearen baimenarekin**)
- [x] `php artisan test --compact`
- [ ] Dusk testak ez dira beharrezkoak fase honetan (admin UI berria, ez flow publiko aldatua)

# Zuzenketak
- [x] crear un seeder para cargar las propiedades, por cada portal que genere 1-A, 1-B, 1-C, 2-A, 2-B, 2-C, .... 6-A, 6-B, 6-C, por cada garaje que genere 1, 2...180
- [x] ordernar los menus, crear diferentes apartados. Web: Iragarkiiak, Argazkiak, Mezuak Komunitatea: Kokalekuak, Jabeak Konfigurazioa: Ezarpenak
- [x] crear un compoonente para que todas las tablas del panel tengan el mismo aspecto
hobeto esan => "usa la tabla que hay en /admin/avisos como base para que todas las tablas tengan el mismo estilo, colores, iconos, botones... cambia kokalekuak y jabeak"
- [x] necesito poder crear nuevas propietarias

## Inplementazio plana (Zuzenketak)

### Helburua

- Zuzenketa-pakete bakarrean 4 behar hauek entregatzea: hasierako propietate-seeding automatikoa, admin menuaren antolaketa berria, panel osorako taula-komponente bateratua, eta jabe berriak sortzeko fluxu erabilgarria.

### Erabaki teknikoak

- Seeder berria idempotentea izango da (`upsert`/egiaztapenekin), exekuzio errepikatuan ez dadin datu bikoizketarik sortu.
- Propietateen sorrera `locations.type`-aren arabera egingo da:
  - `portal`: `1-A`..`6-C` (18 erregistro portal bakoitzeko)
  - `garage`: `1`..`180` (180 erregistro garaje bakoitzeko)
  - `storage`: oraingoz ez da automatikoki sortuko, eskaeran ez delako patroia zehaztu.
- Admin menua taldekatuta antolatuko da lehendik dagoen layout-ean, route izenak eta baimen-eredua hautsi gabe.
- Panel-taulen estiloa Blade osagai partekatu batera aterako da DRY bermatzeko.
- Jabe berria sortzeko UIa lehendik dagoen `CreateOwnerAction`-arekin lotuko da; ez da negozio-logika bikoiztuko Livewire osagaian.

### Exekuzio urratsak

- [x] 1. Seeder-aren diseinua eta datu sortze-eredua finkatu (`locations` + `properties`).
- [x] 2. Seeder berria sortu eta `DatabaseSeeder`-ean erregistratu.
- [x] 3. Seeder-aren testak gehitu (zenbaketa + formatu + idempotentzia).
- [x] 4. Admin menuaren egitura berrantolatu: Web / Komunitatea / Konfigurazioa.
- [x] 5. Taula-base osagai bateratua sortu eta gutxienez 2 zerrendatan integratu.
- [x] 6. “Propietaria berria” sortzeko bidea gehitu (zerrendatik formulariora + gordetze-fluxua).
- [x] 7. Balidazioak eta errore-mezuak i18n bidez osatu (`lang/eu` eta `lang/es`).
- [x] 8. Pint + test minimo eraginkorrak exekutatu Docker barruan.

### Egin beharreko lanak (fitxategi-maila, orientagarria)

- [x] `database/seeders/PropertySeeder.php` (berria)
- [x] `database/seeders/DatabaseSeeder.php` (deia gehitu)
- [x] `tests/Feature/...` edo `tests/Unit/...` (seeder testak)
- [x] `resources/views/layouts/...` (admin menuaren antolaketa)
- [x] `resources/views/components/...` (taula-base osagai berria)
- [x] `resources/views/livewire/admin/owners/...` (sortze-formularioa eta zerrenda lotura)
- [x] `app/Livewire/Admin/...` (create flow wiring, behar den neurrian)
- [x] `lang/eu/*.php` eta `lang/es/*.php` (testu berriak)

### Arriskuak eta zalantzak

- `storage` propietateen seeding patroia ez dago esplizituki definituta; baieztatu behar da automatikoki sortu behar diren ala ez.
- “Menua ordenatu” atalak layout zehatza eskatzen du: sidebar bakarra ala goiko nabigazio konbinatua.
- Taula-osagai bateratuan zein mailatan estandarizatu behar den zehaztu behar da (wrapper bakarrik vs. columns API osatua).

### Balidazioa

- [x] `vendor/bin/pint --dirty --format agent`
- [x] `php artisan test --compact --filter=Seeder` (edo dagokion fitxategia)
- [x] `php artisan test --compact --filter=Admin` (ukitutako fluxuen arabera)
- [x] VS Code Problems panelean ukitutako fitxategiak garbi daudela baieztatu

# Zuzenketak 2
- [x] Aldatu eye ikonoa eta jarri bars ikonoa owners zerrendan
- [x] admin/ubicaciones/xx zerrendatan erabili zerrenden konponentea
- [x] admin/ubicaciones/xx headerren azpian gehitu breadcrum-a itzultzeko dagokion zerrendara, atariak, garajeak edo trastelekuak
- [x] jabeak zerrendan behar dut botoi bat ikusteko jabetzarik ez dutenak
- [x] jabe berri bat sortzean, eskatu behar dit sartzeko bere jabetzak (atariak, garajeak eta trasteroak) hasiera eta bukaera datakin, behar diren guztiak sartzeko aukera egon behar da.

## Inplementazio plana (Zuzenketak 2)

### Helburua

- [x] Admin kudeaketan erabilgarritasuna eta koherentzia hobetzea: ikonoak bateratu, kokalekuetako taulak estandarizatu, breadcrumb nabigazioa gehitu, jabetzarik gabeko jabeak ikusgai jarri, eta jabe-sorreran jabetzen esleipen anitza ahalbidetu.

### Erabaki teknikoak

- [x] `owners` zerrendako `bars` ikonoa detail bistara joateko botoi zuzena izango da (ez ekintza-menu baterako).
- [x] `admin/ubicaciones/*` zerrenda guztiak taula-base osagai partekatura migrauko dira, `admin/avisos`-eko estilo bera erabiliz.
- [x] Breadcrumb-a `heading` azpian gehituko da, itzulera esteka testuinguruaren arabera (`atariak`, `garajeak`, `trasteroak`) kalkulatuta.
- [x] Jabeen zerrendan iragazki/txandakatze bat gehituko da: `jabetzarik gabeak` = `property_assignments`-en erregistrorik ez dutenak EDO dituzten esleipen guztiek `end_date` beteta dutenak (esleipen aktiborik ez).
- [x] Jabe berria sortzeko fluxuan, jabetza-esleipen anitz dinamikoa gehituko da (`property_id`, `start_date`, `end_date?`), eta jabetza motak (atariak, garajeak, trastelekuak) denak batera erakutsiko dira hautaketa berean.
- [x] Propietarien zerrendako filtroak lerro bakarrean antolatuko dira (usability), taulan erregistro gehiago ikusgai gera daitezen.
- [x] Propietarien zerrenda paginatua izango da (Livewire pagination), errendimendua eta irakurgarritasuna hobetzeko.

### Exekuzio urratsak

- [x] 1. `owners` zerrendako eye ikonoa ordezkatu `bars` ikonoarekin eta test selektore egonkorra mantendu.
- [x] 2. `admin/ubicaciones/*` zerrenden bistak berrikusi eta taula-base osagai komunera pasatu.
- [x] 3. `admin/ubicaciones/*/show` edo dagokion detail bistetan breadcrumb osagaia txertatu, mota bakoitzeko itzulera-URLarekin.
- [x] 4. `Owners` Livewire osagaian iragazki berria gehitu (`with_properties` / `without_properties`) eta kontsulta optimizatu esleipen aktiboen logikarekin (`whereHas(... whereNull(end_date))` / `whereDoesntHave(... whereNull(end_date))`).
- [x] 5. Propietarien zerrendako filtro-barra lerro bakarrean antolatu (wrap gabe desktop-en), eta mobile-n degradazio kontrolatuarekin mantendu.
- [x] 6. Propietarien zerrendan paginazioa aktibatu eta orri-aldaketan filtro-egoera mantendu.
- [x] 7. `Owner` sortze-formularioan jabetza lerro errepikagarriak gehitu, jabetza mota guztiak batera zerrendatuta (atariak + garajeak + trastelekuak) eta data-eremuekin.
- [x] 8. Balidazioa ezarri: esleipen bakoitzean `property_id` eta `start_date` derrigorrezkoak; `end_date` >= `start_date` bada bakarrik onartu.
- [x] 9. Gordetzean transakzioa erabili: jabea sortu + esleipen guztiak sortu + esklusibitate araua aplikatu (jabetza aktibo bikoitzik ez).
- [x] 10. UI/Feature testak gehitu eta eguneratu, batez ere: iragazkia, filtroen lerro bakarra, paginazioa, breadcrumb-a eta create-flow berria.

### Egin beharreko lanak

- [x] `app/Livewire/Admin/Owners.php`
- [x] `resources/views/livewire/admin/owners/index.blade.php`
- [ ] `resources/views/livewire/admin/owners/create.blade.php` (edo dagokion create ikuspegia)
- [x] `app/Actions/CreateOwnerAction.php`
- [ ] `app/Actions/AssignPropertyAction.php` (beharrezkoa bada lerro anitzeko sortzearekin lerrokatzeko)
- [x] `resources/views/admin/locations/*` eta/edo `resources/views/livewire/admin/locations/*`
- [ ] `resources/views/components/*` (breadcrumb edo taula osagai partekatua, beharrezkoa bada)
- [x] `lang/eu/*.php` eta `lang/es/*.php` (testu berriak)
- [x] `tests/Feature/*Owners*Test.php`
- [x] `tests/Feature/*Locations*Test.php`
- [ ] `tests/Feature/*Owners*Pagination*Test.php` (beharrezkoa bada)

### Arriskuak eta argitu beharrekoak

- [x] “jabetzarik ez dutenak” definizioa itxita: `property_assignments`-en ezer ez dutenak edo esleipen guztiak amaituta (`end_date` beteta) dituztenak.
- [x] Jabe berriaren hasierako esleipenetan jabetza mota guztiak batera erakutsiko dira (ez taldekatuta separatuki).

### Balidazioa

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --filter=Owners`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --filter=Locations`
- [x] VS Code Problems panelean ukitutako fitxategiek errore/warning berririk ez dutela baieztatu

# Zuzenketak 3

- [x] En settings añadir una nueva section "Jabeak" y añadir dos campos para el asunto (eu, es) y otros dos campos para el texto (eu, es). 
- [x] El email que se envia cuando se crea una nueva propietaria tomará los datos es estos nuevos campos

# Zuzenketak 4

- [x] los botones para crear un nuevo registro tienen que ser siempre como eel que que hay en Iragarkiak. Cambiale el título y ponle "Sortu berria", crear un componente?
- [x] le header de las páginas, separarlo un poco del menu superioor y debe de ser un pco más estrecho para tener más sitio en la pantalla
- [x] los campos que hay en admin/ubicaciones/{id} de validar admin y validar propietaria no van aquí, deben de ir en admin/propietarias/{} asociados a una propietarria y a una propiedad, en esta lista es donde se tiene que validar
- [x] en la lista admin/propietarias/{} quita el boton "desaktibatu" y su funcionalidad.
- [x] en la lista /admin/propietarias en vez de el icono bars, es más práctico si justo debajo de esa linea en la tabla se abre un pequeño espacio para mostrar las propiedades de esa propietaria y desde esa misma lista se editen y se creen nuevas
- [x] al crear una nueva ubicación me tiene que pedir también loos campos de los porcentajes
- [x] dale un poco de formato al breadcrum, adecuado al estilo que se usa en el proyecto y el headerr que llegue hasta el final para que quede alineado con lass tablas y un poco menos alto
- [x] en la lista de jabeak añade el botón de editar siguiendo el estilo de la tabla anuncios
- [x] reordenar los campos de los coopropietarios en el formulario de jabeak, una columna para el 1 con los campos marcados como obligatorios y otra columa para el 2 sin ser obligatorios
- [x] en las listas, al pinchar en el boton "Sortu berria" en vez de aparecer el formulario encima de la lista quedaría más bonito si aparece desde la derecha por encima de la lista un panel con el formulario.
- [x] en ubucaciones, los campos de los porcentajes tanto al crear como al editar deben permitir tanto . como , para loos decimales, luego se guardará siempre con .
- [x] al asignar una nueva propiedad a un owner, asegurarse de que su user tiene is_active=1
- [x] al modificar la fecha fin de una asignación, si el owner de esa asignación tiene todas cerradas, es decir, con fecha de fin, cambiar en su user is_active=0

## Inplementazio plana (Zuzenketak 4 - Balidazio eremuak esleipen-zerrendara mugitzea)

### Helburua

- [x] `admin/ubicaciones/{id}` bistatik `validado admin` eta `validado propietaria` kentzea.
- [x] `admin/propietarias/{id}` bistako esleipen-zerrendan (owner + property) bi balidazio horiek editagarri bihurtzea.

### Erabaki teknikoak

- [x] Balidazio boolearrak `property_assignments` erregistroari lotuta mantenduko dira; ez da eskema aldaketarik behar.
- [x] Kokalekuen detail zerrendan `asignado` egoera bakarrik utziko da; balidazioak ez dira han erakutsiko.
- [x] Jabeen detail zerrendan assignment bakoitzeko bi kontrol gehitu dira (`admin_validated`, `owner_validated`) eta assignment bakoitza banaka balidatzen da (one-by-one).
- [x] Eguneratzea assignment mailan egiten da (ID bidez), eta esleipena itxita badago (`end_date` beteta) balidazioa blokeatzen da.

### Exekuzio urratsak

- [x] 1. `app/Livewire/Admin/LocationDetail.php` eta bere bistan balidazio-zutabeen erreferentziak kendu.
- [x] 2. `app/Livewire/Admin/OwnerDetail.php`-n action berria gehitu: `toggleAssignmentValidation(int $assignmentId, string $field)` + guard clause (`end_date !== null` bada, ez eguneratu).
- [x] 3. `resources/views/livewire/admin/owners/detail.blade.php`-n `Admin balidatua` eta `Jabeak balidatua` zutabeak gehitu (checkbox/toggle estiloko kontrolarekin).
- [x] 4. `lang/eu/admin.php` eta `lang/es/admin.php`-n etiketa berriak osatu (owners assignment validation zutabeetarako).
- [x] 5. Feature testak eguneratu:
- [x]    - kokalekuen detail testetan `data-admin-validated`/`data-owner-validated` ez direla ageri.
- [x]    - owner detail test batean assignment-eko bi balidazioak alda daitezkeela eta DBn gordetzen direla.
- [x] 6. Dusk test sentikorra eguneratu, selector berriekin.

### Arriskuak

- [ ] `admin/propietarias/{id}` zerrendak assignment asko baditu, toggle bakoitzak request bat egingo du; beharrezkoa bada debounce/optimizazioa baloratuko da.
- [x] Balidazioa assignment ez-aktiboetan blokeatuko da (itxita bada, ezin da baimendu).

### Balidazioa

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/OwnerActionsTest.php`

## Inplementazio plana (Zuzenketak 4 - `is_active` sinkronizazioa esleipenen bizi-zikloan)

### Helburua

- [x] Esleipen aktibo berria sortzen denean, jabearen `user.is_active = 1` bermatzea.
- [x] Esleipen baten `end_date` ezartzean, jabe horrek esleipen aktiborik ez badu, `user.is_active = 0` ezartzea.

### Erabaki teknikoak

- [x] Aktibazioa/desaktibazioa action mailan zentralizatzea (`AssignPropertyAction` eta `UnassignPropertyAction`), Livewire osagaietan logika ez bikoizteko.
- [x] `Owners` osagaiko inline fluxuan, `saveAssignment()`-etik zuzenean eguneratu beharrean, ixte-eragiketa `UnassignPropertyAction`-era delegatzea.
- [x] `CreateOwnerAction`-eko gaur egungo portaera mantentzea (jabe berria sortzean `is_active = true`), eta arau berria assignment lifecycle-arekin lerrokatzea.
- [x] Desaktibazioa kalkulatzeko, jabe beraren esleipen aktiboak kontsulta bakarrean egiaztatzea (`whereNull('end_date')->exists()`).

### Exekuzio urratsak

- [x] 1. `AssignPropertyAction::execute()` amaieran jabearen user-a `is_active = true` ezarri (jada true bada, idempotentea).
- [x] 2. `UnassignPropertyAction::execute()`-n `end_date` ezarri ondoren, owner-ak aktiborik duen egiaztatu; ez badu, lotutako user-a desaktibatu.
- [x] 3. `Owners::saveAssignment()`-en `end_date` ezartzen den adarrean `UnassignPropertyAction` erabiltzea, arau bakarra mantentzeko.
- [x] 4. `Owners::createInlineAssignment()`-en assignment aktibo berriak `AssignPropertyAction` erabiltzeari eutsi (eta horren bidez aktibazioa bermatu).
- [x] 5. Errore-mezuak/portaera existentzia mantendu eta regressionik ez dela egiaztatu.

### Egin beharreko lanak

- [x] `app/Actions/AssignPropertyAction.php`
- [x] `app/Actions/UnassignPropertyAction.php`
- [x] `app/Livewire/Admin/Owners.php`
- [x] `tests/Feature/AssignmentActionsTest.php`
- [x] `tests/Feature/AdminOwnersAndLocationsTest.php`

### Arriskuak

- [ ] `saveAssignment()`-en egun dauden balidazio boolearren portaera aldatu gabe mantendu behar da assignment itxietan.
- [ ] Data-aldaketa hutsa (`start_date`) eta itxiera (`end_date`) bereiztea beharrezkoa da, nahi gabeko desaktibazioak saihesteko.
- [ ] Esleipen historikoak sortzeak (`end_date` beteta) ez du erabiltzailea aktibatu behar.

### Balidazioa

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AssignmentActionsTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`

## Inplementazio plana (Zuzenketak 4 - Ehuneko hamartarrak `,` eta `.` onartzea)

### Helburua

- [x] `admin/ubicaciones`-eko ehuneko eremuek sarreran `,` eta `.` onartzea (sortu eta editatu fluxuetan).
- [x] Datu-basean balioa beti puntuarekin (`.`) normalizatuta gordetzea.

### Erabaki teknikoak

- [x] Normalizazioa `LocationDetail` Livewire osagaian zentralizatzea, helper pribatu batekin (`string` -> `normalized decimal string`).
- [x] Balidazioak normalizatutako balioaren gainean egitea (`numeric|min:0|max:100`), eta input originala UIan mantentzea errorea badago.
- [x] `storage` motarako portaera bere horretan uztea (ehunekoak `null`).
- [x] Ikuspegiko `type="number"` murrizketa saihesteko, ehuneko eremuak `type="text"` + `inputmode="decimal"` erabiltzera pasatzea, `,` idazketa erosoa bermatzeko.

### Exekuzio urratsak

- [x] 1. `app/Livewire/Admin/LocationDetail.php`-n normalizazio metodoa gehitu (`str_replace(',', '.', ...)` + trim).
- [x] 2. `addProperty()`-n normalizatutako balioak erabili balidazioan eta `Property::create()` deian.
- [x] 3. `saveProperty()`-n normalizatutako balioak erabili balidazioan eta `update()` deian.
- [x] 4. `resources/views/livewire/admin/locations/detail.blade.php`-n ehuneko inputak `text` + `inputmode="decimal"` bihurtu (create/edit bi kasuetan).
- [x] 5. Feature testak gehitu/eguneratu `1,25` eta `2,5` bezalako sarrerak DBn `1.25`/`2.5` gisa gordetzen direla egiaztatzeko.

### Egin beharreko lanak

- [x] `app/Livewire/Admin/LocationDetail.php`
- [x] `resources/views/livewire/admin/locations/detail.blade.php`
- [x] `tests/Feature/AdminOwnersAndLocationsTest.php`

### Arriskuak

- [ ] Input testu bihurtzean, frontend-eko UXek ez du atzera egin behar (placeholder/focus/errore mezuekin).
- [ ] `numeric` balidazio estandarrarekin koherentzia mantendu behar da normalizazioa egin aurretik/ondoren.

### Balidazioa

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`

## Inplementazio plana (Zuzenketak 4 - "Sortu berria" eskuineko panela)

### Helburua

- [x] Zerrendetan `Sortu berria` sakatzean formularioa ez agertzea goian inline moduan.
- [x] Horren ordez, formularioa eskuinetik sartzen den overlay/slideover panel batean erakustea, zerrendaren gainetik.

### Erabaki teknikoak

- [x] Egoera-logika berdina berrerabili (`showForm`, `showCreateForm`, `showAddForm`), ikuspegi-egitura bakarrik aldatuz.
- [x] Panela eskuinean kokatu (`right-0`) eta atzeko geruza ilundua gehitu (`backdrop`) itxiera UX argiarekin.
- [x] Itxiera bikoitza onartu: `Utzi` botoia eta atzeko geruza klik egitea.
- [x] Mugikorrean pantaila osoa okupatu dezake; desktop-en zabalera mugatua mantendu (`max-w-*`).
- [x] Z-index eta scroll portaera kontrolatu, taula atzean geratzeko baina layout-a ez hausteko.

### Exekuzio urratsak

- [x] 1. Eragina duten bistak identifikatu: jabeak, kokaleku-detail zerrendak eta iragarkiak (non `Sortu berria` badago).
- [x] 2. Bista bakoitzean inline formulario blokea panel osagai-egitura bihurtu, estetikoki bateratuta.
- [ ] 3. Trantsizio sinplea gehitu (`translate-x-full` -> `translate-x-0`) eskuinetik sartzeko efektuarekin.
- [x] 4. Focus/itxiera portaera balidatu (`wire:click` bidez) eta formularioaren bidalketa-logika lehengo moduan mantendu.
- [ ] 5. Feature testetan selector egonkorrak gehitu/eguneratu (`data-*`) panela agertu/desagertu dela egiaztatzeko.

### Egin beharreko lanak

- [x] `resources/views/livewire/admin/owners/index.blade.php`
- [x] `resources/views/livewire/admin/locations/detail.blade.php`
- [x] `resources/views/livewire/admin/notice-manager.blade.php`
- [ ] `resources/views/components/` (beharrezkoa bada panel partekatu berria)
- [ ] `tests/Feature/AdminOwnersAndLocationsTest.php`
- [ ] `tests/Feature/AdminNoticeManagerTest.php` (edo dagokion fitxategia)

### Arriskuak

- [ ] Panel irekiak paginazio/filtro aldaketetan egoera zaharkitua utz dezake; reset politika gehitu beharko da.
- [ ] Overlay z-index txarrarekin, goiko header/menua panelaren gainetik gera daiteke.
- [ ] Test zaharrek inline formularioa espero badute, selector egonkorrak egokitu beharko dira.

### Balidazioa

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact --filter=Notice`
- [ ] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Browser/AdminSensitiveViewsTest.php` (**sqlite test inguruneko taulak ez daude prestatuta; Dusk workflow dedikatua behar du**)

## Inplementazio plana (Zuzenketak 4 - Jabeen zerrenda hedakorra, detail bistarik gabe)

### Helburua

- [x] `admin/propietarias` zerrendan `bars` ikonoaren ordez lerro hedagarria erabiltzea.
- [x] Lerro hedatuan jabearen assignment-ak erakutsi, eta bertatik assignment-ak editatu/sortu ahal izatea.

### Erabaki teknikoak

- [x] `OwnerDetail` full-page ikuspegiaren ordez ez da route berririk sortuko; `Owners` Livewire osagaian bertan kudeatzen da hedadura.
- [x] Lerro bakoitzak `expandedOwnerId` egoera dauka; row nagusiaren azpian `<tr>` osagarri bat irekitzen da `colspan` erabilita.
- [x] Assignment edit/sortze ekintzak jabearen mailan egiten dira, assignment bakoitza banaka eta baliozkotuta.
- [x] Esleipen itxietan (`end_date` beteta) balidazio aldaketak blokeatuta mantentzen dira.

### Exekuzio urratsak

- [x] 1. `app/Livewire/Admin/Owners.php`-n egoera berriak gehitu: `expandedOwnerId`, owner bakoitzeko assignment draft/edizioa.
- [x] 2. `OwnerDetail`-eko assignment logikaren beharrezko zatia `Owners` osagaira ekarri (DRY mantenduz, action existentziak berrerabilita sortze aktiboan).
- [x] 3. `resources/views/livewire/admin/owners/index.blade.php`-n bars botoia kendu eta row-expand botoi/portaera ezarri.
- [x] 4. Lerro hedatuan mini-taula/panel trinkoa txertatu: assignment zerrenda + sortze lerroa + edizio ekintzak.
- [x] 5. `admin/owners/show` esteka-dependentziak egokitu testetan (bars selector zaharra kenduta).
- [x] 6. Feature testak eguneratu: row hedadura ireki, assignment sortu/editatu, eta datuak zerrenda berean eguneratzen direla egiaztatu.

### Arriskuak

- [ ] Row hedadura + paginazioa batera erabiltzean egoera gal daiteke orri-aldaketan; `expandedOwnerId` reset politika zehaztu behar da.
- [ ] Osagai bakarrean logika gehiegi pilatzeko arriskua dago; beharrezkoa bada metodoak action klasera aterako dira.

### Balidazioa

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty --format agent`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/OwnerActionsTest.php`
