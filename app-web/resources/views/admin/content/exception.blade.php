<fieldset class="exception-row" data-exception>
    <legend>Horario especial</legend><button type="button" class="small-link danger exception-remove" data-remove-exception><x-site-icon name="close" /> Quitar fecha</button>
    <label>Fecha<input type="date" name="payload[exceptions][{{ $index }}][date]" value="{{ $day['date'] ?? '' }}" required></label>
    @include('admin.content.day', ['prefix' => 'payload[exceptions]['.$index.']', 'day' => $day])
    <div class="exception-notes"><label>Nota en español (opcional)<input name="payload[exceptions][{{ $index }}][es_note]" value="{{ $day['es_note'] ?? '' }}" maxlength="200"></label><label>Nota en inglés (opcional)<input name="payload[exceptions][{{ $index }}][en_note]" value="{{ $day['en_note'] ?? '' }}" maxlength="200"></label></div>
</fieldset>
