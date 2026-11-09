@props(['name'])
<svg {{ $attributes->class(['site-icon']) }} width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('message')
            <path d="M21 14a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z" />
            <path d="M7 8h10M7 12h6" />
            @break
        @case('phone')
            <path d="m8 3 2 5-3 2a15 15 0 0 0 7 7l2-3 5 2v3a2 2 0 0 1-2 2A18 18 0 0 1 3 5a2 2 0 0 1 2-2Z" />
            @break
        @case('pin')
            <path d="M19 10c0 5-7 11-7 11S5 15 5 10a7 7 0 1 1 14 0Z" />
            <circle cx="12" cy="10" r="2.5" />
            @break
        @case('clock')
            <circle cx="12" cy="12" r="9" /><path d="M12 7v5l3 2" />
            @break
        @case('grid')
            <rect x="3" y="3" width="7" height="7" rx="1.5" /><rect x="14" y="3" width="7" height="7" rx="1.5" />
            <rect x="3" y="14" width="7" height="7" rx="1.5" /><rect x="14" y="14" width="7" height="7" rx="1.5" />
            @break
        @case('chevron-down')
            <path d="m6 9 6 6 6-6" />
            @break
        @case('menu')
            <path d="M4 6h16M4 12h16M4 18h16" />
            @break
        @case('close')
            <path d="m6 6 12 12M6 18 18 6" />
            @break
        @case('home')
            <path d="m3 10 9-7 9 7M5 9v12h5v-7h4v7h5V9" />
            @break
        @case('search')
            <circle cx="10.5" cy="10.5" r="6.5" /><path d="m16 16 5 5" />
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" /><path d="m3 7 9 6 9-6" />
            @break
    @endswitch
</svg>
