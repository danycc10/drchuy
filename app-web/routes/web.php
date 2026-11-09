<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;

require __DIR__.'/admin.php';

Route::get('/', [PublicSiteController::class, 'show'])->name('home');
Route::get('/media/{media}/{variant}', [\App\Http\Controllers\Admin\MediaController::class, 'publicImage'])->whereIn('variant', ['display', 'thumb'])->name('media.image');
Route::post('/idioma', [PublicSiteController::class, 'changeLanguage'])->name('language.update');
Route::get('/aviso-de-privacidad', [PublicSiteController::class, 'showPrivacy'])->name('privacy');
Route::get('/{locale}', [PublicSiteController::class, 'legacy'])->whereIn('locale', ['es', 'en']);
Route::get('/robots.txt', fn () => response(config('clinic.indexable')
    ? "User-agent: *\nDisallow: /admin\nSitemap: ".rtrim(config('app.url'), '/')."/sitemap.xml\n"
    : "User-agent: *\nDisallow: /\n", 200)->header('Content-Type', 'text/plain'));
Route::get('/es/aviso-de-privacidad', [PublicSiteController::class, 'legacy'])->defaults('locale', 'es')->defaults('page', 'privacy');
Route::get('/en/privacy-policy', [PublicSiteController::class, 'legacy'])->defaults('locale', 'en')->defaults('page', 'privacy');
Route::get('/sitemap.xml', [PublicSiteController::class, 'sitemap']);
