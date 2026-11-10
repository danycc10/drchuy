@php
    $name = $service[$locale]['name'];
    $message = $locale === 'es'
        ? 'Hola, quisiera solicitar el siguiente servicio: '.$name.'. ¿Me pueden confirmar la disponibilidad y cómo agendar?'
        : 'Hello, I would like to request the following service: '.$name.'. Could you confirm availability and how to book an appointment?';
@endphp
<li class="service-card" data-service-item>
    <details>
        <summary><h4>{{ $name }}</h4><x-site-icon name="chevron-down" class="service-chevron" /></summary>
        <div class="service-description"><p>{{ $service[$locale]['description'] }}</p></div>
    </details>
    <a class="button secondary service-request" data-service-request href="{{ $clinic['whatsapp'].'?text='.rawurlencode($message) }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $copy['ask_service'].': '.$name }}"><x-site-icon name="message" /> {{ $copy['ask_service'] }}</a>
</li>
