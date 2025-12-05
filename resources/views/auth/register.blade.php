<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <link rel="apple-touch-icon" sizes="76x76" href="../../../assets/img/apple-icon.png" />
    <link rel="icon" type="image/png" href="../../../assets/img/favicon.png" />
    <title>Entreprise Tracker Pro - Create Account</title>

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

<body class="bg-background text-foreground font-sans antialiased">

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    <main class="min-h-screen flex items-center justify-center p-4">

        <div class="w-full max-w-2xl bg-card p-8 rounded-xl shadow-lg">

            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-primary mb-4 shadow-md">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-foreground mb-1">Create Your Account</h1>
                <p class="text-sm text-muted">Enter your details to register</p>
            </div>

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-md relative mb-4" role="alert">
                <span>{{ session('error') }}</span>
            </div>
            @endif
            @if(session('message'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-md relative mb-4" role="alert">
                <span>{{ session('message') }}</span>
            </div>
            @endif

            <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-foreground mb-1">Email <span class="text-red-500">*</span></label>
                        <input type="email" name="email" id="email" 
                               class="w-full px-3 py-2 border border-border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary placeholder:text-muted-foreground"
                               placeholder="*****@gmail.com" value="{{ old('email') }}" required />
                    </div>

                    <div>
                        <label for="entreprise_name" class="block text-sm font-medium text-foreground mb-1">Company Name <span class="text-red-500">*</span></label>
                        <input type="text" name="entreprise_name" id="entreprise_name" 
                               class="w-full px-3 py-2 border border-border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary placeholder:text-muted-foreground"
                               placeholder="Company Name" value="{{ old('entreprise_name') }}" required />
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-foreground mb-1">Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password" id="password" 
                               class="w-full px-3 py-2 border border-border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary placeholder:text-muted-foreground"
                               placeholder="******" required />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-foreground mb-1">Repeat Password <span class="text-red-500">*</span></label>
                        <input type="password" name="password_confirmation" id="password_confirmation" 
                               class="w-full px-3 py-2 border border-border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary placeholder:text-muted-foreground"
                               placeholder="******" required />
                    </div>

                    <div class="md:col-span-2">
                        <label for="timezone" class="block text-sm font-medium text-foreground mb-1">Timezone <span class="text-red-500">*</span></label>
                        <select id="timezone" name="timezone"
                                class="w-full px-3 py-2 border border-border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary">
                            @foreach (timezone_identifiers_list() as $timezone)
                                <option value="{{ $timezone }}">{{ $timezone }}</option>
                            @endforeach
                        </select>
                    </div>

                </div> <button type="submit"
                        class="w-full bg-primary text-primary-foreground py-2 px-4 rounded-md font-semibold shadow-sm hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary mt-6">
                    Sign Up
                </button>

                <p class="text-sm text-center text-muted mt-4">
                    Already registered?
                    <a href="{{ route('login') }}" class="font-medium text-primary hover:underline">Sign In</a>
                </p>

            </form>
        </div>
    </main>
    
    </body>

</html>
