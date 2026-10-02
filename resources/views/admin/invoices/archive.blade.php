<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Invoices Archive - DalyTrac') }}</title>

    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        /* ── Table base ── */
        #archiveTable th {
            font-size: 11px; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #64748b;
            padding: 10px 12px; white-space: nowrap;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        #archiveTable td {
            padding: 9px 12px; font-size: 13px;
            vertical-align: middle; border-bottom: 1px solid #f1f5f9;
        }
        #archiveTable tbody tr:last-child td { border-bottom: none; }
        #archiveTable tbody tr:hover td { background: #f8fafc; }
        #archiveTable tbody tr.row-credit-note td { background: rgba(254,251,235,.5); }
        #archiveTable tbody tr.row-credit-note:hover td { background: rgba(253,246,214,.7); }

        /* ── Type badge ── */
        .inv-type-badge {
            display: inline-flex; align-items: center; gap: 4px;
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
        .act-btn-icon { width: 12px; height: 12px; flex-shrink: 0; }
        .act-btn-chevron { width: 9px; height: 9px; flex-shrink: 0; }

        .act-btn-paid       { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
        .act-btn-paid:hover { background:#dcfce7; border-color:#4ade80; }

        .act-btn-unpaid       { background:#fff1f2; color:#b91c1c; border:1px solid #fca5a5; }
        .act-btn-unpaid:hover { background:#fee2e2; border-color:#f87171; }

        .act-btn-unarchive       { background:#f0fafb; color:#2D7080; border:1px solid #99d6de; }
        .act-btn-unarchive:hover { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-send       { background:#f0fafb; color:#2D7080; border:1px solid #99d6de; }
        .act-btn-send:hover { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-remind       { background:#fffbeb; color:#92400e; border:1px solid #fcd34d; }
        .act-btn-remind:hover { background:#fef3c7; border-color:#f59e0b; }

        .act-btn-credit       { background:#f0f4f8; color:#0B2638; border:1px solid #b0bec5; }
        .act-btn-credit:hover { background:#e2eaf0; border-color:#78909c; }

        /* ── Filter select ── */
        .dt-filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 14px;
            padding-right: 32px;
        }

        /* ── Stat card clickable ── */
        .stat-card-clickable { cursor: pointer; transition: box-shadow .18s, border-color .18s; }
        .stat-card-clickable:hover { box-shadow: 0 4px 24px -4px rgba(11,38,56,.18); border-color: #c9d9da; }

        /* ── act-row ── */
        .act-row {
            display: flex; flex-wrap: wrap;
            align-items: center; gap: 3px;
            justify-content: flex-start;
        }

        /* ── Mobile card layout (≤ 1023px) ── */
        @media (max-width: 1023px) {
            #archiveTable thead { display: none; }
            #archiveTable tbody { display: flex; flex-direction: column; gap: 12px; }
            #archiveTable tbody tr.archive-row {
                display: grid; grid-template-columns: 1fr 1fr;
                gap: 8px; padding: 12px;
                border: 1px solid rgba(148,163,184,.45); border-radius: 14px;
                background: linear-gradient(160deg,#fff 0%,#f8fafc 100%);
                box-shadow: 0 2px 16px -4px rgba(11,38,56,.10);
            }
            #archiveTable tbody tr.archive-row > td {
                display: block; padding: 8px 10px !important;
                border: 1px solid rgba(226,232,240,.85);
                border-radius: 10px; background: rgba(255,255,255,.82);
                white-space: normal !important; word-break: break-word;
            }
            #archiveTable tbody tr.archive-row > td[data-span="2"] { grid-column: span 2; }
            #archiveTable tbody tr.archive-row > td[data-label]::before {
                content: attr(data-label);
                display: block; font-size: 10px; font-weight: 700;
                letter-spacing: .06em; text-transform: uppercase;
                color: #94a3b8; margin-bottom: 4px;
            }
            #archiveTable tbody tr.archive-row > td.archive-toggle-cell[data-label]::before { content: none !important; }
            #archiveTable tbody tr.archive-row.is-collapsed > td:not(.archive-toggle-cell) { display: none !important; }
            .archive-chevron { transition: transform 180ms ease; }
            #archiveTable tbody tr.archive-row[aria-expanded="true"] .archive-chevron { transform: rotate(180deg); }
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
                $returnRoute = '';
                if(Auth::user()->hasRole('Admin'))
                    $returnRoute = route('admin.invoices.invoicehistory');
                elseif(Auth::user()->hasRole('Responsable de projet'))
                    $returnRoute = route('Responsable_de_projet.invoices.invoicehistory');
                elseif(Auth::user()->hasRole('Employee'))
                    $returnRoute = route('employee.invoices.invoicehistory');
                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    $returnRoute = route('Responsable_gestionnaire.invoices.invoicehistory');

                $totalPaid  = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->where('archived', 1)->sum('total_ttc');
                $totalTva   = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 1)->sum('total_tva');
                $totalCount = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('archived', 1)->count();
            @endphp

            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Invoices') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Invoices Archive') }}
                    </h1>
                    <p class="text-[12px] text-slate-500 mt-0.5 hidden sm:block">{{ __('messages.Manage and track all your invoices in one place') }}</p>
                </div>
                <div class="flex-shrink-0">
                    @if($returnRoute)
                        <a href="{{ $returnRoute }}" class="dt-btn dt-btn-ghost text-[12px]">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            {{ __('messages.Return Back') }}
                        </a>
                    @endif
                </div>
            </div>

            {{-- ── Flash messages ── --}}
            @if(session('message'))
                <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-5 relative" role="alert">
                    <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="text-sm font-medium">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-emerald-500 hover:text-emerald-700 text-lg leading-none">&times;</button>
                </div>
            @endif
            @if(session('error'))
                <div class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-5 relative" role="alert">
                    <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="text-sm font-medium">{{ session('error') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-red-500 hover:text-red-700 text-lg leading-none">&times;</button>
                </div>
            @endif

            {{-- ── Stat cards ── --}}
            <div class="grid grid-cols-3 gap-2 sm:gap-3 mb-4">

                {{-- Total archived --}}
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2D7080]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.Invoices Archive') }}</p>
                        <p class="text-[15px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">{{ $totalCount }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 hidden sm:block">{{ __('messages.Total') }}</p>
                    </div>
                </div>

                {{-- Total paid --}}
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-600/15 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL PAID') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-emerald-600 leading-tight">€{{ number_format($totalPaid, 2, ',', '.') }}</p>
                    </div>
                </div>

                {{-- Total VAT --}}
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#E7B84B]/15 ring-1 ring-[#E7B84B]/30 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#c9971f]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL TVA') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">€{{ number_format($totalTva, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- ── Filters ── --}}
            <div class="dt-card p-3 sm:p-4 mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3">
                    <div>
                        <label for="clientFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Client') }}</label>
                        <select id="clientFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Clients') }}</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->name }}">{{ $client->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="projectFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Project') }}</label>
                        <select id="projectFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Projects') }}</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->name }}">{{ strtoupper($project->name) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="typeFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Type') }}</label>
                        <select id="typeFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Invoices') }}</option>
                            <option value="partial">{{ __('messages.Partial Invoice') }}</option>
                            <option value="percentage">{{ __('messages.Percentage Invoice') }}</option>
                            <option value="period">{{ __('messages.Period Invoice') }}</option>
                            <option value="detailed">{{ __('messages.Detailed Invoice') }}</option>
                            <option value="full">{{ __('messages.Full Invoice') }}</option>
                        </select>
                    </div>
                    <div>
                        <label for="dateSort" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Sort by Date') }}</label>
                        <select id="dateSort" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.Default') }}</option>
                            <option value="desc">{{ __('messages.Descending') }}</option>
                            <option value="asc">{{ __('messages.Ascending') }}</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- ── Archive table card ── --}}
            <div class="dt-card overflow-hidden">

                {{-- Card header --}}
                <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Invoices Archive') }}</p>
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
                        {{ $invoices->count() }} {{ __('messages.Total') }}
                    </span>
                </div>
                </div>

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table class="w-full" id="archiveTable">
                        <thead>
                            <tr>
                                <th class="text-left pl-5">{{ __('messages.Invoice N°') }}</th>
                                <th class="text-left">{{ __('messages.Client') }}</th>
                                <th class="text-left">{{ __('messages.Project') }}</th>
                                <th class="text-left whitespace-nowrap w-1">{{ __('messages.Type') }}</th>
                                <th class="text-left whitespace-nowrap w-1">{{ __('messages.VAT') }}</th>
                                <th class="text-left">{{ __('messages.Created At') }}</th>
                                <th class="text-left w-10 ">{{ __('messages.Payment Date') }}</th>
                                <th class="text-right w-10">{{ __('messages.Total Cost') }}</th>
                                <th class="text-left w-[19rem]">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>

@forelse($invoices as $invoice)
@php
    $isPaid       = $invoice->Payment_status == 1;
    $isCreditNote = $invoice->Payment_status == -1;

    $pdfRoute = '#';
    if(Auth::user()->hasRole('Admin'))
        $pdfRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
    elseif(Auth::user()->hasRole('Responsable de projet'))
        $pdfRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
    elseif(Auth::user()->hasRole('Employee'))
        $pdfRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]);
    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        $pdfRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]);

    $deactivateRoute = $activateRoute = $unarchiveRoute = '';
    $reminderRouteBase = $creditNoteRoute = $detailedCreditNoteRoute = '';
    $sendRoute = '';

    if(Auth::user()->hasRole('Admin')) {
        $deactivateRoute        = route('admin.invoice.deactivate', $invoice->id);
        $activateRoute          = route('admin.invoice.activate', $invoice->id);
        $unarchiveRoute         = route('admin.invoice.unarchive', $invoice->id);
        $reminderRouteBase      = 'admin.invoice.sendManualremind';
        $creditNoteRoute        = 'admin.invoice.creditNote';
        $detailedCreditNoteRoute= 'admin.invoice.detailedCreditNote';
        $sendRoute              = route('admin.invoice.sendInvoice', $invoice->id);
    } elseif(Auth::user()->hasRole('Responsable de projet')) {
        $deactivateRoute        = route('Responsable_de_projet.invoice.deactivate', $invoice->id);
        $activateRoute          = route('Responsable_de_projet.invoice.activate', $invoice->id);
        $unarchiveRoute         = route('Responsable_de_projet.invoice.unarchive', $invoice->id);
        $reminderRouteBase      = 'Responsable_de_projet.invoice.sendManualremind';
        $creditNoteRoute        = 'Responsable_de_projet.invoice.creditNote';
        $detailedCreditNoteRoute= 'Responsable_de_projet.invoice.detailedCreditNote';
        $sendRoute              = route('Responsable_de_projet.invoice.sendInvoice', $invoice->id);
    } elseif(Auth::user()->hasRole('Employee')) {
        $deactivateRoute        = route('employee.invoice.deactivate', $invoice->id);
        $activateRoute          = route('employee.invoice.activate', $invoice->id);
        $unarchiveRoute         = route('employee.invoice.unarchive', $invoice->id);
        $reminderRouteBase      = 'employee.invoice.sendManualremind';
        $creditNoteRoute        = 'employee.invoice.creditNote';
        $detailedCreditNoteRoute= 'employee.invoice.detailedCreditNote';
        $sendRoute              = route('employee.invoice.sendInvoice', $invoice->id);
    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
        $deactivateRoute        = route('Responsable_gestionnaire.invoice.deactivate', $invoice->id);
        $activateRoute          = route('Responsable_gestionnaire.invoice.activate', $invoice->id);
        $unarchiveRoute         = route('Responsable_gestionnaire.invoice.unarchive', $invoice->id);
        $reminderRouteBase      = 'Responsable_gestionnaire.invoice.sendManualremind';
        $creditNoteRoute        = 'Responsable_gestionnaire.invoice.creditNote';
        $detailedCreditNoteRoute= 'Responsable_gestionnaire.invoice.detailedCreditNote';
        $sendRoute              = route('Responsable_gestionnaire.invoice.sendInvoice', $invoice->id);
    }

    $project = App\Models\Project::find($invoice->project_id);
    $client  = \App\Models\Client::find($invoice->client_id);

    $typeBadgeClass = match(strtolower($invoice->type ?? '')) {
        'partial'    => 'bg-violet-50  text-violet-700  ring-1 ring-violet-600/15',
        'percentage' => 'bg-blue-50    text-blue-700    ring-1 ring-blue-600/15',
        'period'     => 'bg-cyan-50    text-cyan-700    ring-1 ring-cyan-600/15',
        'detailed'   => 'bg-amber-50   text-amber-700   ring-1 ring-amber-600/15',
        default      => 'bg-slate-100  text-slate-600   ring-1 ring-slate-600/10',
    };
    $typeLabel = $invoice->type;
    if($invoice->type == 'Partial')    $typeLabel .= ' ' . $invoice->partial;
    if($invoice->type == 'Percentage') $typeLabel .= ' ' . $invoice->percentage . '%';
@endphp

<tr
    class="archive-row {{ $isCreditNote ? 'row-credit-note' : '' }}"
    data-payment-status="{{ $isPaid ? 'paid' : ($isCreditNote ? 'credit_note' : 'unpaid') }}"
    aria-expanded="true"
>
    {{-- Invoice number --}}
    <td data-label="{{ __('messages.Invoice N°') }}" data-span="2"
        class="pl-5 archive-toggle-cell font-medium" data-archive-toggle>

        {{-- Desktop --}}
        <div class="hidden lg:flex lg:items-center lg:gap-2">
            <a href="{{ $pdfRoute }}" target="_blank"
               class="text-[12.5px] font-semibold text-[#2D7080] hover:text-[#0B2638] hover:underline">
                {{ $entreprise_infos->invoice_prefix }}-{{ date('ym', strtotime($invoice->created_at)) }}{{ $invoice->number }}
            </a>
            @if($isCreditNote)
                <span class="dt-badge dt-badge-accent text-[10px]">{{ __('messages.Credit Note') }}</span>
            @endif
        </div>

        {{-- Mobile accordion header --}}
        <div class="flex items-center justify-between gap-2 lg:hidden">
            <div class="flex items-center gap-2 min-w-0">
                <a href="{{ $pdfRoute }}" target="_blank"
                   class="text-[12px] font-semibold text-[#2D7080] hover:underline truncate">
                    {{ $entreprise_infos->invoice_prefix }}-{{ date('ym', strtotime($invoice->created_at)) }}{{ $invoice->number }}
                </a>
                @if($isCreditNote)
                    <span class="dt-badge dt-badge-accent text-[10px]">{{ __('messages.Credit Note') }}</span>
                @endif
            </div>
            <svg class="archive-chevron w-4 h-4 text-slate-400 flex-shrink-0" viewBox="0 0 24 24" fill="none">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
        </div>
    </td>

    {{-- Client --}}
    <td data-label="{{ __('messages.Client') }}" class="max-w-[150px]">
        <span class="block truncate text-[12.5px] text-slate-700 font-medium"
              title="{{ $client->name ?? __('messages.N/A') }}">
            {{ $client->name ?? __('messages.N/A') }}
        </span>
    </td>

    {{-- Project --}}
    <td data-label="{{ __('messages.Project') }}" class="max-w-[150px]">
        <span class="block truncate text-[12.5px] text-slate-600"
              title="{{ $invoice->project_name }}">
            {{ strtoupper($invoice->project_name) }}
        </span>
    </td>

    {{-- Type --}}
    <td data-label="{{ __('messages.Type') }}">
        <span class="inv-type-badge {{ $typeBadgeClass }}">{{ $typeLabel }}</span>
    </td>

    {{-- VAT --}}
    <td data-label="{{ __('messages.VAT') }}" class="text-[12.5px] text-slate-500 whitespace-nowrap">
        {{ $project->tva ?? __('messages.N/A') }}%
    </td>

    {{-- Created at --}}
    <td data-label="{{ __('messages.Created At') }}" class="text-[12px] text-slate-500 whitespace-nowrap">
        {{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') }}
    </td>

    {{-- Payment date --}}
    <td data-label="{{ __('messages.Payment Date') }}" class="text-[12px] text-slate-500 whitespace-nowrap">
        @if($isPaid && !empty($invoice->payment_date))
            {{ \Carbon\Carbon::parse($invoice->payment_date)->format('d/m/Y') }}
        @else
            <span class="text-slate-300">—</span>
        @endif
    </td>

    {{-- Amount --}}
    <td data-label="{{ __('messages.Total Cost') }}" data-span="2"
        class="text-right pr-4 whitespace-nowrap">
        <span class="text-[13.5px] font-bold {{ $isPaid ? 'text-emerald-600' : ($isCreditNote ? 'text-amber-600' : 'text-[#0B2638]') }}">
            €{{ number_format($invoice->total_ttc, 2, ',', ' ') }}
        </span>
    </td>

    {{-- Actions --}}
    <td data-label="{{ __('messages.Actions') }}" data-span="2"
        class="w-px whitespace-nowrap">
        <div class="flex flex-col gap-1 pr-3">

            {{-- Row 1: payment status + unarchive + send --}}
            <div class="act-row">

                {{-- Payment status --}}
                @if($isCreditNote)
                    <span class="act-btn act-btn-credit" style="pointer-events:none;">
                        <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        {{ __('messages.Credit Note') }}
                    </span>
                @elseif($isPaid)
                    <form action="{{ $deactivateRoute }}" method="post"
                          onsubmit="return confirm('{{ __('messages.Change payment status?') }}');">
                        @csrf
                        <button type="submit" class="act-btn act-btn-paid">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ __('messages.Paid') }}
                        </button>
                    </form>
                @else
                    <form action="{{ $activateRoute }}" method="post"
                          onsubmit="return confirm('{{ __('messages.Change payment status?') }}');">
                        @csrf
                        <button type="submit" class="act-btn act-btn-unpaid">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                            {{ __('messages.Unpaid') }}
                        </button>
                    </form>
                @endif

                {{-- Unarchive --}}
                @if($unarchiveRoute)
                    <form action="{{ $unarchiveRoute }}" method="post"
                          onsubmit="return confirm('{{ __('messages.Unarchive this invoice?') }}');">
                        @csrf
                        <button type="submit" class="act-btn act-btn-unarchive">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                            {{ __('messages.Unarchive') }}
                        </button>
                    </form>
                @endif

                {{-- Send / Resend --}}
                @can('Send invoice')
                    <button type="button"
                            class="act-btn act-btn-send"
                            @click="$dispatch('open-send-modal', { invoiceId: {{ $invoice->id }} })">
                        <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ $invoice->sentInvoice == 0 ? __('messages.Send') : __('messages.Resend') }}
                    </button>
                @endcan
            </div>

            {{-- Row 2: reminder + credit note (unpaid only) --}}
            @if(!$isPaid && !$isCreditNote)
                <div class="act-row">

                    @can('Send invoice')
                        {{-- Reminder --}}
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button" class="act-btn act-btn-remind">
                                <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                {{ __('messages.Reminder') }}
                                <svg class="act-btn-chevron" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <ul x-show="open" @click.outside="open=false" x-transition
                                style="display:none;"
                                class="absolute left-0 mt-1.5 w-44 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 z-50 py-1">
                                <li><a class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1"
                                       href="{{ route($reminderRouteBase, ['id'=>$invoice->id,'reminder'=>1]) }}">{{ __('messages.Send Reminder 1') }}</a></li>
                                <li><a class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1"
                                       href="{{ route($reminderRouteBase, ['id'=>$invoice->id,'reminder'=>2]) }}">{{ __('messages.Send Reminder 2') }}</a></li>
                                <li><a class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1"
                                       href="{{ route($reminderRouteBase, ['id'=>$invoice->id,'reminder'=>3]) }}">{{ __('messages.Send Reminder 3') }}</a></li>
                            </ul>
                        </div>
                    @endcan

                    {{-- Credit note --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button" class="act-btn act-btn-credit">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            {{ __('messages.Credit Note') }}
                            <svg class="act-btn-chevron" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <ul x-show="open" @click.outside="open=false" x-transition
                            style="display:none;"
                            class="absolute left-0 mt-1.5 w-36 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 z-50 py-1">
                            @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1"
                                       @click.prevent="$dispatch('open-partial-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open=false">{{ __('messages.Partial') }}</a></li>
                                <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full"
                                       class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                            @elseif($creditNoteRoute)
                                <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1"
                                       @click.prevent="$dispatch('open-partial-modal', { invoiceId: {{ $invoice->id }}, totalCost: {{ $invoice->total_ttc }} }); open=false">{{ __('messages.Partial') }}</a></li>
                                <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full"
                                       class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                            @endif
                        </ul>
                    </div>

                </div>
            @endif

        </div>
    </td>
</tr>

@empty
<tr>
    <td colspan="9" class="px-5 py-14 text-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-slate-100 flex items-center justify-center">
                <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <p class="text-[13px] font-medium text-slate-500">{{ __('messages.No invoices found matching your criteria.') }}</p>
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

            {{-- ── Partial Credit Note Modal ── --}}
            <div x-data="{ show: false, invoiceId: null, totalCost: 0, amount: '' }"
                 @open-partial-modal.window="show = true; invoiceId = $event.detail.invoiceId; totalCost = $event.detail.totalCost; amount = ''"
                 x-show="show" x-cloak
                 class="relative z-50">
                <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/50"></div>
                <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                    <div x-show="show" @click.away="show = false"
                         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white rounded-2xl shadow-[0_20px_50px_rgba(15,23,42,0.15)] w-full max-w-md">
                        <div class="flex justify-between items-center p-6 border-b border-slate-200">
                            <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Partial Credit Note') }}</h5>
                            <button @click="show=false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                        </div>
                        <div class="p-6">
                            <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.Enter Amount:') }}</label>
                            <input type="number" x-model="amount" step="0.0001"
                                   class="block w-full rounded-xl border border-slate-300 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] text-sm py-2.5 px-3">
                            <p x-show="amount !== '' && parseFloat(amount) > parseFloat(totalCost)"
                               class="mt-2 text-sm text-red-600">{{ __('messages.Amount exceeds total cost.') }}</p>
                        </div>
                        <div class="flex items-center justify-end gap-2 p-4 bg-slate-50 border-t border-slate-200 rounded-b-2xl">
                            <button @click="show=false" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                            <button type="button" class="dt-btn dt-btn-teal"
                                    @click="
                                        if(amount === '' || parseFloat(amount) <= 0 || parseFloat(amount) > parseFloat(totalCost)) return;
                                        var url = '';
                                        @if(Auth::user()->hasRole('Admin')) url = '{{ route('admin.invoice.creditNote', '') }}/' + invoiceId + '?type=partial&amount=' + amount;
                                        @elseif(Auth::user()->hasRole('Responsable de projet')) url = '{{ route('Responsable_de_projet.invoice.creditNote', '') }}/' + invoiceId + '?type=partial&amount=' + amount;
                                        @elseif(Auth::user()->hasRole('Employee')) url = '{{ route('employee.invoice.creditNote', '') }}/' + invoiceId + '?type=partial&amount=' + amount;
                                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/' + invoiceId + '?type=partial&amount=' + amount;
                                        @endif
                                        if(url) window.location.href = url;
                                    ">
                                {{ __('messages.Submit') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Send Invoice Modal ── --}}
            <div x-data="{ show: false, invoiceId: null }"
                 @open-send-modal.window="show = true; invoiceId = $event.detail.invoiceId; $nextTick(() => { document.getElementById('archiveSendForm').action = buildSendUrl(invoiceId); })"
                 x-show="show" x-cloak
                 class="relative z-50">
                <div x-show="show" x-transition.opacity class="fixed inset-0 bg-black/50"></div>
                <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                    <div x-show="show" @click.away="show=false"
                         x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="bg-white rounded-2xl shadow-[0_20px_50px_rgba(15,23,42,0.15)] w-full max-w-lg">
                        <div class="flex justify-between items-center p-6 border-b border-slate-200">
                            <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Send Invoice') }}</h5>
                            <button @click="show=false" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                        </div>
                        <form id="archiveSendForm" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="p-6 space-y-4">
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">{{ __('messages.Upload Additional Files (Optional):') }}</label>
                                    <input type="file" name="file[]" multiple
                                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-[#2D7080]/10 file:text-[#2D7080] hover:file:bg-[#2D7080]/20 cursor-pointer border border-slate-200 rounded-xl p-1">
                                </div>
                            </div>
                            <div class="flex items-center justify-end gap-2 p-4 bg-slate-50 border-t border-slate-200 rounded-b-2xl">
                                <button type="button" @click="show=false" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                                <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Send') }}</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts

<script>
    // Build send URL per role
    function buildSendUrl(id) {
        var base = '';
        @if(Auth::user()->hasRole('Admin'))
            base = '{{ route("admin.invoice.sendInvoice", ":id") }}';
        @elseif(Auth::user()->hasRole('Responsable de projet'))
            base = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":id") }}';
        @elseif(Auth::user()->hasRole('Employee'))
            base = '{{ route("employee.invoice.sendInvoice", ":id") }}';
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            base = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":id") }}';
        @endif
        return base.replace(':id', id);
    }

    // ── Filter + sort + pagination ──
    document.addEventListener('DOMContentLoaded', function () {

        let currentPage = 1;
        let perPage     = 10;
        let filteredRows = [];

        function getDataRows() {
            return Array.from(document.querySelectorAll('#archiveTable tbody tr.archive-row'));
        }

        function filterAndSort() {
            const cv  = (document.getElementById('clientFilter')?.value  || '').toLowerCase();
            const pv  = (document.getElementById('projectFilter')?.value || '').toLowerCase();
            const tv  = (document.getElementById('typeFilter')?.value    || '').toLowerCase();
            const sort= (document.getElementById('dateSort')?.value      || '');

            let rows = getDataRows().filter(row => {
                const client  = row.querySelector('td:nth-child(2)')?.textContent.trim().toLowerCase() || '';
                const project = row.querySelector('td:nth-child(3)')?.textContent.trim().toLowerCase() || '';
                const type    = row.querySelector('td:nth-child(4)')?.textContent.trim().toLowerCase() || '';
                return (cv === '' || client.includes(cv))
                    && (pv === '' || project.includes(pv))
                    && (tv === '' || type.includes(tv));
            });

            if (sort) {
                rows.sort((a, b) => {
                    let dA = a.querySelector('td:nth-child(7)')?.textContent.trim() || '00/00/0000';
                    let dB = b.querySelector('td:nth-child(7)')?.textContent.trim() || '00/00/0000';
                    // dates are d/m/Y — convert to Y-m-d for comparison
                    const toIso = d => { const p = d.split('/'); return p.length === 3 ? `${p[2]}-${p[1]}-${p[0]}` : '0000-00-00'; };
                    dA = toIso(dA); dB = toIso(dB);
                    return sort === 'asc' ? dA.localeCompare(dB) : dB.localeCompare(dA);
                });
                const tbody = document.querySelector('#archiveTable tbody');
                rows.forEach(r => tbody.appendChild(r));
            }

            filteredRows = rows;
            currentPage  = 1;
            applyPagination();
        }

        function applyPagination() {
            getDataRows().forEach(r => r.style.display = 'none');

            const total      = filteredRows.length;
            const totalPages = Math.max(1, Math.ceil(total / perPage));
            if (currentPage > totalPages) currentPage = totalPages;

            const start = (currentPage - 1) * perPage;
            const end   = Math.min(start + perPage, total);

            filteredRows.forEach((row, i) => {
                row.style.display = (i >= start && i < end) ? '' : 'none';
            });

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

        ['clientFilter', 'projectFilter', 'typeFilter', 'dateSort'].forEach(id => {
            document.getElementById(id)?.addEventListener('change', filterAndSort);
        });

        document.getElementById('perPageSelect')?.addEventListener('change', function () {
            perPage = parseInt(this.value) || 10;
            currentPage = 1;
            applyPagination();
        });

        filterAndSort();

        // ── Mobile accordion ──
        const mql = window.matchMedia('(max-width: 1023px)');
        function setCollapsed(row, collapsed) {
            row.classList.toggle('is-collapsed', collapsed);
            row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }
        function initAccordions() {
            document.querySelectorAll('#archiveTable tbody tr.archive-row')
                .forEach(r => setCollapsed(r, mql.matches));
        }
        document.addEventListener('click', function (e) {
            const cell = e.target.closest('[data-archive-toggle]');
            if (!cell) return;
            if (e.target.closest('a')) return;
            const row = cell.closest('tr.archive-row');
            if (!row) return;
            setCollapsed(row, !row.classList.contains('is-collapsed'));
        });
        (mql.addEventListener ? mql.addEventListener('change', initAccordions) : mql.addListener(initAccordions));
        initAccordions();
    });
</script>
</body>
</html>
