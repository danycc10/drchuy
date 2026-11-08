<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;

class PublicSiteController extends Controller
{
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
        $relation = $preview ? 'currentRevision' : 'publishedRevision';
        $services = Service::with($relation)->whereNotNull($preview ? 'current_revision_id' : 'published_revision_id')->get()
            ->map(fn ($service) => ['category_id' => $service->service_category_id, 'id' => $service->id, ...$service->{$relation}->payload])
            ->filter(fn ($item) => $item['visible'])->sortBy('position')->groupBy('category_id');
        $response = response()->view('public.home', [
            'locale' => $locale, 'preview' => $preview,
            'copy' => config('clinic.'.$locale), 'clinic' => config('clinic'),
            'categories' => ServiceCategory::orderBy('position')->get(), 'services' => $services,
        ]);
        if (! config('clinic.indexable') || $preview) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }
}
