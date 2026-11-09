<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AccountSecurity
{
    // Call within the transaction that changes credentials, role or account status.
    public function invalidate(User $user): void
    {
        $user->auth_version++;
        $user->remember_token = Str::random(60);
        $user->save();
        if (config('session.driver') === 'database') {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
    }

    public function bindSession(Request $request, User $user): void
    {
        Auth::setUser($user);
        $request->session()->regenerate();
        $request->session()->put('auth.version', $user->auth_version);
        if ($user->two_factor_confirmed_at) {
            $request->session()->put('auth.2fa_version', $user->auth_version);
        } else {
            $request->session()->forget('auth.2fa_version');
        }
    }
}
