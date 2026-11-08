# Medicina Respiratoria — aplicación Laravel

Panel propio en Laravel, Blade y Tailwind; MySQL en desarrollo y producción. El plan y los materiales de origen están en la carpeta superior. No usar esa carpeta como raíz pública.

## Instalación local

Requisitos: PHP 8.3 compatible con las dependencias, Composer 2, MySQL y Node para compilar recursos.

1. Ejecutar `composer install` en esta carpeta.
2. Copiar `.env.example` a `.env`, configurar una base exclusiva y ejecutar `php artisan key:generate`.
3. Ejecutar `php artisan migrate --seed`. La importación crea 14 servicios como borradores; no crea usuarios ni sobrescribe contenido existente.
4. Ejecutar `npm ci` y `npm run build`. En PowerShell con restricción de scripts, usar `npm.cmd`.
5. Ejecutar `php artisan serve --host=127.0.0.1 --port=8085`, o configurar Laragon con raíz en `app-web/public`.

## Comprobaciones

`php artisan test` utiliza SQLite en memoria, separado de MySQL local. `npm run build` genera los recursos para producción. Los archivos `.env`, dependencias, bases locales y logs no se versionan. Los archivos lock sí se incluyen.

El contenido inicial se conserva en `database/content/services.json`. Se puede regenerar desde el paquete original mediante `php scripts/prepare-content.php`; no ejecutar esa preparación durante un despliegue.

El proyecto se encuentra en desarrollo. Consultar el estado de implementación antes de preparar una publicación.
