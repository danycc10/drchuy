@props(['title', 'guest' => false])
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ $title }} · Medicina Respiratoria</title>
    @vite(['resources/css/admin.css', 'resources/js/admin.js'])
</head>
<body class="{{ $guest ? 'auth-body' : 'admin-body' }}">
<a class="skip-link" href="#main">Ir al contenido</a>
@if($guest)
    <div class="auth-shell">
        <aside class="auth-story"><span class="eyebrow">MEDICINA RESPIRATORIA</span><h1>El cuidado empieza<br>en los detalles.</h1><p>Un espacio para mantener la información de tu consultorio clara, cercana y al día.</p><div class="story-line"></div><small>Dr. Jesús García Espinoza<br>Piedras Negras, Coahuila</small></aside>
        <main id="main" class="auth-form"><a class="auth-brand" href="{{ route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="Medicina Respiratoria" width="4140" height="1314"></a><span class="eyebrow">ADMINISTRACIÓN</span><h2>{{ $title }}</h2><x-feedback />{{ $slot }}<p class="auth-foot">Acceso exclusivo para el equipo del consultorio.</p></main>
    </div>
@else
    <button type="button" class="nav-backdrop" data-nav-backdrop tabindex="-1" aria-label="Cerrar menú" hidden></button>
    <aside class="sidebar" id="admin-navigation" aria-label="Menú de administración">
        <button type="button" class="nav-close" data-menu-close>Cerrar menú <x-site-icon name="close" /></button>
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><img src="{{ asset('images/logo-white.png') }}" alt="Medicina Respiratoria · Dr. Jesús García Espinoza" style="width:100%;height:auto"></a>
        <span class="nav-caption">ESPACIO DE TRABAJO</span>
        <nav aria-label="Administración">
            <a href="{{ route('admin.dashboard', ['guide' => 1]) }}#panel-guide"><x-site-icon name="document" /> Guía del panel</a>
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}" href="{{ route('admin.dashboard') }}"><x-site-icon name="grid" /> Resumen</a>
            @if(Route::has('admin.services.index'))<a class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.services.*') ? 'page' : 'false' }}" href="{{ route('admin.services.index') }}"><x-site-icon name="list" /> Servicios</a>@endif
            <a class="{{ request()->routeIs('admin.content.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.content.*') ? 'page' : 'false' }}" href="{{ route('admin.content.index') }}"><x-site-icon name="document" /> Contenido y horarios</a>
            <a class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.media.*') ? 'page' : 'false' }}" href="{{ route('admin.media.index') }}"><x-site-icon name="image" /> Imágenes</a>
            @can('viewAny', \App\Models\User::class)<a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.users.*') ? 'page' : 'false' }}" href="{{ route('admin.users.index') }}"><x-site-icon name="user" /> Usuarios</a>@endcan
            <a class="{{ request()->routeIs('admin.security*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.security*') ? 'page' : 'false' }}" href="{{ route('admin.security') }}"><x-site-icon name="lock" /> Mi seguridad</a>
            @can('viewAny', \App\Models\User::class)<a class="{{ request()->routeIs('admin.maintenance*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.maintenance*') ? 'page' : 'false' }}" href="{{ route('admin.maintenance') }}"><x-site-icon name="history" /> Mantenimiento</a>@endcan
            @if(Route::has('admin.activity'))<a class="{{ request()->routeIs('admin.activity') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.activity') ? 'page' : 'false' }}" href="{{ route('admin.activity') }}"><x-site-icon name="history" /> Actividad</a>@endif
        </nav>
        <div class="sidebar-bottom"><span class="status-dot"></span> Panel del consultorio<p>Contenido claro.<br>Atención cercana.</p></div>
    </aside>
    <div class="admin-workspace">
        <header class="admin-topbar"><button type="button" class="menu-toggle" data-menu-toggle aria-controls="admin-navigation" aria-expanded="false"><x-site-icon name="menu" /> Menú</button><span class="breadcrumb">Consultorio <span>/</span> {{ $title }}</span><div class="account"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span class="account-name">{{ auth()->user()->name }}<small>{{ auth()->user()->role->label() }}</small></span><form method="post" action="{{ route('logout') }}">@csrf<button class="text-button"><x-site-icon name="power" /> Salir</button></form></div></header>
        <main id="main" class="admin-main"><x-feedback />{{ $slot }}</main>
        <footer class="admin-footer">Medicina Respiratoria <span>Administración del sitio</span></footer>
    </div>
@endif
<x-confirmation-dialog />
</body>
</html>
