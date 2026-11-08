@inject('hourFormatter', 'App\Services\PublicContent')
@foreach($weeklyHours as $schedule)
    <div class="public-hours-row"><p><strong>{{ $schedule['label'] }}</strong><br>
        @if($schedule['closed']){{ $locale === 'es' ? 'Cerrado' : 'Closed' }}
        @else
            {{ implode($locale === 'es' ? ' y ' : ' and ', array_map(fn ($session) => $hourFormatter->formatTime($session['start'], $locale).'–'.$hourFormatter->formatTime($session['end'], $locale), $schedule['sessions'])) }}
        @endif
    </p>
    @if(!$schedule['closed'])<p class="hours-detail">{{ $locale === 'es' ? 'Últimas citas: ' : 'Latest appointments: ' }}{{ implode($locale === 'es' ? ' y ' : ' and ', array_map(fn ($session) => $hourFormatter->formatTime($session['last'], $locale), $schedule['sessions'])) }}.</p>@endif</div>
@endforeach
@if($exceptions)<div class="hours-exceptions"><h4>{{ $locale === 'es' ? 'Fechas especiales' : 'Special dates' }}</h4>@foreach(collect($exceptions)->sortBy('date') as $exception)<p><strong>{{ \Carbon\CarbonImmutable::parse($exception['date'])->locale($locale)->isoFormat('D MMM YYYY') }}</strong><br>@if($exception['closed']){{ $locale === 'es' ? 'Cerrado' : 'Closed' }}@else @foreach($exception['sessions'] as $session){{ $hourFormatter->formatTime($session['start'], $locale) }}–{{ $hourFormatter->formatTime($session['end'], $locale) }} · {{ $locale === 'es' ? 'última cita' : 'last appointment' }} {{ $hourFormatter->formatTime($session['last'], $locale) }}<br>@endforeach @endif @if(filled($exception[$locale.'_note'] ?? null))<br>{{ $exception[$locale.'_note'] }}@endif</p>@endforeach</div>@endif
<small>{{ $copy['local_time'] }} · {{ $copy['special_hours'] }}</small>
