<?php

namespace App\Console\Commands;

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\AccountSecurity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ResetTwoFactor extends Command
{
    protected $signature = 'app:reset-two-factor';
    protected $description = 'Recuperación excepcional de 2FA con acceso al servidor y verificación previa de identidad';

    public function handle(AccountSecurity $security): int
    {
        $email = Str::lower(trim((string) $this->ask('Correo de la cuenta')));
        $user = User::where('email', $email)->first();
        if (! $user || ! $user->two_factor_confirmed_at) {
            $this->error('No se encontró una cuenta con verificación en dos pasos activada.');

            return self::FAILURE;
        }
        $this->warn('Esto quitará la protección de dos pasos y cerrará todas las sesiones de '.$user->email.'.');
        if (! $this->confirm('¿Verificaste la identidad del titular por un medio independiente y autorizas el restablecimiento?', false)) {
            return self::FAILURE;
        }
        DB::transaction(function () use ($user, $security) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $locked->two_factor_secret = null;
            $locked->two_factor_confirmed_at = null;
            $locked->two_factor_last_step = null;
            $locked->two_factor_recovery_codes = null;
            $security->invalidate($locked);
            DB::table('password_reset_tokens')->where('email', $locked->email)->delete();
            AuditEvent::create(['action' => 'security.reset_console', 'metadata' => ['account_id' => $locked->id, 'account_name' => $locked->name, 'via' => 'console']]);
        });
        $this->info('Protección restablecida. El titular debe entrar con su contraseña y configurar dos pasos de nuevo.');

        return self::SUCCESS;
    }
}
