<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PreviewController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate(['locale' => 'nullable|in:es,en', 'page' => 'nullable|in:home,privacy', 'section' => 'nullable|in:services,physician,office,location,hours,faq']);
        $locale = $data['locale'] ?? 'es';
        $page = $data['page'] ?? 'home';
        $section = $data['section'] ?? '';
        $url = route($page === 'privacy' ? 'admin.preview.privacy' : 'admin.preview', $locale).($page === 'home' && $section ? '#'.$section : '');

        return view('admin.preview', compact('locale', 'page', 'section', 'url'));
    }
}
