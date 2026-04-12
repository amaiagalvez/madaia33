## Roles

**IMPORTANTE** Funcionalidad crítica
Comprobar que se cumplen todas estas condiciones para cada rol, si no hay un test que lo compruebe añadelo. Crea los seeders necesarios den DevSeeder para tenga diferentes usuario con diferentes opciones.

**IMPORTANTE** si encuentras otras funcionalidades que no están en la lista y no sabes qué permisos asignarle, primero preguntame antes de hacer nada y escribe abajo de este listado todos los casos con dudas que hayas encontrado

1. SUPER_ADMIN
2. GENERAL_ADMIN
3. COMMUNITY_ADMIN (+locations)
4. PROPERTY_OWNER (+ property_assignments)
5. DELEGATED_VOTE

Puede ocurrir que un mismo user tenga varios roles, entonces sus permisos serán la combinación de ambos

### SUPER_ADMIN (Amaia)
- [ ] tiene permiso para todo, excepto para votar

### GENERAL_ADMIN (Idoia, Emilio)
- [ ] PANEL - LISTADO AVISOS: Tiene permiso para publicar avisos para todas las propietarias, pero no para una ubicación en concreto
- [ ] PANEL - LISTADO MENSAJES: Tiene permiso para leer los mensajes y responder, pero no para borrar
- [ ] PANEL - LISTADO UBICACIONES: Tiene permiso para ver el listado de localizaciones y de sus ubicaciones, pero no puede editar ni modificar ni borrar nada.
- [ ] PANEL - LISTADO VOTACIONES: Tien permiso para ver el listado de votaciones, pero solo las que sean para todos, es decir, las que no tengan ninguna ubicación asignada. Pueden crear nuevas votaciones (sin opción a eleegir ubicación) y pueden editar y borrar solo las que puedeen ver. Al pinchar en "Ver Censo" y "Ver Votantes", en el modal no verán el nombre de la propietaria.

### COMMUNITY_ADMIN (33I 1A Amaia, Aitor Asesoria, Idoia)
Un community_admin puede tener asingadas varias propiedades, tambien puede que sea prropietaria
- [ ] PANEL - LISTADO AVISOS: Tiene permiso para publicar avisos solo en las  ubicaciones que tiene unidas a su usuario
- [ ] PANEL - LISTADO UBICACIONES: Tiene permiso para ver solo las ubicaciones que tiene unidas a su usuario y las propiedades que están enlazadas a dichas ubicaciones,
- [ ]  pero no puede editar ni modifcar ni borrar nada.
- [ ] PANEL - LISTADO PROPIETARIAS: Tiene permiso para ver solo las propietarias que tiene propiedades activas en las ubicaciones que tiene unidas a su usuario, pero no puede editar ni modificar ni borrar nada.
- [ ] PANEL - LISTADO VOTACIONES: Tien permiso para ver el listado de votaciones, pero solo las que sean  solo las ubicaciones que tiene unidas a su usuario. Pueden crear nuevas votaciones (con opción a eleegir las ubicaciones que tiene unidas a su usuario) y pueden editar y borrar solo las que puedeen ver. Al pinchar en "Ver Censo" y "Ver Votantes", en el modal no verán el nombre de la propietaria.

### PROPERTY_OWNER (Jon Ander e Irati)
- [ ] una propietaria n puede tener activa (sin fehca de fin) una propiedad que otra propietaria ya tenga activa
- [ ] una propietaria puede votar solo en las votaciones que le correspondan por sus propiedades asignadas activas

### DELEGATED_VOTE (Rebeca Trabajadora Asesoria, Idoia)
- [ ] FRONT - VOTACIONES: puede veer la vista de votaciones en el front, pero si no tiene ninguna propiedad activa asignada no puede votar. Puede ver los botones "Voto presencial" y "Voto Delegado"
- [ ] FRONT - VOTO PRESENCIAL: tiene persmiso para utilizar el voto pressencial
- [ ] FRONT - VOTO DELEGADO: tiene permiso para utilizar el boto delegado

## Inplementazio plana

### Helburua

