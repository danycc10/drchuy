<x-admin-layout title="Biblioteca de imágenes">
    <div class="page-heading">
        <div><span class="eyebrow">FOTOGRAFÍAS DEL CONSULTORIO</span><h1>Biblioteca de imágenes</h1><p>Conserva tus originales y prepara fotografías ligeras para el sitio.</p></div>
        @if($mediaBlock)<a class="button secondary" href="{{ route('admin.content.edit', $mediaBlock) }}"><x-site-icon name="image" /> Fotografías del sitio</a>@endif
    </div>
    <details class="panel media-upload" @if($errors->has('title') || $errors->has('image') || $assets->isEmpty()) open @endif>
        <summary><x-site-icon name="image" /> Subir fotografía <x-site-icon name="chevron-down" /></summary>
        <form method="post" action="{{ route('admin.media.store') }}" enctype="multipart/form-data" class="media-upload-form" data-media-upload>
            @csrf
            <label for="media-title">Nombre para identificarla<input id="media-title" name="title" value="{{ old('title') }}" maxlength="150" required placeholder="Ej. Sala de espera"></label>
            <label for="media-file">Fotografía<input id="media-file" name="image" type="file" accept="image/jpeg,image/png,image/webp" required aria-describedby="media-file-help"></label>
            <p id="media-file-help" class="field-help">JPG, PNG o WebP · máximo 8 MB y 12 megapíxeles. Se conserva el original y se crea una copia WebP optimizada. Subir una imagen todavía no la publica.</p>
            <p class="field-help">Usa fotografías del médico y las instalaciones autorizadas para el sitio.</p>
            <button class="button primary" data-upload-button><x-site-icon name="save" /><span data-upload-label>Subir fotografía</span></button>
        </form>
    </details>
    <section class="panel">
        <form method="get" class="filters">
            <label>Buscar fotografía<input type="search" name="q" value="{{ request('q') }}" maxlength="100" placeholder="Nombre o archivo original"></label>
            <label>Uso<select name="usage"><option value="">Todas las imágenes</option><option value="used" @selected(request('usage') === 'used')>Usadas en contenido o historial</option><option value="unused" @selected(request('usage') === 'unused')>Sin usar</option></select></label>
            <button class="button primary"><x-site-icon name="search" /> Filtrar</button><a class="small-link" href="{{ route('admin.media.index') }}">Limpiar</a>
        </form>
        <p class="muted small media-count">{{ $assets->total() }} fotografías</p>
        <div class="media-grid">
            @forelse($assets as $asset)
                <article class="media-card">
                    <a class="media-thumbnail" href="{{ $asset->url(true) }}" target="_blank" rel="noopener noreferrer" aria-label="Ampliar {{ $asset->title }} (pestaña nueva)"><img src="{{ $asset->url(true, 'thumb') }}" alt="{{ $asset->title }}" loading="lazy" width="{{ $asset->display_width }}" height="{{ $asset->display_height }}"></a>
                    <div class="media-card-body"><h2>{{ $asset->title }}</h2><p class="media-filename">{{ $asset->original_name }}</p>
                        <p class="field-help">{{ $asset->width }} × {{ $asset->height }} px · original {{ number_format($asset->original_bytes / 1024, 0) }} KB<br>WebP del sitio: {{ number_format($asset->display_bytes / 1024, 0) }} KB</p>
                        <span class="badge {{ ($asset->revisions_count + $asset->service_revisions_count) ? 'published' : '' }}">{{ ($asset->revisions_count + $asset->service_revisions_count) ? 'Conservada en contenido o historial' : 'Sin usar' }}</span>
                        <div class="media-card-actions">
                            @can('download', $asset)<a class="small-link" href="{{ route('admin.media.original', $asset) }}">Descargar original</a>@endcan
                            @can('delete', $asset)@if(!($asset->revisions_count + $asset->service_revisions_count))<form method="post" action="{{ route('admin.media.destroy', $asset) }}" data-confirm-detail="{{ $asset->title }}" data-confirm="¿Eliminar esta fotografía sin usar y sus archivos? Esta acción no se puede deshacer.">@csrf @method('DELETE')<button class="small-link danger"><x-site-icon name="close" /> Eliminar</button></form>@endif @endcan
                        </div>
                    </div>
                </article>
            @empty<p class="empty-state">No hay fotografías con estos filtros. Puedes subir una nueva o limpiar la búsqueda.</p>@endforelse
        </div>
        <div class="pagination">{{ $assets->links() }}</div>
    </section>
</x-admin-layout>
