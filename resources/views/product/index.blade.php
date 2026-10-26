<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <x-public.head />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo :title="__('messages.product_meta_title')" :description="__('messages.product_meta_desc')" />
    <meta name="theme-color" content="#0b2638">

    <x-json-ld :data="['@context' => 'https://schema.org', '@type' => 'SoftwareApplication', '@id' => config('seo.url') . '/#software', 'name' => 'DalyTrac', 'applicationCategory' => 'BusinessApplication', 'operatingSystem' => 'Web', 'url' => \App\Support\Seo::url('welcome'), 'description' => __('messages.product_schema_desc')]" />

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
