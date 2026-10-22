<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Expense Note - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* ── Form input base ── */
        .dt-input {
            display: block; width: 100%;
            border: 1px solid #e2e8f0; border-radius: 8px;
            background: #f8fafc; color: #1e293b;
            font-size: 13px; padding: 8px 12px;
            transition: border-color .15s, background .15s;
            outline: none;
        }
        .dt-input:focus {
            border-color: #2D7080;
            box-shadow: 0 0 0 3px rgba(45,112,128,.12);
            background: #fff;
        }
        .dt-label {
            display: block; font-size: 11.5px; font-weight: 600;
            color: #475569; margin-bottom: 5px;
        }
        .dt-required { color: #e11d48; margin-left: 2px; }

        /* ── Dynamic row ── */
        .expense-row-grid {
            display: grid;
            grid-template-columns: 1fr 120px 120px 36px;
            gap: 8px;
            align-items: end;
        }
        @media (max-width: 640px) {
            .expense-row-grid {
                grid-template-columns: 1fr 1fr;
            }
            .expense-row-grid .col-delete { grid-column: span 2; justify-self: end; }
        }

        /* ── Total banner ── */
        .total-banner {
            background: linear-gradient(135deg, #f0fafb 0%, #e8f4f6 100%);
            border: 1px solid #b2d8de;
            border-radius: 10px;
            padding: 12px 16px;
        }
    </style>
    @include('partials.edit-layout-styles')
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />

        <main class="dt-edit-page flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">
            <div class="dt-edit-container mx-auto">

            {{-- ── Page header ── --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-5">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Expense Note History') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.New Expense Note') }}
                    </h1>
                </div>
                {{-- History shortcut --}}
                @php
                    $historyRoute = Auth::user()->hasRole('Admin') ? route('admin.notedefrais.notedefraishistory')
                        : (Auth::user()->hasRole('Responsable de projet') ? route('Responsable_de_projet.notedefrais.notedefraishistory')
                        : (Auth::user()->hasRole('Employee') ? route('employee.notedefrais.notedefraishistory')
                        : route('Responsable_gestionnaire.notedefrais.notedefraishistory')));
                @endphp
                <a href="{{ $historyRoute }}" class="dt-btn dt-btn-ghost flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    {{ __('messages.Expense Note History') }}
                </a>
            </div>

            {{-- ── Flash ── --}}
            @if(session('message'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 relative">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="absolute right-3 top-2.5 text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
                </div>
            @endif

            {{-- ── Form card ── --}}
            <div class="dt-card overflow-hidden">

                {{-- Card header --}}
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-[#2D7080]/10 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.New Expense Note') }}</p>
                        <p class="text-[11.5px] text-slate-400">{{ __('messages.Expense Note - DalyTrac') }}</p>
                    </div>
                </div>

                <form id="expenseForm" method="POST"
                    action="{{ route(
                        Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.notedefrais.saveNoteDeFraisPdf' :
                        (Auth::user()->hasRole('Admin') ? 'admin.notedefrais.saveNoteDeFraisPdf' :
                        (Auth::user()->hasRole('Employee') ? 'employee.notedefrais.saveNoteDeFraisPdf' :
                        'Responsable_gestionnaire.notedefrais.saveNoteDeFraisPdf'))
                    ) }}"
                    autocomplete="off">
                    @csrf

                    <div class="p-5 sm:p-6 space-y-5">

                        {{-- ── Section 1: General info ── --}}
                        <div>
                            <p class="text-[10.5px] font-bold uppercase tracking-[.08em] text-slate-400 mb-3">{{ __('messages.Expense Note Information') }}</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label for="employee_name" class="dt-label">{{ __('messages.Employee Name') }}<span class="dt-required">*</span></label>
                                    <select id="employee_name" name="employee_name" class="dt-input" required>
                                        <option value="">{{ __('messages.Select Employee') }}</option>
                                        @foreach($employees as $employee)
                                            <option value="{{ $employee->name }}" @selected(old('employee_name') === $employee->name)>{{ $employee->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="projectDescription" class="dt-label">{{ __('messages.Expense Description') }}<span class="dt-required">*</span></label>
                                    <input type="text" id="projectDescription" name="description"
                                           value="{{ old('description') }}" class="dt-input" required>
                                </div>
                            </div>
                        </div>

                        <input type="hidden" id="projectBudget" name="budget" value="0.00">

                        <div class="border-t border-slate-100 pt-5">
                            <div class="flex items-center justify-end mb-3">

                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="addExpenseRow()"
                                            class="dt-btn dt-btn-teal text-[11.5px] h-7 px-3">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                                        {{ __('messages.Add Row Expense') }}
                                    </button>
                                </div>
                            </div>

                            {{-- Table header (desktop) --}}
                            <div class="expense-row-grid hidden sm:grid mb-2 px-1">
                                <span class="text-[10.5px] font-bold uppercase tracking-[.07em] text-slate-400">{{ __('messages.Expense Details') }}</span>
                                <span class="text-[10.5px] font-bold uppercase tracking-[.07em] text-slate-400">{{ __('messages.Quantity') }}</span>
                                <span class="text-[10.5px] font-bold uppercase tracking-[.07em] text-slate-400">{{ __('messages.Budget expense') }}</span>
                                <span></span>
                            </div>

                            {{-- Rows container --}}
                            <div id="expenseRowsContainer" class="space-y-2">
                                {{-- First row (always present) --}}
                                <div class="expense-row-grid expense-data-row p-2 rounded-lg border border-slate-100 bg-slate-50/50">
                                    <input type="text" name="task[]"
                                           placeholder="{{ __('messages.Expense Details') }}"
                                           class="dt-input">
                                    <input type="number" step="0.0001" name="quantity[]"
                                           placeholder="{{ __('messages.public_pricing_users_solo') }}"
                                           class="taskQuantity dt-input">
                                    <input type="number" step="0.0001" name="taskBudget[]"
                                           placeholder="0.00"
                                           class="taskBudget dt-input">
                                    <div class="col-delete flex items-center">
                                        <button type="button" onclick="removeExpenseRow(this)"
                                                class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- ── Total banner ── --}}
                        <div class="total-banner flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-[#2D7080]/15 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-500">{{ __('messages.Total Budget') }}</p>
                                    <p class="text-[11px] text-slate-400">{{ __('messages.Mandatory information expense') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-[22px] font-bold text-[#0B2638] leading-none" id="totalDisplay">€0.00</p>
                            </div>
                        </div>

                    </div>

                    {{-- ── Footer actions ── --}}
                    <div class="px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-2">
                        <a href="{{ route(
                                Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.index' :
                                (Auth::user()->hasRole('Admin') ? 'admin.index' :
                                (Auth::user()->hasRole('Employee') ? 'employee.index' :
                                'Responsable_gestionnaire.index'))
                            ) }}"
                           class="dt-btn dt-btn-ghost w-full sm:w-auto justify-center">
                            {{ __('messages.Cancel') }}
                        </a>
                        <button type="submit" class="dt-btn dt-btn-primary w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            {{ __('messages.Generate Expense Note') }}
                        </button>
                    </div>

                </form>
            </div>

            </div>
        </main>
    </div>

<script>
    // ── Row management ──
    function rowHtml() {
        return `<div class="expense-row-grid expense-data-row p-2 rounded-lg border border-slate-100 bg-slate-50/50">
            <input type="text" name="task[]" placeholder=@js(__('messages.Expense Details')) class="dt-input">
            <input type="number" step="0.0001" name="quantity[]" placeholder="1" class="taskQuantity dt-input">
            <input type="number" step="0.0001" name="taskBudget[]" placeholder="0.00" class="taskBudget dt-input">
            <div class="col-delete flex items-center">
                <button type="button" onclick="removeExpenseRow(this)"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-red-500 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>`;
    }

    function addExpenseRow() {
        const container = document.getElementById('expenseRowsContainer');
        const div = document.createElement('div');
        div.innerHTML = rowHtml().trim();
        const row = div.firstChild;
        container.appendChild(row);
        row.querySelectorAll('.taskBudget, .taskQuantity').forEach(i => i.addEventListener('input', updateTotal));
    }

    function removeExpenseRow(btn) {
        const container = document.getElementById('expenseRowsContainer');
        if (container.querySelectorAll('.expense-data-row').length <= 1) return;
        btn.closest('.expense-data-row').remove();
        updateTotal();
    }

    function updateTotal() {
        let total = 0;
        const budgets  = document.querySelectorAll('.taskBudget');
        const qtys     = document.querySelectorAll('.taskQuantity');
        budgets.forEach((b, i) => {
            total += (parseFloat(b.value) || 0) * (parseFloat(qtys[i]?.value) || 0);
        });
        document.getElementById('projectBudget').value = total.toFixed(2);
        document.getElementById('totalDisplay').textContent = '€' + total.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ' ').replace('.', ',');
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.taskBudget, .taskQuantity').forEach(i => i.addEventListener('input', updateTotal));
    });
</script>
</body>
</html>
