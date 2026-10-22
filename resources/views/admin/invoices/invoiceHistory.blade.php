<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Invoices History - DalyTrac') }}</title>

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
        #invoicesTable th {
            font-size: 11px; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #64748b;
            padding: 10px 12px; white-space: nowrap;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        #invoicesTable td {
            padding: 9px 12px; font-size: 13px;
            vertical-align: middle; border-bottom: 1px solid #f1f5f9;
        }
        #invoicesTable tbody tr:last-child td { border-bottom: none; }
        #invoicesTable tbody tr:hover td { background: #f8fafc; }
        #invoicesTable tbody tr.row-sent td { background: rgba(236,253,245,.55); }
        #invoicesTable tbody tr.row-sent:hover td { background: rgba(209,250,229,.7); }
        #invoicesTable tbody tr[data-payment-status="credit_note"] td { background: rgba(254,251,235,.5); }
        #invoicesTable tbody tr[data-payment-status="credit_note"]:hover td { background: rgba(253,246,214,.7); }

        .invoice-link-overdue {
            color: #dc2626; /* red-600 */
            font-weight: 600; /* semibold */
        }
        .invoice-link-overdue:hover {
            color: #991b1b; /* red-800 */
        }
        .invoice-link-normal {
            color: #2D7080; /* brand-secondary */
            font-weight: 600;
        }
        .invoice-link-normal:hover {
            color: #0B2638; /* brand-primary */
            text-decoration: underline;
        }

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

        .act-btn-send       { background:#f0fafb; color:#2D7080; border:1px solid #99d6de; }
        .act-btn-send:hover { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-remind       { background:#fffbeb; color:#92400e; border:1px solid #fcd34d; }
        .act-btn-remind:hover { background:#fef3c7; border-color:#f59e0b; }

        .act-btn-credit       { background:#f0f4f8; color:#0B2638; border:1px solid #b0bec5; }
        .act-btn-credit:hover { background:#e2eaf0; border-color:#78909c; }

        .act-btn-edit       { background:#f0fafb; color:#2D7080; border:1px solid #99d6de; }
        .act-btn-edit:hover { background:#e0f2f1; border-color:#2D7080; }

        .act-btn-delete       { background:#dc2626; color:#fff; border:1px solid #dc2626; }
        .act-btn-delete:hover { background:#b91c1c; border-color:#b91c1c; }

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

        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0;
            padding: 0;
        }

        /* Mobile/tablet: show invoice rows as stacked cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .table-responsive {
                overflow-x: visible !important;
            }

            #invoicesTable thead {
                display: none;
            }

            #invoicesTable tbody {
                display: block;
                width: 100%;
            }

            #invoicesTable tbody tr.invoice-row {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px;
                margin-bottom: 12px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.45);
                border-radius: 14px;
                background: linear-gradient(160deg, #fff 0%, #f8fafc 100%);
                box-shadow: 0 2px 16px -4px rgba(11,38,56,.10);
            }
            #invoicesTable tbody td {
                display: block;
                width: 100%;
                padding: 8px 10px !important;
                border: 1px solid rgba(226, 232, 240, 0.85);
                border-radius: 10px;
                background: rgba(255,255,255,0.82);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }

            #invoicesTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #invoicesTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 10px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #94a3b8;
                margin-bottom: 4px;
            }

            /* Center action buttons on mobile, wrapping to avoid overflow */
            #invoicesTable tbody td.invoice-actions-cell .act-row {
                justify-content: center !important;
                flex-wrap: wrap !important;
                gap: 8px !important;
            }

            #invoicesTable tbody td.invoice-actions-cell form,
            #invoicesTable tbody td.invoice-actions-cell button,
            #invoicesTable tbody td.invoice-actions-cell a {
                flex: 0 0 auto;
            }

            #invoicesTable tbody td.invoice-actions-cell .payment-button {
                padding-left: 10px;
                padding-right: 10px;
            }

            /* Mobile accordion: collapsed invoice shows only invoice number */
            #invoicesTable tbody tr.invoice-row.is-collapsed td:not(.invoice-toggle-cell) {
                display: none !important;
            }
            #invoicesTable tbody tr.invoice-row td.invoice-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            /* We render the "Invoice N°" label inline in markup for this cell */
            #invoicesTable tbody tr.invoice-row td.invoice-toggle-cell[data-label]::before {
                content: none !important;
            }
            #invoicesTable tbody tr.invoice-row td.invoice-toggle-cell .invoice-chevron {
                transition: transform 180ms ease;
            }
            #invoicesTable tbody tr.invoice-row[aria-expanded="true"] td.invoice-toggle-cell .invoice-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden" x-data>

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">

            {{-- ── Page header ── --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Invoices') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Invoices History') }}
                    </h1>
                    <p class="text-[12px] text-slate-500 mt-0.5 hidden sm:block">{{ __('messages.Manage and track all your invoices in one place') }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                    <button type="button"
                            id="openInvoiceReportModal"
                            class="dt-btn dt-btn-primary text-[12px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        {{ __('messages.Invoices Reports PDF') }}
                    </button>

                    @php $archiveRoute = ''; @endphp
                    @if(Auth::user()->hasRole('Admin')) @php $archiveRoute = route('admin.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) @php $archiveRoute = route('Responsable_de_projet.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Employee')) @php $archiveRoute = route('employee.invoiceArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $archiveRoute = route('Responsable_gestionnaire.invoiceArchived.archiveIndex'); @endphp @endif

                    @if($archiveRoute)
                    <a href="{{ $archiveRoute }}" class="dt-btn dt-btn-ghost text-[12px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                        {{ __('messages.View Archive') }}
                    </a>
                    @endif
                </div>
            </div>

            {{-- JS Message Container --}}
            <div id="messageContainer" style="display: none;" class="items-center gap-3 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                <strong class="text-[13px] font-bold" id="messageTitle"></strong>
                <span class="block sm:inline text-[13px] font-medium" id="messageText"></span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-lg leading-none opacity-70 hover:opacity-100" aria-label="{{ __('messages.Close') }}">&times;</button>
            </div>
            {{-- Session Message --}}
            @if(session('message'))
            <div id="sessionMessageContainer" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                <span class="text-sm font-medium">{{ session('message') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-emerald-500 hover:text-emerald-700 text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
            </div>
            @endif
            {{-- Session ERROR Message --}}
            @if(session('error'))
            <div id="sessionErrorMessageContainer" class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                <svg class="w-4 h-4 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                <span class="text-sm font-medium">{{ session('error') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-red-500 hover:text-red-700 text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
            </div>
            @endif

            {{-- ── Totals cards ── --}}
            @php
                $currentYear = date('Y');
                $totalPaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->where('Payment_status', 1)->sum('total_ttc');
                $totalUnpaid = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                ->where(function($query) {
                                                    $query->where('Payment_status', 0)->orWhereNull('Payment_status');
                                                })->sum('total_ttc');
                $totalAmount = $totalPaid + $totalUnpaid;
                $totalTva = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)->sum('total_tva');

                // Current year totals
                $totalPaidCurrentYear = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->where('Payment_status', 1)
                                                    ->whereYear('created_at', $currentYear)
                                                    ->sum('total_ttc');
                $totalUnpaidCurrentYear = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->where(function($query) {
                                                        $query->where('Payment_status', 0)->orWhereNull('Payment_status');
                                                    })
                                                    ->whereYear('created_at', $currentYear)
                                                    ->sum('total_ttc');
                $totalAmountCurrentYear = $totalPaidCurrentYear + $totalUnpaidCurrentYear;
                $totalTvaCurrentYear = App\Models\invoices_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                    ->whereYear('created_at', $currentYear)
                                                    ->sum('total_tva');
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 mb-4">
                {{-- Total Paid Card --}}
                <div onclick="filterByStatus('paid')" class="dt-card stat-card-clickable p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-600/15 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL PAID') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-emerald-600 leading-tight">€{{ number_format($totalPaid, 2, ',', '.') }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">{{ $currentYear }}: €{{ number_format($totalPaidCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Total Unpaid Card --}}
                <div onclick="filterByStatus('unpaid')" class="dt-card stat-card-clickable p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-red-50 ring-1 ring-red-600/15 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL UNPAID') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-red-600 leading-tight">€{{ number_format($totalUnpaid, 2, ',', '.') }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">{{ $currentYear }}: €{{ number_format($totalUnpaidCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Total Amount Card --}}
                <div onclick="filterByStatus('')" class="dt-card stat-card-clickable p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#2D7080]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 3a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 12m15 0a2.25 2.25 0 01-2.25 2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 013 12m15 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v6" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL AMOUNT') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">€{{ number_format($totalAmount, 2, ',', '.') }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">{{ $currentYear }}: €{{ number_format($totalAmountCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
                {{-- Total TVA Card --}}
                <div class="dt-card p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#E7B84B]/15 ring-1 ring-[#E7B84B]/30 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#c9971f]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL TVA') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">€{{ number_format($totalTva, 2, ',', '.') }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">{{ $currentYear }}: €{{ number_format($totalTvaCurrentYear, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- ── Filters ── --}}
            <div class="dt-card p-3 sm:p-4 mb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 sm:gap-3">
                    <div>
                        <label for="clientFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Client') }}</label>
                        <select id="clientFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Clients') }}</option>
                            @foreach($clients as $client)
                            <option value="{{$client->name}}">{{$client->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="projectFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Project') }}</label>
                        <select id="projectFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Projects') }}</option>
                            @foreach($projects as $project)
                            <option value="{{$project->name}}">{{strtoupper($project->name)}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="paymentFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Payment') }}</label>
                        <select id="paymentFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Invoices') }}</option>
                            <option value="paid">{{ __('messages.Paid') }}</option>
                            <option value="unpaid">{{ __('messages.Unpaid') }}</option>
                            <option value="overdue">{{ __('messages.Overdue') }}</option>
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
                        <label for="startDate" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Start Date') }}</label>
                        <input type="date" id="startDate" placeholder="{{ __('messages.Start Date placeholder') }}" class="block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                    </div>
                    <div>
                        <label for="endDate" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.End Date') }}</label>
                        <input type="date" id="endDate" placeholder="{{ __('messages.End Date placeholder') }}" class="block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                    </div>
                </div>
            </div>

            {{-- ── Invoices table card ── --}}
            <div class="dt-card overflow-hidden">

                {{-- Card header --}}
                <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Invoices History') }}</p>
                    <div class="flex items-center gap-3 sm:gap-4">
                        {{-- Per-page selector --}}
                        <div class="flex items-center gap-2 text-[12px] text-slate-500">
                            <span class="hidden sm:inline">{{ __('messages.Rows per page') }}:</span>
                            <select id="perPageSelect"
                                    class="dt-filter-select rounded-lg border border-slate-200 bg-white text-[12px] text-slate-700 py-1 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] cursor-pointer">
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
                <div class="table-responsive overflow-x-auto">
                    <table class="w-full" id="invoicesTable">
                        <thead>
                            <tr>
                                <th class="text-left pl-5">{{ __('messages.Invoice N°') }}</th>
                                <th class="text-left">{{ __('messages.Project') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.VAT') }}</th>
                                <th class="text-left">{{ __('messages.Client') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Type') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Created At') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Amount') }}</th>
                                <th class="text-left w-[25rem]">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
@forelse($invoices as $invoice)
<tr
    data-payment-status="{{ $invoice->Payment_status == 1 ? 'paid' : ($invoice->Payment_status == -1 ? 'credit_note' : 'unpaid') }}"
    class="invoice-row {{ $invoice->sentInvoice == 1 ? 'row-sent' : '' }}"
    data-invoice-row
    aria-expanded="true"
>
    <td data-label="{{ __('messages.Invoice N°') }}" data-span="2" class="pl-5 font-medium invoice-toggle-cell" data-invoice-toggle>
        @php
            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
            $today = \Carbon\Carbon::now();
            $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
            $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
            $pdfRoute = '';
            if(Auth::user()->hasRole('Responsable de projet')) $pdfRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
            elseif(Auth::user()->hasRole('Admin')) $pdfRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
            elseif(Auth::user()->hasRole('Employee')) $pdfRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]);
            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $pdfRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]);
        @endphp
        {{-- Desktop: keep original simple link layout --}}
        <a href="{{ $pdfRoute }}" target="_blank" class="{{ $isOverdue ? 'invoice-link-overdue' : 'invoice-link-normal' }} hidden lg:inline text-[12.5px]">
            {{ \App\Support\InvoiceNumbering::reference($entreprise_infos, $invoice, true) }}
            @if($isOverdue)
                <span class="block text-xs">({{ $daysOverdue }} {{ __('messages.Invoice days overdue') }})</span>
            @endif
        </a>

        {{-- Mobile: accordion header (label + number inline + chevron) --}}
        <div class="flex items-start justify-between gap-2 lg:hidden">
            <div class="flex items-start gap-2 min-w-0">
                <span class="text-[10px] font-bold tracking-[.06em] uppercase text-slate-400 whitespace-nowrap">
                    {{ __('messages.Invoice N°') }}
                </span>
                <a href="{{ $pdfRoute }}" target="_blank" class="{{ $isOverdue ? 'invoice-link-overdue' : 'invoice-link-normal' }} truncate text-[12px]">
                    {{ \App\Support\InvoiceNumbering::reference($entreprise_infos, $invoice, true) }}
                    @if($isOverdue)
                        <span class="block text-xs">({{ $daysOverdue }} {{ __('messages.Invoice days overdue') }})</span>
                    @endif
                </a>
            </div>
            <span class="shrink-0 mt-0.5 text-slate-400">
                <svg class="invoice-chevron w-4 h-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </span>
        </div>
    </td>
    <td data-label="{{ __('messages.Project') }}" class="max-w-[150px]">
        <span class="block truncate text-[12.5px] text-slate-700 font-medium" title="{{ $invoice->project_name }}">{{strtoupper($invoice->project_name)}}</span>
    </td>
    <td data-label="{{ __('messages.VAT') }}" class="text-[12.5px] text-slate-500 whitespace-nowrap">
        @php $project = App\Models\Project::find($invoice->project_id); @endphp
        {{$project->tva ?? __('messages.N/A')}}%
    </td>
    <td data-label="{{ __('messages.Client') }}" class="max-w-[150px]">
        @php $client = \App\Models\Client::where('id', $invoice->client_id)->first(); @endphp
        <span class="block truncate text-[12.5px] text-slate-700 font-medium" title="{{ $client->name ?? __('messages.N/A') }}">{{$client->name ?? __('messages.N/A')}}</span>
    </td>
    <td data-label="{{ __('messages.Type') }}">
        @php
            $typeBadgeClass = match(strtolower($invoice->type ?? '')) {
                'partial'    => 'bg-violet-50  text-violet-700  ring-1 ring-violet-600/15',
                'percentage' => 'bg-blue-50    text-blue-700    ring-1 ring-blue-600/15',
                'period'     => 'bg-cyan-50    text-cyan-700    ring-1 ring-cyan-600/15',
                'detailed'   => 'bg-amber-50   text-amber-700   ring-1 ring-amber-600/15',
                default      => 'bg-slate-100  text-slate-600   ring-1 ring-slate-600/10',
            };
        @endphp
        <span class="inv-type-badge {{ $typeBadgeClass }}">@if($invoice->type == 'Partial') {{$invoice->type}}-{{$invoice->partial}}@elseif($invoice->type == 'Percentage') {{$invoice->type}}-{{$invoice->percentage}}@else {{$invoice->type}}@endif</span>
    </td>
    <td data-label="{{ __('messages.Created At') }}" class="text-[12px] text-slate-500 whitespace-nowrap">{{ \Carbon\Carbon::parse($invoice->created_at)->format('Y-m-d') }}</td>

    <td data-label="{{ __('messages.Amount') }}" data-span="2" class="whitespace-nowrap">
        <span class="text-[13.5px] font-bold text-emerald-600">€{{ number_format($invoice->total_ttc, 2, ',', ' ') }}</span>
    </td>

    <td data-label="{{ __('messages.Actions') }}" data-span="2" class="flex flex-wrap gap-1">
        @php
            $isPaid = $invoice->Payment_status == 1;
            $toggleRoute = '';
            if (Auth::user()->hasRole('Admin')) {
                $toggleRoute = $isPaid ? route('admin.invoice.deactivate', $invoice->id) : route('admin.invoice.activate', $invoice->id);
            } elseif (Auth::user()->hasRole('Responsable de projet')) {
                $toggleRoute = $isPaid ? route('Responsable_de_projet.invoice.deactivate', $invoice->id) : route('Responsable_de_projet.invoice.activate', $invoice->id);
            } elseif (Auth::user()->hasRole('Employee')) {
                $toggleRoute = $isPaid ? route('employee.invoice.deactivate', $invoice->id) : route('employee.invoice.activate', $invoice->id);
            } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                $toggleRoute = $isPaid ? route('Responsable_gestionnaire.invoice.deactivate', $invoice->id) : route('Responsable_gestionnaire.invoice.activate', $invoice->id);
            }
            $deleteRoute = '';
            if(Auth::user()->hasRole('Admin')) $deleteRoute = route('admin.invoice.destroy', $invoice->id);
            elseif(Auth::user()->hasRole('Responsable de projet')) $deleteRoute = route('Responsable_de_projet.invoice.destroy', $invoice->id);
            elseif(Auth::user()->hasRole('Employee')) $deleteRoute = route('employee.invoice.destroy', $invoice->id);
            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteRoute = route('Responsable_gestionnaire.invoice.destroy', $invoice->id);
        @endphp

        @if($isPaid)
            {{-- PAID: row 1 – Paid, Credit Note, Send --}}
            
                {{-- Paid --}}
                @if($toggleRoute)
                    <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm(@js(__('messages.Change payment status?')));">
                        @csrf
                        <button type="submit"
                                title="{{ __('messages.Mark as Unpaid') }}"
                                class="payment-button act-btn act-btn-paid">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            {{ __('messages.Paid') }}
                        </button>
                    </form>
                @endif

                {{-- Credit Note --}}
                @php
                    $creditNoteRoute = '';
                    $detailedCreditNoteRoute = '';
                    if(Auth::user()->hasRole('Admin')) {
                        $creditNoteRoute = 'admin.invoice.creditNote';
                        $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote';
                    } elseif(Auth::user()->hasRole('Responsable de projet')) {
                        $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote';
                        $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote';
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $creditNoteRoute = 'employee.invoice.creditNote';
                        $detailedCreditNoteRoute = 'employee.invoice.detailedCreditNote';
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $creditNoteRoute = 'Responsable_gestionnaire.invoice.creditNote';
                        $detailedCreditNoteRoute = 'Responsable_gestionnaire.invoice.detailedCreditNote';
                    }
                @endphp
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" type="button"
                            title="{{ __('messages.Credit Note') }}"
                            class="act-btn act-btn-credit">
                        <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                        {{ __('messages.Credit Note') }}
                        <svg class="act-btn-chevron" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <ul x-show="open"
                        @click.outside="open = false"
                        x-transition
                        style="display: none;"
                        class="absolute right-0 mt-1.5 w-40 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 focus:outline-none z-50 py-1">
                        @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1"
                                   onclick="event.preventDefault(); showPartialDetailedPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">{{ __('messages.Partial') }}</a></li>
                            <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full"
                                   class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                        @elseif($creditNoteRoute)
                            <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1"
                                   onclick="event.preventDefault(); showPartialPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">{{ __('messages.Partial') }}</a></li>
                            <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full"
                                   class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                        @endif
                    </ul>
                </div>

                {{-- Send (Email + Peppol Combined) --}}
                @can('Send invoice')
                    <button type="button"
                            title="{{ __('messages.Send Invoice') }}"
                            class="act-btn act-btn-send"
                            onclick="event.preventDefault(); if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); else setTimeout(function(){ if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); }, 100);">
                        <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        {{ __('messages.Send') }}
                    </button>
                @endcan
          
            {{-- PAID row 2: Delete (first row only) --}}
            @if($loop->first)
                @can('Delete invoice')
                    @if($deleteRoute)
                    <div class="act-row" style="padding-top: 3px;">
                        <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm(@js(__('messages.Delete this invoice? This cannot be undone.')));">
                            @csrf
                            @method('DELETE')
                            <button type="submit" title="{{ __('messages.Delete Invoice') }}" class="act-btn act-btn-delete">
                                <svg xmlns="http://www.w3.org/2000/svg" class="act-btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                {{ __('messages.Delete') }}
                            </button>
                        </form>
                    </div>
                    @endif
                @endcan
            @endif
        @else
            {{-- UNPAID: two compact rows --}}
                
                    {{-- Status --}}
                    @if($toggleRoute)
                        <form action="{{ $toggleRoute }}" method="post" onsubmit="return confirm(@js(__('messages.Change payment status?')));">
                            @csrf
                            <button type="submit"
                                    title="{{ __('messages.Mark as Paid') }}"
                                    class="payment-button act-btn act-btn-unpaid">
                                <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                                {{ __('messages.Unpaid') }}
                            </button>
                        </form>
                    @endif

                    {{-- Send (Email + Peppol Combined) --}}
                    @can('Send invoice')
                        <button type="button"
                                title="{{ __('messages.Send Invoice') }}"
                                class="act-btn act-btn-send"
                                onclick="event.preventDefault(); if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); else setTimeout(function(){ if(window.openCombinedSendModal) window.openCombinedSendModal({{ $invoice->id }}, '{{ strtolower($invoice->type ?? 'full') }}'); }, 100);">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            {{ __('messages.Send') }}
                        </button>
                    @endcan
                    {{-- Reminder --}}
                    @can('Send invoice')
                        @php
                            $reminderLabel = __('messages.Reminder');
                            if (!$invoice->first_reminder_date) {
                                $reminderLabel = __('messages.Reminder 1');
                            } elseif (!$invoice->second_reminder_date) {
                                $reminderLabel = __('messages.Reminder 2');
                            } elseif (!$invoice->third_reminder_date) {
                                $reminderLabel = __('messages.Reminder 3');
                            }
                        @endphp
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" type="button"
                                    title="{{ __('messages.Send Reminder') }}"
                                    class="act-btn act-btn-remind">
                                <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                {{ $reminderLabel }}
                                <svg class="act-btn-chevron" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <ul x-show="open"
                                @click.outside="open = false"
                                x-transition
                                style="display: none;"
                                class="absolute right-0 mt-1.5 w-48 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 focus:outline-none z-50 py-1">
                                @if(!$invoice->first_reminder_date)
                                    <li><a class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : 'employee.invoice.sendManualremind'))), ['id' => $invoice->id, 'reminder' => 1]) }}">{{ __('messages.Send Reminder 1') }}</a></li>
                                @endif
                                @if($invoice->first_reminder_date && !$invoice->second_reminder_date)
                                    <li><a class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : 'employee.invoice.sendManualremind'))), ['id' => $invoice->id, 'reminder' => 2]) }}">{{ __('messages.Send Reminder 2') }}</a></li>
                                @endif
                                @if($invoice->second_reminder_date && !$invoice->third_reminder_date)
                                    <li><a class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1" href="{{ route((Auth::user()->hasRole('Admin') ? 'admin.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet.invoice.sendManualremind' : (Auth::user()->hasRole('Responsable de projet et gestionnaire') ? 'Responsable_gestionnaire.invoice.sendManualremind' : 'employee.invoice.sendManualremind'))), ['id' => $invoice->id, 'reminder' => 3]) }}">{{ __('messages.Send Reminder 3') }}</a></li>
                                @endif
                            </ul>
                        </div>
                    @endcan

                    {{-- Credit Note --}}
                    @php
                        $creditNoteRoute = '';
                        $detailedCreditNoteRoute = '';
                        if(Auth::user()->hasRole('Admin')) {
                            $creditNoteRoute = 'admin.invoice.creditNote';
                            $detailedCreditNoteRoute = 'admin.invoice.detailedCreditNote';
                        } elseif(Auth::user()->hasRole('Responsable de projet')) {
                            $creditNoteRoute = 'Responsable_de_projet.invoice.creditNote';
                            $detailedCreditNoteRoute = 'Responsable_de_projet.invoice.detailedCreditNote';
                        } elseif(Auth::user()->hasRole('Employee')) {
                            $creditNoteRoute = 'employee.invoice.creditNote';
                            $detailedCreditNoteRoute = 'employee.invoice.detailedCreditNote';
                        } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                            $creditNoteRoute = 'Responsable_gestionnaire.invoice.creditNote';
                            $detailedCreditNoteRoute = 'Responsable_gestionnaire.invoice.detailedCreditNote';
                        }
                    @endphp
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" type="button"
                                title="{{ __('messages.Credit Note') }}"
                                class="act-btn act-btn-credit">
                            <svg class="act-btn-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
                            {{ __('messages.Credit Note') }}
                            <svg class="act-btn-chevron" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <ul x-show="open"
                            @click.outside="open = false"
                            x-transition
                            style="display: none;"
                            class="absolute right-0 mt-1.5 w-40 rounded-xl shadow-lg bg-white ring-1 ring-slate-200 focus:outline-none z-50 py-1">
                            @if($invoice->type == 'Detailed' && $detailedCreditNoteRoute)
                                <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1" onclick="event.preventDefault(); showPartialDetailedPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">{{ __('messages.Partial') }}</a></li>
                                <li><a href="{{ route($detailedCreditNoteRoute, $invoice->id) }}?type=full" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                            @elseif($creditNoteRoute)
                                <li><a href="#" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1" onclick="event.preventDefault(); showPartialPopup({{ $invoice->id }}, {{ $invoice->total_ttc }});">{{ __('messages.Partial') }}</a></li>
                                <li><a href="{{ route($creditNoteRoute, $invoice->id) }}?type=full" class="block px-3 py-2 text-[12.5px] text-slate-700 hover:bg-slate-50 rounded-lg mx-1">{{ __('messages.Full') }}</a></li>
                            @endif
                        </ul>
                    </div>
                    {{-- Edit Button: only for first row and if not paid --}}
                    @if($loop->first && $invoice->Payment_status != 1)
                        @php
                            $editRoute = '';
                            if(Auth::user()->hasRole('Admin')) {
                                if($invoice->type == 'Partial') $editRoute = route('admin.invoices.editPartialInvoice', $invoice->id);
                                elseif($invoice->type == 'Percentage') $editRoute = route('admin.invoices.editPercentageInvoice', $invoice->id);
                                elseif($invoice->type == 'Period') $editRoute = route('admin.invoices.editPeriodInvoice', $invoice->id);
                                elseif($invoice->type == 'Detailed') $editRoute = route('admin.invoices.editDetailedInvoice', $invoice->id);
                                else $editRoute = route('admin.invoices.editFullInvoice', $invoice->id);
                            } elseif(Auth::user()->hasRole('Responsable de projet')) {
                                if($invoice->type == 'Partial') $editRoute = route('Responsable_de_projet.invoices.editPartialInvoice', $invoice->id);
                                elseif($invoice->type == 'Percentage') $editRoute = route('Responsable_de_projet.invoices.editPercentageInvoice', $invoice->id);
                                elseif($invoice->type == 'Period') $editRoute = route('Responsable_de_projet.invoices.editPeriodInvoice', $invoice->id);
                                elseif($invoice->type == 'Detailed') $editRoute = route('Responsable_de_projet.invoices.editDetailedInvoice', $invoice->id);
                                else $editRoute = route('Responsable_de_projet.invoices.editFullInvoice', $invoice->id);
                            } elseif(Auth::user()->hasRole('Employee')) {
                                if($invoice->type == 'Partial') $editRoute = route('employee.invoices.editPartialInvoice', $invoice->id);
                                elseif($invoice->type == 'Percentage') $editRoute = route('employee.invoices.editPercentageInvoice', $invoice->id);
                                elseif($invoice->type == 'Period') $editRoute = route('employee.invoices.editPeriodInvoice', $invoice->id);
                                elseif($invoice->type == 'Detailed') $editRoute = route('employee.invoices.editDetailedInvoice', $invoice->id);
                                else $editRoute = route('employee.invoices.editFullInvoice', $invoice->id);
                            } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                if($invoice->type == 'Partial') $editRoute = route('Responsable_gestionnaire.invoices.editPartialInvoice', $invoice->id);
                                elseif($invoice->type == 'Percentage') $editRoute = route('Responsable_gestionnaire.invoices.editPercentageInvoice', $invoice->id);
                                elseif($invoice->type == 'Period') $editRoute = route('Responsable_gestionnaire.invoices.editPeriodInvoice', $invoice->id);
                                elseif($invoice->type == 'Detailed') $editRoute = route('Responsable_gestionnaire.invoices.editDetailedInvoice', $invoice->id);
                                else $editRoute = route('Responsable_gestionnaire.invoices.editFullInvoice', $invoice->id);
                            }
                        @endphp
                        @if($editRoute)
                        <a href="{{ $editRoute }}" title="{{ __('messages.Edit Invoice') }}" class="act-btn act-btn-edit">
                            <svg xmlns="http://www.w3.org/2000/svg" class="act-btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            {{ __('messages.Edit') }}
                        </a>
                        @endif
                    @endif
                    {{-- Delete Button: only for first row --}}
                    @if($loop->first)
                        @can('Delete invoice')
                            @if($deleteRoute)
                            <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm(@js(__('messages.Delete this invoice? This cannot be undone.')));">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="{{ __('messages.Delete Invoice') }}" class="act-btn act-btn-delete">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="act-btn-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    {{ __('messages.Delete') }}
                                </button>
                            </form>
                            @endif
                        @endcan
                    @endif
                </div>
            </div>
        @endif
    </td>
