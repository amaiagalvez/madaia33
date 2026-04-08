# Plan de Implementación: community-web

## Visión general

Implementación incremental de la web de la comunidad de vecinos sobre Laravel 13 + Livewire + TailwindCSS. Cada tarea construye sobre la anterior y termina con la integración completa de todos los componentes.

## Tareas

- [x] 1. Configurar estructura del proyecto y migraciones de base de datos
  - Arrancar el entorno Docker: `docker compose up -d`
  - Crear las migraciones para las tablas: `notices`, `notice_locations`, `images`, `contact_messages`, `settings`
  - Crear los modelos Eloquent: `Notice`, `Image`, `NoticeLocation`, `ContactMessage`, `Setting`
  - Añadir el accessor bilingüe `title`, `content` y `alt_text` en los modelos correspondientes con lógica de fallback
  - Crear seeders para datos iniciales: usuario administrador y settings por defecto
  - Configurar el enlace simbólico de Storage (`php artisan storage:link`)
  - Añadir en `settings` las claves para contenido legal (`legal_page_privacy_policy_eu`, `legal_page_privacy_policy_es`, `legal_page_legal_notice_eu`, `legal_page_legal_notice_es`)
  - Crear `DevSeeder` con datos de prueba realistas (avisos, imágenes, mensajes) y llamarlo desde `DatabaseSeeder` solo cuando `app()->isLocal()` sea `true`
  - _Requisitos: 9.1, 9.2, 9.3, 6.1, 6.2, 15.1, 15.2_

  - [ ]\* 1.1 Escribir tests de propiedad para el accessor bilingüe con fallback
    - **Propiedad 4: Accessor bilingüe con fallback correcto**
    - **Valida: Requisitos 2.4, 2.5**

- [x] 2. Implementar internacionalización y middleware de locale
  - Crear el middleware `SetLocale` que lee el locale de sesión y llama a `App::setLocale()`
  - Registrar el middleware en el grupo `web`
  - Crear la estructura de archivos de traducción en `lang/eu/` y `lang/es/` (general, notices, gallery, contact, admin)
  - Implementar el componente Livewire `LanguageSwitcher` que guarda el locale en sesión y recarga la página
  - _Requisitos: 1.1, 1.2, 1.3, 1.4, 1.5_

  - [ ]\* 2.1 Escribir test de propiedad para locale correcto según selección
    - **Propiedad 1: Locale correcto según selección**
    - **Valida: Requisitos 1.2, 1.3**

  - [ ]\* 2.2 Escribir test de propiedad para persistencia del locale en sesión
    - **Propiedad 2: Persistencia del locale en sesión**
    - **Valida: Requisito 1.4**

- [x] 3. Crear layouts Blade y rutas principales
  - Crear `layouts/public.blade.php` con navegación principal (avisos, galería, contacto, acceso privado) y `LanguageSwitcher`
  - Crear `layouts/admin.blade.php` con sidebar y navegación del panel de administración
  - Definir todas las rutas públicas y de administración en `routes/web.php` según el diseño
  - Añadir las nuevas rutas públicas: `/politica-de-privacidad`, `/aviso-legal`, `/sitemap.xml`, `/robots.txt`
  - Integrar la edición de páginas legales en `/admin/configuracion`
  - Añadir enlaces al footer en `layouts/public.blade.php` para política de privacidad y aviso legal
  - Implementar el stack Blade `@stack('meta')` en el layout público para inyectar título y meta description por página
  - Crear páginas de error personalizadas: `errors/404.blade.php` y `errors/500.blade.php` con soporte i18n
  - Crear la vista placeholder para `/privado` que redirige o muestra mensaje según el estado de autenticación
  - _Requisitos: 5.1, 5.2, 5.3, 6.5, 9.2, 15.1, 15.2, 16.1, 16.2, 16.4, 16.5_

