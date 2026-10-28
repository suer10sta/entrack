<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <head>
    <x-public.head />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <x-seo :title="__('messages.Watch Demo') . ' | DalyTrac'" :description="__('messages.Watch DalyTrac demo videos to see how our platform handles dashboards, invoices, projects, time tracking, expenses, clients and more.')" />
    <meta name="theme-color" content="#0b2638">

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

    @php
      $demoVideos = collect(config('demo.youtube_videos', []))->filter(fn ($video) => preg_match('/^[a-zA-Z0-9_-]{11}$/', $video['id'] ?? ''))->values();
    @endphp
    <main id="main">
      {{-- Hero Section --}}
      <section class="bg-[#f8faf9] pb-12 pt-16 max-md:pt-12">
        <div class="mx-auto max-w-[1200px] px-6 text-center max-md:px-4">
          <p class="mb-5 flex items-center justify-center gap-2.5 text-[0.75rem] font-bold uppercase tracking-[0.13em] text-brand-secondary"><span class="h-0.5 w-7 bg-brand-accent"></span>{{ __('messages.Feature Demos') }}</p>
          <h1 class="mx-auto max-w-3xl text-[clamp(2.5rem,4.5vw,4rem)] font-bold leading-[1.05] tracking-[-0.052em] text-brand-primary">
            {{ __('messages.See DalyTrac in') }} <em class="not-italic text-brand-secondary">{{ __('messages.Action') }}</em>
          </h1>
          <p class="mx-auto mt-5 max-w-3xl text-[1.05rem] leading-[1.7] text-[#5b6b73]">
            {{ __('messages.Click on any feature to watch a detailed demo video and discover how DalyTrac can transform your business operations.') }}
          </p>
        </div>
      </section>

      @if($demoVideos->isEmpty())
      <section class="bg-[#f8faf9] px-6 pb-20 text-center">
        <p>{{ __('messages.demo_videos_unavailable') }}</p>
        <a href="{{ route('contact') }}" class="font-semibold text-brand-secondary">{{ __('messages.Contact Us') }}</a>
      </section>
      @else
      <section class="bg-[#f8faf9] pb-20 pt-4 max-md:pb-14" data-demo-playlist>
        <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
          <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">
            <div class="lg:col-span-2">
              <div class="overflow-hidden rounded-2xl border border-[#dce6e4] bg-white shadow-[0_18px_50px_rgba(7,27,50,0.10)]">
                <div class="relative aspect-video w-full bg-brand-primary" data-demo-player>
                  <button type="button" data-demo-play class="relative block h-full w-full" aria-label="{{ __('messages.demo_youtube_play') }}">
                    <img src="https://i.ytimg.com/vi/{{ $demoVideos->first()['id'] }}/hqdefault.jpg" alt="" class="h-full w-full object-cover" width="480" height="360" fetchpriority="high">
                    <span class="absolute inset-0 flex items-center justify-center bg-brand-primary/30"><span class="grid h-[72px] w-[72px] place-items-center rounded-full bg-brand-secondary text-white shadow-lg"><i data-lucide="play" class="h-7 w-7 fill-current" aria-hidden="true"></i></span></span>
                  </button>
                </div>
                <div class="p-4">
                  <h2 data-demo-title class="mb-3 text-xl font-bold text-brand-primary" aria-live="polite">{{ $demoVideos->first()['title'] }}</h2>
                  <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex gap-2">
                      <button type="button" data-demo-previous disabled class="inline-flex items-center rounded-lg bg-brand-secondary/10 px-4 py-2 text-sm font-semibold text-brand-secondary hover:bg-brand-secondary/20 disabled:cursor-not-allowed disabled:opacity-50"><i data-lucide="chevron-left" class="mr-1 h-4 w-4" aria-hidden="true"></i>{{ __('messages.Previous') }}</button>
                      <button type="button" data-demo-next @disabled($demoVideos->count() < 2) class="inline-flex items-center rounded-lg bg-brand-secondary/10 px-4 py-2 text-sm font-semibold text-brand-secondary hover:bg-brand-secondary/20 disabled:cursor-not-allowed disabled:opacity-50">{{ __('messages.Next') }}<i data-lucide="chevron-right" class="ml-1 h-4 w-4" aria-hidden="true"></i></button>
                    </div>
                    <span class="text-xs font-medium text-[#5b6b73]"><span data-demo-current>1</span> / {{ $demoVideos->count() }}</span>
                  </div>
                  <a data-demo-youtube href="https://www.youtube.com/watch?v={{ $demoVideos->first()['id'] }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-brand-secondary">{{ __('messages.demo_youtube_watch') }}<i data-lucide="external-link" class="h-4 w-4" aria-hidden="true"></i></a>
                </div>
              </div>
            </div>
            <div class="overflow-hidden rounded-2xl border border-[#dce6e4] bg-white shadow-[0_18px_50px_rgba(7,27,50,0.10)]">
              <div class="border-b border-[#dce6e4] bg-[#f2f8f7] p-4">
                <h3 class="text-lg font-bold text-brand-primary">{{ __('messages.Feature Demos') }}</h3>
                <p class="mt-1 text-xs text-[#5b6b73]">{{ __('messages.Select a feature to watch') }}</p>
              </div>
              <div class="playlist-scroll max-h-[520px] overflow-y-auto">
                @foreach($demoVideos as $index => $video)
                  <button type="button" class="playlist-item flex w-full gap-3 border-b border-[#dce6e4] p-3 text-left hover:bg-[#f2f8f7] {{ $index === 0 ? 'active' : '' }}" data-demo-video data-video-id="{{ $video['id'] }}" data-video-title="{{ $video['title'] }}" @if($index === 0) aria-current="true" @endif>
                    <span class="relative h-16 w-24 flex-none overflow-hidden rounded-lg bg-brand-primary">
                      <img src="https://i.ytimg.com/vi/{{ $video['id'] }}/mqdefault.jpg" alt="" class="h-full w-full object-cover" width="320" height="180" loading="lazy">
                      <span class="absolute bottom-1 right-1 rounded bg-brand-primary px-1 text-xs text-white">{{ $video['duration'] }}</span>
                    </span>
                    <span class="min-w-0 self-center text-sm font-semibold leading-snug text-brand-primary">{{ $video['title'] }}</span>
                  </button>
                @endforeach
              </div>
              <a href="{{ config('demo.channel_url') }}" target="_blank" rel="noopener noreferrer" class="block p-4 text-sm font-semibold text-brand-secondary">{{ __('messages.demo_youtube_channel') }} →</a>
            </div>
          </div>
          <noscript><p class="mt-4 text-sm text-[#5b6b73]">{{ __('messages.demo_youtube_nojs') }}</p><ul>@foreach($demoVideos as $video)<li><a href="https://www.youtube.com/watch?v={{ $video['id'] }}" class="text-brand-secondary">{{ $video['title'] }}</a></li>@endforeach</ul></noscript>
        </div>
      </section>
      @endif

      {{-- CTA --}}
      <section class="bg-brand-primary py-16 max-md:py-12">
        <div class="mx-auto flex max-w-[1200px] flex-col items-center px-6 text-center max-md:px-4">
          <h2 class="max-w-2xl text-[clamp(1.8rem,3vw,2.6rem)] font-bold leading-tight tracking-[-0.03em] text-white">{{ __('messages.public_demo_cta_title') }}</h2>
          <p class="mt-3 max-w-xl text-[0.95rem] text-white/80">{{ __('messages.public_demo_cta_text') }}</p>
          <a href="{{ route('register') }}" class="group mt-7 inline-flex min-h-[52px] items-center justify-center gap-2.5 rounded-full border border-transparent btn-sweep btn-sweep-accent bg-brand-secondary px-8 text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.25)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:text-brand-primary hover:shadow-[0_16px_34px_rgba(45,112,128,0.35)] cursor-pointer max-sm:w-full">{{ __('messages.public_demo_cta_button') }} <span aria-hidden="true" class="inline-block transition-transform duration-300 group-hover:translate-x-0.5 group-hover:-translate-y-0.5"><i data-lucide="arrow-up-right" class="h-4 w-4"></i></span></a>
        </div>
      </section>
    </main>

    <x-public.footer />

    <style>
      .playlist-item {
        transition: all 0.2s ease;
      }

      .playlist-item.active {
        background-color: rgba(45, 112, 128, 0.08);
        border-left: 3px solid #2D7080;
      }

      .playlist-item img {
        transition: transform 0.3s ease;
      }

      .playlist-item:hover img {
        transform: scale(1.05);
      }

      .playlist-item:focus-visible { outline: 2px solid #2D7080; outline-offset: -3px; }
      /* Custom scrollbar for playlist */
      .playlist-scroll::-webkit-scrollbar {
        width: 6px;
      }

      .playlist-scroll::-webkit-scrollbar-track {
        background: transparent;
      }

      .playlist-scroll::-webkit-scrollbar-thumb {
        background: rgba(45, 112, 128, 0.3);
        border-radius: 3px;
      }

      .playlist-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(45, 112, 128, 0.5);
      }
    </style>

    <script src="{{ asset('js/demo-videos.js') }}" defer></script>
  </body>
</html>
