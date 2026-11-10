<x-admin-layout :title="$block->label()">
    @php($payload = $block->currentRevision->payload)
    <div class="page-heading"><div><a class="small-link" href="{{ route('admin.content.index') }}"><x-site-icon name="document" /> Contenido y horarios</a><h1 style="margin-top:18px">{{ $block->label() }}</h1><p>{{ $schema['description'] }}</p></div><span class="badge {{ $block->hasDraft() ? 'draft' : 'published' }}">{{ $block->hasDraft() ? 'Borrador' : 'Publicado' }}</span></div>
    @if($block->key === 'legal')<div class="notice">Incorpora el aviso validado para los tratamientos de datos reales del sitio. Puedes guardar un borrador incompleto; para publicar necesitas el texto en ambos idiomas.</div>@endif
    @if($block->key === 'physician')<div class="notice">Conserva la denominación pública y las credenciales autorizadas. Los cambios requieren revisión y publicación por el propietario.</div>@endif
    @if($block->key === 'media')<div class="notice">Elige las fotografías y ajusta sus descripciones en español e inglés. Guarda, revisa ambas vistas previas y publica con la cuenta propietaria. <a class="small-link" href="{{ route('admin.media.index') }}">Abrir biblioteca de imágenes</a></div>@endif
    <x-editor-workflow :target="$block" form="content-editor" />
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
            @if($block->key === 'faq')@include('admin.content.faq')@endif
        </form>
        <aside class="editor-aside"><section class="panel"><h2>Historial</h2>@foreach($revisions as $revision)<div class="revision-row"><a class="small-link" href="{{ route('admin.content.revision', [$block, $revision]) }}">Versión #{{ $revision->id }}</a><small>{{ $revision->author?->name ?? 'Importación inicial' }}</small><small>{{ $revision->created_at->timezone('America/Matamoros')->format('d/m/Y H:i') }}</small>@if($revision->id === $block->published_revision_id)<span class="badge published">Versión pública</span>@endif</div>@endforeach<div class="pagination">{{ $revisions->links('pagination::simple-tailwind') }}</div></section></aside>
    </div>
</x-admin-layout>
