<section class="panel">
    <h2>Preguntas guardadas</h2>
    @forelse($revision->payload['items'] as $item)
        <article class="faq-history-item">
            <h3>Pregunta {{ $loop->iteration }} · {{ $item['visible'] ? 'Visible al publicar' : 'Oculta' }}</h3>
            <dl class="revision-fields">
                @foreach(['es' => 'Español', 'en' => 'English'] as $language => $label)
                    <dt>{{ $label }} · {{ $item[$language]['question'] }}</dt><dd lang="{{ $language }}">{{ $item[$language]['answer'] }}</dd>
                @endforeach
            </dl>
        </article>
    @empty
        <p class="muted small">Esta versión no contiene preguntas. La sección estará oculta al publicarla.</p>
    @endforelse
</section>