- [x] 4. Implementar la parte pública — Avisos
  - Crear el componente Livewire `PublicNotices` con listado paginado de avisos públicos (`is_public=true`)
  - Añadir scope `public()` al modelo `Notice` para filtrar solo avisos publicados
  - Implementar filtro por portal/planta en el componente `PublicNotices`
  - Mostrar mensaje de "no hay avisos" cuando la lista esté vacía
  - Mostrar indicador visual cuando un aviso no tiene traducción al idioma activo (fallback)
  - Crear la vista Blade correspondiente con TailwindCSS
  - _Requisitos: 2.1, 2.2, 2.3, 2.4, 2.5, 2.8, 4.1, 4.2, 4.3, 4.5, 4.6_

  - [ ]\* 4.1 Escribir test de propiedad para visibilidad de avisos públicos
    - **Propiedad 3: Solo avisos públicos visibles en la parte pública**
    - **Valida: Requisitos 2.1, 2.2, 2.3**

  - [ ]\* 4.2 Escribir tests de ejemplo para la sección de avisos
    - Verificar que avisos con `is_public=false` no aparecen en la parte pública
    - Verificar que el indicador de "sin traducción" se muestra cuando corresponde
    - _Requisitos: 2.3, 2.5_

  - [ ]\* 4.3 Escribir test de propiedad para paginación de avisos
    - **Propiedad 18: Paginación de avisos**
    - **Valida: Requisitos 2.6, 2.7**

  - [ ]\* 4.4 Escribir test de propiedad para filtrado por ubicación
    - **Propiedad 19: Filtrado por ubicación**
    - **Valida: Requisitos 4.5, 4.6**

- [x] 5. Implementar la parte pública — Galería de imágenes
  - Crear el componente Livewire `ImageGallery` con grid de imágenes y lightbox
  - Mostrar mensaje de "no hay imágenes" cuando la galería esté vacía
  - Asegurar que cada imagen renderiza el atributo `alt` con el texto del locale activo
  - Crear la vista Blade correspondiente con TailwindCSS
  - _Requisitos: 3.1, 3.2, 3.3, 3.4, 3.6, 3.7, 8.2_

  - [ ]\* 5.1 Escribir test de propiedad para round-trip de imagen
    - **Propiedad 5: Round-trip de imagen (subir / eliminar)**
    - **Valida: Requisitos 3.2, 3.3**

  - [ ]\* 5.2 Escribir test de propiedad para alt text en galería
    - **Propiedad 6: Alt text presente en todas las imágenes de la galería**
    - **Valida: Requisito 3.4**

- [x] 6. Checkpoint — Verificar que todos los tests pasan hasta este punto
  - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [x] 7. Implementar el formulario de contacto
  - Crear las clases Mailable: `ContactConfirmation` y `ContactNotification` con sus vistas Blade
  - Crear el componente Livewire `ContactForm` con las reglas de validación definidas en el diseño
  - Implementar la integración con reCAPTCHA v3: solicitar token en el cliente y verificar score en el servidor
  - Implementar la lógica de envío: guardar `ContactMessage`, despachar Mailables, manejar excepciones de email
  - Endurecer el submit para ignorar dobles clics rápidos o reenvíos inmediatos del mismo payload sin duplicar persistencia ni correos
  - Implementar la limpieza de campos tras envío exitoso y el mensaje de confirmación
  - Crear la vista Blade del formulario con TailwindCSS, incluyendo el checkbox legal con enlace configurable
  - _Requisitos: 10.1, 10.2, 10.3, 10.4, 10.5, 10.6, 10.10, 11.1, 11.2, 11.3, 11.4, 11.5, 12.1, 12.2, 12.3, 13.1, 13.2, 13.3_

  - [ ]\* 7.1 Escribir test de propiedad para validación del formulario con entradas inválidas
    - **Propiedad 9: Validación del formulario de contacto rechaza entradas inválidas**
    - **Valida: Requisitos 10.3, 10.4**

  - [ ]\* 7.2 Escribir test de propiedad para limpieza de campos tras envío exitoso
    - **Propiedad 10: Limpieza de campos tras envío exitoso**
    - **Valida: Requisito 10.6**

  - [ ]\* 7.3 Escribir test de propiedad para emails despachados con contenido correcto
    - **Propiedad 11: Emails despachados con contenido correcto tras envío válido**
    - **Valida: Requisitos 11.1, 11.2, 11.3, 11.4**

  - [ ]\* 7.4 Escribir test de propiedad para rechazo con score de reCAPTCHA bajo
    - **Propiedad 12: Rechazo de envío con score de reCAPTCHA bajo**
    - **Valida: Requisito 12.2**

  - [ ]\* 7.5 Escribir test de propiedad para checkbox legal obligatorio
    - **Propiedad 13: Checkbox legal obligatorio para envío**
    - **Valida: Requisitos 13.1, 13.3**

  - [ ]\* 7.6 Escribir tests de ejemplo para el formulario de contacto
    - Happy path: envío válido guarda mensaje y despacha emails
    - Fallo de envío de email: se registra en log y se muestra advertencia al visitante
    - Fallo de reCAPTCHA por error externo: envío rechazado
    - _Requisitos: 10.5, 11.5, 12.3_