- Rol bakoitzaren baimen eta murrizketa kritikoak modu automatizatuan egiaztatzea, regressio arriskua murrizteko.
- `DevSeeder` eguneratzea, rol-konbinazio errealistak dituzten erabiltzaileekin (`rol bakarra` eta `rol anitz`).
- Zalantzazko baimen kasuak detektatzea eta zerrenda bereizian uztea, exekuzio aurretik baieztatzeko.

### Erabaki teknikoak

- Test estrategia lehenetsia Unit + Feature izango da, eta Browser/Dusk bakarrik benetan front flow kritikoetan.
- Rol baimenak ez dira soilik UI bidez balidatuko; route/middleware eta Livewire ekintzetan ere egiaztatuko dira.
- Datuen prestaketa factory + seeder konbinazioarekin egingo da, seed finko ahulen mendekotasuna saihesteko.
- Rolen konbinazio kasuak explicituki estaliko dira (adibidez, `COMMUNITY_ADMIN + PROPERTY_OWNER`, `DELEGATED_VOTE + PROPERTY_OWNER`).

### Exekuzio urratsak

- [x] 1. Egungo baimen inplementazioa mapatu (routeak, middlewareak, Livewire osagaiak, voting flowa).
- [x] 2. Speceko baldintza bakoitza test-kasu bihurtu, rol eta rol-konbinazio estaldura matrize batekin.
- [x] 3. `DevSeeder` eguneratu erabiltzaile/rol/lotura adierazgarriekin (location, property_assignment, delegated kasuak).
- [x] 4. Missing testak gehitu eta daudenak egokitu, lehentasunez `tests/Unit` eta `tests/Feature` erabiliz.
- [ ] 5. Front flow kritikoetarako `tests/Browser` egiaztapen minimo sendoa gehitu (bozketa ikusgarritasuna eta boto aukeren baimenak).
- [x] 6. Zalantzazko baimen kasuak dokumentatu eta exekuzioa gelditu user baieztapena jaso arte.
- [x] 7. Formateoa/kalitatea/testak exekutatu Docker barruan eta emaitzak txostenean jaso.

### Egin beharreko lanak

- [x] `database/seeders/DevSeeder.php` eta lotutako seederrak.
- [ ] `tests/Unit/**` (DB beharrik ez duten negozio-arauak).
- [x] `tests/Feature/**` (HTTP/DB/Livewire integrazioa behar duten baimenak).
- [x] `tests/Browser/**` (frontend baimen-fluxu sentikorrak bakarrik).
- [x] Rol/baimen logika dauden `app/**` fitxategiak (beharrezkoa bada soilik, testak pasatzeko).

### Balidazioa

- [x] TDD bidezko inplementazioa ahal den neurrian.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`.
- [x] Dagokion test multzo minimoa: `php artisan test --compact` fitxategi/filter bidez.
- [x] Front ukituak badaude, Dusk test kritikoak exekutatu.
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`.
- [x] VS Code Problems panelean ukitutako fitxategien error/warning berririk ez.

### Zalantzak (exekuzio aurretik baieztatzeko)

- [x] `GENERAL_ADMIN` rolak `PANEL - LISTADO UBICACIONES` atalean "localizaciones" = locations eta "ubicaciones" = properties.
- [x] `GENERAL_ADMIN` bozketen araua baieztatua: bai (global bozketen murrizketa mantendu).
- [x] `COMMUNITY_ADMIN` kasuan baieztatua: izen ezkutaketa censo eta votantes modaletan aplikatu.

# Zuzenketak
- [x] Testear qué pasa si un GENERAL_ADMIN, COMUNITY_ADMIN o DELEGATED_VOTE puede votar, porque también es PROPERTY_OWNER o si intentan votar sin ser PROPERTY_OWNER
- [x] El GENERAL_ADMIN no puede ver los avisos que tengan ubucaciones asignadas, solo puede ver los que sean para todos (sin ubucaciones asignadas)
- [x] El GENERAL_ADMIN no puede editar ni borrar locations ni properties, entonces, no muestres los botones de editar y borrar en la lista de ubicaciones
- [x] El GENERAL_ADMIN no puede modificar el lehendakari, soloo ver quien es el lehendakari o el administrador
- [x] El GENERAL_ADMIN no puede tener acceso al listado de usuarias
- [x] El GENERAL_ADMIN puede ver el front bozketak, pero no puede votar, a no ser que también sea PROPERTY_OWNER 

