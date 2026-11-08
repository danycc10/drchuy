# Estado de implementación

Fecha: 8 de noviembre de 2026. Primera entrega funcional local. El plan completo sigue en desarrollo.

## Implementado

- Repositorio Git con commits separados para documentación, base técnica, acceso, edición y sitio.
- Laravel 13.40.0 sobre PHP 8.3.28; MySQL local 8.4.3, base exclusiva `drchuy_web`.
- Catálogo de 14 servicios ES/EN en cinco categorías, importación repetible y estado inicial de borrador.
- Panel propio en Blade/Tailwind: acceso, recuperación de contraseña, tablero, servicios, filtros, paginación, revisión de versiones y actividad.
- Roles propietario, administrador y editor; creación de cuentas mediante comando interactivo. Sesión bloqueada si la cuenta está inactiva.
- Borradores separados de la versión pública; publicación explícita, recuperación como nuevo borrador, bitácora y rechazo de ediciones desactualizadas.
- Restricción de publicación de procedimientos al propietario.
- Sitio ES/EN con fotografías reales, identidad original, contacto, horarios, instalaciones y aviso de urgencias. El catálogo público lee únicamente versiones publicadas y visibles.
- Vista previa privada de borradores, etiquetas SEO/OG/hreflang, robots y sitemap controlados por configuración.
- Derivados WebP y recorte exclusivo de márgenes transparentes de logos. Los archivos originales permanecen intactos.
- Guía de uso y preparación del despliegue.

## Comprobaciones

- 15 pruebas automatizadas de funcionalidad: importación sin duplicados, acceso, cuentas inactivas, límite de intentos, recuperación y uso único de tokens, roles, revisiones, conflictos de edición, traducciones, escape de contenido, visibilidad pública y vistas privadas.
- Pruebas aisladas en SQLite en memoria; migración e importación ejecutadas también sobre MySQL local.
- Compilación de recursos para producción correcta.
- Composer válido, requisitos de plataforma local satisfechos y auditoría PHP sin avisos de vulnerabilidad al comprobarla. Auditoría npm de dependencias de producción sin vulnerabilidades reportadas.
- Sitio ES y formulario de acceso comprobados por HTTP local con respuesta 200 y cabeceras de no indexación.
- Revisión visual interactiva, Android/iPhone y mediciones Lighthouse pendientes: el control de navegador disponible en esta sesión informó que no hay navegador conectado. No se ha verificado visualmente el resultado renderizado.

## Siguiente bloque

1. Revisar visualmente el sitio y el panel en escritorio y móvil, y ajustar el patrón de diseño.
2. Incorporar administración de páginas, perfil, contacto y horarios. Actualmente esos textos y datos se mantienen en `config/clinic.php`.
3. Incorporar biblioteca de medios con validación de cargas, derivados y control de usos. Por ahora las imágenes originales se preparan mediante script local.
4. Incorporar administración de usuarios por navegador y segundo factor con un componente mantenido.
5. Ampliar el flujo editorial al resto de módulos y añadir solicitudes explícitas de revisión. El módulo de servicios actual permite guardar, revisar, publicar y recuperar.
6. Edición SEO, marcado estructurado validado, contenido legal aprobado y pruebas de accesibilidad/rendimiento.
7. Respaldos automatizados y restauración ensayada, medición de clics si se aprueba y revisión final de publicación.

## Pendientes externos

- Cuenta propietaria local creada con el correo proporcionado por el titular y enlace temporal para elegir contraseña. Los datos de acceso se mantienen fuera del repositorio.
- HostGator Turbo: cuenta contratada, sin acceso proporcionado ni comprobación de PHP/CLI, extensiones, SSH, raíz pública, límites, correo y cron.
- Dominio, correo operativo, aviso de privacidad, traducción inglesa y condiciones de procedimientos por confirmar.
- Repositorio remoto: no configurado. Los commits se encuentran en este equipo.

No activar indexación ni desplegar esta entrega como versión final antes de completar los pendientes de lanzamiento. `SITE_INDEXABLE=false` impide indexación solicitada, pero no sustituye una contraseña o restricción de acceso para un entorno de pruebas remoto.
