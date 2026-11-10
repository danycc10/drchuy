@props(['name'])
<svg {{ $attributes->class(['site-icon']) }} width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
    @switch($name)
        @case('facebook')
            <path fill="currentColor" stroke="none" d="M14.2 22v-9.1h3.1l.5-3.6h-3.6V7c0-1 .3-1.8 1.8-1.8H18V2.1c-.3 0-1.5-.1-2.9-.1-2.9 0-4.9 1.8-4.9 5.1v2.2H7v3.6h3.2V22Z" />
            @break
        @case('whatsapp')
            <path d="M21 11.7a9 9 0 0 1-13.3 7.9L3 21l1.4-4.5A9 9 0 1 1 21 11.7Z" /><path fill="currentColor" stroke="none" d="M8 6.8c-.3 0-.6.1-.8.4-.3.3-1 1-1 2.3s1 2.5 1.1 2.7c.2.2 1.9 2.9 4.6 4 .7.3 1.2.4 1.6.5.7.2 1.3.2 1.8.1.5-.1 1.6-.7 1.8-1.3.2-.6.2-1.1.1-1.2-.1-.1-.3-.2-.6-.3l-1.9-.9c-.2-.1-.4-.1-.5.1l-.9 1.1c-.2.2-.3.2-.6.1-.2-.1-1-.4-1.9-1.2-.7-.6-1.2-1.3-1.4-1.6-.1-.2 0-.4.1-.5l.4-.5.3-.4c.1-.2 0-.4 0-.5l-.9-2.1c-.2-.5-.4-.5-.6-.5Z" />
            @break
        @case('copy')
            <rect x="8" y="8" width="12" height="13" rx="2" /><path d="M16 8V5a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h3" />
            @break
        @case('print')
            <path d="M7 8V3h10v5M7 17H4a1 1 0 0 1-1-1v-6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v6a1 1 0 0 1-1 1h-3" /><path d="M7 14h10v7H7zM17 11h.01" />
            @break
        @case('image')
            <rect x="3" y="3" width="18" height="18" rx="3" /><circle cx="8" cy="8" r="1.5" /><path d="m3 17 5-5 4 4 4-6 5 7" />
            @break
        @case('list')
            <rect x="3" y="3" width="18" height="18" rx="3" /><path d="M9 8h7M9 12h7M9 16h7M6.5 8h.01M6.5 12h.01M6.5 16h.01" />
            @break
        @case('document')
            <path d="M14 3H6a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9Z" /><path d="M14 3v6h6M8 13h8M8 17h5" />
            @break
        @case('history')
            <path d="M3 11a9 9 0 1 1 2.6 7.4M3 5v6h6M12 7v5l3 2" />
            @break
        @case('eye')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z" /><circle cx="12" cy="12" r="3" />
            @break
        @case('edit')
            <path d="m15 4 5 5M4 20l5-1L21 7a2 2 0 0 0-5-5L4 14Z" />
            @break
        @case('save')
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h12l4 4v12a2 2 0 0 1-2 2ZM7 3v6h9V3M7 21v-7h10v7" />
            @break
        @case('publish')
            <circle cx="12" cy="12" r="9" /><ellipse cx="12" cy="12" rx="4" ry="9" /><path d="M3 12h18" />
            @break
        @case('lock')
            <rect x="4" y="10" width="16" height="11" rx="2" /><path d="M8 10V7a4 4 0 0 1 8 0v3M12 14v3" />
            @break
        @case('power')
            <path d="M12 3v9M6 5.5a9 9 0 1 0 12 0" />
            @break
        @case('user')
            <circle cx="12" cy="7" r="4" /><path d="M4 21v-2a8 8 0 0 1 16 0v2" />
            @break
        @case('building')
            <rect x="5" y="3" width="14" height="18" rx="2" /><path d="M9 21v-5h6v5M9 7h1M14 7h1M9 11h1M14 11h1" />
            @break
        @case('check')
            <path d="m5 12 4 4L19 6" />
            @break
        @case('calendar')
            <rect x="3" y="5" width="18" height="16" rx="2" /><path d="M7 3v4M17 3v4M3 11h18M7 15h2M13 15h2M7 18h2" />
            @break
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
