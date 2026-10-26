<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

  <head>
    <x-public.head />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <x-seo :title="__('messages.public_pricing_meta_title')" :description="__('messages.public_pricing_meta_desc')" />
    <meta name="theme-color" content="#0b2638">

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
