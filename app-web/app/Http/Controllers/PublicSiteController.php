<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\PublicContent;
use App\Services\ClinicStructuredData;

class PublicSiteController extends Controller
{
    public function __construct(private PublicContent $content) {}

    public function show(string $locale)
    {
        return $this->render($locale, false);
    }

    public function preview(string $locale)
    {
        return $this->render($locale, true);
    }

    private function render(string $locale, bool $preview)
    {
        abort_unless(in_array($locale, ['es', 'en'], true), 404);
        app()->setLocale($locale);
        ['clinic' => $clinic, 'hours' => $hours] = $this->content->snapshot($preview);
        $relation = $preview ? 'currentRevision' : 'publishedRevision';
        $services = Service::with($relation)->whereNotNull($preview ? 'current_revision_id' : 'published_revision_id')->get()
            ->map(fn ($service) => ['category_id' => $service->service_category_id, 'id' => $service->id, ...$service->{$relation}->payload])
            ->filter(fn ($item) => $item['visible'])->sortBy('position')->groupBy('category_id');
        $response = response()->view('public.home', [
            'locale' => $locale, 'preview' => $preview,
            'copy' => $clinic[$locale], 'clinic' => $clinic,
            'structuredData' => app(ClinicStructuredData::class)->encode($clinic, $hours, $locale),
            'weeklyHours' => $this->content->hoursForDisplay($hours, $locale),
            'exceptions' => array_values(array_filter($hours['exceptions'], fn ($item) => $item['date'] >= now('America/Matamoros')->toDateString())),
            'categories' => ServiceCategory::orderBy('position')->get(), 'services' => $services,
        ]);
        if (! config('clinic.indexable') || $preview) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public function privacy(string $locale, bool $preview = false)
    {
        abort_unless(in_array($locale, ['es', 'en'], true), 404);
        $clinic = $this->content->snapshot($preview)['clinic'];
        abort_unless($clinic['has_privacy'], 404);
        $response = response()->view('public.privacy', ['clinic' => $clinic, 'copy' => $clinic[$locale], 'locale' => $locale, 'preview' => $preview]);
        if (! $clinic['indexable'] || $preview) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public function previewPrivacy(string $locale)
    {
        return $this->privacy($locale, true);
    }

    public function sitemap()
    {
        return response()->view('public.sitemap', ['hasPrivacy' => $this->content->snapshot()['clinic']['has_privacy']])->header('Content-Type', 'application/xml');
    }
}
