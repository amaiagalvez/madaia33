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

# Panela
- [ ] 
- Añadir otro canal para las bidalketak. "Eskuz" / "Manual". Al elegir este canal, como recipients se añadiran los coop1 que no tengan, ni telefono, ni email, o si tiene telefono que no tengan whatchapp ni email

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
- Cómo se envian los mensajes programados?


6-B Jose Meléndez         630856088 jose.melendez.amado@gmail.com
3-B Juanjo Ortega López   659054060 ortegalopez33@gmail.com 
1-B Jon Urbizu Etxabarri  685757583 
