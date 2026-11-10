<!doctype html>
<html lang="{{ $locale === 'es' ? 'es-MX' : 'en-US' }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $copy['title'] }}</title><meta name="description" content="{{ $copy['description'] }}">
    <meta name="robots" content="{{ !$clinic['indexable'] || $preview ? 'noindex, nofollow' : 'index, follow' }}">
    <link rel="canonical" href="{{ rtrim(config('app.url'), '/') }}/">
    <meta property="og:type" content="website"><meta property="og:title" content="{{ $copy['title'] }}"><meta property="og:description" content="{{ $copy['description'] }}"><meta property="og:url" content="{{ rtrim(config('app.url'), '/') }}/"><meta property="og:image" content="{{ $clinic['images']['doctor']['url'] }}"><meta property="og:locale" content="{{ $locale === 'es' ? 'es_MX' : 'en_US' }}">
    @if(!$preview)<script type="application/ld+json">{!! $structuredData !!}</script>@endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="public-home">
<a class="skip-link" href="#main">{{ $locale === 'es' ? 'Ir al contenido' : 'Skip to content' }}</a>
@if($preview)<div class="preview-banner">Vista previa privada · incluye borradores sin publicar <a href="{{ route('admin.dashboard') }}"><x-site-icon name="grid" /> Volver al panel</a></div>@endif
<div class="location-bar"><div class="container"><span>{{ $copy['location'] }} <span class="location-separator">/</span> <span class="appointment-top">{{ $locale === 'es' ? 'Atención con cita previa' : 'By appointment' }}</span></span><a href="tel:{{ $clinic['phone'] }}"><x-site-icon name="phone" /> {{ $clinic['phone_display'] }}</a></div></div>
<header class="site-header"><div class="container header-inner">
    <a class="site-brand" href="{{ ($preview ? route('admin.preview', $locale) : route('home')) }}"><img src="{{ asset('images/logo.png') }}" alt="Medicina Respiratoria · Dr. Jesús García Espinoza" width="1400" height="445"></a>
    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-navigation"><x-site-icon name="menu" class="menu-open-icon" /><x-site-icon name="close" class="menu-close-icon" /> {{ $locale === 'es' ? 'Menú' : 'Menu' }}</button>
    <nav id="site-navigation" aria-label="{{ $locale === 'es' ? 'Principal' : 'Main navigation' }}">@foreach(['services', 'physician', 'office', 'location'] as $i => $anchor)<a href="#{{ $anchor }}">{{ $copy['nav'][$i] }}</a>@endforeach<a href="#hours">{{ $locale === 'es' ? 'Horarios' : 'Hours' }}</a></nav>
    <div class="header-actions"><x-public-language-switch :locale="$locale" :preview="$preview" /><x-appointment-link :clinic="$clinic" :copy="$copy" :locale="$locale" class="button compact" :compact="true" /></div>
