<x-admin-layout title="Resumen">
    <div class="page-heading"><div><span class="eyebrow">TU CONSULTORIO, AL DÍA</span><h1>Hola, {{ explode(' ', auth()->user()->name)[0] }}<span class="accent">.</span></h1><p>Este es el estado del contenido de tu sitio.</p></div>@if(Route::has('admin.preview'))<a class="button secondary" href="{{ route('admin.preview', 'es') }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> Vista previa</a>@endif</div>
    @include('admin.partials.dashboard-shortcuts')
    @include('admin.partials.panel-guide')
    <div class="stats-grid dashboard-stats">
        <article class="stat"><div class="stat-heading"><span>Servicios en catálogo</span><x-site-icon name="list" /></div><strong>{{ $total }}</strong><small>Organizados en cinco categorías</small></article>
        <article class="stat"><div class="stat-heading"><span>Servicios visibles</span><x-site-icon name="publish" /></div><strong>{{ $published }}</strong><a class="small-link" href="{{ route('admin.services.index', ['status' => 'published']) }}">Ver publicados</a></article>
        <article class="stat"><div class="stat-heading"><span>Servicios ocultos</span><x-site-icon name="eye" /></div><strong>{{ $hidden }}</strong><a class="small-link" href="{{ route('admin.services.index', ['status' => 'hidden']) }}">Revisar ocultos</a></article>
        <article class="stat"><div class="stat-heading"><span>Borradores por revisar</span><x-site-icon name="edit" /></div><strong>{{ $drafts }}</strong><small>Servicios y contenidos del sitio</small></article>
    </div>
    @include('admin.partials.dashboard-tasks')
    <section class="panel"><div class="section-heading"><h2>Actividad reciente</h2><span class="muted small">Últimos cambios</span></div>@include('admin.partials.activity', ['events' => $events])</section>
</x-admin-layout>
