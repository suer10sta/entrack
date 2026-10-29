<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="robots" content="noindex,nofollow"><meta name="referrer" content="no-referrer">
    <title>{{ __('crm-unsubscribe.'.$state.'_title') }} — DalyTrac</title>
    <link rel="stylesheet" href="{{ asset('css/crm-unsubscribe.css') }}">
</head>
<body>
<header><a href="/" aria-label="DalyTrac"><x-application-logo style="font-size:30px" /></a>
    <nav aria-label="{{ __('crm-unsubscribe.language') }}">@foreach(['fr','en','nl','es'] as $locale)<a href="{{ route('crm.unsubscribe', ['locale' => $locale, 'token' => $token]) }}" lang="{{ $locale }}" @if($locale === app()->getLocale()) aria-current="page" @endif>{{ strtoupper($locale) }}</a>@endforeach</nav>
</header>
<main>
    <section class="unsubscribe-card" aria-labelledby="unsubscribe-title">
        <div class="unsubscribe-icon" aria-hidden="true">
            <svg viewBox="0 0 32 32" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                @if($state === 'done')<circle cx="16" cy="16" r="12"/><path d="m10.5 16 3.5 3.5 7.5-7.5"/>
                @elseif($state === 'invalid')<circle cx="16" cy="16" r="12"/><path d="M16 9v8m0 5v.1"/>
                @else<rect x="4" y="7" width="24" height="18" rx="3"/><path d="m5 9 11 8L27 9"/>@endif
            </svg>
        </div>
        <span class="unsubscribe-eyebrow">{{ __('crm-unsubscribe.preferences') }}</span>
        <h1 id="unsubscribe-title">{{ __('crm-unsubscribe.'.$state.'_title') }}</h1>
        <p>{{ __('crm-unsubscribe.'.$state.'_body') }}</p>
        @if($state === 'confirm')
            @if($email ?? null)<div class="unsubscribe-address"><span>{{ __('crm-unsubscribe.email_label') }}</span><strong>{{ $email }}</strong></div>@endif
            <form method="POST" action="{{ route('crm.unsubscribe', ['locale' => app()->getLocale(), 'token' => $token]) }}">@csrf<button type="submit">{{ __('crm-unsubscribe.confirm_button') }}</button></form>
            <a class="unsubscribe-secondary" href="/">{{ __('crm-unsubscribe.keep_receiving') }}</a>
            <div class="unsubscribe-scope">{{ __('crm-unsubscribe.scope') }}</div>
        @else
            <a class="unsubscribe-home" href="/">{{ __('crm-unsubscribe.home') }}</a>
        @endif
    </section>
    <footer>DalyTrac</footer>
</main>
</body></html>
