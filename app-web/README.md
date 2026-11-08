# Medicina Respiratoria — aplicación Laravel

Panel propio en Laravel, Blade y Tailwind; MySQL en desarrollo y producción. El plan y los materiales de origen están en la carpeta superior. No usar esa carpeta como raíz pública.

## Instalación local

Requisitos: PHP 8.3 compatible con las dependencias, Composer 2, MySQL y Node para compilar recursos.

1. Ejecutar `composer install` en esta carpeta.
2. Copiar `.env.example` a `.env`, configurar una base exclusiva y ejecutar `php artisan key:generate`.
3. Ejecutar `php artisan migrate --seed`. La importación crea 14 servicios como borradores; no crea usuarios ni sobrescribe contenido existente.
4. Ejecutar `npm ci` y `npm run build`. En PowerShell con restricción de scripts, usar `npm.cmd`.
5. Ejecutar `php artisan serve --host=127.0.0.1 --port=8085`, o configurar Laragon con raíz en `app-web/public`.
6. Crear una cuenta con `php artisan app:create-admin` en otra terminal. Elegir el rol `owner` para el titular. No se crean cuentas predeterminadas.

`composer dev` inicia el servidor local en el puerto 8085. Si se cambian estilos o JavaScript, ejecutar `npm.cmd run build` o usar `npm.cmd run dev` en otra terminal para desarrollo. Ningún proceso de Node es necesario en producción.

## Comprobaciones

`php artisan test` utiliza SQLite en memoria, separado de MySQL local. `npm run build` genera los recursos para producción. Los archivos `.env`, dependencias, bases locales y logs no se versionan. Los archivos lock sí se incluyen.

El contenido inicial se conserva en `database/content/services.json`. Se puede regenerar desde el paquete original mediante `php scripts/prepare-content.php`; no ejecutar esa preparación durante un despliegue.

Las imágenes de `public/images` se preparan con `php scripts/prepare-assets.php` usando GD y el paquete original. El script genera WebP y recorta únicamente márgenes transparentes de los logos. No altera los archivos originales.

Consultar `docs/PANEL.md` y `docs/HOSTGATOR.md`. La base de datos local no contiene cuentas predeterminadas. Los servicios se revisan desde la vista previa privada y se publican explícitamente desde el panel.

El proyecto se encuentra en desarrollo. Consultar el estado de implementación antes de preparar una publicación.
