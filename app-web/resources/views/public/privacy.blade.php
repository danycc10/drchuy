<!doctype html>
<html lang="{{ $locale === 'es' ? 'es-MX' : 'en-US' }}"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['privacy_title'] }} · {{ $clinic['name'] }}</title>
    <meta name="robots" content="{{ $preview || !$clinic['indexable'] ? 'noindex, nofollow' : 'index, follow' }}">
    <meta name="description" content="{{ $copy['privacy_title'] }} · {{ $clinic['name'] }}, {{ $clinic['physician'] }}. {{ $copy['location'] }}.">
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}{{ route('privacy', [], false) }}">
    <meta property="og:type" content="article"><meta property="og:site_name" content="{{ $clinic['name'] }}"><meta property="og:title" content="{{ $copy['privacy_title'] }} · {{ $clinic['name'] }}"><meta property="og:url" content="{{ rtrim(config('app.url'), '/') }}{{ route('privacy', [], false) }}"><meta property="og:locale" content="{{ $locale === 'es' ? 'es_MX' : 'en_US' }}">
    @include('public.head-icons')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head><body class="public-privacy">
<a class="skip-link" href="#main">{{ $locale === 'es' ? 'Ir al contenido' : 'Skip to content' }}</a>
    @if($preview)<div class="preview-banner">Vista previa privada · aviso sin publicar</div>@endif
    <header class="site-header"><div class="container header-inner"><a class="site-brand" href="{{ ($preview ? route('admin.preview', $locale) : route('home')) }}"><img src="{{ asset('images/logo-header.webp') }}" alt="Medicina Respiratoria · Dr. Jesús García Espinoza" width="968" height="307"></a><x-public-language-switch :locale="$locale" :preview="$preview" page="privacy" /></div></header>
    @php($homeUrl = $preview ? route('admin.preview', $locale) : route('home'))
    <main id="main" class="container section privacy-document"><a class="explore-link" href="{{ $homeUrl }}"><x-site-icon name="home" /> {{ $locale === 'es' ? 'Volver al inicio' : 'Back to home' }}</a><h1>{{ $copy['privacy_title'] }}</h1>@if($clinic['privacy_updated_at'])<p class="privacy-date">{{ $locale === 'es' ? 'Última actualización' : 'Last updated' }}: {{ $clinic['privacy_updated_at']->timezone('America/Matamoros')->format('d/m/Y') }}</p>@endif<div class="privacy-text">{{ $copy['privacy_content'] }}</div><div class="privacy-end"><a class="button secondary" href="{{ $homeUrl }}"><x-site-icon name="home" /> {{ $locale === 'es' ? 'Volver al inicio' : 'Back to home' }}</a><a class="button" href="{{ $clinic['appointment_url'][$locale] }}" data-appointment-link target="_blank" rel="noopener noreferrer" aria-label="{{ $copy['cta'] }} ({{ $copy['new_tab'] }})"><x-site-icon name="message" /> {{ $copy['short_cta'] }}</a></div></main>
    @include('public.footer', ['anchorBase' => $homeUrl])
</body></html>
