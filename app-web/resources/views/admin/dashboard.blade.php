<x-admin-layout title="Resumen">
    <div class="page-heading"><div><span class="eyebrow">TU CONSULTORIO, AL DÍA</span><h1>Hola, {{ explode(' ', auth()->user()->name)[0] }}<span class="accent">.</span></h1><p>Este es el estado del contenido de tu sitio.</p></div>@if(Route::has('admin.preview'))<a class="button secondary" href="{{ route('admin.preview', 'es') }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> Vista previa</a>@endif</div>
    @include('admin.partials.dashboard-tasks')
    <div class="stats-grid">
        <article class="stat"><div class="stat-heading"><span>Servicios en catálogo</span><x-site-icon name="list" /></div><strong>{{ $total }}</strong><small>Organizados en cinco categorías</small></article>
        <article class="stat"><div class="stat-heading"><span>Servicios publicados</span><x-site-icon name="publish" /></div><strong>{{ $published }}</strong><small>Con una versión pública</small></article>
        <article class="stat"><div class="stat-heading"><span>Borradores por revisar</span><x-site-icon name="edit" /></div><strong>{{ $drafts }}</strong><small>Servicios y contenidos del sitio</small></article>
    </div>
    <section class="panel"><div class="section-heading"><h2>Actividad reciente</h2><span class="muted small">Últimos cambios</span></div>@include('admin.partials.activity', ['events' => $events])</section>
</x-admin-layout>
