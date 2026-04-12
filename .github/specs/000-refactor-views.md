## Objetivo

Reorganizar las vistas en dominios admin y front para mejorar mantenibilidad, eliminar estructura legacy de Livewire y mantener una zona shared para elementos transversales, sin romper rutas, componentes Blade ni resolucion de vistas.

## Decisiones Cerradas

1. Incluir tambien las vistas relacionadas con dashboard del panel admin.
2. Mantener layouts compartidos en shared y mover solo layouts especificos de admin/front.
3. Eliminar legacy Livewire en la misma pasada, sin fase de compatibilidad temporal.

## Alcance Incluido

1. Reorganizacion de `resources/views/components`, `resources/views/livewire`, `resources/views/layouts`, `resources/views/partials`, `resources/views/admin`, `resources/views/public`, `resources/views/pages/dashboard.blade.php`.
2. Actualizacion de referencias en Blade y PHP:
3. Includes y extends en vistas Blade.
4. Referencias de componentes x-.
5. `view(...)` y `Route::view(...)` en `routes/web.php`.
6. `Route::livewire(...)` en `routes/settings.php`.
7. `render()` de Livewire en:
8. `app/Livewire/AdminSettings.php`, `app/Livewire/AdminNoticeManager.php`, `app/Livewire/AdminMessageInbox.php`, `app/Livewire/ContactForm.php`, `app/Livewire/ImageGallery.php`, `app/Livewire/HeroSlider.php`, `app/Livewire/PublicNotices.php`.
9. Ajustes de namespaces si aplica en `config/livewire.php`.

## Alcance Excluido

1. No reorganizar `resources/views/errors`, `resources/views/mail`, `resources/views/flux` salvo impacto indirecto por referencias.
2. No introducir cambios funcionales de negocio.
3. No cambiar copy ni diseno salvo ajustes minimos por rutas de vistas.

## Estado Actual Resumido

1. Ya existen subcarpetas admin/front en varios puntos, pero coexisten con legacy.
2. En Livewire hay wrappers legacy en raiz que redirigen a subcarpetas:
3. `resources/views/livewire/admin-settings.blade.php`
4. `resources/views/livewire/admin-notice-manager.blade.php`
5. `resources/views/livewire/admin-message-inbox.blade.php`
6. `resources/views/livewire/contact-form.blade.php`
7. `resources/views/livewire/image-gallery.blade.php`
8. `resources/views/livewire/hero-slider.blade.php`
9. `resources/views/livewire/public-notices.blade.php`
10. En layouts y partials hay piezas compartidas criticas:
11. `resources/views/partials/head.blade.php`
12. `resources/views/layouts/app.blade.php`
13. `resources/views/layouts/auth.blade.php`

## Estructura Objetivo

1. components:
2. admin para componentes solo de panel.
3. front para componentes solo publicos.
4. shared para componentes usados en mas de un dominio.
5. livewire:
6. admin para vistas Livewire de panel.
7. front para vistas Livewire publicas.
8. shared solo si hay componentes realmente transversales.
9. layouts:
10. admin para layout de panel.
11. front para layout publico.
12. shared para app/auth y piezas comunes.
13. partials:
14. admin para fragmentos exclusivos de panel.
15. front para fragmentos exclusivos publicos.
16. shared para fragmentos transversales.
17. dashboard:
18. separar explicitamente dashboard de usuario y dashboard/admin relacionados en rutas y vistas de destino.

## Mapeo De Migracion Propuesto

1. livewire legacy a destino final:
2. `resources/views/livewire/admin-settings.blade.php` se elimina y queda `resources/views/livewire/admin/settings.blade.php`.
3. `resources/views/livewire/admin-notice-manager.blade.php` se elimina y queda `resources/views/livewire/admin/notice-manager.blade.php`.
4. `resources/views/livewire/admin-message-inbox.blade.php` se elimina y queda `resources/views/livewire/admin/message-inbox.blade.php`.
5. `resources/views/livewire/contact-form.blade.php` se elimina y queda `resources/views/livewire/front/contact-form.blade.php`.
6. `resources/views/livewire/image-gallery.blade.php` se elimina y queda `resources/views/livewire/front/image-gallery.blade.php`.
7. `resources/views/livewire/hero-slider.blade.php` se elimina y queda `resources/views/livewire/front/hero-slider.blade.php`.
8. `resources/views/livewire/public-notices.blade.php` se elimina y queda `resources/views/livewire/front/public-notices.blade.php`.
9. layouts:
10. `resources/views/layouts/admin.blade.php` a `resources/views/layouts/admin/main.blade.php`.
11. `resources/views/layouts/public.blade.php` a `resources/views/layouts/front/main.blade.php`.
12. `resources/views/layouts/app.blade.php`, `resources/views/layouts/auth.blade.php`, `resources/views/layouts/app/header.blade.php`, `resources/views/layouts/app/sidebar.blade.php`, `resources/views/layouts/auth/card.blade.php`, `resources/views/layouts/auth/simple.blade.php`, `resources/views/layouts/auth/split.blade.php` quedan en shared.
13. partials:
14. `resources/views/partials/settings/settings-heading.blade.php` a `resources/views/partials/admin/settings-heading.blade.php`.
15. `resources/views/partials/head.blade.php` a `resources/views/partials/shared/head.blade.php`.
16. dashboard:
17. `resources/views/admin/dashboard.blade.php` queda en dominio admin.
18. `resources/views/pages/dashboard.blade.php` queda en dominio usuario/app.
19. Revisar vistas relacionadas de panel en `resources/views/admin/images.blade.php`, `resources/views/admin/messages.blade.php`, `resources/views/admin/notices.blade.php`, `resources/views/admin/settings.blade.php` para consistencia de ubicacion.

