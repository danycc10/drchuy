<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\User;
use App\Services\AccountSecurity;
use App\Services\TwoFactor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TwoFactorController extends Controller
{
    public function show(Request $request, TwoFactor $twoFactor)
    {
        $setup = $this->setup($request);
        $secret = $setup ? decrypt($setup['secret']) : null;

        return view('admin.security', [
            'account' => $request->user(), 'secret' => $secret,
            'qr' => $secret ? $twoFactor->engine()->getQRCodeImageAsDataUri($request->user()->email, $secret, 240) : null,
        ]);
    }

    public function begin(Request $request, TwoFactor $twoFactor)
    {
        $request->validate(['current_password' => 'required|current_password']);
        abort_if($request->user()->two_factor_confirmed_at, 409);
        $request->session()->put('two_factor.setup', [
            'secret' => encrypt($twoFactor->engine()->createSecret()), 'user_id' => $request->user()->id,
            'version' => $request->user()->auth_version, 'expires' => now()->addMinutes(10)->timestamp,
        ]);

        return back()->with('status', 'Escanea el QR y confirma un código para terminar la activación.');
    }

    public function cancel(Request $request)
    {
        $request->session()->forget('two_factor.setup');

        return to_route('admin.security')->with('status', 'Configuración cancelada.');
    }

    public function confirm(Request $request, TwoFactor $twoFactor, AccountSecurity $security)
    {
        $data = $request->validate(['code' => 'required|string|regex:/^[0-9]{6}$/']);
        $setup = $this->setup($request);
        if (! $setup) {
            throw ValidationException::withMessages(['code' => 'La configuración venció. Inicia la activación de nuevo.']);
        }
        $twoFactor->attempt($request->user());
        [$user, $codes] = DB::transaction(function () use ($request, $setup, $data, $twoFactor, $security) {
            $user = $this->lockCurrent($request);
            abort_if($user->two_factor_confirmed_at, 409);
            $user->two_factor_secret = decrypt($setup['secret']);
            if (! $twoFactor->consume($user, $data['code'])) {
                throw ValidationException::withMessages(['code' => 'El código no es válido. Comprueba la hora de tu teléfono e intenta con el siguiente.']);
            }
            $user->two_factor_confirmed_at = now();
            $codes = $twoFactor->recoveryCodes($user);
            $security->invalidate($user);
            $this->audit($user, 'security.enabled');

            return [$user, $codes];
        });
        $twoFactor->clearAttempts($user);
        $request->session()->forget('two_factor.setup');
        $security->bindSession($request, $user);

        return to_route('admin.security')->with('recovery_codes', encrypt($codes))->with('status', 'Verificación en dos pasos activada. Guarda tus códigos de recuperación.');
    }

    public function regenerate(Request $request, TwoFactor $twoFactor, AccountSecurity $security)
    {
        return $this->change($request, $twoFactor, $security, false);
    }

    public function disable(Request $request, TwoFactor $twoFactor, AccountSecurity $security)
    {
        return $this->change($request, $twoFactor, $security, true);
    }

    private function change(Request $request, TwoFactor $twoFactor, AccountSecurity $security, bool $disable)
    {
        $data = $request->validate(['current_password' => 'required|current_password', 'code' => 'required|string|max:64']);
        $twoFactor->attempt($request->user());
        [$user, $codes] = DB::transaction(function () use ($request, $data, $twoFactor, $security, $disable) {
            $user = $this->lockCurrent($request);
            abort_unless($user->two_factor_confirmed_at, 409);
            if (! $twoFactor->consume($user, $data['code'])) {
                throw ValidationException::withMessages(['code' => 'El código no es válido o ya fue utilizado. Usa uno nuevo.']);
            }
            $codes = [];
            if ($disable) {
                $user->two_factor_secret = null;
                $user->two_factor_recovery_codes = null;
                $user->two_factor_last_step = null;
                $user->two_factor_confirmed_at = null;
            } else {
                $codes = $twoFactor->recoveryCodes($user);
            }
            $security->invalidate($user);
            $this->audit($user, $disable ? 'security.disabled' : 'security.recovery_rotated');

            return [$user, $codes];
        });
        $twoFactor->clearAttempts($user);
        $security->bindSession($request, $user);
        $response = to_route('admin.security')->with('status', $disable ? 'Verificación en dos pasos desactivada.' : 'Códigos renovados. Los anteriores ya no funcionan.');

        return $disable ? $response : $response->with('recovery_codes', encrypt($codes));
    }

    public function challenge(Request $request)
    {
        if (! $this->pending($request)) {
            return to_route('login')->with('status', 'Inicia sesión para continuar.');
        }

        return view('admin.auth.challenge');
    }

    public function verify(Request $request, TwoFactor $twoFactor, AccountSecurity $security)
    {
        $data = $request->validate(['code' => 'required|string|max:64']);
        $user = $this->pending($request);
        if (! $user) {
            return to_route('login')->with('status', 'El acceso pendiente venció. Introduce de nuevo tu correo y contraseña.');
        }
        $twoFactor->attempt($user);
        $user = DB::transaction(function () use ($request, $user, $data, $twoFactor) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->is_active && $locked->two_factor_confirmed_at && $locked->auth_version === $request->session()->get('login.pending.version'), 403);
            if (! $twoFactor->consume($locked, $data['code'])) {
                throw ValidationException::withMessages(['code' => 'El código no es válido o ya fue utilizado. Usa uno nuevo.']);
            }
            $locked->save();

            return $locked;
        });
        $twoFactor->clearAttempts($user);
        $request->session()->forget('login.pending');
        Auth::login($user);
        $security->bindSession($request, $user);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function cancelChallenge(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return to_route('login');
    }

    private function pending(Request $request): ?User
    {
        $pending = $request->session()->get('login.pending');
        $user = $pending ? User::find($pending['id']) : null;
        if (! $user || ! $user->is_active || ! $user->two_factor_confirmed_at || $pending['expires'] <= now()->timestamp || $pending['version'] !== $user->auth_version) {
            $request->session()->forget('login.pending');

            return null;
        }

        return $user;
    }

    private function setup(Request $request): ?array
    {
        $setup = $request->session()->get('two_factor.setup');
        if (! $setup || $setup['expires'] <= now()->timestamp || $setup['user_id'] !== $request->user()->id || $setup['version'] !== $request->user()->auth_version || $request->user()->two_factor_confirmed_at) {
            $request->session()->forget('two_factor.setup');

            return null;
        }

        return $setup;
    }

    private function lockCurrent(Request $request): User
    {
        $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
        abort_unless($user->is_active && $user->auth_version === (int) $request->session()->get('auth.version', 0), 403);

        return $user;
    }

    private function audit(User $user, string $action): void
    {
        AuditEvent::create(['user_id' => $user->id, 'action' => $action, 'metadata' => ['account_id' => $user->id, 'account_name' => $user->name]]);
    }
}
