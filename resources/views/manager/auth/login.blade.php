<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/dalytraclogo.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('img/dalytraclogo.png') }}" />
    <title>DalyTrac - Manager Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gradient-to-b from-sky-50 via-sky-100 to-slate-50 text-foreground font-sans antialiased">

    <main class="min-h-screen relative flex items-center justify-center px-4 py-10 overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-32 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-3xl">
            <div
                class="grid gap-8 rounded-2xl border-4 border-sky-200/60 bg-white/95 shadow-2xl ring-1 ring-border/40 backdrop-blur-md md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">

                <div class="relative hidden flex-col justify-between border-r border-sky-200 bg-gradient-to-br from-sky-200 via-sky-300 to-sky-200 px-8 py-8 text-slate-800 md:flex">
                    <div class="flex items-center justify-between mb-6">
                        <div class="inline-flex items-center gap-2">
                            <img
                                src="{{ asset('img/dalytraclogo.png') }}"
                                alt="DalyTrac logo"
                                class="h-20 w-auto object-contain"
                                
                            />
                        </div>
                        <span
                            class="inline-flex h-7 items-center rounded-full border border-sky-200 bg-white/70 px-3 text-[10px] text-sky-700">
                            Manager
                        </span>
                    </div>

                    <div class="mt-auto">
                        <p class="text-sm text-sky-900/80 leading-relaxed">
                            Manage subscriptions, invoices and entreprise accounts from a dedicated manager area,
                            without impacting normal users.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-8 md:px-8">
                    <div class="mb-6 text-center md:text-left">
                        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">
                            Manager sign in
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Sign in using your manager credentials.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 rounded-lg border border-red-100 bg-red-50 px-3 py-2 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <div>{{ $error }}</div>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('manager.login.submit') }}" class="space-y-4">
                        @csrf

                        <div class="space-y-1">
                            <label for="email" class="block text-sm font-medium text-slate-700">
                                Email
                            </label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none ring-primary/10 transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-2">
                        </div>

                        <div class="space-y-1">
                            <label for="password" class="block text-sm font-medium text-slate-700">
                                Password
                            </label>
                            <input id="password" type="password" name="password" required
                                class="block w-full rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-900 shadow-sm outline-none ring-primary/10 transition placeholder:text-slate-400 focus:border-primary focus:bg-white focus:ring-2">
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="inline-flex items-center gap-2">
                                <input id="remember" type="checkbox" name="remember"
                                    class="h-4 w-4 rounded border-slate-300 text-primary focus:ring-primary/40">
                                <span class="text-xs text-slate-600">Remember me</span>
                            </label>
                        </div>

                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-md shadow-primary/30 transition hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/50">
                            Sign in
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

</body>

</html>
