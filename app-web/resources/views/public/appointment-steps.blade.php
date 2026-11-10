<div class="appointment-steps">
    <h3>{{ $locale === 'es' ? 'Así solicitas tu cita' : 'How to request your appointment' }}</h3>
    <ol>
        <li><span aria-hidden="true">1</span><p>{{ $locale === 'es' ? 'Abre WhatsApp y envía tu solicitud.' : 'Open WhatsApp and send your request.' }}</p></li>
        <li><span aria-hidden="true">2</span><p>{{ $locale === 'es' ? 'Consulta los horarios disponibles con el consultorio.' : 'Check available times with the office.' }}</p></li>
        <li><span aria-hidden="true">3</span><p>{{ $locale === 'es' ? 'Espera la confirmación de tu fecha y hora.' : 'Wait for confirmation of your date and time.' }}</p></li>
    </ol>
    <p class="appointment-channel">{{ $locale === 'es' ? 'El mensaje está preparado y puedes editarlo antes de enviarlo.' : 'Your message is prepared and you can edit it before sending.' }}</p>
</div>
