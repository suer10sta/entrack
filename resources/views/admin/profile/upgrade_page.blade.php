<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Upgrade Your Plan Page Title') }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <div class="container mx-auto max-w-6xl px-4 py-12 sm:px-6 lg:px-8">
        
        {{-- Flash Messages --}}
        @if(session('error'))
            <div class="mb-6 rounded-lg bg-red-50 border border-red-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if(session('success'))
            <div class="mb-6 rounded-lg bg-green-50 border border-green-200 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif
        
        <div class="text-center mb-10">
            <h1 class="text-4xl font-bold text-gray-900 sm:text-5xl">
                {{ __('messages.Upgrade Your Plan') }}
            </h1>
            <p class="mt-4 text-xl text-gray-600">
                {{ __('messages.Upgrade Your Plan subtitle') }}
            </p>
        </div>

        {{-- Renewal preference toggle --}}
        <div class="max-w-xl mx-auto mb-10">
            <div class="bg-white rounded-2xl shadow-md p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-sm font-medium text-gray-900">{{ __('messages.Renewal preference') }}</p>
                    <p class="text-xs text-gray-500">{{ __('messages.Renewal preference description') }}</p>
                </div>
                @php
                    $currentRenewalPreference = optional(Auth::user()->entreprise)->auto_renew === false ? 'manual' : 'auto';
                @endphp
                <div class="inline-flex items-center bg-gray-100 rounded-lg p-1">
                    <button type="button"
                            class="renewal-option px-3 py-1.5 text-xs font-medium rounded-md {{ $currentRenewalPreference === 'auto' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
                            data-renewal-value="auto">
                        {{ __('messages.Auto renew') }}
                    </button>
                    <button type="button"
                            class="renewal-option px-3 py-1.5 text-xs font-medium rounded-md {{ $currentRenewalPreference === 'manual' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-600' }}"
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
            <div id="stripeSyncStatus"
                 class="mt-3 inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2"
                 data-state="{{ $renewalSync['state'] ?? 'unknown' }}"
                 data-is-synced="{{ !empty($renewalSync['is_synced']) ? '1' : '0' }}"
                 data-label="{{ $renewalSync['label'] ?? __('messages.Renewal status unavailable') }}">
                <span id="stripeSyncBadge" class="rounded-full px-2 py-0.5 text-[11px] font-medium">{{ __('messages.Unknown') }}</span>
                <span class="text-xs text-gray-600">
                    <span id="stripeSyncText">{{ $renewalSync['label'] ?? __('messages.Renewal status unavailable') }}</span>
                </span>
            </div>
        </div>

        <div class="mb-12 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            
            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-purple-100 p-3 rounded-lg inline-block">
                    <svg class="h-6 w-6 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">{{ __('messages.0-Day Trial') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.0-Day Trial description') }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-blue-100 p-3 rounded-lg inline-block">
                    <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c.139.132.28.255.424.372M9.75 3.104A2.25 2.25 0 0112 5.25v13.5A2.25 2.25 0 019.75 21v-5.714A2.25 2.25 0 0112 13.688v-5.714a2.25 2.25 0 012.25-2.162M14.25 3.104c.139.132.28.255.424.372M14.25 3.104A2.25 2.25 0 0012 5.25v13.5A2.25 2.25 0 0014.25 21v-5.714a2.25 2.25 0 00-2.25-2.612v-5.714a2.25 2.25 0 002.25-2.162M12 2.25c-.14.118-.277.243-.41.372M12 2.25c.14.118.277.243.41.372" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">{{ __('messages.Advanced Features') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.Advanced Features description') }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-green-100 p-3 rounded-lg inline-block">
                    <svg class="h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-2.144M9 13a4 4 0 100-8 4 4 0 000 8zm0 0v1.5a2.5 2.5 0 005 0V13m-5 0v0a5 5 0 00-5 0v0m5 0v0a5 5 0 015 0v0m-5 0v0a5 5 0 01-5 0v0" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">{{ __('messages.Team Collaboration') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.Team Collaboration description') }}</p>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-6">
                <div class="flex-shrink-0 bg-yellow-100 p-3 rounded-lg inline-block">
                     <svg class="h-6 w-6 text-yellow-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" /></svg>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 mt-4">{{ __('messages.Expert Support') }}</h3>
                <p class="text-gray-600 mt-1">{{ __('messages.Expert Support description') }}</p>
            </div>
        </div>
        {{-- Pricing Cards --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">

                    {{-- Solo Plan --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 flex flex-col hover:shadow-xl transition-shadow duration-300">
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.Solo Plan label') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('messages.Solo Plan subtitle') }}</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-5xl font-extrabold text-gray-900">€19</span>
                            <span class="text-gray-500 font-medium"> {{ __('messages.per month') }}</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'solo']) }}"
                           data-base-href="{{ route('subscription.choose', ['plan' => 'solo']) }}"
                           data-plan-link
                           class="block w-full py-3 px-6 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg text-center transition-colors mb-8">
                            {{ __('messages.Choose Solo') }}
                        </a>
                        <ul class="space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>{{ __('messages.One User Maximum') }}</strong></li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Advanced Invoicing') }}</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Unlimited Clients') }}</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Advanced Reports') }}</li>
                        </ul>
                    </div>

                    <div class="bg-white rounded-2xl shadow-xl border-2 border-green-500 p-8 flex flex-col relative z-10">
                        <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                            <span class="bg-green-500 text-white text-xs font-bold uppercase tracking-wide py-1 px-3 rounded-full shadow-sm">{{ __('messages.Most Popular') }}</span>
                        </div>
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.Premium Plan label') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('messages.Premium Plan subtitle') }}</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-5xl font-extrabold text-gray-900">€49</span>
                            <span class="text-gray-500 font-medium"> {{ __('messages.per month') }}</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'premium']) }}"
                           data-base-href="{{ route('subscription.choose', ['plan' => 'premium']) }}"
                           data-plan-link
                           class="block w-full py-3 px-6 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg text-center transition-colors mb-8 shadow-md">
                            {{ __('messages.Choose Premium') }}
                        </a>
                        <ul class="space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>{{ __('messages.Up to 10 Users') }}</strong></li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Advanced Invoicing') }}</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Unlimited Clients') }}</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Advanced Reports') }}</li>
                        </ul>
                    </div>

                    {{-- Pro Plan --}}
                    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 flex flex-col hover:shadow-xl transition-shadow duration-300">
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">{{ __('messages.Pro Plan label') }}</h2>
                            <p class="text-gray-500 mt-2">{{ __('messages.Pro Plan subtitle') }}</p>
                        </div>
                        <div class="mb-6">
                            <span class="text-5xl font-extrabold text-gray-900">€99</span>
                            <span class="text-gray-500 font-medium"> {{ __('messages.per month') }}</span>
                        </div>
                        <a href="{{ route('subscription.choose', ['plan' => 'pro']) }}"
                           data-base-href="{{ route('subscription.choose', ['plan' => 'pro']) }}"
                           data-plan-link
                           class="block w-full py-3 px-6 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg text-center transition-colors mb-8">
                            {{ __('messages.Choose Pro') }}
                        </a>
                        <ul class="space-y-4 text-gray-600 flex-grow">
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> <strong>{{ __('messages.Unlimited Users') }}</strong></li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Advanced Invoicing') }}</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Unlimited Clients') }}</li>
                            <li class="flex items-start"><svg class="h-5 w-5 text-green-500 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg> {{ __('messages.Advanced Reports') }}</li>
                        </ul>
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
                    planLinks.forEach(link => {
                        const baseHref = link.getAttribute('data-base-href');
                        if (!baseHref) return;
                        const separator = baseHref.includes('?') ? '&' : '?';
                        link.href = baseHref + separator + 'renewal=' + encodeURIComponent(renewal);
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
                renderSyncStatus({
                    state: document.getElementById('stripeSyncStatus')?.dataset.state || 'unknown',
                    is_synced: (document.getElementById('stripeSyncStatus')?.dataset.isSynced || '0') === '1',
                    label: document.getElementById('stripeSyncStatus')?.dataset.label || '{{ __('messages.Renewal status unavailable') }}',
                });
            });
        </script>
    </body>
</html>
