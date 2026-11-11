<?php

/*
 * Punto de entrada para cPanel cuando el dominio obliga a servir desde public_html.
 * La aplicación completa vive fuera de la raíz web (por ejemplo ~/medicina-respiratoria) y en
 * public_html solo se copian los archivos de app-web/public más este archivo y su .htaccess.
 * Ajustar APP_DIRECTORY si la carpeta de la aplicación recibe otro nombre.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

const APP_DIRECTORY = 'medicina-respiratoria';

$appPath = dirname(__DIR__).'/'.APP_DIRECTORY;

if (! is_file($appPath.'/vendor/autoload.php')) {
    http_response_code(503);
    header('Content-Type: text/plain; charset=utf-8');
    exit('La aplicación no está instalada en la carpeta esperada.');
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = $appPath.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require $appPath.'/vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once $appPath.'/bootstrap/app.php';

// Los recursos compilados (build/), imágenes e iconos se sirven desde esta carpeta.
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
