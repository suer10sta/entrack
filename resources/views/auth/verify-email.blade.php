<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="{{ __('messages.DalyTrac Verify Email - Confirm your account') }}">
    <meta property="og:title" content="{{ __('messages.Verify Your Email') }}">
    <meta property="og:description" content="{{ __('messages.DalyTrac Verify Email - Confirm your account') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('verification.notice') }}">
    <title>{{ __('messages.Verify Your Email') }} | DalyTrac</title>

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
        <div class="mx-auto max-w-[560px]">
            <header class="mb-6 flex items-center justify-center">
                <a href="/" aria-label="DalyTrac, home" class="inline-flex items-baseline text-[1.65rem] font-extrabold tracking-[-0.065em] text-brand-primary no-underline max-sm:text-[1.42rem]"><x-application-logo /></a>
            </header>

            <div class="overflow-hidden rounded-[28px] border border-[#dfe9e7] bg-white shadow-[0_24px_70px_rgba(11,38,56,0.08)]">
                <div class="px-5 py-7 sm:px-8 lg:px-10 lg:py-9">
                    <div class="mb-5 text-center">
                        <span class="inline-flex items-center gap-2 rounded-full border border-[#dfe9e7] bg-[#edf7f5] px-3 py-1.5 text-[0.7rem] font-bold uppercase tracking-[0.08em] text-[#2D7080]">
                            <i data-lucide="mail-check" class="h-3.5 w-3.5"></i>
                            {{ __('messages.Email Verification Required') }}
                        </span>
                    </div>

                    <div class="mb-7 text-center">
                        <h1 class="text-[2rem] font-bold leading-[1.1] tracking-[-0.05em] text-[#0B2638]">{{ __('messages.Verify Your Email') }}</h1>
                        <p class="mt-4 px-4 text-[15px] leading-6 text-[#5b6b73]">{!! __('messages.Thank you for registering! A confirmation email has just been sent to you.Please click the link in the email to activate your account.') !!}</p>
                    </div>

                    @if(session('status'))
                        <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700" role="alert">
                            <span class="inline-flex items-center gap-2">
                                <i data-lucide="circle-check" class="h-4 w-4 shrink-0"></i>
                                {{ __('messages.' . session('status')) }}
                            </span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                            <span class="inline-flex items-center gap-2">
                                <i data-lucide="circle-alert" class="h-4 w-4 shrink-0"></i>
                                {{ session('error') }}
                            </span>
                        </div>
                    @endif

                    <div class="mb-6 rounded-[20px] border border-[#dfe9e7] bg-[#f4f7f6] p-4">
                        <div class="flex items-start gap-3">
                            <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white text-[#2D7080] shadow-sm">
                                <i data-lucide="inbox" class="h-5 w-5"></i>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-[#0B2638]">{{ __('messages.You did not receive the email?') }}</p>
                                <p class="mt-1 text-[0.83rem] leading-5 text-[#5b6b73]">{{ __('messages.Check your spam folder or request a new verification email below.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <form method="POST" action="{{ route('verification.send') }}">
                            @csrf
                            <button type="submit" class="btn-sweep btn-sweep-navy group inline-flex min-h-[52px] w-full items-center justify-center gap-2.5 rounded-full border border-transparent bg-brand-secondary px-[22px] text-[0.95rem] font-semibold text-white no-underline shadow-[0_12px_28px_rgba(45,112,128,0.2)] transition-all duration-300 ease-out hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-[0_16px_34px_rgba(45,112,128,0.3)] cursor-pointer">
                                <i data-lucide="send" class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-0.5"></i>
                                <span>{{ __('messages.Resend verification email') }}</span>
                            </button>
                        </form>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="inline-flex min-h-[48px] w-full items-center justify-center gap-2 rounded-full border border-[#dfe9e7] bg-white px-[22px] text-[0.9rem] font-medium text-[#5b6b73] no-underline transition-all duration-300 ease-out hover:-translate-y-0.5 hover:border-[#2D7080] hover:text-[#2D7080] cursor-pointer">
                                <i data-lucide="log-out" class="h-4 w-4"></i>
                                <span>{{ __('messages.Logout') }}</span>
                            </button>
                        </form>
                    </div>
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
