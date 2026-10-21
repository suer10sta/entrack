<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Devis History - DalyTrac') }}</title>
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
        /* Custom select styling */
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        select:invalid { color: #9ca3af; }
        option { color: #111827; }

        /* Devis link styling */
        .devis-link-normal {
             color: #2563eb; /* blue-600 */
             font-weight: 500; /* medium */
         }
         .devis-link-normal:hover {
             color: #1d4ed8; /* blue-800 */
             text-decoration: underline;
         }

        /* --- STYLES COPIED FROM INVOICE FILE --- */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            margin: 0;
            padding: 0;
        }
        .table {
            width: 100%;
            margin-bottom: 0;
        }
        .table td:last-child {
            white-space: nowrap;
        }
        .table td, .table th {
            padding: 0.75rem;
        }

        /* Mobile: show devis rows as modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .table-responsive {
                overflow-x: visible !important;
            }

            #devisTable thead {
                display: none;
            }

            #devisTable tbody {
                display: block;
                width: 100%;
            }

            #devisTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
                box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
                transition: transform 180ms ease, box-shadow 180ms ease;
            }

            #devisTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 12px !important;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.78);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
            }

            #devisTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #devisTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 6px;
            }

            /* Mobile accordion: collapsed row shows only devis number */
            #devisTable tbody tr.devis-row.is-collapsed td:not(.devis-toggle-cell) {
                display: none !important;
            }
            #devisTable tbody tr.devis-row td.devis-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            /* We render the label inline in markup for this cell */
            #devisTable tbody tr.devis-row td.devis-toggle-cell[data-label]::before {
                content: none !important;
            }
            #devisTable tbody tr.devis-row td.devis-toggle-cell .devis-chevron {
                transition: transform 180ms ease;
            }
            #devisTable tbody tr.devis-row[aria-expanded="true"] td.devis-toggle-cell .devis-chevron {
                transform: rotate(180deg);
            }
        }

        #devisTable th {
            font-size: 11px; font-weight: 700; letter-spacing: .07em;
            text-transform: uppercase; color: #64748b;
            padding: 10px 12px; white-space: nowrap;
            background: #f8fafc; border-bottom: 1px solid #e2e8f0;
        }
        #devisTable td {
            padding: 9px 12px; font-size: 13px;
            vertical-align: middle; border-bottom: 1px solid #f1f5f9;
        }
        #devisTable tbody tr:last-child td { border-bottom: none; }
        #devisTable tbody tr:hover td { background: #f8fafc; }
        #devisTable tbody tr.row-sent td { background: rgba(236,253,245,.55); }
        #devisTable tbody tr.row-sent:hover td { background: rgba(209,250,229,.7); }

        .devis-link-normal { color: #2D7080; font-weight: 600; }
        .devis-link-normal:hover { color: #0B2638; text-decoration: underline; }
        .inv-type-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 99px;
            font-size: 11px; font-weight: 600; white-space: nowrap;
        }
        .devis-action {
            display: inline-flex; align-items: center; justify-content: center;
            gap: 4px; height: 26px; padding: 0 10px; border-radius: 6px;
            flex-shrink: 0; cursor: pointer; font-size: 11.5px; font-weight: 600;
            white-space: nowrap; transition: background .15s, border-color .15s;
            outline: none; line-height: 1; text-decoration: none;
        }
        .devis-action svg { width: 12px; height: 12px; flex-shrink: 0; }
        .devis-action-row { display: flex; flex-wrap: wrap; align-items: center; gap: 3px; justify-content: flex-start; }
        .devis-action-primary { background:#f0f4f8; color:#0B2638; border:1px solid #b0bec5; }
        .devis-action-primary:hover { background:#e2eaf0; border-color:#78909c; }
        .devis-action-neutral { background:#f0fafb; color:#2D7080; border:1px solid #99d6de; }
        .devis-action-neutral:hover { background:#e0f2f1; border-color:#2D7080; }
        .devis-action-success { background:#f0fdf4; color:#15803d; border:1px solid #86efac; }
        .devis-action-success:hover { background:#dcfce7; border-color:#4ade80; }
        .devis-action-danger { background:#fff1f2; color:#b91c1c; border:1px solid #fca5a5; }
        .devis-action-danger:hover { background:#fee2e2; border-color:#f87171; }
        .devis-action-muted { background:#fffbeb; color:#92400e; border:1px solid #fcd34d; }
        .devis-action-muted:hover { background:#fef3c7; border-color:#f59e0b; }
        .devis-action-delete { background:#dc2626; color:#fff; border:1px solid #dc2626; }
        .devis-action-delete:hover { background:#b91c1c; border-color:#b91c1c; }

        #devisTable .devis-acceptance-cell .devis-action-row form button {
            display: inline-flex; align-items: center; justify-content: center; gap: 4px;
            height: 26px; padding: 0 10px; border-radius: 6px;
            font-size: 11.5px; font-weight: 600; line-height: 1;
            white-space: nowrap; box-shadow: none;
        }
        #devisTable .devis-acceptance-cell .devis-action-row form:first-child button {
            background: #f0fdf4; color: #15803d; border: 1px solid #86efac;
        }
        #devisTable .devis-acceptance-cell .devis-action-row form:first-child button:hover {
            background: #dcfce7; border-color: #4ade80;
        }
        #devisTable .devis-acceptance-cell .devis-action-row form:nth-child(2) button {
            background: #fff1f2; color: #b91c1c; border: 1px solid #fca5a5;
        }
        #devisTable .devis-acceptance-cell .devis-action-row form:nth-child(2) button:hover {
            background: #fee2e2; border-color: #f87171;
        }
        #devisTable .devis-acceptance-cell .devis-action-row form button::before {
            width: 12px; height: 12px; flex-shrink: 0; content: ""; background: currentColor;
            -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat;
            mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat;
        }
        #devisTable .devis-acceptance-cell .devis-action-row form:nth-child(2) button::before {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12'/%3E%3C/svg%3E");
        }

        .dt-filter-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2394a3b8' stroke-width='2'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 10px center; background-size: 14px;
            padding-right: 32px;
        }
        .stat-card-clickable { cursor: pointer; transition: box-shadow .18s, border-color .18s; }
        .stat-card-clickable:hover { box-shadow: 0 4px 24px -4px rgba(11,38,56,.18); border-color: #c9d9da; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; margin: 0; padding: 0; }

        @media (max-width: 1024px) {
            .table-responsive { overflow-x: visible !important; }
            #devisTable thead { display: none; }
            #devisTable tbody { display: block; width: 100%; }
            #devisTable tbody tr.devis-row {
                display: grid; grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 8px; margin-bottom: 12px; padding: 12px;
                border: 1px solid rgba(148, 163, 184, .45); border-radius: 14px;
                background: linear-gradient(160deg, #fff 0%, #f8fafc 100%);
                box-shadow: 0 2px 16px -4px rgba(11,38,56,.10);
            }
            #devisTable tbody tr.devis-row > td {
                display: block; width: 100%; padding: 8px 10px !important;
                border: 1px solid rgba(226,232,240,.85); border-radius: 10px;
                background: rgba(255,255,255,.82); text-align: left !important;
                white-space: normal !important; word-break: break-word;
            }
            #devisTable tbody tr.devis-row > td[data-span="2"] { grid-column: span 2 / span 2; }
            #devisTable tbody tr.devis-row > td[data-label]::before {
                content: attr(data-label); display: block; margin-bottom: 4px;
                color: var(--dt-text-muted, #5b6b7e); font-size: 10px; font-weight: 700;
                letter-spacing: .06em; text-transform: uppercase;
            }
            #devisTable tbody td.devis-actions-cell .devis-action-row {
                justify-content: center !important; flex-wrap: wrap !important; gap: 8px !important;
            }
            #devisTable tbody td.devis-actions-cell form,
            #devisTable tbody td.devis-actions-cell button,
            #devisTable tbody td.devis-actions-cell a { flex: 0 0 auto; }
            #devisTable tbody tr.devis-row.is-collapsed td:not(.devis-toggle-cell) { display: none !important; }
            #devisTable tbody tr.devis-row td.devis-toggle-cell { cursor: pointer; user-select: none; }
            #devisTable tbody tr.devis-row td.devis-toggle-cell[data-label]::before { content: none !important; }
            #devisTable tbody tr.devis-row td.devis-toggle-cell .devis-chevron { transition: transform 180ms ease; }
            #devisTable tbody tr.devis-row[aria-expanded="true"] td.devis-toggle-cell .devis-chevron { transform: rotate(180deg); }
        }
</style>
</head>

<body class="devis-dashboard-page dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden" x-data>
    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] px-3 py-4 sm:px-5 sm:py-5 lg:px-16 lg:py-7">
        {{-- 1. Header Section --}}
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2 mb-4">
                <div class="min-w-0">
                    <p class="text-[10px] font-semibold uppercase tracking-[.08em] text-slate-400 mb-0.5">{{ __('messages.Devis') }}</p>
                    <h1 class="text-[18px] sm:text-[20px] lg:text-[22px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Devis History') }}</h1>
                </div>
                <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
                    <button type="button" id="openDevisReportModal" class="dt-btn dt-btn-primary text-[12px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        {{ __('messages.Devis Reports PDF') }}
                    </button>

                    @php $archiveRoute = ''; @endphp
                    @if(Auth::user()->hasRole('Admin')) @php $archiveRoute = route('admin.devisArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) @php $archiveRoute = route('Responsable_de_projet.devisArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Employee')) @php $archiveRoute = route('employee.devisArchived.archiveIndex'); @endphp @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $archiveRoute = route('Responsable_gestionnaire.devisArchived.archiveIndex'); @endphp @endif

                    @if($archiveRoute)
                    <a href="{{ $archiveRoute }}" class="dt-btn dt-btn-ghost text-[12px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                         </svg>
                        {{ __('messages.View Archive') }}
                    </a>
                    @endif
                </div>
            </div>

            {{-- JS Message Container --}}
             <div id="messageContainer" style="display: none;" class="px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold" id="messageTitle"></strong>
                <span class="block sm:inline" id="messageText"></span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
             </div>
             {{-- Session Message --}}
             @if(session('message'))
             <div id="sessionMessageContainer" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-4 relative" role="alert">
                 <svg class="w-4 h-4 flex-shrink-0 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                 <span class="text-sm font-medium">{{ session('message') }}</span>
                 <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-2.5 right-3 text-emerald-500 hover:text-emerald-700 text-lg leading-none" aria-label="{{ __('messages.Close') }}">&times;</button>
             </div>
             @endif

             {{-- 3. Totals  Cards Section --}}
             @php
                 $currentYear = date('Y');
                 $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 1)->sum('budget');
                 $Total_Declined = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 2)->sum('budget');
                 $Total_Pending = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 0)->sum('budget');
                 
                 // Current year totals
                 $Total_AccpetedCurrentYear = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                        ->where('acceptanceStatus', 1)
                                                        ->whereYear('created_at', $currentYear)
                                                        ->sum('budget');
                 $Total_DeclinedCurrentYear = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                        ->where('acceptanceStatus', 2)
                                                        ->whereYear('created_at', $currentYear)
                                                        ->sum('budget');
                 $Total_PendingCurrentYear = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)
                                                        ->where('acceptanceStatus', 0)
                                                        ->whereYear('created_at', $currentYear)
                                                        ->sum('budget');
             @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 mb-4">
                <div id="cardAccepted" class="dt-card stat-card-clickable p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-emerald-50 ring-1 ring-emerald-600/15 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL ACCEPTED') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-emerald-600 leading-tight">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">{{ $currentYear }}: €{{ number_format($Total_AccpetedCurrentYear, 2, ',', ' ') }}</p>
                    </div>
                </div>
                 {{-- Total Declined Card --}}
                 {{-- [NEW] Added id="cardDeclined" and "cursor-pointer" --}}
                <div id="cardDeclined" class="dt-card stat-card-clickable p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-red-50 ring-1 ring-red-600/15 flex items-center justify-center">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL DECLINED') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-red-600 leading-tight">€{{ number_format($Total_Declined, 2, ',', ' ') }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">{{ $currentYear }}: €{{ number_format($Total_DeclinedCurrentYear, 2, ',', ' ') }}</p>
                    </div>
                </div>
                 {{-- Total Pending Card --}}
                 {{-- [NEW] Added id="cardPending" and "cursor-pointer" --}}
                <div id="cardPending" class="dt-card stat-card-clickable p-3 sm:p-4 flex items-center gap-2 sm:gap-3">
                    <div class="flex-shrink-0 w-8 h-8 sm:w-10 sm:h-10 rounded-xl bg-[#E7B84B]/15 ring-1 ring-[#E7B84B]/30 flex items-center justify-center">
                         <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#c9971f]" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                         </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[9px] sm:text-[10.5px] font-semibold uppercase tracking-[.07em] text-slate-400 truncate">{{ __('messages.TOTAL PENDING') }}</p>
                        <p class="text-[13px] sm:text-[17px] font-bold text-[#0B2638] leading-tight">€{{ number_format($Total_Pending, 2, ',', ' ') }}</p>
                        <p class="text-[10px] sm:text-[11px] text-slate-400 mt-0.5 truncate">{{ $currentYear }}: €{{ number_format($Total_PendingCurrentYear, 2, ',', ' ') }}</p>
                    </div>
                </div>
            </div>

            {{-- 4. Filters Section --}}
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
                        <label for="acceptanceFilter" class="block text-[11px] font-semibold uppercase tracking-[.07em] text-slate-400 mb-1.5">{{ __('messages.Filter by Acceptance') }}</label>
                        <select id="acceptanceFilter" class="dt-filter-select block w-full rounded-lg border border-slate-200 bg-slate-50 text-[13px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] py-2 px-3 transition-all">
                            <option value="">{{ __('messages.All Devis') }}</option>
                            <option value="{{ __('messages.Accepted') }}">{{ __('messages.Accepted') }}</option>
                            <option value="{{ __('messages.Declined') }}">{{ __('messages.Declined') }}</option>
                            <option value="{{ __('messages.Pending') }}">{{ __('messages.Pending') }}</option>
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

            {{-- 5. Table Section [STYLES UPDATED] --}}
            <div class="dt-card overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-5 py-3.5 border-b border-slate-100">
                    <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.Devis History') }}</p>
                    <div class="flex items-center gap-3 sm:gap-4">
                        <div class="flex items-center gap-2 text-[12px] text-slate-500">
                            <span class="hidden sm:inline">{{ __('messages.Rows per page') }}:</span>
                            <select id="devisPerPage" class="dt-filter-select rounded-lg border border-slate-200 bg-white text-[12px] text-slate-700 py-1 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080] cursor-pointer">
                                <option value="10" selected>10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                            </select>
                        </div>
                        <span class="dt-badge dt-badge-navy text-[11px]"><span class="dt-dot"></span>{{ $devis->count() }} {{ __('messages.Total') }}</span>
                    </div>
                </div>
                <div class="table-responsive overflow-x-auto">
                    <table class="w-full" id="devisTable">
                        <thead>
                            <tr>
                                <th class="text-left pl-5">{{ __('messages.Devis N') }}</th>
                                <th class="text-left">{{ __('messages.Project Name') }}</th>
                                <th class="text-left">{{ __('messages.Client Name / Emails') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Budget') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Unbilled Budget') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Created at') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Status') }}</th>
                                <th class="text-left whitespace-nowrap">{{ __('messages.Acceptance') }}</th>
                                <th class="text-left w-[25rem]">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($devis as $d)
                            <tr class="devis-row {{ $d->sent == 1 ? 'row-sent' : '' }}" data-devis-row aria-expanded="true">
                                <td data-label="{{ __('messages.Devis N') }}" data-span="2" class="px-4 py-3 whitespace-nowrap text-sm devis-toggle-cell" data-devis-toggle>
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                        <a href="{{ route('Responsable_de_projet.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Admin'))
                                        <a href="{{ route('admin.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                        <a href="{{ route('employee.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <a href="{{ route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number) }}" class="devis-link-normal hidden lg:inline">{{$entreprise->devis_prefix}}-{{$d->devis_number}}</a>
                                    @endif

                                    {{-- Mobile accordion header --}}
                                    <div class="flex items-start justify-between gap-3 lg:hidden">
                                        <div class="flex items-baseline gap-2 min-w-0">
                                            <span class="text-[11px] leading-none font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                                {{ __('messages.Devis N') }}
                                            </span>
                                            @php
                                                $devisRoute = '';
                                                if(Auth::user()->hasRole('Responsable de projet')) $devisRoute = route('Responsable_de_projet.devis.downloadDevis', $d->devis_number);
                                                elseif(Auth::user()->hasRole('Admin')) $devisRoute = route('admin.devis.downloadDevis', $d->devis_number);
                                                elseif(Auth::user()->hasRole('Employee')) $devisRoute = route('employee.devis.downloadDevis', $d->devis_number);
                                                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $devisRoute = route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number);
                                            @endphp
                                            <a href="{{ $devisRoute ?: '#' }}" class="devis-link-normal truncate leading-none">
                                                {{$entreprise->devis_prefix}}-{{$d->devis_number}}
                                            </a>
                                        </div>
                                        <span class="shrink-0 mt-0.5 text-slate-400">
                                            <svg class="devis-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('messages.Project Name') }}" data-span="2" class="px-4 py-3 text-sm text-gray-800 font-medium whitespace-normal break-words">
                                    @php $project = \App\Models\Project::where('id', $d->project_id)->first(); @endphp
                                    @if($project)
                                        @if($d->acceptanceStatus == 1 || $d->Existing_project_name != null)
                                            @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.projects.show', $project->id)}}" class="devis-link-normal">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.projects.show', $project->id)}}" class="devis-link-normal">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.projects.show', $project->id)}}" class="devis-link-normal">{{$project->name}}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.projects.show', $project->id)}}" class="devis-link-normal">{{$project->name}}</a> @endif
                                        @else {{$project->name}} @endif
                                    @else {{ __('messages.N/A') }} @endif
                                </td>
                                <td data-label="{{ __('messages.Client Name / Emails') }}" data-span="2" class="px-4 py-3 text-sm text-gray-800 font-medium whitespace-normal break-words">
                                    @php $client = \App\Models\Client::where('id', $d->client_id)->first(); @endphp
                                    @if ($d->emails)
                                        @php $emailArray = array_map('trim', explode(',', $d->emails)); @endphp
                                        @foreach ($emailArray as $email) <span class="block">{{ $email }}</span> @endforeach
                                    @elseif ($client)
                                        @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.clients.show', $client->id)}}" class="devis-link-normal">{{$client->name}}</a> @endif
                                        @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.clients.show', $client->id)}}" class="devis-link-normal">{{$client->name}}</a> @endif
                                        @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.clients.show', $client->id)}}" class="devis-link-normal">{{$client->name}}</a> @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.clients.show', $client->id)}}" class="devis-link-normal">{{$client->name}}</a> @endif
                                    @else {{ __('messages.N/A') }} @endif
                                </td>
                                <td data-label="{{ __('messages.Budget') }}" class="whitespace-nowrap">
                                    <span class="text-[13.5px] font-bold text-emerald-600">€{{ number_format($d->budget, 2, ',', ' ') }}</span>
                                </td>
                                <td data-label="{{ __('messages.Unbilled Budget') }}" class="whitespace-nowrap">
                                    @php
                                        $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)->where('Payment_status', '!=', -1)->sum('total_htva');
                                        $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                    @endphp
                                    <span class="text-[13px] font-semibold text-[#0B2638]">€{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}</span>
                                </td>
                                <td data-label="{{ __('messages.Created at') }}" class="text-[12px] text-slate-500 whitespace-nowrap">
                                    {{ $d->created_at ? \Carbon\Carbon::parse($d->created_at)->format('Y-m-d') : '' }}
                                </td>
                                <td data-label="{{ __('messages.Status') }}" class="whitespace-nowrap">
                                    @if($d->acceptanceStatus == 1)
                                    <span class="inv-type-badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/15">{{ __('messages.Accepted') }}</span>
                                    @elseif($d->acceptanceStatus == 2 )
                                    <span class="inv-type-badge bg-rose-50 text-rose-700 ring-1 ring-rose-600/15">{{ __('messages.Declined') }}</span>
                                    @else
                                    <span class="inv-type-badge bg-amber-50 text-amber-700 ring-1 ring-amber-600/15">{{ __('messages.Pending') }}</span>
                                    @endif
                                </td>
                                <td data-label="{{ __('messages.Acceptance') }}" data-span="2" class="devis-acceptance-cell">
                                    <div class="devis-action-row">
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                        <form action="{{route('Responsable_de_projet.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Accepted?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('Responsable_de_projet.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Declined?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Admin'))
                                        <form action="{{route('admin.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Accepted?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('admin.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Declined?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                        <form action="{{route('employee.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Accepted?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('employee.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Declined?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                        <form action="{{route('Responsable_gestionnaire.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Accepted?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-green-500 hover:bg-green-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Accept') }}</button> </form>
                                        <form action="{{route('Responsable_gestionnaire.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm(@js(__('messages.Mark as Declined?')));" class="inline-block"> @csrf <button type="submit" class="text-white bg-red-500 hover:bg-red-600 font-medium rounded text-xs px-3 py-1 text-center whitespace-nowrap">{{ __('messages.Decline') }}</button> </form>
                                        @endif
                                    </div>
                                </td>
                                
                                <td data-label="{{ __('messages.Actions') }}" data-span="2" class="devis-actions-cell">
                                        <div class="devis-action-row">
                                
                                        {{-- 2x2 Grid for first 4 buttons --}}
                                        <div class="devis-action-row w-full">
                                            
                                            {{-- 1. Download Button --}}
                                            @php $dlRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $dlRoute = route('admin.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $dlRoute = route('Responsable_de_projet.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $dlRoute = route('employee.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $dlRoute = route('Responsable_gestionnaire.devis.downloadDevis', $d->devis_number); @endphp @endif
                                            <a class="devis-action devis-action-primary" href="{{ $dlRoute }}"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 17v3h14v-3"/></svg>{{ __('messages.Download') }}</a>

                                            {{-- 2. Send Button --}}
                                            @can('Send devis')
                                                @php $sendRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin')) @php $sendRoute = route('admin.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) @php $sendRoute = route('Responsable_de_projet.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Employee')) @php $sendRoute = route('employee.devis.sendDevis'); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $sendRoute = route('Responsable_gestionnaire.devis.sendDevis'); @endphp @endif
                                                <form action="{{ $sendRoute }}" method="post" class="inline-block"> @csrf <input type="hidden" name="devis_id" value="{{ $d->id }}"> <button type="submit" class="devis-action devis-action-neutral"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l9 6 9-6M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"/></svg>{{ __('messages.Send') }}</button> </form>
                                            @else
                                                <div class="w-full"></div> @endcan
                                
                                            {{-- 3. Delete Button --}}
                                            @can('Delete devis')
                                                @php $deleteRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin')) @php $deleteRoute = route('admin.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) @php $deleteRoute = route('Responsable_de_projet.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Employee')) @php $deleteRoute = route('employee.devis.destroy', $d->id); @endphp @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $deleteRoute = route('Responsable_gestionnaire.devis.destroy', $d->id); @endphp @endif
                                                <form action="{{$deleteRoute}}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));" class="inline-block"> @csrf @method('DELETE') <button type="submit" class="devis-action devis-action-delete"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16"/></svg>{{ __('messages.Delete') }}</button> </form>
                                            @else
                                                 <div class="w-full"></div> @endcan
                                
                                            {{-- 4. Archive Button --}}
                                            @php $archiveFormRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $archiveFormRoute = route('admin.devis.archive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $archiveFormRoute = route('Responsable_de_projet.devis.archive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $archiveFormRoute = route('employee.devis.archive', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $archiveFormRoute = route('Responsable_gestionnaire.devis.archive', $d->id); @endphp @endif
                                            <form action="{{$archiveFormRoute}}" method="post" onsubmit="return confirm(@js(__('messages.Are you sure?')));" class="inline-block"> @csrf <button type="submit" title="{{ __('messages.Archive') }}" class="devis-action devis-action-muted"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8h18M5 8v11h14V8M3 8l1-4h16l1 4M10 12h4"/></svg>{{ __('messages.Archive') }}</button> </form>
                                        </div>
                                
                                        {{-- Icon buttons below the grid --}}
                                        <div class="devis-action-row w-full">
                                            
                                            {{-- Duplicate Button --}}
                                            @php $duplicateRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $duplicateRoute = route('admin.devis.duplicateDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $duplicateRoute = route('Responsable_de_projet.devis.duplicateDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $duplicateRoute = route('employee.devis.duplicateDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $duplicateRoute = route('Responsable_gestionnaire.devis.duplicateDevis', $d->id); @endphp @endif
                                            <a href="{{$duplicateRoute}}" class="devis-action devis-action-neutral"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>{{ __('messages.Duplicate') }}</a>
                                
                                            {{-- Edit Button --}}
                                            @php $editRoute = '#'; @endphp
                                            @if(Auth::user()->hasRole('Admin')) @php $editRoute = route('admin.devis.editDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet')) @php $editRoute = route('Responsable_de_projet.devis.editDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Employee')) @php $editRoute = route('employee.devis.editDevis', $d->id); @endphp @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $editRoute = route('Responsable_gestionnaire.devis.editDevis', $d->id); @endphp @endif
                                            <a href="{{$editRoute}}" class="devis-action devis-action-neutral"><svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M4 20h4l9.5-9.5-4-4L4 16v4z"/></svg>{{ __('messages.Edit') }}</a>
                                        </div>
                                
                                    </div>
                                </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center px-4 py-6 text-gray-500">
                                        {{ __('messages.No devis found matching your criteria.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-wrap items-center justify-end gap-2 px-5 py-3 border-t border-slate-100 bg-[#f8fafc]">
                    <div class="flex items-center gap-3">
                        <span id="devisPaginationInfo" class="text-[12px] text-slate-500"></span>
                        <nav id="devisPaginationControls" class="flex items-center gap-1" aria-label="{{ __('messages.Pagination Navigation') }}"></nav>
                    </div>
                </div>
            </div>
        </main>
    </div>

{{-- Modals --}}
<div id="devisReportModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white w-full max-w-md mx-4 overflow-hidden">
        <div class="flex justify-between items-center px-5 py-4 border-b border-slate-100">
            <h5 class="text-[15px] font-bold text-[#0B2638]" id="devisReportModalLabel">{{ __('messages.Download Devis Reports') }}</h5>
            <button type="button" id="closeDevisReportModal" class="text-slate-400 hover:text-slate-700" aria-label="{{ __('messages.Close') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6">
            <div class="mb-3">
                <label class="block text-sm font-medium text-gray-700">{{ __('messages.Choose statuses (optional):') }}</label>
                <div class="mt-2 space-y-2">
                    <div class="flex items-center">
                        <input id="status_accepted" name="devisReportStatus[]" type="checkbox" value="accepted" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="status_accepted" class="ml-2 block text-sm text-gray-900">{{ __('messages.Accepted') }}</label>
                    </div>
                    <div class="flex items-center">
                        <input id="status_declined" name="devisReportStatus[]" type="checkbox" value="declined" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="status_declined" class="ml-2 block text-sm text-gray-900">{{ __('messages.Declined') }}</label>
                    </div>
                    <div class="flex items-center">
                        <input id="status_pending" name="devisReportStatus[]" type="checkbox" value="pending" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="status_pending" class="ml-2 block text-sm text-gray-900">{{ __('messages.Pending') }}</label>
                    </div>
                    <hr class="my-3">
                    <div class="flex items-center">
                        <input id="include_archived" type="checkbox" value="1" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <label for="include_archived" class="ml-2 block text-sm text-gray-900">{{ __('messages.Include archived') }}</label>
                    </div>
                </div>
            </div>
            <small class="text-gray-500">{{ __('messages.Date range (optional) will be applied from the fields at the top.') }}</small>
        </div>
        <div class="flex flex-col-reverse sm:flex-row justify-end items-stretch sm:items-center gap-2 p-4 bg-slate-50 border-t border-slate-100">
            <button type="button" id="cancelDevisReportBtn" class="dt-btn dt-btn-ghost justify-center">{{ __('messages.Cancel') }}</button>
            <button type="button" id="confirmDevisReport" class="dt-btn dt-btn-primary justify-center"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0l-4-4m4 4l4-4M5 17v3h14v-3"/></svg>{{ __('messages.Download PDF') }}</button>
        </div>
    </div>
</div>
<div id="sendDevisModal" class="fixed inset-0 z-40 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white w-full max-w-lg mx-4 overflow-hidden">
        <div class="flex justify-between items-center px-5 py-4 border-b border-slate-100">
            <h5 class="text-[15px] font-bold text-[#0B2638]" id="sendDevisModalLabel">{{ __('messages.Send Devis') }}</h5>
            <button type="button" id="closeSendDevisModal" class="text-slate-400 hover:text-slate-700" aria-label="{{ __('messages.Close') }}"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>
        <div class="p-6">
            <form id="sendDevisForm" class="devis-form" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="devis_id" id="devis_id">
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Select Recipients Devis') }}</label>
                    <div id="recipientsList" class="border p-3 rounded-md h-32 overflow-y-auto bg-gray-50 space-y-2">
                         </div>
                </div>

                <div class="mb-3">
                    <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Additional Attachments (PDF only):') }}</label>
                    <input type="file" class="block w-full text-sm text-gray-700 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none file:bg-gray-100 file:border-0 file:px-3 file:py-2" id="attachments" name="attachments[]" multiple accept=".pdf">
                    <small class="text-gray-500">{{ __('messages.Select PDF files to be merged with the devis') }}</small>
                </div>
            </form>
        </div>
        <div class="flex flex-col-reverse sm:flex-row justify-end items-stretch sm:items-center gap-2 p-4 bg-slate-50 border-t border-slate-100">
            <button type="button" id="cancelSendDevisModal" class="dt-btn dt-btn-ghost justify-center">{{ __('messages.Close') }}</button>
            <button type="button" class="dt-btn dt-btn-primary justify-center" id="confirmSend"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4l16 8-16 8 3-8-3-8zm3 8h13"/></svg>{{ __('messages.Send Devis') }}</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // --- 1. FILTER LOGIC ---
    const clientFilter = document.getElementById('clientFilter');
    const acceptanceFilter = document.getElementById('acceptanceFilter');
    const tableBody = document.querySelector('#devisTable tbody');
    const allRows = tableBody.querySelectorAll('tr');
    const dataRows = Array.from(allRows).filter(row => !row.querySelector('td[colspan="9"]'));
    const perPageSelect = document.getElementById('devisPerPage');
    const paginationInfo = document.getElementById('devisPaginationInfo');
    const paginationControls = document.getElementById('devisPaginationControls');
    let currentPage = 1;
    let perPage = parseInt(perPageSelect.value, 10) || 10;
    let filteredRows = [];
    
    // [NEW] Get the new clickable cards
        const cardAccepted = document.getElementById('cardAccepted');
    const cardDeclined = document.getElementById('cardDeclined');
    const cardPending = document.getElementById('cardPending');
    
    // [NEW] Add click listeners for the stat cards
    if (cardAccepted) {
        cardAccepted.addEventListener('click', function() {
            acceptanceFilter.value = @json(__('messages.Accepted')); // Set dropdown value
            filterTable(); // Call existing filter function
        });
    }

    if (cardDeclined) {
        cardDeclined.addEventListener('click', function() {
            acceptanceFilter.value = @json(__('messages.Declined'));
            filterTable();
        });
    }

    if (cardPending) {
        cardPending.addEventListener('click', function() {
            acceptanceFilter.value = @json(__('messages.Pending'));
            filterTable();
        });
    }
    
    function filterTable() {
        const clientValue = clientFilter.value.trim();
        const acceptanceValue = acceptanceFilter.value.trim();
        filteredRows = dataRows.filter(row => {
            const clientCell = row.querySelector('td:nth-child(3)');
            const statusCell = row.querySelector('td:nth-child(7)'); // 7th column is Status
            if (!clientCell || !statusCell || !statusCell.querySelector('span')) return false;

            const clientName = clientCell.textContent.trim();
            const statusText = statusCell.querySelector('span').textContent.trim();
            const clientMatch = clientValue === "" || clientName === clientValue;
            const acceptanceMatch = acceptanceValue === "" || statusText === acceptanceValue;
            return clientMatch && acceptanceMatch;
        });

        currentPage = 1;
        applyPagination();
    }

    function applyPagination() {
        const total = filteredRows.length;
        const totalPages = Math.max(1, Math.ceil(total / perPage));
        currentPage = Math.min(currentPage, totalPages);
        const start = (currentPage - 1) * perPage;
        const end = Math.min(start + perPage, total);
        dataRows.forEach(row => row.style.display = 'none');
        filteredRows.slice(start, end).forEach(row => row.style.display = '');

        if (paginationInfo) paginationInfo.textContent = total ? `${start + 1}–${end} / ${total}` : '';
        const noResultsRow = tableBody.querySelector('td[colspan="9"]');
        if (noResultsRow) noResultsRow.parentElement.style.display = total ? 'none' : '';
        renderPaginationControls(totalPages);
    }

    function renderPaginationControls(totalPages) {
        paginationControls.replaceChildren();
        const base = 'inline-flex items-center justify-center min-w-[28px] h-[28px] px-1.5 rounded-lg text-[12px] font-medium transition-colors cursor-pointer border select-none';
        const normal = `${base} bg-white border-slate-200 text-slate-600 hover:bg-slate-50`;
        const active = `${base} bg-[#2D7080] border-[#2D7080] text-white`;
        const disabled = `${base} bg-slate-50 border-slate-100 text-slate-300 cursor-not-allowed pointer-events-none`;

        function addArrow(direction, isDisabled) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = isDisabled ? disabled : normal;
            button.disabled = isDisabled;
            button.setAttribute('aria-label', direction === 'previous' ? @json(__('messages.Previous')) : @json(__('messages.Next')));
            button.setAttribute('aria-label', direction === 'previous' ? @json(__('messages.Previous')) : @json(__('messages.Next')));
            button.innerHTML = direction === 'previous'
                ? '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>'
                : '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';
            button.addEventListener('click', () => {
                currentPage += direction === 'previous' ? -1 : 1;
                applyPagination();
            });
            paginationControls.appendChild(button);
        }

        addArrow('previous', currentPage === 1);
        const range = [];
        for (let page = 1; page <= totalPages; page++) {
            if (page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)) range.push(page);
            else if (range[range.length - 1] !== '…') range.push('…');
        }
        range.forEach(page => {
            const button = document.createElement('button');
            button.type = 'button';
            if (page === '…') {
                button.className = `${base} bg-white border-slate-100 text-slate-400 cursor-default pointer-events-none`;
                button.textContent = '…';
                button.disabled = true;
            } else {
                button.className = page === currentPage ? active : normal;
                button.textContent = page;
                button.setAttribute('aria-label', `${@json(__('messages.Page'))} ${page}`);
                if (page === currentPage) button.setAttribute('aria-current', 'page');
                button.setAttribute('aria-label', `${@json(__('messages.Page'))} ${page}`);
                if (page === currentPage) button.setAttribute('aria-current', 'page');
                button.addEventListener('click', () => { currentPage = page; applyPagination(); });
            }
            paginationControls.appendChild(button);
        });
        addArrow('next', currentPage === totalPages);
    }
    
    if (clientFilter) clientFilter.addEventListener('change', filterTable);
    if (acceptanceFilter) acceptanceFilter.addEventListener('change', filterTable);
    perPageSelect.addEventListener('change', function () {
        perPage = parseInt(this.value, 10) || 10;
        currentPage = 1;
        applyPagination();
    });
    
    // Initial filter on load
    filterTable();
    
    // --- 2. DEVIS REPORT MODAL LOGIC ---
    const devisReportModal = document.getElementById('devisReportModal');
    const openDevisReportModalBtn = document.getElementById('openDevisReportModal');
    const closeDevisReportModalBtn = document.getElementById('closeDevisReportModal');
    const cancelDevisReportModalBtn = document.getElementById('cancelDevisReportBtn');
    const confirmDevisReportBtn = document.getElementById('confirmDevisReport');

    if (openDevisReportModalBtn) {
        openDevisReportModalBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (devisReportModal) devisReportModal.classList.remove('hidden');
        });
    }
    if (closeDevisReportModalBtn) {
        closeDevisReportModalBtn.addEventListener('click', () => {
            if (devisReportModal) devisReportModal.classList.add('hidden');
        });
    }
    if (cancelDevisReportModalBtn) {
        cancelDevisReportModalBtn.addEventListener('click', () => {
            if (devisReportModal) devisReportModal.classList.add('hidden');
        });
    }
    if (confirmDevisReportBtn) {
        confirmDevisReportBtn.addEventListener('click', function() {
            let baseUrl = '';
            try {
                @if(Auth::user()->hasRole('Responsable de projet'))
                baseUrl = "{{route('Responsable_de_projet.Devissrepports.downloadRepports')}}";
                @endif
                @if(Auth::user()->hasRole('Admin'))
                baseUrl = "{{route('admin.Devissrepports.downloadRepports')}}";
                @endif
                @if(Auth::user()->hasRole('Employee'))
                baseUrl = "{{route('employee.Devissrepports.downloadRepports')}}";
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                baseUrl = "{{route('Responsable_gestionnaire.Devissrepports.downloadRepports')}}";
                @endif
            } catch (e) { console.error("Error setting base URL:", e); }

            const startDate = document.getElementById('startDate')?.value || '';
            const endDate = document.getElementById('endDate')?.value || '';
            const selectedStatuses = Array.from(document.querySelectorAll('input[name="devisReportStatus[]"]:checked')).map(x => x.value);
            const includeArchived = document.getElementById('include_archived')?.checked;

            const params = new URLSearchParams();
            if (startDate && endDate) {
                params.append('startDate', startDate);
                params.append('endDate', endDate);
            }
            if (selectedStatuses.length > 0) {
                params.append('filters', selectedStatuses.join(','));
            }
            if (includeArchived) {
                params.append('archived', '1');
            }

            const finalUrl = params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
            window.location.href = finalUrl;
            if (devisReportModal) devisReportModal.classList.add('hidden');
        });
    }

    // --- 3. SEND DEVIS MODAL LOGIC ---
    const sendDevisModalEl = document.getElementById('sendDevisModal');
    const closeSendDevisModalBtn = document.getElementById('closeSendDevisModal');
    const cancelSendDevisModalBtn = document.getElementById('cancelSendDevisModal');

    if (closeSendDevisModalBtn) {
        closeSendDevisModalBtn.addEventListener('click', () => {
            if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
        });
    }
    if (cancelSendDevisModalBtn) {
        cancelSendDevisModalBtn.addEventListener('click', () => {
            if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
        });
    }

    function handleSendClick(event) {
        event.preventDefault();
        const form = event.target.closest('form');
        const devisId = form.querySelector('input[name="devis_id"]').value;
        const formAction = form.getAttribute('action');
        
        document.getElementById('devis_id').value = devisId;
        const recipientsList = document.getElementById('recipientsList');
                recipientsList.innerHTML = '<div class="text-center">' + @json(__('messages.Loading...')) + '</div>';
        
        // This URL should be dynamic based on user role
        let contactsUrl = '';
        @if(Auth::user()->hasRole('Admin')) contactsUrl = `/admin/devis/get-contacts/${devisId}`;
        @elseif(Auth::user()->hasRole('Responsable de projet')) contactsUrl = `/Responsable_de_projet/devis/get-contacts/${devisId}`;
        @elseif(Auth::user()->hasRole('Employee')) contactsUrl = `/employee/devis/get-contacts/${devisId}`;
        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) contactsUrl = `/Responsable_gestionnaire/devis/get-contacts/${devisId}`;
        @endif

        fetch(contactsUrl)
            .then(response => response.json())
            .then(data => {
                recipientsList.innerHTML = '';
                if (data.contacts && data.contacts.length > 0) {
                    data.contacts.forEach(contact => {
                        const div = document.createElement('div');
                        div.className = 'flex items-center';
                        div.innerHTML = `
                            <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" name="recipients[]" value="${contact.email}" id="recipient_${contact.id}" checked>
                            <label class="ml-2 block text-sm text-gray-900" for="recipient_${contact.id}">
                                ${contact.name} (${contact.email})
                            </label>`;
                        recipientsList.appendChild(div);
                    });
                } else {
                    recipientsList.innerHTML = '<p class="text-gray-500">' + @json(__('messages.No contacts found for this client')) + '</p>';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                recipientsList.innerHTML = '<p class="text-red-600">' + @json(__('messages.Error loading contacts devis history')) + '</p>';
            });
        
        document.getElementById('sendDevisForm').action = formAction;
        if (sendDevisModalEl) sendDevisModalEl.classList.remove('hidden');
    }

    document.querySelectorAll('form[action*="sendDevis"]').forEach(form => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.addEventListener('click', handleSendClick);
        }
    });

    document.getElementById('confirmSend').addEventListener('click', function() {
        const form = document.getElementById('sendDevisForm');
        const devisId = document.getElementById('devis_id').value;
        const selectedRecipients = Array.from(document.querySelectorAll('input[name="recipients[]"]:checked'))
            .map(checkbox => checkbox.value);
        
        if (selectedRecipients.length === 0) {
            alert(@json(__('messages.Please select at least one recipient')));
            return;
        }

        const formData = new FormData();
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        formData.append('_token', csrfToken);
        formData.append('devis_id', devisId);
        formData.append('recipients', JSON.stringify(selectedRecipients));
        
        const attachments = document.getElementById('attachments').files;
        for (let i = 0; i < attachments.length; i++) {
            formData.append('attachments[]', attachments[i]);
        }

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                alert(@json(__('messages.Devis sent successfully!')));
                if (sendDevisModalEl) sendDevisModalEl.classList.add('hidden');
                location.reload();
            } else {
                alert(@json(__('messages.Error sending devis: ')) + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert(@json(__('messages.An error occurred while sending the devis')));
        });
    });
});

