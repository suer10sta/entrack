{{-- Shared head assets for marketing pages and registration only. --}}
<x-favicon />
@if(array_key_exists(request()->route()?->getName() ?? '', config('seo.pages')) || request()->routeIs('blog.show', 'register'))
    @once
        <!-- Google tag (gtag.js) -->
        <meta name="analytics-consent-url" content="{{ url('/analytics/consent') }}">
        <meta name="analytics-csrf-token" content="{{ csrf_token() }}">
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('consent', 'default', {
                analytics_storage: 'denied', ad_storage: 'denied',
                ad_user_data: 'denied', ad_personalization: 'denied', wait_for_update: 500
            });
            gtag('js', new Date());
            gtag('config', 'G-MDJNVZY2WF');
        </script>
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-MDJNVZY2WF"></script>
        <script src="{{ asset('js/analytics-consent.js') }}" defer></script>
    @endonce
@endif
