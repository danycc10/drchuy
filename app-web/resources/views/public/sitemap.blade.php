{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@if(config('clinic.indexable'))
<url><loc>{{ rtrim(config('app.url'), '/') }}/</loc></url>
@if($hasPrivacy)<url><loc>{{ rtrim(config('app.url'), '/') }}{{ route('privacy', [], false) }}</loc></url>@endif
@endif
</urlset>
