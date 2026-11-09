@props(['locale', 'preview' => false, 'page' => 'home'])
@php($other = $locale === 'es' ? 'en' : 'es')
@if($preview)
    <a class="language-switch" data-language-switch href="{{ route($page === 'privacy' ? 'admin.preview.privacy' : 'admin.preview', $other) }}" lang="{{ $other }}" aria-label="{{ $other === 'en' ? 'Read in English' : 'Leer en español' }}">{{ strtoupper($other) }}</a>
@else
    <form method="post" action="{{ route('language.update') }}" data-language-form>
        @csrf
        <input type="hidden" name="locale" value="{{ $other }}">
        <input type="hidden" name="page" value="{{ $page }}">
        <input type="hidden" name="anchor" value="">
        <button class="language-switch" type="submit" lang="{{ $other }}" aria-label="{{ $other === 'en' ? 'Read in English' : 'Leer en español' }}">{{ strtoupper($other) }}</button>
    </form>
@endif
