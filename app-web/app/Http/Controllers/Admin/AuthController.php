<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountSecurity;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request, AccountSecurity $security)
    {
        $request->session()->forget(['login.pending', 'two_factor.setup', 'auth.version', 'auth.2fa_version']);
        $data = $request->validate(['email' => 'required|email|max:254', 'password' => 'required|string|max:255']);
        $data['email'] = Str::lower(trim($data['email']));
        $key = 'login:'.hash('sha256', $data['email'].'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['email' => 'Demasiados intentos. Espera un minuto antes de volver a intentar.']);
        }
        RateLimiter::hit($key, 60);
        if (! Auth::validate([...$data, 'is_active' => true])) {
            throw ValidationException::withMessages(['email' => 'No fue posible iniciar sesión con estos datos.']);
        }
        RateLimiter::clear($key);
        $candidate = Auth::getLastAttempted();
        return DB::transaction(function () use ($candidate, $data, $request, $security) {
            $user = User::whereKey($candidate->id)->lockForUpdate()->firstOrFail();
            if (! $user->is_active || ! hash_equals($candidate->password, $user->password)) {
                throw ValidationException::withMessages(['email' => 'Los datos de acceso cambiaron. Intenta de nuevo.']);
            }
            Auth::getProvider()->rehashPasswordIfRequired($user, $data);
            $request->session()->regenerate();
            if ($user->two_factor_confirmed_at) {
                $request->session()->put('login.pending', ['id' => $user->id, 'version' => $user->auth_version, 'expires' => now()->addMinutes(5)->timestamp]);

                return to_route('two-factor.challenge');
            }
            Auth::login($user);
            $security->bindSession($request, $user);

            return redirect()->intended(route('admin.dashboard'));
        });
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email|max:254']);
        Password::sendResetLink(['email' => Str::lower(trim($request->email)), 'is_active' => true]);

        return back()->with('status', 'Si existe una cuenta activa con ese correo, recibirás un enlace para restablecer tu contraseña.');
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|string',
            'email' => 'required|email|max:254',
            'password' => ['required', 'confirmed', 'max:255', PasswordRule::min(12)->letters()->numbers()],
        ]);
        $data['email'] = Str::lower(trim($data['email']));
        $status = Password::reset([...$data, 'password_confirmation' => $request->password_confirmation, 'is_active' => true], function (User $user, string $password) {
            DB::transaction(function () use ($user, $password) {
                $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                $locked->password = $password;
                app(AccountSecurity::class)->invalidate($locked);
            });
            event(new PasswordReset($user));
        });
        if ($status !== Password::PasswordReset) {
            throw ValidationException::withMessages(['email' => 'El enlace no es válido o ya venció. Solicita uno nuevo.']);
        }

        return redirect()->route('login')->with('status', 'Contraseña actualizada. Ya puedes iniciar sesión.');
    }
}
