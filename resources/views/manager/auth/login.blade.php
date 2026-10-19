<!DOCTYPE html>
<html lang="en">

<head>
    <x-favicon />
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />


    <title>DalyTrac - Manager Login</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f8fafc] text-slate-900 antialiased">

    <main class="min-h-screen relative flex items-center justify-center px-4 py-10 overflow-hidden">
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-32 h-72 w-72 rounded-full bg-[#2D7080]/10 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-[#E7B84B]/15 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-3xl">
            <div class="grid overflow-hidden rounded-2xl bg-white shadow-xl ring-1 ring-slate-900/5 md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">

                <div class="relative hidden flex-col justify-between bg-gradient-to-br from-[#0B2638] via-[#0e2f42] to-[#14505f] px-8 py-8 text-white md:flex">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center gap-2.5">
                            <x-application-mark />
                            <x-application-logo style="background:#F4F7F6;border-radius:8px;padding:7px 10px;" class="inline-flex items-baseline text-[1.1rem] font-extrabold tracking-[-0.03em] text-white leading-none" />
                        </div>
                        <span class="inline-flex h-7 items-center rounded-full bg-[#E7B84B]/15 px-3 text-[10px] font-semibold uppercase tracking-[0.12em] text-[#E7B84B] ring-1 ring-[#E7B84B]/40">
                            Manager
                        </span>
                    </div>

                    <div class="mt-auto">
                        <p class="text-[22px] font-bold tracking-tight leading-snug">
                            Manage subscriptions, invoices and entreprise accounts.
                        </p>
                        <p class="mt-2 text-sm text-slate-300/90 leading-relaxed">
                            A dedicated manager area with high-level metrics and billing oversight, without impacting normal users.
                        </p>
                    </div>
                </div>

                <div class="px-6 py-8 md:px-8">
                    <div class="mb-6 text-center md:text-left">
                        <h1 class="text-2xl font-bold tracking-tight text-[#0B2638]">
                            Manager sign in
                        </h1>
                        <p class="mt-1 text-sm text-slate-500">
                            Sign in using your manager credentials.
                        </p>
                    </div>

                    @if ($errors->any())
                        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
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
                                class="block w-full rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                        </div>

                        <div class="space-y-1">
                            <label for="password" class="block text-sm font-medium text-slate-700">
                                Password
                            </label>
                            <input id="password" type="password" name="password" required
                                class="block w-full rounded-xl border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-800 shadow-sm outline-none transition placeholder:text-slate-400 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="inline-flex items-center gap-2 cursor-pointer">
                                <input id="remember" type="checkbox" name="remember"
                                    class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                <span class="text-xs text-slate-600">Remember me</span>
                            </label>
                        </div>

                        <button type="submit" class="dt-btn dt-btn-teal w-full py-2.5">
                            Sign in
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>

</body>

</html>
