    # profila
- [ ] usa el mismo componente para mostrar en el menu del front y del aginte-panela el nombre de usuario y el logout, usa como referencia el del aginte panela que está mejor
- [ ] 
un usuario tiene que tener un perfil desde el que pueda ver:

organizar la información en diferentes pestañas
- las votaciones en las que ha tomado parte y cuando
- las sesiones que ha abierto, su inicio y su fin y el tiempo de conexión
- un enlace a cambiar contraseña que vaya al de cambiar la contraseña
- su ficha de propietaria y sus propiedades para poder validarlas (asegurate de que no puede la ficha de ningua otra propietaria)
- la primera vez que un usuario se loguee, pidele que acepte las condiciones de uso (texto que se almacenará en settings en el section owners en dos idiomas) y luego muestrale una pantalla con sus propiedades asignadas para que las valide, si no las tiene validadas ya (oweer validation)

- añadir el icono para accedeer al perfil junto al nombre del usuario, tanto en el menú del front como en el menu del aginte-panaela

# zuzenketak
- [x] accepted_terms_at tiene que ir en la tabla owners (modificar migration, no esta en produción)
- [x] en la lista de owners, añadir una columna despues de trasteros que muestre check verde si está aceptado y x roja si no
- [x] Abrir ficha propietara desde el formulario de usuaria no funciona, debe ir a la lista y ahí abrir el formulario d su owner
- [x] añadir en los devseed que una propietaria puede tener más de una propiedad
- [x] hay votaciones y en el menú del front no aparece "Votaciones" pero si veo el banner "Votaciones abiertas" la dos debería de tener la misma condición.
- [x] se ha perdido en el front el nombre de usuario, noo se ve
- [x] si el usuario está logeado y solo tiene el rol propietaria no mostrar el menú "Zona privada" en el front
- [x] en la pantalla del perfil, si noo se han aceptadoo las condiciones mostrar un modal con el texto de las condiciones y no dejarle hacer nada más hasta que las acepte.
- [x] en la pantalla del perfil, la propietaria logeada debe de poder editar sus datos, en la lista de sus propiedades, si ya están validadas no mostrar el botoón validar

## Implementation Plan

### Goal

- [ ] Amaitu `# zuzenketak` ataleko puntuak, gaur egungo egiturarekin bateragarri eta regressiorik gabe.

### Technical Decisions

- [ ] `accepted_terms_at` eremua `owners` taulara eramango da (ez `users`), eta lotutako model/controller/test erreferentziak sinkronizatuko dira.
- [ ] Front eta admin goiburuetan erabiltzaile-menurako osagai bakarra erabiliko da (`x-shared.desktop-user-menu`), profile ikonoa eta logout ekintzak barne.
- [ ] Front menuan "Votaciones" estekaren baldintza `VotingsNavigationComposer` logikarekin lerrokatuko da (bannerrarekin koherentea).
- [ ] "Zona privada" esteka role-aware bihurtuko da: `propietaria` rol hutsa duen erabiltzaile autentifikatuarentzat ez da erakutsiko.
- [ ] Profile pantailan terminoak onartu gabe badaude, modal blokeatzailea erabiliko da (owner fitxa/interakzioa blokeatuz).
- [ ] Propietariaren datuen edizioa profile flow-ean gaituko da, baina bere owner erregistroari soilik.
- [ ] Jabetzen balidazio-zerrendan, dagoeneko balidatutako elementuetan ez da "validar" ekintza erakutsiko.

### Execution Steps

- [x] 1. Datu-eredua egokitu: `accepted_terms_at` `owners` taulan kokatu, migration/model/controller doikuntzak egin, eta lotutako testak eguneratu.
- [x] 2. Admin owners listan accepted terms egoeraren zutabe berria gehitu (check berdea / X gorria).
- [x] 3. Admin users formularioko "Abrir ficha propietaria" fluxua konpondu: owners zerrendara joan eta owner zuzena irekita utzi.
- [x] 4. `DevSeeder` eguneratu, propietaria batek jabetza anitz izan ditzan datu estandarrean.
- [x] 5. Front nabigazioa zuzendu: `Votaciones` baldintza bannerrarekin berdindu, erabiltzaile-izena berriz agerrarazi, eta `Zona privada` estekaren rolaren araberako ikusgarritasuna ezarri.
- [x] 6. Profile UI/flow hobetu: terms modal blokeatzailea, propietariaren auto-edizioa, eta balidatutako jabetzetan balidazio botoia ezkutatzea.
- [x] 7. Itzulpen-gakoak eta test selektore egonkorrak (`data-*`) osatu, regression testak idatzi/eguneratu.

### Work Items

- [ ] `database/migrations/2026_04_11_093011_add_accepted_terms_at_to_users_table.php`
- [ ] `database/migrations/2026_04_09_100004_create_owners_table.php`
- [ ] `app/Models/User.php`
- [ ] `app/Models/Owner.php`
- [ ] `app/Http/Controllers/ProfileController.php`
- [ ] `app/Livewire/Admin/Owners.php`
- [ ] `resources/views/livewire/admin/owners/index.blade.php`
- [ ] `resources/views/livewire/admin/users/index.blade.php`
- [ ] `resources/views/layouts/front/main.blade.php`
- [ ] `resources/views/components/shared/desktop-user-menu.blade.php`
- [ ] `resources/views/public/profile.blade.php`
- [ ] `database/seeders/DevSeeder.php`
- [ ] `lang/eu/*.php` eta `lang/es/*.php` dagokien fitxategiak
- [ ] `tests/Feature/ProfilePageTest.php`
- [ ] Owners/front/profile aldaketek ukitzen dituzten `tests/Feature/**` eta beharrezkoa bada `tests/Browser/**`

### Validation

- [ ] TDD erritmoa: lehenik testak (edo gaurko hutsunea), gero inplementazioa.
- [ ] Docker bidez exekuzioa (`docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 ...`).
- [ ] `vendor/bin/pint --dirty` (Docker barruan).
- [ ] `composer quality` (Docker barruan) gutxienez amaieran.
- [ ] Ukitutako testak: `php artisan test --compact` fitxategi/filter bidez.
- [ ] Front/profile flow-etan aldaketak direnez, dagokion Browser/Dusk estaldura berrikusi/eguneratu.