- [x] 8. Implementar autenticación del panel de administración
  - Configurar Laravel Breeze (stack Blade) limitado al guard `web`
  - Proteger todas las rutas `/admin/*` con el middleware `auth`
  - Personalizar las vistas de login con TailwindCSS y soporte i18n
  - Crear el seeder del usuario administrador inicial
  - _Requisitos: 6.5, 6.6_

  - [ ]\* 8.1 Escribir tests de ejemplo para autenticación
    - Login correcto redirige al dashboard admin
    - Login incorrecto muestra mensaje de error y no concede acceso
    - Acceso a ruta admin sin autenticación redirige al login
    - _Requisitos: 6.5, 6.6_

- [x] 9. Implementar el panel de administración — Gestión de avisos
  - Crear el componente Livewire `AdminNoticeManager` con listado, creación, edición y eliminación de avisos
  - Implementar formulario con campos bilingües (EU + ES), selector múltiple de portales/plantas y toggle de publicación
  - Implementar la acción de publicar/despublicar sin eliminar el aviso
  - Crear las vistas Blade del panel con `layouts/admin.blade.php` y TailwindCSS
  - _Requisitos: 6.1, 6.3, 6.4_

  - [ ]\* 9.1 Escribir test de propiedad para toggle de publicación reversible
    - **Propiedad 8: Toggle de publicación de avisos es reversible**
    - **Valida: Requisito 6.4**

  - [ ]\* 9.2 Escribir tests de ejemplo para CRUD de avisos
    - Crear aviso, verificar que aparece en la lista admin
    - Publicar aviso, verificar que es visible en la parte pública
    - Despublicar aviso, verificar que desaparece de la parte pública
    - Eliminar aviso, verificar que desaparece de la lista admin
    - _Requisitos: 6.1, 6.4_

- [ ] 10. Implementar el panel de administración — Gestión de imágenes
  - Crear el componente Livewire `AdminImageManager` con grid de miniaturas, subida y eliminación
  - Implementar subida con `TemporaryUploadedFile` de Livewire y campos de alt text bilingüe
  - Integrar la vista `/admin/imagenes` con la gestión administrativa real de imágenes
  - _Requisitos: 6.2_

  - [ ]\* 10.1 Escribir tests de ejemplo para gestión de imágenes
    - Subir imagen, verificar que aparece en la galería pública
    - Eliminar imagen, verificar que desaparece de la galería pública
    - _Requisitos: 6.2, 3.2, 3.3_

