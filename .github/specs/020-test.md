# tests suit
**IMPORTANT**
comprobar si hay un test que cubre el caso y si no añadirlo
si se puede, preferiblemente que sea unit sin base de datos
si es del front, añadir un dusk-test también

## Roles
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

### DELEGATED_VOTE (Rebeca Trabajadora Asesoria, Idoia)
- [ ] FRONT - VOTACIONES: puede veer la vista de votaciones en el front, pero si no tiene ninguna propiedad activa asignada no puede votar. Puede ver los botones "Voto presencial" y "Voto Delegado"
- [ ] FRONT - VOTO PRESENCIAL: tiene persmiso para utilizar el voto pressencial
- [ ] FRONT - VOTO DELEGADO: tiene permiso para utilizar el boto delegado

**IMPORTANT**
Si hay alguna acción del panel de control que no está en este listado (si tener el cuenta el superadmin), es que el usuario logueado no tiene permiso para realizarla.
tratalo y escribe aquí las funcionalidades que hayas encontrado que no estén en esta lista (sin tener en cuenta el superadmin).

## Implementation Plan

### Goal

- [x] `composer quality`-ko PHPMD blokeoak kentzea, arauen atalaseak igo gabe.

### Technical Decisions

- [x] `AssignLocationChiefAction::execute()` metodoa helper txikiagoetan banatu (balidazioa, aurreko chief transferra, role sinkronizazioa).
- [x] `Owners::saveAssignment()` metodoa flow txikietan zatitu (`reopen`, `close`, `keep-open`) konplexutasuna eta NPath jaisteko.
- [x] `Owners` eta `Votings` klaseetan bloke koherenteak trait pribatuetara mugitzea, API publikoa eta UI portaera aldatu gabe.

### Execution Steps

- [x] 1. PHPMD baseline berria atera (`phpmd app text phpmd.xml`) eta errore lerroak baieztatu.
- [x] 2. `AssignLocationChiefAction` refactor txikia egin eta dagoen test focused-a exekutatu.
- [x] 3. `Owners::saveAssignment()` zatitu eta errore mezuen tratamendua bateratu.
- [x] 4. `Owners` klasearen tamaina murriztu trait batera erauziz assignment-related metodoak.
- [x] 5. `Votings` klasearen tamaina murriztu trait batera erauziz modal/delegation-related metodoak.
- [x] 6. Pint, PHPStan, test focused eta `composer quality` exekutatu Docker barruan.

### Work Items

- [x] app/Actions/Locations/AssignLocationChiefAction.php
- [x] app/Livewire/Admin/Owners.php
- [x] app/Livewire/Admin/Votings.php
- [x] app/Livewire/Admin/Concerns/* (berria, beharrezkoa bada)

### Validation

- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --dirty`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/phpstan analyse --memory-limit=1G --no-progress`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/AdminOwnersAndLocationsTest.php`
- [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality`

### Notes

- Branch berria sortzeko saiakera egin da (`git checkout -b 020-test-phpmd-refactor`), baina terminal deia erabiltzaileak skip egin du.
