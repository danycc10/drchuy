# Instalar en HostGator

Dominio principal: **https://medicinarespiratoriadrjesus.com**. Este paquete es para una instalación nueva y contiene código, dependencias de producción y recursos compilados. No incluye la base de datos, usuarios, imágenes subidas al panel ni credenciales del equipo local. El catálogo inicial se importa como borrador y debe revisarse/publicarse desde el panel.

## 1. Preparar cPanel

1. Asociar el dominio a la cuenta y configurar sus DNS según los valores reales de HostGator. Confirmar que su raíz es `/home/USUARIO/public_html`. Si es un dominio adicional con otra raíz, adaptar `bootstrap/public-path.php` y el `$appPath` de `index.php` antes de instalar.
2. Activar AutoSSL para el dominio y `www`. El paquete redirige HTTP y `www` al dominio principal con HTTPS. Probar el certificado antes de abrir el sitio. Estas reglas suponen HTTPS directo en Apache; si se usa CDN/proxy, verificar su configuración antes de activarlo.
3. Seleccionar PHP **8.3 o superior compatible con composer.lock**, también para Terminal/cron. Confirmar extensiones con `composer check-platform-reqs --no-dev`; hacen falta, entre otras, PDO MySQL, GD con WebP, EXIF, XMLWriter y ZIP con AES-256. Ver [requisitos oficiales de Laravel](https://laravel.com/docs/13.x/deployment).
4. En MultiPHP INI Editor: `upload_max_filesize=8M`, `post_max_size=12M`, `memory_limit=256M`, `max_execution_time=120`.
5. Crear base MySQL y usuario exclusivos, asignándoles permisos sobre esa base. Usar los nombres completos, incluido el prefijo de cPanel. Crear/configurar un buzón SMTP y obtener sus parámetros reales.

## 2. Subir el ZIP fuera de public_html

Respaldar primero cualquier sitio existente. Subir y extraer el ZIP en `/home/USUARIO`, no dentro de `public_html`. La estructura debe quedar:

```text
/home/USUARIO/medicina-respiratoria/   código privado, vendor, storage y .env
/home/USUARIO/public_html/            index.php, .htaccess, build, imágenes e iconos
```

Mostrar archivos ocultos en el administrador de cPanel para comprobar `.htaccess`. Conservar la configuración PHP que cPanel agregue a ese archivo. No subir el repositorio completo ni crear enlaces públicos a `storage`. No usar permisos 777: el usuario PHP debe poder escribir únicamente en `storage` y `bootstrap/cache` (normalmente directorios 755, archivos 644; `.env` 600 si el usuario de PHP lo permite).

## 3. Configurar la aplicación en Terminal

Los comandos usan `php`; confirmar primero `php -v`. Si cPanel utiliza otro ejecutable, sustituirlo en todos los comandos y en cron. Si Composer no está instalado, el paquete ya incluye `vendor`, pero pedir al hosting que verifique todas las extensiones requeridas antes de continuar.

```bash
cd ~/medicina-respiratoria
php -v
composer check-platform-reqs --no-dev
cp .env.production.example .env
chmod 600 .env
```

Editar `.env` desde cPanel o el editor de Terminal: completar `DB_*`, contraseña SMTP, host/puerto/esquema reales y remitente autorizado. Los valores SMTP del ejemplo son propuestas, no cuentas creadas. Poner entre comillas las contraseñas que contengan espacios o `#`. Conservar `APP_DEBUG=false`, `APP_ENV=production`, cookies seguras y `SITE_INDEXABLE=false` durante la revisión. No compartir este archivo por chat ni guardarlo en una carpeta pública.

Solo para instalación nueva, con una base vacía:

```bash
php artisan key:generate --force
php artisan down
php artisan migrate --force
php artisan db:seed --force
php artisan app:create-admin
php artisan optimize
php artisan up
```

En `app:create-admin`, usar **danycc10@gmail.com**, rol **owner** y una contraseña nueva que el propietario elija. No se incluye ninguna contraseña predeterminada. Conservar `APP_KEY` en un respaldo privado: no regenerarla en actualizaciones ni al trasladar datos existentes. `down` mantiene el sitio cerrado durante la preparación; `up` lo abre para revisión y no activa por sí mismo la indexación.

## 4. Revisar antes del lanzamiento

1. Abrir `/admin/login`, activar dos pasos en **Mi seguridad** y guardar los códigos de recuperación.
2. Revisar y publicar servicios, textos, datos médicos, horarios, contacto e imágenes. Publicar el aviso de privacidad aprobado en los idiomas disponibles. La instalación nueva no replica las ediciones de la base local; para trasladarlas usar el procedimiento privado de `docs/RESPALDOS.md`, conservando su APP_KEY, en lugar de regenerar una clave y sembrar datos.
3. Probar desde celular y escritorio: tarjetas/filtros de servicios, WhatsApp con el mensaje del servicio, llamadas, idioma sin prefijos, formulario de acceso, imágenes y recuperación de contraseña con correo recibido realmente.
4. Confirmar redirecciones HTTP y `www`, certificado válido, `/up` y que `/.env`, `/composer.json` y `/storage/logs/laravel.log` no exponen archivos. No publicar `phpinfo()` ni instaladores web.
5. Configurar y probar copias cifradas y restauración siguiendo `docs/RESPALDOS.md`, además de una copia externa al hosting. Activar `BACKUP_ENABLED=true` solo al preparar este proceso y ejecutar `php artisan config:cache`.
6. En trabajos cron de cPanel, cada minuto (sustituir USUARIO y la ruta de PHP por las reales):

```cron
* * * * * /RUTA/REAL/php /home/USUARIO/medicina-respiratoria/artisan schedule:run >> /home/USUARIO/medicina-respiratoria/storage/logs/cron.log 2>&1
```

7. Ejecutar `php artisan app:launch-check`. Resolver los pendientes; este comando no demuestra entrega de correo, ejecución real de cron ni aprobación médica/legal. Revisar también permisos, cuotas y rotación de `cron.log`.
8. Tras aprobar la revisión, cambiar `SITE_INDEXABLE=true`, ejecutar `php artisan config:cache`, comprobar `/robots.txt` y `/sitemap.xml` y registrar el sitio en Search Console si corresponde.

## Actualizaciones

No repetir la instalación inicial sobre un sitio activo. Respaldar base, archivos privados y `.env`; conservar la entrega anterior. Activar mantenimiento, reemplazar código/vendor y archivos públicos sin borrar `.env` ni `storage`, ejecutar `php artisan optimize:clear`, `php artisan migrate --force`, `php artisan optimize` y `php artisan up`. Mantener la misma APP_KEY. Si hay un fallo, restaurar código y, si el esquema cambió, la copia compatible de base/datos. No ejecutar `migrate:fresh` ni regenerar claves en producción.

## Reconstruir el paquete (equipo de desarrollo)

Desde la raíz del repositorio, con PHP, Composer, Node/npm y Git disponibles:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File app-web/deploy/build-release.ps1
```

La entrega y su ZIP quedan en `.local/releases/`, fuera de Git. El script compila assets, instala dependencias sin desarrollo en una carpeta independiente, verifica que arranque Artisan y rechaza datos locales al empaquetar. Ejecutar las pruebas del proyecto antes de construir una entrega. No requiere Node en HostGator.
