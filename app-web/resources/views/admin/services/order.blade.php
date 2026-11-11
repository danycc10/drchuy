<x-admin-layout title="Ordenar servicios">
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.services.index') }}">Volver a servicios</a><h1>Ordenar servicios</h1><p>Organiza cómo aparecen dentro de cada categoría.</p></div></div>
    <section class="panel">
        <form method="get" class="filters"><label>Categoría<select name="category">@foreach($categories as $category)<option value="{{ $category->id }}" @selected($selected?->id === $category->id)>{{ $category->name['es'] }}</option>@endforeach</select></label><button class="button secondary">Ver categoría</button></form>
        <p class="field-help">Arrastra una tarjeta o utiliza Subir y Bajar. Guarda el orden como borrador y publica después cada servicio modificado. Cambiar de categoría sin guardar descarta el orden elegido.</p>
        <form method="post" action="{{ route('admin.services.reorder') }}" data-service-order data-editor>
            @csrf<input type="hidden" name="category" value="{{ $selected?->id }}">
            <ol class="service-order-list" data-order-list>
                @foreach($services as $service)<li data-order-item>
                    <input type="hidden" name="order[]" value="{{ $service->id }}"><input type="hidden" name="revisions[{{ $service->id }}]" value="{{ $service->current_revision_id }}">
                    <span class="order-number" aria-hidden="true">{{ $loop->iteration }}</span>
                    <div class="order-copy"><strong data-order-name>{{ $service->currentRevision->payload['es']['name'] }}</strong><div>@if($service->publishedRevision)<span class="badge published">{{ $service->publishedRevision->payload['visible'] ? 'Publicado' : 'Oculto' }}</span>@endif @if($service->hasDraft())<span class="badge draft">Pendiente de publicar</span>@endif</div><a class="small-link" href="{{ route('admin.services.edit', $service) }}">Editar y publicar</a></div>
                    <div class="order-actions" hidden data-order-controls><button type="button" class="button secondary" data-order-up aria-label="Subir {{ $service->currentRevision->payload['es']['name'] }}">Subir</button><button type="button" class="button secondary" data-order-down aria-label="Bajar {{ $service->currentRevision->payload['es']['name'] }}">Bajar</button></div>
                </li>@endforeach
            </ol>
            <p class="field-help" data-order-status role="status" aria-live="polite"></p>
            <p class="field-help" data-save-state role="status"></p>
            <button class="button primary" data-save-button>Guardar orden como borrador</button>
            <noscript><p class="field-help">Abre cada servicio para cambiar su número de orden sin JavaScript.</p></noscript>
        </form>
    </section>
</x-admin-layout>
