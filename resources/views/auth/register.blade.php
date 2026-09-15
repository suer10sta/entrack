<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/dalytraclogo.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('img/dalytraclogo.png') }}" />
    <title>DalyTrac - Create Account</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/42d5adcbca.js" crossorigin="anonymous"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .async-hide {
            opacity: 0 !important;
        }
    </style>
    <script>
        (function (a, s, y, n, c, h, i, d, e) {
            s.className += " " + y;
            h.start = 1 * new Date();
            h.end = i = function () {
                s.className = s.className.replace(RegExp(" ?" + y), "");
            };
            (a[n] = a[n] || []).hide = h;
            setTimeout(function () {
                i();
                h.end = null;
            }, c);
            h.timeout = c;
        })(window, document.documentElement, "async-hide", "dataLayer", 4000, {
            "GTM-K9BGS8K": true,
        });
    </script>
    <script>
        (function (i, s, o, g, r, a, m) {
            i["GoogleAnalyticsObject"] = r;
            (i[r] =
                i[r] ||
                function () {
                    (i[r].q = i[r].q || []).push(arguments);
                }),
                (i[r].l = 1 * new Date());
            (a = s.createElement(o)), (m = s.getElementsByTagName(o)[0]);
            a.async = 1;
            a.src = g;
            m.parentNode.insertBefore(a, m);
        })(
            window,
            document,
            "script",
            "https://www.google-analytics.com/analytics.js",
            "ga"
        );
        ga("create", "UA-46172202-22", "auto", {
            allowLinker: true,
        });
        ga("set", "anonymizeIp", true);
        ga("require", "GTM-K9BGS8K");
        ga("require", "displayfeatures");
        ga("require", "linker");
        ga("linker:autoLink", ["2checkout.com", "avangate.com"]);
    </script>
    <script>
        (function (w, d, s, l, i) {
            w[l] = w[l] || [];
            w[l].push({
                "gtm.start": new Date().getTime(),
                event: "gtm.js",
            });
            var f = d.getElementsByTagName(s)[0],
                j = d.createElement(s),
                dl = l != "dataLayer" ? "&l=" + l : "";
            j.async = true;
            j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
            f.parentNode.insertBefore(j, f);
        })(window, document, "script", "dataLayer", "GTM-NKDMSK6");
    </script>
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
</head>

