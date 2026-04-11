Igandea
=======
- Terminar formato de Votaciones  y Usuarias
- Testak Rolak Panela y Front
- pasar pint, quality y test a todo 
- votaciones pdf
- azalpena bozketak front-ean gehitzeko

# unificar terminología

locations: Ubicaciones => Comunidades
properties: Propiedades => Fincas
property_assignments => Propiedades
owners => Propietarias

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
- [ ] PANEL - LISTADO VOTACIONES:

### COMMUNITY_ADMIN (33I 1A Amaia, Aitor Asesoria)
- [ ] PANEL - LISTADO AVISOS: Tiene permiso para publicar avisos solo en las  ubicaciones que tiene unidas a su usuario
- [ ] PANEL - LISTADO UBICACIONES: Tiene permiso para ver solo las ubicaciones que tiene unidas a su usuario y las propiedades que están enlazadas a dichas ubicaciones,
- [ ]  pero no puede editar ni modifcar ni borrar nada.
- [ ] PANEL - LISTADO PROPIETARIAS: Tiene permiso para ver solo las propietarias que tiene propiedades activas en las ubicaciones que tiene unidas a su usuario, pero no puede editar ni modificar ni borrar nada.
- [ ] PANEL - LISTADO VOTACIONES:

### PROPERTY_OWNER (Jon Ander e Irati)
- [ ] no puede tener activa (sin fehca de fin) una propiedad que otra propietaria ya tenga activa
- [ ] 

### DELEGATED_VOTE (Rebeca)
- [ ] d



**IMPORTANT**
Si hay alguna acción del panel de control que no está en este listado (si tener el cuenta el superadmin), es que el usuario logueado no tiene permiso para realizarla.
tratalo y escribe aquí las funcionalidades que hayas encontrado que no estén en esta lista (sin tener en cuenta el superadmin).
