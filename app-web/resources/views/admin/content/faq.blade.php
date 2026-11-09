<section class="panel faq-editor" data-faq-editor>
    <h2>Preguntas y respuestas</h2>
    <p class="muted small">Hasta 12 preguntas, en el orden en que aparecerán en el sitio. Completa ambos idiomas y revisa las respuestas con el médico antes de publicar.</p>
    <p class="field-help">Desmarca «Mostrar en el sitio» para ocultar una pregunta al publicar. Si no hay preguntas visibles, la sección completa se oculta.</p>
    <noscript><p class="notice">Activa JavaScript para agregar, quitar o reordenar preguntas. Puedes editar y guardar las existentes.</p></noscript>
    <div data-faq-items>
        @foreach((session()->hasOldInput('payload') ? old('payload.items', []) : ($payload['items'] ?? [])) as $index => $item)
            @include('admin.content.faq-item')
        @endforeach
    </div>
    <p data-faq-empty class="field-help" hidden>No hay preguntas. Agrega la primera para preparar esta sección.</p>
    <button type="button" class="button secondary" data-faq-add hidden><x-site-icon name="message" /> Agregar pregunta</button>
    <p data-faq-status class="field-help" role="status" aria-live="polite"></p>
    <template data-faq-template>@include('admin.content.faq-item', ['index' => '__INDEX__', 'item' => ['visible' => true]])</template>
</section>
