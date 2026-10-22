<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.internal_expenses') }} - DalyTrac</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Use the new styling system --}}
    @vite('resources/css/app.css')
    @livewireStyles

    <style>
        .enterprise-billing-page label {
            color: #475569;
            font-size: 12px;
            font-weight: 600;
        }

        .enterprise-billing-page input:not([type="checkbox"]) {
            border-color: #e2e8f0;
            border-radius: 8px;
            background-color: #f8fafc;
            color: #1e293b;
            font-size: 13px;
        }

        .enterprise-billing-page input:not([type="checkbox"]):focus {
            border-color: #2D7080;
            background-color: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(45, 112, 128, .12);
        }

        .enterprise-billing-page input[type="checkbox"] { accent-color: #2D7080; }

        .enterprise-billing-page .billing-inline-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 6px;
        }

        /* Entreprise expenses: collapse budget cards on mobile/tablet */
        @media (max-width: 1024px) {
            .enterprise-card[data-budget-history-collapsible] [data-collapsible-header] {
                cursor: pointer;
                user-select: none;
            }

            .enterprise-card[data-budget-history-collapsible].is-collapsed [data-collapsible-body] {
                display: none !important;
            }

            .enterprise-card .enterprise-chevron {
                transition: transform 180ms ease;
            }

            .enterprise-card[data-budget-history-collapsible][aria-expanded="true"] .enterprise-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
</head>

<body class="enterprise-billing-page dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- <noscript>...iframe...</noscript> --}}

    {{-- Layout powered by Blade components --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto w-full">
        <x-dashboard.navbar />

        {{-- Main content area using Tailwind styling --}}
        <main class="flex-1 min-w-0 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">
            <div class="w-full max-w-7xl mx-auto">
                @php
                    $billingFlashKeys = [
                        'Budget added successfully' => 'Budget added successfully',
                        'Please add a budget first' => 'Please add a budget first',
                        'Invalid name or price' => 'Invalid expense name or price',
                        'Budget remaining is not enough to validate this charge' => 'Insufficient remaining budget',
                        'Charges created successfully' => 'Expenses created successfully',
                        'Mismatch between names and prices' => 'Expense name and price mismatch',
                    ];
                    $billingFlash = fn ($message) => isset($billingFlashKeys[$message])
                        ? __('messages.' . $billingFlashKeys[$message])
                        : $message;
                @endphp

                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                    <div>
                        <p class="dt-overline mb-0.5">{{ __('messages.internal_expenses') }}</p>
                        <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.internal_expenses') }}</h1>
                        <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Expenses subtitle') }}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-3 mb-4">
                    
                    <div class="lg:col-span-1 dt-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Current Budget') }}</h2>
                        </div>
                        <div class="p-4 sm:p-5 space-y-4">
                            @if($latestBudget)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.Cashbox label') }}</label>
                                    <input id="budget-input-{{ $latestBudget->id }}"
                                           type="number" name="monthBudget"
                                           value="{{ $latestBudget->budget }}" readonly
                                           ondblclick="enableEditing(this, {{ $latestBudget->id }})"
                                           class="w-full px-3 py-2 border cursor-pointer focus:bg-white focus:cursor-text">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">{{ __('messages.Remaining Balance') }}</label>
                                    <input id="sold-input" type="number" name="sold"
                                           value="{{$latestBudget->budget_remaining}}" readonly
                                           class="w-full px-3 py-2 border cursor-not-allowed text-slate-500">
                                </div>
                            @else
                                <p class="text-sm text-gray-500">{{ __('messages.No budget defined') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-3">
                        @php
                            $reportPrefix = 'admin';
                            if (Auth::user()->hasRole('Responsable de projet')) $reportPrefix = 'Responsable_de_projet';
                            elseif (Auth::user()->hasRole('Employee')) $reportPrefix = 'employee';
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportPrefix = 'Responsable_gestionnaire';
                        @endphp
                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100">
                                <h3 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Export Report') }}</h3>
                            </div>
                            <div class="p-4 sm:p-5">
                            <form action="{{ route($reportPrefix.'.entrepriseBillings.downloadRepports') }}" method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
                                <div class="flex-1">
                                    <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Start Date') }}:</label>
                                    <input id="start-date" type="date" name="startDate" required
                                           class="w-full px-3 py-2 border">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.End Date') }}:</label>
                                    <input id="end-date" type="date" name="endDate" required
                                           class="w-full px-3 py-2 border">
                                </div>
                                <button type="submit" id="downloadLink"
                                   class="dt-btn dt-btn-primary justify-center flex-shrink-0 w-full md:w-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19z"/></svg>
                                    {{ __('messages.Print PDF') }}
                                </button>
                            </form>
                            </div>
                        </div>

                        @php
                            $addBudgetRoute = '#';
                            if (Auth::user()->hasRole('Admin')) $addBudgetRoute = route('admin.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Responsable de projet')) $addBudgetRoute = route('Responsable_de_projet.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Employee')) $addBudgetRoute = route('employee.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $addBudgetRoute = route('Responsable_gestionnaire.entreprise.addBudget');
                        @endphp
                        <div class="dt-card overflow-hidden">
                            <div class="px-5 py-4 border-b border-slate-100">
                                <h3 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Add Budget to Cashbox') }}</h3>
                            </div>
                            <div class="p-4 sm:p-5">
                            <form action="{{ $addBudgetRoute }}" method="post" class="flex flex-col md:flex-row md:items-end gap-4">
                                @csrf
                                <div class="flex-1">
                                    <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.New budget') }}</label>
                                    <input type="number" name="newBudget" step="0.0001"
                                           class="w-full px-3 py-2 border"
                                           placeholder="{{ __('messages.Amount to add') }}">
                                </div>
                                <button type="submit" class="dt-btn dt-btn-teal justify-center flex-shrink-0 w-full md:w-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('messages.Add') }}
                                </button>
                            </form>
                            @if(session('successNewBudget'))
                                <div class="mt-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                                    {{ $billingFlash(session('successNewBudget')) }}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="mt-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                                    {{ $billingFlash(session('error')) }}
                                </div>
                            @endif
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $storeExpenseRoute = '#';
                    $cancelRoute = '#';
                    if (Auth::user()->hasRole('Admin')) {
                        $storeExpenseRoute = route('admin.Enterprise_infos.store');
                        $cancelRoute = route('admin.index');
                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                        $storeExpenseRoute = route('Responsable_de_projet.Enterprise_infos.store');
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif (Auth::user()->hasRole('Employee')) {
                        $storeExpenseRoute = route('employee.Enterprise_infos.store');
                        $cancelRoute = route('employee.index');
                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $storeExpenseRoute = route('Responsable_gestionnaire.Enterprise_infos.store');
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp
                <form class="mb-8" method="POST" action="{{ $storeExpenseRoute }}" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <div class="dt-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <h2 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.New Expense') }}</h2>
                        </div>
                        <div class="p-4 sm:p-5">
                            @if(session('successNewDepense'))
                                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                                    {{ $billingFlash(session('successNewDepense')) }}
                                </div>
                            @endif
                            @if(session('errorNewDepense'))
                                <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                                    {{ $billingFlash(session('errorNewDepense')) }}
                                </div>
                            @endif

                            <div id="billings" class="space-y-4">
                                <div class="billing-row grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Expense description') }}</label>
                                        <input class="w-full px-3 py-2 border" type="text" placeholder="{{ __('messages.Expense name placeholder') }}" name="name[]" />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Expense price') }}</label>
                                        <input class="w-full px-3 py-2 border" type="number" name="price[]" step="0.0001" placeholder="0.00"/>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-billing" 
                                    class="mt-3 dt-btn dt-btn-ghost text-[12px] text-[#2D7080]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                {{ __('messages.Add another expense line') }}
                            </button>
                        </div>
                        
                        <div class="p-4 sm:px-5 sm:py-4 bg-slate-50 border-t border-slate-100 flex flex-col-reverse sm:flex-row sm:justify-end sm:items-center gap-2">
                            <a href="{{ $cancelRoute }}" class="dt-btn dt-btn-ghost justify-center w-full sm:w-auto">
                                {{ __('messages.Cancel') }}
                            </a>
                            <button type="submit" class="dt-btn dt-btn-primary justify-center w-full sm:w-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.Submit') }}
                            </button>
                        </div>
                    </div>
                </form>

                <div class="space-y-3">
                    @foreach($billings_budget as $billing)
                        <div class="enterprise-card dt-card overflow-hidden"
                             data-budget-history-collapsible
                             aria-expanded="true">
                            <div class="px-5 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3"
                                 data-collapsible-header>
                                <div>
                                    <h3 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Budget label enterprise') }} {{$billing->budget}}€</h3>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ __('messages.Budget created at') }} {{$billing->created_at->format('d-m-Y')}}</p>
                                </div>
                                <div class="flex items-center gap-2 self-start sm:self-auto">
                                    @php
                                        $deleteBudgetRoute = '#';
                                        if (Auth::user()->hasRole('Admin')) $deleteBudgetRoute = route('admin.billing.destroy', $billing->id);
                                        elseif (Auth::user()->hasRole('Responsable de projet')) $deleteBudgetRoute = route('Responsable_de_projet.billing.destroy', $billing->id);
                                        elseif (Auth::user()->hasRole('Employee')) $deleteBudgetRoute = route('employee.billing.destroy', $billing->id);
                                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteBudgetRoute = route('Responsable_gestionnaire.billing.destroy', $billing->id);
                                    @endphp
                                    <form method="post" action="{{ $deleteBudgetRoute }}" onsubmit="return confirm(@js(__('messages.Are you sure?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dt-btn dt-btn-ghost text-red-700 text-[11px]">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            {{ __('messages.Delete') }}
                                        </button>
                                    </form>

                                    <span class="shrink-0 text-slate-400 lg:hidden" aria-hidden="true">
                                        <svg class="enterprise-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <div class="divide-y divide-slate-100" data-collapsible-body>
                                @php 
                                    $entrepriseCharges = App\Models\entrepriseCharges::where('entreprise_id', $entreprise->id)->where('Billings_budget_id', $billing->id)->orderBy('created_at')->get();
                                @endphp
                                
                                @if($entrepriseCharges->isEmpty())
                                    <p class="p-6 text-sm text-gray-500 text-center">{{ __('messages.No expenses for this budget') }}</p>
                                @else
                                    @foreach($entrepriseCharges as $entrepriseCharge)
                                        <div class="billing-history-row p-4 sm:p-5 space-y-3 md:space-y-0 md:grid md:grid-cols-12 md:gap-3 md:items-end">
                                            <div class="md:col-span-3">
                                                <label class="block text-[10px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Expense Object') }}</label>
                                                      <input id="charge-name-{{ $entrepriseCharge->id }}" type="text" name="chargename" value="{{ $entrepriseCharge->charge_name }}" readonly
                                                          class="editable w-full px-3 py-2 border text-[13px]
                                                              {{ $entrepriseCharge->validated != 1 ? 'bg-gray-50 cursor-pointer focus:bg-white focus:cursor-text' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-[10px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Price') }}</label>
                                                      <input id="price-{{ $entrepriseCharge->id }}" type="text" name="price" value="{{ $entrepriseCharge->price }}€" readonly
                                                          class="editable w-full px-3 py-2 border text-[13px]
                                                              {{ $entrepriseCharge->validated != 1 ? 'bg-gray-50 cursor-pointer focus:bg-white focus:cursor-text' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}">
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="block text-[10px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1">{{ __('messages.Date') }}</label>
                                                      <input id="created_at-{{ $entrepriseCharge->id }}" type="text" name="created_at" value="{{ $entrepriseCharge->created_at->format('d-m-Y') }}" readonly
                                                          class="editable w-full px-3 py-2 border text-[13px]
                                                              {{ $entrepriseCharge->validated != 1 ? 'bg-gray-50 cursor-pointer focus:bg-white focus:cursor-text' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}">
                                            </div>
                                            <div class="md:col-span-4 billing-inline-actions md:justify-end">
                                                @if($billing->sold_out == 0)
                                                    @if($entrepriseCharge->validated != 1)
                                                        @php
                                                            $validateRoute = '#';
                                                            if (Auth::user()->hasRole('Admin')) $validateRoute = route('admin.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $validateRoute = route('Responsable_de_projet.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Employee')) $validateRoute = route('employee.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $validateRoute = route('Responsable_gestionnaire.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                        @endphp
                                                        <form method="post" action="{{ $validateRoute }}">
                                                            @csrf
                                                            <button type="submit" class="dt-btn dt-btn-teal text-[11px]"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ __('messages.Validate') }}</button>
                                                        </form>
                                                        @php
                                                            $deleteExpenseRoute = '#';
                                                            if (Auth::user()->hasRole('Admin')) $deleteExpenseRoute = route('admin.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $deleteExpenseRoute = route('Responsable_de_projet.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                            elseif (Auth::user()->hasRole('Employee')) $deleteExpenseRoute = route('employee.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteExpenseRoute = route('Responsable_gestionnaire.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                        @endphp
                                                        <form method="post" action="{{ $deleteExpenseRoute }}" onsubmit="return confirm(@js(__('messages.Are you sure?')));">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dt-btn dt-btn-ghost text-red-700 text-[11px]"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7h6m-7-3h8m-9 3 1 14h6l1-14"/></svg>{{ __('messages.Delete') }}</button>
                                                        </form>
                                                    @else
                                                        @php
                                                            $unvalidateRoute = '#';
                                                            if (Auth::user()->hasRole('Admin')) $unvalidateRoute = route('admin.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $unvalidateRoute = route('Responsable_de_projet.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Employee')) $unvalidateRoute = route('employee.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $unvalidateRoute = route('Responsable_gestionnaire.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                        @endphp
                                                        <form method="post" action="{{ $unvalidateRoute }}">
                                                            @csrf
                                                            <button type="submit" class="dt-btn dt-btn-accent text-[11px]">{{ __('messages.Cancel validation') }}</button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[11px] font-semibold ring-1
                                                                {{ $entrepriseCharge->validated == 0 ? 'bg-rose-50 text-rose-700 ring-rose-600/15' : 'bg-emerald-50 text-emerald-700 ring-emerald-600/15' }}">
                                                        {{ $entrepriseCharge->validated == 0 ? __('messages.Not Validated') : __('messages.Validated') }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    {{-- CONSERVATION DES SCRIPTS ORIGINAUX --}}
    @include('dashboard_layouts.scripts') 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    @livewireScripts 

    <script>
        // Script pour désactiver l'autocomplete
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });

        // Script pour ajouter dynamiquement des champs de dépense
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('add-billing').addEventListener('click', function() {
                var billings = document.getElementById('billings');
                var newBilling = billings.firstElementChild.cloneNode(true);
                
                // Réinitialiser les valeurs des nouveaux champs
                newBilling.querySelector('input[name="name[]"]').value = '';
                newBilling.querySelector('input[name="price[]"]').value = '';

                // Ajouter un bouton de suppression au nouveau champ
                if (billings.children.length >= 0) { // Logique originale autorisait la suppression du premier
                    var deleteButton = document.createElement('button');
                    deleteButton.textContent = @json(__('messages.Delete'));
                    deleteButton.type = 'button';
                    // Ajout des classes Tailwind pour le bouton de suppression
                    deleteButton.className = 'dt-btn dt-btn-ghost text-red-700 text-[11px] remove-billing';
                    deleteButton.addEventListener('click', function() {
                        billings.removeChild(newBilling);
                    });
                    newBilling.appendChild(deleteButton);
                }

                billings.appendChild(newBilling);
            });
        });

        // Script pour la modification des champs de DÉPENSE (editable)
        document.addEventListener('DOMContentLoaded', function() {
            const editableInputs = document.querySelectorAll('.editable');

            editableInputs.forEach(input => {
                input.addEventListener('dblclick', function() {
                    if (this.readOnly) {
                        this.readOnly = false;
                        this.focus();
                        // Ajout des classes Tailwind pour le mode édition
                        this.classList.remove('bg-gray-50', 'cursor-pointer');
                        this.classList.add('bg-white', 'cursor-text');
                    }
                });

                input.addEventListener('blur', function() {
                    this.readOnly = true;
                    // Ajout des classes Tailwind pour le mode lecture
                    this.classList.add('bg-gray-50', 'cursor-pointer');
                    this.classList.remove('bg-white', 'cursor-text');
                    updateField(this);
                });

                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            });

            function updateField(input) {
                const id = input.id.split('-')[2]; // l'id est en 3ème partie (ex: charge-name-123)
                const column = input.id.split('-')[0]; // ex: 'charge-name' ou 'price' ou 'created_at'
                const content = input.value;
                let fetchUrl = '';
                
                @if(Auth::user()->hasRole('Admin'))
                    fetchUrl = '{{ route("admin.Enterprise_Billings.updateBillings") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    fetchUrl = '{{ route("Responsable_de_projet.Enterprise_Billings.updateBillings") }}';
                @elseif(Auth::user()->hasRole('Employee'))
                    fetchUrl = '{{ route("employee.Enterprise_Billings.updateBillings") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    fetchUrl = '{{ route("Responsable_gestionnaire.Enterprise_Billings.updateBillings") }}';
                @endif
                
                if (!fetchUrl) return; // Ne fait rien si aucune route n'est définie

                fetch(fetchUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        id: id,
                        column: column, // 'charge-name', 'price', 'created_at'
                        content: content
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Update successful');
                        location.reload();
                    } else {
                        console.error('Update failed');
                        location.reload(); // La logique originale rechargeait aussi en cas d'erreur
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    location.reload(); // La logique originale rechargeait aussi en cas d'erreur
                });
            }
        });

        // Script pour la modification du BUDGET (enableEditing)
        function enableEditing(input, budgetId) {
            input.removeAttribute('readonly');
            input.focus();
            // Ajout des classes Tailwind pour le mode édition
            input.classList.remove('bg-gray-50', 'cursor-pointer');
            input.classList.add('bg-white', 'cursor-text');

            const onBlur = function() {
                updateBudget(input, budgetId);
                input.removeEventListener('blur', onBlur);
                input.removeEventListener('keypress', onKeypress);
            };

            const onKeypress = function(event) {
                if (event.key === 'Enter') {
                    updateBudget(input, budgetId);
                    input.removeEventListener('blur', onBlur);
                    input.removeEventListener('keypress', onKeypress);
                }
            };

            input.addEventListener('blur', onBlur);
            input.addEventListener('keypress', onKeypress);
        }

        function updateBudget(input, budgetId) {
            input.setAttribute('readonly', true);
            // Ajout des classes Tailwind pour le mode lecture
            input.classList.add('bg-gray-50', 'cursor-pointer');
            input.classList.remove('bg-white', 'cursor-text');
            
            const budget = input.value;
            let fetchUrl = '';

            @if(Auth::user()->hasRole('Admin'))
                fetchUrl = `/admin/budget/update/${budgetId}`;
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                fetchUrl = `/Responsable_de_projet/budget/update/${budgetId}`;
            @elseif(Auth::user()->hasRole('Employee'))
                fetchUrl = `/employee/budget/update/${budgetId}`;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                fetchUrl = `/Responsable_gestionnaire/budget/update/${budgetId}`;
            @endif

            if (!fetchUrl) return;

            fetch(fetchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ budget: budget })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(@json(__('messages.Budget has expenses')));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(@json(__('messages.Budget update error')));
                location.reload();
            });
        }

        // Collapse cards on mobile/tablet (<= 1024px)
        (function initEntrepriseExpensesCardAccordions() {
            const mql = window.matchMedia('(max-width: 1024px)');

            function setCollapsed(card, collapsed) {
                if (!card) return;
                card.classList.toggle('is-collapsed', !!collapsed);
                card.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }

            function initForViewport() {
                const isCompact = mql.matches;
                const cards = Array.from(document.querySelectorAll('.enterprise-card[data-budget-history-collapsible]'));

                for (const card of cards) {
                    const header = card.querySelector('[data-collapsible-header]');
                    const body = card.querySelector('[data-collapsible-body]');
                    if (!header || !body) continue;

                    if (!header.dataset.collapseBound) {
                        header.dataset.collapseBound = '1';
                        header.addEventListener('click', (e) => {
                            const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                            if (tag === 'a' || tag === 'button' || tag === 'input' || tag === 'select' || tag === 'textarea' || tag === 'label') return;

                            e.preventDefault();
                            e.stopPropagation();

                            const collapsed = card.classList.contains('is-collapsed');
                            setCollapsed(card, !collapsed);
                        });
                    }

                    setCollapsed(card, isCompact);
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initForViewport);
            } else {
                initForViewport();
            }

            if (mql.addEventListener) mql.addEventListener('change', initForViewport);
            else if (mql.addListener) mql.addListener(initForViewport);
        })();
    </script>
</body>
</html>
