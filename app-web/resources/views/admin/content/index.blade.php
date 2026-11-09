<x-admin-layout title="Contenido y horarios">
    <div class="page-heading"><div><span class="eyebrow">INFORMACIÓN DEL CONSULTORIO</span><h1>Contenido y horarios</h1><p>Actualiza tu sitio en ambos idiomas, con revisión antes de publicar.</p></div><a class="button secondary" href="{{ route('admin.preview', 'es') }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="eye" /> Vista previa</a></div>
    <div class="content-grid">
        @foreach($blocks as $block)
            @php($icon = match($block->key) { 'home' => 'home', 'physician' => 'user', 'office' => 'building', 'contact' => 'phone', 'seo' => 'search', 'hours' => 'calendar', 'media' => 'image', 'faq' => 'message', default => 'document' })
            <article class="panel content-card">
                <div class="content-card-top"><span class="module-icon"><x-site-icon :name="$icon" /></span><span class="badge {{ $block->hasDraft() ? 'draft' : 'published' }}">{{ $block->hasDraft() ? 'Borrador' : 'Publicado' }}</span></div>
                <h2>{{ $block->label() }}</h2>
                <p class="muted small">{{ $schemas[$block->key]['description'] }}</p>
                <a class="button secondary" href="{{ route('admin.content.edit', $block) }}" aria-label="Editar {{ $block->label() }}"><x-site-icon name="edit" /> Editar contenido</a>
            </article>
        @endforeach
    </div>
</x-admin-layout>
