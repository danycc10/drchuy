<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ServiceController;
use App\Models\AuditEvent;
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
        Route::get('/services', [ServiceController::class, 'index'])->name('admin.services.index');
        Route::get('/services/{service}/edit', [ServiceController::class, 'edit'])->name('admin.services.edit');
        Route::put('/services/{service}', [ServiceController::class, 'update'])->name('admin.services.update');
        Route::post('/services/{service}/publish', [ServiceController::class, 'publish'])->name('admin.services.publish');
        Route::get('/services/{service}/revisions/{revision}', [ServiceController::class, 'revision'])->name('admin.services.revision');
        Route::post('/services/{service}/revisions/{revision}/restore', [ServiceController::class, 'restore'])->name('admin.services.restore');
        Route::get('/activity', fn () => view('admin.activity', ['events' => AuditEvent::with(['user', 'service.currentRevision'])->latest('id')->paginate(20)]))->name('admin.activity');
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
