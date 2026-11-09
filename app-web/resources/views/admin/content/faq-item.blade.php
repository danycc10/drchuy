<fieldset class="faq-editor-item" data-faq-item>
    <legend data-faq-number>Pregunta {{ is_numeric($index) ? $index + 1 : '' }}</legend>
    <div class="faq-item-actions" data-faq-actions hidden>
        <button type="button" class="button secondary" data-faq-up>Subir</button>
        <button type="button" class="button secondary" data-faq-down>Bajar</button>
        <button type="button" class="button secondary" data-faq-remove>Quitar</button>
    </div>
    <input type="hidden" name="payload[items][{{ $index }}][visible]" value="0">
    <label class="checkbox-label"><input type="checkbox" name="payload[items][{{ $index }}][visible]" value="1" @checked($item['visible'] ?? false)> Mostrar en el sitio</label>
    <div class="faq-editor-languages">
        @foreach(['es' => 'Español', 'en' => 'Inglés'] as $language => $languageName)
            <div lang="{{ $language }}">
                <h3 class="language-label">{{ $languageName }}</h3>
                <label for="faq-{{ $index }}-{{ $language }}-question">Pregunta · {{ strtoupper($language) }}
                    <input id="faq-{{ $index }}-{{ $language }}-question" name="payload[items][{{ $index }}][{{ $language }}][question]" value="{{ $item[$language]['question'] ?? '' }}" maxlength="200" required>
                </label>
                <label for="faq-{{ $index }}-{{ $language }}-answer">Respuesta · {{ strtoupper($language) }}
                    <textarea id="faq-{{ $index }}-{{ $language }}-answer" name="payload[items][{{ $index }}][{{ $language }}][answer]" rows="5" maxlength="1500" required>{{ $item[$language]['answer'] ?? '' }}</textarea>
                </label>
            </div>
        @endforeach
    </div>
</fieldset>
