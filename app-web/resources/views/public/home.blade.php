<!doctype html>
<html lang="{{ $locale === 'es' ? 'es-MX' : 'en-US' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['title'] }}</title><meta name="description" content="{{ $copy['description'] }}">
    <meta name="robots" content="{{ !$clinic['indexable'] || $preview ? 'noindex, nofollow' : 'index, follow' }}">
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}/{{ $locale }}">
    @foreach(['es' => 'es-MX', 'en' => 'en-US'] as $code => $tag)<link rel="alternate" hreflang="{{ $tag }}" href="{{ rtrim(config('app.url'), '/') }}/{{ $code }}">@endforeach
    <link rel="alternate" hreflang="x-default" href="{{ rtrim(config('app.url'), '/') }}/es">
    <meta property="og:type" content="website"><meta property="og:title" content="{{ $copy['title'] }}"><meta property="og:description" content="{{ $copy['description'] }}"><meta property="og:url" content="{{ rtrim(config('app.url'), '/') }}/{{ $locale }}"><meta property="og:image" content="{{ rtrim(config('app.url'), '/') }}/images/doctor.webp"><meta property="og:locale" content="{{ $locale === 'es' ? 'es_MX' : 'en_US' }}">
    @if(!$preview)<script type="application/ld+json">{!! $structuredData !!}</script>@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-home">
<a class="skip-link" href="#main">{{ $locale === 'es' ? 'Ir al contenido' : 'Skip to content' }}</a>
@if($preview)<div class="preview-banner">Vista previa privada · incluye borradores sin publicar <a href="{{ route('admin.dashboard') }}">Volver al panel →</a></div>@endif
<div class="location-bar"><div class="container"><span>{{ $copy['location'] }} <span class="location-separator">/</span> <span class="appointment-top">{{ $locale === 'es' ? 'Atención con cita previa' : 'By appointment' }}</span></span><a href="tel:{{ $clinic['phone'] }}">{{ $clinic['phone_display'] }}</a></div></div>
<header class="site-header"><div class="container header-inner">
    <a class="site-brand" href="{{ route($preview ? 'admin.preview' : 'home', $locale) }}"><img src="{{ asset('images/logo.png') }}" alt="Medicina Respiratoria · Dr. Jesús García Espinoza" width="1400" height="445"></a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation">{{ $locale === 'es' ? 'Menú' : 'Menu' }}</button>
    <nav id="site-navigation" aria-label="{{ $locale === 'es' ? 'Principal' : 'Main navigation' }}">@foreach(['services', 'physician', 'office', 'location'] as $i => $anchor)<a href="#{{ $anchor }}">{{ $copy['nav'][$i] }}</a>@endforeach<a href="#hours">{{ $locale === 'es' ? 'Horarios' : 'Hours' }}</a></nav>
    <div class="header-actions"><a class="language-switch" data-language-switch href="{{ route($preview ? 'admin.preview' : 'home', $locale === 'es' ? 'en' : 'es') }}" lang="{{ $locale === 'es' ? 'en' : 'es' }}" aria-label="{{ $locale === 'es' ? 'Read in English' : 'Leer en español' }}">{{ $locale === 'es' ? 'EN' : 'ES' }}</a><a class="button compact" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['short_cta'] }} <span aria-hidden="true">↗</span></a></div>
