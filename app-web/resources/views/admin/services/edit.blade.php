<x-admin-layout title="Editar servicio">
    @php($payload = $service->currentRevision->payload)
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.services.index') }}"><x-site-icon name="list" /> Todos los servicios</a><h1 style="margin-top:18px">{{ $payload['es']['name'] }}</h1><p>Editar servicio · {{ $service->category->name['es'] }}</p></div><span class="badge {{ $service->hasDraft() ? 'draft' : 'published' }}">{{ $service->hasDraft() ? 'Sin publicar' : 'En el sitio' }}</span></div>
    <x-editor-actions :target="$service" form="service-editor" />
    <div class="editor-grid">
        <form id="service-editor" data-editor data-pending-publish="{{ $service->hasDraft() ? 'true' : 'false' }}" data-recovered-input="{{ session()->hasOldInput('es') || session()->hasOldInput('en') ? 'true' : 'false' }}" method="post" action="{{ route('admin.services.update', $service) }}" class="editor-fields">
            @csrf @method('PUT')<input type="hidden" name="revision_id" value="{{ old('revision_id', $service->current_revision_id) }}">
            <x-language-tabs />
            @foreach(['es' => 'Español · ES', 'en' => 'Inglés · EN'] as $locale => $label)
                <section class="panel" id="language-{{ $locale }}" data-language-panel><h2 class="language-label">{{ $label }}</h2>
                    <label for="name-{{ $locale }}">Nombre del servicio<input id="name-{{ $locale }}" name="{{ $locale }}[name]" value="{{ old($locale.'.name', $payload[$locale]['name']) }}" required maxlength="200" lang="{{ $locale }}"></label>@error($locale.'.name')<p class="field-error">{{ $message }}</p>@enderror
                    <label for="description-{{ $locale }}">Descripción<textarea id="description-{{ $locale }}" name="{{ $locale }}[description]" rows="6" required maxlength="3000" lang="{{ $locale }}">{{ old($locale.'.description', $payload[$locale]['description']) }}</textarea></label>@error($locale.'.description')<p class="field-error">{{ $message }}</p>@enderror
                    <p class="field-help">Texto simple, hasta 3,000 caracteres. Conserva las condiciones de disponibilidad del servicio.</p>
                    <h3 style="margin-top:24px">Información para la visita</h3>
                    <p class="field-help">Opcional. Incluye únicamente indicaciones confirmadas por el médico, en ambos idiomas. Los campos vacíos no aparecen en el sitio.</p>
                    @foreach(['preparation' => ['Preparación previa', 1500], 'duration' => ['Duración aproximada', 200], 'bring' => ['Qué llevar', 1000]] as $field => [$fieldLabel, $limit])
                        <label for="{{ $field }}-{{ $locale }}">{{ $fieldLabel }}<textarea id="{{ $field }}-{{ $locale }}" name="{{ $locale }}[{{ $field }}]" rows="{{ $field === 'duration' ? 2 : 3 }}" maxlength="{{ $limit }}" lang="{{ $locale }}">{{ old($locale.'.'.$field, $payload[$locale][$field] ?? '') }}</textarea></label>
                        @error($locale.'.'.$field)<p class="field-error">{{ $message }}</p>@enderror
                    @endforeach
                </section>
            @endforeach
            @include('admin.services.image-field')
            <section class="panel"><h2>Presentación</h2><label style="margin-top:20px">Orden dentro de la categoría<input type="number" name="position" value="{{ old('position', $payload['position']) }}" min="1" max="999" required></label><input type="hidden" name="visible" value="0"><label style="margin-top:20px"><input type="checkbox" name="visible" value="1" @checked(old('visible', $payload['visible']))> Mostrar este servicio en el sitio</label></section>
        </form>
        <aside class="editor-aside"><section class="panel"><h2>Historial</h2>@foreach($revisions as $revision)<div class="revision-row"><a class="small-link" href="{{ route('admin.services.revision', [$service, $revision]) }}">Versión #{{ $revision->id }}</a><small>{{ $revision->author?->name ?? 'Importación inicial' }}</small><small>{{ $revision->created_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}</small>@if($revision->id === $service->published_revision_id)<span class="badge published">Versión pública</span>@endif</div>@endforeach<div class="pagination">{{ $revisions->links('pagination::simple-tailwind') }}</div></section></aside>
    </div>
</x-admin-layout>
