importante: seguir las convenciones del proyecto, tanto en estructura de ficheros como en diseño.
esta feature es muy crítica, tiene que funcionar perfectamente y tiene que ser auditable
no puedo tener ningún tipo de problemas en las votaciones

## votaciones - panel de cotrol
una votación tiene 
un nombre
una pregunta
una fecha de incio y una fecha de fin en las que se podrá votar
un campo publicado (boolean por defecto false)
un campo anonimo (boolean por defecto false)

una lista de opciones que se tendrán que votar
dos campos más para poder elegir a qué portales y a qué garajes se les permitirá votar, por defecto null (entonces la votación será abierta para todos)

todos los campos son eu y es

el listado de las votaciones mostrará por defecto las votaciones activas con las columnas: nombre, fecha incio, fecha fin y publicado (iconos check o x)
añade una columna censo en la que se calculará cuantas proietarias tienen derecho a votar
si la votacion no tiene ni portales ni garajes asignados signifca que todas las propietarias pueden votar
si la votación tiene asignada un portal, solo las propietarias que tengan asigndada una propiedad en ese portal podrán votar
si la votación tiene asignada un garaje, solo las propietarias que tengan asigndada una propiedad en ese garaje podrán votar

al lado del censo, poner un icono bars y al pincharlo se verá el listado de propietarios con el porcenje que le corresponde
en otra columna poner votos, es decir, el número de propietarias que han votado y un icono bar que al abrirlo muestre el mismo listado de propietarios anterior, pero solo con los que ya han votado

al crear una nueva votación, hay que validar las fechas y los campos nombre, pregunta y fechas son obligatorios

## votacioes - front
si hay votacioes activas encima del bloque de historioa se mostrará de forma clara pero atractiva que hay  votacioes pendientes, con un boton para acceder a la pantalla de las votaciones (en el móvil, este apartado se verá primero)
al pinchar en el botón, si no está logeado irá a la blade private y al logearse correctamete irá a una vista del front que solo es accesible si se está logeado y hay votaciones abiertas

esa pantalla debe tener el mismo estilo del front
y se mostrarán en difrentes tarjetas las votaciones abiertas: nombre, pregunta, opciones a votar que hay que elegir y un botón para votar


## guardar voto desde el front
antes de guardar el voto, volver a comprobar que el usuario tiene permiso para votar en esa votación
si una propietaria tiene mas una propiedad, no importa, solo podrá votar una vez si su usario tiene permiso para votar en esa votación
después de guardar el voto enviar un mail al propietario confirmando que ha votado y dándole las gracias por participar (Asunto: Votaciones Comunidad)

### si la votación es anonimo=false

en una tabla se guardará quien ha votado y en qué votación
en otra tabla se guardrá qué votacion y qué opcion ha elegido y quien ha votado
en otra tercera tabla se llevará la cuenta del número de votos que tiene cada opción de cada votación

## si la votación es anonimo=true

en una tabla se guardará quien ha votado y en qué votación
en otra tercera tabla se llevará la cuenta del número de votos que tiene cada opción de cada votación

## voto delegado
en el listado de votaciones, añadir un botón para el voto delegado, se mostrará el listado de las propietarias que todavía no han votado en alguna de las votaciones activas en ese momento
junto ha cada propietaria añadir un botón que lleve a la pantalla dee votaciones del front como si fuera ella la que está logueada. 
al guardar en la tabla quien ha votado y en qué votación, guardar también el user_id de la persona que ha votado por ella.
en la lista de personas que han votado a la que se accede desde el listado de votaciones, añadir una nueva columna para mostrar el nombre del que ha votado en su nombre.

## Inplementazio plana

### Helburua

- [x] Votings domain osoa gehitu: admin kudeaketa, front bozketa, audit trail eta boto delegatua.

### Erabaki teknikoak

- [x] DB eredua 6 taulatan banatuta: `votings`, `voting_options`, `voting_locations`, `voting_ballots`, `voting_selections`, `voting_option_totals`.
- [x] Baimen-kalkulua `VotingEligibilityService` bidez zentralizatuta (portal/garaje murrizketekin).
- [x] Bozketa gordetzea `CastVotingBallotAction` transakzioan eta race-condition aurkako blokeoarekin.
- [x] Front bozketa `auth` bidez babestuta eta delegated owner saio-gakoarekin.

### Exekuzio urratsak

- [x] 1. Datu eredua, modeloak, factory-ak eta bozka gordetzeko action-a sortu.
- [x] 2. Admin bozketa kudeaketa (zerrenda, errolda modalak, boto delegatua) inplementatu.
- [x] 3. Front bozketa orria eta home callout-a gehitu.
- [x] 4. Posta-konfirmazioa eta audit-erregistroak (cast_by_user_id) lotu.
- [x] 5. Testak idatzi eta exekutatu.
- [x] 6. ERD eta view-map Mermaid dokumentazioa eguneratu.

### Balidazioa

- [x] `vendor/bin/pint --dirty --format agent` exekutatua Docker barruan.
- [x] `php artisan test --compact tests/Feature/AdminVotingsTest.php tests/Feature/VotingsAuditabilityTest.php tests/Feature/VotingsFeatureTest.php tests/Feature/HomeVotingCalloutTest.php` (12 test, 35 assertion) pass.
- [x] Dusk front workflow: `php artisan test --compact tests/Browser/VotingsFrontFlowTest.php` selenium-first Docker moduan (2 test, 6 assertion) pass.
- [ ] `composer quality` ezin izan da berde utzi: repo osoan aurretik zeuden style issue ugari eta `safe.directory` konfigurazio abisua agertu dira.


# Kritikotasuna
- [x] ahora necesito que me asegures que todo este flujo está bien validado con test y con dusk-test para el front y que es completamente auditable para no tener ningún problema cuando lo ponga en marcha
- [x] vuleve a repasarlo es un código MUY CRITICO

## Errepaso kritiko gehigarria (2026-04-10)

- [x] Boto delegatuaren segurtasuna gogortu da: owner erabiltzaileek ezin dute delegated session bidez beste jabe baten identitatea hartu.
- [x] Segurtasun-regresio test berriak gehitu dira eta berde daude (Feature + Dusk front).