</div></header>
<main id="main">
    <section class="hero container"><div class="hero-copy"><span class="eyebrow"><span class="little-line"></span> {{ $copy['practice_label'] }}</span><h1><span class="sr-only">{{ $clinic['name'] }}: </span>{{ $copy['hero'] }}</h1><p class="hero-intro">{{ $copy['intro'] }}</p><x-appointment-link :clinic="$clinic" :copy="$copy" :locale="$locale" class="button" /><p class="appointment-note">{{ $copy['appointment'] }}</p>
        <nav class="visit-shortcuts" aria-label="{{ $locale === 'es' ? 'Planea tu visita' : 'Plan your visit' }}">
            <a href="#hours"><span class="shortcut-icon"><x-site-icon name="clock" /></span> {{ $locale === 'es' ? 'Ver horarios' : 'View office hours' }}</a>
            @if($clinic['faq'])<a href="#faq"><span class="shortcut-icon"><x-site-icon name="message" /></span> {{ $locale === 'es' ? 'Preguntas frecuentes' : 'Common questions' }}</a>@endif
            <a href="{{ $clinic['maps'] }}" target="_blank" rel="noopener noreferrer"><span class="shortcut-icon"><x-site-icon name="pin" /></span> {{ $copy['directions'] }}</a>
        </nav>
        <a class="explore-link" href="#services"><x-site-icon name="grid" /> {{ $copy['explore'] }}</a></div><div class="hero-image"><img src="{{ $clinic['images']['doctor']['url'] }}" alt="{{ $clinic['images']['doctor']['alt'][$locale] }}" width="{{ $clinic['images']['doctor']['width'] }}" height="{{ $clinic['images']['doctor']['height'] }}" fetchpriority="high"><div class="portrait-caption"><strong>{{ $clinic['physician'] }}</strong><span>{{ $copy['designation'] }}</span></div><span class="photo-index" aria-hidden="true">01 / MEDICINA RESPIRATORIA</span></div></section>
    <section class="care-section"><div class="container care-inner"><h2>{{ $copy['care_title'] }}</h2><p>{{ $copy['care_copy'] }}</p></div></section>
    <section class="section container" id="services"><div class="section-heading"><div><span class="eyebrow">{{ $copy['services_label'] }}</span><h2>{{ $copy['services_title'] }}</h2></div><p>{{ $copy['services_copy'] }}</p></div>
        @if($services->isNotEmpty())@include('public.service-search')@endif
        <div id="service-catalog">
        @foreach($categories as $category)
            @if($services->has($category->id))
                <div class="service-group" data-service-group>
                    <div class="category-heading"><span class="category-number">0{{ $loop->iteration }}</span><h3>{{ $category->name[$locale] }}</h3></div>
                    <ul class="service-items service-catalog-list">
                        @foreach($services[$category->id] as $service)
                            @include('public.service-item')
                        @endforeach
                    </ul>
                </div>
            @endif
        @endforeach
        </div>
        @if($services->isNotEmpty())<p class="service-request-help">{{ $locale === 'es' ? 'Elige «Solicitar servicio» para abrir WhatsApp con su nombre en un mensaje que puedes editar antes de enviarlo.' : 'Choose “Request service” to open WhatsApp with the service name in a message you can edit before sending.' }}</p>@endif
        @if($services->isEmpty())<div class="availability"><p>{{ $copy['availability'] }}</p><a class="button" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="message" /> {{ $locale === 'es' ? 'Consultar servicios por WhatsApp' : 'Ask about services on WhatsApp' }}</a></div>@endif
        <p class="procedure-note">{{ $copy['procedure_note'] }}</p>
    </section>
    <section class="physician-section" id="physician"><div class="container physician-inner"><div class="physician-image"><img src="{{ $clinic['images']['doctor']['url'] }}" alt="{{ $clinic['images']['doctor']['alt'][$locale] }}" width="{{ $clinic['images']['doctor']['width'] }}" height="{{ $clinic['images']['doctor']['height'] }}" loading="lazy"></div><div><span class="eyebrow">{{ $copy['doctor_label'] }}</span><h2>{{ $clinic['physician'] }}</h2><p class="designation">{{ $copy['designation'] }}</p><p>{{ $copy['bio'] }}</p><p>{{ $copy['bio_second'] }}</p><div class="license"><span>{{ $copy['license_label'] }}</span><strong>{{ $clinic['license'] }}</strong></div></div></div></section>
    <section class="section container" id="office"><div class="section-heading"><div><span class="eyebrow">{{ $copy['office_label'] }}</span><h2>{{ $copy['office_title'] }}</h2></div><p>{{ $copy['office_copy'] }}</p></div><div class="office-gallery">@foreach(['consultorio', 'fachada', 'entrada'] as $i => $photo)<figure><img src="{{ $clinic['images'][$photo]['url'] }}" alt="{{ $clinic['images'][$photo]['alt'][$locale] }}" loading="lazy" width="{{ $clinic['images'][$photo]['width'] }}" height="{{ $clinic['images'][$photo]['height'] }}"><figcaption><span>0{{ $i + 1 }}</span> {{ $copy['photos'][$i] }}</figcaption></figure>@endforeach</div></section>
    @include('public.faq')
    <section class="contact-section" id="location">
        <div class="container"><span class="eyebrow">{{ $copy['contact_label'] }}</span><h2>{{ $copy['contact_title'] }}</h2>
            <div class="contact-grid visit-layout">
                @include('public.visit-card')
                <div class="contact-action">@include('public.appointment-steps')<p>{{ $copy['appointment'] }}</p><x-appointment-link :clinic="$clinic" :copy="$copy" :locale="$locale" class="button light" :compact="true" /><a class="light-link" href="tel:{{ $clinic['phone'] }}"><x-site-icon name="phone" /> {{ $copy['call'] }}</a>@if(filled($clinic['email'] ?? null))<a class="light-link" href="mailto:{{ $clinic['email'] }}"><x-site-icon name="mail" /> {{ $clinic['email'] }}</a>@endif</div>
            </div>
        </div>
    </section>
</main>
<footer class="site-footer"><div class="container"><div class="footer-top"><span>© {{ now()->year }} Medicina Respiratoria</span><div class="footer-links">@include('public.privacylink')<a href="{{ $clinic['facebook'] }}" target="_blank" rel="noopener noreferrer">Facebook</a></div></div><p>{{ $copy['disclaimer'] }}</p></div></footer>
<nav class="mobile-contact" aria-label="{{ $locale === 'es' ? 'Contactar al consultorio' : 'Contact the office' }}">
    <a class="button secondary" href="tel:{{ $clinic['phone'] }}"><x-site-icon name="phone" /> {{ $locale === 'es' ? 'Llamar' : 'Call' }}</a>
    <x-appointment-link :clinic="$clinic" :copy="$copy" :locale="$locale" :compact="true" />
</nav>
</body>
</html>
