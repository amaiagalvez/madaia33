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
