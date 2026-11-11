@inject('hourFormatter', 'App\Services\PublicContent')
@isset($openingStatus)
<p class="hours-status {{ $openingStatus['open'] ? 'is-open' : 'is-closed' }}" data-hours-status><span class="status-dot" aria-hidden="true"></span><strong>{{ $openingStatus['label'] }}</strong>@if($openingStatus['detail']) <span>{{ $openingStatus['detail'] }}</span>@endif</p>
<p class="hours-today"><strong>{{ $locale === 'es' ? 'Hoy' : 'Today' }}</strong>, {{ now('America/Matamoros')->locale($locale)->isoFormat($locale === 'es' ? 'dddd D [de] MMMM' : 'dddd, MMMM D') }}: {{ $openingStatus['closed_today'] ? ($locale === 'es' ? 'cerrado' : 'closed') : $openingStatus['today_hours'] }}@if($openingStatus['exception']) · {{ $locale === 'es' ? 'horario especial' : 'special hours' }}@endif</p>
@endisset
@foreach($weeklyHours as $schedule)
    @php($isToday = isset($openingStatus) && ! $openingStatus['exception'] && in_array($openingStatus['day'], $schedule['keys'], true))
    <div class="public-hours-row{{ $isToday ? ' is-today' : '' }}" @if($isToday) aria-current="date" @endif><p><strong>{{ $schedule['label'] }}</strong>@if($isToday) <span class="today-badge">{{ $locale === 'es' ? 'Hoy' : 'Today' }}</span>@endif<br>
        @if($schedule['closed']){{ $locale === 'es' ? 'Cerrado' : 'Closed' }}
        @else
            {{ implode($locale === 'es' ? ' y ' : ' and ', array_map(fn ($session) => $hourFormatter->formatTime($session['start'], $locale).'–'.$hourFormatter->formatTime($session['end'], $locale), $schedule['sessions'])) }}
        @endif
    </p>
    @if(!$schedule['closed'])<p class="hours-detail">{{ $locale === 'es' ? 'Últimas citas: ' : 'Latest appointments: ' }}{{ implode($locale === 'es' ? ' y ' : ' and ', array_map(fn ($session) => $hourFormatter->formatTime($session['last'], $locale), $schedule['sessions'])) }}.</p>@endif</div>
@endforeach
@if($exceptions)<div class="hours-exceptions"><h4>{{ $locale === 'es' ? 'Fechas especiales' : 'Special dates' }}</h4>@foreach(collect($exceptions)->sortBy('date') as $exception)@php($isToday = isset($openingStatus) && $exception['date'] === $openingStatus['date'])<p class="{{ $isToday ? 'is-today' : '' }}" @if($isToday) aria-current="date" @endif><strong>{{ \Carbon\CarbonImmutable::parse($exception['date'])->locale($locale)->isoFormat('D MMM YYYY') }}</strong>@if($isToday) <span class="today-badge">{{ $locale === 'es' ? 'Hoy' : 'Today' }}</span>@endif<br>@if($exception['closed']){{ $locale === 'es' ? 'Cerrado' : 'Closed' }}@else @foreach($exception['sessions'] as $session){{ $hourFormatter->formatTime($session['start'], $locale) }}–{{ $hourFormatter->formatTime($session['end'], $locale) }} · {{ $locale === 'es' ? 'última cita' : 'last appointment' }} {{ $hourFormatter->formatTime($session['last'], $locale) }}<br>@endforeach @endif @if(filled($exception[$locale.'_note'] ?? null))<br>{{ $exception[$locale.'_note'] }}@endif</p>@endforeach</div>@endif
<small>{{ $copy['local_time'] }} · {{ $copy['special_hours'] }}</small>
