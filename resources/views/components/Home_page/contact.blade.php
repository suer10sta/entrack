<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-favicon />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ __('messages.Contact | DalyTrac') }}</title>
    <meta name="description"
        content="{{ __('messages.Contact the DalyTrac team with questions about pricing, onboarding, features or support. We’ll respond as quickly as possible.') }}">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('contact') }}">

    <meta property="og:title" content="{{ __('messages.Contact | DalyTrac') }}">
    <meta property="og:description"
        content="{{ __('messages.Reach out to DalyTrac for help with invoicing, projects, time tracking and account support.') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('contact') }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('messages.Contact | DalyTrac') }}">
    <meta name="twitter:description" content="{{ __('messages.Send a message to the DalyTrac support and sales team.') }}">

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
    {{-- TEMPORARILY DISABLED: Cloudflare Turnstile verification (restore to re-enable anti-bot check).
    <!-- Cloudflare Turnstile -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    --}}
</head>

<body class="overflow-x-hidden bg-[#f4f7f6] font-sans text-[#0B2638] antialiased">

    <x-public.header />

    <main class="min-h-screen px-4 pt-8 pb-12 sm:px-6 lg:px-8 lg:pb-16">
        <div class="mx-auto max-w-[720px] ">
            <div class="overflow-hidden rounded-[28px] border border-[#dfe9e7] bg-white shadow-[0_24px_70px_rgba(11,38,56,0.08)]">
                <div class="">
                    

                    <section class="px-5 py-7 sm:px-8 lg:px-10 lg:py-10">
                        <div class="mx-auto max-w-[560px]">
                            <div class="mb-7">
                                <h2 class="text-[2rem] font-bold leading-[1.1] tracking-[-0.05em] text-[#0B2638]">{{ __('messages.Contact Us') }}</h2>
                                <p class="mt-2 text-[0.95rem] leading-7 text-[#5b6b73]">
                                    {{ __('messages.Have a question or need assistance? We\'d love to hear from you. Fill out the form below and we\'ll get back to you as soon as possible.') }}
                                </p>
                            </div>

                            @if (session('error'))
                                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if (session('success'))
                                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                    <ul class="m-0 list-disc space-y-1 pl-5">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('contact.submit') }}" class="space-y-5" novalidate>
                                @csrf

                                <div style="display: none;">
                                    <label for="website">{{ __('messages.Website') }}</label>
                                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                                </div>

                                <div class="grid gap-4 sm:grid-cols-2">
                                    <div class="space-y-2 sm:col-span-1">
                                        <label for="name" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Name') }} <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                                <i data-lucide="user" class="h-4 w-4"></i>
                                            </span>
                                            <input type="text" id="name" name="name" required value="{{ old('name') }}" placeholder="{{ __('messages.Your name') }}" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10 @error('name') border-red-300 bg-red-50 @enderror" />
                                        </div>
                                        @error('name')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div class="space-y-2 sm:col-span-1">
                                        <label for="email" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Email') }} <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                                <i data-lucide="mail" class="h-4 w-4"></i>
                                            </span>
                                            <input type="email" id="email" name="email" required value="{{ old('email') }}" placeholder="your@email.com" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10 @error('email') border-red-300 bg-red-50 @enderror" />
                                        </div>
                                        @error('email')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <label for="subject" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Subject') }} <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                            <i data-lucide="tag" class="h-4 w-4"></i>
                                        </span>
                                        <input type="text" id="subject" name="subject" required value="{{ old('subject') }}" placeholder="{{ __('messages.How can we help?') }}" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10 @error('subject') border-red-300 bg-red-50 @enderror" />
                                    </div>
                                    @error('subject')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <label for="message" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Message') }} <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute left-3 top-3.5 flex items-center text-[#5b6b73]">
                                            <i data-lucide="message-square-text" class="h-4 w-4"></i>
                                        </span>
                                        <textarea id="message" name="message" rows="6" required placeholder="{{ __('messages.Tell us more about your inquiry...') }}" class="w-full resize-none rounded-[22px] border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10 @error('message') border-red-300 bg-red-50 @enderror">{{ old('message') }}</textarea>
                                    </div>
                                    @error('message')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- TEMPORARILY DISABLED: Cloudflare Turnstile verification (restore to re-enable anti-bot check).
                                @if(config('services.turnstile.site_key'))
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                                @endif
                                --}}

                                <button type="submit" class="btn-sweep btn-sweep-navy group inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-full border border-transparent bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer">
                                    <span>{{ __('messages.Send Message') }}</span>
                                    <i data-lucide="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5"></i>
                                </button>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <x-public.footer />

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>

</body>

</html>