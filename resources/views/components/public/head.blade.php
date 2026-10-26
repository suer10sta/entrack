{{-- Shared head assets for marketing pages and registration only. --}}
<x-favicon />
@if(array_key_exists(request()->route()?->getName() ?? '', config('seo.pages')) || request()->routeIs('blog.show', 'register'))
    @once
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-MDJNVZY2WF"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-MDJNVZY2WF');
        </script>
    @endonce
@endif
