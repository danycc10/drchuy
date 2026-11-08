<x-admin-layout title="Contenido y horarios">
    <div class="page-heading"><div><span class="eyebrow">INFORMACIÓN DEL CONSULTORIO</span><h1>Contenido y horarios</h1><p>Actualiza tu sitio en ambos idiomas, con revisión antes de publicar.</p></div><a class="button secondary" href="{{ route('admin.preview', 'es') }}" target="_blank" rel="noopener noreferrer">Vista previa ↗</a></div>
    <div class="content-grid">
        @foreach($blocks as $block)
            <article class="panel content-card"><div class="section-heading"><h2>{{ $block->label() }}</h2><span class="badge {{ $block->hasDraft() ? 'draft' : 'published' }}">{{ $block->hasDraft() ? 'Borrador' : 'Publicado' }}</span></div><p class="muted small">{{ $schemas[$block->key]['description'] }}</p><a class="button secondary" href="{{ route('admin.content.edit', $block) }}">Editar contenido →</a></article>
        @endforeach
    </div>
</x-admin-layout>
