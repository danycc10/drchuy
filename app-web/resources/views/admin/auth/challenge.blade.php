<x-admin-layout title="Verifica tu acceso" :guest="true">
    <p class="muted">Introduce el código de tu aplicación autenticadora. Si no tienes el teléfono, utiliza uno de tus códigos de recuperación.</p>
    <form method="post" action="{{ route('two-factor.verify') }}" class="stack-form">@csrf
        <label>Código de verificación o recuperación<input name="code" required autofocus autocomplete="one-time-code" maxlength="64" spellcheck="false" autocapitalize="off"></label>
        <p class="field-help">El código de la aplicación tiene seis dígitos. Cada código de recuperación funciona una sola vez.</p>
        <button class="button primary"><x-site-icon name="lock" /> Verificar y entrar</button>
    </form>
    <form method="post" action="{{ route('two-factor.cancel') }}" class="security-cancel">@csrf<button class="text-button">Volver al inicio de sesión</button></form>
</x-admin-layout>
