<x-admin-layout title="Bienvenido de nuevo" :guest="true">
    <p class="muted">Ingresa para gestionar tu sitio web.</p>
    <form method="post" action="{{ route('login.store') }}" class="stack-form">
        @csrf
        <label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" maxlength="254"></label>
        <label>Contraseña<input type="password" name="password" required autocomplete="current-password" maxlength="255"></label>
        <a class="small-link" href="{{ route('password.request') }}">Olvidé mi contraseña</a>
        <button class="button primary"><x-site-icon name="lock" /> Iniciar sesión</button>
    </form>
</x-admin-layout>
