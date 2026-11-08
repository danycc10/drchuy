<div class="service-search" data-service-search role="search" aria-label="{{ $locale === 'es' ? 'Buscar en los servicios' : 'Search services' }}" hidden>
    <label for="service-query">{{ $locale === 'es' ? '¿Qué servicio buscas?' : 'Which service are you looking for?' }}</label>
    <p id="service-search-help">{{ $locale === 'es' ? 'Busca por nombre de consulta, estudio o procedimiento.' : 'Search for a consultation, test or procedure by name.' }}</p>
    <div class="search-controls">
        <input id="service-query" data-service-query type="search" maxlength="100" autocomplete="off" placeholder="{{ $locale === 'es' ? 'Ej. espirometría' : 'e.g. spirometry' }}" aria-describedby="service-search-help" aria-controls="service-catalog">
        <button type="button" data-clear-service-search>{{ $locale === 'es' ? 'Limpiar' : 'Clear' }}</button>
    </div>
    <p class="search-count" data-service-count data-template="{{ $locale === 'es' ? 'Servicios encontrados: {count} de {total}' : 'Services found: {count} of {total}' }}" role="status" aria-live="polite" aria-atomic="true"></p>
</div>
<div class="search-empty" data-service-empty hidden>
    <h3>{{ $locale === 'es' ? 'No encontramos coincidencias' : 'No matching services' }}</h3>
    <p>{{ $locale === 'es' ? 'Prueba otro nombre o consulta la disponibilidad con el consultorio.' : 'Try another name or contact the office to check availability.' }}</p>
    <div class="search-empty-actions">
        <button class="button secondary" type="button" data-clear-service-search>{{ $locale === 'es' ? 'Ver todos los servicios' : 'Show all services' }}</button>
        <a class="button" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer">{{ $locale === 'es' ? 'Consultar por WhatsApp' : 'Ask on WhatsApp' }} ↗</a>
    </div>
</div>
