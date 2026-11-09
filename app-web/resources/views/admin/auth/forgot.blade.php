<x-admin-layout title="Recupera tu acceso" :guest="true">
    <p class="muted">Te enviaremos un enlace para elegir una contraseña nueva.</p>
    <form method="post" action="{{ route('password.email') }}" class="stack-form">@csrf
        <label>Correo electrónico<input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" maxlength="254"></label>
        <button class="button primary"><x-site-icon name="mail" /> Enviar enlace</button><a class="small-link" href="{{ route('login') }}">Volver al acceso</a>
    </form>
</x-admin-layout>