- [-] 11. Implementar el panel de administración — Bandeja de mensajes
  - Crear el componente Livewire `AdminMessageInbox` con tabla de mensajes ordenable
  - Implementar la apertura de mensaje con marcado automático como leído
  - Implementar el toggle manual de estado leído/no leído
  - Implementar la diferenciación visual de mensajes no leídos con TailwindCSS
  - Implementar la eliminación con modal de confirmación (`$confirmingDelete`)
  - Implementar la ordenación por fecha de recepción y por estado de lectura
  - _Requisitos: 14.1, 14.2, 14.3, 14.4, 14.5, 14.6, 14.7, 14.8_

  - [ ]\* 11.1 Escribir test de propiedad para completitud de la bandeja de mensajes
    - **Propiedad 14: Completitud de la bandeja de mensajes**
    - **Valida: Requisito 14.1**

  - [ ]\* 11.2 Escribir test de propiedad para campos requeridos en lista y detalle
    - **Propiedad 15: Campos requeridos presentes en lista y detalle de mensajes**
    - **Valida: Requisitos 14.2, 14.3**

  - [ ]\* 11.3 Escribir test de propiedad para toggle de estado de lectura reversible
    - **Propiedad 16: Toggle de estado de lectura es reversible con diferenciación visual**
    - **Valida: Requisitos 14.4, 14.5**

  - [ ]\* 11.4 Escribir test de propiedad para ordenación de mensajes
    - **Propiedad 17: Ordenación de mensajes produce secuencia correcta**
    - **Valida: Requisito 14.8**

  - [ ]\* 11.5 Escribir tests de ejemplo para la bandeja de mensajes
    - Listar mensajes, verificar que aparecen todos
    - Abrir mensaje, verificar que se marca como leído automáticamente
    - Eliminar mensaje con confirmación en modal
    - _Requisitos: 14.1, 14.3, 14.6, 14.7_

- [x] 12. Implementar el panel de administración — Configuración
  - Crear el componente Livewire `AdminSettings` con formulario de configuración
  - Implementar lectura y escritura de la tabla `settings` para: email admin, claves reCAPTCHA, texto legal (EU+ES) y URL legal
  - Asegurar que el campo de clave privada reCAPTCHA se renderiza como `type="password"`
  - Crear el dashboard admin con resumen de avisos, imágenes y mensajes no leídos
  - _Requisitos: 11.6, 12.4, 13.4_

  - [ ]\* 12.1 Escribir tests de ejemplo para configuración de settings
    - Guardar email admin, verificar que se usa en notificaciones de contacto
    - Guardar claves reCAPTCHA, verificar que se usan en la verificación
    - _Requisitos: 11.6, 12.4_

- [x] 13. Implementar páginas legales, SEO y seguridad
  - Crear el componente Livewire `AdminLegalPages` para editar el contenido de política de privacidad y aviso legal (EU + ES)
  - Crear las vistas Blade públicas para `/politica-de-privacidad` y `/aviso-legal` que muestran el contenido del locale activo, permitiendo compartir una plantilla reutilizable para ambas rutas
  - Implementar el controlador/ruta para `/sitemap.xml` que genera dinámicamente las URLs de la parte pública
  - Implementar el archivo `robots.txt` que permite la parte pública y bloquea `/admin`
  - Crear el middleware de cabeceras de seguridad HTTP (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) y registrarlo en el grupo `web`
  - Configurar la expiración de sesión admin (120 minutos por defecto, configurable en `.env`)
  - Inyectar título de página y meta description únicos en cada vista pública mediante `@push('meta')`
  - _Requisitos: 15.1, 15.2, 15.3, 15.4, 15.5, 16.1, 16.2, 16.3, 16.4, 16.5, 17.1, 17.2, 17.3, 17.4, 17.5_

  - [ ]\* 13.1 Escribir test de propiedad para cabeceras de seguridad
    - **Propiedad 20: Cabeceras de seguridad presentes en todas las respuestas**
    - **Valida: Requisito 17.3**

  - [ ]\* 13.2 Escribir test de propiedad para expiración de sesión admin
    - **Propiedad 21: Expiración de sesión admin**
    - **Valida: Requisito 17.4**

  - [ ]\* 13.3 Escribir tests de ejemplo para páginas legales
    - Verificar acceso público a `/politica-de-privacidad` y `/aviso-legal`
    - Verificar que el contenido se muestra en el idioma activo
    - Verificar que el administrador puede editar el contenido desde `/admin/configuracion`
    - _Requisitos: 15.1, 15.2, 15.3, 15.4_

