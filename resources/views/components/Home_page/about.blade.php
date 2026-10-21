<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <head>
    <x-favicon />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('messages.About Us') }} | DalyTrac</title>
    <meta name="description" content="{{ __('messages.Learn about DalyTrac, the all‑in‑one business management platform that unifies invoices, projects, teams and finances into one calm, powerful workspace.') }}">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('about') }}">

    <meta property="og:title" content="{{ __('messages.About Us') }} | DalyTrac">
    <meta property="og:description" content="{{ __('messages.Discover the story, mission and values behind DalyTrac and how we help teams simplify operations, invoicing and time tracking.') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('about') }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('messages.About Us') }} | DalyTrac">
    <meta name="twitter:description" content="{{ __('messages.DalyTrac connects your invoices, projects and people in a single, unified platform.') }}">

    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
      href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap"
      rel="stylesheet"
    />
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="welcome/css/aos.css" />
    <!-- This script is for the "fas fa-arrow-right" icon in your CTA button -->
    <script
      src="https://kit.fontawesome.com/a076d05399.js"
      crossorigin="anonymous"
    ></script>
  </head>

  <!-- 
    CHANGES:
    - bg-white -> bg-card (or bg-white, they are the same in your config)
    - text-slate-600 -> text-muted
  -->
  <body class="antialiased bg-card text-muted font-figtree">
    <x-public.header />

    <main>

      {{-- Hero / Intro --}}
      <section class="relative pt-40 pb-24 lg:pt-48 lg:pb-32 overflow-hidden" data-aos="fade-in">
        <div class="absolute inset-0 bg-[linear-gradient(to_right,theme(colors.secondary)_1px,transparent_1px),linear-gradient(to_bottom,theme(colors.secondary)_1px,transparent_1px)] bg-[size:4rem_4rem]"></div>
        
        <div class="relative z-10 container mx-auto px-6 text-center">
          <div class="inline-block mb-6 px-4 py-1.5 rounded-full bg-secondary border border-border text-primary text-sm font-semibold tracking-wide uppercase">
            {{ __('messages.About Us') }}
          </div>
          <h1 class="text-5xl lg:text-7xl font-bold text-foreground mb-8 tracking-tight">
            {{ __('messages.Meet') }} <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary to-accent">DalyTrac</span><br />
            {{ __('messages.your operational command center') }}
          </h1>
          <p class="text-xl text-muted max-w-3xl mx-auto leading-relaxed">
            {{ __('messages.We strip away the complexity of business operations and bring your invoices, projects, teams, and finances together into one calm, powerful workspace.') }}
          </p>
        </div>
      </section>

      {{-- Mission + Snapshot --}}
      <section class="py-24 bg-secondary">
        <div class="container mx-auto px-6">
            <div class="flex flex-col md:flex-row gap-12 items-center">
                <div class="md:w-1/2" data-aos="fade-right">
                    <h2 class="text-3xl font-bold text-foreground mb-6 border-l-4 border-primary pl-4">
                        {{ __('messages.Our Mission') }}
                    </h2>
                    <p class="text-lg text-muted mb-6">
                        {{ __('messages.We believe software shouldn\'t force you to change how you work. It should adapt to you.') }}
                    </p>
                    <p class="text-lg text-muted">
                        {{ __('messages.Our mission is to provide a unified platform where invoices, projects, and people connect seamlessly, removing the "digital friction" that slows down modern businesses.') }}
                    </p>
                </div>
                <div class="md:w-1/2" data-aos="fade-left">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-card p-6 rounded-2xl shadow-sm border-t-4 border-primary">
                            <h3 class="text-4xl font-bold text-foreground mb-1">99%</h3>
                            <p class="text-sm text-muted-foreground">{{ __('messages.Uptime Reliability') }}</p>
                        </div>
                        <div class="bg-primary p-6 rounded-2xl shadow-sm text-primary-foreground mt-8">
                            <h3 class="text-4xl font-bold mb-1">24/7</h3>
                            <p class="text-sm opacity-90">{{ __('messages.Dedicated Support') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </section>

      {{-- Our Story --}}
      <section class="py-24 bg-card border-y border-border">
        <div class="container mx-auto px-6 max-w-6xl">
          <div class="grid gap-12 lg:grid-cols-2 items-center">
            <div data-aos="fade-right">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-secondary text-xs font-semibold uppercase tracking-wide text-primary mb-4">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                {{ __('messages.About Our Story Badge') }}
              </div>
              <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-6">
                {{ __('messages.About Our Story Title') }}
              </h2>
              <p class="text-muted mb-4 leading-relaxed">
                {{ __('messages.About Our Story Paragraph 1') }}
              </p>
              <p class="text-muted mb-4 leading-relaxed">
                {{ __('messages.About Our Story Paragraph 2') }}
              </p>
              <p class="text-muted leading-relaxed">
                {{ __('messages.About Our Story Paragraph 3') }}
              </p>
            </div>

            <div data-aos="fade-left">
              <div class="relative">
                <div class="absolute -inset-4 bg-gradient-to-tr from-primary/10 via-accent/10 to-transparent rounded-3xl blur-2xl"></div>
                <div class="relative bg-background border border-border rounded-3xl p-6 md:p-8 shadow-xl">
                  <div class="flex items-center justify-between mb-6">
                    <div>
                      <p class="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                        {{ __('messages.About Today Label') }}
                      </p>
                      <p class="text-2xl font-bold text-foreground">{{ __('messages.About DalyTrac at a glance') }}</p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-secondary text-primary">
                      {{ __('messages.About Live platform') }}
                    </span>
                  </div>

                  <dl class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-secondary rounded-2xl p-4">
                      <dt class="text-xs font-medium text-muted-foreground mb-1">{{ __('messages.About Teams served') }}</dt>
                      <dd class="text-2xl font-bold text-foreground">50+</dd>
                    </div>
                    <div class="bg-secondary rounded-2xl p-4">
                      <dt class="text-xs font-medium text-muted-foreground mb-1">{{ __('messages.About Invoices tracked') }}</dt>
                      <dd class="text-2xl font-bold text-foreground">10k+</dd>
                    </div>
                    <div class="bg-secondary rounded-2xl p-4">
                      <dt class="text-xs font-medium text-muted-foreground mb-1">{{ __('messages.About Time entries') }}</dt>
                      <dd class="text-2xl font-bold text-foreground">250k+</dd>
                    </div>
                    <div class="bg-secondary rounded-2xl p-4">
                      <dt class="text-xs font-medium text-muted-foreground mb-1">{{ __('messages.About Countries') }}</dt>
                      <dd class="text-2xl font-bold text-foreground">10+</dd>
                    </div>
                  </dl>

                  <div class="flex items-start gap-3">
                    <div class="mt-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 6v6l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                    <p class="text-sm text-muted-foreground leading-relaxed">
                      {{ __('messages.About Today Footnote') }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      {{-- Values --}}
      <section class="py-24 bg-background" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-6xl">
          <div class="text-center mb-14">
            <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">{{ __('messages.About Values Title') }}</h2>
            <p class="text-muted max-w-2xl mx-auto">
              {{ __('messages.About Values Subtitle') }}
            </p>
          </div>

          <div class="grid gap-8 md:grid-cols-3">
            <div class="bg-card border border-border rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
              <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('messages.About Value 1 Title') }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ __('messages.About Value 1 Body') }}
              </p>
            </div>

            <div class="bg-card border border-border rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
              <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('messages.About Value 2 Title') }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ __('messages.About Value 2 Body') }}
              </p>
            </div>

            <div class="bg-card border border-border rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
              <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 8h2a2 2 0 012 2v8a2 2 0 01-2 2h-2M7 8H5a2 2 0 00-2 2v8a2 2 0 002 2h2m10-8a5 5 0 00-10 0m10 0v3a5 5 0 11-10 0v-3" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-foreground mb-2">{{ __('messages.About Value 3 Title') }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ __('messages.About Value 3 Body') }}
              </p>
            </div>
          </div>
        </div>
      </section>

      {{-- Simple timeline / How we help --}}
      <section class="py-24 bg-card" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-5xl">
          <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">{{ __('messages.About Timeline Title') }}</h2>
            <p class="text-muted max-w-2xl mx-auto">
              {{ __('messages.About Timeline Subtitle') }}
            </p>
          </div>

          <ol class="space-y-8">
            <li class="relative pl-10">
              <span class="absolute left-0 top-1.5 w-7 h-7 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-bold shadow-md">
                {{ __('messages.public_pricing_users_solo') }}
              </span>
              <h3 class="text-lg font-semibold text-foreground mb-1">{{ __('messages.About Step 1 Title') }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ __('messages.About Step 1 Body') }}
              </p>
            </li>
            <li class="relative pl-10">
              <span class="absolute left-0 top-1.5 w-7 h-7 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-bold shadow-md">
                2
              </span>
              <h3 class="text-lg font-semibold text-foreground mb-1">{{ __('messages.About Step 2 Title') }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ __('messages.About Step 2 Body') }}
              </p>
            </li>
            <li class="relative pl-10">
              <span class="absolute left-0 top-1.5 w-7 h-7 rounded-full bg-primary text-primary-foreground flex items-center justify-center text-xs font-bold shadow-md">
                3
              </span>
              <h3 class="text-lg font-semibold text-foreground mb-1">{{ __('messages.About Step 3 Title') }}</h3>
              <p class="text-sm text-muted-foreground leading-relaxed">
                {{ __('messages.About Step 3 Body') }}
              </p>
            </li>
          </ol>
        </div>
      </section>

      {{-- Feature overview (reassurance) --}}
      <section class="py-24 bg-card" data-aos="fade-up">
        <div class="container mx-auto px-6 max-w-7xl">
          
          <div class="text-center mb-16">
            <h2 class="text-3xl md:text-4xl font-bold text-foreground mb-4">{{ __('messages.About Everything Title') }}</h2>
            <p class="text-muted max-w-2xl mx-auto">{{ __('messages.About Everything Subtitle') }}</p>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            
            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">{{ __('messages.About Everything Invoice Title') }}</h3>
              <p class="text-muted leading-relaxed">{{ __('messages.About Everything Invoice Body') }}</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M6.429 9.75L2.25 12l4.179 2.25m0-4.5l5.571 3 5.571-3m-11.142 0L2.25 7.5 12 2.25l9.75 5.25-4.179 2.25m0 0L21.75 12l-4.179 2.25m0 0l4.179 2.25L12 21.75 2.25 16.5l4.179-2.25m11.142 0l-5.571 3-5.571-3" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">{{ __('messages.About Everything Project Title') }}</h3>
              <p class="text-muted leading-relaxed">{{ __('messages.About Everything Project Body') }}</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">{{ __('messages.About Everything Team Title') }}</h3>
              <p class="text-muted leading-relaxed">{{ __('messages.About Everything Team Body') }}</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">{{ __('messages.About Everything Analytics Title') }}</h3>
              <p class="text-muted leading-relaxed">{{ __('messages.About Everything Analytics Body') }}</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">{{ __('messages.About Everything Clients Title') }}</h3>
              <p class="text-muted leading-relaxed">{{ __('messages.About Everything Clients Body') }}</p>
            </div>

            <div class="group p-8 bg-card rounded-2xl border border-border shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
              <div class="w-14 h-14 bg-secondary rounded-xl flex items-center justify-center text-primary mb-6 group-hover:bg-primary group-hover:text-primary-foreground transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-foreground mb-3">{{ __('messages.About Everything Expenses Title') }}</h3>
              <p class="text-muted leading-relaxed">{{ __('messages.About Everything Expenses Body') }}</p>
            </div>

          </div>
        </div>
      </section>

      <section class="py-24 bg-secondary border-t border-border" data-aos="zoom-in">
        <div class="container mx-auto px-6 text-center">
            
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-card border border-border text-primary text-xs font-bold tracking-wide uppercase mb-6 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-primary"></span>
                {{ __('messages.About Personalized Badge') }}
            </div>
            
            <h2 class="text-3xl md:text-5xl font-bold text-foreground mb-6 tracking-tight">
                {{ __('messages.About Personalized Title Line 1') }} <br/> {{ __('messages.About Personalized Title Line 2') }}
            </h2>
            
            <p class="text-lg text-muted mb-10 max-w-2xl mx-auto leading-relaxed">
                {{ __('messages.About Personalized Body') }}
            </p>

            <a href="/contact" class="inline-flex items-center gap-3 px-8 py-4 bg-primary text-primary-foreground font-bold rounded-lg shadow-lg shadow-primary/20 hover:bg-primary/90 hover:-translate-y-1 transition-all duration-300">
                <span>{{ __('messages.About Personalized CTA') }}</span>
                <i class="fas fa-arrow-right"></i>
            </a>
        </div>
      </section>

    </main>

    <x-public.footer />

    <script src="welcome/js/aos.js"></script>
    <script>
      AOS.init({ duration: 800, once: true, offset: 50 });
    </script>
  </body>
</html>
