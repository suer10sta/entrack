<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Credit Note History - DalyTrac') }}</title>

    @vite('resources/css/app.css')
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <style>
        /* Searchable filter selects (Tom Select, dashboard look) */
        .ts-wrapper.single .ts-control {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            padding: 0.5rem 0.75rem !important;
            font-size: 13px !important;
            background-color: #f8fafc !important;
            min-height: 38px !important;
            color: #475569 !important;
            box-shadow: none !important;
        }
        .ts-wrapper.single.focus .ts-control {
            border-color: #2D7080 !important;
            background-color: #fff !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.20) !important;
        }
        .ts-dropdown {
            border: 1px solid #e2e8f0 !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1) !important;
        }
        .ts-dropdown .option {
            padding: 0.5rem 0.75rem !important;
            font-size: 13px !important;
        }
        .ts-dropdown .option.active {
            background-color: rgba(45, 112, 128, 0.10) !important;
            color: #0B2638 !important;
        }
    </style>
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        /* ── Table base ── */
        #cnTable th {
            font-size: 11px; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #64748b;
            padding: 10px 12px; white-space: nowrap;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        #cnTable td {
            padding: 9px 12px; font-size: 13px;
            vertical-align: middle; border-bottom: 1px solid #f1f5f9;
        }
        #cnTable tbody tr:last-child td { border-bottom: none; }
        #cnTable tbody tr:hover td     { background: #f8fafc; }

        /* ── Credit note number link ── */
        .cn-link        { color: #2D7080; font-weight: 500; }
        .cn-link:hover  { color: #0B2638; text-decoration: underline; }

        /* ── Amount negative ── */
        .cn-amount { color: #b91c1c; font-weight: 700; }

        /* ── Type badge ── */
        .inv-type-badge {
            display: inline-flex; align-items: center;
            padding: 2px 8px; border-radius: 99px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }

        /* ── Action buttons ── */
        .act-btn {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 4px; height: 26px; padding: 0 10px; border-radius: 6px;
            flex-shrink: 0; cursor: pointer; font-size: 11.5px; font-weight: 600;
            white-space: nowrap; transition: background .15s, border-color .15s;
            outline: none; line-height: 1; text-decoration: none;
        }
        .act-btn-icon    { width: 12px; height: 12px; flex-shrink: 0; }
        .act-btn-chevron { width: 9px;  height: 9px;  flex-shrink: 0; }

        .act-btn-view   { background:#f0fafb; color:#2D7080;  border:1px solid #99d6de; }
        .act-btn-view:hover  { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-send   { background:#f0fafb; color:#2D7080;  border:1px solid #99d6de; }
        .act-btn-send:hover  { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-delete { background:#fff1f2; color:#b91c1c; border:1px solid #fca5a5; }
        .act-btn-delete:hover { background:#fee2e2; border-color:#f87171; }

        /* ── act-row ── */
        .act-row {
            display: flex; flex-wrap: wrap;
            align-items: center; gap: 3px;
            justify-content: flex-start;
        }

        /* ── Filter select ── */
        .dt-filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 14px;
            padding-right: 32px;
        }

        /* ── Mobile card layout (≤ 1023px) ── */
        @media (max-width: 1023px) {
            #cnTable thead { display: none; }
            #cnTable tbody { display: flex; flex-direction: column; gap: 12px; }
            #cnTable tbody tr.cn-row {
                display: grid; grid-template-columns: 1fr 1fr;
                gap: 8px; padding: 12px;
                border: 1px solid rgba(148,163,184,.45); border-radius: 14px;
                background: linear-gradient(160deg,#fff 0%,#f8fafc 100%);
                box-shadow: 0 2px 16px -4px rgba(11,38,56,.10);
            }
            #cnTable tbody tr.cn-row > td {
                display: block; padding: 8px 10px !important;
                border: 1px solid rgba(226,232,240,.85);
                border-radius: 10px; background: rgba(255,255,255,.82);
                white-space: normal !important; word-break: break-word;
            }
            #cnTable tbody tr.cn-row > td[data-span="2"] { grid-column: span 2; }
            #cnTable tbody tr.cn-row > td[data-label]::before {
                content: attr(data-label);
                display: block; font-size: 10px; font-weight: 700;
                letter-spacing: .06em; text-transform: uppercase;
                color: #94a3b8; margin-bottom: 4px;
            }
            #cnTable tbody tr.cn-row > td.cn-toggle-cell[data-label]::before { content: none !important; }
            #cnTable tbody tr.cn-row.is-collapsed > td:not(.cn-toggle-cell) { display: none !important; }
            .cn-chevron { transition: transform 180ms ease; }
            #cnTable tbody tr.cn-row[aria-expanded="true"] .cn-chevron { transform: rotate(180deg); }
        }
    </style>
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden" x-data>

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        <x-dashboard.navbar />

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">

            {{-- ── Page header ── --}}
            @php
                $total = App\Models\credit_note::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_ttc');
                $totalCount = App\Models\credit_note::where('entreprise_id', Auth::user()->entreprise_id)->count();
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Invoices') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Credit Note History') }}
                    </h1>
                    <p class="text-[12px] text-slate-500 mt-0.5 hidden sm:block">{{ __('messages.Manage and track all credit notes') }}</p>
                </div>
                <div class="flex-shrink-0" x-data="datePicker()">
                    <button @click.prevent="updateHref" class="dt-btn dt-btn-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        {{ __('messages.Credit Note Reports PDF') }}
                    </button>
                </div>
            </div>

            {{-- ── Flash messages ── --}}
            @if(session('message'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                    <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-red-500 hover:text-red-700 text-lg leading-none">&times;</button>
                </div>
            @endif

            {{-- ── Stat cards ── --}}
            <div class="flex gap-4 sm:gap-3 mb-4">

                {{-- Total count --}}
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Credit Note History') }}</p>
                        <p class="text-[15px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">{{ $totalCount }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 hidden sm:block">{{ __('messages.Total') }}</p>
                    </div>
                </div>

                {{-- Total amount --}}
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-red-50 ring-1 ring-red-600/15 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4M12 4l-8 8 8 8"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Total Credit Notes:') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-red-600 leading-tight">-€{{ number_format($total, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- ── Date range + filters card ── --}}
            <div class="dt-card p-3 sm:p-4 mb-4" x-data="datePicker()">
                {{-- Date row --}}
                <div class="flex flex-wrap items-end gap-2 sm:gap-3 mb-3">
                    <div class="flex-1 min-w-[130px]">
                        <label for="startDate" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Start Date') }}</label>
                        <input type="date" id="startDate" x-model="startDate"
                               class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                    </div>
                    <div class="flex-1 min-w-[130px]">
                        <label for="endDate" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.End Date') }}</label>
                        <input type="date" id="endDate" x-model="endDate"
                               class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                    </div>
                </div>

                <div class="border-t border-slate-100 mb-3"></div>

                {{-- Filter selects --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 sm:gap-3">
                    <div>
                        <label for="clientFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Credit Note Client') }}</label>
                        <select id="clientFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Clients') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->name }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="projectFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Credit Note Project') }}</label>
                        <select id="projectFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Projects') }}</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->name }}">{{ strtoupper($project->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="typeFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Credit Note Type') }}</label>
                        <select id="typeFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Type') }}</option>
                            <option value="detailed">{{ __('messages.Detailed') }}</option>
                            <option value="partial">{{ __('messages.Partial') }}</option>
                            <option value="full period">{{ __('messages.Period Invoice') }}</option>
                            <option value="partial period">{{ __('messages.Partial') }} {{ __('messages.Period Invoice') }}</option>
                            <option value="partial full">{{ __('messages.Partial') }} {{ __('messages.Full Invoice') }}</option>
                            <option value="full">{{ __('messages.Full') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Table card ── --}}
            <div class="dt-card overflow-hidden">

                {{-- Card header --}}
                <div class="flex items-center justify-between px-4 sm:px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Credit Note History') }}</p>
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
                                {{ $credit_notes->count() }} {{ __('messages.Total') }}
                            </span>
                        </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full" id="cnTable">
                        <thead>
                            <tr>
                                <th class="text-left pl-5 ">{{ __('messages.Credit Note N') }}</th>
                                <th class="text-left">{{ __('messages.Client') }}</th>
                                <th class="text-left">{{ __('messages.Project') }}</th>
                                <th class="text-left">{{ __('messages.Type') }}</th>
                                <th class="text-left w-px whitespace-nowrap">{{ __('messages.VAT') }}</th>
                                <th class="text-left w-px whitespace-nowrap">{{ __('messages.Start Date column') }}</th>
                                <th class="text-left w-px whitespace-nowrap">{{ __('messages.End Date column') }}</th>
                                <th class="text-left">{{ __('messages.Created At') }}</th>
                                <th class="text-right w-px whitespace-nowrap">{{ __('messages.Amount') }}</th>
                                <th class="text-left w-[23rem]">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>

@forelse($credit_notes as $credit_note)
@php
    $creditNoteKind = $credit_note->credit_note_type ?? '';
    $typeLabel = $credit_note->type;
    if (strtolower($credit_note->type) === 'detailed') {
        $typeLabel = 'Detailed';
    } elseif (strtolower($credit_note->type) === 'period') {
        $typeLabel = ($creditNoteKind === 'Partial') ? 'Partial Period' : 'Full Period';
    } elseif (strtolower($credit_note->type) === 'full') {
        $typeLabel = ($creditNoteKind === 'Partial') ? 'Partial Full' : 'Full';
    }

    $typeBadgeClass = match(strtolower($typeLabel)) {
        'detailed'      => 'bg-amber-50   text-amber-700   ring-1 ring-amber-600/15',
        'partial'       => 'bg-violet-50  text-violet-700  ring-1 ring-violet-600/15',
        'full period'   => 'bg-cyan-50    text-cyan-700    ring-1 ring-cyan-600/15',
        'partial period'=> 'bg-blue-50    text-blue-700    ring-1 ring-blue-600/15',
        'partial full'  => 'bg-violet-50  text-violet-700  ring-1 ring-violet-600/15',
        default         => 'bg-slate-100  text-slate-600   ring-1 ring-slate-600/10',
    };

    $pdfRoute = '';
    $destroyRoute = '';
    $sendRoute = '';
    if(Auth::user()->hasRole('Admin')) {
        $pdfRoute     = route('admin.invoices.serveCreditNotePdf', $credit_note->id);
        $destroyRoute = route('admin.credit_note.destroy', $credit_note->id);
        $sendRoute    = route('admin.credit_note.send', $credit_note->id);
    } elseif(Auth::user()->hasRole('Responsable de projet')) {
        $pdfRoute     = route('Responsable_de_projet.invoices.serveCreditNotePdf', $credit_note->id);
        $destroyRoute = route('Responsable_de_projet.credit_note.destroy', $credit_note->id);
    } elseif(Auth::user()->hasRole('Employee')) {
        $pdfRoute     = route('employee.invoices.serveCreditNotePdf', $credit_note->id);
        $destroyRoute = route('employee.credit_note.destroy', $credit_note->id);
    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
        $pdfRoute     = route('Responsable_gestionnaire.invoices.serveCreditNotePdf', $credit_note->id);
        $destroyRoute = route('Responsable_gestionnaire.credit_note.destroy', $credit_note->id);
    }

    $project = App\Models\Project::find($credit_note->project_id);
    $client  = \App\Models\Client::find($credit_note->client_id);
@endphp

<tr class="cn-row" data-type="{{ strtolower($typeLabel) }}" aria-expanded="true">

    {{-- Credit note number --}}
    <td data-label="{{ __('messages.Credit Note N') }}" data-span="2"
        class="pl-5 cn-toggle-cell font-medium" data-cn-toggle>

        {{-- Desktop --}}
        <a href="{{ $pdfRoute }}" target="_blank" class="cn-link text-[12.5px] font-semibold hidden lg:inline">
            NC-{{ $credit_note->created_at->format('ym') }}{{ $credit_note->credit_note_number }}
        </a>

        {{-- Mobile accordion header --}}
        <div class="flex items-center justify-between gap-2 lg:hidden">
            <a href="{{ $pdfRoute }}" target="_blank" class="cn-link text-[12px] font-semibold truncate">
                NC-{{ $credit_note->created_at->format('ym') }}{{ $credit_note->credit_note_number }}
            </a>
            <svg class="cn-chevron w-4 h-4 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </td>

    {{-- Client --}}
    <td data-label="{{ __('messages.Client') }}" class="max-w-[140px]">
        <span class="block truncate text-[12.5px] text-slate-700 font-medium"
              title="{{ $client->name ?? __('messages.N/A') }}">
            {{ $client->name ?? __('messages.N/A') }}
        </span>
    </td>

    {{-- Project --}}
    <td data-label="{{ __('messages.Project') }}" class="max-w-[140px]">
        <span class="block truncate text-[12.5px] text-slate-600"
              title="{{ $credit_note->project_name }}">
            {{ strtoupper($credit_note->project_name) }}
        </span>
    </td>

    {{-- Type badge --}}
    <td data-label="{{ __('messages.Type') }}">
        <span class="inv-type-badge {{ $typeBadgeClass }}">{{ $typeLabel }}</span>
    </td>

    {{-- VAT --}}
    <td data-label="{{ __('messages.VAT') }}" class="text-[12.5px] text-slate-500 whitespace-nowrap">
        {{ $project->tva ?? __('messages.N/A') }}%
    </td>

    {{-- Start date --}}
    <td data-label="{{ __('messages.Start Date column') }}" class="text-[12px] text-slate-500 whitespace-nowrap">
        {{ $credit_note->start_date ? \Carbon\Carbon::parse($credit_note->start_date)->format('d/m/Y') : '—' }}
    </td>

    {{-- End date --}}
    <td data-label="{{ __('messages.End Date column') }}" class="text-[12px] text-slate-500 whitespace-nowrap">
        {{ $credit_note->end_date ? \Carbon\Carbon::parse($credit_note->end_date)->format('d/m/Y') : '—' }}
    </td>

    {{-- Created at --}}
    <td data-label="{{ __('messages.Created At') }}" class="text-[12px] text-slate-500 whitespace-nowrap">
        {{ \Carbon\Carbon::parse($credit_note->created_at)->format('d/m/Y') }}
    </td>

    {{-- Amount --}}
    <td data-label="{{ __('messages.Amount') }}" data-span="2"
        class="text-right pr-4 whitespace-nowrap">
        <span class="cn-amount text-[13.5px]">-€{{ number_format($credit_note->total_ttc, 2, ',', '.') }}</span>
    </td>

    {{-- Actions --}}
    <td data-label="{{ __('messages.Actions') }}" data-span="2"
        class="w-px whitespace-nowrap">
        <div class="act-row pr-3">

            {{-- View / Download --}}
            @if($pdfRoute)
                <a href="{{ $pdfRoute }}" target="_blank" class="act-btn act-btn-view">
                    <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    {{ Auth::user()->hasRole('Admin') ? __('messages.View & Download') : __('messages.View') }}
                </a>
            @endif

            {{-- Resend (Admin only) --}}
            @if(Auth::user()->hasRole('Admin') && $sendRoute)
                <button type="button" class="act-btn act-btn-send"
                        @click="$dispatch('open-modal', { id: 'sendCreditNoteModal', creditNoteId: {{ $credit_note->id }}, invoiceId: {{ $credit_note->invoice_id }} })">
                    <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                    {{ __('messages.Resend') }}
                </button>
            @endif

            {{-- Delete --}}
            @if($destroyRoute)
                <form action="{{ $destroyRoute }}" method="post"
                      onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                    @csrf @method('DELETE')
                    <button type="submit" class="act-btn act-btn-delete">
                        <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        {{ __('messages.Delete Invoice') }}
                    </button>
                </form>
            @endif

        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="10" class="px-5 py-14 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
            </div>
            <p class="text-[13px] font-medium text-slate-500">{{ __('messages.No credit notes found') }}</p>
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
                        <div class="flex items-center gap-1" id="paginationControls"></div>
                    </div>
                </div>
            </div>
            {{-- end table card --}}

            {{-- ── Send Credit Note Modal (Admin only) ── --}}
            @if(Auth::user()->hasRole('Admin'))
            <div x-data="sendCreditNoteModal()"
                 x-show="show"
                 @open-modal.window="if ($event.detail.id === 'sendCreditNoteModal') open($event.detail.creditNoteId, $event.detail.invoiceId)"
                 x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                 class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
                 style="display:none;">

                <div x-show="show" x-transition @click.outside="show = false"
                     class="bg-white rounded-2xl shadow-[0_20px_50px_rgba(15,23,42,0.15)] w-full max-w-lg mx-4">

                    <div class="flex justify-between items-center p-6 border-b border-slate-200">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Send Credit Note') }}</h5>
                        <button @click="show = false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>

                    <form id="sendCreditNoteForm" @submit.prevent="submitForm">
                        @csrf
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.Select Recipients:') }}</label>
                                <div id="recipientsContainer"
                                     class="border border-slate-200 rounded-xl p-3 h-40 overflow-y-auto bg-slate-50 text-sm text-slate-700">
                                    <div class="flex items-center justify-center h-full">
                                        <svg class="animate-spin h-5 w-5 text-[#2D7080]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <label for="fileUpload" class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.Upload Additional Files (Optional):') }}</label>
                                <input type="file" id="fileUpload" name="file[]" multiple
                                       class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#2D7080]/10 file:text-[#2D7080] hover:file:bg-[#2D7080]/20 cursor-pointer border border-slate-200 rounded-xl p-1">
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 p-4 bg-slate-50 border-t border-slate-200 rounded-b-2xl">
                            <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close') }}</button>
                            <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Send Credit Note') }}</button>
                        </div>
                    </form>
                </div>
            </div>
            @endif

        </main>
    </div>

    @livewireScripts

<script>
    // ── Alpine: Send Credit Note Modal ──
    function sendCreditNoteModal() {
        return {
            show: false,
            creditNoteId: null,
            invoiceId: null,

            open(creditNoteId, invoiceId) {
                this.creditNoteId = creditNoteId;
                this.invoiceId    = invoiceId;

                const form = document.getElementById('sendCreditNoteForm');
                if (form) {
                    form.action = '{{ route("admin.credit_note.send", ":id") }}'.replace(':id', creditNoteId);
                }

                const container = document.getElementById('recipientsContainer');
                if (container) {
                    container.innerHTML = '<div class="flex items-center justify-center h-full"><svg class="animate-spin h-5 w-5 text-[#2D7080]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg></div>';

                    fetch(`/admin/invoice/${invoiceId}/recipients`)
                        .then(r => r.json())
                        .then(data => {
                            if (data.message && (!data.recipients || data.recipients.length === 0)) {
                                container.innerHTML = '<p class="text-sm text-red-600">' + data.message + '</p>';
                            } else if (data.recipients?.length) {
                                container.innerHTML = data.recipients.map(rec => `
                                    <div class="flex items-center mb-2">
                                        <input class="h-4 w-4 text-[#2D7080] border-slate-300 rounded focus:ring-[#2D7080]"
                                               type="checkbox" name="recipient_emails[]"
                                               value="${rec.email}" id="rec_${rec.id}"
                                               ${rec.is_comptable ? 'checked' : ''}>
                                        <label class="ml-2 text-sm text-slate-700" for="rec_${rec.id}">
                                            ${rec.name ? rec.name + ' (' + rec.email + ')' : rec.email}
                                        </label>
                                    </div>`).join('');
                            } else {
                                container.innerHTML = '<p class="text-sm text-slate-500">' + @json(__('messages.No recipients found')) + '</p>';
                            }
                        })
                        .catch(() => {
                            container.innerHTML = '<p class="text-sm text-red-600">' + @json(__('messages.Error loading recipients')) + '</p>';
                        });
                }
                this.show = true;
            },

            submitForm() {
                const form = document.getElementById('sendCreditNoteForm');
                if (!form) return;
                const formData = new FormData(form);
                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    this.show = false;
                    if (data.success) {
                        alert(data.message || @json(__('messages.Credit note sent successfully!')));
                        window.location.reload();
                    } else {
                        alert('Error: ' + (data.message || 'Unknown error'));
                    }
                })
                .catch(() => { alert(@json(__('messages.An error occurred while sending the credit note.'))); });
            }
        };
    }

    // ── Alpine: Date picker for PDF report ──
    function datePicker() {
        return {
            startDate: '',
            endDate: '',
            updateHref() {
                let base = '';
                @if(Auth::user()->hasRole('Responsable de projet'))       base = "{{ route('Responsable_de_projet.creditNote.downloadRepports') }}"; @endif
                @if(Auth::user()->hasRole('Admin'))                        base = "{{ route('admin.creditNote.downloadRepports') }}"; @endif
                @if(Auth::user()->hasRole('Employee'))                     base = "{{ route('employee.creditNote.downloadRepports') }}"; @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) base = "{{ route('Responsable_gestionnaire.creditNote.downloadRepports') }}"; @endif
                let url = base;
                if (this.startDate && this.endDate) url += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                window.location.href = url;
            }
        };
    }

    // ── Filter + pagination ──
    document.addEventListener('DOMContentLoaded', function () {

        let currentPage  = 1;
        let perPage      = 10;
        let filteredRows = [];

        function getDataRows() {
            return Array.from(document.querySelectorAll('#cnTable tbody tr.cn-row'));
        }

        function filterTable() {
            const cv = (document.getElementById('clientFilter')?.value  || '').toLowerCase();
            const pv = (document.getElementById('projectFilter')?.value || '').toLowerCase();
            const tv = (document.getElementById('typeFilter')?.value    || '').toLowerCase();

            filteredRows = getDataRows().filter(row => {
                const client  = row.querySelector('td:nth-child(2)')?.textContent.trim().toLowerCase() || '';
                const project = row.querySelector('td:nth-child(3)')?.textContent.trim().toLowerCase() || '';
                const type    = (row.dataset.type || '').toLowerCase();

                const typeMatch = tv === '' || (tv === 'partial' ? type.includes('partial') : type === tv);

                return (cv === '' || client.includes(cv))
                    && (pv === '' || project.includes(pv))
                    && typeMatch;
            });

            currentPage = 1;
            applyPagination();
        }

        function applyPagination() {
            getDataRows().forEach(r => r.style.display = 'none');

            const total      = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;
            const end   = Math.min(start + perPage, total);
            filteredRows.forEach((row, i) => { row.style.display = (i >= start && i < end) ? '' : 'none'; });

            const info = document.getElementById('paginationInfo');
            if (info) info.textContent = total === 0 ? '' : `${start + 1}–${end} / ${total}`;

            renderPaginationControls(totalPages);
        }

        function renderPaginationControls(totalPages) {
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
                if (p === '…') {
                    btn.textContent = '…';
                    btn.className = base + ' bg-white border-slate-100 text-slate-400 cursor-default pointer-events-none';
                } else {
                    btn.textContent = p;
                    btn.className = p === currentPage ? active : normal;
                    btn.onclick = () => { currentPage = p; applyPagination(); };
                }
                container.appendChild(btn);
            });

            const next = document.createElement('button');
            next.innerHTML = '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';
            next.className = currentPage === totalPages ? disabled : normal;
            next.onclick = () => { if (currentPage < totalPages) { currentPage++; applyPagination(); } };
            container.appendChild(next);
        }

        ['clientFilter', 'projectFilter', 'typeFilter'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', filterTable);
        });

        document.getElementById('perPageSelect')?.addEventListener('change', function () {
            perPage = parseInt(this.value) || 10;
            currentPage = 1;
            applyPagination();
        });

        filterTable();

        // ── Mobile accordion ──
        const mql = window.matchMedia('(max-width: 1023px)');
        function setCollapsed(row, collapsed) {
            row.classList.toggle('is-collapsed', collapsed);
            row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }
        function initAccordions() {
            document.querySelectorAll('#cnTable tbody tr.cn-row')
                .forEach(r => setCollapsed(r, mql.matches));
        }
        document.addEventListener('click', function (e) {
            const cell = e.target.closest('[data-cn-toggle]');
            if (!cell) return;
            if (e.target.closest('a')) return;
            const row = cell.closest('tr.cn-row');
            if (!row) return;
            setCollapsed(row, !row.classList.contains('is-collapsed'));
        });
        (mql.addEventListener ? mql.addEventListener('change', initAccordions) : mql.addListener(initAccordions));
        initAccordions();
    });
</script>
    {{-- Searchable filter selects (Tom Select). Syncs the original selects and
         fires their change events, so existing filter logic keeps working. --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['clientFilter', 'projectFilter', 'typeFilter'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el && window.TomSelect && !el.tomselect) {
                    new TomSelect(el, {
                        create: false,
                        sortField: null,
                        maxOptions: null,
                        allowEmptyOption: true,
                        placeholder: el.options[0] ? el.options[0].text : ''
                    });
                }
            });
        });
    </script>
</body>
</html>
