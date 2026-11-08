<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
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
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    });
});
