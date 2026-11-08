<x-admin-layout title="Nueva contraseña" :guest="true">
    <p class="muted">Usa al menos 12 caracteres, incluyendo letras y números.</p>
    <form method="post" action="{{ route('password.update') }}" class="stack-form">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <label>Correo<input type="email" name="email" value="{{ old('email', request('email')) }}" required autocomplete="username"></label>
        <label>Contraseña<input type="password" name="password" required minlength="12" autocomplete="new-password"></label>
        <label>Repite la contraseña<input type="password" name="password_confirmation" required minlength="12" autocomplete="new-password"></label>
        <button class="button primary">Guardar contraseña</button>
    </form>
</x-admin-layout>
