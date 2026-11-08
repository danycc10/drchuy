@php($isClosed = (bool) data_get($day, 'closed', true))
<div data-hours-day class="hours-day">
    <input type="hidden" name="{{ $prefix }}[closed]" value="0">
    <label class="closed-toggle"><input type="checkbox" data-closed-toggle name="{{ $prefix }}[closed]" value="1" @checked($isClosed)> Cerrado</label>
    <div data-hours-times @if($isClosed) hidden @endif>
        @for($shift = 0; $shift < 2; $shift++)
            <fieldset class="shift-row"><legend>Turno {{ $shift + 1 }}{{ $shift === 1 ? ' (opcional)' : '' }}</legend>
                @foreach(['start' => 'Inicio', 'end' => 'Fin', 'last' => 'Última cita'] as $part => $label)
                    <label>{{ $label }}<input type="time" name="{{ $prefix }}[sessions][{{ $shift }}][{{ $part }}]" value="{{ data_get($day, 'sessions.'.$shift.'.'.$part, '') }}"></label>
                @endforeach
            </fieldset>
        @endfor
    </div>
</div>
