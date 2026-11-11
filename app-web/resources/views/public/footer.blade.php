<footer class="site-footer">
    <div class="container">
        <div class="footer-main">
            <div class="footer-identity">
                <a class="footer-brand" href="{{ $preview ? route('admin.preview', $locale) : route('home') }}">{{ $clinic['name'] }}</a>
                <p class="footer-physician">{{ $clinic['physician'] }}</p>
                <p class="footer-location"><x-site-icon name="pin" /> {{ $copy['location'] }}</p>
            </div>
            <nav class="footer-navigation" aria-label="{{ $locale === 'es' ? 'Explorar el sitio' : 'Explore the website' }}">
                <p class="footer-label">{{ $locale === 'es' ? 'EXPLORA' : 'EXPLORE' }}</p>
                <a href="#services">{{ $copy['nav'][0] }}</a>
                <a href="#physician">{{ $copy['nav'][1] }}</a>
                <a href="#hours">{{ $copy['hours_label'] }}</a>
                <a href="#location">{{ $copy['nav'][3] }}</a>
            </nav>
            <div class="footer-contact">
                <p class="footer-label">{{ $locale === 'es' ? 'CITAS Y CONTACTO' : 'APPOINTMENTS & CONTACT' }}</p>
                <a class="footer-phone" href="tel:{{ $clinic['phone'] }}">{{ $clinic['phone_display'] }}</a>
                <p class="footer-contact-note">{{ $locale === 'es' ? 'Atención con cita previa.' : 'Visits by appointment.' }}</p>
                <nav class="footer-social" aria-label="{{ $locale === 'es' ? 'Redes y contacto' : 'Social media and contact' }}">
                    <a class="footer-social-link footer-whatsapp" href="{{ $clinic['appointment_url'][$locale] }}" data-appointment-link target="_blank" rel="noopener noreferrer" aria-label="{{ $locale === 'es' ? 'Solicitar cita por WhatsApp' : 'Request an appointment on WhatsApp' }} ({{ $copy['new_tab'] }})"><x-site-icon name="whatsapp" /> WhatsApp</a>
                    <a class="footer-social-link footer-facebook" href="{{ $clinic['facebook'] }}" target="_blank" rel="noopener noreferrer" aria-label="{{ $locale === 'es' ? 'Visitar nuestra página de Facebook' : 'Visit our Facebook page' }} ({{ $copy['new_tab'] }})"><x-site-icon name="facebook" /> Facebook</a>
                </nav>
            </div>
        </div>
        <div class="footer-bottom"><span>© {{ now()->year }} {{ $clinic['name'] }}</span><div class="footer-links">@include('public.privacylink')</div></div>
        <p class="footer-disclaimer">{{ $copy['disclaimer'] }}</p>
    </div>
</footer>
