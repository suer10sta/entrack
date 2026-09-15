<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('messages.Watch Demo') }} | DalyTrac</title>
    <meta name="description" content="Watch DalyTrac demo videos to see how our platform handles dashboards, invoices, projects, time tracking, expenses, clients and more.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('demo') }}">

    <meta property="og:title" content="{{ __('messages.Watch Demo') }} | DalyTrac">
    <meta property="og:description" content="Explore DalyTrac feature demos and discover how to streamline your business operations end‑to‑end.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('demo') }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('messages.Watch Demo') }} | DalyTrac">
    <meta name="twitter:description" content="See DalyTrac in action across invoicing, projects, time tracking, calendar and more.">

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
      href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
      rel="stylesheet"
    />
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="welcome/css/aos.css" />
    <script
      src="https://kit.fontawesome.com/a076d05399.js"
      crossorigin="anonymous"
    ></script>
  </head>

  <body class="antialiased bg-gradient-to-b from-background to-primary/5 text-muted font-figtree">
    <x-Home_page.header />

    <main>
      {{-- Hero Section --}}
      <section class="relative pt-24 pb-8 lg:pt-32 lg:pb-12 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-transparent to-cyan-500/5"></div>
        
        <div class="relative z-10 container mx-auto px-6 text-center">
          <div class="inline-block mb-4 px-4 py-1.5 rounded-full bg-primary/10 border border-primary/20 text-primary text-sm font-semibold tracking-wide">
            {{ __('messages.Feature Demos') }}
          </div>
          <h1 class="text-3xl lg:text-5xl font-bold text-foreground mb-4 tracking-tight">
            {{ __('messages.See DalyTrac in') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-cyan-500">{{ __('messages.Action') }}</span>
          </h1>
          <p class="text-lg lg:text-xl text-muted-foreground max-w-3xl mx-auto leading-relaxed">
            {{ __('messages.Click on any feature to watch a detailed demo video and discover how DalyTrac can transform your business operations.') }}
          </p>
        </div>
      </section>

      {{-- YouTube-style Layout: Video on Left, Playlist on Right --}}
      <section class="py-8 lg:py-12 pb-16" style="margin-top: -35px;">
        <div class="container mx-auto px-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 max-w-7xl mx-auto">
            
            {{-- Left Side: Video Player (2/3 width on large screens) --}}
            <div class="lg:col-span-2">
              {{-- Video Player --}}
              <div id="video-player-container" class="bg-card rounded-xl shadow-lg overflow-hidden border border-border">
                <div class="w-full aspect-video bg-black">
                  <video 
                    id="demo-video-player"
                    controls
                    autoplay
                    class="w-full h-full"
                  >
                    <source src="{{ asset('videos/DashboardDemo.mp4') }}" type="video/mp4">
                    {{ __('messages.Your browser does not support the video tag.') }}
                  </video>
                </div>
                <div class="p-4 bg-background">
                  <h2 id="current-video-title" class="text-xl font-bold text-foreground mb-2">{{ __('messages.Dashboard Overview') }}</h2>
                  <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex gap-2">
                      <button 
                        onclick="playPreviousVideo()" 
                        id="prev-btn"
                        class="px-4 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold"
                        disabled
                      >
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        {{ __('messages.Previous') }}
                      </button>
                      <button 
                        onclick="playNextVideo()" 
                        id="next-btn"
                        class="px-4 py-2 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 transition-colors disabled:opacity-50 disabled:cursor-not-allowed text-sm font-semibold"
                      >
                        {{ __('messages.Next') }}
                        <svg class="w-4 h-4 inline ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                      </button>
                    </div>
                    <div id="video-progress" class="text-xs text-muted-foreground font-medium">
                      <span id="current-video-number">1</span> / <span id="total-videos">3</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            {{-- Right Side: Playlist (1/3 width on large screens) --}}
            <div class="lg:col-span-1" style="height: 562px;">
              <div class="bg-card rounded-xl border border-border shadow-lg overflow-hidden flex flex-col h-full max-h-[600px]">
                <div class="p-4 bg-gradient-to-r from-primary/10 to-primary/5 border-b border-border">
                  <h3 class="text-lg font-bold text-foreground">{{ __('messages.Feature Demos') }}</h3>
                  <p class="text-xs text-muted-foreground mt-1">{{ __('messages.Select a feature to watch') }}</p>
                </div>
                
                <div class="flex-1 overflow-y-auto">
                  {{-- Playlist Items --}}
                  {{-- 0. Dashboard Overview --}}
                  <div 
                    onclick="playPlaylistVideo(0)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="0"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-primary/20 to-primary/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/DashboardDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Dashboard Overview') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.See your business at a glance with real-time metrics and quick access to all modules.') }}</p>
                    </div>
                  </div>

                  {{-- 1. Invoice Management --}}
                  <div 
                    onclick="playPlaylistVideo(1)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="1"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-blue-500/20 to-cyan-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/InvoiceDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Invoice Management') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Create, send, and track professional invoices with automated reminders.') }}</p>
                    </div>
                  </div>

                  {{-- 2. Quotes / Devis to Invoice --}}
                  <div 
                    onclick="playPlaylistVideo(2)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="2"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-indigo-500/20 to-purple-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/DevisDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Quotes & Devis to Invoice') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Create quotes, get approvals, and convert them into real projects and tasks in one click.') }}</p>
                    </div>
                  </div>

                  {{-- 3. Project & Task Management --}}
                  <div 
                    onclick="playPlaylistVideo(3)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="3"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-emerald-500/20 to-teal-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/Projects_TasksDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Project & Task Management') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Organize projects, subprojects, and tasks with assignments and deadlines.') }}</p>
                    </div>
                  </div>

                  {{-- 4. Time Tracking / Track Timer --}}
                  <div 
                    onclick="playPlaylistVideo(4)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="4"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-emerald-500/20 to-teal-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/TimeTrackerDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Time Tracking') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Track time per task and project with real-time timers and logs.') }}</p>
                    </div>
                  </div>

                  {{-- 5. Expense Management / Note de frais --}}
                  <div 
                    onclick="playPlaylistVideo(5)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="5"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-amber-500/20 to-yellow-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/ExpenseDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Expense Management') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Record expenses, attach receipts, and categorize note de frais.') }}</p>
                    </div>
                  </div>


                  {{-- 7. Client Management --}}
                  <div 
                    onclick="playPlaylistVideo(7)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="7"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-pink-500/20 to-rose-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/ClientDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2a3 3 0 00-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Client Management') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Manage client profiles, history, and documents in one place.') }}</p>
                    </div>
                  </div>

                  {{-- 8. Team & Employee Management --}}
                  <div 
                    onclick="playPlaylistVideo(8)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="8"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-amber-500/20 to-orange-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >   
                        <source src="{{ asset('videos/EmployeesDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Team & Employee Management') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Create teams, manage employees, and control roles & permissions.') }}</p>
                    </div>
                  </div>

                  {{-- 9. Calendar & Events --}}
                  <div 
                    onclick="playPlaylistVideo(9)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors border-b border-border"
                    data-video-index="9"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-green-500/20 to-emerald-500/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/PlanningDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Calendar & Events') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Schedule events, deadlines, and meetings with reminders.') }}</p>
                    </div>
                  </div>

                  {{-- 10. End-to-End Day in the Life --}}
                  <div 
                    onclick="playPlaylistVideo(10)" 
                    class="playlist-item group flex gap-3 p-3 hover:bg-primary/5 cursor-pointer transition-colors"
                    data-video-index="10"
                  >
                    <div class="flex-shrink-0 w-40 h-24 rounded bg-gradient-to-br from-slate-500/20 to-slate-700/10 relative overflow-hidden">
                      <video 
                        class="w-full h-full object-cover"
                        muted
                        loop
                        playsinline
                      >
                        <source src="{{ asset('videos/DayDemo.mp4') }}" type="video/mp4">
                      </video>
                      <div class="absolute inset-0 flex items-center justify-center bg-black/30 group-hover:bg-black/20 transition-colors">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1"/>
                        </svg>
                      </div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <h4 class="text-sm font-semibold text-foreground group-hover:text-primary transition-colors line-clamp-2 mb-1">{{ __('messages.Day in the Life') }}</h4>
                      <p class="text-xs text-muted-foreground line-clamp-2">{{ __('messages.Follow a real workflow from client to project to invoice and reporting.') }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </main>

    <x-Home_page.footer />

    <style>
      .playlist-item {
        transition: all 0.2s ease;
      }
      
      .playlist-item.active {
        background-color: rgba(14, 165, 233, 0.1);
        border-left: 3px solid rgb(14, 165, 233);
      }
      
      .playlist-item video {
        transition: transform 0.3s ease;
      }
      
      .playlist-item:hover video {
        transform: scale(1.05);
      }
      
      /* Custom scrollbar for playlist */
      .max-h-\[600px\]::-webkit-scrollbar {
        width: 6px;
      }
      
      .max-h-\[600px\]::-webkit-scrollbar-track {
        background: transparent;
      }
      
      .max-h-\[600px\]::-webkit-scrollbar-thumb {
        background: rgba(14, 165, 233, 0.3);
        border-radius: 3px;
      }
      
      .max-h-\[600px\]::-webkit-scrollbar-thumb:hover {
        background: rgba(14, 165, 233, 0.5);
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

