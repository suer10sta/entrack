<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/dalytraclogo.png') }}" />
    <link rel="icon" type="image/png" href="{{ asset('img/dalytraclogo.png') }}" />
    <title>DalyTrac - Login</title>

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
            <div class="grid gap-8 rounded-2xl border-4 border-sky-200/60 bg-white/95 shadow-2xl ring-1 ring-border/40 backdrop-blur-md md:grid-cols-[minmax(0,1.1fr)_minmax(0,1fr)]">
                <!-- Left / Visual design only -->
                <div class="relative hidden flex-col justify-between border-r border-sky-200 bg-gradient-to-br from-sky-200 via-sky-300 to-sky-200 px-8 py-8 text-slate-800 md:flex">
                    <div class="flex items-center justify-between mb-6">
                        <div class="inline-flex items-center gap-2">
                            <img
                                src="{{ asset('img/dalytraclogo.png') }}"
                                alt="DalyTrac logo"
                                class="h-24 w-auto object-contain"
                                style="transform: scale(1.8); transform-origin: left center; margin-left: -10px; margin-top: -10px;"
                                
                            />
                        </div>
                        <span class="inline-flex h-7 items-center rounded-full border border-sky-200 bg-white/70 px-3 text-[10px] text-sky-700">
                            Live
                            <span class="ml-1 inline-flex h-1.5 w-1.5 rounded-full bg-emerald-400 shadow-[0_0_6px_rgba(16,185,129,0.7)]"></span>
                        </span>
                    </div>

                    <!-- Metric cards row -->
                    <div class="grid grid-cols-2 gap-3 mb-4">
                        <div class="rounded-xl bg-sky-50 p-3 shadow-inner shadow-sky-100">
                            <div class="mb-2 flex items-center justify-between text-[10px] text-slate-500">
                                <span>Revenue</span>
                                <span class="inline-flex items-center gap-1 text-emerald-400">
                                    <i class="fa-solid fa-arrow-trend-up text-[9px]"></i>
                                    12%
                                </span>
                            </div>
                            <div class="h-14 w-full overflow-hidden rounded-md bg-sky-100">
                                <div class="flex h-full w-full items-end gap-1 px-1 pb-1">
                                    <span class="w-1.5 rounded-sm bg-emerald-500/70" style="height: 30%"></span>
                                    <span class="w-1.5 rounded-sm bg-emerald-500/80" style="height: 55%"></span>
                                    <span class="w-1.5 rounded-sm bg-emerald-400/90" style="height: 80%"></span>
                                    <span class="w-1.5 rounded-sm bg-emerald-500/80" style="height: 65%"></span>
                                    <span class="w-1.5 rounded-sm bg-emerald-600/70" style="height: 40%"></span>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-xl bg-sky-50 p-3 shadow-inner shadow-sky-100">
                            <div class="mb-2 flex items-center justify-between text-[10px] text-slate-500">
                                <span>Activity</span>
                                <span class="inline-flex items-center gap-1 text-sky-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-sky-400"></span>
                                    68%
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-[9px] text-slate-500">
                                <div class="flex -space-x-1.5">
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-sky-100 bg-white text-[9px]">AK</span>
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-sky-100 bg-white text-[9px]">MN</span>
                                    <span class="inline-flex h-5 w-5 items-center justify-center rounded-full border border-sky-100 bg-white text-[9px]">SJ</span>
                                </div>
                                <span class="rounded-full bg-sky-100 px-2 py-0.5 text-[9px] text-slate-600">
                                    3 teams
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Mini timeline / chart -->
                    <div class="mb-6 rounded-xl border border-sky-100 bg-sky-50 p-3">
                        <div class="mb-3 flex items-center justify-between text-[10px] text-slate-500">
                            <span class="inline-flex items-center gap-1">
                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                                Flow
                            </span>
                            <span class="rounded-full bg-white px-2 py-0.5 text-[9px] text-slate-600 shadow-sm">Today</span>
                        </div>
                        <div class="relative h-16 w-full overflow-hidden rounded-md bg-gradient-to-b from-sky-100 to-sky-50">
                            <div class="absolute inset-0 flex items-end justify-between px-2 pb-1">
                                <span class="h-6 w-[14%] rounded-md bg-primary/40"></span>
                                <span class="h-10 w-[14%] rounded-md bg-primary/60"></span>
                                <span class="h-8 w-[14%] rounded-md bg-sky-500/60"></span>
                                <span class="h-12 w-[14%] rounded-md bg-emerald-500/70"></span>
                                <span class="h-7 w-[14%] rounded-md bg-primary/50"></span>
                                <span class="h-4 w-[14%] rounded-md bg-slate-600/80"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Small footer strip with almost no text -->
                    <div class="flex items-center justify-between text-[9px] text-slate-500/90">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex h-5 w-8 items-center justify-center rounded-md bg-white text-[9px] text-slate-600 shadow-sm">
                                v2.1
                            </span>
                            <span class="h-px w-16 bg-sky-200"></span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="h-1 w-4 rounded-full bg-emerald-400/80"></span>
                            <span class="h-1 w-4 rounded-full bg-sky-500/70"></span>
                            <span class="h-1 w-4 rounded-full bg-amber-400/70"></span>
                        </div>
                    </div>
                </div>

                <!-- Right / Auth form side -->
                <div class="px-6 py-8 sm:px-8 md:px-10 md:py-10">
                    <div class="mb-6 text-center md:text-left">
                        <div class="mb-4 inline-flex items-center justify-center rounded-xl bg-primary/10 p-1 pr-3 text-xs font-medium text-primary">
                            <span class="mr-2 flex h-6 w-6 items-center justify-center rounded-lg bg-primary text-primary-foreground">
                                <i class="fa-solid fa-lock text-[11px]"></i>
                            </span>
                            Secure sign in
                        </div>
                        <h1 class="text-2xl font-semibold tracking-tight text-foreground sm:text-3xl">Welcome back</h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            Sign in to access your management dashboard and continue where you left off.
                        </p>
                    </div>

                    @if(session('error'))
                    <div class="mb-4 rounded-md border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-600" role="alert">
                        <span>{{ session('error') }}</span>
                    </div>
                    @endif

                    @if($errors->has('email'))
                    <div class="mb-4 rounded-md border border-red-500/40 bg-red-500/10 px-4 py-3 text-sm text-red-600" role="alert">
                        <span>{{ $errors->first('email') }}</span>
                    </div>
                    @endif
                    @if(session('message'))
                    <div class="mb-4 rounded-md border border-emerald-500/40 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-600" role="alert">
                        <span>{{ session('message') }}</span>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}" class="space-y-5">
                        @csrf

                        <div class="space-y-1">
                            <label for="email" class="block text-sm font-medium text-foreground">Email</label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground">
                                    <i class="fa-regular fa-envelope text-xs"></i>
                                </span>
                                <input type="email" name="email" id="email"
                                       class="w-full rounded-lg border border-border bg-background px-9 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground"
                                       placeholder="you@company.com" value="{{ old('email') }}" required />
                            </div>
                        </div>

                        <div class="space-y-1">
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium text-foreground">Password</label>
                                <a href="{{ route('password.request') }}" class="text-xs font-medium text-primary hover:text-primary/90 hover:underline">
                                    Forgot password?
                                </a>
                            </div>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-3 flex items-center text-muted-foreground">
                                    <i class="fa-solid fa-key text-xs"></i>
                                </span>
                                <input type="password" name="password" id="password"
                                       class="w-full rounded-lg border border-border bg-background px-9 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground"
                                       placeholder="••••••••" required />
                            </div>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <label class="inline-flex items-center gap-2 text-xs text-muted-foreground">
                                <input type="checkbox" name="remember" class="h-3.5 w-3.5 rounded border-border text-primary focus:ring-primary/40">
                                <span>Keep me signed in on this device</span>
                            </label>
                        </div>

                        <button type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-primary px-4 py-2.5 text-sm font-semibold text-primary-foreground shadow-sm shadow-primary/40 transition hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary/70 focus-visible:ring-offset-2 focus-visible:ring-offset-background">
                            <i class="fa-solid fa-arrow-right-to-bracket text-xs"></i>
                            <span>Sign in to dashboard</span>
                        </button>
                    </form>

                    <div class="mt-6 space-y-4 text-center text-xs text-muted-foreground">
                        <p>
                            Don’t have an account?
                            <a href="{{ route('register') }}" class="font-medium text-primary hover:text-primary/90 hover:underline">
                                Create a free account
                            </a>
                        </p>
                        
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    </body>

</html>
