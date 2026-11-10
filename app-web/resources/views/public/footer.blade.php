<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-identity">
                <a class="footer-brand" href="{{ $preview ? route('admin.preview', $locale) : route('home') }}"><img src="{{ asset('images/logo.png') }}" alt="{{ $clinic['name'] }}" width="1400" height="445" loading="lazy"></a>
                <p class="footer-physician">{{ $clinic['physician'] }}</p>
                <p>{{ $copy['location'] }}</p>
            </div>
            <div class="footer-contact">
                <p class="footer-contact-title">{{ $locale === 'es' ? 'Sigamos en contacto' : 'Stay in touch' }}</p>
                <nav class="footer-social" aria-label="{{ $locale === 'es' ? 'Redes y contacto' : 'Social media and contact' }}">
                    <a class="footer-social-link footer-whatsapp" href="{{ $clinic['appointment_url'][$locale] }}" data-appointment-link target="_blank" rel="noopener noreferrer" aria-label="{{ $locale === 'es' ? 'Solicitar cita por WhatsApp' : 'Request an appointment on WhatsApp' }}"><span class="footer-social-icon"><x-site-icon name="whatsapp" /></span><span>WhatsApp<small>{{ $locale === 'es' ? 'Solicitar cita' : 'Request an appointment' }}</small></span></a>
                    <a class="footer-social-link footer-facebook" href="{{ $clinic['facebook'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $locale === 'es' ? 'Visitar nuestra página de Facebook' : 'Visit our Facebook page' }}"><span class="footer-social-icon"><x-site-icon name="facebook" /></span><span>Facebook<small>{{ $locale === 'es' ? 'Visita nuestra página' : 'Visit our page' }}</small></span></a>
                </nav>
            </div>
        </div>
        <div class="footer-bottom"><span>© {{ now()->year }} {{ $clinic['name'] }}</span><div class="footer-links">@include('public.privacylink')</div></div>
        <p class="footer-disclaimer">{{ $copy['disclaimer'] }}</p>
    </div>
</footer>
