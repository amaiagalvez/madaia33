## profile
arreglar la ruta profile

## login
la ruta login me tiene llevar a la ruta privado en el idioma en el que este navegando, si no hay idioma por defecto en euskera

en la ruta privado, el botón "Saioa hasi" tiene que hacer lo mismo que lo que hace el botón "Sartu" del login actual

adapta las vistas del /forgot-password al estilo del proyecto

si el user tiene un owner asignado, podrá logearse con el email o con el dni pero solo si is_active = 1, sino no se podrá logear
si el user no tiene un owner asignado solo podrá logearse con el email siempre que tenga is_active = 1

## Inplementazio plana

### Helburua

- Login fluxua zuzentzea hizkuntza-kontestuarekin koherentea izan dadin (eu/es), private orriko botoiak login ofizialera eramatea bermatuz.
- Forgot password bistak proiektuaren estilo eta i18n konbentzioetara egokitzea.
- Autentikazio arauak doitzea owner esleipenaren eta `is_active` egoeraren arabera.
- Profile ibilbidearen arazoa identifikatu eta zuzentzea (route naming/path koherentzia).

### Erabaki teknikoak

- Fortify autentikazioa [app/Providers/FortifyServiceProvider.php](app/Providers/FortifyServiceProvider.php) fitxategian mantenduko da, bertan centralizatuta dagoelako login identifikatzailearen logika.
- Owner esleipenaren egiaztapena erabiltzailearen erlazio bidez egingo da (`owner` edo `owners` erlazio erreala zein den lehenik baieztatuta), query kopurua minimizatuz.
- Hizkuntza-redirect logika route izen lokalizatuetan oinarrituko da (`private.eu` / `private.es`) eta fallbacka eu izango da.
- Private orriko “Saioa hasi” botoia [resources/views/public/private.blade.php](resources/views/public/private.blade.php) fitxategian login route ofizialera lotuko da, “Sartu” botoiaren portaera bera emanez.
- Forgot-password bista [resources/views/pages/auth/forgot-password.blade.php](resources/views/pages/auth/forgot-password.blade.php) login bistaren eredura lerrokatuko da.

### Exekuzio urratsak

- [x] 1. Egungo route/redirect portaera aztertu eta profile/login/private maparen arazo zehatza erreproduzitu.
- [x] 2. Login route redirecta egokitu: nabigazio-hizkuntza mantendu, fallback eu-rekin.
- [x] 3. Private orriko “Saioa hasi” botoia login fluxu ofizialera konektatu.
- [x] 4. Forgot-password bista restylatu proiektuaren auth shell eta itzulpen klabeekin.
- [x] 5. Fortify autentikazio logika doitu:
    - owner esleitua badu: email edo dni,
    - owner esleitu gabe: email bakarrik,
    - bi kasuetan `is_active = 1` derrigor.
- [x] 6. Eskakizun berriak estaltzen dituzten Feature testak gehitu/eguneratu eta lehendik daudenak ez haustea.

### Egin beharreko lanak

    - [x] [app/Providers/FortifyServiceProvider.php](app/Providers/FortifyServiceProvider.php): autentikazio arau berriak.
    - [x] [resources/views/public/private.blade.php](resources/views/public/private.blade.php): login botoiaren portaera.
    - [x] [resources/views/pages/auth/forgot-password.blade.php](resources/views/pages/auth/forgot-password.blade.php): estilo + i18n lerrokapena.
    - [x] [resources/views/pages/auth/reset-password.blade.php](resources/views/pages/auth/reset-password.blade.php): pasahitz-aldaketako bista ere auth estilo berrira ekarria.
    - [x] [routes/web.php](routes/web.php) eta/edo auth redirect konfigurazioa: hizkuntza-kontestuko redirecta.
    - [x] [routes/settings.php](routes/settings.php) edo profile loturak: profile route fix.
    - [x] [tests/Feature/Auth/AuthenticationTest.php](tests/Feature/Auth/AuthenticationTest.php): owner + dni/email + is_active kasuak.
    - [x] [tests/Feature/Auth/PasswordResetTest.php](tests/Feature/Auth/PasswordResetTest.php): forgot-password/reset-password bistak eta erregresioak.

### Balidazioa

    - [x] TDD bidez inplementazioa ahal den neurrian (testak eguneratu eta exekutatuta).
    - [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 vendor/bin/pint --format agent ...` ukitutako PHP fitxategietan exekutatua.
    - [x] `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 php artisan test --compact tests/Feature/Auth/AuthenticationTest.php tests/Feature/Auth/PasswordResetTest.php tests/Feature/Settings/ProfileUpdateTest.php tests/Feature/AdminAuthTest.php tests/Feature/DashboardTest.php tests/Feature/Auth/TwoFactorChallengeTest.php`
    - [x] Login/private fluxuarekin lotutako Browser testak Dusk workflowarekin exekutatu: `tests/Browser/AdminAuthTest.php` eta `tests/Browser/PrivatePageTest.php`.

### Oharrak

    - `docker compose run --rm --user ${DC_UID:-1000}:${DC_GID:-1000} madaia33 composer quality` exekutatu da, baina ez da osorik pasa repoan aurrez zeuden style arazoengatik ukitu gabeko fitxategi askotan.

# Zuzenketak 1
- [x] arregla la ruta /settings/profile
- [x] adapta el formato de todas las vistas que tengan que ver con el cambio de contraseña para quee usen el mismo formato que la vista pribatua
- [x] los emails se debeen enviar desde el email configurado en settings en el section "email_configurration"
- [x] quita el blade login actual, no lo uso
- [x] quita el dashboard de laravel /dashboard, no lo uso
- [x] /settings/profile muevelo al menu superior al pinchar en el nombre del usuario logeado
- [x] no se pueden crear usuarios nuevos desde fuera, quita las vistas correspondientes

# Zuzenketak 2
- [x] en las pantallas para recuperar contraseña, en vez del logo de laravel usa el de madaia33.png, añade las traducciones a EU y ES que faltan, añade también los idiomas a las rutass necesarias
- [x] el email que se manda para reecupera contraseña tienee que estar en el lenguaje en el que se está navegando
- [x] los input de las pantallass para recuperar contraseñas casi no se ven, dale a los botones y los links de estas pantallas el estilo del proyecto
- [x] cuando voy a la ruta pribatua, si ya estoy logeada que me lleve directamente al aginte-panela

# Zuzenketa osagarria
- [x] el asunto del email para recuperar contraseña también tiene que ser en función del idioma

### Plan zehatza

- Laravel-ek reset emailaren subject-erako erabiltzen duen gakoa `Reset your password` dela aprobetxatu, eta ez dagoen EU/ES itzulpena gehitu.
- [tests/Feature/Auth/PasswordResetTest.php](tests/Feature/Auth/PasswordResetTest.php) eguneratu, locale bakoitzean notifikazioaren `subject` zuzena dela egiaztatzeko.
- Ukitutako itzulpen-fitxategiak eta Feature test fokalizatua Docker bidez exekutatu.
