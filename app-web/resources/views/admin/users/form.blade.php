<x-admin-layout :title="$account->exists ? 'Administrar cuenta' : 'Crear cuenta'">
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.users.index') }}"><x-site-icon name="user" /> Usuarios</a><h1>{{ $account->exists ? $account->name : 'Nueva cuenta' }}</h1><p>Cada persona utiliza su propio acceso. Los cambios quedan registrados en Actividad.</p></div></div>
    <div class="editor-grid">
        <div class="editor-fields">
            @if(session('setup_link'))
                <section class="panel"><h2>Enlace para elegir contraseña</h2><p class="muted small">Cópialo y compártelo de forma privada con {{ $account->name }}. Vence en {{ config('auth.passwords.users.expire') }} minutos, funciona una sola vez y reemplaza cualquier enlace anterior. No se envió por correo.</p><label>Enlace privado<textarea readonly rows="4" class="private-value" spellcheck="false">{{ decrypt(session('setup_link')) }}</textarea></label></section>
            @endif
            <form class="panel stack-form" method="post" action="{{ $account->exists ? route('admin.users.update', $account) : route('admin.users.store') }}">
                @csrf @if($account->exists)@method('PUT')@endif
                <h2>Datos de la cuenta</h2>
                <label>Nombre<input name="name" value="{{ old('name', $account->name) }}" maxlength="100" required autocomplete="name"></label>
                @if($account->exists)<p class="muted">Correo de acceso: <strong>{{ $account->email }}</strong></p>@else<label>Correo de acceso<input type="email" name="email" value="{{ old('email') }}" maxlength="254" required autocomplete="off"></label>@endif
                @if($account->exists && $account->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $account->role->value }}"><input type="hidden" name="is_active" value="1"><p class="field-help">Tu cuenta conserva el rol de propietario y el acceso activo.</p>
                @else
                    <label>Rol<select name="role" required>@foreach(\App\Enums\Role::cases() as $role)<option value="{{ $role->value }}" @selected(old('role', $account->role?->value ?? 'editor') === $role->value)>{{ $role->label() }}</option>@endforeach</select></label>
                    @if($account->exists)<input type="hidden" name="is_active" value="0"><label class="account-toggle"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $account->is_active))> Permitir acceso al panel</label><p class="field-help">Al desactivar o cambiar el rol se cierran sus sesiones anteriores. Se conserva su historial de trabajo.</p>@endif
                @endif
                <label>Tu contraseña actual<input type="password" name="current_password" required autocomplete="current-password" maxlength="255"></label>
                <button class="button primary"><x-site-icon name="save" /> {{ $account->exists ? 'Guardar cuenta' : 'Crear cuenta' }}</button>
            </form>
            @if($account->exists && $account->is_active && $account->id !== auth()->id())
                <form class="panel stack-form" method="post" action="{{ route('admin.users.setup-link', $account) }}">@csrf<h2>Configurar o recuperar contraseña</h2><p class="muted small">Genera un enlace privado para que esta persona elija su contraseña. La verificación en dos pasos, si está activada, seguirá siendo necesaria.</p><label>Tu contraseña actual<input type="password" name="current_password" required autocomplete="current-password" maxlength="255"></label><button class="button secondary"><x-site-icon name="lock" /> Generar enlace privado</button></form>
            @endif
        </div>
        <aside class="editor-aside"><section class="panel"><h2>Permisos de cada rol</h2><dl class="revision-fields"><dt>Editor</dt><dd>Prepara borradores y consulta vistas previas.</dd><dt>Administrador</dt><dd>También publica contenido general. Los procedimientos, el perfil, las fotografías y los avisos legales requieren al propietario.</dd><dt>Propietario</dt><dd>Publica todo el contenido y administra las cuentas del equipo.</dd></dl></section></aside>
    </div>
</x-admin-layout>
