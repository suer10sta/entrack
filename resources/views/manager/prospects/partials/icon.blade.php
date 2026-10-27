@php
    $paths = [
        'users' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M16 3a4 4 0 0 1 0 8 M22 21v-2a4 4 0 0 0-3-3.87',
        'new' => 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2 M20 8v6 M17 11h6',
        'phone' => 'M22 16.92v3a2 2 0 0 1-2.18 2A19.79 19.79 0 0 1 3.09 5.18 2 2 0 0 1 5.08 3h3a2 2 0 0 1 2 1.72c.12.96.35 1.9.69 2.79a2 2 0 0 1-.45 2.11L9.05 10.9a16 16 0 0 0 4.05 4.05l1.27-1.27a2 2 0 0 1 2.11-.45c.89.34 1.83.57 2.79.69A2 2 0 0 1 22 16.92Z',
        'send' => 'm22 2-7 20-4-9-9-4 20-7Z M22 2 11 13',
        'clock' => 'M12 8v4l3 2',
        'check' => 'm8 12 3 3 5-6',
        'lost' => 'm9 9 6 6 M15 9l-6 6',
        'search' => 'm21 21-4.35-4.35',
        'filter' => 'M4 7h16 M7 12h10 M10 17h4',
        'reset' => 'M3 10a9 9 0 1 1 2 8 M3 4v6h6',
        'eye' => 'M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z',
        'edit' => 'm16 3 5 5 M4 20l4-1L21 6a2.12 2.12 0 0 0-3-3L5 16l-1 4Z',
        'link' => 'M10 13a5 5 0 0 0 7 .5l3-3a5 5 0 0 0-7-7l-2 2 M14 11a5 5 0 0 0-7-.5l-3 3a5 5 0 0 0 7 7l2-2',
        'mail' => 'M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z m-2 2 10 7L22 6',
        'copy' => 'M9 9h12v12H9z M5 15H3V3h12v2',
        'globe' => 'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z M3 12h18 M12 3c4 5 4 13 0 18-4-5-4-13 0-18Z',
        'building' => 'M4 21V3h12v18 M16 9h4v12 M2 21h20 M8 7h4 M8 11h4 M8 15h4 M9 21v-2h2v2',
        'close' => 'm6 6 12 12 M18 6 6 18',
    ];
@endphp
<svg class="{{ $iconClass ?? 'crm-icon' }}" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
    <path d="{{ $paths[$icon] ?? $paths['users'] }}"/>
    @if(in_array($icon, ['users', 'new']))<circle cx="9" cy="7" r="4"/>@endif
    @if(in_array($icon, ['clock', 'check', 'lost']))<circle cx="12" cy="12" r="9"/>@endif
    @if($icon === 'search')<circle cx="10.5" cy="10.5" r="7.5"/>@endif
    @if($icon === 'eye')<circle cx="12" cy="12" r="3"/>@endif
</svg>
