<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta name="robots" content="noindex,follow">
    <x-favicon />
    @include('partials.password-visibility')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="{{ __('messages.DalyTrac Create Account - Start your free trial') }}">
    <meta property="og:title" content="{{ __('messages.Create account') }}">
    <meta property="og:description" content="{{ __('messages.It only takes a moment to set up your DalyTrac workspace.') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('register') }}">
    <title>{{ __('messages.DalyTrac Create Account - Start your free trial') }} | DalyTrac</title>


    {{-- TEMPORARILY DISABLED: Cloudflare Turnstile verification (restore to re-enable anti-bot check).
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    --}}

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script src="https://unpkg.com/lucide@1.47.0/dist/umd/lucide.min.js" defer></script>

    <style>
        .async-hide {
            opacity: 0 !important;
        }
        .signup-feature-strip {
            font-family: inherit;
            container-type: inline-size;
            border: 1px solid #dfe9e7;
            border-radius: 16px;
            background: rgb(255 255 255 / 85%);
            overflow: hidden;
        }
        .signup-feature-strip-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); padding-block: 14px; }
        .signup-feature-strip-item { display: grid; grid-template-columns: 14px minmax(0, 1fr); grid-template-rows: minmax(32px, auto) auto; align-content: start; min-width: 0; padding-inline: 14px; column-gap: 7px; row-gap: 5px; }
        .signup-feature-strip-item + .signup-feature-strip-item { border-left: 1px solid #dfe9e7; }
        .signup-feature-strip-title { grid-column: 2; grid-row: 1; color: #2D7080; font-weight: 600; letter-spacing: .045em; line-height: 1.45; overflow-wrap: break-word; }
        .signup-feature-strip-value { grid-column: 2; grid-row: 2; color: #0B2638; line-height: 1.5; overflow-wrap: break-word; }
        .signup-feature-strip-icon { display: block; grid-column: 1; grid-row: 1; width: 14px; height: 14px; margin-top: 1px; color: #2D7080; }
        @container (max-width: 320px) {
            .signup-feature-strip-grid { grid-template-columns: minmax(0, 1fr); padding-block: 0; }
            .signup-feature-strip-item { grid-template-columns: 14px minmax(0, 1fr) auto; grid-template-rows: auto; align-items: center; padding: 12px 14px; column-gap: 8px; row-gap: 0; }
            .signup-feature-strip-icon { margin-top: 0; }
            .signup-feature-strip-value { grid-column: 3; grid-row: 1; }
            .signup-feature-strip-item + .signup-feature-strip-item { border-left: 0; border-top: 1px solid #dfe9e7; }
        }
    </style>
</head>

<body class="overflow-x-hidden bg-[#f4f7f6]  text-[#0B2638] ">
    <main class="min-h-screen px-4 pt-10 pb-5 sm:px-6 lg:px-8">
        <div class="mx-auto max-w-[1100px]">
            <header class="mb-6 flex items-center justify-end gap-4">
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
                                <a href="/" aria-label="{{ __('messages.DalyTrac, accueil') }}" class="inline-flex items-baseline text-[1.65rem] font-extrabold tracking-[-0.065em] text-brand-primary no-underline max-sm:text-[1.42rem]"><x-application-logo /></a>
                                <h2 class="mt-7 text-[1.6rem] font-medium leading-[1.15] text-[#0B2638]">{{ __('messages.auth_side_title') }}</h2>
                            </div>

                            <div class="mt-8 space-y-4">
                               <div class="rounded-[20px] border border-[#dfe9e7] bg-white p-4 shadow-[0_12px_30px_rgba(11,38,56,0.05)]">
                                <div class="flex items-center justify-between text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[#2D7080]">
                                     <span>{{ __('messages.auth_register_card_time_label') }}</span>

                                    <span class="inline-flex items-center gap-1 rounded-full bg-[#edf7f5] px-2 py-1 text-[#2D7080]">
                                        <i data-lucide="trending-up" class="h-3 w-3"></i>
                                        {{ __('messages.auth_register_card_time_value') }}
                                    </span>
                                </div>

                                <div class="mt-4 flex h-[220px] items-end gap-2 sm:h-[250px]">
                                    <span class="h-[45%] w-1/5 rounded-t-md bg-[#dfe9e7]"></span>
                                    <span class="h-[30%] w-1/5 rounded-t-md bg-[#2D7080]"></span>
                                    <span class="h-[66%] w-1/5 rounded-t-md bg-[#dfe9e7]"></span>
                                    <span class="h-[60%] w-1/5 rounded-t-md bg-[#E7B84B]"></span>
                                    <span class="h-[42%] w-1/5 rounded-t-md bg-[#dfe9e7]"></span>
                                </div>
                            </div>

                                <div class="signup-feature-strip">
                                    <div class="signup-feature-strip-grid" role="list">
                                        @foreach([
                                            [__('messages.auth_register_card_projects_label'), __('messages.auth_register_card_projects_value'), 'M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7Z'],
                                            [__('messages.auth_register_card_planning_label'), __('messages.auth_register_card_planning_value'), 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Zm3 12 2 2 5-5'],
                                            [__('messages.auth_register_card_invoices_label'), __('messages.auth_register_card_invoices_value'), 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6ZM14 2v6h6M8 13h8M8 17h5'],
                                        ] as [$label, $value, $icon])
                                            <div class="signup-feature-strip-item" role="listitem">
                                                <svg class="signup-feature-strip-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}" /></svg>
                                                <span class="signup-feature-strip-title text-[0.7rem] font-bold uppercase tracking-[0.08em]">{{ $label }}</span>
                                                <span class="signup-feature-strip-value text-[13px] text-[#5b6b73]">{{ $value }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="px-5 py-7 sm:px-8 lg:px-12 lg:pt-24 lg:pb-10">
                        <div class="mx-auto max-w-[480px]">
                            <div class="mb-7 text-left">
                                <h1 class="text-[2rem] font-bold leading-[1.1] tracking-[-0.05em] text-[#0B2638]">{{ __('messages.Create account') }}</h1>
                                <p class="mt-2 text-[0.95rem] leading-6 text-[#5b6b73]">{{ __('messages.It only takes a moment to set up your DalyTrac workspace.') }}</p>
                            </div>

                            @if(session('error'))
                                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                    {{ session('error') }}
                                </div>
                            @endif

                            @php
                                $summaryErrors = collect($errors->all())->reject(fn ($error) =>
                                    (session('registration_email_exists') && $error === __('messages.auth_register_email_exists'))
                                    || $error === $errors->first('terms_accepted')
                                );
                            @endphp
                            @if($summaryErrors->isNotEmpty())
                                <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                    <ul class="m-0 list-disc space-y-1 pl-5">
                                        @foreach ($summaryErrors as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if(session('message'))
                                <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="alert">
                                    {{ session('message') }}
                                </div>
                            @endif

                            <form method="POST" action="{{ route('register') }}" class="space-y-5" novalidate enctype="multipart/form-data">
                                @csrf

                                <div style="display: none;">
                                    <label for="website">{{ __('messages.Website') }}</label>
                                    <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                                </div>

                                {{-- TEMPORARILY DISABLED: Cloudflare Turnstile verification (restore to re-enable anti-bot check).
                                @if($errors->has('cf-turnstile-response'))
                                    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                                        {{ $errors->first('cf-turnstile-response') }}
                                    </div>
                                @endif
                                --}}

                                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-1">
                                    <div class="space-y-1 sm:col-span-1">
                                        <label for="email" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Email') }} <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                                <i data-lucide="mail" class="h-4 w-4"></i>
                                            </span>
                                            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email" placeholder="{{ __('messages.auth_register_email_placeholder') }}" @if($errors->has('email')) aria-invalid="true" aria-describedby="register-email-error" @endif class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10 @error('email') border-red-300 bg-red-50 @enderror" />
                                        </div>
                                        @if(session('registration_email_exists') && $errors->has('email'))
                                            <div id="register-email-error" role="alert" class="rounded-xl border border-red-200 bg-red-50 px-3 py-2.5 text-xs leading-relaxed text-red-700">
                                                <p>{{ __('messages.auth_register_email_exists') }}</p>
                                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-2">
                                                    <a href="{{ route('login') }}" class="font-semibold underline underline-offset-2 hover:text-[#0B2638]">{{ __('messages.Sign in') }}</a>
                                                    <a href="{{ route('password.request') }}" class="font-semibold underline underline-offset-2 hover:text-[#0B2638]">{{ __('messages.Forgot password?') }}</a>
                                                </div>
                                            </div>
                                        @else
                                            @error('email')
                                                <p id="register-email-error" class="text-xs text-red-600">{{ $message }}</p>
                                            @enderror
                                        @endif
                                    </div>

                                    <div class="space-y-1 sm:col-span-1">
                                        <label for="full_name" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Full Name') }} <span class="text-red-500">*</span></label>
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                                <i data-lucide="user" class="h-4 w-4"></i>
                                            </span>
                                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name') }}" required autocomplete="name" placeholder="{{ __('messages.Full Name placeholder') }}" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10 @error('full_name') border-red-300 bg-red-50 @enderror" />
                                        </div>
                                        @error('full_name')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="mt-2 grid gap-4 sm:grid-cols-2">
                                        <div class="space-y-2 sm:col-span-1">
                                          <label for="password" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Password') }} <span class="text-red-500">*</span></label>
                                          <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                                <i data-lucide="lock-keyhole" class="h-4 w-4"></i>
                                            </span>
                                            <input type="password" name="password" id="password" required autocomplete="new-password" placeholder="••••••••" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10 @error('password') border-red-300 bg-red-50 @enderror" />
                                          </div>
                                          @error('password')
                                            <p class="text-xs text-red-600">{{ $message }}</p>
                                          @enderror
                                        </div>

                                        <div class="space-y-2 sm:col-span-1">
                                            <label for="password_confirmation" class="block text-sm font-medium text-[#0B2638]">{{ __('messages.Confirm password') }} <span class="text-red-500">*</span></label>
                                            <div class="relative">
                                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-[#5b6b73]">
                                                    <i data-lucide="shield-check" class="h-4 w-4"></i>
                                                </span>
                                                <input type="password" name="password_confirmation" id="password_confirmation" required autocomplete="new-password" placeholder="••••••••" class="w-full rounded-full border border-[#dfe9e7] bg-[#f7faf9] py-3 pl-10 pr-4 text-[0.95rem] text-[#0B2638] outline-none transition-all duration-300 placeholder:text-[#6a7c84] focus:border-[#2D7080] focus:bg-white focus:ring-4 focus:ring-[#2D7080]/10" />
                                            </div>
                                        </div>
                                    </div>

                                   

                                {{-- TEMPORARILY DISABLED: Cloudflare Turnstile verification (restore to re-enable anti-bot check).
                                @if(config('services.turnstile.site_key'))
                                    <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                                @endif
                                --}}

                                <div>
                                    <div class="flex items-start gap-2.5">
                                        <input type="checkbox" id="terms_accepted" name="terms_accepted" value="1" required @checked(old('terms_accepted')) @if($errors->has('terms_accepted')) aria-invalid="true" aria-describedby="register-consent-error" @endif class="mt-1 h-4 w-4 flex-shrink-0 rounded border-[#dfe9e7] text-[#2D7080] focus:ring-[#2D7080]/30">
                                        <label for="terms_accepted" class="text-xs leading-relaxed text-[#5b6b73]">
                                            {!! strtr(e(__('messages.auth_register_consent')), [
                                                ':terms' => '<a href="'.e(route('cgu')).'" target="_blank" rel="noopener" class="font-medium text-[#2D7080] underline underline-offset-2">'.e(__('messages.auth_register_terms_title')).'</a>',
                                                ':privacy' => '<a href="'.e(route('confidentialite')).'" target="_blank" rel="noopener" class="font-medium text-[#2D7080] underline underline-offset-2">'.e(__('messages.auth_register_privacy_title')).'</a>',
                                            ]) !!}
                                        </label>
                                    </div>
                                    @error('terms_accepted')
                                        <p id="register-consent-error" role="alert" class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <button type="submit" class="btn-sweep btn-sweep-navy group inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-full border border-transparent bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer">
                                    <span>{{ __('messages.Create a free account') }}</span>
                                    <i data-lucide="arrow-right" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5"></i>
                                </button>
                            </form>

                            <p class="mt-7 text-center text-sm text-[#5b6b73]">
                                {{ __('messages.Already have an account?') }}
                                <a href="{{ route('login') }}" class="font-medium text-[#2D7080] no-underline transition-colors duration-300 hover:text-[#0B2638] hover:underline">
                                    {{ __('messages.Sign in') }}
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
