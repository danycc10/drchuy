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
        <main id="main" class="auth-form"><span class="eyebrow">ADMINISTRACIÓN</span><h2>{{ $title }}</h2><x-feedback />{{ $slot }}<p class="auth-foot">Acceso exclusivo para el equipo del consultorio.</p></main>
    </div>
@else
    <aside class="sidebar" id="admin-navigation">
        <a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="brand-mark">MR<span>+</span></span><span>Medicina<br><strong>Respiratoria</strong></span></a>
        <span class="nav-caption">ESPACIO DE TRABAJO</span>
        <nav aria-label="Administración">
            <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><span aria-hidden="true">◫</span> Resumen</a>
            @if(Route::has('admin.services.index'))<a class="{{ request()->routeIs('admin.services.*') ? 'active' : '' }}" href="{{ route('admin.services.index') }}"><span aria-hidden="true">☷</span> Servicios</a>@endif
            @if(Route::has('admin.activity'))<a class="{{ request()->routeIs('admin.activity') ? 'active' : '' }}" href="{{ route('admin.activity') }}"><span aria-hidden="true">↺</span> Actividad</a>@endif
        </nav>
        <div class="sidebar-bottom"><span class="status-dot"></span> Panel del consultorio<p>Contenido claro.<br>Atención cercana.</p></div>
    </aside>
    <div class="admin-workspace">
        <header class="admin-topbar"><button type="button" class="menu-toggle" data-menu-toggle aria-controls="admin-navigation" aria-expanded="false">Menú</button><span class="breadcrumb">Consultorio <span>/</span> {{ $title }}</span><div class="account"><span class="avatar">{{ mb_substr(auth()->user()->name, 0, 1) }}</span><span>{{ auth()->user()->name }}<small>{{ auth()->user()->role->label() }}</small></span><form method="post" action="{{ route('logout') }}">@csrf<button class="text-button">Salir</button></form></div></header>
        <main id="main" class="admin-main"><x-feedback />{{ $slot }}</main>
        <footer class="admin-footer">Medicina Respiratoria <span>Administración del sitio</span></footer>
    </div>
@endif
</body>
</html>
