<x-admin-layout title="Servicios">
    <div class="page-heading"><div><span class="eyebrow">CONTENIDO DEL SITIO</span><h1>Servicios</h1><p>Información médica clara, en español e inglés.</p></div><span class="badge">{{ $services->total() }} resultados</span></div>
    <section class="panel">
        <div class="section-heading"><h2>Catálogo de servicios</h2><a class="button secondary" href="{{ route('admin.services.order') }}"><x-site-icon name="list" /> Ordenar servicios</a></div>
        <form method="get" class="filters">
            <label>Buscar servicio<input type="search" name="q" value="{{ request('q') }}" placeholder="Nombre en español o inglés" maxlength="100"></label>
            <label>Categoría<select name="category"><option value="">Todas las categorías</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name['es'] }}</option>@endforeach</select></label>
            <label>Estado<select name="status"><option value="">Todos los estados</option><option value="draft" @selected(request('status') === 'draft')>Pendientes de publicar</option><option value="published" @selected(request('status') === 'published')>Publicados y visibles</option><option value="hidden" @selected(request('status') === 'hidden')>Ocultos</option></select></label>
            <button class="button primary"><x-site-icon name="search" /> Filtrar</button><a href="{{ route('admin.services.index') }}" class="small-link">Limpiar</a>
        </form>
        <div class="table-wrap"><table><thead><tr><th>Servicio</th><th>Categoría</th><th>Estado</th><th><span class="sr-only">Acciones</span></th></tr></thead><tbody>
        @forelse($services as $service)<tr><td><strong>{{ $service->currentRevision->payload['es']['name'] }}</strong><small>{{ $service->currentRevision->payload['en']['name'] }}</small></td><td>{{ $service->category->name['es'] }}</td><td>@if($service->published_revision_id)<span class="badge published">{{ $service->publishedRevision->payload['visible'] ? 'Publicado' : 'Oculto' }}</span>@endif @if($service->hasDraft())<span class="badge draft">Borrador</span>@endif</td><td><a class="small-link" href="{{ route('admin.services.edit', $service) }}" aria-label="Editar {{ $service->currentRevision->payload['es']['name'] }}"><x-site-icon name="edit" /> Editar</a></td></tr>
        @empty<tr><td colspan="4"><p class="empty-state">No encontramos servicios con estos filtros.</p></td></tr>@endforelse
        </tbody></table></div><div class="pagination">{{ $services->links() }}</div>
    </section>
</x-admin-layout>
