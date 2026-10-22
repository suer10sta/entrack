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

    /* Dashboard: Team employees Overview table -> card layout below desktop widths,
       real table on >= 1366px so columns never get cramped */
    @media (max-width: 1365.98px) {
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
            margin-bottom: 14px;
            padding: 12px;
            border: 1px solid rgba(148, 163, 184, 0.45);
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(15,23,42,0.07);
        }

        #employeesTable tbody td {
            display: block;
            width: 100%;
            padding: 10px 12px !important;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 10px;
            background: rgba(248,250,252,0.7);
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
            font-size: 10.5px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 5px;
        }

        /* Accordion: collapsed shows only employee cell */
        #employeesTable tbody tr.employee-overview-row.is-collapsed td:not(.employee-overview-toggle-cell) {
            display: none !important;
        }
        #employeesTable tbody tr.employee-overview-row td.employee-overview-toggle-cell {
            cursor: pointer;
            user-select: none;
        }
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

    /* ---- Stat card (+) quick-add button: same visual system as the card icon ---- */
    .dt-stat-plus {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 12px; /* matches icon container rounded-xl */
        background: rgba(11, 38, 56, 0.06); /* matches bg-[#0B2638]/[0.06] */
        color: #0B2638;
        box-shadow: inset 0 0 0 1px rgba(11, 38, 56, 0.10); /* matches ring-[#0B2638]/10 */
        flex-shrink: 0;
        transition: background-color .15s ease, box-shadow .15s ease, color .15s ease, transform .15s ease;
    }
    .dt-stat-plus:hover {
        background: rgba(45, 112, 128, 0.12);
        box-shadow: inset 0 0 0 1px rgba(45, 112, 128, 0.35);
        color: #2D7080;
        transform: translateY(-1px);
    }
    .dt-stat-plus:focus-visible {
        outline: 2px solid #2D7080;
        outline-offset: 2px;
    }
    .dt-stat-plus svg { width: 16px; height: 16px; }

    /* ---- Compact action buttons in the employee table ---- */
    .dt-tbl-action {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        height: 30px;
        padding: 0 12px;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1;
        white-space: nowrap;
        cursor: pointer;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease, box-shadow .15s ease;
    }
    .dt-tbl-action svg { width: 14px; height: 14px; flex-shrink: 0; }
    .dt-tbl-action-primary { background: #0B2638; color: #fff; }
    .dt-tbl-action-primary:hover { background: #16394a; color: #fff; box-shadow: 0 4px 10px -4px rgba(11, 38, 56, 0.5); }
    .dt-tbl-action-ghost { background: #f8fafc; color: #475569; border-color: #e2e8f0; }
    .dt-tbl-action-ghost:hover { border-color: #2D7080; color: #2D7080; background: #f0f7f8; }
    .dt-tbl-action-outline { background: #fffbeb; color: #92400e; border-color: #fde68a; }
    .dt-tbl-action-outline:hover { background: #fef3c7; border-color: #f59e0b; }

    /* ---- Icon-only action buttons in the employee table ---- */
    .dt-icon-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        flex-shrink: 0;
        cursor: pointer;
        transition: background-color .15s ease, border-color .15s ease, color .15s ease, transform .15s ease;
    }
    .dt-icon-btn:hover { transform: translateY(-1px); }
    .dt-icon-btn:focus-visible { outline: 2px solid #2D7080; outline-offset: 2px; }
    .dt-icon-btn:disabled { opacity: .55; cursor: default; transform: none; }
    .dt-icon-btn svg { width: 15px; height: 15px; flex-shrink: 0; }
    .dt-icon-btn-edit:hover { border-color: #2D7080; color: #2D7080; background: #f0f7f8; }
    .dt-icon-btn-archive { color: #b45309; border-color: #fde68a; background: #fffbeb; }
    .dt-icon-btn-archive:hover { background: #fef3c7; border-color: #f59e0b; color: #92400e; }
    .dt-icon-btn-delete { color: #dc2626; border-color: #fecaca; background: #fef2f2; }
    .dt-icon-btn-delete:hover { background: #fee2e2; border-color: #f87171; }

    /* ---- Generic action-confirm modal (replaces native confirm()) ---- */
    #confirmActionOkBtn {
        display: inline-flex; align-items: center; justify-content: center;
        padding: 8px 18px; font-size: 13px; font-weight: 600; color: #fff;
        border-radius: 8px; cursor: pointer;
        transition: background-color .15s ease;
    }
    #confirmActionOkBtn.dt-confirm-danger { background: #dc2626; }
    #confirmActionOkBtn.dt-confirm-danger:hover { background: #b91c1c; }
    #confirmActionOkBtn.dt-confirm-warn { background: #d97706; }
    #confirmActionOkBtn.dt-confirm-warn:hover { background: #b45309; }
    .dt-confirm-icon-danger { background: #fee2e2; color: #dc2626; }
    .dt-confirm-icon-warn { background: #fef3c7; color: #d97706; }

    /* ---- Desktop table refinements (>= 1366px) ---- */
    @media (min-width: 1366px) {
        #employeesTable { min-width: 960px; }
        #employeesTable thead tr.dt-table-head th {
            background: #f8fafc;
            border-bottom: 1px solid #cbd5e1;
        }
        #employeesTable thead tr.dt-table-head th + th {
            border-left: 1px solid #e2e8f0;
        }
        #employeesTable tbody td { border-left: 1px solid transparent; }
        #employeesTable tbody td + td { border-left: 1px solid #e2e8f0; }
        #employeesTable tbody tr.employee-overview-row:hover td { background: rgba(248, 250, 252, 0.8); }
    }
    /* Chevron only exists in card mode */
    .overview-chevron-toggle { display: inline-flex; }
    @media (min-width: 1366px) {
        .overview-chevron-toggle { display: none; }
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
        color: var(--dt-text-muted, #5b6b7e);
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


<div class="dt-dashboard-canvas flex-1 bg-[#f8fafc]">
    {{-- Main content --}}
    <div class="dt-dashboard-content mx-auto w-full">

        {{-- Page heading + Quick actions --}}
        <div class="dt-dashboard-heading flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
            <div class="min-w-0">
                <!-- <p class="dt-overline mb-1">{{ now()->format('l, d F Y') }}</p> -->
                <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
                    {{ __('messages.Dashboard') }}
                </h1>
                <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Track time, projects and revenue at a glance.') }}</p>
            </div>
            <div class="dt-dashboard-actions flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.tasks.create') }}" class="dt-btn dt-btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    {{ __('messages.Add Task') }}
                </a>
                <a href="{{ route('admin.invoices.fullinvoice') }}" class="dt-btn bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 hover:bg-[#0B2638]/[0.12] hover:ring-[#0B2638]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    {{ __('messages.Generate invoice') }}
                </a>
                <a href="{{ route('admin.calendar.index') }}" class="dt-btn bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 hover:bg-[#0B2638]/[0.12] hover:ring-[#0B2638]/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    {{ __('messages.Planning Menu') }}
                </a>
            </div>
        </div>

        {{-- Stats Cards — consistent DalyTrac design --}}
        <div class="dt-dashboard-metrics grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Clients --}}
            <div class="dt-card dt-card-hover p-4 flex items-center gap-3.5">
                <a href="{{ route('admin.clients.index') }}" class="flex items-center gap-3.5 flex-1 min-w-0 no-underline">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <rect x="4" y="3" width="16" height="18" rx="2" stroke-linecap="round" stroke-linejoin="round" />
                            <path d="M9 21v-4h6v4M8 7h2M8 11h2M14 7h2M14 11h2" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Clients') }}</p>
                        <div class="flex items-baseline gap-1.5 mt-0.5 min-w-0">
                            <span class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight">{{ $data['clients']->count() }}</span>
                            <span class="text-[12px] text-slate-400 font-medium hidden min-[1440px]:inline">{{ __('messages.Total') }}</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.clients.create') }}"
                   class="dt-stat-plus"
                   title="{{ __('messages.Add Client') }}"
                   aria-label="{{ __('messages.Add Client') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            </div>

            {{-- Employees --}}
            <div class="dt-card dt-card-hover p-4 flex items-center gap-3.5">
                <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-3.5 flex-1 min-w-0 no-underline">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <circle cx="9" cy="8" r="3.5" />
                            <path d="M3.5 20c0-3.3 2.5-5 5.5-5s5.5 1.7 5.5 5" stroke-linecap="round" />
                            <path d="M16 5.6a3.5 3.5 0 010 6.5M17.7 15.4c1.9.7 2.8 2 2.8 4.1" stroke-linecap="round" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Employees') }}</p>
                        <div class="flex items-baseline gap-1.5 mt-0.5 min-w-0">
                            <span class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight">{{ $data['employees']->count() }}</span>
                            <span class="text-[12px] text-slate-400 font-medium hidden min-[1440px]:inline">{{ __('messages.Total') }}</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.employees.create') }}"
                   class="dt-stat-plus"
                   title="{{ __('messages.Add Employee') }}"
                   aria-label="{{ __('messages.Add Employee') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            </div>

            {{-- Projects --}}
            <div class="dt-card dt-card-hover p-4 flex items-center gap-3.5">
                <a href="{{ route('admin.projects.index') }}" class="flex items-center gap-3.5 flex-1 min-w-0 no-underline">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                            <path d="M3 7a2 2 0 0 1 2-2h4l2 2h8a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7z" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Projects') }}</p>
                        <div class="flex items-baseline gap-1.5 mt-0.5 min-w-0">
                            <span class="text-[26px] leading-none font-bold text-[#0B2638] tracking-tight">{{ $data['totalProjectsCount'] ?? ($data['projects']->total() ?? $data['projects']->count()) }}</span>
                            <span class="text-[12px] text-slate-400 font-medium hidden min-[1440px]:inline">{{ __('messages.Total') }}</span>
                        </div>
                    </div>
                </a>
                <a href="{{ route('admin.projects.create') }}"
                   class="dt-stat-plus"
                   title="{{ __('messages.Add Project') }}"
                   aria-label="{{ __('messages.Add Project') }}">
                    <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </a>
            </div>

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
                    <div class="flex items-center flex-wrap gap-x-2 gap-y-1 mt-0.5">
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
                <div class="flex-shrink-0 hidden 2xl:block"
                     title="{{ __('messages.Paid invoices') }}: €{{ number_format($monthlyPaid, 0, ',', ' ') }} / €{{ number_format($data['monthlyRevenue'] ?? 0, 0, ',', ' ') }} ({{ $collectionDisplay }}%)">
                    <div class="relative w-14 h-14" role="img" aria-label="{{ __('messages.Paid invoices') }}: {{ $collectionDisplay }}%">
                        <svg viewBox="0 0 64 64" class="w-14 h-14">
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

        {{-- Today's Events Section - Moved to top for visibility --}}
        <div class="dt-dashboard-events">
            <div class="dt-card p-5 animate-fadeInSlideUp" style="animation-delay: 200ms;">
                {{-- Header --}}
                <div class="dt-events-heading flex items-center justify-between gap-3 mb-4">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="w-1.5 h-6 bg-[#E7B84B] rounded-full flex-shrink-0"></span>
                        <h2 class="dt-section-title truncate">{{ __('messages.Today\'s Events') }}</h2>
                    </div>
                    <a href="{{ route('admin.calendar.index') }}" class="dt-btn dt-btn-ghost flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ __('messages.See full calendar') }}
                    </a>
                </div>

                {{-- Date --}}
                <p class="text-[12.5px] text-slate-500 mb-4">{{ now()->locale(app()->getLocale())->translatedFormat('d F Y') }}</p>

                {{-- Events list in horizontal scrollable cards --}}
                @if($data['dayEvents']->isNotEmpty())
                    <div class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1" style="-webkit-overflow-scrolling: touch;">
                        @foreach($data['dayEvents'] as $event)
                            <div class="flex-shrink-0 w-64 sm:w-72 flex gap-3 rounded-xl border border-slate-100 bg-slate-50/60 px-3 py-3 hover:border-[#2D7080] hover:shadow-sm transition-all">
                                <div class="w-1 flex-shrink-0 bg-[#2D7080] rounded-full"></div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-[13px] font-semibold text-[#0B2638] truncate">{{ $event->title }}</p>
                                    <p class="text-[12px] text-slate-500 flex items-center gap-1.5 mt-1.5">
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
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="dt-dashboard-empty rounded-xl border border-dashed border-slate-200 px-6 py-8 text-center">
                        <div class="dt-empty-icon mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="text-[13.5px] font-semibold text-[#0B2638] mb-1">{{ __('messages.No events scheduled for today.') }}</p>
                        <p class="text-[12.5px] text-slate-500">{{ __('messages.Enjoy a calm day — or plan one.') }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Team employees Overview --}}
        <div class="dt-dashboard-team dt-card overflow-hidden animate-fadeInSlideUp"
            style="animation-delay: 500ms;">
            {{-- Header and action button --}}
            <div class="px-5 sm:px-6 py-4 flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between border-b border-slate-100 bg-white">
                <div class="min-w-0">
                    <h2 class="dt-section-title">{{ __('messages.Team employees Overview') }}</h2>
                    <p class="dt-section-subtitle mt-0.5">{{ __('messages.Live status, timers and actions for today.') }}</p>
                </div>
            </div>

            {{-- Table --}}
            <div class="team-employees-overview-table-wrap px-3 sm:px-4 py-3 overflow-x-auto">
                <table id="employeesTable" class="w-full text-left" style="width:100%">
                    <thead>
                        <tr class="dt-table-head">
                            <th class="px-3 py-2.5 text-left w-[200px]">{{ __('messages.Employee') }}</th>
                            <th class="px-3 py-2.5 text-center w-[120px]">{{ __('messages.Statut') }}</th>
                            <th class="px-3 py-2.5 text-left w-[190px]">{{ __('messages.Current Project') }}</th>
                            <th class="px-3 py-2.5 text-left">{{ __('messages.Tâche en cours') }}</th>
                            <th class="px-3 py-2.5 text-center w-[128px]">{{ __('messages.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @php
                            $isSuperAdmin = auth()->user()->hasRole('SuperAdmin') || auth()->user()->hasRole('super_admin');
                        @endphp
                        @foreach($data['employees'] as $employee)
                            @php
                                $employeeUser = \App\Models\User::find($employee->user_id);
                                $employeeRoles = is_array($employee->roles ?? null) ? $employee->roles : (array)($employee->roles ?? []);
                                $nowRow = \Carbon\Carbon::now();

                                // Active timers (open end_date), used across all cells
                                if ($isSuperAdmin) {
                                    $rowActiveTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                        ->whereNull('end_date')
                                        ->orderBy('start_date', 'desc')
                                        ->get();
                                } else {
                                    $rowActiveTasks = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                        ->whereNull('end_date')
                                        ->latest()->take(1)->get();
                                }

                                // Group open + today's completed timers by task_id so every task
                                // the employee worked on today shows once in the task column
                                $rowTodayCompleted = \App\Models\tasks_timer::where('employee_id', $employee->id)
                                    ->whereDate('start_date', \Carbon\Carbon::today())
                                    ->whereNotNull('end_date')
                                    ->orderBy('start_date', 'desc')
                                    ->get();
                                $rowTasksToday = $rowActiveTasks->concat($rowTodayCompleted)->groupBy('task_id');

                                $rowActiveIds = $rowActiveTasks->pluck('task_id')->filter()->unique();

                                // Active tasks for actions php block
                                $activeTimers = $rowActiveTasks;
                                $activeTaskIds = $rowActiveIds;
                            @endphp
                            <tr class="employee-overview-row hover:bg-slate-50/60 transition-colors" data-employee-id="{{ $employee->id }}" aria-expanded="true">

                                {{-- EMPLOYEE --}}
                                <td data-label="{{ __('messages.Employee') }}" data-span="2"
                                    class=" py-2.5 align-middle employee-overview-toggle-cell" data-employee-overview-toggle>
                                    <div class="flex items-center justify-between gap-2">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="flex flex-col min-w-0">
                                                <a href="{{ route('admin.employees.show', $employee->id) }}"
                                                    class="text-[13px] font-semibold text-slate-800 hover:text-brand-secondary whitespace-nowrap leading-tight">
                                                    {{ $employee->name }}
                                                </a>
                                                <span class="text-[11px] text-slate-400 whitespace-normal leading-tight" style="overflow-wrap: anywhere;">{{ $employee->email }}</span>
                                                @if(count($employeeRoles))
                                                    <div class="flex flex-wrap gap-0.5 mt-0.5">
                                                        @foreach($employeeRoles as $role)
                                                            <span class="inline-block px-1.5 py-px text-[9.5px] font-semibold rounded
                                                                {{ $role === 'SuperAdmin' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700' }}">{{ $role }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="shrink-0 text-slate-300 overview-chevron-toggle">
                                            <svg class="employee-overview-chevron w-4 h-4" viewBox="0 0 24 24" fill="none">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>

                                @include('components.dashboard.employee-row-cells')

                                {{-- ACTIONS --}}
                                <td data-label="{{ __('messages.Actions') }}" data-span="2"
                                    class="px-3 py-2.5 align-middle">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <a href="{{ route('admin.employees.show', $employee->id) }}"
                                            title="{{ __('messages.Edit') }}" aria-label="{{ __('messages.Edit') }}"
                                            class="dt-icon-btn dt-icon-btn-edit">
                                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <form action="{{ route('admin.employees.archive', $employee->id) }}" method="post"
                                            data-confirm="{{ __('messages.Are you sure?') }}" data-confirm-kind="archive" class="contents">
                                            @csrf
                                            <button type="submit" class="dt-icon-btn dt-icon-btn-archive"
                                                title="{{ __('messages.Archive') }}" aria-label="{{ __('messages.Archive') }}">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                                                </svg>
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="post"
                                            data-confirm="{{ __('messages.Are you sure?') }}" data-confirm-kind="delete" class="contents">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dt-icon-btn dt-icon-btn-delete"
                                                title="{{ __('messages.Delete') }}" aria-label="{{ __('messages.Delete') }}">
                                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @livewireStyles
        @livewireScripts

        <script>
            function stopAllTimersForEmployee(employeeId) {
                const forms = document.querySelectorAll('.stop-form-' + employeeId);
                if (!forms.length) return;
                if (!confirm(@js(__('messages.Stop all timers for this employee?')))) return;

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

                    // Remove rows of employees that are gone (archived/deleted) so no
                    // stale row with dead action buttons is left behind
                    const seenIds = new Set(data.employees.map(employee => String(employee.id)));
                    document.querySelectorAll('#employeesTable tbody tr[data-employee-id]').forEach(tr => {
                        if (!seenIds.has(tr.dataset.employeeId)) {
                            tr.remove();
                        }
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

                // Replace the dynamic cells (Status → Project → Task/Timer)
                // with fresh markup rendered by the shared employee-row-cells Blade
                // partial — the same partial that produced the initial page markup,
                // so a refresh can never insert different/older styling.
                if (employee.rowCells) {
                    const startCell = row.querySelector('.cell-current-status');
                    const endCell = row.querySelector('.cell-current-task');

                    // Parse in a detached <tr> so <td> tokens keep their table context
                    const probe = document.createElement('tr');
                    probe.innerHTML = employee.rowCells;

                    if (startCell && endCell
                        && startCell.parentNode === endCell.parentNode
                        && probe.querySelector('.cell-current-status')
                        && probe.querySelector('.cell-current-task')) {
                        const frag = document.createDocumentFragment();
                        while (probe.firstChild) frag.appendChild(probe.firstChild);
                        startCell.parentNode.insertBefore(frag, startCell);

                        // Remove the old Status..Task cells (inclusive)
                        let node = startCell;
                        while (node) {
                            const next = node.nextSibling;
                            node.parentNode.removeChild(node);
                            if (node === endCell) break;
                            node = next;
                        }
                    }
                }

            }

            // Start auto-refresh
            function startAutoRefresh() {
                refreshEmployeeData();
                refreshTimer = setInterval(() => {
                    refreshEmployeeData();
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

                    // Check if this form is inside the employees table
                    const isEmployeeTableForm = form.closest('#employeesTable');

                    if (!isEmployeeTableForm) {
                        return; // Not a dashboard form, let it submit normally
                    }

                    e.preventDefault(); // Prevent page reload
                    e.stopPropagation(); // Stop event from bubbling

                    const submitButton = form.querySelector('button[type="submit"]');
                    if (!submitButton) return;

                    const originalText = submitButton.textContent.trim();
                    const originalClasses = submitButton.className;
                    // Icon-only buttons have no text content: the textContent feedback below
                    // would wipe their SVG, so they only get the disabled state as feedback.
                    const isIconButton = originalText === '' && submitButton.querySelector('svg');

                    // Prevent double submission
                    if (submitButton.disabled) return;

                    // Handle confirmation dialogs (custom modal, replaces native confirm())
                    const confirmMessage = form.getAttribute('data-confirm');
                    if (confirmMessage) {
                        const confirmed = await requestActionConfirm(form.getAttribute('data-confirm-kind') || 'default', confirmMessage, originalText);
                        if (!confirmed) {
                            return; // User cancelled
                        }
                    }
                    // Fallback for forms still using onsubmit (if any)
                    else if (form.getAttribute('onsubmit') && form.getAttribute('onsubmit').includes('confirm')) {
                        const onsubmit = form.getAttribute('onsubmit');
                        const confirmMatch = onsubmit.match(/confirm\(['"](.+?)['"]\)/);
                        if (confirmMatch) {
                            const confirmed = await requestActionConfirm('default', confirmMatch[1], originalText);
                            if (!confirmed) {
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
                        if (!isIconButton) submitButton.textContent = loadingText;

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

                            // Chrono actions change the GLOBAL timer state: let the navbar
                            // (single timer owner) re-fetch the source of truth so it, the
                            // store and all row controls reflect it immediately, no reload.
                            // Single mechanism: one event (single-flight guard dedupes anyway).
                            if (/\/(start|stop|pause|resume)(\/|$|[?#])/.test(form.action)) {
                                try { window.dispatchEvent(new CustomEvent('chrono-revalidate', { detail: { force: true } })); } catch {}
                            }

                            // Show success feedback
                            const successText = originalText === 'Start' ? 'Started!' :
                                originalText === 'Stop' ? 'Stopped!' :
                                    originalText === 'Active' ? 'Deactivated!' :
                                        originalText === 'Inactive' ? 'Activated!' :
                                            originalText === 'Archive' ? 'Archived!' : 'Done!';

                            if (!isIconButton) {
                                submitButton.textContent = successText;
                                submitButton.classList.add('bg-green-600');
                            }

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
                        if (!isIconButton) {
                            submitButton.textContent = 'Error';
                            submitButton.classList.add('bg-red-600');
                        }

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
            const employeeOverviewMql = window.matchMedia('(max-width: 1365px)');

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
        <x-chrono-stop-modal />

{{-- Generic action confirmation modal (replaces native confirm()) --}}
        <div id="confirmActionModal" class="modal" style="z-index: 70;">
            <div class="modal-content-custom" style="max-width: 400px;">
                <div class="flex items-center justify-center mb-3">
                    <div id="confirmActionIconWrap" class="rounded-full p-3 dt-confirm-icon-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <h2 id="confirmActionTitle" class="text-lg font-semibold text-slate-800 mb-2"></h2>
                <p id="confirmActionMessage" class="text-sm text-slate-500 mb-5"></p>
                <div class="flex justify-center gap-2">
                    <button type="button" id="confirmActionCancelBtn" class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors">{{ __('messages.Cancel') }}</button>
                    <button type="button" id="confirmActionOkBtn" class="dt-confirm-danger">OK</button>
                </div>
            </div>
        </div>

        <script>
            // Reusable promise-based confirmation dialog (replaces native confirm()).
            // Usage: const ok = await requestActionConfirm('stop'|'archive'|'delete'|'default', message, fallbackOkText);
            let confirmActionResolve = null;
            const CONFIRM_ACTION_KINDS = {
                stop: { title: @js(__('messages.Stop Task')), ok: @js(__('messages.Stop')), tone: 'danger' },
                archive: { title: @js(__('messages.Archive')), ok: @js(__('messages.Archive')), tone: 'warn' },
                delete: { title: @js(__('messages.Delete')), ok: @js(__('messages.Delete')), tone: 'danger' },
                default: { title: @js(__('messages.Are you sure?')), ok: @js(__('messages.Confirm')), tone: 'danger' }
            };
            function requestActionConfirm(kind, message, fallbackOkText) {
                const preset = CONFIRM_ACTION_KINDS[kind] || CONFIRM_ACTION_KINDS.default;
                document.getElementById('confirmActionTitle').textContent = preset.title;
                document.getElementById('confirmActionMessage').textContent = message;
                const okBtn = document.getElementById('confirmActionOkBtn');
                okBtn.textContent = preset.ok || fallbackOkText || 'OK';
                okBtn.classList.remove('dt-confirm-danger', 'dt-confirm-warn');
                okBtn.classList.add(preset.tone === 'warn' ? 'dt-confirm-warn' : 'dt-confirm-danger');
                const iconWrap = document.getElementById('confirmActionIconWrap');
                iconWrap.classList.remove('dt-confirm-icon-danger', 'dt-confirm-icon-warn');
                iconWrap.classList.add(preset.tone === 'warn' ? 'dt-confirm-icon-warn' : 'dt-confirm-icon-danger');
                document.getElementById('confirmActionModal').style.display = 'block';
                okBtn.focus();
                return new Promise((resolve) => { confirmActionResolve = resolve; });
            }
            function closeConfirmActionDialog(result) {
                document.getElementById('confirmActionModal').style.display = 'none';
                if (confirmActionResolve) {
                    const resolve = confirmActionResolve;
                    confirmActionResolve = null;
                    resolve(result);
                }
            }
            document.getElementById('confirmActionOkBtn').addEventListener('click', () => closeConfirmActionDialog(true));
            document.getElementById('confirmActionCancelBtn').addEventListener('click', () => closeConfirmActionDialog(false));
            document.getElementById('confirmActionModal').addEventListener('click', (e) => {
                if (e.target === document.getElementById('confirmActionModal')) closeConfirmActionDialog(false);
            });
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && document.getElementById('confirmActionModal').style.display === 'block') {
                    closeConfirmActionDialog(false);
                }
            });
        </script>

        <script>
$(document).ready(function() {
$(document).on('click', '.stop-button', function(event) {
                event.preventDefault(); event.stopPropagation();
                const form = this.closest('.stop-form');
                if (!form) return;
                const action = form.getAttribute('action');
                const token = form.querySelector('input[name="_token"]')?.value;
                window.ChronoStopModal.open(action, async function(comment) {
                    const data = new FormData(); data.append('_token', token); data.append('stop_comment', comment);
                    const response = await fetch(action, { method: 'POST', body: data, headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                    if (!response.ok) throw new Error('Stop failed');
                    if (typeof refreshEmployeeData === 'function') refreshEmployeeData();
                    if (typeof startAutoRefresh === 'function') startAutoRefresh();
                    window.dispatchEvent(new CustomEvent('chrono-revalidate', { detail: { force: true } }));
                });
            });
});
</script>
    </div>{{-- /p-4 sm:p-6 main content inner --}}
</div>{{-- /flex-1 overflow-auto --}}