## Implementation Plan (GENERAL_ADMIN azken 3 zuzenketak)

### Goal

- [x] GENERAL_ADMIN rola `read-only` uztea location detail-eko lehendakari atalean.
- [x] GENERAL_ADMIN rola `admin.users` zerrendatik kanpo uztea (route + osagai baimenak koherente).
- [x] GENERAL_ADMIN rolarentzat front bozketen ikusgarritasuna mantentzea, baina boto zuzena blokeatuta uztea.

### Technical Decisions

- [x] Lehendakari aldaketarako backend guard dedikatua erabili (`saveChiefOwner`) eta UI botoia/forma baldintza berarekin lotu.
- [x] Users ataleko sarbidea route mailan mugatu (`superadmin` bakarrik), eta dagoen Users Livewire guardarekin lerrokatu.
- [x] Public votings `mount`-en GENERAL_ADMIN front sarbidea baimendu: `PROPERTY_OWNER` ere bada bozkatu ahal izan dezala; bestela `read-only` (`canCastVotes=false`) + `vote()` errore-kontrola mantendu.

### Execution Steps

- [x] 1. `LocationDetail` + bere Blade: GENERAL_ADMINentzat lehendakaria aldatzeko kontrolak ezkutatu/blokeatu.
- [x] 2. `routes/private.php` eta users testak: GENERAL_ADMIN sarbidea kentzea eta testak eguneratzea.
- [x] 3. `PublicVotings` eta dagokion Feature/Brower test minimoa: GENERAL_ADMIN ikusgai; botoa soilik `PROPERTY_OWNER` konbinazioarekin.
- [x] 4. Pint + test fokalizatua exekutatu Docker barruan.

### Work Items

- [x] `app/Livewire/Admin/LocationDetail.php`
- [x] `resources/views/livewire/admin/locations/detail.blade.php`
- [x] `routes/private.php`
- [x] `app/Livewire/PublicVotings.php`
- [x] `tests/Feature/AdminOwnersAndLocationsTest.php` (edo `tests/Feature/AdminLocationsPermissionsTest.php`)
- [x] `tests/Feature/AdminUsersManagementTest.php`
- [x] `tests/Feature/VotingsFeatureTest.php`

### Validation

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminUsersManagementTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminLocationsPermissionsTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/VotingsFeatureTest.php --filter=general_admin`

### Questions or Risks

- [x] Argituta: kontraesan posiblea FRONTerako bakarrik da (`GENERAL_ADMIN` front-ean ikus dezake baina ezin du bozkatu).
- [x] Argituta (PANEL): `GENERAL_ADMIN`ek admin-votings zerrenda ikus dezake soilik global bozketekin (kokapenik gabe), horietan sortu/edita/ezabatu ahal du, eta `Ver Censo` / `Ver Votantes` modaletan jabe-izenak ezkutatuta erakutsi behar dira.


# Zuzenketak 2
- [x] El COMUNITY_ADMIN solo puede ver los avisoos del panel en los que tengan asignados las propiedades asignadas a su usuario
- [x] El COMUNITY_ADMIN puede ver el front bozketak, pero no puede votar, a no ser que también sea PROPERTY_OWNER

# Zuzenketak 3
- [x] Aginte paneneal `GENERAL_ADMIN`ek admin-votings zerrenda ikus dezake soilik global bozketekin (kokapenik gabe), horietan sortu/edita/ezabatu ahal du, eta `Ver Censo` / `Ver Votantes` modaletan jabe-izenak ezkutatuta erakutsi behar dira.
- [x] Aginte paneneal `COMUNITY_ADMIN`ek admin-votings zerrenda ikus dezake soilik bere erabiltzaileak lotuta dazukan ubikazioekin lotuta dauden bozkeitak, horietan sortu/edita/ezabatu ahal du, eta `Ver Censo` / `Ver Votantes` modaletan jabe-izenak ezkutatuta erakutsi behar dira.
- [x] si el usuario con DELEGATED_VOTE o con PROPERTY_OWNER pero que no tiene el rol GENERAL_ADMIN o COMUNITY_ADMIN, al intentar acceder al panel le debe redirigir al home
- [x] en /eu/bozketak, si está activadoo "Boto delegatua itxi", no mostrar los votones "Boto presentziala" y "Boto delegatua"