## Referencias Que Deben Actualizarse

1. rutas en `routes/web.php` con `view(...)` para admin/public/dashboard.
2. rutas en `routes/settings.php` con `Route::livewire(...)`.
3. `render()` de componentes Livewire en:
4. `app/Livewire/AdminSettings.php`
5. `app/Livewire/AdminNoticeManager.php`
6. `app/Livewire/AdminMessageInbox.php`
7. `app/Livewire/ContactForm.php`
8. `app/Livewire/ImageGallery.php`
9. `app/Livewire/HeroSlider.php`
10. `app/Livewire/PublicNotices.php`
11. includes de partials:
12. `resources/views/layouts/auth/card.blade.php`
13. `resources/views/layouts/auth/simple.blade.php`
14. `resources/views/layouts/auth/split.blade.php`
15. `resources/views/layouts/app/header.blade.php`
16. `resources/views/layouts/app/sidebar.blade.php`
17. includes de settings:
18. `resources/views/pages/settings/⚡profile.blade.php`
19. `resources/views/pages/settings/⚡security.blade.php`
20. `resources/views/pages/settings/⚡appearance.blade.php`
21. config de namespaces si cambia estructura en `config/livewire.php`.
22. fortify si impacta rutas namespaced en `app/Providers/FortifyServiceProvider.php`.

## Riesgos Y Mitigacion

1. Riesgo: Missing view por rutas antiguas.
2. Mitigacion: busqueda global de strings de vistas legacy y reemplazo atomico antes de borrar wrappers.
3. Riesgo: componentes x- no resuelven tras mover carpetas.
4. Mitigacion: validar aliases existentes y ajustar nombres de componente donde corresponda.
5. Riesgo: romper auth/settings por layouts compartidos.
6. Mitigacion: mantener app/auth en shared y validar flujo Fortify completo.
7. Riesgo: romper tabs/parciales de settings admin.
8. Mitigacion: revisar includes en `resources/views/livewire/admin/settings.blade.php` y partials internos.
9. Riesgo: regresiones silenciosas en panel.
10. Mitigacion: ejecutar tests focalizados y revision manual de rutas criticas.

## Plan De Ejecucion

1. Crear arbol destino final admin/front/shared sin borrar todavia legacy.
2. Mover archivos por bloques: components, livewire, layouts, partials, dashboard.
3. Actualizar referencias de Blade y PHP en una sola pasada.
4. Eliminar wrappers legacy de `resources/views/livewire`.
5. Validar que no quedan referencias antiguas.
6. Ejecutar validacion tecnica y pruebas.

## Checklist De Verificacion

1. Busqueda global sin resultados para:
2. `livewire.admin-settings`
3. `livewire.admin-notice-manager`
4. `livewire.admin-message-inbox`
5. `livewire.contact-form`
6. `livewire.image-gallery`
7. `livewire.hero-slider`
8. `livewire.public-notices`
9. `route:list` en Docker sin errores de resolucion de vistas.
10. Tests focalizados de panel/front/settings.
11. Navegacion manual validada:
12. home, notices, gallery, contact, private.
13. admin dashboard, notices, images, messages, settings.
14. dashboard de usuario y settings de cuenta.
15. Problems de VS Code revisados en archivos tocados.

## Criterio De Aceptacion

1. Estructura final separada por dominios admin/front/shared en carpetas objetivo.
2. Sin wrappers legacy Livewire en raiz.
3. Cero errores de resolucion de vistas y componentes en rutas criticas.
4. Tests focalizados en verde.
5. Sin nuevos errores en Problems de los archivos tocados.
