<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PublicSiteController;

require __DIR__.'/admin.php';

Route::redirect('/', '/es');
Route::get('/{locale}', [PublicSiteController::class, 'show'])->whereIn('locale', ['es', 'en'])->name('home');
Route::get('/robots.txt', fn () => response(config('clinic.indexable')
    ? "User-agent: *\nDisallow: /admin\nSitemap: ".rtrim(config('app.url'), '/')."/sitemap.xml\n"
    : "User-agent: *\nDisallow: /\n", 200)->header('Content-Type', 'text/plain'));
Route::get('/sitemap.xml', fn () => response()->view('public.sitemap')->header('Content-Type', 'application/xml'));
