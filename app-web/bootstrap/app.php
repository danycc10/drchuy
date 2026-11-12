<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts(at: fn () => ['^'.preg_quote((string) parse_url(config('app.url'), PHP_URL_HOST), '/').'$'], subdomains: false);
        // Only when the hosting places a proxy or CDN in front: otherwise X-Forwarded-* headers are ignored.
        $proxies = array_values(array_filter(array_map('trim', explode(',', (string) env('TRUSTED_PROXIES', '')))));
        if ($proxies) {
            $middleware->trustProxies(at: in_array('*', $proxies, true) ? '*' : $proxies);
        }
        $middleware->append(\App\Http\Middleware\PublicHeaders::class);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        $middleware->alias([
            'admin.active' => \App\Http\Middleware\ActiveAdministrator::class,
            'admin.headers' => \App\Http\Middleware\AdminHeaders::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['current_password', 'code', 'recovery_code']);
    })->create();

// El paquete de cPanel incluye esta ruta para que HTTP y Artisan usen la misma raíz.
if (is_file($publicPath = __DIR__.'/public-path.php')) {
    $app->usePublicPath(require $publicPath);
}

return $app;
