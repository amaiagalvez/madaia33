# unificar terminología

Ubicaciones => Comunidades

# tests suit
**IMPORTANT**
comprobar si hay un test que cubre el caso y si no añadirlo
si se puede, preferiblemente que sea unit sin base de datos
si es del front, añadir un dusk-test también

## Roles
1. SUPER_ADMIN
2. GENERAL_ADMIN
3. COMMUNITY_ADMIN
4. PROPERTY_OWNER
5. DELEGATED_VOTE

Puede ocurrir que un mismo user tenga varios roles, entonces sus permisos serán la combinación de ambos

### SUPER_ADMIN (Amaia)
- [ ] Tiene permiso para todo excepto para votar

### GENERAL_ADMIN (Idoia, Emilio)
- [ ] AVISOS: Tiene permiso para publicar avisos para todas las propietarias, pero no para una ubicación en concreto
- [ ] MENSAJES: Tiene permiso para leer los mensajes y responder, pero no para borrar
- [ ] UBICACIONES: Tiene permiso para ver el listado de localizaciones y de sus ubicaciones, pero no puede editar ni modificar ni borrar nada.

### COMMUNITY_ADMIN (33I 1A Amaia, Aitor Asesoria)
- [ ] AVISOS: Tiene permiso para publicar avisos solo en las  ubicaciones que tiene unidas a su usuario
- [ ] UBICACIONES: Tiene permiso para ver solo las ubicaciones que tiene unidas a su usuario y las propiedades que están enlazadas a dichas ubicaciones, pero no puede editar ni modifcar ni borrar nada.

### PROPERTY_OWNER (Jon Ander e Irati)


### DELEGATED_VOTE

