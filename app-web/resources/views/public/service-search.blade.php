<div class="service-search" data-service-search role="search" aria-label="{{ $locale === 'es' ? 'Buscar en los servicios' : 'Search services' }}" hidden>
    <label for="service-query">{{ $locale === 'es' ? '¿Qué servicio buscas?' : 'Which service are you looking for?' }}</label>
    <p id="service-search-help">{{ $locale === 'es' ? 'Selecciona una categoría y busca un servicio dentro de ella.' : 'Select a category and search for a service within it.' }}</p>
    <div class="search-controls">
        <div class="search-input-wrap"><x-site-icon name="search" /><input id="service-query" data-service-query type="search" maxlength="100" autocomplete="off" placeholder="{{ $locale === 'es' ? 'Ej. espirometría' : 'e.g. spirometry' }}" aria-describedby="service-search-help" aria-controls="service-catalog"></div>
        <button type="button" data-clear-service-search>{{ $locale === 'es' ? 'Limpiar búsqueda' : 'Clear search' }}</button>
    </div>
    <div class="mobile-service-category"><label for="service-category">{{ $locale === 'es' ? 'Categoría de atención' : 'Service category' }}</label><select id="service-category" data-service-category-select aria-controls="service-catalog">@foreach($categories->filter(fn ($category) => $services->has($category->id)) as $category)<option value="{{ $category->slug }}">{{ $category->name[$locale] }} ({{ $services[$category->id]->count() }})</option>@endforeach</select></div>
    <div class="service-filters" role="group" aria-label="{{ $locale === 'es' ? 'Filtrar por categoría' : 'Filter by category' }}">
        @foreach($categories->filter(fn ($category) => $services->has($category->id)) as $category)
            <button type="button" data-service-filter="{{ $category->slug }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}" aria-controls="service-catalog">{{ $category->name[$locale] }} <span>{{ $services[$category->id]->count() }}</span></button>
        @endforeach
    </div>
    <p class="search-count" data-service-count data-template="{{ $locale === 'es' ? 'Servicios encontrados: {count} de {total}' : 'Services found: {count} of {total}' }}" role="status" aria-live="polite" aria-atomic="true"></p>
</div>
<div class="search-empty" data-service-empty hidden>
    <h3>{{ $locale === 'es' ? 'No encontramos coincidencias' : 'No matching services' }}</h3>
    <p>{{ $locale === 'es' ? 'Prueba otro nombre o selecciona otra categoría.' : 'Try another name or select another category.' }}</p>
    <div class="search-empty-actions">
        <button class="button secondary" type="button" data-clear-service-search>{{ $locale === 'es' ? 'Ver servicios de esta categoría' : 'Show services in this category' }}</button>
        <a class="button" href="{{ $clinic['whatsapp'] }}" target="_blank" rel="noopener noreferrer"><x-site-icon name="message" /> {{ $locale === 'es' ? 'Consultar por WhatsApp' : 'Ask on WhatsApp' }}</a>
    </div>
</div>
