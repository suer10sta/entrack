<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Expense Note History - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* ── Table base ── */
        #expenseTable th {
            font-size: 11px; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #64748b;
            padding: 10px 12px; white-space: nowrap;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        #expenseTable td {
            padding: 9px 12px; font-size: 13px;
            vertical-align: middle; border-bottom: 1px solid #f1f5f9;
        }
        #expenseTable tbody tr:last-child td { border-bottom: none; }
        #expenseTable tbody tr:hover td     { background: #f8fafc; }

        /* ── Note link ── */
        .note-link        { color: #2D7080; font-weight: 500; }
        .note-link:hover  { color: #0B2638; text-decoration: underline; }

        /* ── Action buttons ── */
        .act-btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 4px; height: 26px; padding: 0 10px; border-radius: 6px;
            flex-shrink: 0; cursor: pointer; font-size: 11.5px; font-weight: 600;
            white-space: nowrap; transition: background .15s, border-color .15s;
            outline: none; line-height: 1; text-decoration: none;
        }
        .act-btn-icon    { width: 12px; height: 12px; flex-shrink: 0; }
        .act-row         { display: flex; flex-wrap: wrap; align-items: center; gap: 3px; justify-content: flex-start; }

        .act-btn-view    { background:#f0fafb; color:#2D7080;  border:1px solid #99d6de; }
        .act-btn-view:hover   { background:#e0f2f1; border-color:#2D7080; }
        .act-btn-delete  { background:#fff1f2; color:#b91c1c; border:1px solid #fca5a5; }
        .act-btn-delete:hover { background:#fee2e2; border-color:#f87171; }

        /* ── Filter select ── */
        .dt-filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 14px;
            padding-right: 32px;
        }

        /* ── Mobile card layout (≤ 1023px) ── */
        @media (max-width: 1023px) {
            #expenseTable thead { display: none; }
            #expenseTable tbody { display: flex; flex-direction: column; gap: 12px; }
            #expenseTable tbody tr.expense-row {
                display: grid; grid-template-columns: 1fr 1fr;
                gap: 8px; padding: 12px;
                border: 1px solid rgba(148,163,184,.45); border-radius: 14px;
                background: linear-gradient(160deg,#fff 0%,#f8fafc 100%);
                box-shadow: 0 2px 16px -4px rgba(11,38,56,.10);
            }
            #expenseTable tbody tr.expense-row > td {
                display: block; padding: 8px 10px !important;
                border: 1px solid rgba(226,232,240,.85);
                border-radius: 10px; background: rgba(255,255,255,.82);
                white-space: normal !important; word-break: break-word;
            }
            #expenseTable tbody tr.expense-row > td[data-span="2"] { grid-column: span 2; }
            #expenseTable tbody tr.expense-row > td[data-label]::before {
                content: attr(data-label);
                display: block; font-size: 10px; font-weight: 700;
                letter-spacing: .06em; text-transform: uppercase;
                color: #94a3b8; margin-bottom: 4px;
            }
            #expenseTable tbody tr.expense-row > td.expense-toggle-cell[data-label]::before { content: none !important; }
            #expenseTable tbody tr.expense-row.is-collapsed > td:not(.expense-toggle-cell) { display: none !important; }
            .expense-chevron { transition: transform 180ms ease; }
            #expenseTable tbody tr.expense-row[aria-expanded="true"] .expense-chevron { transform: rotate(180deg); }
        }
    </style>
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

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">

            {{-- ── Totals ── --}}
            @php
                $Total_budget = App\Models\note_de_frais::where('entreprise_id', Auth::user()->entreprise_id)->sum('budget');
            @endphp

            {{-- ── Page header ── --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.New Expense Note') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Expense Note History') }}
                    </h1>
                    <p class="text-[12px] text-slate-500 mt-0.5 hidden sm:block">{{ __('messages.Track and manage all expense notes') }}</p>
                </div>
                <button type="button" id="openReportModalBtn" class="dt-btn dt-btn-primary flex-shrink-0">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    {{ __('messages.Expense Note Reports PDF') }}
                </button>
            </div>

            {{-- ── Flash ── --}}
            @if(session('message'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 relative">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="absolute right-3 top-2.5 text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 relative">
                    <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                    <button onclick="this.parentElement.style.display='none'" class="absolute right-3 top-2.5 text-red-500 hover:text-red-700 text-lg leading-none">&times;</button>
                </div>
            @endif

            {{-- ── Stat card ── --}}
            <div class="flex gap-2 sm:gap-3 mb-4">
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-600/15 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400">{{ __('messages.TOTAL ACCEPTED') }}</p>
                        <p class="text-[15px] sm:text-[17px] font-bold text-emerald-600 leading-tight">€{{ number_format($Total_budget, 2, ',', '.') }}</p>
                    </div>
                </div>
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400">{{ __('messages.Expense Note History') }}</p>
                        <p class="text-[15px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">{{ $notedefrais->count() }}</p>
                    </div>
                </div>
            </div>

            {{-- ── Date filters ── --}}
            <div class="dt-card p-3 sm:p-4 mb-4">
                <div class="flex flex-wrap items-end gap-2 sm:gap-3">
                    <div class="flex-1 min-w-[130px]">
                        <label for="startDate" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Start Date') }}</label>
                        <input type="date" id="startDate"
                               class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                    </div>
                    <div class="flex-1 min-w-[130px]">
                        <label for="endDate" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.End Date') }}</label>
                        <input type="date" id="endDate"
                               class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                    </div>
                </div>
            </div>

            {{-- ── Table card ── --}}
            <div class="dt-card overflow-hidden">

                <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Expense Note History') }}</p>
                     <div class="flex gap-4">
                     {{-- Per-page selector --}}
                            <div class="flex items-center gap-2 text-[12px] text-slate-500">
                                <span>{{ __('messages.Rows per page') }}:</span>
                                <select id="perPageSelect"
                                        class="rounded-lg border border-slate-200 bg-white text-[12px] text-slate-700 py-1 pr-[1.6rem] focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] cursor-pointer">
                                    <option value="10">10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                            <span class="dt-badge dt-badge-navy text-[11px]">
                                <span class="dt-dot"></span>
                                {{ $notedefrais->count() }} {{ __('messages.Total') }}
                            </span>
                        </div>
                    </div>

                <div class="overflow-x-auto">
                    <table class="w-full" id="expenseTable">
                        <thead>
                            <tr>
                                <th class="text-left pl-5">{{ __('messages.Note N') }}</th>
                                <th class="text-left">{{ __('messages.Employee Name') }}</th>
                                <th class="text-left">{{ __('messages.Budget') }}</th>
                                <th class="text-left">{{ __('messages.Created at expense') }}</th>
                                <th class="text-left w-[20rem]">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>

@forelse($notedefrais as $note)
@php
    $noteNumberDisplay = $entreprise->notedefraisPrefix . '-' . date('ymd', strtotime($note->created_at)) . $note->note_number;

    $dlRoute  = '#';
    $delRoute = '#';
    if(Auth::user()->hasRole('Admin')) {
        $dlRoute  = route('admin.notedefrais.downloadNoteDeFrais', $note->note_number);
        $delRoute = route('admin.notedefrais.destroy', $note->id);
    } elseif(Auth::user()->hasRole('Responsable de projet')) {
        $dlRoute  = route('Responsable_de_projet.notedefrais.downloadNoteDeFrais', $note->note_number);
        $delRoute = route('Responsable_de_projet.notedefrais.destroy', $note->id);
    } elseif(Auth::user()->hasRole('Employee')) {
        $dlRoute  = route('employee.notedefrais.downloadNoteDeFrais', $note->note_number);
        $delRoute = route('employee.notedefrais.destroy', $note->id);
    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
        $dlRoute  = route('Responsable_gestionnaire.notedefrais.downloadNoteDeFrais', $note->note_number);
        $delRoute = route('Responsable_gestionnaire.notedefrais.destroy', $note->id);
    }
    $rowDate = $note->created_at ? \Carbon\Carbon::parse($note->created_at)->format('Y-m-d') : '';
@endphp

<tr class="expense-row" data-row-date="{{ $rowDate }}" aria-expanded="true">

    {{-- Note number --}}
    <td data-label="{{ __('messages.Note N') }}" data-span="2"
        class="pl-5 expense-toggle-cell" data-expense-toggle>

        {{-- Desktop --}}
        <a href="{{ $dlRoute }}" class="note-link text-[12.5px] font-semibold hidden lg:inline">
            {{ $noteNumberDisplay }}
        </a>

        {{-- Mobile accordion header --}}
        <div class="flex items-center justify-between gap-2 lg:hidden">
            <a href="{{ $dlRoute }}" class="note-link text-[12px] font-semibold truncate">
                {{ $noteNumberDisplay }}
            </a>
            <svg class="expense-chevron w-4 h-4 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </td>

    {{-- Employee --}}
    <td data-label="{{ __('messages.Employee Name') }}" class="text-[12.5px] text-slate-700 font-medium">
        {{ $note->employee_name }}
    </td>

    {{-- Budget --}}
    <td data-label="{{ __('messages.Budget') }}" class=" pr-4 text-[13px] font-bold text-[#0B2638] whitespace-nowrap">
        €{{ number_format($note->budget, 2, ',', '.') }}
    </td>

    {{-- Date --}}
    <td data-label="{{ __('messages.Created at expense') }}" class="text-[12px] text-slate-500 whitespace-nowrap">
        {{ $note->created_at ? \Carbon\Carbon::parse($note->created_at)->format('d/m/Y') : '—' }}
    </td>

    {{-- Actions --}}
    <td data-label="{{ __('messages.Actions') }}" data-span="2" class="w-px whitespace-nowrap">
        <div class="act-row pr-3">

            {{-- Download / View --}}
            @if($dlRoute !== '#')
                <a href="{{ $dlRoute }}" class="act-btn act-btn-view">
                    <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/>
                    </svg>
                    {{ __('messages.View & Download') }}
                </a>
            @endif

            {{-- Delete --}}
            @can('Delete expense note')
                @if($delRoute !== '#')
                    <form action="{{ $delRoute }}" method="post"
                          onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this expense note?') }}');">
                        @csrf @method('DELETE')
                        <button type="submit" class="act-btn act-btn-delete">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            {{ __('messages.Delete Invoice') }}
                        </button>
                    </form>
                @endif
            @endcan

        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="5" class="px-5 py-14 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-[13px] font-medium text-slate-500">{{ __('messages.No expense notes found matching your criteria.') }}</p>
        </div>
    </td>
</tr>
@endforelse

                        </tbody>
                    </table>
                </div>

                {{-- Pagination bar --}}
               <div class="flex items-center justify-end px-5 py-3 border-t border-slate-100 bg-[#f8fafc]">
                    
                    <div class="flex items-center gap-3">
                        <span id="paginationInfo" class="text-[12px] text-slate-500"></span>
                        <div id="paginationControls" class="flex items-center gap-1"></div>
                    </div>
                </div>
            </div>
            {{-- end table card --}}

            {{-- ── Report Modal ── --}}
            <div id="reportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black/50 hidden">
                <div class="bg-white rounded-2xl shadow-[0_20px_50px_rgba(15,23,42,0.15)] w-full max-w-md mx-4">
                    <div class="flex justify-between items-center p-6 border-b border-slate-200">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Expense Note Reports PDF') }}</h5>
                        <button id="closeReportModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <div class="p-6">
                        <p class="text-[13px] text-slate-600">{{ __('messages.The report will use the Start Date and End Date you selected on the main page. If no dates are selected, all records will be downloaded.') }}</p>
                    </div>
                    <div class="flex items-center justify-end gap-2 p-4 bg-slate-50 border-t border-slate-200 rounded-b-2xl">
                        <button id="cancelReportModal" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                        <button id="confirmReport" class="dt-btn dt-btn-primary">{{ __('messages.Download PDF') }}</button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Report modal ──
    const modal       = document.getElementById('reportModal');
    const openBtn     = document.getElementById('openReportModalBtn');
    const closeBtn    = document.getElementById('closeReportModal');
    const cancelBtn   = document.getElementById('cancelReportModal');
    const confirmBtn  = document.getElementById('confirmReport');

    openBtn?.addEventListener('click',  () => modal?.classList.remove('hidden'));
    closeBtn?.addEventListener('click', () => modal?.classList.add('hidden'));
    cancelBtn?.addEventListener('click',() => modal?.classList.add('hidden'));

    confirmBtn?.addEventListener('click', function () {
        let base = '';
        @if(Auth::user()->hasRole('Admin'))                                base = "{{ route('admin.notedefrais.downloadRepports') }}"; @endif
        @if(Auth::user()->hasRole('Responsable de projet'))               base = "{{ route('Responsable_de_projet.notedefrais.downloadRepports') }}"; @endif
        @if(Auth::user()->hasRole('Employee'))                            base = "{{ route('employee.notedefrais.downloadRepports') }}"; @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) base = "{{ route('Responsable_gestionnaire.notedefrais.downloadRepports') }}"; @endif
        const sd = document.getElementById('startDate')?.value || '';
        const ed = document.getElementById('endDate')?.value   || '';
        window.location.href = (sd && ed) ? `${base}?startDate=${sd}&endDate=${ed}` : base;
        modal?.classList.add('hidden');
    });

    // ── Date filter + pagination ──
    let currentPage  = 1;
    let perPage      = 10;
    let filteredRows = [];

    function getDataRows() {
        return Array.from(document.querySelectorAll('#expenseTable tbody tr.expense-row'));
    }

    function filterByDate() {
        const sd = document.getElementById('startDate')?.value || '';
        const ed = document.getElementById('endDate')?.value   || '';

        filteredRows = getDataRows().filter(row => {
            const d = row.dataset.rowDate || '';
            if (sd && d < sd) return false;
            if (ed && d > ed) return false;
            return true;
        });
        currentPage = 1;
        applyPagination();
    }

    function applyPagination() {
        getDataRows().forEach(r => r.style.display = 'none');
        const total = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        if (currentPage > totalPages) currentPage = totalPages;
        const start = (currentPage - 1) * perPage;
        const end   = Math.min(start + perPage, total);
        filteredRows.forEach((row, i) => { row.style.display = (i >= start && i < end) ? '' : 'none'; });

        const empty = document.querySelector('#expenseTable tbody tr td[colspan="5"]');
        if (empty) empty.parentElement.style.display = total === 0 ? '' : 'none';

        const info = document.getElementById('paginationInfo');
        if (info) info.textContent = total === 0 ? '' : `${start + 1}–${end} / ${total}`;
        renderPageControls(totalPages);
    }

    function renderPageControls(totalPages) {
        const container = document.getElementById('paginationControls');
        if (!container) return;
        container.innerHTML = '';
        const base     = 'inline-flex items-center justify-center min-w-[28px] h-[28px] px-1.5 rounded-lg text-[12px] font-medium transition-colors cursor-pointer border select-none';
        const normal   = base + ' bg-white border-slate-200 text-slate-600 hover:bg-slate-50';
        const active   = base + ' bg-[#2D7080] border-[#2D7080] text-white';
        const disabled = base + ' bg-slate-50 border-slate-100 text-slate-300 cursor-not-allowed pointer-events-none';

        const prev = document.createElement('button');
        prev.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>';
        prev.className = currentPage === 1 ? disabled : normal;
        prev.onclick = () => { if (currentPage > 1) { currentPage--; applyPagination(); } };
        container.appendChild(prev);

        const range = [];
        for (let i = 1; i <= totalPages; i++) {
            if (i === 1 || i === totalPages || (i >= currentPage - 1 && i <= currentPage + 1)) range.push(i);
            else if (range[range.length - 1] !== '…') range.push('…');
        }
        range.forEach(p => {
            const btn = document.createElement('button');
            if (p === '…') { btn.textContent = '…'; btn.className = base + ' bg-white border-slate-100 text-slate-400 cursor-default pointer-events-none'; }
            else { btn.textContent = p; btn.className = p === currentPage ? active : normal; btn.onclick = () => { currentPage = p; applyPagination(); }; }
            container.appendChild(btn);
        });

        const next = document.createElement('button');
        next.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';
        next.className = currentPage === totalPages ? disabled : normal;
        next.onclick = () => { if (currentPage < totalPages) { currentPage++; applyPagination(); } };
        container.appendChild(next);
    }

    document.getElementById('startDate')?.addEventListener('change', filterByDate);
    document.getElementById('endDate')?.addEventListener('change',   filterByDate);
    document.getElementById('perPageSelect')?.addEventListener('change', function () {
        perPage = parseInt(this.value) || 10; currentPage = 1; applyPagination();
    });
    filterByDate();

    // ── Mobile accordion ──
    const mql = window.matchMedia('(max-width: 1023px)');
    function setCollapsed(row, c) { row.classList.toggle('is-collapsed', c); row.setAttribute('aria-expanded', c ? 'false' : 'true'); }
    function initAccordions() { document.querySelectorAll('#expenseTable tbody tr.expense-row').forEach(r => setCollapsed(r, mql.matches)); }
    document.addEventListener('click', e => {
        const cell = e.target.closest('[data-expense-toggle]'); if (!cell) return;
        if (e.target.closest('a')) return;
        const row = cell.closest('tr.expense-row'); if (!row) return;
        setCollapsed(row, !row.classList.contains('is-collapsed'));
    });
    mql.addEventListener ? mql.addEventListener('change', initAccordions) : mql.addListener(initAccordions);
    initAccordions();
});
</script>
</body>
</html>
