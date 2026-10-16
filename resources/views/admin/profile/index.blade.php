<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.My Profile Page Title') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

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

    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <div class="mx-auto w-full max-w-6xl p-4 sm:p-6 lg:p-8">

            <div class="mb-5 sm:mb-6">
                <h1 class="text-[22px] sm:text-2xl font-bold tracking-tight text-[#0B2638]">{{ __('messages.My Profile') }}</h1>
                <p class="dt-section-subtitle mt-1">{{ __('messages.My Profile subtitle') }}</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

                {{-- LEFT COLUMN: User Profile Card --}}
                <div class="lg:col-span-1">
                    <div class="dt-card p-5 sm:p-6 h-full">

                        <div class="flex flex-col items-center text-center">
                            {{-- Initials Logic --}}
                            @php
                                $nameParts = explode(' ', $user->name);
                                $initials = count($nameParts) > 1
                                            ? strtoupper(substr($nameParts[0], 0, 1) . substr(end($nameParts), 0, 1))
                                            : strtoupper(substr($user->name, 0, 2));
                            @endphp
                            <div class="w-20 h-20 rounded-2xl bg-[#2D7080] flex items-center justify-center flex-shrink-0">
                                <span class="text-white text-2xl font-bold">{{$initials}}</span>
                            </div>
                            <h2 class="text-[17px] font-bold tracking-tight text-[#0B2638] mt-3">{{$user->name}}</h2>
                            <p class="text-[12.5px] text-slate-500 break-all">{{$user->email}}</p>
                            @if($currentPlan['show_timer'])
                                <span class="dt-badge dt-badge-warning mt-2.5"><span class="dt-dot"></span>{{ $daysLeft }} {{ __('messages.days left') }}</span>
                            @else
                                <span class="dt-badge dt-badge-navy mt-2.5"><span class="dt-dot"></span>{{ $currentPlan['label'] }}</span>
                            @endif
                        </div>

                        <hr class="my-5 border-slate-200">

                        <div class="space-y-4">
                            <div>
                                <h3 class="dt-overline">{{ __('messages.Account Type') }}</h3>
                                <div class="flex items-center mt-1.5">
                                    {{-- Dynamic Icon based on plan --}}
                                    @if($currentPlan['icon'] === 'crown')
                                        <svg class="w-[18px] h-[18px] mr-2 text-[#2D7080]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25L7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                                        </svg>
                                    @elseif($currentPlan['icon'] === 'clock')
                                        <svg class="w-[18px] h-[18px] mr-2 text-[#2D7080]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-[18px] h-[18px] mr-2 text-[#2D7080]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    @endif

                                    <span class="text-[#0B2638] text-sm font-semibold">
                                        {{ $currentPlan['label'] }}
                                    </span>
                                </div>
                            </div>

                            <div>
                                <h3 class="dt-overline">{{ __('messages.Member Since') }}</h3>
                                <p class="text-slate-800 text-sm font-medium mt-1.5">
                                    {{ $user->employee?->Date_Entree ? $user->employee->Date_Entree->format('F j, Y') : now()->format('F j, Y') }}
                                </p>
                            </div>

                            <div>
                                <h3 class="dt-overline">{{ __('messages.Status') }}</h3>
                                <div class="flex items-center mt-1.5">
                                    @if($user->is_activate ?? true)
                                        <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2"></span>
                                        <span class="text-slate-800 text-sm font-medium">{{ __('messages.Active') }}</span>
                                    @else
                                        <span class="w-2 h-2 bg-red-500 rounded-full mr-2"></span>
                                        <span class="text-slate-800 text-sm font-medium">{{ __('messages.Inactive') }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <a href="{{ route('profile.edit') }}" class="dt-btn dt-btn-primary w-full min-h-[42px] mt-6">
                            {{ __('messages.Edit Profile') }}
                        </a>
                    </div>
                </div>

                {{-- RIGHT COLUMN: Plan Details --}}
                <div class="lg:col-span-2">
                    <div class="dt-card p-5 sm:p-6 h-full">

                        {{-- Header Section --}}
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-3">
                            <div class="flex items-center gap-3">
                                <div class="flex-shrink-0 w-11 h-11 rounded-xl bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center">
                                    {{-- Dynamic Icon SVG --}}
                                    @if($currentPlan['icon'] === 'crown')
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75V16.5L12 14.25L7.5 16.5V3.75m9 0H18A2.25 2.25 0 0120.25 6v12A2.25 2.25 0 0118 20.25H6A2.25 2.25 0 013.75 18V6A2.25 2.25 0 016 3.75h1.5m9 0h-9" />
                                        </svg>
                                    @elseif($currentPlan['icon'] === 'clock')
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                        </svg>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h2 class="text-[17px] font-bold tracking-tight text-[#0B2638]">{{ $currentPlan['title'] }}</h2>
                                    <p class="dt-section-subtitle">{{ $currentPlan['subtitle'] }}</p>
                                </div>
                            </div>

                            {{-- Days Left Timer (Only for Trial) - Now using $daysLeft from controller --}}
                            @if($currentPlan['show_timer'])
                                <div class="text-left sm:text-center flex-shrink-0">
                                    <div class="text-[26px] leading-none font-bold text-[#0B2638]">
                                        {{ $daysLeft }}
                                    </div>
                                    <div class="dt-overline mt-1">{{ __('messages.days left') }}</div>
                                </div>
                            @endif
                        </div>

                        {{-- Features Grid --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 mt-5">
                            @foreach($currentPlan['features'] as $feature)
                                <div class="flex items-center gap-2.5 rounded-xl border border-slate-200 bg-slate-50/60 px-3.5 py-3">
                                    <svg class="w-[18px] h-[18px] text-[#2D7080] flex-shrink-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                    </svg>
                                    <span class="text-[13px] font-medium text-slate-800">{{ $feature }}</span>
                                </div>
                            @endforeach
                        </div>

                        {{-- Plan Actions --}}
                        <div class="mt-5 space-y-3">
                            <a href="{{ route('profile.upgrade') }}" class="dt-btn dt-btn-primary w-full min-h-[46px]">
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
                                <div class="rounded-xl border border-slate-200 bg-slate-50/60 p-4 space-y-3">
                                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                        <div class="min-w-0">
                                            <p class="dt-overline">
                                                {{ __('messages.Renewal preference') }}
                                            </p>
                                            <p class="dt-section-subtitle mt-0.5">
                                                {{ __('messages.Renewal preference description') }}
                                            </p>
                                        </div>
                                        <div class="flex items-center gap-2 flex-shrink-0">
                                            <form method="POST" action="{{ route('subscription.renewal-preference.update') }}">
                                                @csrf
                                                <input type="hidden" name="renewal" value="auto">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $currentRenewalPreference === 'auto' ? 'bg-[#0B2638] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200' }}">
                                                    {{ __('messages.Auto renew') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('subscription.renewal-preference.update') }}">
                                                @csrf
                                                <input type="hidden" name="renewal" value="manual">
                                                <button type="submit"
                                                        class="px-3 py-1.5 text-xs font-semibold rounded-lg transition {{ $currentRenewalPreference === 'manual' ? 'bg-[#0B2638] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200' }}">
                                                    {{ __('messages.Manual renew') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    <div class="inline-flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2">
                                        @php
                                            $badgeState = $profileRenewalSync['state'] ?? 'unknown';
                                            $badgeIsSynced = !empty($profileRenewalSync['is_synced']);
                                        @endphp
                                        @php
                                            if ($badgeState === 'synced' && $badgeIsSynced) {
                                                $badgeClasses = 'dt-badge dt-badge-success';
                                                $badgeLabel = __('messages.Synced');
                                            } elseif ($badgeState === 'out_of_sync') {
                                                $badgeClasses = 'dt-badge dt-badge-danger';
                                                $badgeLabel = __('messages.Not synced');
                                            } else {
                                                $badgeClasses = 'dt-badge dt-badge-neutral';
                                                $badgeLabel = __('messages.Unknown');
                                            }
                                        @endphp
                                        <span class="{{ $badgeClasses }}">
                                            <span class="dt-dot"></span>
                                            {{ $badgeLabel }}
                                        </span>
                                        <span class="text-xs text-slate-600">
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
                                            class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-red-50 border border-red-200 rounded-xl font-semibold text-[13px] text-red-700 hover:bg-red-100 transition">
                                        {{ __('messages.Cancel current subscription button') }}
                                    </button>
                                </form>
                            @endif
                        </div>

                    </div>
                </div>
            </div>

            {{-- Danger Zone: permanently delete entreprise --}}
            <div class="mt-4 max-w-3xl">
                <div class="dt-card !border-red-200 p-5 sm:p-6">
                    <h2 class="text-[15px] font-semibold tracking-tight text-red-600">{{ __('messages.Danger zone title') }}</h2>
                    <p class="dt-section-subtitle mt-1">
                        {{ __('messages.Danger zone description') }}
                    </p>
                    <button type="button"
                            onclick="window.showDeleteEnterpriseModal && window.showDeleteEnterpriseModal();"
                            class="dt-btn mt-4 bg-red-600 text-white hover:bg-red-700">
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
         class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B2638]/50 p-4 hidden">
        <div class="dt-card w-full max-w-lg p-5 sm:p-6">
            <h3 class="text-[15px] font-semibold tracking-tight text-[#0B2638]">{{ __('messages.Delete enterprise modal title') }}</h3>
            <p class="mt-2 text-[13px] leading-5 text-slate-600">
                {{ __('messages.Delete enterprise modal intro prefix') }}
                @if($enterpriseName)
                    <span class="font-semibold text-[#0B2638]">"{{ $enterpriseName }}"</span>
                @else
                    {{ __('messages.Delete enterprise modal intro account suffix') }}
                @endif
                {{ __('messages.Delete enterprise modal intro suffix') }}
            </p>
            @if($enterpriseName)
                <p class="mt-3 text-[13px] text-slate-600">
                    {{ __('messages.Delete enterprise modal type name') }}
                </p>
            @else
                <p class="mt-3 text-[13px] text-slate-600">
                    {{ __('messages.Delete enterprise modal type delete') }}
                </p>
            @endif

            <div class="mt-4">
                <input id="delete-enterprise-confirmation-input"
                       type="text"
                       class="w-full rounded-lg border-slate-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                       placeholder="@if($enterpriseName) {{ $enterpriseName }} @else DELETE @endif">
            </div>

            <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                <button type="button"
                        onclick="window.hideDeleteEnterpriseModal && window.hideDeleteEnterpriseModal();"
                        class="dt-btn dt-btn-ghost sm:w-auto w-full">
                    {{ __('messages.Cancel') }}
                </button>
                <form id="delete-enterprise-form"
                      method="POST"
                      action="{{ route('subscription.cancel-and-delete') }}"
                      class="sm:w-auto w-full">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="confirmation_name" value="">
                    <button type="submit"
                            id="delete-enterprise-confirm-button"
                            disabled
                            class="dt-btn w-full bg-red-600 text-white hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed">
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
