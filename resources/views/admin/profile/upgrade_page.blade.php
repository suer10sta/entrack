<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Upgrade Your Plan Page Title') }}</title>
    @vite('resources/css/app.css')
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
        <div class="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">

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

        <div class="text-center mb-6 sm:mb-8">
            <h1 class="mt-1 text-2xl sm:text-[32px] font-bold tracking-tight text-[#0B2638]">
                {{ __('messages.Upgrade Your Plan') }}
            </h1>
            <p class="dt-section-subtitle mt-1.5 max-w-xl mx-auto">
                {{ __('messages.Upgrade Your Plan subtitle') }}
            </p>
        </div>

        <div class="max-w-4xl mx-auto mb-4 dt-card p-5 sm:p-6">
            <div class="flex items-center gap-3 mb-4">
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
            <div class="flex flex-col sm:flex-row gap-2.5">
                <div class="flex-1 min-w-0">
                    <label for="companyCountry" class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Country') }}</label>
                    <input type="text" id="companyCountry" name="country" value="{{ Auth::user()->entreprise?->country ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" placeholder="{{ __('messages.Enter your country') }}">
                </div>
                <div class="flex items-end flex-shrink-0">
                    <button type="button" id="validateCountryBtn" class="dt-btn dt-btn-teal min-h-[42px] w-full sm:w-auto">
                        {{ __('messages.Validate') }}
                    </button>
                </div>
            </div>
            <div id="countryValidationMsg" class="mt-2 text-[12.5px] hidden"></div>
        </div>

        {{-- Renewal preference + Stripe sync status in a single card --}}
        <div class="max-w-4xl mx-auto mb-6 sm:mb-8 dt-card p-5 sm:p-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.Renewal preference') }}</p>
                    <p class="dt-section-subtitle">{{ __('messages.Renewal preference description') }}</p>
                </div>
                @php
                    $currentRenewalPreference = optional(Auth::user()->entreprise)->auto_renew === false ? 'manual' : 'auto';
                @endphp
                <div class="inline-flex items-center self-start md:self-auto bg-slate-100 rounded-xl p-1 flex-shrink-0">
                    <button type="button"
                            class="renewal-option px-3.5 py-1.5 text-xs font-semibold rounded-lg whitespace-nowrap {{ $currentRenewalPreference === 'auto' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                            data-renewal-value="auto">
                        {{ __('messages.Auto renew') }}
                    </button>
                    <button type="button"
                            class="renewal-option px-3.5 py-1.5 text-xs font-semibold rounded-lg whitespace-nowrap {{ $currentRenewalPreference === 'manual' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                            data-renewal-value="manual">
                        {{ __('messages.Manual renew') }}
                    </button>
                </div>
            </div>
            <input type="hidden" id="renewalPreference" value="{{ $currentRenewalPreference }}">
            @php
                $renewalSync = $renewalSync ?? [
                    'state' => 'unknown',
                    'is_synced' => false,
                    'label' => __('messages.Renewal status unavailable'),
                ];
            @endphp
            <hr class="my-4 border-slate-200">
            <div id="stripeSyncStatus"
                 class="flex flex-wrap items-center gap-x-2.5 gap-y-1.5"
                 data-state="{{ $renewalSync['state'] ?? 'unknown' }}"
                 data-is-synced="{{ !empty($renewalSync['is_synced']) ? '1' : '0' }}"
                 data-label="{{ $renewalSync['label'] ?? __('messages.Renewal status unavailable') }}">
                <span id="stripeSyncBadge" class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ __('messages.Unknown') }}</span>
                <span class="text-xs text-slate-600">
                    <span id="stripeSyncText">{{ $renewalSync['label'] ?? __('messages.Renewal status unavailable') }}</span>
                </span>
            </div>
        </div>

        <div class="mb-6 sm:mb-8 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4 sm:gap-4">

            <div class="dt-card dt-card-hover p-5">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-3">
                    @if($trialDaysRemaining !== null && $trialDaysRemaining > 0)
                        {{ $trialDaysRemaining }} {{ __('messages.days left') }}
                    @elseif($trialDaysRemaining === null)
                        Essai en cours
                    @else
                        Trial Expiré
                    @endif
                </h3>
                <p class="dt-section-subtitle mt-1">
                    @if($trialDaysRemaining !== null && $trialDaysRemaining > 0)
                        {{ __('messages.0-Day Trial description') }}
                    @elseif($trialDaysRemaining === null)
                        Essai en cours
                    @else
                        Votre période d'essai est terminée
                    @endif
                </p>
            </div>

            <div class="dt-card dt-card-hover p-5">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c.139.132.28.255.424.372M9.75 3.104A2.25 2.25 0 0112 5.25v13.5A2.25 2.25 0 019.75 21v-5.714A2.25 2.25 0 0112 13.688v-5.714a2.25 2.25 0 012.25-2.162M14.25 3.104c.139.132.28.255.424.372M14.25 3.104A2.25 2.25 0 0012 5.25v13.5A2.25 2.25 0 0014.25 21v-5.714a2.25 2.25 0 00-2.25-2.612v-5.714a2.25 2.25 0 002.25-2.162M12 2.25c-.14.118-.277.243-.41.372M12 2.25c.14.118.277.243.41.372" /></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-3">{{ __('messages.Advanced Features') }}</h3>
                <p class="dt-section-subtitle mt-1">{{ __('messages.Advanced Features description') }}</p>
            </div>

            <div class="dt-card dt-card-hover p-5">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-2.144M9 13a4 4 0 100-8 4 4 0 000 8zm0 0v1.5a2.5 2.5 0 005 0V13m-5 0v0a5 5 0 00-5 0v0m5 0v0a5 5 0 015 0v0m-5 0v0a5 5 0 01-5 0v0" /></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-3">{{ __('messages.Team Collaboration') }}</h3>
                <p class="dt-section-subtitle mt-1">{{ __('messages.Team Collaboration description') }}</p>
            </div>

            <div class="dt-card dt-card-hover p-5">
                <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                </div>
                <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638] mt-3">{{ __('messages.Expert Support') }}</h3>
                <p class="dt-section-subtitle mt-1">{{ __('messages.Expert Support description') }}</p>
            </div>
        </div>
        {{-- Pricing Cards --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 sm:gap-6 items-stretch">

                    {{-- Solo Plan --}}
                    <div class="dt-card dt-card-hover p-6 sm:p-7 flex flex-col">
                        <div class="mb-4">
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
                        <div class="mb-4">
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
                        <div class="mb-4">
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
            </div>

                {{-- Trust & Guarantee Section --}}
        <div class="max-w-4xl mx-auto mb-4">
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

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const renewalInput = document.getElementById('renewalPreference');
                const planLinks = document.querySelectorAll('[data-plan-link]');
                const renewalButtons = document.querySelectorAll('.renewal-option');
                const saveEndpoint = '{{ route('subscription.renewal-preference.update') }}';
                const csrfToken = '{{ csrf_token() }}';

                function setActiveButton(value) {
                    renewalButtons.forEach(btn => {
                        btn.classList.remove('bg-white', 'text-gray-900', 'shadow-sm');
                        btn.classList.add('text-gray-600');
                    });

                    const activeButton = document.querySelector('.renewal-option[data-renewal-value="' + value + '"]');
                    if (activeButton) {
                        activeButton.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                        activeButton.classList.remove('text-gray-600');
                    }
                }

                async function savePreference(value) {
                    const response = await fetch(saveEndpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: JSON.stringify({ renewal: value }),
                    });

                    if (!response.ok) {
                        throw new Error('{{ __('messages.Unable to save renewal preference') }}');
                    }

                    return response.json();
                }

                function renderSyncStatus(sync) {
                    const statusRoot = document.getElementById('stripeSyncStatus');
                    const badge = document.getElementById('stripeSyncBadge');
                    const text = document.getElementById('stripeSyncText');
                    if (!statusRoot || !badge || !text || !sync) return;

                    const state = sync.state || 'unknown';
                    const isSynced = Boolean(sync.is_synced);
                    const label = sync.label || '{{ __('messages.Renewal status unavailable') }}';

                    badge.classList.remove('bg-green-100', 'text-green-700', 'bg-red-100', 'text-red-700', 'bg-gray-100', 'text-gray-700');
                    if (state === 'synced' && isSynced) {
                        badge.textContent = '{{ __('messages.Synced') }}';
                        badge.classList.add('bg-green-100', 'text-green-700');
                    } else if (state === 'out_of_sync') {
                        badge.textContent = '{{ __('messages.Not synced') }}';
                        badge.classList.add('bg-red-100', 'text-red-700');
                    } else {
                        badge.textContent = '{{ __('messages.Unknown') }}';
                        badge.classList.add('bg-gray-100', 'text-gray-700');
                    }

                    text.textContent = label;
                }

                function updateLinks() {
                    const renewal = renewalInput.value || 'auto';
                    // Carry the currently typed country into every plan link so
                    // the backend can save it (when missing) and start checkout.
                    const country = countryInput ? countryInput.value.trim() : '';
                    planLinks.forEach(link => {
                        const baseHref = link.getAttribute('data-base-href');
                        if (!baseHref) return;
                        try {
                            const url = new URL(baseHref, window.location.origin);
                            url.searchParams.set('renewal', renewal);
                            if (country) {
                                url.searchParams.set('country', country);
                            } else {
                                url.searchParams.delete('country');
                            }
                            link.href = url.toString();
                        } catch (e) {
                            const separator = baseHref.includes('?') ? '&' : '?';
                            link.href = baseHref + separator + 'renewal=' + encodeURIComponent(renewal);
                        }
                    });
                }

                renewalButtons.forEach(button => {
                    button.addEventListener('click', async function () {
                        const value = this.getAttribute('data-renewal-value') || 'auto';
                        const previousValue = renewalInput.value || 'auto';
                        if (value === previousValue) {
                            return;
                        }

                        renewalInput.value = value;
                        setActiveButton(value);
                        updateLinks();

                        try {
                            const result = await savePreference(value);
                            if (result && result.stripe_sync) {
                                renderSyncStatus(result.stripe_sync);
                            }
                        } catch (error) {
                            renewalInput.value = previousValue;
                            setActiveButton(previousValue);
                            updateLinks();
                            window.alert('{{ __('messages.Failed to save renewal preference') }}');
                        }
                    });
                });

                // Initialize links on first load
                setActiveButton(renewalInput.value || 'auto');
                updateLinks();
                // Exposed so the country field can refresh plan links as you type.
                window.dalytracUpdatePlanLinks = updateLinks;
                renderSyncStatus({
                    state: document.getElementById('stripeSyncStatus')?.dataset.state || 'unknown',
                    is_synced: (document.getElementById('stripeSyncStatus')?.dataset.isSynced || '0') === '1',
                    label: document.getElementById('stripeSyncStatus')?.dataset.label || '{{ __('messages.Renewal status unavailable') }}',
                });

                // Country validation before subscription
                const planLinks = document.querySelectorAll('[data-plan-link]');
                const countryInput = document.getElementById('companyCountry');
                const validateBtn = document.getElementById('validateCountryBtn');
                const validationMsg = document.getElementById('countryValidationMsg');

                function validateCountry() {
                    const country = countryInput.value.trim();
                    if (!country) {
                        validationMsg.textContent = '{{ __('messages.Please enter your country before subscribing.') }}';
                        validationMsg.className = 'mt-2 text-[12.5px] text-red-600';
                        validationMsg.classList.remove('hidden');
                        return false;
                    }
                    validationMsg.textContent = '{{ __('messages.Country validated! You can now subscribe.') }}';
                    validationMsg.className = 'mt-2 text-[12.5px] text-emerald-600';
                    validationMsg.classList.remove('hidden');
                    return true;
                }

                if (validateBtn) {
                    validateBtn.addEventListener('click', function () {
                        if (validateCountry()) {
                            // Country validated: refresh plan links so the typed
                            // value is carried to checkout (backend saves it).
                            updateLinks();
                        }
                    });
                }

                planLinks.forEach(link => {
                    link.addEventListener('click', function (e) {
                        updateLinks();
                        if (!countryInput.value.trim()) {
                            e.preventDefault();
                            validationMsg.textContent = '{{ __('messages.Please enter your country before subscribing.') }}';
                            validationMsg.className = 'mt-2 text-[12.5px] text-red-600';
                            validationMsg.classList.remove('hidden');
                            countryInput.focus();
                        }
                    });
                });
            });
        </script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const countryInput = document.getElementById('companyCountry');
                if (countryInput) {
                    countryInput.addEventListener('input', function () {
                        const msg = document.getElementById('countryValidationMsg');
                        if (msg && !msg.classList.contains('hidden')) {
                            msg.classList.add('hidden');
                        }
                        // Keep plan links in sync while typing so checkout
                        // always carries the current country value.
                        if (window.dalytracUpdatePlanLinks) {
                            window.dalytracUpdatePlanLinks();
                        }
                    });
                }
            });
        </script>
    </body>
</html>