</tr>
@empty
<tr>
    <td colspan="8" class="px-5 py-14 text-center">
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
                <div class="flex flex-wrap items-center justify-end gap-2 px-5 py-3 border-t border-slate-100 bg-[#f8fafc]">
                    <div class="flex items-center gap-3">
                        <span id="paginationInfo" class="text-[12px] text-slate-500"></span>
                        <div class="flex items-center gap-1" id="paginationControls"></div>
                    </div>
                </div>
            </div>
            {{-- end table card --}}

            {{-- ── Send Invoice Modal ── --}}
            <div id="sendInvoiceModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
                    <div class="flex justify-between items-center px-5 sm:px-6 py-4 border-b border-slate-100">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Send Invoice') }}</h5>
                        <button type="button" id="closeSendInvoiceModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <form id="sendInvoiceForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="invoice_id" id="invoice_id">
                        <div class="px-5 sm:px-6 py-5 space-y-4">
                            <div>
                                <h6 class="text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Select Recipients:') }}</h6>
                                <div id="recipientsList" class="space-y-2 p-3 border border-slate-200 rounded-xl bg-slate-50 h-40 overflow-y-auto text-sm text-slate-700"></div>
                            </div>
                            <div>
                                <label for="fileUpload" class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Upload Additional Files (Optional):') }}</label>
                                <input type="file" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2D7080]/10 file:text-[#2D7080] hover:file:bg-[#2D7080]/20 cursor-pointer" id="fileUpload" name="file[]" multiple>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                            <button type="button" id="cancelSendInvoiceModal" class="dt-btn dt-btn-ghost">{{ __('messages.Close') }}</button>
                            <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Send Invoice') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Peppol Send Modal --}}
            <div id="sendPeppolModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4">
                    <div class="flex justify-between items-center px-5 sm:px-6 py-4 border-b border-slate-100">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Send Invoice via Peppol') }}</h5>
                        <button type="button" id="closeSendPeppolModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <form id="sendPeppolForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="invoice_id" id="peppol_invoice_id">
                        <input type="hidden" name="invoice_type" id="peppol_invoice_type">
                        <div class="px-5 sm:px-6 py-5 space-y-4">
                            <div class="text-sm text-slate-700">
                                <p class="mb-2">
                                    {{ __('messages.You are about to send this invoice via Peppol.') }}
                                </p>
                                <p class="mb-4">
                                    {{ __('messages.Please confirm that you want to proceed. This action will send the invoice through the Peppol network.') }}
                                </p>
                            </div>

                            {{-- File Attachments Section --}}
                            <div class="border-t border-slate-100 pt-4">
                                <label class="block text-[13px] font-semibold text-slate-700 mb-2">
                                    {{ __('messages.Attach Files (Optional)') }}
                                    <span class="text-xs text-slate-500 font-normal">(PDF, Excel, CSV, JPEG, PNG)</span>
                                </label>
                                <div class="mt-2">
                                    <input type="file"
                                           id="peppol_attachments"
                                           name="attachments[]"
                                           multiple
                                           accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png"
                                           class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2D7080]/10 file:text-[#2D7080] hover:file:bg-[#2D7080]/20 cursor-pointer border border-slate-200 rounded-xl">
                                    <p class="mt-1 text-xs text-slate-500">{{ __('messages.Maximum file size: 10MB per file.') }}</p>
                                </div>

                                {{-- Selected Files Display --}}
                                <div id="peppol_selected_files" class="mt-3 space-y-2 hidden">
                                    <p class="text-xs font-medium text-slate-700">{{ __('messages.Selected files:') }}</p>
                                    <ul id="peppol_files_list" class="text-xs text-slate-600 space-y-1"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                            <button type="button" id="cancelSendPeppolModal" class="dt-btn dt-btn-ghost">{{ __('messages.Annuler') }}</button>
                            <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Confirmer l\'envoi via Peppol') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Combined Send Modal (Email + Peppol) --}}
            <div id="combinedSendModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-xl mx-4">
                    <div class="flex justify-between items-center px-5 sm:px-6 py-4 border-b border-slate-100">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Send Invoice') }}</h5>
                        <button type="button" id="closeCombinedSendModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <form id="combinedSendForm" method="POST" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="invoice_id" id="combined_invoice_id">
                        <input type="hidden" name="invoice_type" id="combined_invoice_type">
                        <div class="px-5 sm:px-6 py-5 space-y-5">
                            <div>
                                <h6 class="text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Choose how to send:') }}</h6>
                                <div class="space-y-2">
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" id="combined_send_email" class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30">
                                        <span class="text-sm text-slate-700">{{ __('messages.Send via Email') }}</span>
                                    </label>
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox" id="combined_send_peppol" class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30">
                                        <span class="text-sm text-slate-700">{{ __('messages.Send via Peppol') }}</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Email recipients (visible only when email is checked) --}}
                            <div id="combined_email_section" class="space-y-2 hidden">
                                <h6 class="text-[13px] font-semibold text-slate-700">{{ __('messages.Select Recipients (Email):') }}</h6>
                                <div id="combinedRecipientsList" class="space-y-2 p-3 border border-slate-200 rounded-xl bg-slate-50 h-40 overflow-y-auto text-sm text-slate-700">
                                    <div class="text-center text-slate-500">{{ __('messages.Loading...') }}</div>
                                </div>
                            </div>

                            {{-- Attachments for both channels --}}
                            <div>
                                <label class="block text-[13px] font-semibold text-slate-700 mb-2">
                                    {{ __('messages.Attach Files (Optional)') }}
                                    <span class="text-xs text-slate-500 font-normal">{{ __('messages.(PDF, Excel, CSV, JPEG, PNG – max 10MB each)') }}</span>
                                </label>
                                <input type="file"
                                       id="combined_attachments"
                                       name="combined_attachments[]"
                                       multiple
                                       accept=".pdf,.xlsx,.xls,.csv,.jpg,.jpeg,.png"
                                       class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-[#2D7080]/10 file:text-[#2D7080] hover:file:bg-[#2D7080]/20 cursor-pointer border border-slate-200 rounded-xl">
                                <div id="combined_selected_files" class="mt-3 space-y-2 hidden">
                                    <p class="text-xs font-medium text-slate-700">{{ __('messages.Selected files:') }}</p>
                                    <ul id="combined_files_list" class="text-xs text-slate-600 space-y-1"></ul>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                            <button type="button" id="cancelCombinedSendModal" class="dt-btn dt-btn-ghost">{{ __('messages.Close') }}</button>
                            <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Send') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Partial Credit Note Modal --}}
            <div id="partialCreditNoteModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
                    <div class="flex justify-between items-center px-5 sm:px-6 py-4 border-b border-slate-100">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Partial Credit Note') }}</h5>
                        <button type="button" id="closePartialCreditNoteModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <form id="partialCreditNoteForm">
                        <div class="px-5 sm:px-6 py-5">
                            <label for="partialAmount" class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Enter Amount:') }}</label>
                            <input type="number" class="block w-full rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" id="partialAmount" step="0.0001" required>
                            <span id="partialAmountError" class="text-red-600 text-sm mt-1 hidden"></span>
                        </div>
                        <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                            <button type="button" id="cancelPartialCreditNoteModal" class="dt-btn dt-btn-ghost">{{ __('messages.Close') }}</button>
                            <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Partial Detailed Credit Note Modal --}}
            <div id="partialDetailedCreditNoteModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
                    <div class="flex justify-between items-center px-5 sm:px-6 py-4 border-b border-slate-100">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Partial Credit Note (Detailed)') }}</h5>
                        <button type="button" id="closePartialDetailedCreditNoteModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <form id="partialDetailedCreditNoteForm">
                        <div class="px-5 sm:px-6 py-5">
                            <label for="partialDetailedAmount" class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Enter Amount:') }}</label>
                            <input type="number" class="block w-full rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" id="partialDetailedAmount" step="0.0001" required>
                            <span id="partialDetailedAmountError" class="text-red-600 text-sm mt-1 hidden"></span>
                        </div>
                        <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                            <button type="button" id="cancelPartialDetailedCreditNoteModal" class="dt-btn dt-btn-ghost">{{ __('messages.Close') }}</button>
                            <button type="submit" class="dt-btn dt-btn-teal">{{ __('messages.Submit') }}</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Invoice Report Modal --}}
            <div id="invoiceReportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
                    <div class="flex justify-between items-center px-5 sm:px-6 py-4 border-b border-slate-100">
                        <h5 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Download Invoices Reports') }}</h5>
                        <button type="button" id="closeInvoiceReportModal" class="text-slate-400 hover:text-slate-600 text-2xl leading-none">&times;</button>
                    </div>
                    <div class="px-5 sm:px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Choose payment statuses (optional):') }}</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="status_paid" value="paid">
                                    <label class="ml-2 text-sm text-slate-700" for="status_paid">{{ __('messages.Paid status label') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="status_unpaid" value="unpaid">
                                    <label class="ml-2 text-sm text-slate-700" for="status_unpaid">{{ __('messages.Unpaid status label') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="status_overdue" value="overdue">
                                    <label class="ml-2 text-sm text-slate-700" for="status_overdue">{{ __('messages.Overdue') }}</label>
                                </div>
                            </div>
                        </div>
                        <hr class="border-slate-100">
                        <div>
                            <label class="block text-[13px] font-semibold text-slate-700 mb-2">{{ __('messages.Choose invoice types (optional):') }}</label>
                            <div class="space-y-2">
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="type_partial" value="partial">
                                    <label class="ml-2 text-sm text-slate-700" for="type_partial">{{ __('messages.Partial') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="type_percentage" value="percentage">
                                    <label class="ml-2 text-sm text-slate-700" for="type_percentage">{{ __('messages.Percentage') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="type_full" value="full">
                                    <label class="ml-2 text-sm text-slate-700" for="type_full">{{ __('messages.Full') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="type_period" value="period">
                                    <label class="ml-2 text-sm text-slate-700" for="type_period">{{ __('messages.Period') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="type_detailed" value="detailed">
                                    <label class="ml-2 text-sm text-slate-700" for="type_detailed">{{ __('messages.Detailed') }}</label>
                                </div>
                            </div>
                        </div>
                        <hr class="border-slate-100">
                        <div class="flex items-center">
                            <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30" type="checkbox" id="credit_note_applied" value="1">
                            <label class="ml-2 text-sm text-slate-700" for="credit_note_applied">{{ __('messages.Credit note applied') }}</label>
                        </div>
                        <small class="text-slate-500 text-xs">{{ __('messages.Date range (optional) will be applied from the fields at the top.') }}</small>
                    </div>
                    <div class="flex items-center justify-end gap-2 px-5 sm:px-6 py-4 bg-slate-50 border-t border-slate-100 rounded-b-2xl">
                        <button type="button" id="cancelInvoiceReportModal" class="dt-btn dt-btn-ghost">{{ __('messages.Cancel') }}</button>
                        <button type="button" id="confirmInvoiceReport" class="dt-btn dt-btn-teal">{{ __('messages.Download PDF') }}</button>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    // Setup CSRF token for all jQuery AJAX requests
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // Global functions for opening modals (defined early so they're available immediately)
    window.showPartialPopup = function(invoiceId, totalCost) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'partialCreditNoteModal', invoiceId: invoiceId, totalCost: totalCost } }));
    };
    window.showPartialDetailedPopup = function(invoiceId, totalCost) {
        window.dispatchEvent(new CustomEvent('open-modal', { detail: { id: 'partialDetailedCreditNoteModal', invoiceId: invoiceId, totalCost: totalCost } }));
    };

    // Global modal opening functions (will be defined in DOMContentLoaded, but we'll set up placeholders)
    window.openSendInvoiceModal = null;
    window.openSendPeppolModal = null;
    window.openCombinedSendModal = null;
    window.openPartialCreditNoteModal = null;
    window.openPartialDetailedCreditNoteModal = null;

    // Set up event listener immediately (not waiting for DOMContentLoaded)
    window.addEventListener('open-modal', function(event) {
        const detail = event.detail;
        if (detail.id === 'sendInvoiceModal' && detail.invoiceId && window.openSendInvoiceModal) {
            window.openSendInvoiceModal(detail.invoiceId);
        } else if (detail.id === 'sendPeppolModal' && detail.invoiceId && window.openSendPeppolModal) {
            window.openSendPeppolModal(detail.invoiceId, detail.invoiceType);
        } else if (detail.id === 'partialCreditNoteModal' && detail.invoiceId && detail.totalCost && window.openPartialCreditNoteModal) {
            window.openPartialCreditNoteModal(detail.invoiceId, detail.totalCost);
        } else if (detail.id === 'partialDetailedCreditNoteModal' && detail.invoiceId && detail.totalCost && window.openPartialDetailedCreditNoteModal) {
            window.openPartialDetailedCreditNoteModal(detail.invoiceId, detail.totalCost);
        } else if ((detail === 'invoiceReportModal' || detail.id === 'invoiceReportModal')) {
            const invoiceReportModal = document.getElementById('invoiceReportModal');
            if (invoiceReportModal) invoiceReportModal.classList.remove('hidden');
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        // --- MODAL HANDLING (Vanilla JS like devisHistory) ---

        // Send Invoice Modal
        const sendInvoiceModal = document.getElementById('sendInvoiceModal');
        const closeSendInvoiceModalBtn = document.getElementById('closeSendInvoiceModal');
        const cancelSendInvoiceModalBtn = document.getElementById('cancelSendInvoiceModal');
        const sendInvoiceForm = document.getElementById('sendInvoiceForm');

        if (closeSendInvoiceModalBtn) {
            closeSendInvoiceModalBtn.addEventListener('click', () => {
                if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
            });
        }
        if (cancelSendInvoiceModalBtn) {
            cancelSendInvoiceModalBtn.addEventListener('click', () => {
                if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
            });
        }

        // Handle Send Invoice Form Submission
        if (sendInvoiceForm) {
            sendInvoiceForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const invoiceId = document.getElementById('invoice_id').value;
                const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked')).map(checkbox => checkbox.value);

                if (selectedRecipients.length === 0) {
                    showMessage(@json(__('messages.Please select at least one recipient')), 'danger');
                    return;
                }

                let actionUrl = '';
                @if(Auth::user()->hasRole('Admin'))
                    actionUrl = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    actionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":invoice_id") }}';
                @elseif(Auth::user()->hasRole('Employee'))
                    actionUrl = '{{ route("employee.invoice.sendInvoice", ":invoice_id") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    actionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":invoice_id") }}';
                @endif
                actionUrl = actionUrl.replace(':invoice_id', invoiceId);

                const formData = new FormData(sendInvoiceForm);
                formData.delete('selected_recipients[]');
                selectedRecipients.forEach(recipient => {
                    formData.append('selected_recipients[]', recipient);
                });

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            try {
                                return JSON.parse(text);
                            } catch {
                                throw new Error('Server error: ' + response.status);
                            }
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
                        showMessage(data.message, 'success');
                        setTimeout(() => window.location.reload(), 2000);
                    } else {
                        if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
                        showMessage('Error sending invoice: ' + (data.message || 'Unknown error'), 'danger');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (sendInvoiceModal) sendInvoiceModal.classList.add('hidden');
                    showMessage('An error occurred while sending the invoice. Please try again.', 'danger');
                });
            });
        }

        // Function to open Send Invoice Modal
        window.openSendInvoiceModal = function(invoiceId) {
            document.getElementById('invoice_id').value = invoiceId;
            const recipientsList = document.getElementById('recipientsList');
            recipientsList.innerHTML = ('<div class="text-center">' + @js(__('messages.Loading...')) + '</div>');

            let recipientsUrl = '';
            @if(Auth::user()->hasRole('Admin'))
                recipientsUrl = '/admin/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                recipientsUrl = '/Responsable_de_projet/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Employee'))
                recipientsUrl = '/employee/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                recipientsUrl = '/Responsable_gestionnaire/invoice/' + invoiceId + '/recipients';
            @endif

            fetch(recipientsUrl)
                .then(response => response.json())
                .then(data => {
                    recipientsList.innerHTML = '';
                    if (data.message && (!data.recipients || data.recipients.length === 0)) {
                        recipientsList.innerHTML = '<p class="text-sm text-red-600">' + data.message + '</p>';
                    } else if (data.recipients && data.recipients.length > 0) {
                        data.recipients.forEach(recipient => {
                            const div = document.createElement('div');
                            div.className = 'mb-2 flex items-center';
                            div.innerHTML = `
                                <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30 mr-2" type="checkbox" name="selected_recipients[]" value="${recipient.email}" id="recipient-${recipient.email}" ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                <label class="text-sm text-slate-700" for="recipient-${recipient.email}"><strong>${recipient.name}</strong> (${recipient.email})</label>
                            `;
                            recipientsList.appendChild(div);
                        });
                    } else {
                        recipientsList.innerHTML = ('<p class="text-sm text-slate-500">' + @js(__('messages.No recipients found')) + '</p>');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    recipientsList.innerHTML = ('<p class="text-sm text-red-600">' + @js(__('messages.Error loading recipients')) + '</p>');
                });

            if (sendInvoiceModal) sendInvoiceModal.classList.remove('hidden');
        };

        // Send Peppol Modal
        const sendPeppolModal = document.getElementById('sendPeppolModal');
        const closeSendPeppolModalBtn = document.getElementById('closeSendPeppolModal');
        const cancelSendPeppolModalBtn = document.getElementById('cancelSendPeppolModal');
        const sendPeppolForm = document.getElementById('sendPeppolForm');
        const billitConfigured = @json(isset($entreprise_infos) && !is_null($entreprise_infos->getRawOriginal('billit_api_key')));

        if (closeSendPeppolModalBtn) {
            closeSendPeppolModalBtn.addEventListener('click', () => {
                if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
            });
        }
        if (cancelSendPeppolModalBtn) {
            cancelSendPeppolModalBtn.addEventListener('click', () => {
                if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
            });
        }

        // Handle Send Peppol Form Submission
        if (sendPeppolForm) {
            sendPeppolForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const invoiceId = document.getElementById('peppol_invoice_id').value;
                const invoiceType = document.getElementById('peppol_invoice_type').value;

                let actionUrl = '';
                @if(Auth::user()->hasRole('Admin'))
                    actionUrl = '{{ route("admin.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    actionUrl = '{{ route("Responsable_de_projet.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @elseif(Auth::user()->hasRole('Employee'))
                    actionUrl = '{{ route("employee.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    actionUrl = '{{ route("Responsable_gestionnaire.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                @endif
                actionUrl = actionUrl.replace(':invoice_id', invoiceId).replace(':invoice_type', invoiceType) + '?channel=Peppol';

                const formData = new FormData(sendPeppolForm);

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                })
                .then(async (response) => {
                    const ok = response.ok;
                    const redirected = response.redirected;

                    // Attempt to parse JSON for accurate error messages.
                    let data = null;
                    const contentType = response.headers.get('content-type') || '';
                    try {
                        if (contentType.includes('application/json')) {
                            data = await response.json();
                        } else {
                            const text = await response.text();
                            data = JSON.parse(text);
                        }
                    } catch {
                        data = null;
                    }

                    if (!ok && !redirected) {
                        if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
                        showMessage('Error sending invoice via Peppol: ' + ((data && data.message) ? data.message : 'Unknown error'), 'danger');
                        return;
                    }

                    if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
                    showMessage((data && data.message) ? data.message : @json(__('messages.Invoice sent successfully via Peppol')), 'success');
                    setTimeout(() => window.location.reload(), 2000);
                })
                .catch(error => {
                    console.error('Error:', error);
                    if (sendPeppolModal) sendPeppolModal.classList.add('hidden');
                    showMessage('An error occurred while sending the invoice. Please try again.', 'danger');
                });
            });
        }

        // Function to open Send Peppol Modal (simple confirmation, no recipients)
        window.openSendPeppolModal = function(invoiceId, invoiceType) {
            if (!billitConfigured) {
                showMessage(@json(__('messages.Billit is not configured for your enterprise. Please add your Billit API key in Enterprise Information before sending via Peppol.')), 'danger');
                return;
            }
            document.getElementById('peppol_invoice_id').value = invoiceId;
            document.getElementById('peppol_invoice_type').value = invoiceType || 'full';

            // Reset file input and selected files display
            const fileInput = document.getElementById('peppol_attachments');
            const selectedFilesDiv = document.getElementById('peppol_selected_files');
            const filesList = document.getElementById('peppol_files_list');
            if (fileInput) fileInput.value = '';
            if (selectedFilesDiv) selectedFilesDiv.classList.add('hidden');
            if (filesList) filesList.innerHTML = '';

            if (sendPeppolModal) sendPeppolModal.classList.remove('hidden');
        };

        // Handle file selection display for Peppol modal
        const peppolFileInput = document.getElementById('peppol_attachments');
        if (peppolFileInput) {
            peppolFileInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                const selectedFilesDiv = document.getElementById('peppol_selected_files');
                const filesList = document.getElementById('peppol_files_list');

                if (files.length === 0) {
                    if (selectedFilesDiv) selectedFilesDiv.classList.add('hidden');
                    if (filesList) filesList.innerHTML = '';
                    return;
                }

                // Validate files
                const allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'png'];
                const maxFileSize = 10 * 1024 * 1024; // 10MB
                const validFiles = [];
                const errors = [];

                files.forEach((file) => {
                    const extension = file.name.split('.').pop().toLowerCase();
                    const fileSize = file.size;

                    if (!allowedExtensions.includes(extension)) {
                        errors.push(`${file.name}: Unsupported file type. Allowed: PDF, Excel, CSV, JPEG, PNG`);
                        return;
                    }

                    if (fileSize > maxFileSize) {
                        errors.push(`${file.name}: File too large (max 10MB)`);
                        return;
                    }

                    validFiles.push(file);
                });

                // Show errors if any
                if (errors.length > 0) {
                    alert('File validation errors:\n\n' + errors.join('\n'));
                    // Remove invalid files from input
                    const dataTransfer = new DataTransfer();
                    validFiles.forEach(file => dataTransfer.items.add(file));
                    e.target.files = dataTransfer.files;
                }

                // Display valid files
                if (validFiles.length === 0) {
                    if (selectedFilesDiv) selectedFilesDiv.classList.add('hidden');
                    if (filesList) filesList.innerHTML = '';
                    return;
                }

                if (selectedFilesDiv) selectedFilesDiv.classList.remove('hidden');
                if (filesList) {
                    filesList.innerHTML = '';
                    validFiles.forEach((file, index) => {
                        const li = document.createElement('li');
                        li.className = 'flex items-center justify-between bg-slate-50 px-2 py-1 rounded';
                        li.innerHTML = `
                            <span class="truncate flex-1">${file.name}</span>
                            <span class="text-slate-400 ml-2">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                        `;
                        filesList.appendChild(li);
                    });
                }
            });
        }

        // Combined Send Modal (Email + Peppol)
        const combinedSendModal = document.getElementById('combinedSendModal');
        const closeCombinedSendModalBtn = document.getElementById('closeCombinedSendModal');
        const cancelCombinedSendModalBtn = document.getElementById('cancelCombinedSendModal');
        const combinedSendForm = document.getElementById('combinedSendForm');
        const combinedSendEmailCheckbox = document.getElementById('combined_send_email');
        const combinedSendPeppolCheckbox = document.getElementById('combined_send_peppol');
        const combinedEmailSection = document.getElementById('combined_email_section');
        const combinedRecipientsList = document.getElementById('combinedRecipientsList');
        const combinedAttachmentsInput = document.getElementById('combined_attachments');
        const combinedSelectedFilesDiv = document.getElementById('combined_selected_files');
        const combinedFilesList = document.getElementById('combined_files_list');

        function toggleCombinedEmailSection() {
            if (!combinedEmailSection) return;
            if (combinedSendEmailCheckbox && combinedSendEmailCheckbox.checked) {
                combinedEmailSection.classList.remove('hidden');
            } else {
                combinedEmailSection.classList.add('hidden');
            }
        }

        if (combinedSendEmailCheckbox) {
            combinedSendEmailCheckbox.addEventListener('change', toggleCombinedEmailSection);
        }

        if (closeCombinedSendModalBtn) {
            closeCombinedSendModalBtn.addEventListener('click', () => {
                if (combinedSendModal) combinedSendModal.classList.add('hidden');
            });
        }
        if (cancelCombinedSendModalBtn) {
            cancelCombinedSendModalBtn.addEventListener('click', () => {
                if (combinedSendModal) combinedSendModal.classList.add('hidden');
            });
        }

        // Open Combined modal
        window.openCombinedSendModal = function(invoiceId, invoiceType) {
            const invoiceIdInput = document.getElementById('combined_invoice_id');
            const invoiceTypeInput = document.getElementById('combined_invoice_type');
            if (invoiceIdInput) invoiceIdInput.value = invoiceId;
            if (invoiceTypeInput) invoiceTypeInput.value = invoiceType || 'full';

            if (combinedSendEmailCheckbox) combinedSendEmailCheckbox.checked = false;
            if (combinedSendPeppolCheckbox) combinedSendPeppolCheckbox.checked = false;
            toggleCombinedEmailSection();

            if (combinedAttachmentsInput) {
                combinedAttachmentsInput.value = '';
            }
            if (combinedSelectedFilesDiv) combinedSelectedFilesDiv.classList.add('hidden');
            if (combinedFilesList) combinedFilesList.innerHTML = '';

            if (combinedRecipientsList) {
                combinedRecipientsList.innerHTML = ('<div class="text-center text-slate-500">' + @js(__('messages.Loading...')) + '</div>');
            }

            let recipientsUrl = '';
            @if(Auth::user()->hasRole('Admin'))
                recipientsUrl = '/admin/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                recipientsUrl = '/Responsable_de_projet/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Employee'))
                recipientsUrl = '/employee/invoice/' + invoiceId + '/recipients';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                recipientsUrl = '/Responsable_gestionnaire/invoice/' + invoiceId + '/recipients';
            @endif

            if (recipientsUrl && combinedRecipientsList) {
                fetch(recipientsUrl)
                    .then(response => response.json())
                    .then(data => {
                        combinedRecipientsList.innerHTML = '';
                        if (data.message && (!data.recipients || data.recipients.length === 0)) {
                            combinedRecipientsList.innerHTML = '<p class="text-sm text-red-600">' + data.message + '</p>';
                        } else if (data.recipients && data.recipients.length > 0) {
                            data.recipients.forEach(recipient => {
                                const div = document.createElement('div');
                                div.className = 'mb-2 flex items-center';
                                div.innerHTML = `
                                    <input class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30 mr-2" type="checkbox" name="combined_selected_recipients[]" value="${recipient.email}" id="combined-recipient-${recipient.email}" ${recipient.is_comptable === 1 ? 'checked' : ''}>
                                    <label class="text-sm text-slate-700" for="combined-recipient-${recipient.email}"><strong>${recipient.name}</strong> (${recipient.email})</label>
                                `;
                                combinedRecipientsList.appendChild(div);
                            });
                        } else {
                            combinedRecipientsList.innerHTML = ('<p class="text-sm text-slate-500">' + @js(__('messages.No recipients found')) + '</p>');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        combinedRecipientsList.innerHTML = ('<p class="text-sm text-red-600">' + @js(__('messages.Error loading recipients')) + '</p>');
                    });
            }

            if (combinedSendModal) combinedSendModal.classList.remove('hidden');
        };

        // Handle file selection display for combined modal
        if (combinedAttachmentsInput) {
            combinedAttachmentsInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                if (!combinedSelectedFilesDiv || !combinedFilesList) return;

                if (files.length === 0) {
                    combinedSelectedFilesDiv.classList.add('hidden');
                    combinedFilesList.innerHTML = '';
                    return;
                }

                const allowedExtensions = ['pdf', 'xlsx', 'xls', 'csv', 'jpg', 'jpeg', 'png'];
                const maxFileSize = 10 * 1024 * 1024;
                const validFiles = [];
                const errors = [];

                files.forEach((file) => {
                    const extension = file.name.split('.').pop().toLowerCase();
                    const fileSize = file.size;

                    if (!allowedExtensions.includes(extension)) {
                        errors.push(`${file.name}: Unsupported file type. Allowed: PDF, Excel, CSV, JPEG, PNG`);
                        return;
                    }

                    if (fileSize > maxFileSize) {
                        errors.push(`${file.name}: File too large (max 10MB)`);
                        return;
                    }

                    validFiles.push(file);
                });

                if (errors.length > 0) {
                    alert('File validation errors:\n\n' + errors.join('\n'));
                    const dataTransfer = new DataTransfer();
                    validFiles.forEach(file => dataTransfer.items.add(file));
                    e.target.files = dataTransfer.files;
                }

                const finalFiles = validFiles.length > 0 ? validFiles : [];
                if (finalFiles.length === 0) {
                    combinedSelectedFilesDiv.classList.add('hidden');
                    combinedFilesList.innerHTML = '';
                    return;
                }

                combinedSelectedFilesDiv.classList.remove('hidden');
                combinedFilesList.innerHTML = '';
                finalFiles.forEach(file => {
                    const li = document.createElement('li');
                    li.className = 'flex items-center justify-between bg-slate-50 px-2 py-1 rounded';
                    li.innerHTML = `
                        <span class="truncate flex-1">${file.name}</span>
                        <span class="text-slate-400 ml-2">${(file.size / 1024 / 1024).toFixed(2)} MB</span>
                    `;
                    combinedFilesList.appendChild(li);
                });
            });
        }

        if (combinedSendForm) {
            combinedSendForm.addEventListener('submit', async function(e) {
                e.preventDefault();

                const sendEmail = combinedSendEmailCheckbox && combinedSendEmailCheckbox.checked;
                const sendPeppol = combinedSendPeppolCheckbox && combinedSendPeppolCheckbox.checked;

                if (!sendEmail && !sendPeppol) {
                    showMessage(@json(__('messages.Please select at least one sending option (Email or Peppol).')), 'danger');
                    return;
                }
                if (sendPeppol && !billitConfigured) {
                    if (combinedSendModal) combinedSendModal.classList.add('hidden');
                    showMessage(@json(__('messages.Billit is not configured for your enterprise. Please add your Billit API key in Enterprise Information before sending via Peppol.')), 'danger');
                    return;
                }

                const invoiceId = document.getElementById('combined_invoice_id')?.value;
                const invoiceType = document.getElementById('combined_invoice_type')?.value || 'full';
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                let attachmentsFiles = [];
                if (combinedAttachmentsInput) {
                    attachmentsFiles = Array.from(combinedAttachmentsInput.files);
                }

                try {
                    // First: send by email if requested
                    if (sendEmail) {
                        const selectedRecipients = Array.from(document.querySelectorAll('input[name="combined_selected_recipients[]"]:checked')).map(cb => cb.value);
                        if (selectedRecipients.length === 0) {
                            if (combinedSendModal) combinedSendModal.classList.add('hidden');
                            showMessage(@json(__('messages.Please select at least one email recipient.')), 'danger');
                            return;
                        }

                        let emailActionUrl = '';
                        @if(Auth::user()->hasRole('Admin'))
                            emailActionUrl = '{{ route("admin.invoice.sendInvoice", ":invoice_id") }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet'))
                            emailActionUrl = '{{ route("Responsable_de_projet.invoice.sendInvoice", ":invoice_id") }}';
                        @elseif(Auth::user()->hasRole('Employee'))
                            emailActionUrl = '{{ route("employee.invoice.sendInvoice", ":invoice_id") }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            emailActionUrl = '{{ route("Responsable_gestionnaire.invoice.sendInvoice", ":invoice_id") }}';
                        @endif
                        emailActionUrl = emailActionUrl.replace(':invoice_id', invoiceId);

                        const emailFormData = new FormData();
                        selectedRecipients.forEach(recipient => {
                            emailFormData.append('selected_recipients[]', recipient);
                        });
                        attachmentsFiles.forEach(file => {
                            emailFormData.append('file[]', file);
                        });

                        const emailResponse = await fetch(emailActionUrl, {
                            method: 'POST',
                            body: emailFormData,
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });

                        if (!emailResponse.ok) {
                            const text = await emailResponse.text();
                            try {
                                const data = JSON.parse(text);
                                if (!data.success) {
                                    if (combinedSendModal) combinedSendModal.classList.add('hidden');
                                    showMessage('Error sending invoice by email: ' + (data.message || 'Unknown error'), 'danger');
                                    return;
                                }
                            } catch {
                                throw new Error('Server error while sending email: ' + emailResponse.status);
                            }
                        } else {
                            const data = await emailResponse.json();
                            if (!data.success) {
                                if (combinedSendModal) combinedSendModal.classList.add('hidden');
                                showMessage('Error sending invoice by email: ' + (data.message || 'Unknown error'), 'danger');
                                return;
                            }
                        }
                    }

                    // Second: send via Peppol if requested
                    if (sendPeppol) {
                        let peppolActionUrl = '';
                        @if(Auth::user()->hasRole('Admin'))
                            peppolActionUrl = '{{ route("admin.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet'))
                            peppolActionUrl = '{{ route("Responsable_de_projet.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @elseif(Auth::user()->hasRole('Employee'))
                            peppolActionUrl = '{{ route("employee.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            peppolActionUrl = '{{ route("Responsable_gestionnaire.billit.send-invoice-type", ["id" => ":invoice_id", "type" => ":invoice_type"]) }}';
                        @endif
                        peppolActionUrl = peppolActionUrl.replace(':invoice_id', invoiceId).replace(':invoice_type', invoiceType) + '?channel=Peppol';

                        const peppolFormData = new FormData();
                        attachmentsFiles.forEach(file => {
                            peppolFormData.append('attachments[]', file);
                        });

                        const peppolResponse = await fetch(peppolActionUrl, {
                            method: 'POST',
                            body: peppolFormData,
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        });

                        if (!(peppolResponse.redirected || peppolResponse.ok)) {
                            let errMsg = 'Unknown error';
                            const contentType = peppolResponse.headers.get('content-type') || '';
                            try {
                                if (contentType.includes('application/json')) {
                                    const data = await peppolResponse.json();
                                    if (data && data.message) errMsg = data.message;
                                } else {
                                    const text = await peppolResponse.text();
                                    const data = JSON.parse(text);
                                    if (data && data.message) errMsg = data.message;
                                }
                            } catch {}
                            if (combinedSendModal) combinedSendModal.classList.add('hidden');
                            showMessage('Error sending invoice via Peppol: ' + errMsg, 'danger');
                            return;
                        }
                    }

                    if (combinedSendModal) combinedSendModal.classList.add('hidden');

                    if (sendEmail && sendPeppol) {
                        showMessage(@json(__('messages.Invoice sent successfully by email and via Peppol.')), 'success');
                    } else if (sendEmail) {
                        showMessage(@json(__('messages.Invoice sent successfully by email.')), 'success');
                    } else if (sendPeppol) {
                        showMessage(@json(__('messages.Invoice sent successfully via Peppol')), 'success');
                    }

                    setTimeout(() => window.location.reload(), 2000);
                } catch (error) {
                    console.error('Error:', error);
                    if (combinedSendModal) combinedSendModal.classList.add('hidden');
                    showMessage('An error occurred while sending the invoice. Please try again.', 'danger');
                }
            });
        }

        // Partial Credit Note Modal
        const partialCreditNoteModal = document.getElementById('partialCreditNoteModal');
        const closePartialCreditNoteModalBtn = document.getElementById('closePartialCreditNoteModal');
        const cancelPartialCreditNoteModalBtn = document.getElementById('cancelPartialCreditNoteModal');
        const partialCreditNoteForm = document.getElementById('partialCreditNoteForm');
        let currentPartialInvoiceId = null;
        let currentPartialTotalCost = 0;

        if (closePartialCreditNoteModalBtn) {
            closePartialCreditNoteModalBtn.addEventListener('click', () => {
                if (partialCreditNoteModal) partialCreditNoteModal.classList.add('hidden');
            });
        }
        if (cancelPartialCreditNoteModalBtn) {
            cancelPartialCreditNoteModalBtn.addEventListener('click', () => {
                if (partialCreditNoteModal) partialCreditNoteModal.classList.add('hidden');
            });
        }

        if (partialCreditNoteForm) {
            partialCreditNoteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const amountInput = document.getElementById('partialAmount');
                const errorSpan = document.getElementById('partialAmountError');
                const amount = parseFloat(amountInput.value);

                errorSpan.classList.add('hidden');

                if (isNaN(amount) || amount <= 0) {
                    errorSpan.textContent = @json(__('messages.Please enter a valid amount greater than 0.'));
                    errorSpan.classList.remove('hidden');
                    return;
                }
                if (amount > currentPartialTotalCost) {
                    errorSpan.textContent = @json(__('messages.Amount exceeds total cost.'));
                    errorSpan.classList.remove('hidden');
                    return;
                }

                let url = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    url = `{{ route('Responsable_de_projet.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @elseif(Auth::user()->hasRole('Admin'))
                    url = `{{ route('admin.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @elseif(Auth::user()->hasRole('Employee'))
                    url = `{{ route('employee.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    url = `{{ route('Responsable_gestionnaire.invoice.creditNote', '') }}/${currentPartialInvoiceId}?type=partial&amount=${amount}`;
                @endif
                window.location.href = url;
            });
        }

        window.openPartialCreditNoteModal = function(invoiceId, totalCost) {
            currentPartialInvoiceId = invoiceId;
            currentPartialTotalCost = parseFloat(totalCost);
            document.getElementById('partialAmount').value = '';
            document.getElementById('partialAmountError').classList.add('hidden');
            if (partialCreditNoteModal) partialCreditNoteModal.classList.remove('hidden');
        };

        // Partial Detailed Credit Note Modal
        const partialDetailedCreditNoteModal = document.getElementById('partialDetailedCreditNoteModal');
        const closePartialDetailedCreditNoteModalBtn = document.getElementById('closePartialDetailedCreditNoteModal');
        const cancelPartialDetailedCreditNoteModalBtn = document.getElementById('cancelPartialDetailedCreditNoteModal');
        const partialDetailedCreditNoteForm = document.getElementById('partialDetailedCreditNoteForm');
        let currentPartialDetailedInvoiceId = null;
        let currentPartialDetailedTotalCost = 0;

        if (closePartialDetailedCreditNoteModalBtn) {
            closePartialDetailedCreditNoteModalBtn.addEventListener('click', () => {
                if (partialDetailedCreditNoteModal) partialDetailedCreditNoteModal.classList.add('hidden');
            });
        }
        if (cancelPartialDetailedCreditNoteModalBtn) {
            cancelPartialDetailedCreditNoteModalBtn.addEventListener('click', () => {
                if (partialDetailedCreditNoteModal) partialDetailedCreditNoteModal.classList.add('hidden');
            });
        }

        if (partialDetailedCreditNoteForm) {
            partialDetailedCreditNoteForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const amountInput = document.getElementById('partialDetailedAmount');
                const errorSpan = document.getElementById('partialDetailedAmountError');
                const amount = parseFloat(amountInput.value);

                errorSpan.classList.add('hidden');

                if (isNaN(amount) || amount <= 0) {
                    errorSpan.textContent = @json(__('messages.Please enter a valid amount greater than 0.'));
                    errorSpan.classList.remove('hidden');
                    return;
                }
                if (amount > currentPartialDetailedTotalCost) {
                    errorSpan.textContent = @json(__('messages.Amount exceeds total cost.'));
                    errorSpan.classList.remove('hidden');
                    return;
                }

                let url = '';
                @if(Auth::user()->hasRole('Admin'))
                    url = '{{ route("admin.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    url = '{{ route("Responsable_de_projet.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @elseif(Auth::user()->hasRole('Employee'))
                    url = '{{ route("employee.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    url = '{{ route("Responsable_gestionnaire.invoice.detailedCreditNote", ":id") }}?type=partial&amount=' + amount;
                @endif
                window.location.href = url.replace(':id', currentPartialDetailedInvoiceId);
            });
        }

        window.openPartialDetailedCreditNoteModal = function(invoiceId, totalCost) {
            currentPartialDetailedInvoiceId = invoiceId;
            currentPartialDetailedTotalCost = parseFloat(totalCost);
            document.getElementById('partialDetailedAmount').value = '';
            document.getElementById('partialDetailedAmountError').classList.add('hidden');
            if (partialDetailedCreditNoteModal) partialDetailedCreditNoteModal.classList.remove('hidden');
        };

        // Invoice Report Modal
        const invoiceReportModal = document.getElementById('invoiceReportModal');
        const openInvoiceReportModalBtn = document.getElementById('openInvoiceReportModal');
        const closeInvoiceReportModalBtn = document.getElementById('closeInvoiceReportModal');
        const cancelInvoiceReportModalBtn = document.getElementById('cancelInvoiceReportModal');
        const confirmInvoiceReportBtn = document.getElementById('confirmInvoiceReport');

        if (openInvoiceReportModalBtn) {
            openInvoiceReportModalBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (invoiceReportModal) invoiceReportModal.classList.remove('hidden');
            });
        }

        if (closeInvoiceReportModalBtn) {
            closeInvoiceReportModalBtn.addEventListener('click', () => {
                if (invoiceReportModal) invoiceReportModal.classList.add('hidden');
            });
        }
        if (cancelInvoiceReportModalBtn) {
            cancelInvoiceReportModalBtn.addEventListener('click', () => {
                if (invoiceReportModal) invoiceReportModal.classList.add('hidden');
            });
        }

        if (confirmInvoiceReportBtn) {
            confirmInvoiceReportBtn.addEventListener('click', function() {
                let baseUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet')) baseUrl = "{{route('Responsable_de_projet.Invoicessrepports.downloadRepports')}}"; @endif
                @if(Auth::user()->hasRole('Admin')) baseUrl = "{{route('admin.Invoicessrepports.downloadRepports')}}"; @endif
                @if(Auth::user()->hasRole('Employee')) baseUrl = "{{route('employee.Invoicessrepports.downloadRepports')}}"; @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) baseUrl = "{{route('Responsable_gestionnaire.Invoicessrepports.downloadRepports')}}"; @endif

                const startDate = document.getElementById('startDate')?.value || '';
                const endDate = document.getElementById('endDate')?.value || '';
                const selectedStatuses = Array.from(document.querySelectorAll('#invoiceReportModal input[type="checkbox"][id^="status_"]:checked')).map(x => x.value);
                const selectedTypes = Array.from(document.querySelectorAll('#invoiceReportModal input[type="checkbox"][id^="type_"]:checked')).map(x => x.value);
                const creditNoteApplied = document.getElementById('credit_note_applied')?.checked;

                const params = new URLSearchParams();
                if (startDate && endDate) {
                    params.append('startDate', startDate);
                    params.append('endDate', endDate);
                }
                if (selectedStatuses.length > 0) {
                    params.append('filters', selectedStatuses.join(','));
                }
                if (selectedTypes.length > 0) {
                    params.append('types', selectedTypes.join(','));
                }
                if (creditNoteApplied) {
                    params.append('credit_note', '1');
                }

                const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
                window.location.href = finalUrl;
                if (invoiceReportModal) invoiceReportModal.classList.add('hidden');
            });
        }

        // Listen for modal open events
        window.addEventListener('open-modal', function(event) {
            const detail = event.detail;
            if (detail.id === 'sendInvoiceModal' && detail.invoiceId) {
                openSendInvoiceModal(detail.invoiceId);
            } else if (detail.id === 'sendPeppolModal' && detail.invoiceId) {
                openSendPeppolModal(detail.invoiceId, detail.invoiceType);
            } else if (detail.id === 'partialCreditNoteModal' && detail.invoiceId && detail.totalCost) {
                openPartialCreditNoteModal(detail.invoiceId, detail.totalCost);
            } else if (detail.id === 'partialDetailedCreditNoteModal' && detail.invoiceId && detail.totalCost) {
                openPartialDetailedCreditNoteModal(detail.invoiceId, detail.totalCost);
            } else if (detail === 'invoiceReportModal' || detail.id === 'invoiceReportModal') {
                if (invoiceReportModal) invoiceReportModal.classList.remove('hidden');
            }
        });
    });

    // [NEW] Filter by status function for cards
    function filterByStatus(status) {
        const paymentFilter = document.getElementById('paymentFilter');
        if (paymentFilter) {
            // Set the value
            paymentFilter.value = status;

            // Dispatch 'change' event so any listeners react
            const event = new Event('change');
            paymentFilter.dispatchEvent(event);
        }
    }

    // Fonction globale pour afficher les messages
    function showMessage(message, type) {
        const messageContainer = document.getElementById('messageContainer');
        const messageText = document.getElementById('messageText');
        const messageTitle = document.getElementById('messageTitle');
        let alertClasses = '';
        let titleText = '';

        const billitNotConfiguredMessage = @json(__('messages.Billit is not configured for your enterprise. Please add your Billit API key in Enterprise Information before sending via Peppol.'));
        const shouldScrollToTop = String(message || '') === billitNotConfiguredMessage;
        const autoHideMs = shouldScrollToTop ? 10000 : 5000;

        if (type === 'success') {
            alertClasses = 'bg-emerald-50 border border-emerald-200 text-emerald-800';
            titleText = @json(__('messages.Success!'));
        } else if (type === 'danger') {
            alertClasses = 'bg-red-50 border border-red-200 text-red-800';
            titleText = @json(__('messages.Error!'));
        } else {
            alertClasses = 'bg-blue-50 border border-blue-200 text-blue-800';
            titleText = @json(__('messages.Info'));
        }

        messageContainer.className = `mb-4 px-4 py-3 rounded-xl relative flex items-center gap-2 ${alertClasses}`;
        messageTitle.textContent = titleText;
        messageText.textContent = message;
        messageContainer.style.display = 'block';

        if (shouldScrollToTop) {
            // This page uses a scrollable <main> (overflow-y-auto), so scrolling the window
            // may do nothing. Prefer scrolling the alert into view (scrolls nearest container).
            requestAnimationFrame(() => {
                try {
                    messageContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
                } catch {
                    // ignore
                }

                const mainScrollContainer = messageContainer.closest('main');
                if (mainScrollContainer && typeof mainScrollContainer.scrollTo === 'function') {
                    try {
                        mainScrollContainer.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
                    } catch {
                        mainScrollContainer.scrollTop = 0;
                    }
                } else {
                    try {
                        window.scrollTo({ top: 0, left: 0, behavior: 'smooth' });
                    } catch {
                        window.scrollTo(0, 0);
                    }
                }
            });
        }

        setTimeout(() => {
            if (messageContainer) {
                messageContainer.style.display = 'none';
            }
        }, autoHideMs);
    }

    // ── Filter + pagination ──
    document.addEventListener('DOMContentLoaded', function () {

        let currentPage = 1;
        let perPage     = 10;
        let filteredRows = [];

        function getDataRows() {
            return Array.from(document.querySelectorAll('#invoicesTable tbody tr.invoice-row'));
        }

        function filterTable() {
            const clientFilter = document.getElementById('clientFilter');
            const projectFilter = document.getElementById('projectFilter');
            const paymentFilter = document.getElementById('paymentFilter');
            const typeFilter = document.getElementById('typeFilter');

            if (clientFilter) clientFilter.addEventListener('change', filterTable);
            if (projectFilter) projectFilter.addEventListener('change', filterTable);
            if (paymentFilter) paymentFilter.addEventListener('change', filterTable);
            if (typeFilter) typeFilter.addEventListener('change', filterTable);

            const cv  = (clientFilter?.value  || '').toLowerCase();
            const pv  = (projectFilter?.value || '').toLowerCase();
            const pay = (paymentFilter?.value || '').toLowerCase();
            const tv  = (typeFilter?.value    || '').toLowerCase();

            let rows = getDataRows().filter(row => {
                const client  = row.querySelector('td:nth-child(4)')?.textContent.trim().toLowerCase() || '';
                const project = row.querySelector('td:nth-child(2)')?.textContent.trim().toLowerCase() || '';
                const type    = row.querySelector('td:nth-child(5)')?.textContent.trim().toLowerCase() || '';
                const isOverdueRow = row.querySelector('.invoice-link-overdue') !== null;
                const paymentStatus = (row.dataset.paymentStatus || '').toLowerCase();
                const invoiceType = type.split('-')[0].trim();

                const clientMatch  = cv === '' || client === cv;
                const projectMatch = pv === '' || project === pv;
                const typeMatch    = tv === '' || invoiceType === tv;

                let paymentMatch = false;
                if (pay === '') {
                    paymentMatch = true;
                } else if (pay === 'overdue') {
                    paymentMatch = isOverdueRow;
                } else {
                    paymentMatch = paymentStatus === pay;
                }

                return clientMatch && projectMatch && paymentMatch && typeMatch;
            });

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
            const disabled = base + ' bg-slate-50 border-slate-100 text-slate-300 cursor-not-allowed pointer-events:none';

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
                    btn.className = base + ' bg-white border-slate-100 text-slate-400 cursor-default pointer-events:none';
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

        document.getElementById('perPageSelect')?.addEventListener('change', function () {
            perPage = parseInt(this.value) || 10;
            currentPage = 1;
            applyPagination();
        });

        filterTable();
    });

    // Mobile accordion per invoice row (show only invoice number when collapsed)
    document.addEventListener('DOMContentLoaded', function () {
        const mql = window.matchMedia('(max-width: 1024px)');

        function setInvoiceRowCollapsed(row, collapsed) {
            if (!row) return;
            row.classList.toggle('is-collapsed', collapsed);
            row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        function initInvoiceRowAccordions() {
            const rows = document.querySelectorAll('#invoicesTable tbody tr.invoice-row');
            const isMobile = mql.matches;
            rows.forEach(row => setInvoiceRowCollapsed(row, isMobile));
        }

        document.addEventListener('click', function (e) {
            const toggleCell = e.target.closest('[data-invoice-toggle]');
            if (!toggleCell) return;

            // Let normal link clicks open the PDF without toggling.
            if (e.target.closest('a')) return;

            const row = toggleCell.closest('tr.invoice-row');
            if (!row) return;

            const collapsed = row.classList.contains('is-collapsed');
            setInvoiceRowCollapsed(row, !collapsed);
        });

        if (mql.addEventListener) mql.addEventListener('change', initInvoiceRowAccordions);
        else if (mql.addListener) mql.addListener(initInvoiceRowAccordions);

        initInvoiceRowAccordions();
    });
</script>
    {{-- Searchable filter selects (Tom Select). Syncs the original selects and
         fires their change events, so existing filter logic keeps working. --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['clientFilter', 'projectFilter', 'paymentFilter', 'typeFilter'].forEach(function (id) {
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
