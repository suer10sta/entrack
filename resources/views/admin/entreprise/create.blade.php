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
        .expenses-overview { max-width: 460px; margin-bottom: 1.25rem; }
        .expense-wallet { min-width: 0; padding: 1rem; border: 1px solid #c5dfe2; border-radius: .9rem; color: #0B2638; background: linear-gradient(125deg, #f7fcfc, #eef7f7); box-shadow: 0 2px 5px rgba(11,38,56,.025); }
        .wallet-heading { display: flex; align-items: center; gap: .65rem; margin-bottom: 1rem; }
        .wallet-heading h1 { font-size: 13px; font-weight: 700; margin: 0; }
        .wallet-icon { display: inline-flex; padding: .6rem; border: 1px solid #c5e1e4; border-radius: .9rem; color: #2D7080; background: #e0f0f2; }
        .wallet-balances { display: grid; grid-template-columns: minmax(0,1fr) minmax(0,1fr); gap: .75rem; }
        .enterprise-billing-page .expense-wallet label { display: block; color: #536779; font-size: 11px; margin-bottom: .15rem; }
        .wallet-remaining { text-align: right; }
        .wallet-amount { display: flex; align-items: baseline; gap: .2rem; min-width: 0; }
        .enterprise-billing-page .expense-wallet .wallet-amount input { width: auto; field-sizing: content; max-width: calc(100% - 1rem); min-width: 0; padding: 0; border: 0; border-radius: 0; background: transparent; color: #0B2638; font-size: clamp(18px, 2vw, 23px); line-height: 1.3; font-weight: 700; box-shadow: none; font-variant-numeric: tabular-nums; appearance: textfield; }
        .wallet-amount input::-webkit-inner-spin-button, .wallet-amount input::-webkit-outer-spin-button { appearance: none; margin: 0; }
        .wallet-amount span { font-size: 15px; font-weight: 700; }
        .wallet-remaining .wallet-amount { justify-content: flex-end; }
        .wallet-remaining .wallet-amount input { text-align: right; }
        .expense-wallet .wallet-remaining .wallet-amount input, .wallet-remaining .wallet-amount span { color: #16a34a; }
        .wallet-progress { height: 9px; margin-top: .8rem; border-radius: 99px; overflow: hidden; background: #e3ebee; }
        .wallet-progress-fill { height: 100%; border-radius: inherit; background: linear-gradient(90deg, #2D7080, #40949d); }
        .wallet-add-budget { margin-top: .85rem; }
        .wallet-add-budget summary { display: inline-flex; align-items: center; gap: .4rem; padding: .4rem .65rem; border: 1px solid #b8d4d9; border-radius: .5rem; color: #245e6b; background: #fff; cursor: pointer; font-size: 11px; font-weight: 600; list-style: none; }
        .wallet-add-budget summary:hover { background: #e7f2f3; }
        .wallet-add-budget summary::-webkit-details-marker { display: none; }
        .wallet-plus { display: inline-flex; align-items: center; justify-content: center; font-size: 16px; line-height: 1; }
        .wallet-add-form { margin-top: .85rem; }
        .wallet-empty { color: #536779; font-size: 13px; }
        .expenses-report-bar { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: .75rem; margin-bottom: .85rem; padding: .75rem 0; border-bottom: 1px solid #e2e8f0; }
        .expenses-report-bar h2 { margin: 0; padding-bottom: .5rem; font-size: 13px; font-weight: 600; color: #0B2638; }
        .expenses-report-bar form { display: grid; grid-template-columns: 155px 155px auto; align-items: end; gap: .5rem; }
        .expenses-report-bar form > div { min-width: 0; }
        .expenses-report-bar label { font-size: 10px; }
        .expenses-report-bar input { min-height: 34px; }
        .expenses-report-bar form > button { min-height: 34px; width: auto; }
        .expense-modal { width: min(640px, calc(100% - 2rem)); max-height: calc(100dvh - 2rem); padding: 0; border: 1px solid #e2e8f0; border-radius: 1rem; color: #0B2638; box-shadow: 0 24px 70px rgba(11,38,56,.25); overflow: auto; }
        .expense-modal::backdrop { background: rgba(11,38,56,.45); backdrop-filter: blur(3px); }
        .expense-modal .dt-card { border: 0; box-shadow: none; }
        .expense-modal-form { margin: 0; }
        .expense-modal-close { position: absolute; top: .65rem; right: .75rem; display: grid; place-items: center; width: 30px; height: 30px; border-radius: .5rem; color: #64748b; font-size: 24px; line-height: 1; background: #f1f5f9; }
        .expense-modal-close:hover { color: #0B2638; background: #e2e8f0; }
        .expense-modal-form > .dt-card > div:first-child { padding-right: 3.25rem; }
        @media (max-width: 639px) { .expenses-report-bar form { width: 100%; grid-template-columns: repeat(2, minmax(0, 1fr)); } .expenses-report-bar form > button { width: 100%; grid-column: 1 / -1; } }
        .enterprise-card .billing-history-row:nth-child(even) { background: #fafcfd; }
        .enterprise-card [data-collapsible-header] { background: #fff; }
        .budget-card-actions .dt-btn { min-height: 32px; padding: .4rem .65rem; }




        .expenses-overview { display: grid; grid-template-columns: 50% 45%; justify-content: space-between; align-items: start; width: 100%; max-width: none; margin-bottom: 2.5rem; gap: 0; }
        .expense-wallet { position: relative; isolation: isolate; overflow: hidden; padding: 1rem 1.1rem; border: 1px solid #c9dfe3; border-radius: 1rem; color: #0B2638; background: linear-gradient(145deg,#fff 14%,#f3f9fa 100%); box-shadow: 0 8px 22px rgba(11,38,56,.07); }
        .expense-wallet::before { position: absolute; z-index: -1; top: -4.5rem; right: -3.25rem; width: 11rem; height: 11rem; border: 1px solid rgba(45,112,128,.09); border-radius: 50%; content: ""; box-shadow: 0 0 0 1.5rem rgba(45,112,128,.025),0 0 0 3rem rgba(45,112,128,.02); }
        .wallet-heading { position: relative; gap: .7rem; margin-bottom: .85rem; }
        .wallet-icon { padding: .55rem; border: 1px solid #d7e9eb; border-radius: .75rem; color: #fff; background: linear-gradient(145deg,#367f8e,#245d6a); box-shadow: 0 3px 8px rgba(45,112,128,.2); }
        .wallet-heading h1 { font-size: 14px; letter-spacing: -.01em; }
        .wallet-balances { gap: .65rem; padding: 0; }
        .wallet-balances > div:first-child,.wallet-remaining { min-width: 0; padding: .7rem .8rem; border: 1px solid #e0eaed; border-radius: .75rem; background: rgba(255,255,255,.82); }
        .wallet-remaining { border-color: #d2e6e8; background: linear-gradient(140deg,#e9f5f5,#f5fbfb); }
        .enterprise-billing-page .wallet-balances label { margin-bottom: .3rem; color: #647987; font-size: 11px; font-weight: 600; }
        .enterprise-billing-page .expense-wallet .wallet-amount input { font-size: clamp(20px,2.2vw,27px); letter-spacing: -.035em; }
        .wallet-amount span { font-size: 14px; }
        .expense-wallet .wallet-remaining .wallet-amount input,.wallet-remaining .wallet-amount span { color: #237383; }
        .wallet-progress { height: 7px; margin-top: .8rem; background: #dfe9eb; }
        .wallet-progress-fill { background: linear-gradient(90deg,#2D7080,#53a2a5); box-shadow: 0 0 8px rgba(45,112,128,.2); }
        .wallet-add-budget { margin-top: .8rem; padding-top: .7rem; border-top: 1px solid #dce8ea; }
        .wallet-add-budget summary { gap: .5rem; padding: .35rem .6rem .35rem .35rem; border: 1px solid #d4e5e7; border-radius: .6rem; color: #245e6b; background: #fff; font-size: 12px; font-weight: 600; transition: background .15s ease,border-color .15s ease,transform .15s ease; }
        .wallet-add-budget summary:hover { border-color: #a9cdd2; background: #f2f9f9; color: #174c58; }
        .wallet-add-budget summary:active { transform: translateY(1px); }
        .wallet-plus { width: 26px; height: 26px; border-radius: .45rem; color: #fff; background: #2D7080; font-size: 17px; }
        @media (max-width: 639px) { .expense-wallet { padding: .9rem; } .wallet-balances { gap: .45rem; } .wallet-balances > div:first-child,.wallet-remaining { padding: .6rem; } .enterprise-billing-page .expense-wallet .wallet-amount input { font-size: clamp(17px,5vw,22px); } }
        @media (max-width: 559px) { .expenses-report-card form { grid-template-columns: minmax(0,1fr); } .expenses-report-card form > button { grid-column: 1; width: 100%; } }
        .enterprise-billing-page .expense-wallet .wallet-amount input { font-size: clamp(22px, 2.5vw, 30px); }
        .expenses-report-card { grid-column: 2; grid-row: 1; width: 100%; min-width: 0; align-self: start; justify-self: end; padding: 1rem 1.1rem; display: block; border: 1px solid #dce5e9; border-radius: 1rem; background: #fff; box-shadow: 0 8px 22px rgba(11,38,56,.07); }
        .expense-wallet { grid-column: 1; grid-row: 1; }
        .expenses-report-card h2 { margin: 0 0 .85rem; color: #0B2638; font-size: 16px; font-weight: 700; }
        .expenses-report-card form { display: grid; grid-template-columns: minmax(0,1fr); gap: .55rem; align-items: stretch; }
        .expenses-report-card form > div { min-width: 0; }
        .expenses-report-card form > button { grid-column: 1; justify-self: start; width: auto; min-height: 34px; margin-top: .1rem; padding: .35rem .75rem; font-size: 13px; white-space: nowrap; }
        .enterprise-billing-page .expenses-report-card input[type="date"] { min-height: 34px; height: 34px; padding: .3rem .5rem; border: 1px solid #94a3b8; border-radius: .45rem; background: #fff; font-size: 12px; }
        .enterprise-billing-page .expenses-report-card input[type="date"]:focus { border-color: #2D7080; box-shadow: 0 0 0 2px rgba(45,112,128,.12); }
        .expenses-report-card form label { font-size: 10px; margin-bottom: .3rem; }
        @media (max-width: 559px) { .expenses-report-card form { width: 100%; grid-template-columns: minmax(0,1fr); } .expenses-report-card form > button { grid-column: 1; width: 100%; } }
        .budget-toggle { display: grid; place-items: center; width: 30px; height: 30px; border-radius: .5rem; }
        .budget-toggle:hover { background: #f1f5f9; color: #2D7080; }
        .enterprise-chevron { transition: transform .18s ease; }
        .enterprise-card[aria-expanded="true"] .enterprise-chevron { transform: rotate(180deg); }
        .enterprise-card [data-collapsible-header] { cursor: pointer; }
        .enterprise-card [data-collapsible-body][hidden] { display: none !important; }
        .expense-table-wrap { overflow-x: auto; }
        .expense-table { width: 100%; min-width: 570px; border-collapse: collapse; table-layout: fixed; }
        .expense-table th { padding: .65rem .9rem; color: #334155; font-size: 12px; font-weight: 600; text-align: left; background: #e9eef3; border-bottom: 1px solid #d4dde6; }
        .expense-table th:nth-child(1) { width: 42%; } .expense-table th:nth-child(2) { width: 15%; } .expense-table th:nth-child(3) { width: 19%; } .expense-table th:nth-child(4) { width: 24%; }
        .expense-table th:last-child, .expense-table td:last-child { text-align: right; padding-right: .9rem; }
        .enterprise-billing-page .expense-table .billing-inline-actions { justify-content: flex-end; }
        .expense-table td { padding: .4rem .75rem; border-bottom: 1px solid #edf1f5; vertical-align: middle; }
        .expense-table tr:last-child td { border-bottom: 0; }
        .enterprise-billing-page .expense-table .expense-cell-input { width: 100%; min-width: 0; border: 1px solid transparent; border-radius: .3rem; padding: .25rem .1rem; font-size: 12px; background: transparent; color: #334155; }
        .enterprise-billing-page .expense-table .expense-cell-input:not([readonly]) { background: #fff; border-color: #2D7080; }
        .expense-table .expense-validate-btn { background: #15803d; color: #fff; border: 1px solid #15803d; border-radius: .4rem; box-shadow: 0 1px 2px rgba(0,0,0,.12); }
        .expense-table .expense-validate-btn:hover { background: #166534; border-color: #166534; box-shadow: 0 2px 4px rgba(0,0,0,.16); }
        .expense-table .expense-validate-btn:active { transform: translateY(1px); box-shadow: none; }
        .expense-table .expense-validate-btn:focus-visible { outline: 2px solid #15803d; outline-offset: 2px; }
        .expense-table .dt-btn svg { flex-shrink: 0; width: 14px; height: 14px; }
        .expense-table .dt-btn { min-height: 27px; padding: .25rem .45rem; font-size: 10px; }
        @media (max-width: 900px) { .expenses-overview { grid-template-columns: minmax(0,1fr); } .expenses-report-card,.expense-wallet { grid-column: auto; grid-row: auto; } }

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

@php
                            $reportPrefix = 'admin';
                            if (Auth::user()->hasRole('Responsable de projet')) $reportPrefix = 'Responsable_de_projet';
                            elseif (Auth::user()->hasRole('Employee')) $reportPrefix = 'employee';
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportPrefix = 'Responsable_gestionnaire';
                        @endphp

                <div class="expenses-overview">
                    @php
                            $addBudgetRoute = '#';
                            if (Auth::user()->hasRole('Admin')) $addBudgetRoute = route('admin.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Responsable de projet')) $addBudgetRoute = route('Responsable_de_projet.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Employee')) $addBudgetRoute = route('employee.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $addBudgetRoute = route('Responsable_gestionnaire.entreprise.addBudget');
                        @endphp
                    <section class="expense-wallet" aria-labelledby="wallet-title">
                        <div class="wallet-heading">
                            <span class="wallet-icon" aria-hidden="true"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M20 8V6a2 2 0 0 0-2-2H6a3 3 0 0 0 0 6h14v10H6a3 3 0 0 1-3-3V7"/><path d="M20 12h-5v5h5"/><path d="M16.5 14.5h.01"/></svg></span>
                            <h1 id="wallet-title">{{ __('messages.Current Budget') }}</h1>
                        </div>
                        @if($latestBudget)
                            <div class="wallet-balances">
                                <div>
                                    <label for="budget-input-{{ $latestBudget->id }}">{{ __('messages.Budget') }}</label>
                                    <div class="wallet-amount"><input id="budget-input-{{ $latestBudget->id }}" type="number" name="monthBudget" step="any" value="{{ $latestBudget->budget }}" readonly ondblclick="enableEditing(this, {{ $latestBudget->id }})"><span>€</span></div>
                                </div>
                                <div class="wallet-remaining">
                                    <label for="sold-input">{{ __('messages.Remaining') }}</label>
                                    <div class="wallet-amount"><input id="sold-input" type="number" name="sold" value="{{ $latestBudget->budget_remaining }}" readonly><span>€</span></div>
                                </div>
                            </div>
                            @php
                                $walletUsage = (float) $latestBudget->budget > 0
                                    ? max(0, min(100, (1 - (float) $latestBudget->budget_remaining / (float) $latestBudget->budget) * 100))
                                    : 0;
                            @endphp
                            <div class="wallet-progress" role="progressbar" aria-label="{{ __('messages.Current Budget') }}" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ round($walletUsage) }}">
                                <div class="wallet-progress-fill" style="width: {{ $walletUsage }}%"></div>
                            </div>
                        @else
                            <p class="wallet-empty">{{ __('messages.No budget defined') }}</p>
                        @endif
                        <details class="wallet-add-budget">
                            <summary><span class="wallet-plus" aria-hidden="true">+</span>{{ __('messages.Add Budget to Cashbox') }}</summary>
                            <div class="wallet-add-form"><form action="{{ $addBudgetRoute }}" method="post" class="flex flex-col sm:flex-row sm:items-end gap-2">
                                @csrf
                                <div class="flex-1">
                                    <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.New budget') }}</label>
                                    <input type="number" name="newBudget" step="0.0001"
                                           class="w-full px-3 py-2 border"
                                           placeholder="{{ __('messages.Amount to add') }}">
                                </div>
                                <button type="submit" class="dt-btn dt-btn-teal justify-center flex-shrink-0 w-full sm:w-auto">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                    {{ __('messages.Add') }}
                                </button>
                            </form></div>
                        </details>
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
                    </section>

                    <section class="expenses-report-card" aria-labelledby="expense-report-title">
                        <h2 id="expense-report-title">{{ __('messages.Export Report') }}</h2>
                        <form action="{{ route($reportPrefix.'.entrepriseBillings.downloadRepports') }}" method="GET" class="flex flex-col md:flex-row md:items-end gap-4">
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Start Date') }}:</label>
                                <input id="start-date" type="date" name="startDate" required class="w-full px-3 py-2 border">
                            </div>
                            <div class="flex-1">
                                <label class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.End Date') }}:</label>
                                <input id="end-date" type="date" name="endDate" required class="w-full px-3 py-2 border">
                            </div>
                            <button type="submit" id="downloadLink" class="dt-btn dt-btn-primary justify-center flex-shrink-0 w-full md:w-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19z"/></svg>
                                {{ __('messages.Print PDF') }}
                            </button>
                        </form>
                    </section>

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


                @if(session('successNewDepense'))
                                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                                    {{ $billingFlash(session('successNewDepense')) }}
                                </div>
                            @endif

                <div class="space-y-3">
                    @foreach($billings_budget as $billing)
                        <div class="enterprise-card dt-card overflow-hidden"
                             data-budget-history-collapsible
                             aria-expanded="true">
                            <div class="px-4 py-3 border-b border-slate-100 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3"
                                 data-collapsible-header>
                                <div>
                                    <h3 class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Budget label enterprise') }} {{$billing->budget}}€</h3>
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ __('messages.Budget created at') }} {{$billing->created_at->format('d-m-Y')}}</p>
                                </div>
                                <div class="budget-card-actions flex flex-wrap items-center gap-2 self-start sm:self-auto">
                                    @php
                                        $deleteBudgetRoute = '#';
                                        if (Auth::user()->hasRole('Admin')) $deleteBudgetRoute = route('admin.billing.destroy', $billing->id);
                                        elseif (Auth::user()->hasRole('Responsable de projet')) $deleteBudgetRoute = route('Responsable_de_projet.billing.destroy', $billing->id);
                                        elseif (Auth::user()->hasRole('Employee')) $deleteBudgetRoute = route('employee.billing.destroy', $billing->id);
                                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteBudgetRoute = route('Responsable_gestionnaire.billing.destroy', $billing->id);
                                    @endphp
                                    <button type="button" class="dt-btn dt-btn-teal text-[11px]" data-add-budget-expense="{{ $billing->id }}" data-budget-label="{{ __('messages.Budget label enterprise') }} {{ $billing->budget }}€"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" d="M12 4v16M4 12h16"/></svg>{{ __('messages.Add Expense') }}</button>
                                    <form method="post" action="{{ $deleteBudgetRoute }}" onsubmit="return confirm(@js(__('messages.Are you sure?')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="dt-btn dt-btn-ghost text-red-700 text-[11px]">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                            {{ __('messages.Delete') }}
                                        </button>
                                    </form>

                                    <button type="button" data-budget-toggle class="budget-toggle shrink-0 text-slate-400" aria-label="{{ __('messages.Budget') }}" aria-expanded="true">
                                        <svg class="enterprise-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                            <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>

                            <div class="divide-y divide-slate-100" data-collapsible-body>
                                @php
                                    $entrepriseCharges = App\Models\entrepriseCharges::where('entreprise_id', $entreprise->id)->where('Billings_budget_id', $billing->id)->orderBy('created_at')->get();
                                @endphp

                                @if($entrepriseCharges->isEmpty())
                                    <p class="p-6 text-sm text-gray-500 text-center">{{ __('messages.No expenses for this budget') }}</p>
                                @else
                                    <div class="expense-table-wrap"><table class="expense-table">
                                        <thead><tr><th>{{ __('messages.Expense Object') }}</th><th>{{ __('messages.Price') }}</th><th>{{ __('messages.Date') }}</th><th>{{ __('messages.Actions') }}</th></tr></thead>
                                        <tbody>
                                    @foreach($entrepriseCharges as $entrepriseCharge)
                                        <tr class="billing-history-row">
                                            <td><input id="charge-name-{{ $entrepriseCharge->id }}" type="text" name="chargename" value="{{ $entrepriseCharge->charge_name }}" readonly aria-label="{{ __('messages.Expense Object') }}" class="editable expense-cell-input"></td>
                                            <td><input id="price-{{ $entrepriseCharge->id }}" type="text" name="price" value="{{ $entrepriseCharge->price }}€" readonly aria-label="{{ __('messages.Price') }}" class="editable expense-cell-input"></td>
                                            <td><input id="created_at-{{ $entrepriseCharge->id }}" type="text" name="created_at" value="{{ $entrepriseCharge->created_at->format('d-m-Y') }}" readonly aria-label="{{ __('messages.Date') }}" class="editable expense-cell-input"></td>
                                            <td><div class="billing-inline-actions">
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
                                                            <button type="submit" class="dt-btn expense-validate-btn text-[11px]"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>{{ __('messages.Validate') }}</button>
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
                                                            <button type="submit" class="dt-btn dt-btn-ghost text-red-700 text-[11px]"><svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M5 6l1 14a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1l1-14M10 10v7M14 10v7"/></svg>{{ __('messages.Delete') }}</button>
                                                        </form>
                                                    @else
                                                        @php
                                                            $unvalidateRoute = '#';
                                                            if (Auth::user()->hasRole('Admin')) $unvalidateRoute = route('admin.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $unvalidateRoute = route('Responsable_de_projet.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Employee')) $unvalidateRoute = route('employee.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $unvalidateRoute = route('Responsable_gestionnaire.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                        @endphp
                                                        <span class="dt-badge dt-badge-success">{{ __('messages.Validated') }}</span>
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

                                            </div></td>
                                        </tr>
                                    @endforeach
                                        </tbody></table></div>

                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    <dialog id="expense-modal" class="expense-modal" aria-labelledby="expense-modal-title">
        <button type="button" data-close-expense-modal class="expense-modal-close" aria-label="{{ __('messages.Close') }}">&times;</button>
        <form id="expense-form" class="expense-modal-form" method="POST" action="{{ $storeExpenseRoute }}" enctype="multipart/form-data" autocomplete="off">
                    @csrf

                    <input id="expense-budget-id" type="hidden" name="budget_id" value="{{ old('budget_id', $latestBudget?->id) }}">
                    <div class="dt-card overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100">
                            <div class="flex flex-wrap items-center justify-between gap-2"><h2 id="expense-modal-title" class="text-[15px] font-semibold text-[#0B2638]">{{ __('messages.New Expense') }}</h2><span id="expense-budget-label" class="text-[12px] text-slate-500">@if($latestBudget){{ __('messages.Budget label enterprise') }} {{ $latestBudget->budget }}€@endif</span></div>
                        </div>
                        <div class="p-4 sm:p-5">

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
                            <button type="button" data-close-expense-modal class="dt-btn dt-btn-ghost justify-center w-full sm:w-auto">
                                {{ __('messages.Cancel') }}
                            </button>
                            <button type="submit" class="dt-btn dt-btn-primary justify-center w-full sm:w-auto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                {{ __('messages.Submit') }}
                            </button>
                        </div>
                    </div>
                </form>
    </dialog>

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

        document.addEventListener('DOMContentLoaded', function () {
            const modal = document.getElementById('expense-modal');
            const form = document.getElementById('expense-form');
            let trigger = null;
            function openExpenseModal(button) {
                trigger = button;
                document.getElementById('expense-budget-id').value = button.dataset.addBudgetExpense;
                document.getElementById('expense-budget-label').textContent = button.dataset.budgetLabel;
                modal.showModal();
                form.querySelector('input[name="name[]"]').focus();
            }
            document.querySelectorAll('[data-add-budget-expense]').forEach(function (button) {
                button.setAttribute('aria-haspopup', 'dialog');
                button.setAttribute('aria-controls', 'expense-modal');
                button.addEventListener('click', function (event) {
                    event.stopPropagation();
                    openExpenseModal(button);
                });
            });
            modal.querySelectorAll('[data-close-expense-modal]').forEach(function (button) {
                button.addEventListener('click', function () { modal.close(); });
            });
            modal.addEventListener('click', function (event) {
                if (event.target !== modal) return;
                const bounds = modal.getBoundingClientRect();
                if (event.clientX < bounds.left || event.clientX > bounds.right || event.clientY < bounds.top || event.clientY > bounds.bottom) modal.close();
            });
            modal.addEventListener('close', function () { if (trigger) trigger.focus(); });
            @if(session('errorNewDepense') || $errors->any())
                const previousBudget = document.getElementById('expense-budget-id').value;
                const previousButton = Array.from(document.querySelectorAll('[data-add-budget-expense]')).find(button => button.dataset.addBudgetExpense === previousBudget);
                if (previousButton) openExpenseModal(previousButton);
            @endif
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
                card.querySelector('[data-collapsible-body]').hidden = collapsed;
                card.querySelector('[data-budget-toggle]').setAttribute('aria-expanded', String(!collapsed));
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
                            if (e.target.closest('a, button, input, select, textarea, label') && !e.target.closest('[data-budget-toggle]')) return;

                            e.preventDefault();
                            e.stopPropagation();

                            const collapsed = card.classList.contains('is-collapsed');
                            setCollapsed(card, !collapsed);
                        });
                    }

                    if (!card.dataset.accordionInitialized) {
                        setCollapsed(card, card !== cards[0]);
                        card.dataset.accordionInitialized = '1';
                    }
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
