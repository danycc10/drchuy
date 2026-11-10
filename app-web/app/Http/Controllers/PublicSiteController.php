<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ServiceCategory;
use App\Services\PublicContent;
use App\Services\ClinicStructuredData;
use Illuminate\Http\Request;

class PublicSiteController extends Controller
{
    public function __construct(private PublicContent $content) {}

    private function selectedLocale(Request $request): string
    {
        $locale = $request->session()->get('public_locale', 'es');

        return in_array($locale, ['es', 'en'], true) ? $locale : 'es';
    }

    public function show(Request $request)
    {
        return $this->render($this->selectedLocale($request), false);
    }

    public function showPrivacy(Request $request)
    {
        return $this->privacy($this->selectedLocale($request));
    }

    public function changeLanguage(Request $request)
    {
        $data = $request->validate([
            'locale' => 'required|string|in:es,en',
            'page' => 'required|string|in:home,privacy',
            'anchor' => 'nullable|string|in:services,physician,office,location,hours,faq',
        ]);
        $request->session()->put('public_locale', $data['locale']);
        $anchor = $data['page'] === 'home' && filled($data['anchor'] ?? null) ? '#'.$data['anchor'] : '';

        return redirect(route($data['page']).$anchor, 303)->withHeaders([
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function legacy(Request $request, string $locale, string $page = 'home')
    {
        abort_unless(in_array($locale, ['es', 'en'], true), 404);
        $request->session()->put('public_locale', $locale);

        return redirect()->route($page)->withHeaders(['Cache-Control' => 'private, no-store']);
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
        $services = Service::with($relation.'.image')->whereNotNull($preview ? 'current_revision_id' : 'published_revision_id')->get()
            ->map(function ($service) use ($relation, $preview) {
                $image = $service->{$relation}->image;
                return ['category_id' => $service->service_category_id, 'id' => $service->id, ...$service->{$relation}->payload,
                    'image' => $image ? ['url' => $image->url($preview), 'width' => $image->display_width, 'height' => $image->display_height] : null];
            })
            ->filter(fn ($item) => $item['visible'])->sortBy('position')->groupBy('category_id');
        $response = response()->view('public.home', [
            'locale' => $locale, 'preview' => $preview,
            'copy' => $clinic[$locale], 'clinic' => $clinic,
            'structuredData' => app(ClinicStructuredData::class)->encode($clinic, $hours, $locale),
            'weeklyHours' => $this->content->hoursForDisplay($hours, $locale),
            'exceptions' => array_values(array_filter($hours['exceptions'], fn ($item) => $item['date'] >= now('America/Matamoros')->toDateString())),
            'categories' => ServiceCategory::orderBy('position')->get(), 'services' => $services,
        ])->header('Content-Language', $locale)->header('Cache-Control', 'private, no-store')->header('Vary', 'Cookie');
        if (! config('clinic.indexable') || $preview) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        }

        return $response;
    }

    public function privacy(string $locale, bool $preview = false)
    {
        abort_unless(in_array($locale, ['es', 'en'], true), 404);
        app()->setLocale($locale);
        $clinic = $this->content->snapshot($preview)['clinic'];
        abort_unless($clinic['has_privacy'], 404);
        $response = response()->view('public.privacy', ['clinic' => $clinic, 'copy' => $clinic[$locale], 'locale' => $locale, 'preview' => $preview])
            ->header('Content-Language', $locale)->header('Cache-Control', 'private, no-store')->header('Vary', 'Cookie');
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
