<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use App\Models\User;
use App\Services\AccountSecurity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', User::class);
        $data = $request->validate(['q' => 'nullable|string|max:100']);
        $users = User::query()->when($data['q'] ?? null, fn ($query, $term) => $query->where(fn ($q) => $q->where('name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%')))->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        Gate::authorize('viewAny', User::class);

        return view('admin.users.form', ['account' => new User()]);
    }

    public function store(Request $request)
    {
        Gate::authorize('viewAny', User::class);
        $request->merge(['email' => Str::lower(trim((string) $request->input('email')))]);
        $data = $request->validate([
            'name' => 'required|string|max:100', 'email' => 'required|email|max:254|unique:users,email',
            'role' => ['required', Rule::enum(Role::class)], 'current_password' => 'required|current_password',
        ]);
        $account = DB::transaction(function () use ($data, $request) {
            $this->assertCurrentOwner(User::whereKey($request->user()->id)->lockForUpdate()->first(), $request);
            $account = new User(['name' => $data['name'], 'email' => $data['email'], 'password' => Str::random(64)]);
            $account->role = Role::from($data['role']);
            $account->save();
            AuditEvent::create(['user_id' => $request->user()->id, 'action' => 'user.created', 'metadata' => ['account_id' => $account->id, 'account_name' => $account->name, 'role' => $data['role']]]);

            return $account;
        });

        return to_route('admin.users.edit', $account)->with('status', 'Cuenta creada. Genera su enlace para que el titular elija una contraseña.');
    }

    public function edit(User $account)
    {
        Gate::authorize('viewAny', User::class);

        return view('admin.users.form', compact('account'));
    }

    public function update(Request $request, User $account, AccountSecurity $security)
    {
        Gate::authorize('viewAny', User::class);
        $data = $request->validate([
            'name' => 'required|string|max:100', 'role' => ['required', Rule::enum(Role::class)],
            'is_active' => 'required|boolean', 'current_password' => 'required|current_password',
        ]);
        DB::transaction(function () use ($request, $account, $data, $security) {
            // Serialize account changes, including simultaneous changes to owner accounts.
            $accounts = User::orderBy('id')->lockForUpdate()->get();
            $actor = $accounts->firstWhere('id', $request->user()->id);
            $this->assertCurrentOwner($actor, $request);
            $locked = $accounts->firstWhere('id', $account->id);
            $removesOwner = $locked->role === Role::Owner && ($data['role'] !== Role::Owner->value || ! $data['is_active']);
            if ($locked->id === $request->user()->id && ($data['role'] !== $locked->role->value || ! $data['is_active'])) {
                throw ValidationException::withMessages(['role' => 'No puedes quitarte el rol de propietario ni desactivar tu propia cuenta.']);
            }
            if ($removesOwner && $accounts->where('is_active', true)->where('role', Role::Owner)->count() <= 1) {
                throw ValidationException::withMessages(['role' => 'Debe permanecer al menos un propietario activo.']);
            }
            $locked->name = $data['name'];
            $locked->role = Role::from($data['role']);
            $locked->is_active = (bool) $data['is_active'];
            if ($locked->isDirty(['role', 'is_active'])) {
                $security->invalidate($locked);
                DB::table('password_reset_tokens')->where('email', $locked->email)->delete();
            } else {
                $locked->save();
            }
            AuditEvent::create(['user_id' => $request->user()->id, 'action' => 'user.updated', 'metadata' => ['account_id' => $locked->id, 'account_name' => $locked->name, 'role' => $locked->role->value, 'is_active' => $locked->is_active]]);
        });

        return back()->with('status', 'Cuenta actualizada. Los cambios de rol o estado cierran sus sesiones anteriores.');
    }

    public function setupLink(Request $request, User $account)
    {
        Gate::authorize('viewAny', User::class);
        $request->validate(['current_password' => 'required|current_password']);
        $url = DB::transaction(function () use ($request, $account) {
            // Use the same ascending lock order as account updates.
            $accounts = User::whereIn('id', [$request->user()->id, $account->id])->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            $this->assertCurrentOwner($accounts->get($request->user()->id), $request);
            $target = $accounts->get($account->id);
            abort_unless($target?->is_active && $target->id !== $request->user()->id, 403);
            $token = Password::createToken($target);
            AuditEvent::create(['user_id' => $request->user()->id, 'action' => 'user.setup_link', 'metadata' => ['account_id' => $target->id, 'account_name' => $target->name]]);

            return rtrim(config('app.url'), '/').'/admin/reset-password/'.rawurlencode($token).'?'.http_build_query(['email' => $target->email]);
        });

        return back()->with('setup_link', encrypt($url))->with('status', 'Enlace generado. Compártelo directamente con el titular de la cuenta.');
    }

    private function assertCurrentOwner(?User $actor, Request $request): void
    {
        abort_unless($actor?->is_active && $actor->role === Role::Owner && $actor->auth_version === $request->user()->auth_version, 403);
    }
}