// Mobile accordion per devis row (show only number when collapsed)
document.addEventListener('DOMContentLoaded', function () {
    const mql = window.matchMedia('(max-width: 1024px)');

    function setRowCollapsed(row, collapsed) {
        if (!row) return;
        row.classList.toggle('is-collapsed', collapsed);
        row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }

    function initAccordions() {
        const rows = document.querySelectorAll('#devisTable tbody tr.devis-row');
        const isMobile = mql.matches;
        rows.forEach(row => setRowCollapsed(row, isMobile));
    }

    document.addEventListener('click', function (e) {
        const toggleCell = e.target.closest('[data-devis-toggle]');
        if (!toggleCell) return;

        // Let normal link clicks open the PDF without toggling.
        if (e.target.closest('a')) return;

        const row = toggleCell.closest('tr.devis-row');
        if (!row) return;

        const collapsed = row.classList.contains('is-collapsed');
        setRowCollapsed(row, !collapsed);
    });

    if (mql.addEventListener) mql.addEventListener('change', initAccordions);
    else if (mql.addListener) mql.addListener(initAccordions);

    initAccordions();
});
</script>
    {{-- Searchable filter selects (Tom Select). Syncs the original selects and
         fires their change events, so existing filter logic keeps working. --}}
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            ['clientFilter', 'acceptanceFilter'].forEach(function (id) {
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
