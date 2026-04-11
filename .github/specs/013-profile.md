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
- [ ] accepted_terms_at tiene que ir en la tabla owners (modificar migration, no esta en produción)
- [ ] en la lista de owners, añadir una columna despues de trasteros que muestre check verde si está aceptado y x roja si no
- [ ] Abrir ficha propietara desde el formulario de usuaria no funciona, debe ir a la lista y ahí abrir el formulario d su owner
- [ ] añadir en los devseed que una propietaria puede tener más de una propiedad
- [ ] hay votaciones y en el menú del front no aparece "Votaciones" pero si veo el banner "Votaciones abiertas" la dos debería de tener la misma condición.
- [ ] se ha perdido en el front el nombre de usuario, noo se ve
- [ ] si el usuario está logeado y solo tiene el rol propietaria no mostrar el menú "Zona privada" en el front
- [ ] en la pantalla del perfil, si noo se han aceptadoo las condiciones mostrar un modal con el texto de las condiciones y no dejarle hacer nada más hasta que las acepte.
- [ ] en la pantalla del perfil, la propietaria logeada debe de poder editar sus datos, en la lista de sus propiedades, si ya están validadas no mostrar el botoón validar
