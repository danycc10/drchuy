@php
    $name = $service[$locale]['name'];
    $message = $locale === 'es'
        ? 'Hola, quisiera solicitar el siguiente servicio: '.$name.'. ¿Me pueden confirmar la disponibilidad y cómo agendar?'
        : 'Hello, I would like to request the following service: '.$name.'. Could you confirm availability and how to book an appointment?';
@endphp
<li class="service-card" id="service-{{ $service['id'] }}" data-service-item @if($preview && $previewServiceId === $service['id']) data-preview-focus tabindex="-1" @endif>
    @if($preview && $previewServiceId === $service['id'])<p class="service-preview-note">{{ $service['visible'] ? 'Servicio que estás revisando' : 'Servicio oculto: esta tarjeta no aparecerá al publicar.' }}</p>@endif
    <div class="service-card-image">
        @if($service['image'])
            <img src="{{ $service['image']['url'] }}" style="object-position: {{ (int) ($service['image_position']['x'] ?? 50) }}% {{ (int) ($service['image_position']['y'] ?? 50) }}%" alt="" width="{{ $service['image']['width'] }}" height="{{ $service['image']['height'] }}" loading="lazy" decoding="async">
        @else
            <img src="{{ asset('images/services/'.$category->slug.'-640.webp') }}" srcset="{{ asset('images/services/'.$category->slug.'-640.webp') }} 640w, {{ asset('images/services/'.$category->slug.'-960.webp') }} 960w" sizes="(max-width: 600px) calc(100vw - 40px), (max-width: 1000px) calc((100vw - 104px) / 2), 378px" alt="" width="960" height="640" loading="lazy" decoding="async">
        @endif
    </div>
    <h4>{{ $name }}</h4>
    <details @if($preview && $previewServiceId === $service['id']) open @endif>
        <summary aria-label="{{ ($locale === 'es' ? 'Conocer el servicio: ' : 'About this service: ').$name }}"><span>{{ $locale === 'es' ? 'Conocer el servicio' : 'About this service' }}</span><x-site-icon name="chevron-down" class="service-chevron" /></summary>
        <div class="service-description"><p>{{ $service[$locale]['description'] }}</p>
            @include('public.service-visit-details', ['details' => $service[$locale]])
        </div>
    </details>
    <a class="button secondary service-request" data-service-request href="{{ $clinic['whatsapp'].'?text='.rawurlencode($message) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $copy['ask_service'].': '.$name }}"><x-site-icon name="message" /> {{ $copy['ask_service'] }}</a>
</li>
