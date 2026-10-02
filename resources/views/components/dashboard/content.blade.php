@php
    $user = Auth::user();
    $isSoloPlan = optional(optional($user)->entreprise)->type_compt === 'solo';
@endphp

<style>
    @keyframes fadeInSlideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeInSlideUp {
        opacity: 0;
        animation: fadeInSlideUp 0.5s ease-out forwards;
    }

    /* Override default DataTables styles to match Tailwind */
    #employeesTable_wrapper .dt-search input {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        margin-left: 0.5rem;
    }

    #employeesTable_wrapper .dt-search input:focus {
        border-color: #2D7080;
        box-shadow: 0 0 0 2px #c3d9dd;
        outline: none;
    }

    /* FIXED: Better DataTables pagination styling */
    #employeesTable_wrapper .dt-paging {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* DataTables pagination – light theme to match dashboard */
    #employeesTable_wrapper .dataTables_paginate {
        display: flex;
        justify-content: center;
        margin-top: 1rem;
        gap: 0.25rem;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.25rem;
        height: 2.25rem;
        padding: 0 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 500;
        color: #4b5563;
        /* gray-600 */
        background-color: #ffffff;
        /* white */
        border: 1px solid #e5e7eb;
        /* gray-200 */
        cursor: pointer;
        transition: all 0.15s ease-in-out;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: #eef4f5;
        /* teal-50 */
        border-color: #c3d9dd;
        /* teal-200 */
        color: #0B2638;
        /* brand-primary */
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.current {
        background-color: #2D7080;
        /* brand-secondary */
        border-color: #2D7080;
        color: #ffffff;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        background-color: #f9fafb;
        /* gray-50 */
    }

    /* Keep prev/next consistent with light theme */
    #employeesTable_wrapper .dataTables_paginate .paginate_button.previous,
    #employeesTable_wrapper .dataTables_paginate .paginate_button.next {
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        color: #4b5563;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.previous:not(.disabled):hover,
    #employeesTable_wrapper .dataTables_paginate .paginate_button.next:not(.disabled):hover {
        background-color: #eef4f5;
        border-color: #c3d9dd;
        color: #0B2638;
    }

    #employeesTable_wrapper .dataTables_paginate .paginate_button.previous.disabled,
    #employeesTable_wrapper .dataTables_paginate .paginate_button.next.disabled {
        background-color: #f9fafb;
        color: #9ca3af;
        border-color: #e5e7eb;
    }

    #employeesTable_wrapper .dt-paging a,
    #employeesTable_wrapper .dt-paging button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }

    #employeesTable_wrapper .dt-paging a:hover,
    #employeesTable_wrapper .dt-paging button:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
    }

    #employeesTable_wrapper .dt-paging a.current,
    #employeesTable_wrapper .dt-paging button.current {
        background-color: #2D7080;
        color: #ffffff;
        border-color: #2D7080;
        font-weight: 600;
    }

    #employeesTable_wrapper .dt-paging a.disabled,
    #employeesTable_wrapper .dt-paging button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }

    #employeesTable_wrapper .dt-info {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* Laravel paginator (Jetstream/Tailwind) override */
    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md {
        box-shadow: none !important;
        gap: 0.35rem;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md>* {
        margin-left: 0 !important;
        border-radius: 0.5rem !important;
        border: 1px solid #e5e7eb !important;
        background-color: #ffffff !important;
        color: #374151 !important;
        transition: all 0.15s ease;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md>*:hover {
        background-color: #eef4f5 !important;
        border-color: #c3d9dd !important;
        color: #0B2638 !important;
    }

    .relative.z-0.inline-flex.rtl\:flex-row-reverse.shadow-sm.rounded-md>span[aria-current="page"]>span {
        background-color: #2D7080 !important;
        border-color: #2D7080 !important;
        color: #ffffff !important;
    }

    /* Dashboard: Team employees Overview table -> mobile cards + accordion */
    @media (max-width: 1024px) {
        .team-employees-overview-table-wrap {
            overflow-x: visible !important;
        }

        #employeesTable thead {
            display: none;
        }

        #employeesTable tbody {
            display: block;
            width: 100%;
        }

        #employeesTable tbody tr.employee-overview-row {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 16px;
            padding: 12px;
            border: 1px solid rgba(148, 163, 184, 0.55);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
            box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
        }

        #employeesTable tbody td {
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

        #employeesTable tbody td[data-span="2"] {
            grid-column: span 2 / span 2;
        }

        #employeesTable tbody td[data-label]::before {
            content: attr(data-label);
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }

        /* Accordion: collapsed shows only employee cell */
        #employeesTable tbody tr.employee-overview-row.is-collapsed td:not(.employee-overview-toggle-cell) {
            display: none !important;
        }
        #employeesTable tbody tr.employee-overview-row td.employee-overview-toggle-cell {
            cursor: pointer;
            user-select: none;
        }
        /* No pseudo label for the toggle cell */
        #employeesTable tbody tr.employee-overview-row td.employee-overview-toggle-cell[data-label]::before {
            content: none !important;
        }
        #employeesTable tbody tr.employee-overview-row td.employee-overview-toggle-cell .employee-overview-chevron {
            transition: transform 180ms ease;
        }
        #employeesTable tbody tr.employee-overview-row[aria-expanded="true"] td.employee-overview-toggle-cell .employee-overview-chevron {
            transform: rotate(180deg);
        }
    }

    /* Dashboard: modern cards + mobile overview */
    @media (max-width: 1024px) {
        /* Employee Overview: remove forced horizontal scrolling on mobile */
        .dashboard-employee-overview .overview-scroll {
            overflow-x: visible !important;
        }
        .dashboard-employee-overview .overview-minw {
            min-width: 0 !important;
        }
        .dashboard-employee-overview .overview-headers {
            display: none !important;
        }

        /* Employee Overview: convert rows into 2-col card blocks */
        .dashboard-employee-overview-list .dashboard-emp-row {
            padding: 0 !important;
            margin: 0 0 16px 0;
            border: 1px solid rgba(148, 163, 184, 0.55);
            border-radius: 16px;
            background: linear-gradient(180deg, rgba(255,255,255,0.95), rgba(248,250,252,0.92));
            box-shadow: 0 18px 45px rgba(15,23,42,0.10), 0 3px 10px rgba(15,23,42,0.08);
        }
        .dashboard-employee-overview-list .dashboard-emp-project {
            display: block !important;
            padding: 12px;
        }

        /* Keep EMPLOYEES block as flex (avatar left) */
        .dashboard-employee-overview-list .dashboard-emp-employee {
            display: block !important;
            width: 100% !important;
            margin-bottom: 10px;
            padding-right: 0 !important;
        }

        /* Convert detail columns into a 2-col grid */
        .dashboard-employee-overview-list .dashboard-emp-details {
            display: grid !important;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 10px !important;
            width: 100% !important;
        }

        /* Reset Tailwind col-span-* on mobile so grid can flow */
        .dashboard-employee-overview-list .dashboard-emp-details > div {
            grid-column: span 1 / span 1 !important;
        }

        .dashboard-employee-overview-list .dashboard-emp-employee::before,
        .dashboard-employee-overview-list .dashboard-emp-details > div[data-label]::before {
            content: attr(data-label);
            display: block;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }

        .dashboard-employee-overview-list .dashboard-emp-employee,
        .dashboard-employee-overview-list .dashboard-emp-details > div {
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 12px;
            background: rgba(255,255,255,0.78);
            padding: 10px 12px;
        }

        /* Ensure avatar + name stay aligned inside EMPLOYEES block */
        .dashboard-employee-overview-list .dashboard-emp-employee .dashboard-emp-employee-inner {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Mobile accordion for each employee card */
        .dashboard-employee-overview-list .dashboard-emp-row.is-collapsed .dashboard-emp-details {
            display: none !important;
        }
        .dashboard-employee-overview-list .dashboard-emp-row .emp-overview-chevron {
            transition: transform 180ms ease;
        }
        .dashboard-employee-overview-list .dashboard-emp-row[aria-expanded="true"] .emp-overview-chevron {
            transform: rotate(180deg);
        }

        /* Actions: center the eye button nicely */
        .dashboard-employee-overview-list .dashboard-emp-actions {
            display: flex !important;
            flex-direction: column !important;
            justify-content: flex-start !important;
            align-items: flex-start !important;
            padding-top: 0 !important;
            gap: 8px;
        }

        /* Full-width blocks in the 2-col grid */
        .dashboard-employee-overview-list .dashboard-emp-details > div[data-span="2"] {
            grid-column: span 2 / span 2;
        }
    }

    .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
    .modal-content { animation: slideDown 0.3s ease-out; }
    @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    .modal-content-custom {
        background-color: #fefefe;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 600px;
        text-align: center;
        position: relative;
        border-radius: 0.75rem;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    .close-button {
        color: #94a3b8;
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close-button:hover { color: #475569; }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
@livewireStyles


<div class="flex-1 overflow-auto bg-[#f8fafc]">
    {{-- Main content --}}
    <div class="p-4 sm:p-6 lg:px-20 lg:py-8 mx-auto w-full">

        {{-- Page heading + Quick actions --}}
        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
            <div class="min-w-0">
                <!-- <p class="dt-overline mb-1">{{ now()->format('l, d F Y') }}</p> -->
                <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
                    {{ __('messages.Dashboard') }}
                </h1>
                <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Track time, projects and revenue at a glance.') }}</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.invoices.fullinvoice') }}" class="dt-btn dt-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('messages.Generate invoice') }}
                </a>
                <a href="{{ route('admin.calendar.index') }}" class="dt-btn dt-btn-ghost">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('messages.Planning Menu') }}
                </a>
            </div>
        </div>

        {{-- Stats Cards — consistent DalyTrac design --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Clients --}}
            <a href="{{ route('admin.clients.index') }}"
                class="dt-card dt-card-hover p-4 flex items-center gap-3.5 group no-underline">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="4" y="3" width="16" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M9 21v-4h6v4M8 7h2M8 11h2M14 7h2M14 11h2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Clients') }}</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight">{{ $data['clients']->count() }}</span>
                        <span class="text-[12px] text-slate-400 font-medium">{{ __('messages.Total') }}</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-[#2D7080] group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>

            {{-- Employees --}}
            <a href="{{ route('admin.employees.index') }}"
                class="dt-card dt-card-hover p-4 flex items-center gap-3.5 group no-underline">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <circle cx="9" cy="8" r="3.5" />
                        <path d="M3.5 20c0-3.3 2.5-5 5.5-5s5.5 1.7 5.5 5" stroke-linecap="round" />
                        <path d="M16 5.6a3.5 3.5 0 010 6.5M17.7 15.4c1.9.7 2.8 2 2.8 4.1" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Employees') }}</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight">{{ $data['employees']->count() }}</span>
                        <span class="text-[12px] text-slate-400 font-medium">{{ __('messages.Total') }}</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-[#2D7080] group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>

            {{-- Projects --}}
            <a href="{{ route('admin.projects.index') }}"
                class="dt-card dt-card-hover p-4 flex items-center gap-3.5 group no-underline">
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Projects') }}</p>
                    <div class="flex items-baseline gap-1.5 mt-0.5">
                        <span class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight">{{ $data['totalProjectsCount'] ?? ($data['projects']->total() ?? $data['projects']->count()) }}</span>
                        <span class="text-[12px] text-slate-400 font-medium">{{ __('messages.Total') }}</span>
                    </div>
                </div>
                <svg class="w-4 h-4 text-slate-300 group-hover:text-[#2D7080] group-hover:translate-x-0.5 transition-all flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
            </a>

            {{-- Revenue + paid invoices trend --}}
            <a href="{{ route('admin.invoices.invoicehistory') }}"
                class="dt-card dt-card-hover p-4 flex items-center gap-3 group no-underline relative overflow-hidden">
                <span class="absolute left-0 top-0 h-full w-1 bg-[#E7B84B]"></span>
                <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <rect x="3" y="6" width="18" height="12" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                        <circle cx="12" cy="12" r="2.5" />
                        <path d="M6.5 9.5h.01M17.5 14.5h.01" stroke-linecap="round" />
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Monthly Revenue') }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight whitespace-nowrap">€{{ number_format($data['monthlyRevenue'], 0, ',', ' ') }}</span>
                        @php
                            $revenueGrowth = $data['monthlyRevenueGrowth'] ?? 0;
                            $growthIsPositive = $revenueGrowth >= 0;
                        @endphp
                        <span class="dt-badge {{ $growthIsPositive ? 'dt-badge-success' : 'dt-badge-danger' }} flex-shrink-0">
                            {{ $growthIsPositive ? '+' : '' }}{{ number_format($revenueGrowth, 0) }}%
                        </span>
                    </div>
                </div>
                @php
                    $collectionRate = min(100, max(0, (float) ($data['collectionRate'] ?? 0)));
                    $collectionDisplay = (int) round($collectionRate);
                    $donutRadius = 26;
                    $donutCircumference = 2 * M_PI * $donutRadius;
                    $donutOffset = $donutCircumference * (1 - $collectionRate / 100);
                    $monthlyPaid = (float) ($data['monthlyPaidRevenue'] ?? ($data['monthlyRevenue'] ?? 0) * $collectionRate / 100);
                @endphp
                <div class="flex-shrink-0"
                     title="{{ __('messages.Paid invoices') }}: €{{ number_format($monthlyPaid, 0, ',', ' ') }} / €{{ number_format($data['monthlyRevenue'] ?? 0, 0, ',', ' ') }} ({{ $collectionDisplay }}%)">
                    <div class="relative w-16 h-16" role="img" aria-label="{{ __('messages.Paid invoices') }}: {{ $collectionDisplay }}%">
                        <svg viewBox="0 0 64 64" class="w-16 h-16">
                            <circle cx="32" cy="32" r="26" fill="none" stroke="#e2e8f0" stroke-width="8" />
                            <circle cx="32" cy="32" r="26" fill="none" stroke="#2D7080" stroke-width="8"
                                    stroke-linecap="round" transform="rotate(-90 32 32)"
                                    stroke-dasharray="{{ number_format($donutCircumference, 2, '.', '') }}"
                                    stroke-dashoffset="{{ number_format($donutOffset, 2, '.', '') }}" />
                        </svg>
                        <span class="absolute inset-0 flex items-center justify-center text-[13px] font-bold text-[#0B2638]">{{ $collectionDisplay }}%</span>
                    </div>
                </div>
            </a>
        </div>


        {{-- Team employees Overview --}}
        <div class="dt-card mt-6 overflow-hidden animate-fadeInSlideUp"
            style="animation-delay: 500ms;">
            {{-- Header and action button --}}
            <div class="px-5 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between border-b border-slate-100 bg-white">
                <div class="min-w-0">
                    <h2 class="dt-section-title">{{ __('messages.Team employees Overview') }}</h2>
                    <p class="dt-section-subtitle mt-0.5">{{ __('messages.Live status, timers and actions for today.') }}</p>
                </div>
                @unless($isSoloPlan)
                    <a href="{{ route('admin.employees.create') }}"
                        class="dt-btn dt-btn-teal w-full sm:w-auto">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        {{__('messages.Add employee')}}
                    </a>
                @endunless
            </div>

            {{-- Table --}}
            <div class="team-employees-overview-table-wrap px-3 sm:px-4 py-3 overflow-x-auto">
                <table id="employeesTable" class="w-full text-left" style="width:100%">
                    <thead>
                        <tr class="border-b border-slate-100 dt-table-head">
                            <th class="px-4 py-3 text-left">
                                {{ __('messages.Employee') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center w-[8.5rem] ">
                                {{ __('messages.Current Status') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center">
                                {{ __('messages.Current Project') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center">
                                {{ __('messages.Current Task') }}
                            </th>
                                
                            {{--<th
                                class="px-6 py-3 text-xs font-semibold text-[#0B2638] uppercase tracking-wider text-center">
                                Task Progress</th> --}}
                            <th
                                class="px-4 py-3 text-center">
                                {{ __('messages.Duration') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center">
                                {{ __('messages.Chrono') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center">
                                {{ __('messages.Account Status') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center">
                                {{ __('messages.Archive') }}
                            </th>
                            <th
                                class="px-4 py-3 text-center">
                                {{ __('messages.Actions') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $avatarColors = ['bg-emerald-500', 'bg-gray-400', 'bg-amber-500', 'bg-red-500', 'bg-purple-500', 'bg-sky-500', 'bg-rose-800', 'bg-slate-700', 'bg-yellow-400', 'bg-indigo-500'];
                        @endphp
                        @foreach($data['employees'] as $employee)
                            <tr class="employee-overview-row hover:bg-[#f4f7f6]" data-employee-id="{{ $employee->id }}" aria-expanded="true">
                                {{-- Employee --}}
                                <td data-label="{{ __('messages.Employee') }}" data-span="2" class="px-6 py-4 employee-overview-toggle-cell" data-employee-overview-toggle>
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-3 min-w-0">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $avatarColors[$loop->index % count($avatarColors)] }}">
                                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                                        </div>
                                        <div class="flex flex-col">
                                            <a href="{{ route('admin.employees.show', $employee->id) }}"
                                                class="text-sm font-medium text-gray-900 hover:text-brand-secondary">
                                                {{ $employee->name }}
                                            </a>
                                            <p class="text-xs text-gray-500">{{ $employee->email }}</p>
                                            {{-- Roles under email to minimize width --}}
                                            @php
                                                $employeeRoles = is_array($employee->roles ?? null) ? $employee->roles : (array) ($employee->roles ?? []);
                                            @endphp
                                            <div class="flex flex-wrap gap-1 pt-1">
                                                @forelse($employeeRoles as $role)
                                                    <span
                                                        class="inline-block px-2 py-0.5 text-[10.5px] font-medium rounded {{ $role == 'SuperAdmin' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700'}}">
                                                        {{ $role }}
                                                    </span>
                                                @empty
                                                    <span class="text-[10px] text-gray-400">{{ __('messages.No Roles') }}</span>
                                                @endforelse
                                            </div>
                                        </div>
                                        </div>
                                        <span class="shrink-0 text-slate-400 lg:hidden">
                                            <svg class="employee-overview-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>

                                {{-- Current Status --}}
                                <td data-label="{{ __('messages.Current Status') }}" class="px-6 py-4 text-center align-middle text-sm cell-current-status">
                                    @php
                                        $employeeUser = \App\Models\User::find($employee->user_id);
                                    @endphp
                                    @if($employeeUser && $employeeUser->is_loggedIn == 0)
                                        <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ __('messages.Off Work') }}</span>
                                    @elseif($employeeUser && $employeeUser->is_loggedIn == 1)
                                        @php
                                            // Align status with other columns: any still-open timer counts, regardless of start day
                                            $task_timer = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereNull('end_date')
                                                ->first();
                                        @endphp
                                        @if($task_timer)
                                            @if(($task_timer->end_date === null && $task_timer->start_date !== null) && $task_timer->paused == 1)
                                                <span class="dt-badge dt-badge-warning"><span class="dt-dot"></span>{{ __('messages.On Pause') }}</span>
                                            @else
                                                <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.On Work') }}</span>
                                            @endif
                                        @else
                                            <span class="dt-badge dt-badge-neutral"><span class="dt-dot"></span>{{ __('messages.Off Work') }}</span>
                                        @endif
                                    @endif
                                </td>

                                {{-- Current Project --}}
                                <td data-label="{{ __('messages.Current Project') }}" data-span="2" class="px-6 py-4 text-center align-middle text-sm cell-current-project">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            // For superadmin, get all active tasks
                                            // Active = timer still open, regardless of which day it started
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            // For others, get only the latest active task
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }

                                        // Group active tasks by project so each task line has a matching project line
                                        $groupedActiveTasksProjects = $activeTasks
                                            ->map(function ($t) {
                                                $projectId = \App\Models\Tasks::where('id', $t->task_id)->value('project_id');
                                                $t->project_for_grouping = $projectId ?? 0;
                                                return $t;
                                            })
                                            ->sortBy('project_for_grouping')
                                            ->groupBy('project_for_grouping');
                                    @endphp
                                    @if($activeTasks->isNotEmpty())
                                        <div class="space-y-3">
                                            @foreach($groupedActiveTasksProjects as $projectId => $projectTasks)
                                                @php
                                                    $project = $projectId
                                                        ? \App\Models\Project::find($projectId)
                                                        : null;
                                                    $linesCount = $projectTasks->count();
                                                    // Determine if this project has any active (running) timer
                                                    $hasActiveTimer = $projectTasks->contains(function ($t) {
                                                        return $t->end_date === null && (int) ($t->paused ?? 0) === 0;
                                                    });
                                                    $projectTextClasses = 'text-xs font-semibold ' . ($hasActiveTimer ? 'text-emerald-600' : 'text-gray-400');
                                                    $linkClasses = $hasActiveTimer ? 'hover:underline block' : 'block';
                                                @endphp
                                                <div class="space-y-1 w-full {{ !$loop->last ? 'pb-2 border-b border-gray-200' : '' }}">
                                                    {{-- First line: project name aligned with first task of this project --}}
                                                    <div class="{{ $projectTextClasses }}">
                                                        @if($project)
                                                            <a href="{{ route('admin.projects.show', $project->id) }}"
                                                                class="{{ $linkClasses }}">
                                                                {{ $project->name }}
                                                            </a>
                                                        @else
                                                            <span class="text-gray-400 block">{{ __('messages.No Project') }}</span>
                                                        @endif
                                                    </div>

                                                    {{-- Extra spacer lines so tasks/durations/chrono stay aligned --}}
                                                    @for($i = 1; $i < $linesCount; $i++)
                                                        <div class="text-xs">&nbsp;</div>
                                                    @endfor
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">{{ __('messages.No Project') }}</span>
                                    @endif
                                </td>

                                {{-- Current Task --}}
                                <td data-label="{{ __('messages.Current Task') }}" data-span="2" class="px-6 py-4 text-center align-middle text-sm cell-current-task">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            // For superadmin, get all active tasks
                                            // Active = timer still open, regardless of which day it started
                                            $currentTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            // For others, get only the latest active task
                                            $currentTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                    @endphp
                                    @if($currentTasks->isNotEmpty())
                                        @php
                                            $taskListClasses = 'space-y-1';
                                            if ($isSuperAdmin && $currentTasks->count() > 1) {
                                                $taskListClasses .= ' divide-y divide-gray-500';
                                            }
                                            // Ensure each task name appears only once, even if there are multiple timers
                                            $tasksById = $currentTasks->groupBy('task_id');
                                        @endphp
                                        <div class="{{ $taskListClasses }}">
                                            @foreach($tasksById as $taskId => $taskGroup)
                                                @php $currentTask = $taskGroup->first(); @endphp
                                                @if(auth()->user()->hasRole('Admin') || auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin'))
                                                    <a href="{{ route('admin.tasks.show', $currentTask->task_id) }}"
                                                        class="text-xs font-semibold text-brand-secondary hover:underline block">
                                                @elseif(auth()->user()->hasRole('Responsable de projet'))
                                                        <a href="{{ route('Responsable_de_projet.tasks.show', $currentTask->task_id) }}"
                                                            class="text-xs font-semibold text-brand-secondary hover:underline block">
                                                    @else
                                                            <a href="#" class="text-xs font-semibold text-brand-secondary block">
                                                        @endif
                                                            {{ $currentTask->task_name }}
                                                        </a>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">{{ __('messages.No Task') }}</span>
                                    @endif
                                </td>

                                {{-- Task Progress 
                                <td class="px-6 py-4 text-center align-middle text-sm cell-task-progress">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                        $firstActiveTask = $activeTasks->first();
                                    @endphp
                                    @if($firstActiveTask)
                                        @php
                                            $task = \App\Models\Tasks::where('id', $firstActiveTask->task_id)->first();
                                        @endphp
                                        @if($task && $task->estimated_hours !== null)
                                            @php
                                                $tasktotalduration = \App\Models\tasks_timer::where('task_id', $firstActiveTask->task_id)
                                                    ->where('employee_id', $employee->id)
                                                    ->sum('duration');
                                                $taskestimatedMinutes = max(0, (($task->estimated_hours ?? 0) - ($task->consumed_hours ?? 0)) * 60);
                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                            @endphp
                                            <div class="w-full bg-gray-200 rounded-full h-2 overflow-hidden">
                                                <div class="h-2 rounded-full text-[10px] text-white flex items-center justify-center
                                                                                                                                                                                                                                @if($taskprogressPercentage < 50) bg-emerald-500
                                                                                                                                                                                                                                @elseif($taskprogressPercentage < 75) bg-amber-500
                                                                                                                                                                                                                                @else bg-red-500 @endif"
                                                    style="width: {{ $taskformattedProgressPercentage }}%;">
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs font-medium text-gray-700">
                                                {{ $taskformattedProgressPercentage }}%
                                            </p>
                                        @else
                                            <p class="text-xs font-medium text-gray-600">No Estimated Hours Inserted</p>
                                        @endif
                                    @else
                                        <p class="text-xs font-medium text-gray-600">No Task</p>
                                    @endif
                                </td> --}}

                                {{-- Duration --}}
                                <td data-label="{{ __('messages.Duration') }}" class="px-6 py-4 text-center align-middle text-sm cell-duration">
                                    @php
                                        // Show TOTAL worked time today per task, grouped by project (initial render)
                                        $today = \Carbon\Carbon::today();
                                        $timersToday = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                            ->whereDate('start_date', $today)
                                            ->orderBy('start_date', 'desc')
                                            ->get();
                                    @endphp
                                    @if($timersToday->isNotEmpty())
                                        @php
                                            $now = \Carbon\Carbon::now();
                                            $groupedActiveTasks = $timersToday
                                                ->map(function ($t) {
                                                    $projectId = \App\Models\Tasks::where('id', $t->task_id)->value('project_id');
                                                    $t->project_for_grouping = $projectId ?? 0;
                                                    return $t;
                                                })
                                                ->sortBy('project_for_grouping')
                                                ->groupBy('project_for_grouping');
                                        @endphp
                                        <div class="space-y-3">
                                            @foreach($groupedActiveTasks as $projectId => $projectTasks)
                                                <div class="space-y-1 w-full {{ !$loop->last ? 'pb-2 border-b border-gray-200' : '' }}">
                                                    @php
                                                        $tasksById = $projectTasks->groupBy('task_id');
                                                        $project = $projectId ? \App\Models\Project::find($projectId) : null;
                                                    @endphp
                                                    @foreach($tasksById as $taskId => $taskTimers)
                                                        @php
                                                            $totalMinutes = 0;
                                                            foreach ($taskTimers as $timer) {
                                                                $baseMinutes = (int) ($timer->duration ?? 0);
                                                                if ($timer->end_date === null) {
                                                                    $startTime = \Carbon\Carbon::parse($timer->start_date);
                                                                    if ($timer->paused == 1) {
                                                                        $totalMinutes += $baseMinutes;
                                                                    } else {
                                                                        $elapsedSeconds = $startTime->diffInSeconds($now);
                                                                        $totalMinutes += $baseMinutes + (int) floor($elapsedSeconds / 60);
                                                                    }
                                                                } else {
                                                                    $totalMinutes += $baseMinutes;
                                                                }
                                                            }
                                                            $hours = floor($totalMinutes / 60);
                                                            $minutes = $totalMinutes % 60;
                                                        @endphp
                                                        <div class="text-xs">
                                                            <div class="font-semibold text-gray-800">
                                                                {{ $hours }}h:{{ sprintf('%02d', $minutes) }}m
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                    
                                                    {{-- Project total (all time) --}}
                                                    @if($project)
                                                        @php
                                                            $projecttasksId = \App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                            $projectTotalMinutes = \App\Models\tasks_timer::where(function ($q) use ($projecttasksId, $project) {
                                                                $q->whereIn('task_id', $projecttasksId)
                                                                    ->orWhere('project_id', $project->id);
                                                            })->where('employee_id', $employee->id)->sum('duration');
                                                            $projectTotalHours = floor($projectTotalMinutes / 60);
                                                            $projectTotalMins = $projectTotalMinutes % 60;
                                                        @endphp
                                                        <div class="text-xs text-gray-500 mt-1 pt-1 border-t border-gray-200">
                                                            {{ __('messages.Total') }}: {{ $projectTotalHours }}h:{{ sprintf('%02d', $projectTotalMins) }}m
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs font-medium text-gray-700">{{ __('messages.No work today') }}</span>
                                    @endif
                                </td>

                                {{-- Chrono (Stop button only, matching legacy behaviour) --}}
                                <td data-label="{{ __('messages.Chrono') }}" class="px-6 py-4 text-center align-middle text-sm cell-chrono">
                                    @php
                                        $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                        if ($isSuperAdmin) {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->orderBy('start_date', 'desc')
                                                ->get();
                                        } else {
                                            $activeTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', today())
                                                ->whereNull('end_date')
                                                ->latest()
                                                ->take(1)
                                                ->get();
                                        }
                                        $firstActiveTask = $activeTasks->first();
                                    @endphp
                                    @if($activeTasks->isNotEmpty())
                                        @if($isSuperAdmin && $activeTasks->count() > 1)
                                            <button type="button"
                                                class="inline-flex items-center px-3 py-1 mb-2 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200"
                                                onclick="stopAllTimersForEmployee({{ $employee->id }})">
                                                {{ __('messages.Stop all') }}
                                            </button>
                                        @endif
                                        @php
                                            // Group active tasks by project for visual separation in Chrono
                                            $groupedActiveTasksChrono = $activeTasks
                                                ->map(function ($t) {
                                                    $projectId = \App\Models\Tasks::where('id', $t->task_id)->value('project_id');
                                                    $t->project_for_grouping = $projectId ?? 0;
                                                    return $t;
                                                })
                                                ->sortBy('project_for_grouping')
                                                ->groupBy('project_for_grouping');
                                        @endphp
                                        <div class="space-y-3 flex flex-col items-center">
                                            @foreach($groupedActiveTasksChrono as $projectId => $projectTasks)
                                                <div class="space-y-1 w-full flex flex-col items-center {{ !$loop->last ? 'pb-2 border-b border-gray-200' : '' }}">
                                                    @foreach($projectTasks as $activeTask)
                                                        <form
                                                            class="flex items-center justify-center gap-2 stop-form-{{ $employee->id }}"
                                                            action="{{ route('admin.tasks.stop', ['tasks' => $activeTask->task_employee_id, 'employee_id' => $employee->id]) }}"
                                                            method="POST" data-confirm="{{ __('messages.Stop timer for this task?') }}">
                                                            @csrf
                                                            <button type="submit"
                                                                class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-500 text-white hover:bg-red-600">
                                                                {{ __('messages.Stop') }}
                                                            </button>
                                                        </form>
                                                    @endforeach
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-gray-400">{{ __('messages.No Active Timer') }}</span>
                                    @endif
                                </td>

                                {{-- Account Status --}}
                                <td data-label="{{ __('messages.Account Status') }}" class="px-6 py-4 text-center align-middle text-sm cell-account-status">
                                    @if($employeeUser && $employeeUser->is_activate == 1)
                                        <form action="{{ route('admin.employee.deactivate', $employee->id) }}" method="post"
                                            onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 hover:bg-emerald-200">
                                                {{ __('messages.Active') }}
                                            </button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.employee.activate', $employee->id) }}" method="post"
                                            onsubmit="return confirm('{{ __('messages.Are you sure?') }}');">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex px-3 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-700 hover:bg-red-200">
                                                {{ __('messages.Inactive') }}
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                {{-- Archive --}}
                                <td data-label="{{ __('messages.Archive') }}" class="px-6 py-4 text-center align-middle text-sm">
                                    <form action="{{ route('admin.employees.archive', $employee->id) }}" method="post"
                                        onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-700 hover:bg-slate-300">
                                            {{ __('messages.Archive') }}
                                        </button>
                                    </form>
                                </td>

                                {{-- Actions (view / edit / delete / tasks done today) --}}
                                <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-6 py-4 text-center align-middle">
                                    <div class="relative flex items-center justify-start gap-2">
                                        @php
                                            $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                                            // Active timers for the employee today
                                            if ($isSuperAdmin) {
                                                $activeTimers = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                    ->whereDate('start_date', \Carbon\Carbon::today())
                                                    ->whereNull('end_date')
                                                    ->get();
                                            } else {
                                                $activeTimers = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                    ->whereDate('start_date', \Carbon\Carbon::today())
                                                    ->whereNull('end_date')
                                                    ->latest()
                                                    ->take(1)
                                                    ->get();
                                            }
                                            $activeTaskIds = $activeTimers->pluck('task_id')->filter()->unique();

                                            $completedTasksToday = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                                ->whereDate('start_date', \Carbon\Carbon::today())
                                                ->whereNotNull('end_date')
                                                // exclude any task currently active (so history shows only stopped tasks)
                                                ->when($activeTaskIds->isNotEmpty(), fn($q) => $q->whereNotIn('task_id', $activeTaskIds))
                                                ->get()
                                                // Group by task (prefer task_id, otherwise fallback to task name)
                                                ->groupBy(fn($t) => $t->task_id ?? 'name:' . ($t->task_name ?? 'unknown'))
                                                ->map(function ($group) {
                                                    // keep latest session info, but sum durations across the day
                                                    $latest = $group->sortByDesc('end_date')->first();
                                                    $latest->total_duration = $group->sum('duration');
                                                    return $latest;
                                                })
                                                ->sortByDesc('end_date');
                                        @endphp

                                        {{-- Tasks history toggle (shows tasks done today, excluding current) --}}
                                        <button type="button" onclick="toggleTasksTodayRow('tasks-row-{{ $employee->id }}')"
                                            title="{{ __('messages.Task History (Today)') }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-slate-500 hover:bg-slate-600">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M4 6h16M4 12h16M4 18h16" />
                                            </svg>
                                        </button>

                                        {{-- View details --}}
                                        <a href="{{ route('admin.employees.show', $employee->id) }}"
                                            title="{{ __('messages.Details') }}"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-brand-primary hover:bg-[#16394a]">
                                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                            </svg>
                                        </a>

                                        {{-- Delete employee --}}
                                        <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST"
                                            onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this employee?') }}');"
                                            class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="{{ __('messages.Delete') }}"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            {{-- Task history row (hidden by default) --}}
                            <tr id="tasks-row-{{ $employee->id }}" class="hidden bg-gray-50">
                                <td colspan="10" class="px-6 py-4">
                                    <div class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <h4 class="text-sm font-semibold text-gray-800">{{ __('messages.Task History (Today)') }}</h4>
                                            <button type="button"
                                                onclick="toggleTasksTodayRow('tasks-row-{{ $employee->id }}')"
                                                class="text-xs text-gray-500 hover:text-gray-700">{{ __('messages.Close') }}</button>
                                        </div>
                                        @forelse($completedTasksToday as $doneTask)
                                            @php
                                                $durationMinutes = (int) ($doneTask->total_duration ?? $doneTask->duration);
                                                $h = floor($durationMinutes / 60);
                                                $m = $durationMinutes % 60;
                                                $taskModel = \App\Models\Tasks::find($doneTask->task_id);
                                                $projectName = null;
                                                // Prefer direct project_id on timer if present
                                                if (!empty($doneTask->project_id)) {
                                                    $projectName = optional(\App\Models\Project::find($doneTask->project_id))->name;
                                                }
                                                // Fallback to task's project
                                                if (!$projectName && $taskModel) {
                                                    $projectName = optional($taskModel->project)->name ?? null;
                                                }
                                            @endphp
                                            <div
                                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 rounded-lg border border-gray-200 bg-white px-3 py-2">
                                                <div class="flex flex-col">
                                                    <span
                                                        class="text-sm font-medium text-gray-900">{{ $doneTask->task_name ?? ($taskModel->title ?? __('messages.Task')) }}</span>
                                                    <span
                                                        class="text-xs text-gray-500">{{ $projectName ?? __('messages.No project linked') }}</span>
                                                </div>
                                                <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3 w-full sm:w-auto">
                                                    <span
                                                        class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700">{{ __('messages.Completed') }}</span>
                                                    <span class="text-xs text-gray-700 whitespace-nowrap">{{ $h }}h
                                                        {{ $m }}m</span>
                                                    <form
                                                        action="{{ route('admin.tasks.start', ['tasks' => $doneTask->task_employee_id ?? $doneTask->task_id, 'employee_id' => $doneTask->employee_id]) }}"
                                                        method="POST" class="history-start-form"
                                                        data-task-id="{{ $doneTask->task_employee_id ?? $doneTask->task_id }}">
                                                        @csrf
                                                        <textarea name="comment" placeholder="{{ __('messages.Enter comment') }}" rows="1"
                                                            class="border border-slate-300 rounded px-2 py-1 text-xs text-slate-700 focus:outline-none focus:ring focus:ring-emerald-200 resize-y min-h-[32px] w-full sm:w-56"></textarea>
                                                        <button type="submit"
                                                            class="inline-flex items-center justify-center px-3 py-1 rounded-full text-[11px] font-semibold bg-emerald-500 text-white hover:bg-emerald-600 w-full sm:w-auto">
                                                            {{ __('messages.Start') }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <p class="text-xs text-gray-500">{{ __('messages.No completed tasks today.') }}</p>
                                        @endforelse
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Project Overview + Events --}}
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-4 gap-4">

            {{-- Employee Overview (col-span-3) --}}
            <div class="dashboard-employee-overview lg:col-span-3 dt-card overflow-hidden animate-fadeInSlideUp"
                style="animation-delay: 600ms;">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <h2 class="dt-section-title">{{ __('messages.Employee Overview - Today\'s Work') }}</h2>
                    <p class="dt-section-subtitle mt-0.5">{{ __('messages.Who worked on what today.') }}</p>
                </div>

                <div class="overview-scroll overflow-x-auto" style="-webkit-overflow-scrolling: touch;">
                    <div class="overview-minw" style="min-width: 1100px;">
                        {{-- Column headers --}}
                        <div class="overview-headers px-4 sm:px-6 py-3 grid grid-cols-12 gap-2 sm:gap-4 border-b border-gray-200">
                            <span
                                class="col-span-2 text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap truncate overflow-hidden">{{ __('messages.EMPLOYEES') }}</span>
                            <span
                                class="col-span-3 text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap truncate overflow-hidden">{{ __('messages.PROJECTS & CLIENT') }}</span>
                            <span
                                class="col-span-2 text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap truncate overflow-hidden">{{ __('messages.TIME TRACKING') }}</span>
                            <span
                                class="col-span-3 text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap truncate overflow-hidden">{{ __('messages.TASKS') }}</span>
                            <span
                                class="col-span-1 text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap truncate overflow-hidden">{{ __('messages.TASK DURATION') }}</span>
                            <span
                                class="col-span-1 text-[11px] sm:text-xs font-semibold text-gray-500 uppercase tracking-wider whitespace-nowrap truncate overflow-hidden text-right">{{ __('messages.Employee Actions') }}</span>
                        </div>

                        {{-- Employee list container --}}
                        <div id="projects-container">
                    @php
                        $hasWorkedToday = false;
                        $todayStart = \Carbon\Carbon::today();
                        $todayEnd = \Carbon\Carbon::today()->endOfDay();
                        $avatarColors = ['bg-emerald-500', 'bg-gray-400', 'bg-amber-500', 'bg-red-500', 'bg-purple-500', 'bg-sky-500', 'bg-rose-800', 'bg-slate-700', 'bg-yellow-400', 'bg-indigo-500'];

                        // Collect timers that overlap today
                        $entrepriseId = optional(Auth::user())->entreprise_id;
                        $timersToday = \App\Models\tasks_timer::where(function ($q) use ($todayStart, $todayEnd) {
                            $q->where('start_date', '<=', $todayEnd)
                                ->where(function ($qq) use ($todayStart) {
                                    $qq->whereNull('end_date')
                                        ->orWhere('end_date', '>=', $todayStart);
                                });
                        })
                            ->when($entrepriseId, fn($q) => $q->where('entreprise_id', $entrepriseId))
                            ->get();

                        // Group timers by employee and collect their projects
                        $employeeIdsToday = $timersToday->pluck('employee_id')->filter()->unique();
                        $employeesToday = $employeeIdsToday->isNotEmpty()
                            ? \App\Models\Employees::whereIn('id', $employeeIdsToday)->get()
                            : collect();
                    @endphp

                    @include('components.dashboard.employee-overview-list', ['employeesToday' => $employeesToday, 'timersToday' => $timersToday])

                        </div>
                    </div>
                </div>
            </div>

            {{-- Today's Events --}}
            <div class="lg:col-span-1 dt-card p-5 h-fit animate-fadeInSlideUp"
                style="animation-delay: 700ms;">
                {{-- Header with "Add Event" button --}}
                <div class="flex items-start justify-between gap-3 mb-1">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-1.5 h-6 bg-[#E7B84B] rounded-full flex-shrink-0"></span>
                        <h2 class="dt-section-title truncate">{{ __('messages.Today\'s Events') }}</h2>
                    </div>
                </div>

                {{-- Date --}}
                <p class="text-[12.5px] text-slate-500 mb-4">{{ now()->format('d F Y') }}</p>

                {{-- Events list --}}
                <ul class="space-y-3">
                    @forelse($data['dayEvents'] as $event)
                        <li class="flex gap-3 rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-2.5">
                            <div class="w-1 flex-shrink-0 bg-[#2D7080] rounded-full"></div>
                            <div class="min-w-0">
                                <p class="text-[13px] font-semibold text-[#0B2638] truncate">{{ $event->title }}</p>
                                <p class="text-[12px] text-slate-500 flex items-center gap-1.5 mt-1">
                                    <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none"
                                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <span>
                                        {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} -
                                        {{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                                    </span>
                                </p>
                            </div>
                        </li>
                    @empty
                        <li class="rounded-xl border border-dashed border-slate-200 px-4 py-6 text-center">
                            <div class="dt-empty-icon mx-auto mb-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                            <p class="text-[13px] font-semibold text-[#0B2638]">{{ __('messages.No events scheduled for today.') }}</p>
                            <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Enjoy a calm day — or plan one.') }}</p>
                        </li>
                    @endforelse
                </ul>

                <a href="{{ route('admin.calendar.index') }}"
                    class="dt-btn dt-btn-ghost w-full mt-4">
                    {{ __('messages.See full calendar') }}
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </a>

            </div>
        </div>

        @livewireStyles
        @livewireScripts

        <script>
            function toggleTasksTodayRow(id) {
                const row = document.getElementById(id);
                if (!row) return;
                row.classList.toggle('hidden');
            }

            // Mobile accordion for "Employee Overview - Today's Work" cards
            (function initEmployeeOverviewCardsAccordion() {
                const mql = window.matchMedia('(max-width: 1024px)');

                function setCollapsed(row, collapsed) {
                    if (!row) return;
                    row.classList.toggle('is-collapsed', collapsed);
                    row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                }

                function initForViewport() {
                    const rows = document.querySelectorAll('.dashboard-employee-overview-list .dashboard-emp-row');
                    const isMobile = mql.matches;
                    rows.forEach(r => setCollapsed(r, isMobile));
                }

                document.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-emp-overview-toggle]');
                    if (!btn) return;

                    const row = btn.closest('.dashboard-emp-row');
                    if (!row) return;

                    const collapsed = row.classList.contains('is-collapsed');
                    setCollapsed(row, !collapsed);
                });

                if (mql.addEventListener) mql.addEventListener('change', initForViewport);
                else if (mql.addListener) mql.addListener(initForViewport);

                document.addEventListener('DOMContentLoaded', initForViewport);
            })();

            function stopAllTimersForEmployee(employeeId) {
                const forms = document.querySelectorAll('.stop-form-' + employeeId);
                if (!forms.length) return;
                if (!confirm('{{ __('messages.Stop all timers for this employee?') }}')) return;

                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

                const requests = Array.from(forms).map(form => {
                    const action = form.getAttribute('action');
                    const formData = new FormData(form);
                    return fetch(action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                        body: formData,
                    });
                });

                Promise.all(requests)
                    .then(() => window.location.reload())
                    .catch(() => window.location.reload());
            }

            // Live-updating durations
            function formatDurationSeconds(totalSeconds) {
                const hours = Math.floor(totalSeconds / 3600);
                const minutes = Math.floor((totalSeconds % 3600) / 60);
                const seconds = totalSeconds % 60;
                return `${String(hours).padStart(2, '0')}h:${String(minutes).padStart(2, '0')}m:${String(seconds).padStart(2, '0')}s`;
            }

            function refreshLiveDurations() {
                const nowMs = Date.now();
                document.querySelectorAll('.live-duration').forEach(node => {
                    const paused = node.dataset.paused === '1';
                    const startMs = node.dataset.start ? new Date(node.dataset.start).getTime() : null;
                    const extraSeconds = parseInt(node.dataset['extraSeconds'] || '0', 10);
                    if (!startMs) return;
                    const elapsedSeconds = paused ? 0 : Math.floor((nowMs - startMs) / 1000);
                    const totalSeconds = extraSeconds + elapsedSeconds;
                    node.textContent = formatDurationSeconds(totalSeconds);
                });
            }

            // ===== AUTO-REFRESH FUNCTIONALITY =====
            const AUTO_REFRESH_INTERVAL = 5000; // 5 seconds
            let refreshTimer = null;

            // Function to refresh employee data via AJAX
            async function refreshEmployeeData() {
                console.log('[AUTO-REFRESH] Starting refresh at', new Date().toLocaleTimeString());
                try {
                    const response = await fetch('{{ route("admin.refresh-employees") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch data');

                    const data = await response.json();
                    console.log('[AUTO-REFRESH] Received data for', data.employees.length, 'employees');

                    // Update each employee row with fresh data
                    data.employees.forEach(employee => {
                        updateEmployeeRow(employee);
                    });

                    // Update live durations after data refresh
                    refreshLiveDurations();

                    console.log('[AUTO-REFRESH] Refresh completed successfully');

                } catch (error) {
                    console.error('[AUTO-REFRESH] Error:', error);
                }
            }

            // Function to update a single employee row
            function updateEmployeeRow(employee) {
                const row = document.querySelector(`tr[data-employee-id="${employee.id}"]`);
                if (!row) return;

                // Update only the specific cells
                const cells = {
                    '.cell-current-status': employee.currentStatus,
                    '.cell-current-task': employee.currentTask,
                    '.cell-current-project': employee.currentProject,
                    '.cell-task-progress': employee.taskProgress,
                    '.cell-duration': employee.duration,
                    '.cell-account-status': employee.accountStatus,
                    '.cell-chrono': employee.chrono
                };

                Object.entries(cells).forEach(([selector, html]) => {
                    const cell = row.querySelector(selector);
                    if (cell) {
                        cell.innerHTML = html;
                    }
                });

                // Update task history section (if it exists)
                const historyRow = document.getElementById(`tasks-row-${employee.id}`);
                if (historyRow && employee.taskHistory) {
                    // Check if user is typing in this row (prevent overwriting input)
                    if (historyRow.contains(document.activeElement)) {
                        // console.log(`[AUTO-REFRESH] Skipping history update for employee ${employee.id} due to user focus`);
                        return;
                    }

                    const historyContent = historyRow.querySelector('.space-y-3');
                    if (historyContent) {
                        // Keep the header, update only the task list
                        // The structure is: <div><h4>Header</h4><button>Close</button></div> ... tasks ...
                        // We want to keep the first div (header) and replace the rest

                        // Simple way: Find the header div
                        const headerDiv = historyContent.querySelector('.flex.items-center.justify-between');
                        if (headerDiv) {
                            // Rebuild content: Header + New History HTML
                            historyContent.innerHTML = '';
                            historyContent.appendChild(headerDiv);

                            // Insert the new history HTML after header
                            // Since employee.taskHistory is a string of HTML, we need to parse it or insert adjacent
                            headerDiv.insertAdjacentHTML('afterend', employee.taskHistory);
                        }
                    }
                }
            }

            // Function to refresh projects data
            async function refreshProjectsData() {
                try {
                    const response = await fetch('{{ route("admin.refresh-projects") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        }
                    });

                    if (!response.ok) throw new Error('Failed to fetch project data');

                    const data = await response.json();
                    if (data.success) {
                        const container = document.getElementById('projects-container');
                        if (container) {
                            container.innerHTML = data.html;
                        }
                    }

                } catch (error) {
                    console.error('[AUTO-REFRESH] Projects Error:', error);
                }
            }

            // Start auto-refresh
            function startAutoRefresh() {
                refreshEmployeeData();
                refreshProjectsData();
                refreshTimer = setInterval(() => {
                    refreshEmployeeData();
                    refreshProjectsData();
                }, AUTO_REFRESH_INTERVAL);
            }

            // Stop auto-refresh
            function stopAutoRefresh() {
                if (refreshTimer) {
                    clearInterval(refreshTimer);
                    refreshTimer = null;
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                refreshLiveDurations();
                setInterval(refreshLiveDurations, 1000);

                // Start auto-refresh
                startAutoRefresh();

                // Universal AJAX handler for ALL dashboard forms (start, stop, activate, deactivate, archive, etc.)
                document.addEventListener('submit', async (e) => {
                    const form = e.target;

                    // Check if this form is inside the employees table or task history
                    const isEmployeeTableForm = form.closest('#employeesTable') || form.classList.contains('history-start-form');

                    if (!isEmployeeTableForm) {
                        return; // Not a dashboard form, let it submit normally
                    }

                    e.preventDefault(); // Prevent page reload
                    e.stopPropagation(); // Stop event from bubbling

                    const submitButton = form.querySelector('button[type="submit"]');
                    if (!submitButton) return;

                    const originalText = submitButton.textContent.trim();
                    const originalClasses = submitButton.className;

                    // Prevent double submission
                    if (submitButton.disabled) return;

                    // Handle confirmation dialogs
                    const confirmMessage = form.getAttribute('data-confirm');
                    if (confirmMessage) {
                        if (!confirm(confirmMessage)) {
                            return; // User cancelled
                        }
                    }
                    // Fallback for forms still using onsubmit (if any)
                    else if (form.getAttribute('onsubmit') && form.getAttribute('onsubmit').includes('confirm')) {
                        const onsubmit = form.getAttribute('onsubmit');
                        const confirmMatch = onsubmit.match(/confirm\(['"](.+?)['"]\)/);
                        if (confirmMatch) {
                            // This path shouldn't be taken for forms where we removed onsubmit
                            // But keeping it for other forms
                            if (!confirm(confirmMatch[1])) {
                                return; // User cancelled
                            }
                        }
                    }

                    try {
                        // Disable button and show loading state
                        submitButton.disabled = true;
                        const loadingText = originalText === 'Start' ? 'Starting...' :
                            originalText === 'Stop' ? 'Stopping...' :
                                originalText === 'Active' ? 'Updating...' :
                                    originalText === 'Inactive' ? 'Updating...' :
                                        originalText === 'Archive' ? 'Archiving...' : 'Processing...';
                        submitButton.textContent = loadingText;

                        const formData = new FormData(form);
                        const response = await fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                            }
                        });

                        if (response.ok) {
                            // Clear comment field if it exists
                            const commentField = form.querySelector('[name="comment"]');
                            if (commentField) commentField.value = '';

                            // Trigger immediate refresh to show updated data
                            await refreshEmployeeData();

                            // Show success feedback
                            const successText = originalText === 'Start' ? 'Started!' :
                                originalText === 'Stop' ? 'Stopped!' :
                                    originalText === 'Active' ? 'Deactivated!' :
                                        originalText === 'Inactive' ? 'Activated!' :
                                            originalText === 'Archive' ? 'Archived!' : 'Done!';

                            submitButton.textContent = successText;
                            submitButton.classList.add('bg-green-600');

                            setTimeout(() => {
                                submitButton.textContent = originalText;
                                submitButton.className = originalClasses;
                                submitButton.disabled = false;
                            }, 1500);
                        } else {
                            throw new Error('Request failed');
                        }
                    } catch (error) {
                        console.error('AJAX form submission error:', error);
                        submitButton.textContent = 'Error';
                        submitButton.classList.add('bg-red-600');

                        setTimeout(() => {
                            submitButton.textContent = originalText;
                            submitButton.className = originalClasses;
                            submitButton.disabled = false;
                        }, 2000);
                    }
                });




                // Pause refresh when page is not visible
                document.addEventListener('visibilitychange', () => {
                    if (document.hidden) {
                        stopAutoRefresh();
                    } else {
                        startAutoRefresh();
                    }
                });

                // Debug: Detect if something is trying to navigate away
                window.addEventListener('beforeunload', (e) => {
                    console.warn('[DEBUG] Page is about to unload/reload!');
                    console.trace('[DEBUG] Unload stack trace');
                });

            });

            // Clean up on page unload
            window.addEventListener('beforeunload', () => {
                stopAutoRefresh();
            });

            // Mobile accordion for Team employees Overview table
            const employeeOverviewMql = window.matchMedia('(max-width: 1024px)');

            function setEmployeeOverviewRowCollapsed(row, collapsed) {
                if (!row) return;
                row.classList.toggle('is-collapsed', collapsed);
                row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
            }

            function initEmployeeOverviewAccordion() {
                const rows = document.querySelectorAll('#employeesTable tbody tr.employee-overview-row');
                const isMobile = employeeOverviewMql.matches;
                rows.forEach(row => setEmployeeOverviewRowCollapsed(row, isMobile));
            }

            document.addEventListener('click', function (e) {
                const toggleCell = e.target.closest('[data-employee-overview-toggle]');
                if (!toggleCell) return;

                // Allow link clicks to navigate normally.
                if (e.target.closest('a')) return;

                const row = toggleCell.closest('tr.employee-overview-row');
                if (!row) return;

                const collapsed = row.classList.contains('is-collapsed');
                setEmployeeOverviewRowCollapsed(row, !collapsed);
            });

            if (employeeOverviewMql.addEventListener) employeeOverviewMql.addEventListener('change', initEmployeeOverviewAccordion);
            else if (employeeOverviewMql.addListener) employeeOverviewMql.addListener(initEmployeeOverviewAccordion);

            initEmployeeOverviewAccordion();
        </script>

        {{-- Stop Comment Modal --}}
        <div id="stopCommentModal" class="modal">
            <div class="modal-content-custom">
                <span id="stopCommentClose" class="close-button">&times;</span>
                <div class="flex items-center justify-center mb-3">
                    <div class="bg-red-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Stop Task') }}</h2>
                <p class="text-sm text-slate-500 mb-4">{{ __('messages.Please leave a comment describing the work done before stopping the task.') }}</p>
                <div class="text-left mb-4">
                    <div class="flex justify-between items-center mb-1">
                        <label for="stopCommentText" class="block text-sm font-medium text-slate-700">{{ __('messages.Comment') }} <span class="text-red-500">*</span></label>
                        <div class="flex gap-3 items-center">
                            <button type="button" id="stopCommentAiBtn" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Correct with AI') }}" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                                <span id="stopCommentAiBtnText">{{ __('messages.Correct with AI') }}</span>
                            </button>
                            <button type="button" id="stopCommentTranslateBtn" class="text-xs font-semibold text-brand-secondary hover:text-[#245f6d] flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Translate to Dutch') }}" disabled>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                                </svg>
                                <span id="stopCommentTranslateBtnText">{{ __('messages.Translate to Dutch') }}</span>
                            </button>
                        </div>
                    </div>
                    <textarea id="stopCommentText" rows="3" maxlength="500" placeholder="{{ __('messages.Describe what you worked on...') }}" spellcheck="true" class="border border-slate-300 rounded-lg px-3 py-2 w-full text-sm focus:outline-none focus:ring-2 focus:ring-red-400 resize-none transition-colors"></textarea>
                    <p id="stopCommentError" class="text-xs text-red-500 mt-1 hidden">{{ __('messages.Comment is required to stop the task.') }}</p>
                    <p id="stopCommentAiFeedback" class="text-xs mt-1 hidden"></p>
                </div>
                <div class="flex justify-end gap-2 pt-2">
                    <button type="button" id="stopCommentCancelBtn" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">{{ __('messages.Cancel') }}</button>
                    <button type="button" id="stopCommentConfirmBtn" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-lg hover:bg-red-600 transition-colors opacity-50 cursor-not-allowed" disabled>{{ __('messages.Confirm Stop') }}</button>
                </div>
            </div>
        </div>

        {{-- Spell-Check Confirmation Modal --}}
        <div id="spellCheckModal" class="modal" style="z-index: 60;">
            <div class="modal-content-custom" style="max-width: 420px;">
                <div class="flex items-center justify-center mb-3">
                    <div class="bg-amber-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Check Spelling?') }}</h2>
                <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to verify the spelling of your comment before submitting?') }}</p>
                <div class="flex flex-col gap-2">
                    <button type="button" id="spellCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-slate-500 rounded-lg hover:bg-slate-600 transition-colors flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('messages.Yes, verify spelling') }}
                    </button>
                    <button type="button" id="spellCheckSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        {{ __('messages.No, submit as is') }}
                    </button>
                </div>
            </div>
        </div>

        {{-- Translate Confirmation Modal --}}
        <div id="translateCheckModal" class="modal" style="z-index: 60;">
            <div class="modal-content-custom" style="max-width: 420px;">
                <div class="flex items-center justify-center mb-3">
                    <div class="bg-blue-100 rounded-full p-3">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-brand-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Translate to Dutch?') }}</h2>
                <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to translate your comment into Dutch before submitting?') }}</p>
                <div class="flex flex-col gap-2">
                    <button type="button" id="translateCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-brand-secondary rounded-lg hover:bg-[#245f6d] transition-colors flex items-center justify-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        {{ __('messages.Yes, translate to Dutch') }}
                    </button>
                    <button type="button" id="translateCheckSkipBtn" class="w-full px-4 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">
                        {{ __('messages.No, submit as is') }}
                    </button>
                </div>
            </div>
        </div>

        <script>
            $(document).ready(function() {
                // Store the form action URL and CSRF token at click time
                // (the DOM element may be replaced by auto-refresh before the user confirms)
                var storedFormAction = null;
                var storedCsrfToken = null;
                var stopModal = document.getElementById('stopCommentModal');
                var stopCommentText = document.getElementById('stopCommentText');
                var stopCommentError = document.getElementById('stopCommentError');
                var stopConfirmBtn = document.getElementById('stopCommentConfirmBtn');
                var stopCancelBtn = document.getElementById('stopCommentCancelBtn');
                var stopCloseBtn = document.getElementById('stopCommentClose');
                var stopCommentAiBtn = document.getElementById('stopCommentAiBtn');
                var stopCommentAiBtnText = document.getElementById('stopCommentAiBtnText');
                var stopCommentTranslateBtn = document.getElementById('stopCommentTranslateBtn');
                var stopCommentTranslateBtnText = document.getElementById('stopCommentTranslateBtnText');
                var stopCommentAiFeedback = document.getElementById('stopCommentAiFeedback');
                var spellCheckModal = document.getElementById('spellCheckModal');
                var spellCheckVerifyBtn = document.getElementById('spellCheckVerifyBtn');
                var spellCheckSkipBtn = document.getElementById('spellCheckSkipBtn');
                var translateCheckModal = document.getElementById('translateCheckModal');
                var translateCheckVerifyBtn = document.getElementById('translateCheckVerifyBtn');
                var translateCheckSkipBtn = document.getElementById('translateCheckSkipBtn');

                // State flags to avoid repetitive popups
                var hasSpelledChecked = false;
                var hasTranslated = false;

                // Intercept stop button clicks to show modal
                $(document).on('click', '.stop-button', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    var form = $(this).closest('.stop-form')[0];
                    // Snapshot the action URL and CSRF token NOW before auto-refresh replaces the DOM
                    storedFormAction = form.getAttribute('action');
                    storedCsrfToken = form.querySelector('input[name="_token"]').value;

                    // Pause dashboard auto-refresh while the modal is open
                    if (typeof stopAutoRefresh === 'function') stopAutoRefresh();
                    
                    // Reset modal state
                    stopCommentText.value = '';
                    stopCommentError.classList.add('hidden');
                    stopCommentAiFeedback.classList.add('hidden');
                    stopCommentAiFeedback.textContent = '';
                    stopConfirmBtn.disabled = true;
                    stopConfirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.disabled = true;
                    stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentTranslateBtn.disabled = true;
                    stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    hasSpelledChecked = false;
                    hasTranslated = false;
                    stopModal.style.display = 'block';
                    stopCommentText.focus();
                });

                // Enable/disable confirm, AI and Translate button based on comment content
                stopCommentText.addEventListener('input', function() {
                    var hasContent = this.value.trim().length > 0;
                    stopConfirmBtn.disabled = !hasContent;
                    stopCommentAiBtn.disabled = !hasContent;
                    stopCommentTranslateBtn.disabled = !hasContent;
                    if (hasContent) {
                        stopConfirmBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        stopCommentTranslateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        stopCommentError.classList.add('hidden');
                    } else {
                        stopConfirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                        stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    }
                });

                // AI correction request
                stopCommentAiBtn.addEventListener('click', function() {
                    var comment = stopCommentText.value.trim();
                    if (!comment) return;

                    stopCommentAiBtn.disabled = true;
                    stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentText.disabled = true;
                    var oldBtnText = stopCommentAiBtnText.textContent;
                    stopCommentAiBtnText.textContent = "{{ __('messages.Correcting...') }}";
                    stopCommentAiFeedback.classList.add('hidden');

                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch('/tasks/correct-comment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ comment: comment })
                    })
                    .then(function(res) {
                        if (!res.ok) {
                            return res.json().then(function(err) {
                                throw new Error(err.error || 'Failed to correct comment.');
                            });
                        }
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.corrected) {
                            stopCommentText.value = data.corrected;
                            stopCommentAiFeedback.textContent = "{{ __('messages.Spelling and grammar corrected!') }}";
                            stopCommentAiFeedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                            stopCommentAiFeedback.classList.remove('hidden');
                            hasSpelledChecked = true;
                        }
                    })
                    .catch(function(err) {
                        console.error(err);
                        stopCommentAiFeedback.textContent = "{{ __('messages.Failed to correct comment. Please check API configuration.') }}";
                        stopCommentAiFeedback.className = "text-xs mt-1 text-red-500 font-medium";
                        stopCommentAiFeedback.classList.remove('hidden');
                    })
                    .finally(function() {
                        stopCommentAiBtn.disabled = false;
                        stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        stopCommentText.disabled = false;
                        stopCommentAiBtnText.textContent = oldBtnText;
                    });
                });

                // Translate request
                stopCommentTranslateBtn.addEventListener('click', function() {
                    var comment = stopCommentText.value.trim();
                    if (!comment) return;

                    stopCommentTranslateBtn.disabled = true;
                    stopCommentTranslateBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.disabled = true;
                    stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentText.disabled = true;
                    var oldBtnText = stopCommentTranslateBtnText.textContent;
                    stopCommentTranslateBtnText.textContent = "{{ __('messages.Translating...') }}";
                    stopCommentAiFeedback.classList.add('hidden');

                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    fetch('/tasks/translate-comment', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({ comment: comment })
                    })
                    .then(function(res) {
                        if (!res.ok) {
                            return res.json().then(function(err) {
                                throw new Error(err.error || 'Failed to translate comment.');
                            });
                        }
                        return res.json();
                    })
                    .then(function(data) {
                        if (data.translated) {
                            stopCommentText.value = data.translated;
                            stopCommentAiFeedback.textContent = "{{ __('messages.Translated to Dutch!') }}";
                            stopCommentAiFeedback.className = "text-xs mt-1 text-emerald-600 font-medium";
                            stopCommentAiFeedback.classList.remove('hidden');
                            hasTranslated = true;
                        }
                    })
                    .catch(function(err) {
                        console.error(err);
                        stopCommentAiFeedback.textContent = "{{ __('messages.Failed to translate comment. Please check API configuration.') }}";
                        stopCommentAiFeedback.className = "text-xs mt-1 text-red-500 font-medium";
                        stopCommentAiFeedback.classList.remove('hidden');
                    })
                    .finally(function() {
                        stopCommentTranslateBtn.disabled = false;
                        stopCommentTranslateBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        stopCommentAiBtn.disabled = false;
                        stopCommentAiBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                        stopCommentText.disabled = false;
                        stopCommentTranslateBtnText.textContent = oldBtnText;
                    });
                });

                // Function to actually submit the stop task
                function doSubmitStop() {
                    var comment = stopCommentText.value.trim();
                    if (!storedFormAction) return;

                    // Disable button to prevent double submission
                    stopConfirmBtn.disabled = true;
                    stopConfirmBtn.textContent = "{{ __('messages.Stopping...') }}";

                    var formData = new FormData();
                    formData.append('_token', storedCsrfToken);
                    formData.append('stop_comment', comment);

                    fetch(storedFormAction, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    })
                    .then(function(response) {
                        if (response.ok) {
                            stopModal.style.display = 'none';
                            // Refresh the dashboard data to reflect the stopped task
                            if (typeof refreshEmployeeData === 'function') refreshEmployeeData();
                            if (typeof startAutoRefresh === 'function') startAutoRefresh();
                        } else {
                            throw new Error('Request failed');
                        }
                    })
                    .catch(function(err) {
                        console.error('Stop task error:', err);
                        alert("{{ __('messages.An error occurred while stopping the task.') }}");
                        stopConfirmBtn.disabled = false;
                        stopConfirmBtn.textContent = "{{ __('messages.Confirm Stop') }}";
                        if (typeof startAutoRefresh === 'function') startAutoRefresh();
                    });
                }

                // Confirm stop: show spell-check or translate popups if they haven't been completed yet
                stopConfirmBtn.addEventListener('click', function() {
                    var comment = stopCommentText.value.trim();
                    if (!comment) {
                        stopCommentError.classList.remove('hidden');
                        stopCommentText.focus();
                        return;
                    }
                    if (!storedFormAction) return;
                    
                    if (!hasSpelledChecked) {
                        spellCheckModal.style.display = 'block';
                    } else if (!hasTranslated) {
                        translateCheckModal.style.display = 'block';
                    } else {
                        doSubmitStop();
                    }
                });

                // Spell-check popup: verify spelling (trigger AI correction then return to comment modal)
                spellCheckVerifyBtn.addEventListener('click', function() {
                    spellCheckModal.style.display = 'none';
                    hasSpelledChecked = true;
                    // Trigger the AI correction button click
                    if (!stopCommentAiBtn.disabled) {
                        stopCommentAiBtn.click();
                    }
                });

                // Spell-check popup: skip and open translate confirmation popup
                spellCheckSkipBtn.addEventListener('click', function() {
                    spellCheckModal.style.display = 'none';
                    hasSpelledChecked = true;
                    if (!hasTranslated) {
                        translateCheckModal.style.display = 'block';
                    } else {
                        doSubmitStop();
                    }
                });

                // Close spell-check modal on outside click
                spellCheckModal.addEventListener('click', function(e) {
                    if (e.target === spellCheckModal) {
                        spellCheckModal.style.display = 'none';
                    }
                });

                // Translate popup: verify spelling (trigger translation then return to comment modal)
                translateCheckVerifyBtn.addEventListener('click', function() {
                    translateCheckModal.style.display = 'none';
                    hasTranslated = true;
                    if (!stopCommentTranslateBtn.disabled) {
                        stopCommentTranslateBtn.click();
                    }
                });

                // Translate popup: skip and submit directly
                translateCheckSkipBtn.addEventListener('click', function() {
                    translateCheckModal.style.display = 'none';
                    hasTranslated = true;
                    doSubmitStop();
                });

                // Close translate modal on outside click
                translateCheckModal.addEventListener('click', function(e) {
                    if (e.target === translateCheckModal) {
                        translateCheckModal.style.display = 'none';
                    }
                });

                // Cancel / Close modal
                function closeStopModal() {
                    stopModal.style.display = 'none';
                    storedFormAction = null;
                    storedCsrfToken = null;
                    // Resume auto-refresh
                    if (typeof startAutoRefresh === 'function') startAutoRefresh();
                }
                stopCancelBtn.addEventListener('click', closeStopModal);
                stopCloseBtn.addEventListener('click', closeStopModal);
                stopModal.addEventListener('click', function(e) {
                    if (e.target === stopModal) closeStopModal();
                });

                stopCommentText.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        if (!stopConfirmBtn.disabled) {
                            stopConfirmBtn.click();
                        }
                    }
                });
            });
        </script>