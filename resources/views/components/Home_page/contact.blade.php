<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Contact | DalyTrac</title>
    <meta name="description"
        content="Contact the DalyTrac team with questions about pricing, onboarding, features or support. We’ll respond as quickly as possible.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('contact') }}">

    <meta property="og:title" content="Contact | DalyTrac">
    <meta property="og:description"
        content="Reach out to DalyTrac for help with invoicing, projects, time tracking and account support.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('contact') }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Contact | DalyTrac">
    <meta name="twitter:description" content="Send a message to the DalyTrac support and sales team.">

    <!-- Fonts & CSS -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    {{--
    <link rel="stylesheet" href="welcome/css/bootstrap.min.css"> --}}
    <link rel="stylesheet" href="welcome/css/aos.css">
    <link rel="stylesheet" href="welcome/css/owl.carousel.min.css">
    <link rel="stylesheet" href="welcome/css/owl.theme.default.min.css">
    {{--
    <link href="{{ asset('css/app.css') }}" rel="stylesheet"> --}}
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="welcome/css/templatemo-digital-trend.css">

    <style>
        input:focus,
        textarea:focus {
            outline: 2px solid theme('colors.primary');
            outline-offset: 2px;
            border-color: transparent;
        }
    </style>
    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>

<body class="antialiased bg-gradient-to-b from-sky-50 via-sky-100 to-slate-50 text-foreground">

    {{-- Header --}}
    <x-Home_page.header />

    {{-- Main Contact Section --}}
    <main
        class="relative min-h-screen overflow-hidden px-4 pt-24 pb-12 sm:px-6 sm:pt-28 sm:pb-16 lg:px-8 lg:pt-32 lg:pb-20">
        <!-- Ambient background glow to match auth pages -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-32 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl"></div>
        </div>

        <div class="relative z-10 container mx-auto">
            <div
                class="max-w-xl mx-auto bg-white/95 p-6 sm:p-8 lg:p-10 rounded-2xl shadow-2xl ring-1 ring-border/40 backdrop-blur-md">

                <h1 class="text-3xl sm:text-4xl font-bold text-foreground mb-3 text-center">
                    {{ __('messages.Contact Us') }}</h1>
                <p class="text-sm text-muted mb-8 text-center">
                    {{ __('messages.Have a question or need assistance? We\'d love to hear from you. Fill out the form below and we\'ll get back to you as soon as possible.') }}
                </p>

                {{-- Mail error message --}}
                @if (session('error'))
                    <p class="mb-4 text-sm text-center text-red-600">
                        {{ session('error') }}
                    </p>
                @endif

                {{-- Flash message --}}
                @if (session('success'))
                    <p class="mb-4 text-sm text-center text-green-600">
                        {{ session('success') }}
                    </p>
                @endif

                @if ($errors->any())
                    <div class="mb-4 text-sm text-red-600">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Contact Form --}}
                <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5">
                    @csrf

                    {{-- Honeypot Field --}}
                    <div style="display: none;">
                        <label for="website">Website</label>
                        <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="name"
                                class="block text-xs font-medium text-muted mb-2">{{ __('messages.Name') }}</label>
                            <input type="text" id="name" name="name" required
                                class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground bg-card"
                                placeholder="{{ __('messages.Your name') }}">
                        </div>

                        <div>
                            <label for="email"
                                class="block text-xs font-medium text-muted mb-2">{{ __('messages.Email') }}</label>
                            <input type="email" id="email" name="email" required
                                class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground bg-card"
                                placeholder="your@email.com">
                        </div>
                    </div>
                    <div>
                        <label for="subject"
                            class="block text-xs font-medium text-muted mb-2">{{ __('messages.Subject') }}</label>
                        <input type="text" id="subject" name="subject" required
                            class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground bg-card"
                            placeholder="{{ __('messages.How can we help?') }}">
                    </div>

                    <div>
                        <label for="message"
                            class="block text-xs font-medium text-muted mb-2">{{ __('messages.Message') }}</label>
                        <textarea id="message" name="message" rows="5" required
                            class="w-full px-3 py-2.5 text-sm border border-border rounded-md placeholder-muted-foreground resize-none bg-card"
                            placeholder="{{ __('messages.Tell us more about your inquiry...') }}"></textarea>
                    </div>

                    {{-- Turnstile Widget --}}
                    @if(config('services.turnstile.site_key'))
                        <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                    @endif

                    <button type="submit"
                        class="w-full text-primary-foreground text-sm font-medium py-3 rounded-md bg-primary hover:bg-primary/90 transition-colors">
                        {{ __('messages.Send Message') }}
                    </button>
                </form>
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <x-Home_page.footer />

</body>

</html>