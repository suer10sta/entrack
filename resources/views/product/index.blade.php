<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.product_meta_title') }}</title>
    <meta name="description" content="{{ __('messages.product_meta_desc') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('produit') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='15' fill='%230b2638'/%3E%3Cpath d='M18 16h14c11 0 18 6 18 16s-7 16-18 16H18V16Zm12 23c6 0 10-2 10-7s-4-7-10-7h-2v14h2Z' fill='%238ccfd0'/%3E%3C/svg%3E">

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
        "description": "{{ __('messages.product_schema_desc') }}"
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
