<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <head>
    <x-favicon />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('messages.Watch Demo') }} | DalyTrac</title>
    <meta name="description" content="Watch DalyTrac demo videos to see how our platform handles dashboards, invoices, projects, time tracking, expenses, clients and more.">
    <meta name="robots" content="index,follow">
    <meta name="theme-color" content="#0b2638">
    <link rel="canonical" href="{{ route('demo') }}">


    <meta property="og:title" content="{{ __('messages.Watch Demo') }} | DalyTrac">
    <meta property="og:description" content="Explore DalyTrac feature demos and discover how to streamline your business operations end‑to‑end.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('demo') }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('messages.Watch Demo') }} | DalyTrac">
    <meta name="twitter:description" content="See DalyTrac in action across invoicing, projects, time tracking, calendar and more.">

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

      {{-- Video + Playlist --}}
      <section class="bg-[#f8faf9] pb-20 pt-4 max-md:pb-14">
        <div class="mx-auto max-w-[1200px] px-6 max-md:px-4">
          <div class="mx-auto grid max-w-7xl grid-cols-1 gap-6 lg:grid-cols-3 lg:gap-8">

            {{-- Left Side: Video Player (2/3 width on large screens) --}}
            <div class="lg:col-span-2">
              <div id="video-player-container" class="overflow-hidden rounded-2xl border border-[#dce6e4] bg-white shadow-[0_18px_50px_rgba(7,27,50,0.10)]">
                <div class="aspect-video w-full bg-brand-primary">
                  <video
                    id="demo-video-player"
                    controls
                    autoplay
                    class="h-full w-full"
                  >
                    <source src="{{ asset('videos/DashboardDemo.mp4') }}" type="video/mp4">
                    {{ __('messages.Your browser does not support the video tag.') }}
                  </video>
                </div>
                <div class="bg-white p-4">
                  <h2 id="current-video-title" class="mb-2 text-xl font-bold text-brand-primary">{{ __('messages.Dashboard Overview') }}</h2>
                  <div class="flex flex-wrap items-center justify-between gap-3">
                    <div class="flex gap-2">
                      <button
                        onclick="playPreviousVideo()"
                        id="prev-btn"
                        class="inline-flex items-center rounded-lg bg-brand-secondary/10 px-4 py-2 text-sm font-semibold text-brand-secondary transition-colors duration-300 hover:bg-brand-secondary/20 disabled:cursor-not-allowed disabled:opacity-50"
                        disabled
                      >
                        <i data-lucide="chevron-left" class="mr-1 h-4 w-4"></i>
                        {{ __('messages.Previous') }}
                      </button>
                      <button
                        onclick="playNextVideo()"
                        id="next-btn"
                        class="inline-flex items-center rounded-lg bg-brand-secondary/10 px-4 py-2 text-sm font-semibold text-brand-secondary transition-colors duration-300 hover:bg-brand-secondary/20 disabled:cursor-not-allowed disabled:opacity-50"
                      >
                        {{ __('messages.Next') }}
                        <i data-lucide="chevron-right" class="ml-1 h-4 w-4"></i>
                      </button>
                    </div>
                    <div id="video-progress" class="text-xs font-medium text-[#5b6b73]">
                      <span id="current-video-number">1</span> / <span id="total-videos">3</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- Right Side: Playlist (1/3 width on large screens) --}}
            <div class="lg:col-span-1">
              <div class="flex h-full max-h-[600px] flex-col overflow-hidden rounded-2xl border border-[#dce6e4] bg-white shadow-[0_18px_50px_rgba(7,27,50,0.10)] max-lg:max-h-[520px]">
                <div class="border-b border-[#dce6e4] bg-[#f2f8f7] p-4">
                  <h3 class="text-lg font-bold text-brand-primary">{{ __('messages.Feature Demos') }}</h3>
                  <p class="mt-1 text-xs text-[#5b6b73]">{{ __('messages.Select a feature to watch') }}</p>
                </div>

                <div class="flex-1 overflow-y-auto playlist-scroll">
                  {{-- 0. Dashboard Overview --}}
                  <div
                    onclick="playPlaylistVideo(0)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="0"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/DashboardDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Dashboard Overview') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.See your business at a glance with real-time metrics and quick access to all modules.') }}</p>
                    </div>
                  </div>

                  {{-- 1. Invoice Management --}}
                  <div
                    onclick="playPlaylistVideo(1)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="1"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/InvoiceDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Invoice Management') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Create, send, and track professional invoices with automated reminders.') }}</p>
                    </div>
                  </div>

                  {{-- 2. Quotes / Devis to Invoice --}}
                  <div
                    onclick="playPlaylistVideo(2)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="2"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/DevisDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Quotes & Devis to Invoice') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Create quotes, get approvals, and convert them into real projects and tasks in one click.') }}</p>
                    </div>
                  </div>

                  {{-- 3. Project & Task Management --}}
                  <div
                    onclick="playPlaylistVideo(3)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="3"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/Projects_TasksDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Project & Task Management') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Organize projects, subprojects, and tasks with assignments and deadlines.') }}</p>
                    </div>
                  </div>

                  {{-- 4. Time Tracking / Track Timer --}}
                  <div
                    onclick="playPlaylistVideo(4)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="4"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/TimeTrackerDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Time Tracking') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Track time per task and project with real-time timers and logs.') }}</p>
                    </div>
                  </div>

                  {{-- 5. Expense Management / Note de frais --}}
                  <div
                    onclick="playPlaylistVideo(5)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="5"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-accent/30 to-brand-accent/10">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/ExpenseDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Expense Management') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Record expenses, attach receipts, and categorize note de frais.') }}</p>
                    </div>
                  </div>

                  {{-- 6. Client Management --}}
                  <div
                    onclick="playPlaylistVideo(6)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="6"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/ClientDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Client Management') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Manage client profiles, history, and documents in one place.') }}</p>
                    </div>
                  </div>

                  {{-- 7. Team & Employee Management --}}
                  <div
                    onclick="playPlaylistVideo(7)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="7"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/EmployeesDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Team & Employee Management') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Create teams, manage employees, and control roles & permissions.') }}</p>
                    </div>
                  </div>

                  {{-- 8. Calendar & Events --}}
                  <div
                    onclick="playPlaylistVideo(8)"
                    class="playlist-item group flex cursor-pointer gap-3 border-b border-[#dce6e4] p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="8"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/PlanningDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Calendar & Events') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Schedule events, deadlines, and meetings with reminders.') }}</p>
                    </div>
                  </div>

                  {{-- 9. End-to-End Day in the Life --}}
                  <div
                    onclick="playPlaylistVideo(9)"
                    class="playlist-item group flex cursor-pointer gap-3 p-3 transition-colors duration-300 hover:bg-[#f2f8f7]"
                    data-video-index="9"
                  >
                    <div class="relative h-24 w-40 flex-none overflow-hidden rounded-lg bg-gradient-to-br from-brand-secondary/25 to-[#8ccfd0]/20">
                      <video
                        class="h-full w-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/DayDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-brand-primary/30 transition-colors duration-300 group-hover:bg-brand-primary/20">
                        <i data-lucide="play" class="h-8 w-8 fill-current text-white"></i>
                      </div>
                    </div>
                    <div class="min-w-0 flex-1">
                      <h4 class="mb-1 line-clamp-2 text-sm font-semibold text-brand-primary transition-colors duration-300 group-hover:text-brand-secondary">{{ __('messages.Day in the Life') }}</h4>
                      <p class="line-clamp-2 text-xs text-[#5b6b73]">{{ __('messages.Follow a real workflow from client to project to invoice and reporting.') }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

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

      .playlist-item video {
        transition: transform 0.3s ease;
      }

      .playlist-item:hover video {
        transform: scale(1.05);
      }

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

    <script>
      // Video Playlist Functions
      let currentPlaylistIndex = 0;
      const playlistVideos = [
        { src: '{{ asset("videos/DashboardDemo.mp4") }}', name: {!! json_encode(__('messages.Dashboard Overview')) !!} },
        { src: '{{ asset("videos/InvoiceDemo.mp4") }}', name: {!! json_encode(__('messages.Invoice Management')) !!} },
        { src: '{{ asset("videos/DevisDemo.mp4") }}', name: {!! json_encode(__('messages.Quotes & Devis to Invoice')) !!} },
        { src: '{{ asset("videos/Projects_TasksDemo.mp4") }}', name: {!! json_encode(__('messages.Project & Task Management')) !!} },
        { src: '{{ asset("videos/TimeTrackerDemo.mp4") }}', name: {!! json_encode(__('messages.Time Tracking')) !!} },
        { src: '{{ asset("videos/ExpenseDemo.mp4") }}', name: {!! json_encode(__('messages.Expense Management')) !!} },
        { src: '{{ asset("videos/ClientDemo.mp4") }}', name: {!! json_encode(__('messages.Client Management')) !!} },
        { src: '{{ asset("videos/EmployeesDemo.mp4") }}', name: {!! json_encode(__('messages.Team & Employee Management')) !!} },
        { src: '{{ asset("videos/PlanningDemo.mp4") }}', name: {!! json_encode(__('messages.Calendar & Events')) !!} },
        { src: '{{ asset("videos/DayDemo.mp4") }}', name: {!! json_encode(__('messages.Day in the Life')) !!} }
      ];

      // Initialize: Play first video on load
      document.addEventListener('DOMContentLoaded', function() {
        playPlaylistVideo(0);

        // Auto-play thumbnail videos on hover
        const playlistItems = document.querySelectorAll('.playlist-item');
        playlistItems.forEach((item, index) => {
          const thumbnailVideo = item.querySelector('video');
          if (thumbnailVideo) {
            item.addEventListener('mouseenter', function() {
              thumbnailVideo.play();
            });
            item.addEventListener('mouseleave', function() {
              thumbnailVideo.pause();
              thumbnailVideo.currentTime = 0;
            });
          }
        });
      });

      function playPlaylistVideo(index) {
        currentPlaylistIndex = index;
        const video = document.getElementById('demo-video-player');
        const playlistItems = document.querySelectorAll('.playlist-item');
        const videoTitle = document.getElementById('current-video-title');

        if (video && playlistVideos[index]) {
          // Update video source
          video.innerHTML = '<source src="' + playlistVideos[index].src + '" type="video/mp4">';
          video.load();

          // Update video title
          if (videoTitle) {
            videoTitle.textContent = playlistVideos[index].name;
          }

          // Update active state
          playlistItems.forEach((item, i) => {
            if (i === index) {
              item.classList.add('active');
            } else {
              item.classList.remove('active');
            }
          });

          // Update navigation buttons
          updatePlaylistNavigation();

          // Play video
          video.play();
        }
      }

      function playNextVideo() {
        if (currentPlaylistIndex < playlistVideos.length - 1) {
          playPlaylistVideo(currentPlaylistIndex + 1);
        }
      }

      function playPreviousVideo() {
        if (currentPlaylistIndex > 0) {
          playPlaylistVideo(currentPlaylistIndex - 1);
        }
      }

      function updatePlaylistNavigation() {
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const currentNumber = document.getElementById('current-video-number');
        const totalVideos = document.getElementById('total-videos');

        if (prevBtn) prevBtn.disabled = currentPlaylistIndex === 0;
        if (nextBtn) nextBtn.disabled = currentPlaylistIndex === playlistVideos.length - 1;
        if (currentNumber) currentNumber.textContent = currentPlaylistIndex + 1;
        if (totalVideos) totalVideos.textContent = playlistVideos.length;
      }
    </script>
  </body>
</html>