</div></header>
<main id="main">
    <section class="hero container"><div class="hero-copy"><span class="eyebrow"><span class="little-line"></span> {{ $copy['practice_label'] }}</span><h1><span class="sr-only">{{ $clinic['name'] }}: </span>{{ $copy['hero'] }}</h1><p class="hero-intro">{{ $copy['intro'] }}</p><a class="button" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['cta'] }} <span aria-hidden="true">↗</span></a><p class="appointment-note">{{ $copy['appointment'] }}</p>
        <nav class="visit-shortcuts" aria-label="{{ $locale === 'es' ? 'Planea tu visita' : 'Plan your visit' }}">
            <a href="#hours">{{ $locale === 'es' ? 'Ver horarios' : 'View office hours' }} <span aria-hidden="true">↓</span></a>
            <a href="{{ $clinic['maps'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['directions'] }} <span aria-hidden="true">↗</span></a>
        </nav>
        <a class="explore-link" href="#services"><span aria-hidden="true">↓</span> {{ $copy['explore'] }}</a></div><div class="hero-image"><img src="{{ asset('images/doctor.webp') }}" alt="{{ $copy['doctor_alt'] }}" width="709" height="1536" fetchpriority="high"><div class="portrait-caption"><strong>{{ $clinic['physician'] }}</strong><span>{{ $copy['designation'] }}</span></div><span class="photo-index" aria-hidden="true">01 / MEDICINA RESPIRATORIA</span></div></section>
    <section class="care-section"><div class="container care-inner"><h2>{{ $copy['care_title'] }}</h2><p>{{ $copy['care_copy'] }}</p></div></section>
    <section class="section container" id="services"><div class="section-heading"><div><span class="eyebrow">{{ $copy['services_label'] }}</span><h2>{{ $copy['services_title'] }}</h2></div><p>{{ $copy['services_copy'] }}</p></div>
        @foreach($categories as $category)
            @if($services->has($category->id))<div class="service-group"><div class="category-heading"><span class="category-number">0{{ $loop->iteration }}</span><h3>{{ $category->name[$locale] }}</h3></div><div class="service-items">@foreach($services[$category->id] as $service)<details><summary>{{ $service[$locale]['name'] }}<span aria-hidden="true">+</span></summary><div class="service-description"><p>{{ $service[$locale]['description'] }}</p><a href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['ask_service'] }} ↗</a></div></details>@endforeach</div></div>@endif
        @endforeach
        @if($services->isEmpty())<p class="availability">{{ $copy['availability'] }}</p>@endif
        <p class="procedure-note">{{ $copy['procedure_note'] }}</p>
    </section>
    <section class="physician-section" id="physician"><div class="container physician-inner"><div class="physician-image"><img src="{{ asset('images/doctor.webp') }}" alt="{{ $copy['doctor_alt'] }}" width="709" height="1536" loading="lazy"></div><div><span class="eyebrow">{{ $copy['doctor_label'] }}</span><h2>{{ $clinic['physician'] }}</h2><p class="designation">{{ $copy['designation'] }}</p><p>{{ $copy['bio'] }}</p><p>{{ $copy['bio_second'] }}</p><div class="license"><span>{{ $copy['license_label'] }}</span><strong>{{ $clinic['license'] }}</strong></div></div></div></section>
    <section class="section container" id="office"><div class="section-heading"><div><span class="eyebrow">{{ $copy['office_label'] }}</span><h2>{{ $copy['office_title'] }}</h2></div><p>{{ $copy['office_copy'] }}</p></div><div class="office-gallery">@foreach(['consultorio', 'fachada', 'entrada'] as $i => $photo)<figure><img src="{{ asset('images/'.$photo.'.webp') }}" alt="{{ $copy['photos'][$i] }}" loading="lazy" width="800" height="600"><figcaption><span>0{{ $i + 1 }}</span> {{ $copy['photos'][$i] }}</figcaption></figure>@endforeach</div></section>
    <section class="contact-section" id="location">
        <div class="container"><span class="eyebrow">{{ $copy['contact_label'] }}</span><h2>{{ $copy['contact_title'] }}</h2>
            <div class="contact-grid">
                <div><h3>{{ $copy['address_label'] }}</h3><address>{{ $clinic['address'] }}<br>{{ $clinic['city'] }}</address><a class="light-link" href="{{ $clinic['maps'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['directions'] }} ↗</a><p class="payment">{{ $copy['payment'] }}</p></div>
                <div id="hours" tabindex="-1"><h3>{{ $copy['hours_label'] }}</h3>@include('public.hours')</div>
                <div class="contact-action"><p>{{ $copy['appointment'] }}</p><a class="button light" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer">{{ $copy['short_cta'] }} ↗</a><a class="light-link" href="tel:{{ $clinic['phone'] }}">{{ $copy['call'] }}</a>@if(filled($clinic['email'] ?? null))<a class="light-link" href="mailto:{{ $clinic['email'] }}">{{ $clinic['email'] }}</a>@endif</div>
            </div>
        </div>
    </section>
</main>
<footer class="site-footer"><div class="container"><div class="footer-top"><span>© {{ now()->year }} Medicina Respiratoria</span><div class="footer-links">@include('public.privacylink')<a href="{{ $clinic['facebook'] }}" target="_blank" rel="noopener noreferrer">Facebook ↗</a></div></div><p>{{ $copy['disclaimer'] }}</p></div></footer>
<nav class="mobile-contact" aria-label="{{ $locale === 'es' ? 'Contactar al consultorio' : 'Contact the office' }}">
    <a class="button secondary" href="tel:{{ $clinic['phone'] }}">{{ $locale === 'es' ? 'Llamar' : 'Call' }}</a>
    <a class="button" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer">WhatsApp <span aria-hidden="true">↗</span></a>
</nav>
</body>
</html>
