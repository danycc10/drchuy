<?php

namespace App\Providers;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(fn ($user, string $token) =>
            rtrim(config('app.url'), '/').'/admin/reset-password/'.rawurlencode($token)
                .'?'.http_build_query(['email' => $user->getEmailForPasswordReset()])
        );
    }
}
