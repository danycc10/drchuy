@props(['clinic', 'copy', 'locale', 'compact' => false])
<a {{ $attributes->class(['button']) }} href="{{ $clinic['appointment_url'][$locale] }}" data-appointment-link target="_blank" rel="noopener noreferrer" aria-label="{{ $copy['cta'] }} ({{ $copy['new_tab'] }})"><x-site-icon name="message" /> {{ $compact ? $copy['short_cta'] : $copy['cta'] }}</a>