<body class="bg-gradient-to-b from-sky-50 via-sky-100 to-slate-50 text-foreground font-sans antialiased">

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    <main class="min-h-screen relative flex items-center justify-center px-4 py-10 overflow-hidden">
        <!-- Ambient background glow -->
        <div class="pointer-events-none absolute inset-0">
            <div class="absolute -top-32 -left-32 h-72 w-72 rounded-full bg-sky-300/20 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-sky-400/20 blur-3xl"></div>
        </div>

        <div class="relative z-10 w-full max-w-5xl">
            <div
                class="grid gap-8 rounded-2xl border-4 border-sky-200/60 bg-white/95 shadow-2xl ring-1 ring-border/40 backdrop-blur-md md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">

                <!-- Left / Visual side (reuse login style) -->
                <div
                    class="relative hidden flex-col justify-between border-r border-sky-200 bg-gradient-to-br from-sky-200 via-sky-300 to-sky-200 px-8 py-8 text-slate-800 md:flex">
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
                            Setup
                            <span class="ml-1 inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(16,185,129,0.7)]"></span>
                        </span>
                    </div>

                    <!-- Onboarding steps cards -->
                    <div class="space-y-3 mb-5">
                        <div
                            class="flex items-center justify-between rounded-xl bg-sky-50 px-3 py-2.5 text-[11px] shadow-inner shadow-sky-100">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-emerald-500/10 text-emerald-500">
                                    1
                                </span>
                                <span class="text-slate-700">Clients</span>
                            </div>
                            <span class="h-1.5 w-12 rounded-full bg-emerald-400/70"></span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-xl bg-sky-50 px-3 py-2.5 text-[11px] shadow-inner shadow-sky-100">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-sky-500/10 text-sky-500">
                                    2
                                </span>
                                <span class="text-slate-700">Projects</span>
                            </div>
                            <span class="h-1.5 w-12 rounded-full bg-sky-500/70"></span>
                        </div>
                        <div
                            class="flex items-center justify-between rounded-xl bg-sky-50 px-3 py-2.5 text-[11px] shadow-inner shadow-sky-100">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex h-5 w-5 items-center justify-center rounded-full bg-amber-500/10 text-amber-500">
                                    3
                                </span>
                                <span class="text-slate-700">Employees</span>
                            </div>
                            <span class="h-1.5 w-12 rounded-full bg-slate-300/80"></span>
                        </div>
                    </div>

                    <!-- Mini stats -->
                    <div class="grid grid-cols-2 gap-3 mb-6 text-[10px] text-slate-500">
                        <div class="rounded-xl border border-sky-100 bg-sky-50 p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <span>Teams</span>
                                <span class="rounded-full bg-white px-2 py-0.5 text-[9px] text-emerald-500 shadow-sm">+3
                                    new</span>
                            </div>
                            <div class="flex -space-x-1.5">
                                <span
                                    class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-sky-100 bg-white text-[9px]">A</span>
                                <span
                                    class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-sky-100 bg-white text-[9px]">F</span>
                                <span
                                    class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-sky-100 bg-white text-[9px]">S</span>
                            </div>
                        </div>
                        <div class="rounded-xl border border-sky-100 bg-sky-50 p-3">
                            <div class="mb-2 flex items-center justify-between">
                                <span>Reports</span>
                                <span class="text-sky-400">Auto</span>
                            </div>
                            <div class="h-1.5 w-full rounded-full bg-sky-100">
                                <div class="h-1.5 w-1/2 rounded-full bg-sky-500"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer accents -->
                    <div class="flex items-center justify-between text-[9px] text-slate-500/90">
                        <span class="inline-flex h-5 items-center rounded-full bg-white px-2 shadow-sm">New workspace</span>
                        <div class="flex items-center gap-1.5">
                            <span class="h-1 w-4 rounded-full bg-emerald-400/80"></span>
                            <span class="h-1 w-4 rounded-full bg-sky-500/70"></span>
                            <span class="h-1 w-4 rounded-full bg-amber-400/70"></span>
                        </div>
                    </div>
                </div>

                <!-- Right / Register form -->
                <div class="px-6 py-8 sm:px-8 md:px-10 md:py-10">
                    <div class="mb-6 text-center md:text-left">
                        <div
                            class="mb-4 inline-flex items-center justify-center rounded-xl bg-primary/10 p-1 pr-3 text-xs font-medium text-primary">
                            <span
                                class="mr-2 flex h-6 w-6 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                <i class="fa-solid fa-user-plus text-[11px]"></i>
                            </span>
                            Create account
                        </div>
                        <h1 class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">Get started
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            It only takes a moment to set up your DalyTrac workspace.
                        </p>
                    </div>

                    @if(session('error'))
                    <div class="mb-4 rounded-md border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-600"
                        role="alert">
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif
                    @if(session('message'))
                    <div
                        class="mb-4 rounded-md border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600"
                        role="alert">
                        <span>{{ session('message') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data"
                        class="space-y-5">
                        @csrf

                        {{-- Honeypot Field --}}
                        <div style="display: none;">
                            <label for="website">Website</label>
                            <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                        </div>

                        @if($errors->has('cf-turnstile-response'))
                        <div class="mb-4 rounded-md border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-600" role="alert">
                            <span>{{ $errors->first('cf-turnstile-response') }}</span>
                        </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <label for="email" class="block text-sm font-medium text-foreground">Email <span
                                        class="text-red-500">*</span></label>
                                <input type="email" name="email" id="email"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground"
                                    placeholder="you@company.com" value="{{ old('email') }}" required />
                            </div>

                            <div class="space-y-1">
                                <label for="entreprise_name" class="block text-sm font-medium text-foreground">Company
                                    name <span class="text-red-500">*</span></label>
                                <input type="text" name="entreprise_name" id="entreprise_name"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground"
                                    placeholder="Company name" value="{{ old('entreprise_name') }}" required />
                            </div>

                            <div class="space-y-1">
                                <label for="password" class="block text-sm font-medium text-foreground">Password <span
                                        class="text-red-500">*</span></label>
                                <input type="password" name="password" id="password"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground"
                                    placeholder="••••••••" required />
                            </div>

                            <div class="space-y-1">
                                <label for="password_confirmation"
                                    class="block text-sm font-medium text-foreground">Repeat password <span
                                        class="text-red-500">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground"
                                    placeholder="••••••••" required />
                            </div>

                            <div class="md:col-span-2 space-y-1">
                                <label for="timezone" class="block text-sm font-medium text-foreground">Timezone <span
                                        class="text-red-500">*</span></label>
                                <select id="timezone" name="timezone"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40">
                                    @foreach (timezone_identifiers_list() as $timezone)
                                    <option value="{{ $timezone }}">{{ $timezone }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Turnstile Widget --}}
                        @if(config('services.turnstile.site_key'))
                            <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.site_key') }}"></div>
                        @endif

                        <button type="submit"
                            class="w-full bg-primary text-primary-foreground py-2.5 px-4 rounded-md text-sm font-semibold shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary">
                            Sign up
                        </button>

                        <p class="text-xs text-center text-muted-foreground">
                            Already have an account?
                            <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Sign in</a>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    </body>

</html>
