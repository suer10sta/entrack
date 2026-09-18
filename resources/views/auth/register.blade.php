<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="{{ __('messages.DalyTrac Create Account - Start your free trial') }}">
    <meta property="og:title" content="{{ __('messages.Create account') }}">
    <meta property="og:description" content="{{ __('messages.It only takes a moment to set up your DalyTrac workspace.') }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('register') }}">
    <title>{{ __('messages.DalyTrac Create Account - Start your free trial') }} | DalyTrac</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Figtree:wght@400;500;600;700&display=swap" rel="stylesheet">
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
                    @if(session('status'))
                    <div
                        class="mb-4 rounded-md border border-sky-500/40 bg-sky-500/10 px-4 py-3 text-sm text-sky-600"
                        role="alert">
                        <span>{{ session('status') }}</span>
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
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground @error('email') border-red-500 @enderror"
                                    placeholder="you@company.com" value="{{ old('email') }}" required />
                                @error('email')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label for="entreprise_name" class="block text-sm font-medium text-foreground">Company
                                    name <span class="text-red-500">*</span></label>
                                <input type="text" name="entreprise_name" id="entreprise_name"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground @error('entreprise_name') border-red-500 @enderror"
                                    placeholder="Company name" value="{{ old('entreprise_name') }}" required />
                                @error('entreprise_name')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="space-y-1">
                                <label for="password" class="block text-sm font-medium text-foreground">Password <span
                                        class="text-red-500">*</span></label>
                                <input type="password" name="password" id="password"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 placeholder:text-muted-foreground @error('password') border-red-500 @enderror"
                                    placeholder="••••••••" required />
                                @error('password')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
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
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 @error('timezone') border-red-500 @enderror">
                                    @foreach (timezone_identifiers_list() as $timezone)
                                    <option value="{{ $timezone }}" {{ old('timezone') == $timezone ? 'selected' : '' }}>{{ $timezone }}</option>
                                    @endforeach
                                </select>
                                @error('timezone')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2 space-y-1">
                                <label for="country" class="block text-sm font-medium text-foreground">{{ __('messages.Country') }} <span
                                        class="text-red-500">*</span></label>
                                <select id="country" name="country"
                                    class="w-full rounded-lg border border-border bg-background px-3 py-2.5 text-sm shadow-sm outline-none ring-0 transition focus:border-primary focus:ring-2 focus:ring-primary/40 @error('country') border-red-500 @enderror" required>
                                    <option value="">{{ __('messages.Select your country') }}</option>
                                    @php
                                        $countries = [
                                            'AF' => 'Afghanistan', 'AL' => 'Albania', 'DZ' => 'Algeria', 'AS' => 'American Samoa', 'AD' => 'Andorra',
                                            'AO' => 'Angola', 'AI' => 'Anguilla', 'AQ' => 'Antarctica', 'AG' => 'Antigua and Barbuda', 'AR' => 'Argentina',
                                            'AM' => 'Armenia', 'AW' => 'Aruba', 'AU' => 'Australia', 'AT' => 'Austria', 'AZ' => 'Azerbaijan',
                                            'BS' => 'Bahamas', 'BH' => 'Bahrain', 'BD' => 'Bangladesh', 'BB' => 'Barbados', 'BY' => 'Belarus',
                                            'BE' => 'Belgium', 'BZ' => 'Belize', 'BJ' => 'Benin', 'BM' => 'Bermuda', 'BT' => 'Bhutan',
                                            'BO' => 'Bolivia', 'BA' => 'Bosnia and Herzegovina', 'BW' => 'Botswana', 'BV' => 'Bouvet Island', 'BR' => 'Brazil',
                                            'IO' => 'British Indian Ocean Territory', 'BN' => 'Brunei Darussalam', 'BG' => 'Bulgaria', 'BF' => 'Burkina Faso',
                                            'BI' => 'Burundi', 'KH' => 'Cambodia', 'CM' => 'Cameroon', 'CA' => 'Canada', 'CV' => 'Cape Verde',
                                            'KY' => 'Cayman Islands', 'CF' => 'Central African Republic', 'TD' => 'Chad', 'CL' => 'Chile', 'CN' => 'China',
                                            'CX' => 'Christmas Island', 'CC' => 'Cocos (Keeling) Islands', 'CO' => 'Colombia', 'KM' => 'Comoros',
                                            'CG' => 'Congo', 'CD' => 'Congo, Democratic Republic of the', 'CK' => 'Cook Islands', 'CR' => 'Costa Rica',
                                            'HR' => 'Croatia', 'CU' => 'Cuba', 'CY' => 'Cyprus', 'CZ' => 'Czech Republic', 'DK' => 'Denmark',
                                            'DJ' => 'Djibouti', 'DM' => 'Dominica', 'DO' => 'Dominican Republic', 'EC' => 'Ecuador', 'EG' => 'Egypt',
                                            'SV' => 'El Salvador', 'GQ' => 'Equatorial Guinea', 'ER' => 'Eritrea', 'EE' => 'Estonia', 'ET' => 'Ethiopia',
                                            'FK' => 'Falkland Islands (Malvinas)', 'FO' => 'Faroe Islands', 'FJ' => 'Fiji', 'FI' => 'Finland', 'FR' => 'France',
                                            'GF' => 'French Guiana', 'PF' => 'French Polynesia', 'TF' => 'French Southern Territories', 'GA' => 'Gabon',
                                            'GM' => 'Gambia', 'GE' => 'Georgia', 'DE' => 'Germany', 'GH' => 'Ghana', 'GI' => 'Gibraltar',
                                            'GR' => 'Greece', 'GL' => 'Greenland', 'GD' => 'Grenada', 'GP' => 'Guadeloupe', 'GU' => 'Guam',
                                            'GT' => 'Guatemala', 'GG' => 'Guernsey', 'GN' => 'Guinea', 'GW' => 'Guinea-Bissau', 'GY' => 'Guyana',
                                            'HT' => 'Haiti', 'HM' => 'Heard Island and Mcdonald Islands', 'VA' => 'Holy See (Vatican City State)',
                                            'HN' => 'Honduras', 'HK' => 'Hong Kong', 'HU' => 'Hungary', 'IS' => 'Iceland', 'IN' => 'India',
                                            'ID' => 'Indonesia', 'IR' => 'Iran, Islamic Republic of', 'IQ' => 'Iraq', 'IE' => 'Ireland', 'IM' => 'Isle of Man',
                                            'IL' => 'Israel', 'IT' => 'Italy', 'JM' => 'Jamaica', 'JP' => 'Japan', 'JE' => 'Jersey', 'JO' => 'Jordan',
                                            'KZ' => 'Kazakhstan', 'KE' => 'Kenya', 'KI' => 'Kiribati', 'KP' => 'Korea, Democratic People\'s Republic of',
                                            'KR' => 'Korea, Republic of', 'KW' => 'Kuwait', 'KG' => 'Kyrgyzstan', 'LA' => 'Lao People\'s Democratic Republic',
                                            'LV' => 'Latvia', 'LB' => 'Lebanon', 'LS' => 'Lesotho', 'LR' => 'Liberia', 'LY' => 'Libyan Arab Jamahiriya',
                                            'LI' => 'Liechtenstein', 'LT' => 'Lithuania', 'LU' => 'Luxembourg', 'MO' => 'Macau', 'MK' => 'Macedonia',
                                            'MG' => 'Madagascar', 'MW' => 'Malawi', 'MY' => 'Malaysia', 'MV' => 'Maldives', 'ML' => 'Mali',
                                            'MT' => 'Malta', 'MH' => 'Marshall Islands', 'MQ' => 'Martinique', 'MR' => 'Mauritania', 'MU' => 'Mauritius',
                                            'YT' => 'Mayotte', 'MX' => 'Mexico', 'FM' => 'Micronesia, Federated States of', 'MD' => 'Moldova, Republic of',
                                            'MC' => 'Monaco', 'MN' => 'Mongolia', 'ME' => 'Montenegro', 'MS' => 'Montserrat', 'MA' => 'Morocco',
                                            'MZ' => 'Mozambique', 'MM' => 'Myanmar', 'NA' => 'Namibia', 'NR' => 'Nauru', 'NP' => 'Nepal',
                                            'NL' => 'Netherlands', 'AN' => 'Netherlands Antilles', 'NC' => 'New Caledonia', 'NZ' => 'New Zealand',
                                            'NI' => 'Nicaragua', 'NE' => 'Niger', 'NG' => 'Nigeria', 'NU' => 'Niue', 'NF' => 'Norfolk Island',
                                            'MP' => 'Northern Mariana Islands', 'NO' => 'Norway', 'OM' => 'Oman', 'PK' => 'Pakistan', 'PW' => 'Palau',
                                            'PS' => 'Palestinian Territory, Occupied', 'PA' => 'Panama', 'PG' => 'Papua New Guinea', 'PY' => 'Paraguay',
                                            'PE' => 'Peru', 'PH' => 'Philippines', 'PN' => 'Pitcairn', 'PL' => 'Poland', 'PT' => 'Portugal',
                                            'PR' => 'Puerto Rico', 'QA' => 'Qatar', 'RE' => 'Reunion', 'RO' => 'Romania', 'RU' => 'Russian Federation',
                                            'RW' => 'Rwanda', 'BL' => 'Saint Barthelemy', 'SH' => 'Saint Helena', 'KN' => 'Saint Kitts and Nevis',
                                            'LC' => 'Saint Lucia', 'MF' => 'Saint Martin', 'PM' => 'Saint Pierre and Miquelon', 'VC' => 'Saint Vincent and the Grenadines',
                                            'WS' => 'Samoa', 'SM' => 'San Marino', 'ST' => 'Sao Tome and Principe', 'SA' => 'Saudi Arabia', 'SN' => 'Senegal',
                                            'RS' => 'Serbia', 'SC' => 'Seychelles', 'SL' => 'Sierra Leone', 'SG' => 'Singapore', 'SK' => 'Slovakia',
                                            'SI' => 'Slovenia', 'SB' => 'Solomon Islands', 'SO' => 'Somalia', 'ZA' => 'South Africa', 'GS' => 'South Georgia and the South Sandwich Islands',
                                            'ES' => 'Spain', 'LK' => 'Sri Lanka', 'SD' => 'Sudan', 'SR' => 'Suriname', 'SJ' => 'Svalbard and Jan Mayen',
                                            'SZ' => 'Swaziland', 'SE' => 'Sweden', 'CH' => 'Switzerland', 'SY' => 'Syrian Arab Republic', 'TW' => 'Taiwan, Province of China',
                                            'TJ' => 'Tajikistan', 'TZ' => 'Tanzania, United Republic of', 'TH' => 'Thailand', 'TL' => 'Timor-Leste', 'TG' => 'Togo',
                                            'TK' => 'Tokelau', 'TO' => 'Tonga', 'TT' => 'Trinidad and Tobago', 'TN' => 'Tunisia', 'TR' => 'Turkey',
                                            'TM' => 'Turkmenistan', 'TC' => 'Turks and Caicos Islands', 'TV' => 'Tuvalu', 'UG' => 'Uganda', 'UA' => 'Ukraine',
                                            'AE' => 'United Arab Emirates', 'GB' => 'United Kingdom', 'US' => 'United States', 'UM' => 'United States Minor Outlying Islands',
                                            'UY' => 'Uruguay', 'UZ' => 'Uzbekistan', 'VU' => 'Vanuatu', 'VE' => 'Venezuela', 'VN' => 'Viet Nam',
                                            'VG' => 'Virgin Islands, British', 'VI' => 'Virgin Islands, U.S.', 'WF' => 'Wallis and Futuna', 'EH' => 'Western Sahara',
                                            'YE' => 'Yemen', 'ZM' => 'Zambia', 'ZW' => 'Zimbabwe',
                                        ];
                                    @endphp
                                    @foreach ($countries as $code => $name)
                                    <option value="{{ $name }}" {{ old('country') == $name ? 'selected' : '' }}>{{ $name }}</option>
                                    @endforeach
                                </select>
                                @error('country')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
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
