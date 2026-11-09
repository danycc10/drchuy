<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\TwoFactorController;
use App\Models\AuditEvent;
use App\Http\Controllers\PublicSiteController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->middleware('admin.headers')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/two-factor', [TwoFactorController::class, 'challenge'])->name('two-factor.challenge');
        Route::post('/two-factor', [TwoFactorController::class, 'verify'])->middleware('throttle:10,1,two-factor:')->name('two-factor.verify');
        Route::post('/two-factor/cancel', [TwoFactorController::class, 'cancelChallenge'])->name('two-factor.cancel');
        Route::view('/login', 'admin.auth.login')->name('login');
        Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login.store');
        Route::view('/forgot-password', 'admin.auth.forgot')->name('password.request');
        Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:3,1')->name('password.email');
        Route::get('/reset-password/{token}', fn (string $token) => view('admin.auth.reset', ['token' => $token]))->name('password.reset');
        Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:6,1')->name('password.update');
    });
    Route::middleware(['auth', 'admin.active'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('admin.users.create');
        Route::post('/users', [UserController::class, 'store'])->middleware('throttle:10,1,user-management:')->name('admin.users.store');
        Route::get('/users/{account}/edit', [UserController::class, 'edit'])->name('admin.users.edit');
        Route::put('/users/{account}', [UserController::class, 'update'])->middleware('throttle:10,1,user-management:')->name('admin.users.update');
        Route::post('/users/{account}/setup-link', [UserController::class, 'setupLink'])->middleware('throttle:5,1,setup-link:')->name('admin.users.setup-link');
        Route::get('/security', [TwoFactorController::class, 'show'])->name('admin.security');
        Route::post('/security/begin', [TwoFactorController::class, 'begin'])->middleware('throttle:5,1,security-begin:')->name('admin.security.begin');
        Route::post('/security/cancel', [TwoFactorController::class, 'cancel'])->name('admin.security.cancel');
        Route::post('/security/confirm', [TwoFactorController::class, 'confirm'])->middleware('throttle:10,1,security-confirm:')->name('admin.security.confirm');
        Route::post('/security/recovery', [TwoFactorController::class, 'regenerate'])->middleware('throttle:5,1,security-change:')->name('admin.security.recovery');
        Route::delete('/security', [TwoFactorController::class, 'disable'])->middleware('throttle:5,1,security-change:')->name('admin.security.disable');
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
