<div class="service-search" data-service-search role="search" aria-label="{{ $locale === 'es' ? 'Buscar en los servicios' : 'Search services' }}" hidden>
    <label for="service-query">{{ $locale === 'es' ? '¿Qué servicio buscas?' : 'Which service are you looking for?' }}</label>
    <p id="service-search-help">{{ $locale === 'es' ? 'Busca por nombre de consulta, estudio o procedimiento.' : 'Search for a consultation, test or procedure by name.' }}</p>
    <div class="search-controls">
        <div class="search-input-wrap"><x-site-icon name="search" /><input id="service-query" data-service-query type="search" maxlength="100" autocomplete="off" placeholder="{{ $locale === 'es' ? 'Ej. espirometría' : 'e.g. spirometry' }}" aria-describedby="service-search-help" aria-controls="service-catalog"></div>
        <button type="button" data-clear-service-search>{{ $locale === 'es' ? 'Limpiar filtros' : 'Clear filters' }}</button>
    </div>
    <div class="service-filters" role="group" aria-label="{{ $locale === 'es' ? 'Filtrar por categoría' : 'Filter by category' }}">
        <button type="button" data-service-filter="all" aria-pressed="true" aria-controls="service-catalog">{{ $locale === 'es' ? 'Todos' : 'All' }} <span>{{ $services->sum(fn ($items) => $items->count()) }}</span></button>
        @foreach($categories as $category)
            @if($services->has($category->id))<button type="button" data-service-filter="{{ $category->slug }}" aria-pressed="false" aria-controls="service-catalog">{{ $category->name[$locale] }} <span>{{ $services[$category->id]->count() }}</span></button>@endif
        @endforeach
    </div>
    <p class="search-count" data-service-count data-template="{{ $locale === 'es' ? 'Servicios encontrados: {count} de {total}' : 'Services found: {count} of {total}' }}" role="status" aria-live="polite" aria-atomic="true"></p>
</div>
<div class="search-empty" data-service-empty hidden>
    <h3>{{ $locale === 'es' ? 'No encontramos coincidencias' : 'No matching services' }}</h3>
    <p>{{ $locale === 'es' ? 'Prueba otro nombre o consulta la disponibilidad con el consultorio.' : 'Try another name or contact the office to check availability.' }}</p>
    <div class="search-empty-actions">
        <button class="button secondary" type="button" data-clear-service-search>{{ $locale === 'es' ? 'Ver todos los servicios' : 'Show all services' }}</button>
        <a class="button" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="message" /> {{ $locale === 'es' ? 'Consultar por WhatsApp' : 'Ask on WhatsApp' }}</a>
    </div>
</div>
