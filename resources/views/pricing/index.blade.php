<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.public_pricing_meta_title') }}</title>
    <meta name="description" content="{{ __('messages.public_pricing_meta_desc') }}">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('tarifs') }}">
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='15' fill='%230b2638'/%3E%3Cpath d='M18 16h14c11 0 18 6 18 16s-7 16-18 16H18V16Zm12 23c6 0 10-2 10-7s-4-7-10-7h-2v14h2Z' fill='%238ccfd0'/%3E%3C/svg%3E">

    <meta property="og:title" content="{{ __('messages.public_pricing_og_title') }}">
    <meta property="og:description" content="{{ __('messages.public_pricing_og_desc') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('tarifs') }}">

    <!-- Vite Assets (Tailwind CSS) -->
    @vite('resources/css/app.css')

    <style>
      [x-cloak] { display: none !important; }
      .faq-summary::-webkit-details-marker { display: none; }
      html, body { overflow-x: clip; }
      @supports not (overflow: clip) {
        html, body { overflow-x: hidden; }
      }
    </style>
  </head>

  <body class="bg-white text-[#1b3444] antialiased">

    <x-public.header />

    {{-- Shared Alpine state: team-size finder <-> price matrix highlight.
         plan: null | 'solo' | 'premium' | 'pro' --}}
    <main id="main"
      data-default-message="{{ __('messages.public_pricing_default_message') }}"
      data-message-solo="{{ __('messages.public_pricing_message_solo') }}"
      data-message-premium="{{ __('messages.public_pricing_message_premium') }}"
      data-message-pro="{{ __('messages.public_pricing_message_pro') }}"
      x-data="{ plan: null, messages: {}, defaultMessage: '' }"
      x-init="messages = { solo: $el.dataset.messageSolo, premium: $el.dataset.messagePremium, pro: $el.dataset.messagePro }; defaultMessage = $el.dataset.defaultMessage">
      @include('pricing.sections.top')
      @include('pricing.sections.prices')
      @include('pricing.sections.trial')
      @include('pricing.sections.next')
      @include('pricing.sections.faq')
    </main>

    <x-public.footer />

  </body>

</html>
