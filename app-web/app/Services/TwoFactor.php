<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

class TwoFactor
{
    public function engine(): TwoFactorAuth
    {
        return new TwoFactorAuth(new BaconQrCodeProvider(format: 'svg'), 'Medicina Respiratoria');
    }

    public function attempt(User $user): void
    {
        $key = 'two-factor:'.$user->id;
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['code' => 'Demasiados intentos. Espera un minuto antes de volver a intentar.']);
        }
        RateLimiter::hit($key, 60);
    }

    public function clearAttempts(User $user): void
    {
        RateLimiter::clear('two-factor:'.$user->id);
    }

    // The caller must hold a row lock and save in the same transaction.
    public function consume(User $user, string $code): bool
    {
        if (preg_match('/^[0-9]{6}$/', $code)) {
            $step = 0;
            if ($this->engine()->verifyCode($user->two_factor_secret, $code, 1, now()->timestamp, $step)
                && $step > ($user->two_factor_last_step ?? 0)) {
                $user->two_factor_last_step = $step;

                return true;
            }

            return false;
        }
        $hash = hash('sha256', $this->normalizeRecovery($code));
        foreach ($user->two_factor_recovery_codes ?? [] as $index => $stored) {
            if (hash_equals($stored, $hash)) {
                $codes = $user->two_factor_recovery_codes;
                unset($codes[$index]);
                $user->two_factor_recovery_codes = array_values($codes);

                return true;
            }
        }

        return false;
    }

    public function recoveryCodes(User $user): array
    {
        $codes = array_map(fn () => implode('-', str_split(strtoupper(bin2hex(random_bytes(16))), 8)), range(1, 10));
        $user->two_factor_recovery_codes = array_map(fn ($code) => hash('sha256', $this->normalizeRecovery($code)), $codes);

        return $codes;
    }

    private function normalizeRecovery(string $code): string
    {
        return strtoupper(str_replace(['-', ' '], '', trim($code)));
    }
}
