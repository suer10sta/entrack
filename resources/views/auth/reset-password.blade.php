<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    @include('partials.password-visibility')
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="{{ __('messages.DalyTrac Reset Password - Secure your account') }}">
    <meta property="og:title" content="{{ __('messages.Create new password') }}">
    <meta property="og:description" content="{{ __('messages.Choose a strong password to secure your DalyTrac account.') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('password.reset', ['token' => $request->route('token')]) }}">
    <title>{{ __('messages.DalyTrac Reset Password - Secure your account') }} | DalyTrac</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-b from-sky-50 via-sky-100 to-slate-50 text-foreground font-sans antialiased">
    <main class="min-h-screen relative flex items-center justify-center px-4 py-10 overflow-hidden">
        <!-- Ambient background glow -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-32 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-5xl">
            <div
                class="grid gap-8 rounded-2xl bg-white/95 shadow-2xl ring-1 ring-border/40 backdrop-blur-md md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">

                <!-- Left / Visual security panel -->
                <div
                    class="relative hidden flex-col justify-between border-r border-sky-100 bg-gradient-to-br from-sky-50 via-sky-100 to-white px-8 py-8 text-slate-800 md:flex">
                    <div class="flex items-center justify-between mb-6">
                        <div class="inline-flex items-center gap-2">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary shadow-md shadow-primary/30">
                                <i class="fa-solid fa-shield-halved text-sm text-primary-foreground"></i>
                            </div>
                            <span
                                class="text-xs font-semibold tracking-[0.18em] uppercase text-sky-700">{{ __('messages.privacy_nav_security') }}</span>
                        </div>
                        <span
                            class="inline-flex h-7 items-center rounded-full border border-sky-200 bg-white/70 px-3 text-[10px] text-sky-700">
                            {{ __('messages.Protected') }}
                            <span class="ml-1 inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(16,185,129,0.7)]"></span>
                        </span>
                    </div>

                    <!-- Locks / status visuals -->
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        <div
                            class="rounded-xl border border-sky-100 bg-sky-50 p-3 shadow-inner shadow-sky-100 text-[10px] text-slate-600">
                            <div class="mb-2 flex items-center justify-between">
                                <span>{{ __('messages.Sessions') }}</span>
                                <span class="inline-flex items-center gap-1 text-emerald-500">
                                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                    OK
                                </span>
                            </div>
                            <div class="flex h-10 items-end justify-between gap-1">
                                <span class="w-1.5 rounded-sm bg-emerald-500/70" style="height: 40%"></span>
                                <span class="w-1.5 rounded-sm bg-emerald-500/80" style="height: 65%"></span>
                                <span class="w-1.5 rounded-sm bg-emerald-400/90" style="height: 85%"></span>
                                <span class="w-1.5 rounded-sm bg-emerald-500/80" style="height: 55%"></span>
                                <span class="w-1.5 rounded-sm bg-emerald-600/70" style="height: 35%"></span>
                            </div>
                        </div>
                        <div
                            class="rounded-xl border border-sky-100 bg-sky-50 p-3 shadow-inner shadow-sky-100 text-[10px] text-slate-600">
                            <div class="mb-2 flex items-center justify-between">
                                <span>{{ __('messages.Devices') }}</span>
                                <span class="inline-flex items-center gap-1 text-sky-500">
                                    <i class="fa-solid fa-mobile-screen text-[9px]"></i>
                                    3
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="inline-flex h-5 w-7 items-center justify-center rounded-md bg-white shadow-sm">2FA</span>
                                <span class="h-1.5 w-12 rounded-full bg-sky-500/70"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Password strength meter -->
                    <div class="mb-6 rounded-xl border border-sky-100 bg-sky-50 p-3 text-[10px] text-slate-600">
                        <div class="mb-2 flex items-center justify-between">
                            <span class="inline-flex items-center gap-1">
                                <i class="fa-solid fa-key text-[9px] text-amber-400"></i>
                                {{ __('messages.Password strength') }}
                            </span>
                            <span class="rounded-full bg-white px-2 py-0.5 text-[9px] text-slate-600 shadow-sm">{{ __('messages.Live') }}</span>
                        </div>
                        <div class="flex gap-1.5">
                            <span class="h-1.5 flex-1 rounded-full bg-slate-700"></span>
                            <span class="h-1.5 flex-1 rounded-full bg-amber-500/80"></span>
                            <span class="h-1.5 flex-1 rounded-full bg-emerald-500/80"></span>
                        </div>
                    </div>

                    <!-- Footer accent -->
                    <div class="flex items-center justify-between text-[9px] text-slate-500/90">
                        <span class="inline-flex h-5 items-center rounded-full bg-white px-2 shadow-sm">
                            {{ __('messages.Reset in progress') }}
                        </span>
                        <div class="flex items-center gap-1.5">
                            <span class="h-1 w-4 rounded-full bg-emerald-400/80"></span>
                            <span class="h-1 w-4 rounded-full bg-sky-500/70"></span>
                            <span class="h-1 w-4 rounded-full bg-amber-400/70"></span>
                        </div>
                    </div>
                </div>

                <!-- Right / Reset form -->
                <div class="px-6 py-8 sm:px-8 md:px-10 md:py-10">
                    <div class="mb-6 text-center md:text-left">
                        <div
                            class="mb-4 inline-flex items-center justify-center rounded-xl bg-primary/10 p-1 pr-3 text-xs font-medium text-primary">
                            <span
                                class="mr-2 flex h-6 w-6 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                <i class="fa-solid fa-lock text-[11px]"></i>
                            </span>
                            {{ __('messages.Reset password') }}
                        </div>
                        <h1 class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">Create new
                            password</h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ __('messages.Choose a strong password to secure your DalyTrac account.') }}
                        </p>
                    </div>

                    @if (session('status'))
                    <div class="mb-4 rounded-md border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600"
                        role="alert">
                        <span>{{ session('status') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('password.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                        <div class="space-y-1">
                            <label for="email" class="block text-sm font-medium text-foreground">{{ __('messages.Email') }}</label>
                            <input id="email" type="email" name="email"
                                value="{{ old('email', $request->email) }}"
                                required autofocus autocomplete="username"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground">
                            @error('email')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="password" class="block text-sm font-medium text-foreground">{{ __('messages.New password') }}</label>
                            <input id="password" type="password" name="password" required autocomplete="new-password"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground">
                            @error('password')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-1">
                            <label for="password_confirmation"
                                class="block text-sm font-medium text-foreground">{{ __('messages.Confirm password') }}</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" required
                                autocomplete="new-password"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground">
                            @error('password_confirmation')
                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full bg-primary text-primary-foreground py-2.5 px-4 rounded-md text-sm font-semibold shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            {{ __('messages.Reset password') }}
                        </button>

                        <p class="text-xs text-center text-muted-foreground">
                            {{ __('messages.Remember your password?') }}
                            <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Back to
                                sign in</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
