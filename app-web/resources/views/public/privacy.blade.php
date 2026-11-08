<!doctype html>
<html lang="{{ $locale === 'es' ? 'es-MX' : 'en-US' }}"><head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['privacy_title'] }} · {{ $clinic['name'] }}</title>
    <meta name="robots" content="{{ $preview || !$clinic['indexable'] ? 'noindex, nofollow' : 'index, follow' }}">
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}{{ route('privacy.'.$locale, [], false) }}">
    @foreach(['es' => 'es-MX', 'en' => 'en-US'] as $code => $tag)<link rel="alternate" hreflang="{{ $tag }}" href="{{ rtrim(config('app.url'), '/') }}{{ route('privacy.'.$code, [], false) }}">@endforeach
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head><body>
    @if($preview)<div class="preview-banner">Vista previa privada · aviso sin publicar</div>@endif
    <header class="site-header"><div class="container header-inner"><a class="site-brand" href="{{ route($preview ? 'admin.preview' : 'home', $locale) }}"><img src="{{ asset('images/logo.png') }}" alt="Medicina Respiratoria" width="4140" height="1314"></a><a class="language-switch" href="{{ $preview ? route('admin.preview.privacy', $locale === 'es' ? 'en' : 'es') : route('privacy.'.($locale === 'es' ? 'en' : 'es')) }}">{{ $locale === 'es' ? 'EN' : 'ES' }}</a></div></header>
    <main class="container section privacy-document"><a class="explore-link" href="{{ route($preview ? 'admin.preview' : 'home', $locale) }}">← {{ $locale === 'es' ? 'Volver al inicio' : 'Back to home' }}</a><h1>{{ $copy['privacy_title'] }}</h1>@if($clinic['privacy_updated_at'])<p class="privacy-date">{{ $locale === 'es' ? 'Última actualización' : 'Last updated' }}: {{ $clinic['privacy_updated_at']->timezone('America/Matamoros')->format('d/m/Y') }}</p>@endif<div class="privacy-text">{{ $copy['privacy_content'] }}</div></main>
</body></html>
