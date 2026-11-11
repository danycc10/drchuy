@php
    $request = request();
    $locale = $request->hasSession() ? $request->session()->get('public_locale') : null;
    $locale = in_array($locale, ['es', 'en'], true) ? $locale : ($request->getPreferredLanguage(['es', 'en']) ?? 'es');
    try {
        $clinic = app(\App\Services\PublicContent::class)->snapshot()['clinic'];
    } catch (\Throwable) {
        $clinic = config('clinic');
        $clinic['whatsapp'] = 'https://wa.me/'.ltrim($clinic['phone'], '+');
        $clinic['appointment_url'] = ['es' => $clinic['whatsapp'], 'en' => $clinic['whatsapp']];
    }
    $copy = $clinic[$locale];
@endphp
<!doctype html>
<html lang="{{ $locale === 'es' ? 'es-MX' : 'en-US' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $locale === 'es' ? 'Página no encontrada' : 'Page not found' }} · {{ $clinic['name'] }}</title>
    <meta name="robots" content="noindex, nofollow">
    @include('public.head-icons')
    @vite(['resources/css/app.css'])
</head>
<body class="public-privacy">
    <a class="skip-link" href="#main">{{ $locale === 'es' ? 'Ir al contenido' : 'Skip to content' }}</a>
    <header class="site-header"><div class="container header-inner"><a class="site-brand" href="{{ route('home') }}"><img src="{{ asset('images/logo-header.webp') }}" alt="Medicina Respiratoria · Dr. Jesús García Espinoza" width="968" height="307"></a></div></header>
    <main id="main" class="container section privacy-document not-found">
        <span class="eyebrow">{{ $locale === 'es' ? 'ERROR 404' : 'ERROR 404' }}</span>
        <h1>{{ $locale === 'es' ? 'No encontramos esta página.' : 'We could not find this page.' }}</h1>
        <p class="not-found-copy">{{ $locale === 'es' ? 'Es posible que el enlace haya cambiado o que la dirección esté incompleta. Puedes volver al inicio o escribirnos para solicitar una cita.' : 'The link may have changed or the address may be incomplete. You can return to the home page or message us to request an appointment.' }}</p>
        <div class="privacy-end">
            <a class="button secondary" href="{{ route('home') }}"><x-site-icon name="home" /> {{ $locale === 'es' ? 'Volver al inicio' : 'Back to home' }}</a>
            <a class="button secondary" href="{{ route('home') }}#services"><x-site-icon name="grid" /> {{ $copy['nav'][0] }}</a>
            <a class="button" href="{{ $clinic['appointment_url'][$locale] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $copy['cta'] }} ({{ $copy['new_tab'] }})"><x-site-icon name="message" /> {{ $copy['short_cta'] }}</a>
        </div>
    </main>
</body>
</html>
