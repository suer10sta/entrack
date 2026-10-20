<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Upgrade Your Plan Page Title') }}</title>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css">
    <style>
        .upgrade-page .ts-control { border: 1px solid #cbd5e1; border-radius: .75rem; padding: .75rem; font-size: .875rem; min-height: 46px; box-shadow: none; }
        .upgrade-page .ts-wrapper.focus .ts-control { border-color: #2D7080; box-shadow: 0 0 0 3px rgb(45 112 128 / .15); }
        .upgrade-page .ts-dropdown { border-radius: .75rem; border-color: #cbd5e1; overflow: hidden; font-size: .875rem; }
        .upgrade-page .ts-dropdown .option { padding: .65rem .75rem; }
        .upgrade-page .ts-dropdown .active { background: #edf4f4; color: #0B2638; }
        .upgrade-page .ts-dropdown .selected { background: #2D7080; color: white; }
        .upgrade-page [data-plan-link]:focus-visible { outline: 3px solid #2D7080; outline-offset: 3px; }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <div class="upgrade-page mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">

        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-4 sm:mb-5 rounded-xl bg-red-50 border border-red-200 px-4 py-3">
                <div class="flex gap-2.5">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-4 sm:mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3">
                <div class="flex gap-2.5">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-emerald-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[13px] font-medium text-emerald-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="mb-8 border-b border-slate-200 pb-6 sm:mb-10">
            <h1 class="mt-1 text-2xl sm:text-[32px] font-bold tracking-tight text-[#0B2638]">
                {{ __('messages.Upgrade Your Plan') }}
            </h1>
            <p class="dt-section-subtitle mt-2 max-w-2xl">
                {{ __('messages.Upgrade Your Plan subtitle') }}
            </p>
        </div>

        <div class="mb-8 dt-card p-5 sm:p-6 lg:flex lg:items-center lg:justify-between lg:gap-8">
            <div class="flex items-center gap-3 mb-5 lg:mb-0 lg:max-w-md">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#E7B84B]/20 text-[#7a5a12] flex items-center justify-center">
                    <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21a4.622 4.622 0 01-1.341-8.753c0-1.051.26-1.784.608-2.487A4.997 4.997 0 018 6h8a4.997 4.997 0 013.733 1.76c.348.703.608 1.436.608 2.487A4.622 4.622 0 0121 21H3z" />
                    </svg>
                </div>
                <div class="min-w-0">
                    <h2 class="text-[15px] font-semibold tracking-tight text-[#0B2638]">{{ __('messages.Company Information') }}</h2>
                    <p class="dt-section-subtitle">{{ __('messages.Your company country is required before subscribing.') }}</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row gap-3 lg:w-96 lg:shrink-0">
                <div class="flex-1 min-w-0">
                    <label for="companyCountry" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Country') }}</label>
                    @php
                        $countriesList = json_decode(file_get_contents(base_path('countries.json')), true) ?? [];
                        $selectedCountry = Auth::user()->entreprise?->country ?? '';
                        $companyInfoRoutePrefix = collect([
                            'Admin' => 'admin',
                            'Responsable de projet' => 'Responsable_de_projet',
                            'Employee' => 'employee',
                            'SuperAdmin' => 'SuperAdmin',
                            'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire',
                        ])->first(fn ($prefix, $role) => Auth::user()->hasRole($role));
                        $companyInfoUpdateUrl = $companyInfoRoutePrefix
                            ? route($companyInfoRoutePrefix . '.Enterprise_infos.updateInfos')
                            : null;
                    @endphp
                    <select id="companyCountry" name="country" aria-describedby="countryValidationMsg" class="w-full rounded-xl border-slate-300 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]">
                        <option value="">{{ __('messages.Enter your country') }}</option>
                        @if($selectedCountry && !in_array($selectedCountry, array_column($countriesList, 'name'), true))
                            <option value="{{ $selectedCountry }}" selected>{{ $selectedCountry }}</option>
                        @endif
                        @foreach($countriesList as $country)
                            <option value="{{ $country['name'] }}" data-code="{{ $country['code'] }}" @selected($selectedCountry === $country['name'])>{{ $country['name'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-end flex-shrink-0">
                    <button type="button" id="validateCountryBtn" class="dt-btn dt-btn-teal min-h-[46px] w-full sm:w-auto">
                        {{ __('messages.Validate') }}
                    </button>
                </div>
            </div>
        </div>
        <div id="countryValidationMsg" role="status" aria-live="polite" class="-mt-6 mb-8 text-sm hidden"></div>

        {{-- Pricing Cards --}}
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-stretch mb-8">

                    {{-- Solo Plan --}}
                    <div class="dt-card dt-card-hover p-6 sm:p-7 flex flex-col">
                        <div class="mb-4 min-h-[68px]">
                            <h2 class="text-xl font-bold tracking-tight text-[#0B2638]">{{ __('messages.Solo Plan label') }}</h2>
                            <p class="dt-section-subtitle mt-1">{{ __('messages.Solo Plan subtitle') }}</p>
                        </div>
                        <div class="mb-5 sm:mb-6">
                            <span class="text-[40px] leading-none font-extrabold tracking-tight text-[#0B2638]">€19</span>
                            <span class="text-slate-500 text-sm font-medium"> {{ __('messages.per month') }}</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'solo', 'country' => Auth::user()->entreprise?->country]) }}"
                           data-base-href="{{ route('subscription.choose', ['plan' => 'solo', 'country' => Auth::user()->entreprise?->country]) }}"
                           data-plan-link
                           class="dt-btn dt-btn-primary w-full min-h-[46px] mb-5 sm:mb-6">
                            {{ __('messages.Choose Solo') }}
                        </a>
                        <ul class="space-y-3 text-[13.5px] text-slate-600 flex-grow">
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>1 utilisateur</strong></li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Facturation de base</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Jusqu'à 10 clients</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Rapports de base</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Support par email</li>
                        </ul>
                    </div>

                    <div class="dt-card dt-card-hover p-6 sm:p-7 flex flex-col relative border-2 !border-[#E7B84B]">
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="dt-badge dt-badge-accent !text-[11px] !font-bold uppercase tracking-wide shadow-sm"><span class="dt-dot"></span>{{ __('messages.Most Popular') }}</span>
                        </div>
                        <div class="mb-4 min-h-[68px]">
                            <h2 class="text-xl font-bold tracking-tight text-[#0B2638]">{{ __('messages.Premium Plan label') }}</h2>
                            <p class="dt-section-subtitle mt-1">{{ __('messages.Premium Plan subtitle') }}</p>
                        </div>
                        <div class="mb-5 sm:mb-6">
                            <span class="text-[40px] leading-none font-extrabold tracking-tight text-[#0B2638]">€49</span>
                            <span class="text-slate-500 text-sm font-medium"> {{ __('messages.per month') }}</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'premium', 'country' => Auth::user()?->entreprise?->country]) }}"
                           data-base-href="{{ route('subscription.choose', ['plan' => 'premium', 'country' => Auth::user()?->entreprise?->country]) }}"
                           data-plan-link
                           class="dt-btn dt-btn-accent w-full min-h-[46px] mb-5 sm:mb-6 shadow-sm">
                            {{ __('messages.Choose Premium') }}
                        </a>
                        <ul class="space-y-3 text-[13.5px] text-slate-600 flex-grow">
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>Jusqu'à 10 utilisateurs</strong></li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Facturation avancée</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Clients illimités</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Rapports avancés</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Collaboration d'équipe</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Support prioritaire</li>
                        </ul>
                    </div>

                    {{-- Pro Plan --}}
                    <div class="dt-card dt-card-hover p-6 sm:p-7 flex flex-col">
                        <div class="mb-4 min-h-[68px]">
                            <h2 class="text-xl font-bold tracking-tight text-[#0B2638]">{{ __('messages.Pro Plan label') }}</h2>
                            <p class="dt-section-subtitle mt-1">{{ __('messages.Pro Plan subtitle') }}</p>
                        </div>
                        <div class="mb-5 sm:mb-6">
                            <span class="text-[40px] leading-none font-extrabold tracking-tight text-[#0B2638]">€99</span>
                            <span class="text-slate-500 text-sm font-medium"> {{ __('messages.per month') }}</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'pro', 'country' => Auth::user()?->entreprise?->country]) }}"
                           data-base-href="{{ route('subscription.choose', ['plan' => 'pro', 'country' => Auth::user()?->entreprise?->country]) }}"
                           data-plan-link
                           class="dt-btn dt-btn-primary w-full min-h-[46px] mb-5 sm:mb-6">
                            {{ __('messages.Choose Pro') }}
                        </a>
                        <ul class="space-y-3 text-[13.5px] text-slate-600 flex-grow">
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>Utilisateurs illimités</strong></li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Facturation avancée + Personnalisation</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Clients illimités</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Rapports personnalisés</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Collaboration d'équipe avancée</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Support dédié</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Accès API</li>
                            <li class="flex items-start gap-2"><svg class="h-[18px] w-[18px] text-[#2D7080] mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> Intégrations sur mesure</li>
                        </ul>
                    </div>
                </div>

        <div class="mb-6 sm:mb-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 sm:gap-4">

            <div class="dt-card p-5 sm:p-6">
                <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-4">
                    @if($trialDaysRemaining !== null && $trialDaysRemaining > 0)
                        {{ $trialDaysRemaining }} {{ __('messages.days left') }}
                    @elseif($trialDaysRemaining === null)
                        Essai en cours
                    @else
                        Trial Expiré
                    @endif
                </h3>
                <p class="dt-section-subtitle mt-2 leading-relaxed">
                    @if($trialDaysRemaining !== null && $trialDaysRemaining > 0)
                        {{ __('messages.0-Day Trial description') }}
                    @elseif($trialDaysRemaining === null)
                        Essai en cours
                    @else
                        Votre période d'essai est terminée
                    @endif
                </p>
            </div>

            <div class="dt-card p-5 sm:p-6">
                <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M4 7h9m4 0h3M4 17h3m4 0h9"/><circle cx="15" cy="7" r="2"/><circle cx="9" cy="17" r="2"/></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-4">{{ __('messages.Advanced Features') }}</h3>
                <p class="dt-section-subtitle mt-2 leading-relaxed">{{ __('messages.Advanced Features description') }}</p>
            </div>

            <div class="dt-card p-5 sm:p-6">
                <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/><circle cx="9" cy="7" r="4"/></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-4">{{ __('messages.Team Collaboration') }}</h3>
                <p class="dt-section-subtitle mt-2 leading-relaxed">{{ __('messages.Team Collaboration description') }}</p>
            </div>

            <div class="dt-card p-5 sm:p-6">
                <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M3 14v-3a9 9 0 0 1 18 0v3m0 3v1a3 3 0 0 1-3 3h-3"/><rect x="2" y="11" width="4" height="7" rx="2"/><rect x="18" y="11" width="4" height="7" rx="2"/></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-4">{{ __('messages.Expert Support') }}</h3>
                <p class="dt-section-subtitle mt-2 leading-relaxed">{{ __('messages.Expert Support description') }}</p>
            </div>
        </div>

        {{-- Trust & Guarantee Section --}}
        <div class="mb-4">
            <div class="grid grid-cols-1 gap-4">
                {{-- Secure Payments --}}
                <div class="dt-card p-6 sm:p-7 text-center">
                    <div class="w-12 h-12 bg-[#2D7080]/10 rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2D7080" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                    </div>
                    <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mb-1">{{ __('messages.Secure Payments') }}</h3>
                    <p class="dt-section-subtitle max-w-md mx-auto">{{ __('messages.Your payment is processed securely through Stripe. We never store your card details on our servers.') }}</p>
                </div>
            </div>
        </div>
    </div>

        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const countryInput = document.getElementById('companyCountry');
                const validateBtn = document.getElementById('validateCountryBtn');
                const validationMsg = document.getElementById('countryValidationMsg');
                const planLinks = document.querySelectorAll('[data-plan-link]');
                let savedCountry = countryInput.value;
                let saving = null;

                // Supplement the shared company list with every ISO 3166-1 territory.
                // Existing names remain the values expected by company information.
                const codes = 'AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR BS BT BV BW BY BZ CA CC CD CF CG CH CI CK CL CM CN CO CR CU CV CW CX CY CZ DE DJ DK DM DO DZ EC EE EG EH ER ES ET FI FJ FK FM FO FR GA GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT GU GW GY HK HM HN HR HT HU ID IE IL IM IN IO IQ IR IS IT JE JM JO JP KE KG KH KI KM KN KP KR KW KY KZ LA LB LC LI LK LR LS LT LU LV LY MA MC MD ME MF MG MH MK ML MM MN MO MP MQ MR MS MT MU MV MW MX MY MZ NA NC NE NF NG NI NL NO NP NR NU NZ OM PA PE PF PG PH PK PL PM PN PR PS PT PW PY QA RE RO RS RU RW SA SB SC SD SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX SY SZ TC TD TF TG TH TJ TK TL TM TN TO TR TT TV TW TZ UA UG UM US UY UZ VA VC VE VG VI VN VU WF WS YE YT ZA ZM ZW'.split(' ');
                const existingCodes = new Set(Array.from(countryInput.options, option => option.dataset.code));
                const names = new Intl.DisplayNames(['fr'], { type: 'region' });
                codes.filter(code => !existingCodes.has(code)).forEach(code => {
                    const name = names.of(code);
                    if (!Array.from(countryInput.options).some(option => option.value === name)) {
                        countryInput.add(new Option(name, name));
                    }
                });
                const selector = typeof TomSelect === 'function' ? new TomSelect(countryInput, {
                    create: false,
                    maxOptions: null,
                    render: {
                        no_results() { const message = document.createElement('div'); message.className = 'no-results'; message.textContent = @json(__('messages.No results found')); return message; },
                    },
                    sortField: { field: 'text', direction: 'asc' },
                    onChange() { validationMsg.classList.add('hidden'); updateLinks(); },
                }) : null;

                function updateLinks() {
                    planLinks.forEach(link => {
                        const url = new URL(link.dataset.baseHref, window.location.origin);
                        if (countryInput.value) url.searchParams.set('country', countryInput.value);
                        else url.searchParams.delete('country');
                        link.href = url.toString();
                    });
                }
                function showMessage(message, error = false) {
                    validationMsg.textContent = message;
                    validationMsg.className = '-mt-6 mb-8 rounded-xl border px-4 py-3 text-sm font-medium ' + (error ? 'border-red-200 bg-red-50 text-red-700' : 'border-emerald-200 bg-emerald-50 text-emerald-800');
                }
                async function saveCountry() {
                    if (saving) return saving;
                    const country = countryInput.value;
                    if (!country) {
                        showMessage(@json(__('messages.Please enter your country before subscribing.')), true);
                        selector ? selector.focus() : countryInput.focus();
                        return false;
                    }
                    validateBtn.disabled = true;
                    selector ? selector.lock() : countryInput.disabled = true;
                    saving = (async () => {
                        try {
                            if (country !== savedCountry) {
                                const updateUrl = @json($companyInfoUpdateUrl);
                                if (!updateUrl) throw new Error('Company information update unavailable for this role');
                                const response = await fetch(updateUrl, {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @json(csrf_token()) },
                                    body: JSON.stringify({ id: @json(Auth::user()->entreprise_id), column: 'country', content: country }),
                                });
                                if (!response.ok) throw new Error('Country save failed');
                                const result = await response.json();
                                if (!result.success) throw new Error('Country save failed');
                                savedCountry = country;
                            }
                            updateLinks();
                            showMessage(@json(__('messages.upgrade_country_saved')));
                            return true;
                        } catch (error) {
                            showMessage(@json(__('messages.Failed to save changes. Please try again.')), true);
                            return false;
                        } finally {
                            validateBtn.disabled = false;
                            selector ? selector.unlock() : countryInput.disabled = false;
                            saving = null;
                        }
                    })();
                    return saving;
                }
                validateBtn.addEventListener('click', saveCountry);
                countryInput.addEventListener('change', () => { validationMsg.classList.add('hidden'); updateLinks(); });
                let checkingOut = false;
                planLinks.forEach(link => link.addEventListener('click', async event => {
                    event.preventDefault();
                    if (checkingOut) return;
                    checkingOut = true;
                    if (await saveCountry()) window.location.assign(link.href);
                    else checkingOut = false;
                }));
                updateLinks();
            });
        </script>
    </div>
</body>
</html>
