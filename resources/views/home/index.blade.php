<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <x-favicon />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.Logiciel de gestion de projets, temps et facturation | Dalytrac') }}</title>
    <meta name="description" content="{{ __('messages.Dalytrac centralise projets, suivi du temps et facturation pour aider les entreprises de services à détecter les dérapages et protéger leurs marges.') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('welcome') }}">


    <meta property="og:title" content="{{ __('messages.Logiciel de gestion de projets, temps et facturation | Dalytrac') }}">
    <meta property="og:description" content="{{ __('messages.Dalytrac centralise projets, suivi du temps et facturation pour aider les entreprises de services à détecter les dérapages et protéger leurs marges.') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('welcome') }}">

    <script type="application/ld+json">
      {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Dalytrac",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ route('welcome') }}",
        "description": "All-in-one platform for project management, time tracking and invoicing for service companies."
      }
    </script>

    <!-- Vite Assets (Tailwind CSS) -->
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/dalytrac-home-mobile.css') }}">

    <style>
      [x-cloak] { display: none !important; }
      .faq-summary::-webkit-details-marker { display: none; }
      html, body { overflow-x: clip; }
      @supports not (overflow: clip) {
        html, body { overflow-x: hidden; }
      }
    </style>
  </head>

  <body class="dt-home-page bg-white text-[#1b3444] antialiased">

    <x-public.header :compact-mobile="true" />

    <main id="main">
      @include('home.sections.hero')
      @include('home.sections.pain')
      @include('home.sections.flow')
      @include('home.sections.benefits')
      @include('home.sections.roles')
      @include('home.sections.audience')
      @include('home.sections.demo')
      @include('home.sections.proof')
      @include('home.sections.faq')
      @include('home.sections.cta')
    </main>

    <x-public.footer />

  </body>

</html>
