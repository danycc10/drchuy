@if($clinic['has_privacy'])<a href="{{ $preview ? route('admin.preview.privacy', $locale) : route('privacy') }}">{{ $copy['privacy_title'] }}</a>@endif