- [x] 14. Checkpoint — Verificar que todos los tests pasan
  - Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

- [x] 15. Implementar tests de navegador con Laravel Dusk
  - Configurar Laravel Dusk con ChromeDriver y el fichero `.env.dusk.local` con reCAPTCHA deshabilitado o con test keys de Google
  - Escribir test Dusk: cambio de idioma EU/ES y verificación de textos de interfaz
  - Escribir test Dusk: navegación pública por avisos, galería y contacto
  - Escribir test Dusk: flujo completo del formulario de contacto (rellenar, enviar, verificar confirmación)
  - Escribir test Dusk: login del administrador con credenciales válidas y redirección al dashboard
  - Escribir test Dusk: CRUD de avisos desde el panel admin (crear, publicar, verificar en parte pública, despublicar, eliminar)
  - Escribir test Dusk: leer mensaje en bandeja (verificar marcado como leído) y eliminarlo con confirmación
  - Escribir test Dusk: verificar que `/privado` muestra el placeholder correcto o redirige según lo esperado
  - Escribir test Dusk: footer con enlaces a política de privacidad y aviso legal
  - Escribir test Dusk: páginas `/politica-de-privacidad` y `/aviso-legal` se muestran en el idioma activo seleccionado
  - Escribir test Dusk: `/sitemap.xml` es accesible públicamente y contiene URLs de la parte pública
  - Escribir test Dusk: filtro por portal/planta en `/avisos` filtra los resultados en tiempo real
  - Comprobar que todos los test Dusk generados pasan y guardar imagen de cada uno
  - _Requisitos: 1.2, 1.3, 2.1, 3.1, 5.2, 5.3, 6.1, 6.2, 6.4, 10.5, 14.3, 14.6, 15.1, 15.2, 15.4, 16.4, 4.5, 4.6_

- [x] 16. Integración final y ajustes de accesibilidad y responsive
  - Revisar que todas las vistas públicas son navegables por teclado (Requisito 8.3)
  - Verificar que todos los `<img>` tienen atributo `alt` correcto en ambos idiomas (Requisito 8.2)
  - Verificar diseño responsive en las vistas principales con TailwindCSS (Requisito 7.2)
  - Asegurar que no se han introducido librerías de estilos adicionales que conflicten con TailwindCSS (Requisito 9.5)
  - Conectar el `LanguageSwitcher` en ambos layouts y verificar que el locale persiste en todas las secciones
  - _Requisitos: 7.2, 8.2, 8.3, 9.5_

- [x] 17. Checkpoint final — Todos los tests pasan
  - Ejecutar la suite completa (Pest + Dusk). Asegurarse de que todos los tests pasan. Consultar al usuario si surgen dudas.

## Notas

- Las tareas marcadas con `*` son opcionales y pueden omitirse para un MVP más rápido
- Cada tarea referencia los requisitos específicos para trazabilidad
- `->repeat()` solo debe usarse en tests realmente aleatorios y con un máximo de 2 iteraciones (`.repeat(2)`).
- Los tests de navegador Dusk requieren ChromeDriver; en CI usar las test keys oficiales de Google para reCAPTCHA
- La base de datos en desarrollo/staging es SQLite; en producción MySQL (solo configuración `.env`)
- Todos los comandos Artisan, Composer y npm se ejecutan dentro del contenedor: `docker compose exec madaia33 <comando>`
- Las variables `DC_UID` y `DC_GID` deben coincidir con el usuario del host para evitar problemas de permisos en los volúmenes
