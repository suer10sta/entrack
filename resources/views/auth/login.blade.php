<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="{{ __('messages.DalyTrac Login - Sign in to your management dashboard') }}">
    <meta property="og:title" content="{{ __('messages.Welcome back') }}">
    <meta property="og:description" content="{{ __('messages.Sign in to access your management dashboard and continue where you left off.') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('login') }}">
    <title>{{ __('messages.DalyTrac Login - Sign in to your management dashboard') }} | DalyTrac</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@1.47.0/dist/umd/lucide.min.js" defer></script>

    <style>
        .async-hide {
            opacity: 0 !important;
        }
    </style>
</head>

<body class="overflow-x-hidden bg-[#f4f7f6] font-sans text-[#0B2638] antialiased">
    <main class="min-h-screen px-4 pt-10 pb-5 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1100px]">
            <header class="mb-6 flex items-center justify-end gap-4">
                <!-- <a href="{{ route('welcome') }}" class="inline-flex items-center gap-3 no-underline" aria-label="DalyTrac accueil">
                    <img src="{{ asset('img/dalytraclogo.png') }}" alt="DalyTrac" class="h-10 w-auto" />
                </a> -->
                <a href="{{ route('welcome') }}" class="inline-flex items-center justify-center rounded-full border border-[#dfe9e7] bg-white px-4 py-2 text-[0.78rem] font-medium text-[#0B2638] no-underline transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-[#2D7080] hover:text-[#2D7080]">{{ __('messages.auth_back_to_site') }} <i data-lucide="arrow-right" class="h-4 w-4 ml-2"></i></a>
            </header>

            <div class="overflow-hidden rounded-[28px] border border-[#dfe9e7] bg-white shadow-[0_24px_70px_rgba(11,38,56,0.08)] ">
                <div class="grid lg:grid-cols-[0.92fr_1.08fr]">
                    <section class="relative hidden overflow-hidden border-r border-[#e6efee] bg-[#f4f7f6] p-8 pt-20 lg:flex">
                        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(45,112,128,0.18),_transparent_40%),radial-gradient(circle_at_bottom_right,_rgba(231,184,75,0.18),_transparent_32%)]"></div>
                        <div class="relative z-10 flex w-full flex-col justify-between">
                            <div class="pb-8">
                                <!-- <a href="{{ route('welcome') }}" class="inline-flex items-center no-underline" aria-label="DalyTrac accueil">
                                    <img src="{{ asset('img/dalytraclogo.png') }}" alt="DalyTrac" class="h-14 w-auto" />
                                </a> -->
                                <a href="#top" aria-label="DalyTrac, accueil" class="inline-flex items-baseline text-[2.2rem] font-extrabold tracking-[-0.065em] text-brand-primary no-underline max-sm:text-[1.42rem]"><span>Daly</span><strong class="font-extrabold text-brand-secondary">Trac</strong></a>
                                <h2 class="mt-7 text-[2.3rem] font-semibold leading-[1.08] text-[#0B2638]">{{ __('messages.auth_side_title') }}</h2>
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

                                <div class="grid gap-3 sm:grid-cols-3 lg:grid-cols-1 xl:grid-cols-3">
                                    <div class="rounded-[18px] flex flex-col justify-center items-center border border-[#dfe9e7] bg-white/85 p-3">
                                        <div class="flex items-center gap-2 text-[#2D7080]">
                                            <span class="text-[0.7rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.auth_side_projects') }}</span>
                                        </div>
                                        <p class="mt-2 text-[1.25rem] font-bold text-[#0B2638]">24</p>
                                    </div>
                                    <div class="rounded-[18px] flex flex-col justify-center items-center border border-[#dfe9e7] bg-white/85 p-3">
                                        <div class="flex items-center gap-2 text-[#2D7080]">
                                            <span class="text-[0.7rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.auth_side_time') }}</span>
                                        </div>
                                        <p class="mt-2 text-[1.25rem] font-bold text-[#0B2638]">1 240h</p>
                                    </div>
                                    <div class="rounded-[18px] flex flex-col justify-center items-center border border-[#dfe9e7] bg-white/85 p-3">
                                        <div class="flex items-center gap-2 text-[#2D7080]">
                                            <span class="text-[0.7rem] font-bold uppercase tracking-[0.08em]">{{ __('messages.auth_side_invoices') }}</span>
                                        </div>
                                        <p class="mt-2 text-[1.25rem] font-bold text-[#0B2638]">€48k</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="px-5 py-7 sm:px-8 lg:px-12 lg:pt-24 lg:pb-10">
                        <div class="mx-auto max-w-[420px]">
                           
                            <div class="mb-7 text-left">
                                <h1 class="text-[2rem] font-bold leading-[1.1] tracking-[-0.05em] text-[#0B2638]">{{ __('messages.Login') }}</h1>
                                <p class="mt-2 text-[0.95rem] leading-6 text-[#5b6b73]">{{ __('messages.Welcome back') }}. {{ __('messages.Sign in to dashboard') }}.</p>
                            </div>

                            @if(session('error'))
                                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @if($errors->has('email'))
                                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                    {{ $errors->first('email') }}
                                </div>
                            @endif

                            @if(session('message'))
                                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="alert">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}" class="space-y-5" novalidate>
                                @csrf

                                <div style="display: none;">
                                    <label for="website">Website</label>
                                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                                </div>

                                @if($errors->has('cf-turnstile-response'))
                                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                        {{ $errors->first('cf-turnstile-response') }}
                                    </div>
                                @endif

                                <div class="space-y-2">
                                    <label for="email" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Email') }}</label>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                            <i data-lucide="mail" class="h-4 w-4"></i>
                                        </span>
                                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@company.com" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10" />
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-center justify-between gap-3">
                                        <label for="password" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Password') }}</label>
                                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-[#2D7080] no-underline transition-colors duration-300 hover:text-[#0B2638] hover:underline">
                                            {{ __('messages.Forgot password?') }}
                                        </a>
                                    </div>
                                    <div class="relative">
                                        <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                            <i data-lucide="lock-keyhole" class="h-4 w-4"></i>
                                        </span>
                                        <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="••••••••" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10" />
                                    </div>
                                </div>

                                <label class="inline-flex items-center gap-2 text-sm text-[#5b6b73]">
                                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#dfe9e7] text-[#2D7080] focus:ring-[#2D7080]" />
                                    {{ __('messages.Keep me signed in on this device') }}
                                </label>

                                @if(config('services.turnstile.site_key'))
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                                @endif

                                <button type="submit" class="btn-sweep btn-sweep-navy group inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-full border border-transparent bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer">
                                    <span>{{ __('messages.Sign in to dashboard') }}</span>
                                    <i data-lucide="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5"></i>
                                </button>
                            </form>

                            <p class="mt-7 text-center text-sm text-[#5b6b73]">
                                {{ __("messages.Don't have an account?") }}
                                <a href="{{ route('register') }}" class="font-medium text-[#2D7080] no-underline transition-colors duration-300 hover:text-[#0B2638] hover:underline">
                                    {{ __('messages.Create a free account') }}
                                </a>
                            </p>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });
    </script>
</body>

</html>

