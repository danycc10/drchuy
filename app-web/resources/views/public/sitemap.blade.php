{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@if(config('clinic.indexable'))
@foreach(['es', 'en'] as $locale)
<url><loc>{{ rtrim(config('app.url'), '/') }}/{{ $locale }}</loc></url>
@endforeach
@endif
</urlset>
