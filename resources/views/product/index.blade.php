<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <x-favicon />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.product_meta_title') }}</title>
    <meta name="description" content="{{ __('messages.product_meta_desc') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('produit') }}">


    <meta property="og:title" content="{{ __('messages.product_og_title') }}">
    <meta property="og:description" content="{{ __('messages.product_og_desc') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('produit') }}">

    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "DalyTrac",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ route('produit') }}",
        "description": @js(__('messages.product_schema_desc'))
      }
    </script>

    <!-- Vite Assets (Tailwind CSS) -->
    @vite('resources/css/app.css')

    <style>
      [x-cloak] { display: none !important; }
      html, body { overflow-x: clip; }
      @supports not (overflow: clip) {
        html, body { overflow-x: hidden; }
      }
    </style>
  </head>

  <body class="bg-white font-normal text-[#1b3444] antialiased">

    <x-public.header />

    <main id="main">
      @include('product.sections.hero')
      @include('product.sections.modules')
      @include('product.sections.access')
      @include('product.sections.onboarding')
      @include('product.sections.demo')
      @include('product.sections.cta')
    </main>

    <x-public.footer />

  </body>

</html>
