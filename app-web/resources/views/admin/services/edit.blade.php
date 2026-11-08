<x-admin-layout title="Editar servicio">
    @php($payload = $service->currentRevision->payload)
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.services.index') }}">← Todos los servicios</a><h1 style="margin-top:18px">Editar servicio</h1><p>{{ $service->category->name['es'] }}</p></div><span class="badge {{ $service->hasDraft() ? 'draft' : 'published' }}">{{ $service->hasDraft() ? 'Borrador' : 'Publicado' }}</span></div>
    <div class="editor-grid">
        <form id="service-editor" data-editor method="post" action="{{ route('admin.services.update', $service) }}" class="editor-fields">
            @csrf @method('PUT')<input type="hidden" name="revision_id" value="{{ old('revision_id', $service->current_revision_id) }}">
            @foreach(['es' => 'Español · ES', 'en' => 'English · EN'] as $locale => $label)
                <section class="panel"><h2 class="language-label">{{ $label }}</h2>
                    <label for="name-{{ $locale }}">Nombre del servicio<input id="name-{{ $locale }}" name="{{ $locale }}[name]" value="{{ old($locale.'.name', $payload[$locale]['name']) }}" required maxlength="200" lang="{{ $locale }}"></label>@error($locale.'.name')<p class="field-error">{{ $message }}</p>@enderror
                    <label for="description-{{ $locale }}">Descripción<textarea id="description-{{ $locale }}" name="{{ $locale }}[description]" rows="6" required maxlength="3000" lang="{{ $locale }}">{{ old($locale.'.description', $payload[$locale]['description']) }}</textarea></label>@error($locale.'.description')<p class="field-error">{{ $message }}</p>@enderror
                    <p class="field-help">Texto simple, hasta 3,000 caracteres. Conserva las condiciones de disponibilidad del servicio.</p>
                </section>
            @endforeach
            <section class="panel"><h2>Presentación</h2><label style="margin-top:20px">Orden dentro de la categoría<input type="number" name="position" value="{{ old('position', $payload['position']) }}" min="1" max="999" required></label><input type="hidden" name="visible" value="0"><label style="margin-top:20px"><input type="checkbox" name="visible" value="1" @checked(old('visible', $payload['visible']))> Mostrar este servicio al publicar</label></section>
        </form>
        <aside class="editor-aside"><section class="panel"><h2>Guardar y publicar</h2><p class="muted small">Guardar crea un borrador. Publicar aplica la última versión guardada al sitio.</p><p data-dirty-note class="dirty-note" hidden>Tienes cambios sin guardar.</p><button class="button primary" type="submit" form="service-editor">Guardar borrador</button>
            @can('publish', $service)<form method="post" action="{{ route('admin.services.publish', $service) }}" data-confirm="¿Publicar la última versión guardada de este servicio?">@csrf<input type="hidden" name="revision_id" value="{{ $service->current_revision_id }}"><button class="button secondary" @disabled(!$service->hasDraft())>Publicar versión guardada</button></form>
            @else<p class="field-help" style="margin-top:16px">{{ $service->category->slug === 'procedures' ? 'El propietario debe revisar y publicar los procedimientos.' : 'Un administrador o el propietario debe publicar tus cambios.' }}</p>@endcan
            @if($service->published_at)<p class="field-help" style="margin-top:15px">Última publicación: {{ $service->published_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}.</p>@endif
        </section><section class="panel"><h2>Historial</h2>@foreach($revisions as $revision)<div class="revision-row"><a class="small-link" href="{{ route('admin.services.revision', [$service, $revision]) }}">Versión #{{ $revision->id }}</a><small>{{ $revision->author?->name ?? 'Importación inicial' }}</small><small>{{ $revision->created_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}</small>@if($revision->id === $service->published_revision_id)<span class="badge published">Versión pública</span>@endif</div>@endforeach<div class="pagination">{{ $revisions->links('pagination::simple-tailwind') }}</div></section></aside>
    </div>
</x-admin-layout>
