@php($detailLabels = $locale === 'es' ? ['preparation' => 'Preparación previa', 'duration' => 'Duración aproximada', 'bring' => 'Qué llevar'] : ['preparation' => 'Before your visit', 'duration' => 'Approximate duration', 'bring' => 'What to bring'])
@if(collect($detailLabels)->keys()->contains(fn ($field) => filled($details[$field] ?? null)))
    <dl class="service-visit-details">
        @foreach($detailLabels as $field => $label)
            @if(filled($details[$field] ?? null))<div><dt>{{ $label }}</dt><dd>{{ $details[$field] }}</dd></div>@endif
        @endforeach
    </dl>
@endif
