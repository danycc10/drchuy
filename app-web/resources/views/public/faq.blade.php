@if($clinic['faq'])
    <section class="section container faq-section" id="faq" aria-labelledby="faq-heading">
        <div class="section-heading">
            <div><span class="eyebrow">{{ $locale === 'es' ? 'Preguntas frecuentes' : 'Frequently asked questions' }}</span><h2 id="faq-heading">{{ $copy['faq_title'] }}</h2></div>
            <p>{{ $copy['faq_intro'] }}</p>
        </div>
        <div class="service-items faq-list">
            @foreach($clinic['faq'] as $item)
                <details>
                    <summary><h3>{{ $item[$locale]['question'] }}</h3><x-site-icon name="chevron-down" class="service-chevron" /></summary>
                    <div class="service-description"><p>{{ $item[$locale]['answer'] }}</p></div>
                </details>
            @endforeach
        </div>
        <div class="faq-contact"><p>{{ $locale === 'es' ? '¿Tienes otra duda sobre tu visita?' : 'Have another question about your visit?' }}</p><a class="button" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="message" /> {{ $locale === 'es' ? 'Contactar al consultorio' : 'Contact the office' }}<x-new-tab-hint :copy="$copy" /></a></div>
    </section>
@endif
