<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate(['locale' => 'nullable|in:es,en', 'page' => 'nullable|in:home,privacy', 'section' => 'nullable|in:services,physician,office,location,hours,faq', 'service_id' => 'nullable|integer|exists:services,id']);
        $locale = $data['locale'] ?? 'es';
        $page = $data['page'] ?? 'home';
        $section = $data['section'] ?? '';
        $serviceId = isset($data['service_id']) ? (int) $data['service_id'] : null;
        $url = $page === 'privacy' ? route('admin.preview.privacy', $locale)
            : route('admin.preview', ['locale' => $locale, 'service_id' => $serviceId]).($serviceId ? '#service-'.$serviceId : ($section ? '#'.$section : ''));

        return view('admin.preview', compact('locale', 'page', 'section', 'url', 'serviceId'));
    }
}
