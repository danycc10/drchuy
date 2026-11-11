# Revisión de seguridad — 11 de noviembre de 2026

Revisión del código Laravel, rutas, permisos, autenticación, sesiones, publicación, biblioteca de imágenes, respaldos y preparación de despliegue. Incluye pruebas automatizadas aisladas y consultas HTTP al servidor local. No se accedió a HostGator ni se modificaron contraseñas, la clave de aplicación, la configuración TOTP o el archivo `.env` real.

## Hallazgo corregido: revocación durante una solicitud

Una solicitud que ya había cargado la identidad del usuario podía terminar una escritura aunque otra operación hubiera revocado sus permisos. En creación de cuentas y generación de enlaces, dos pruebas reprodujeron el problema antes de la corrección: se creaba la cuenta o el token aun después de cambiar el rol/estado en la base. Requería una identidad previamente autenticada, su contraseña para las operaciones de cuentas y coincidir con una revocación; no era un acceso anónimo al panel.

Ahora las operaciones sensibles vuelven a leer y bloquear la cuenta dentro de la transacción y comprueban su estado, versión de autenticación y permiso actual. El control se aplica también a borradores, publicación, revisión editorial, orden de servicios y cargas/eliminación de imágenes. El orden de bloqueos empieza por la cuenta y después el contenido para mantener consistencia. Una carga rechazada elimina sus archivos temporales y devuelve 403.

- `83103e8`: revalidación del propietario para cuentas y enlaces.
- `d1ec4df`: revalidación de la identidad en escrituras editoriales y medios.

Las pruebas simulan una identidad cargada antes de la revocación; no constituyen un ensayo de carga concurrente sobre el MySQL de HostGator.

## Refuerzos adicionales

- `93705eb`: en producción se acepta únicamente el hostname exacto de `APP_URL`, sin confianza automática en subdominios. Los alias deben redirigirse en el servidor. Se utiliza [TrustHosts de Laravel](https://github.com/laravel/docs/blob/13.x/requests.md#configuring-trusted-hosts).
- Desactivadas las rutas genéricas GET/PUT del disco privado mediante `serve=false`. El sitio no necesita [URLs temporales del disco local](https://laravel.com/framework/docs/13.x/filesystem#temporary-urls): originales y borradores siguen pasando por los controladores y permisos de la aplicación. Esto reduce superficie; las rutas anteriores requerían firma, no se confirmó acceso anónimo a archivos privados.
- La lista de orden de servicios exige índices consecutivos y limita el tamaño del mapa de revisiones. Evita entradas asociativas inesperadas y errores internos.
- `app:launch-check` comprueba además HttpOnly, SameSite Lax/Strict y que el disco privado no habilite rutas genéricas.

## Evidencia

- `composer audit --locked --format=json`: sin avisos de vulnerabilidades conocidas ni paquetes abandonados al consultar.
- `npm audit --json`: cero vulnerabilidades conocidas reportadas.
- Suite Laravel: **93 pruebas, 1,376 aserciones**, todas correctas. Incluye permisos, revocación, recuperación de contraseña, TOTP, protección de revisiones, aislamiento de imágenes, escape de contenido y respaldos.
- Prueba CSRF con el middleware activo: solicitudes de cambio desde otro origen sin token válido devuelven 419; una solicitud con token válido funciona.
- Prueba de producción: el dominio configurado responde, un host distinto y un subdominio no autorizado devuelven 400.
- HTTP local sin autenticación: `/` devuelve 200; `/.env`, `/.git/HEAD`, `/composer.json`, `/storage/logs/laravel.log` y una ruta de respaldo de prueba devuelven 404. Vista previa, exportación y usuarios devuelven 302 hacia el acceso del panel.
- No se cambiaron dependencias ni archivos del frontend en esta revisión. La última verificación de JavaScript sigue siendo 42 pruebas y compilación correctas de la entrega anterior.

## Pendiente para producción

El chequeo local todavía marca pendientes `APP_ENV=production`, `APP_DEBUG=false`, dominio HTTPS, cookies Secure, SMTP, dos pasos para propietarios activos, aviso de privacidad y programación de respaldos. El entorno local está preparado para desarrollo y no debe copiarse tal cual al servidor público.

Antes del despliegue, verificar el document root en `public/`, extensiones y versiones del hosting, permisos de archivos, HTTPS, envío de correo, cron y copia externa. [Laravel advierte sobre servir el proyecto desde su raíz y exponer archivos sensibles](https://github.com/laravel/docs/blob/13.x/deployment.md#nginx). Seguir [HOSTGATOR.md](HOSTGATOR.md) y repetir las auditorías de dependencias con la entrega definitiva.

Esta revisión no es una garantía de ausencia de vulnerabilidades ni una prueba de penetración del hosting. No se verificaron infraestructura remota, WAF, aislamiento entre cuentas del servidor compartido o restauración en HostGator.
