<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="@yield('page-description')">
    <title>@yield('page-title') | DalyTrac</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @include('partials.password-visibility')
    @include('auth.partials.feature-strip-styles')
    <script src="https://unpkg.com/lucide@1.47.0/dist/umd/lucide.min.js" defer></script>
</head>
<body class="overflow-x-hidden bg-[#f4f7f6] font-sans text-[#0B2638] antialiased">
    <main class="min-h-screen px-4 pt-10 pb-5 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1100px]">
            <header class="mb-6 flex items-center justify-between gap-4">
                <a href="{{ route('welcome') }}" aria-label="{{ __('messages.DalyTrac, accueil') }}" class="inline-flex items-baseline text-[1.65rem] font-extrabold tracking-[-0.065em] text-brand-primary no-underline lg:invisible"><x-application-logo /></a>
                <a href="{{ route('welcome') }}" class="inline-flex items-center justify-center rounded-full border border-[#dfe9e7] bg-white px-4 py-2 text-[0.78rem] font-medium text-[#0B2638] no-underline transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-[#2D7080] hover:text-[#2D7080]">{{ __('messages.auth_back_to_site') }} <i data-lucide="arrow-right" class="h-4 w-4 ml-2" aria-hidden="true"></i></a>
            </header>
            <div class="overflow-hidden rounded-[28px] border border-[#dfe9e7] bg-white shadow-[0_24px_70px_rgba(11,38,56,0.08)]">
                <div class="grid lg:grid-cols-[0.92fr_1.08fr]">
                    <section class="relative hidden overflow-hidden border-r border-[#e6efee] bg-[#f4f7f6] p-8 pt-20 lg:flex">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(45,112,128,0.18),_transparent_40%),radial-gradient(circle_at_bottom_right,_rgba(231,184,75,0.18),_transparent_32%)]"></div>
                        <div class="relative z-10 flex w-full flex-col justify-between">
                            <div class="pb-8">
                                <!-- <a href="{{ route('welcome') }}" class="inline-flex items-center no-underline" aria-label="DalyTrac accueil">
                                    <img src="{{ asset('img/dalytraclogo.png') }}" alt="DalyTrac" class="h-14 w-auto" />
                                </a> -->
                                <a href="/" aria-label="{{ __('messages.DalyTrac, accueil') }}" class="inline-flex items-baseline text-[1.65rem] font-extrabold tracking-[-0.065em] text-brand-primary no-underline max-sm:text-[1.42rem]"><x-application-logo /></a>
                                <h2 class="mt-7 text-[1.6rem] font-medium leading-[1.15] text-[#0B2638]">{{ __('messages.auth_side_title') }}</h2>
                            </div>

                            <div class="mt-8 space-y-4">
                                <div class="rounded-[20px] border border-[#dfe9e7] bg-white p-4 shadow-[0_12px_30px_rgba(11,38,56,0.05)]">
                                    <div class="flex items-center justify-between text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[#2D7080]">
                                        <span>{{ __('messages.auth_side_tracking') }}</span>
                                        <span class="inline-flex items-center gap-1 rounded-full bg-[#edf7f5] px-2 py-1 text-[#2D7080]">
                                            <i data-lucide="trending-up" class="h-3 w-3"></i>
                                            +18%
                                        </span>
                                    </div>
                                    <div class="mt-4 flex items-end gap-2">
                                        <span class="h-12 w-1/5 rounded-t-md bg-[#dfe9e7]"></span>
                                        <span class="h-16 w-1/5 rounded-t-md bg-[#2D7080]"></span>
                                        <span class="h-10 w-1/5 rounded-t-md bg-[#dfe9e7]"></span>
                                        <span class="h-20 w-1/5 rounded-t-md bg-[#E7B84B]"></span>
                                        <span class="h-14 w-1/5 rounded-t-md bg-[#dfe9e7]"></span>
                                    </div>
                                </div>

                                @include('auth.partials.feature-strip')
                            </div>
                        </div>
                    </section>
                    <section class="px-5 py-7 sm:px-8 lg:px-12 lg:pt-24 lg:pb-10">
                        <div class="mx-auto max-w-[420px]">
                            @yield('recovery-content')
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) window.lucide.createIcons();
        });
    </script>
</body>
</html>
