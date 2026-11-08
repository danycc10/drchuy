<section class="panel"><h2>Semana habitual</h2><p class="muted small">Hora local de Piedras Negras. La última cita debe estar dentro de su turno, antes del cierre.</p>
    @foreach(\App\Support\ContentSchema::days() as $dayKey => $dayNames)
        <fieldset class="weekday"><legend>{{ $dayNames['es'] }}</legend>@include('admin.content.day', ['prefix' => 'payload[week]['.$dayKey.']', 'day' => old('payload.week.'.$dayKey, $payload['week'][$dayKey])])</fieldset>
    @endforeach
</section>
<section class="panel"><h2>Fechas especiales</h2><p class="muted small">Estas fechas reemplazan el horario semanal. Máximo 20 excepciones; elimina las anteriores cuando ya no se necesiten.</p>
    <div data-exceptions>@foreach(old('payload.exceptions', $payload['exceptions']) as $index => $day)@include('admin.content.exception', ['index' => $index, 'day' => $day])@endforeach</div>
    <template id="exception-template">@include('admin.content.exception', ['index' => '__INDEX__', 'day' => ['closed' => true, 'sessions' => []]])</template>
    <button type="button" class="button secondary" data-add-exception>Agregar fecha especial</button>
</section>
