<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.My Profile Page Title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Tailwind is required for the color classes --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- 
        =========================================
        LOGIC BLOCK: DEFINE PLAN CONFIGURATIONS
        =========================================
    --}}
    @php
        // IMPORTANT: $planKey is now passed from the controller
        // It will be: 'trial', 'solo', 'premium', or 'pro'
        
        // Define the look & feel for each plan
        $plans = [
            'trial' => [
                'label' => __('messages.Free Trial label'),
                'title' => __('messages.Free Trial title'),
                'subtitle' => __('messages.Free Trial subtitle'),
                'color_theme' => 'purple',
                'bg_theme' => 'purple',
                'button_color' => 'bg-blue-600 hover:bg-blue-700',
                'button_text' => __('messages.Choose a Plan'),
                'icon' => 'clock',
                'show_timer' => true,
                'features' => [
                    __('messages.Unlimited Invoices'),
                    __('messages.Unlimited Clients'),
                    __('messages.Unlimited Projects'),
                    __('messages.Advanced Reports'),
                    __('messages.Email Support'),
                    __('messages.No Credit Card Required'),
                    __('messages.Zero Day Trial')
                ]
            ],
            'solo' => [
                'label' => __('messages.Solo Plan label'),
                'title' => __('messages.Solo Plan title'),
                'subtitle' => __('messages.Solo Plan subtitle'),
                'color_theme' => 'blue',
                'bg_theme' => 'blue',
                'button_color' => 'bg-blue-600 hover:bg-blue-700',
                'button_text' => __('messages.Solo Plan button'),
                'icon' => 'user',
                'show_timer' => false,
                'features' => [
                    __('messages.One User Maximum'),
                    __('messages.Advanced Invoicing'),
                    __('messages.Unlimited Clients'),
                    __('messages.Advanced Reports')
                ]
            ],
            'premium' => [
                'label' => __('messages.Premium Plan label'),
                'title' => __('messages.Premium Plan title'),
                'subtitle' => __('messages.Premium Plan subtitle'),
                'color_theme' => 'emerald',
                'bg_theme' => 'emerald',
                'button_color' => 'bg-emerald-600 hover:bg-emerald-700',
                'button_text' => __('messages.Premium Plan button'),
                'icon' => 'crown',
                'show_timer' => false,
                'features' => [
                    __('messages.Up to 10 Users'),
                    __('messages.Advanced Invoicing'),
                    __('messages.Unlimited Clients'),
                    __('messages.Advanced Reports')
                ]
            ],
            'pro' => [
                'label' => __('messages.Pro Plan label'),
                'title' => __('messages.Pro Plan title'),
                'subtitle' => __('messages.Pro Plan subtitle'),
                'color_theme' => 'orange',
                'bg_theme' => 'green',
                'button_color' => 'bg-orange-600 hover:bg-orange-700',
                'button_text' => __('messages.Pro Plan button'),
                'icon' => 'crown',
                'show_timer' => false,
                'features' => [
                    __('messages.Unlimited Users'),
                    __('messages.Advanced Invoicing'),
                    __('messages.Unlimited Clients'),
                    __('messages.Advanced Reports')
                ]
            ]
        ];

        // Load the config for the current plan (passed from controller)
        $currentPlan = $plans[$planKey] ?? $plans['trial'];
    @endphp

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
        <div class="container mx-auto max-w-6xl p-4 md:p-8">

            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">{{ __('messages.My Profile') }}</h1>
                <p class="text-gray-600 mt-1">{{ __('messages.My Profile subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                {{-- LEFT COLUMN: User Profile Card --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-lg p-6 h-full">
                        
                        <div class="flex flex-col items-center">
                            {{-- Initials Logic --}}
                            @php
                                $nameParts = explode(' ', $user->name);
                                $initials = count($nameParts) > 1 
                                    ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                    : strtoupper(substr($user->name, 0, 2));
                            @endphp
                            <div class="w-24 h-24 rounded-full bg-blue-600 flex items-center justify-center shadow-md">
                                <span class="text-white text-3xl font-bold">{{$initials}}</span>
                            </div>
                            <h2 class="text-xl font-bold text-gray-900 mt-4">{{$user->name}}</h2>
                            <p class="text-sm text-gray-500">{{$user->email}}</p>
                        </div>

                        <hr class="my-6 border-gray-200">

                        <div class="space-y-4">
                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('messages.Account Type') }}</h3>
                                <div class="flex items-center mt-2">
                                    {{-- Dynamic Icon based on plan --}}
                                    @if($currentPlan['icon'] === 'crown')
                                        <svg class="w-5 h-5 mr-2 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25L7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                                        </svg>
                                    @elseif($currentPlan['icon'] === 'clock')
                                        <svg class="w-5 h-5 mr-2 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-5 h-5 mr-2 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    @endif
                                    
                                    <span class="text-{{ $currentPlan['color_theme'] }}-600 font-bold">
                                        {{ $currentPlan['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('messages.Member Since') }}</h3>
                                <p class="text-gray-800 font-medium mt-2">
                                    {{ $user->employee?->Date_Entree ? $user->employee->Date_Entree->format('F j, Y') : now()->format('F j, Y') }}
                                </p>
                            </div>

                            <div>
                                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider">{{ __('messages.Status') }}</h3>
                                <div class="flex items-center mt-2">
                                    @if($user->is_activate ?? true)
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                                        <span class="text-gray-800 font-medium">{{ __('messages.Active') }}</span>
                                    @else
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                        <span class="text-gray-800 font-medium">{{ __('messages.Inactive') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="mt-8 w-full bg-blue-600 text-white py-2 px-4 rounded-lg font-semibold hover:bg-blue-700 transition duration-300 block text-center">
                            {{ __('messages.Edit Profile') }}
                        </a>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Plan Details --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-lg p-6 h-full relative">
                        
                        {{-- Header Section --}}
                        <div class="flex justify-between items-start">
                            <div class="flex items-center mb-6">
                                {{-- Dynamic Icon Background Color --}}
                                <div class="flex-shrink-0 p-3 bg-{{ $currentPlan['color_theme'] }}-100 rounded-lg">
                                    
                                    {{-- Dynamic Icon SVG --}}
                                    @if($currentPlan['icon'] === 'crown')
                                        <svg class="w-8 h-8 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25L7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                                        </svg>
                                    @elseif($currentPlan['icon'] === 'clock')
                                        <svg class="w-8 h-8 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-8 h-8 text-{{ $currentPlan['color_theme'] }}-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <h2 class="text-xl font-bold text-gray-900">{{ $currentPlan['title'] }}</h2>
                                    <p class="text-gray-500">{{ $currentPlan['subtitle'] }}</p>
                                </div>
                            </div>

                            {{-- Days Left Timer (Only for Trial) - Now using $daysLeft from controller --}}
                            @if($currentPlan['show_timer'])
                                <div class="text-center hidden sm:block">
                                    <div class="text-3xl font-bold text-{{ $currentPlan['color_theme'] }}-600">
                                        {{ $daysLeft }}
                                    </div>
                                    <div class="text-xs text-gray-500 font-medium uppercase">{{ __('messages.days left') }}</div>
                                </div>
                            @endif
                        </div>

                        {{-- Features Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                            @foreach($currentPlan['features'] as $feature)
                                <div class="flex items-center p-4 bg-{{ $currentPlan['bg_theme'] }}-50 border border-{{ $currentPlan['bg_theme'] }}-100 rounded-lg">
                                    <svg class="w-5 h-5 text-{{ $currentPlan['color_theme'] }}-500 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="ml-3 text-gray-800 font-medium">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Plan Actions --}}
                        <div class="mt-8 space-y-3">
                            <a href="{{ route('profile.upgrade') }}" class="w-full block {{ $currentPlan['button_color'] }} text-white py-3 px-6 rounded-lg font-bold text-center transition duration-300 shadow-md">
                                {{ $currentPlan['button_text'] }}
                            </a>

                            @php
                                $entreprise = $user->entreprise;
                                $currentRenewalPreference = optional($entreprise)->auto_renew === false ? 'manual' : 'auto';
                                $profileRenewalSync = $renewalSync ?? [
                                    'state' => 'unknown',
                                    'is_synced' => false,
                                    'label' => __('messages.Renewal status unavailable'),
                                ];
                            @endphp

                            @if($entreprise && $entreprise->hasActiveSubscription())
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div>
                                            <p class="text-xs font-semibold text-gray-800 uppercase tracking-wider">
                                                {{ __('messages.Renewal preference') }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                {{ __('messages.Renewal preference description') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <form method="POST" action="{{ route('subscription.renewal-preference.update') }}">
                                                @csrf
                                                <input type="hidden" name="renewal" value="auto">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium rounded-md {{ $currentRenewalPreference === 'auto' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 border border-gray-200' }}">
                                                    {{ __('messages.Auto renew') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('subscription.renewal-preference.update') }}">
                                                @csrf
                                                <input type="hidden" name="renewal" value="manual">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-medium rounded-md {{ $currentRenewalPreference === 'manual' ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-gray-700 border border-gray-200' }}">
                                                    {{ __('messages.Manual renew') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="mt-2 inline-flex items-center gap-2 rounded-md border border-gray-200 bg-white px-3 py-2">
                                        @php
                                            $badgeState = $profileRenewalSync['state'] ?? 'unknown';
                                            $badgeIsSynced = !empty($profileRenewalSync['is_synced']);
                                        @endphp
                                        @php
                                            $badgeClasses = 'rounded-full px-2 py-0.5 text-[11px] font-medium ';
                                            if ($badgeState === 'synced' && $badgeIsSynced) {
                                                $badgeClasses .= 'bg-green-100 text-green-700';
                                                $badgeLabel = __('messages.Synced');
                                            } elseif ($badgeState === 'out_of_sync') {
                                                $badgeClasses .= 'bg-red-100 text-red-700';
                                                $badgeLabel = __('messages.Not synced');
                                            } else {
                                                $badgeClasses .= 'bg-gray-100 text-gray-700';
                                                $badgeLabel = __('messages.Unknown');
                                            }
                                        @endphp
                                        <span class="{{ $badgeClasses }}">
                                            {{ $badgeLabel }}
                                        </span>
                                        <span class="text-xs text-gray-600">
                                            {{ $profileRenewalSync['label'] ?? __('messages.Renewal status unavailable') }}
                                        </span>
                                    </div>
                                </div>
                            @endif

                            @if($entreprise && $entreprise->hasActiveSubscription())
                                <form method="POST"
                                      action="{{ route('subscription.cancel') }}"
                                      onsubmit="return confirm('{{ __('messages.Cancel subscription confirm') }}');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-50 border border-red-200 rounded-md font-semibold text-xs text-red-700 uppercase tracking-widest hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                        {{ __('messages.Cancel current subscription button') }}
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- Danger Zone: permanently delete entreprise --}}
            <div class="mt-10 max-w-3xl">
                <div class="bg-white border border-red-200 rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-bold text-red-600">{{ __('messages.Danger zone title') }}</h2>
                    <p class="mt-2 text-sm text-gray-600">
                        {{ __('messages.Danger zone description') }}
                    </p>
                    <button type="button"
                            onclick="window.showDeleteEnterpriseModal && window.showDeleteEnterpriseModal();"
                            class="mt-4 inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                        {{ __('messages.Delete enterprise button') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Enterprise Modal (two-step confirmation) --}}
    @php
        $enterpriseName = $user->entreprise?->name;
    @endphp
    <div id="delete-enterprise-modal"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4 p-6">
            <h3 class="text-lg font-semibold text-gray-900">{{ __('messages.Delete enterprise modal title') }}</h3>
            <p class="mt-2 text-sm text-gray-600">
                {{ __('messages.Delete enterprise modal intro prefix') }}
                @if($enterpriseName)
                    <span class="font-semibold text-gray-900">"{{ $enterpriseName }}"</span>
                @else
                    {{ __('messages.Delete enterprise modal intro account suffix') }}
                @endif
                {{ __('messages.Delete enterprise modal intro suffix') }}
            </p>
            @if($enterpriseName)
                <p class="mt-3 text-sm text-gray-600">
                    {{ __('messages.Delete enterprise modal type name') }}
                </p>
            @else
                <p class="mt-3 text-sm text-gray-600">
                    {{ __('messages.Delete enterprise modal type delete') }}
                </p>
            @endif

            <div class="mt-4">
                <input id="delete-enterprise-confirmation-input"
                       type="text"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                       placeholder="@if($enterpriseName) {{ $enterpriseName }} @else DELETE @endif">
            </div>

            <div class="mt-6 flex justify-end space-x-3">
                <button type="button"
                        onclick="window.hideDeleteEnterpriseModal && window.hideDeleteEnterpriseModal();"
                        class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-400">
                    {{ __('messages.Cancel') }}
                </button>
                <form id="delete-enterprise-form"
                      method="POST"
                      action="{{ route('subscription.cancel-and-delete') }}">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="confirmation_name" value="">
                    <button type="submit"
                            id="delete-enterprise-confirm-button"
                            disabled
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('messages.Delete enterprise modal confirm button') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const modal = document.getElementById('delete-enterprise-modal');
            const input = document.getElementById('delete-enterprise-confirmation-input');
            const confirmButton = document.getElementById('delete-enterprise-confirm-button');
            const form = document.getElementById('delete-enterprise-form');
            const hiddenField = form ? form.querySelector('input[name="confirmation_name"]') : null;
            const expected = @json($enterpriseName ?: 'DELETE');

            window.showDeleteEnterpriseModal = function () {
                if (!modal) return;
                modal.classList.remove('hidden');
                if (input) {
                    input.value = '';
                    input.focus();
                }
                if (confirmButton) {
                    confirmButton.disabled = true;
                }
            };

            window.hideDeleteEnterpriseModal = function () {
                if (!modal) return;
                modal.classList.add('hidden');
            };

            if (input && confirmButton) {
                input.addEventListener('input', function () {
                    const value = this.value.trim();
                    const isValid = value === String(expected);
                    confirmButton.disabled = !isValid;
                    if (hiddenField) {
                        hiddenField.value = value;
                    }
                });
            }

            // Close modal on ESC key
            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                    window.hideDeleteEnterpriseModal();
                }
            });
        })();
    </script>

</body>
</html>
