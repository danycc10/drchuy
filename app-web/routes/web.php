<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;

require __DIR__.'/admin.php';

Route::redirect('/', '/es');
Route::get('/{locale}', [PublicSiteController::class, 'show'])->whereIn('locale', ['es', 'en'])->name('home');
Route::get('/robots.txt', fn () => response(config('clinic.indexable')
    ? "User-agent: *\nDisallow: /admin\nSitemap: ".rtrim(config('app.url'), '/')."/sitemap.xml\n"
    : "User-agent: *\nDisallow: /\n", 200)->header('Content-Type', 'text/plain'));
Route::get('/es/aviso-de-privacidad', fn (PublicSiteController $controller) => $controller->privacy('es'))->name('privacy.es');
Route::get('/en/privacy-policy', fn (PublicSiteController $controller) => $controller->privacy('en'))->name('privacy.en');
Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap']);
