<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\MediaController;
use App\Models\AuditEvent;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('admin.headers')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::view('/login', 'admin.auth.login')->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login.store');
        Route::view('/forgot-password', 'admin.auth.forgot')->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset-password/{token}', fn (string $token) => view('admin.auth.reset', ['token' => $token]))->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
    });
    Route::middleware(['auth', 'admin.active'])->group(function () {
        Route::get('/', DashboardController::class)->name('admin.dashboard');
        Route::get('/media', [MediaController::class, 'index'])->name('admin.media.index');
        Route::post('/media', [MediaController::class, 'store'])->middleware('throttle:20,1')->name('admin.media.store');
        Route::get('/media/{media}/image/{variant}', [MediaController::class, 'image'])->whereIn('variant', ['display', 'thumb'])->name('admin.media.image');
        Route::get('/media/{media}/original', [MediaController::class, 'original'])->name('admin.media.original');
        Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('admin.media.destroy');
        Route::get('/preview/{locale}', [PublicSiteController::class, 'preview'])->whereIn('locale', ['es', 'en'])->name('admin.preview');
        Route::get('/preview/{locale}/privacy', [PublicSiteController::class, 'previewPrivacy'])->whereIn('locale', ['es', 'en'])->name('admin.preview.privacy');
        Route::get('/services', [ServiceController::class, 'index'])->name('admin.services.index');
        Route::get('/content', [ContentController::class, 'index'])->name('admin.content.index');
        Route::get('/content/{block}/edit', [ContentController::class, 'edit'])->name('admin.content.edit');
        Route::put('/content/{block}', [ContentController::class, 'update'])->name('admin.content.update');
        Route::post('/content/{block}/publish', [ContentController::class, 'publish'])->name('admin.content.publish');
        Route::get('/content/{block}/revisions/{revision}', [ContentController::class, 'revision'])->name('admin.content.revision');
        Route::post('/content/{block}/revisions/{revision}/restore', [ContentController::class, 'restore'])->name('admin.content.restore');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
        Route::post('/services/{service}/publish', [ServiceController::class, 'publish'])->name('admin.services.publish');
        Route::get('/services/{service}/revisions/{revision}', [ServiceController::class, 'revision'])->name('admin.services.revision');
        Route::post('/services/{service}/revisions/{revision}/restore', [ServiceController::class, 'restore'])->name('admin.services.restore');
        Route::get('/activity', fn () => view('admin.activity', ['events' => AuditEvent::with(['user', 'service.currentRevision'])->latest('id')->paginate(20)]))->name('admin.activity');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
