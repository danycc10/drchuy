<x-admin-layout :title="$block->label()">
    @php($payload = $block->currentRevision->payload)
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.content.index') }}"><x-site-icon name="document" /> Contenido y horarios</a><h1 style="margin-top:18px">{{ $block->label() }}</h1><p>{{ $schema['description'] }}</p></div><span class="badge {{ $block->hasDraft() ? 'draft' : 'published' }}">{{ $block->hasDraft() ? 'Borrador' : 'Publicado' }}</span></div>
    @if($block->key === 'legal')<div class="notice">Incorpora el aviso validado para los tratamientos de datos reales del sitio. Puedes guardar un borrador incompleto; para publicar necesitas el texto en ambos idiomas.</div>@endif
    @if($block->key === 'physician')<div class="notice">Conserva la denominación pública y las credenciales autorizadas. Los cambios requieren revisión y publicación por el propietario.</div>@endif
    <x-editor-toolbar form="content-editor" />
    <div class="editor-grid">
        <form id="content-editor" data-editor data-recovered-input="{{ session()->hasOldInput('payload') ? 'true' : 'false' }}" method="post" action="{{ route('admin.content.update', $block) }}" class="editor-fields">
            @csrf @method('PUT')<input type="hidden" name="revision_id" value="{{ old('revision_id', $block->current_revision_id) }}">
            @if($schema['shared'])<section class="panel"><h2 class="language-label">Datos compartidos</h2>@include('admin.content.fields', ['fields' => $schema['shared'], 'group' => 'shared'])</section>@endif
            @if($block->key === 'hours')@include('admin.content.hours')@endif
            @if($schema['translated'])
                <x-language-tabs />
                @foreach(['es' => 'Español · ES', 'en' => 'Inglés · EN'] as $group => $label)
                    <section class="panel" id="language-{{ $group }}" data-language-panel><h2 class="language-label">{{ $label }}</h2>@include('admin.content.fields', ['fields' => $schema['translated'], 'group' => $group])</section>
                @endforeach
            @endif
        </form>
        <aside class="editor-aside"><section class="panel"><h2>Revisar y publicar</h2><p class="muted small">La vista previa abre la última versión guardada en una pestaña nueva.</p>
            @if($block->key === 'legal' && (!filled($payload['es']['privacy_content'] ?? '') || !filled($payload['en']['privacy_content'] ?? '')))
                <p class="field-help" style="margin-top:12px">Guarda el aviso en español e inglés para habilitar su vista previa.</p>
            @else
            <div class="preview-links" aria-label="Vista previa">
                @foreach(['es' => 'Español', 'en' => 'Inglés'] as $locale => $language)
                    <a class="small-link" href="{{ route($block->key === 'legal' ? 'admin.preview.privacy' : 'admin.preview', $locale) }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> {{ $language }}</a>
                @endforeach
            </div>
            @endif
            <p data-dirty-note class="dirty-note" id="publish-help" hidden>Guarda tus cambios antes de revisarlos o publicarlos.</p>
            @can('publish', $block)<form method="post" action="{{ route('admin.content.publish', $block) }}" data-confirm="¿Publicar la última versión guardada de este contenido?">@csrf<input type="hidden" name="revision_id" value="{{ $block->current_revision_id }}"><button data-publish-button aria-describedby="publish-help" class="button secondary" @disabled(!$block->hasDraft())><x-site-icon name="publish" /> Publicar versión guardada</button></form>@else<p class="field-help" style="margin-top:16px">{{ ($schema['owner_only'] ?? false) ? 'Solo el propietario puede publicar este bloque.' : 'Un administrador o el propietario debe publicar los cambios.' }}</p>@endcan
        </section><section class="panel"><h2>Historial</h2>@foreach($revisions as $revision)<div class="revision-row"><a class="small-link" href="{{ route('admin.content.revision', [$block, $revision]) }}">Versión #{{ $revision->id }}</a><small>{{ $revision->author?->name ?? 'Importación inicial' }}</small><small>{{ $revision->created_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}</small>@if($revision->id === $block->published_revision_id)<span class="badge published">Versión pública</span>@endif</div>@endforeach<div class="pagination">{{ $revisions->links('pagination::simple-tailwind') }}</div></section></aside>
    </div>
</x-admin-layout>
