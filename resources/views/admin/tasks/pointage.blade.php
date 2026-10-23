<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Tasks Timer - DalyTrac') }}</title>
    
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Alpine.js Modal Functions - Must be defined before Alpine.js loads --}}
    <script>
        // --- ALPINE MODAL LOGIC ---
        function editDateModal() {
            return {
                show: false,
                taskId: null,
                field: '',
                date: '',
                time: '',
                open(detail) {
                    this.taskId = detail.taskId;
                    this.field = detail.field;
                    if (detail.datetime) {
                        this.date = detail.datetime.substring(0, 10);
                        this.time = detail.datetime.substring(11, 16);
                    } else {
                        const now = new Date();
                        // Adjust for timezone offset
                        now.setMinutes(now.getMinutes() - now.getTimezoneOffset());
                        const isoNow = now.toISOString().substring(0, 16);
                        this.date = isoNow.substring(0, 10);
                        this.time = isoNow.substring(11, 16);
                    }
                    this.show = true;
                },
                save() {
                    const newValue = `${this.date} ${this.time}`;
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `/admin/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `/Responsable_de_projet/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `/employee/tasks/update-${this.field}/${this.taskId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `/Responsable_gestionnaire/tasks/update-${this.field}/${this.taskId}`; @endif

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: {
                            [this.field]: newValue,
                            _token: '{{ csrf_token() }}'
                        },
                        success: (response) => {
                            if (response.status === 'success') {
                                location.reload();
                            } else {
                                alert(response.message || @json(__('messages.Failed to update.')));
                            }
                        },
                        error: (xhr) => {
                            let msg = @json(__('messages.Failed to update.'));
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                msg = xhr.responseJSON.message;
                            }
                            alert(msg);
                        }
                    });
                    this.show = false;
                }
            }
        }
        
        @php
            $rolePrefix = 'admin';
            if (auth()->user()->hasRole('Responsable de projet')) {
                $rolePrefix = 'Responsable_de_projet';
            } elseif (auth()->user()->hasRole('Employee')) {
                $rolePrefix = 'employee';
            } elseif (auth()->user()->hasRole('Responsable de projet et gestionnaire')) {
                $rolePrefix = 'Responsable_gestionnaire';
            }
        @endphp
        window.rolePrefix = @json($rolePrefix);
        var rolePrefix = window.rolePrefix;

        function moveDurationModal() {
            return {
                show: false,
                originalTimerId: null,
                currentTaskId: null,
                projectId: '',
                taskId: '',
                employeeId: '',
                startDate: '',
                endDate: '',
                duration: '',
                durationMinutes: 0,
                originalDuration: 0,
                open(detail) {
                    this.originalTimerId = detail.timerId;
                    this.currentTaskId = detail.taskId;
                    this.projectId = detail.projectId;
                    this.employeeId = detail.employeeId;
                    this.startDate = detail.startDate;
                    this.endDate = detail.endDate;
                    this.originalDuration = detail.duration;
                    this.durationMinutes = detail.duration;
                    this.duration = this.minutesToHoursMinutes(detail.duration);
                    this.loadTasks(detail.taskId);
                    this.show = true;
                },
                loadTasks(selectedTaskId = null) {
                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = `/admin/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = `/Responsable_de_projet/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Employee')) url = `/employee/tasks/get-project-tasks/${this.projectId}`; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = `/Responsable_gestionnaire/tasks/get-project-tasks/${this.projectId}`; @endif

                    fetch(url)
                        .then(response => response.json())
                        .then(tasks => {
                            const taskSelect = document.getElementById('taskSelect');
                            taskSelect.innerHTML = '';
                            tasks.forEach(task => {
                                const option = new Option(task.name, task.id);
                                taskSelect.add(option);
                            });
                            this.taskId = selectedTaskId || (tasks.length > 0 ? tasks[0].id : '');
                        });
                },
                calculateDuration() {
                    if (!this.startDate || !this.endDate) return;
                    const start = new Date(this.startDate);
                    const end = new Date(this.endDate);
                    const diffMs = end - start;
                    if (diffMs < 0) {
                        this.duration = '00h:00m'; this.durationMinutes = 0; return;
                    }
                    const minutes = Math.floor(diffMs / 60000);
                    this.durationMinutes = minutes;
                    this.duration = this.minutesToHoursMinutes(minutes);
                },
                validateDurationFormat() {
                    if (/^(\d+)h:(\d{2})m$/.test(this.duration)) {
                        this.durationMinutes = this.hoursMinutesToMinutes(this.duration);
                        // keep string normalized (e.g. 1h:5m -> 01h:05m)
                        this.duration = this.minutesToHoursMinutes(this.durationMinutes);
                    }
                },
                saveMove() {
                    if (!this.duration.match(/^(\d+)h:(\d{2})m$/)) {
                        alert(@json(__('messages.Please enter duration in format (e.g., 01h:30m or 10h:00m)')));
                        return;
                    }
                    if (this.durationMinutes <= 0) {
                        alert(@json(__('messages.Duration must be greater than 0 minutes.')));
                        return;
                    }
                    
                    const formData = {
                        original_task_id: this.originalTimerId, 
                        new_task_id: this.taskId || this.currentTaskId,
                        employee_id: this.employeeId, start_date: this.startDate,
                        end_date: this.endDate,
                        duration: this.durationMinutes,
                        _token: '{{ csrf_token() }}'
                    };

                    let url = '';
                    @if(Auth::user()->hasRole('Admin')) url = '/admin/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet')) url = '/Responsable_de_projet/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Employee')) url = '/employee/tasks/move-duration'; @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) url = '/Responsable_gestionnaire/tasks/move-duration'; @endif

                    $.ajax({
                        url: url,
                        type: 'POST',
                        data: formData,
                        success: (response) => {
                            if (response.status === 'success') location.reload();
                            else alert(@json(__('messages.Failed: ')) + (response.message || @json(__('messages.Unknown error'))));
                        },
                        error: (xhr) => alert(@json(__('messages.Error: ')) + xhr.responseText)
                    });
                    this.show = false;
                },
                minutesToHoursMinutes(minutes) {
                    const hours = Math.floor(minutes / 60);
                    const remainingMinutes = minutes % 60;
                    return `${hours.toString().padStart(2, '0')}h:${remainingMinutes.toString().padStart(2, '0')}m`;
                },
                hoursMinutesToMinutes(durationString) {
                    const matches = durationString.match(/^(\d+)h:(\d{2})m$/);
                    if (!matches) return 0;
                    return (parseInt(matches[1]) * 60) + parseInt(matches[2]);
                }
            }
        }
    </script>
    
    <script src="//unpkg.com/alpinejs" defer></script> 

    <style>
        /* Custom Select Styling */
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
        /* Remove dropdown arrow for multiple selects */
        select.form-select-custom[multiple] {
            background-image: none;
            padding-right: 0.75rem;
        }
        select:invalid { color: #9ca3af; }
        option { color: #111827; }
        /* Multiple select styling */
        select[multiple] {
            overflow-y: auto;
        }
        select[multiple] option {
            padding: 0.45rem 0.6rem;
            margin: 0.125rem 0.25rem;
            border-radius: 0.6rem;
            font-size: 13px;
            color: #334155;
        }
        select[multiple] option:hover {
            background-color: rgba(45, 112, 128, 0.08);
        }
        select[multiple] option:checked {
            background: linear-gradient(#2D7080, #2D7080);
            background-color: #2D7080 !important;
            color: #fff;
        }
        select[multiple]::-webkit-scrollbar {
            width: 8px;
        }
        select[multiple]::-webkit-scrollbar-track {
            background: transparent;
        }
        select[multiple]::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 999px;
        }
        select[multiple]::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        select[multiple]:focus {
            border-color: #2D7080 !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.25) !important;
            outline: none;
        }
        /* Native date inputs: match the custom dropdown buttons */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.55;
            cursor: pointer;
        }
        input[type="date"]:hover::-webkit-calendar-picker-indicator {
            opacity: 0.85;
        }
        /* Green text for projects with tasks in date range */
        #projectFilter option.has-tasks {
            color: #16a34a !important; /* green-600 */
            font-weight: 500;
        }
        /* Task filter specific styling to show full task names */
        #taskFilter {
            min-width: 100%;
            width: 100%;
            border-radius: 0.8rem;
            padding: 0.35rem;
        }
        #taskFilter option {
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        /* Table Highlight Styles */
        .highlighted-task td { background-color: #e6f3ff !important; }
        .regie-task td { background-color: #fff3e6 !important; }
        
        /* Clickable Date Styles */
        td[data-field="start_date"],
        td[data-field="end_date"] {
            color: #2D7080 !important;
            cursor: pointer !important;
            font-weight: 500;
        }
        td[data-field="start_date"]:hover,
        td[data-field="end_date"]:hover {
            color: #0B2638 !important;
            text-decoration: underline;
        }

        /* Editable Comment Styles */
        td.editable-comment {
            cursor: pointer;
            background-color: #f9fafb; /* subtle background */
        }
        td.editable-comment:hover {
            background-color: #eff6ff; /* light blue on hover */
        }
        td.editable-comment .comment-text {
            border-bottom: 1px dashed #9ca3af; /* dashed underline to indicate editability */
        }
        td.editable-comment .comment-text:empty::before {
            content: '{{ __('messages.Double-click to add comment') }}';
            font-size: 0.75rem; /* text-xs */
            color: #9ca3af;     /* gray-400 */
            font-style: italic;
        }

        /* Table styles from Invoice page */
        .table-responsive {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
        /* Sticky header on desktop (thead is hidden on mobile/tablet cards) */
        @media (min-width: 1025px) {
            #taskTable thead th {
                position: sticky;
                top: 0;
                z-index: 5;
                background-color: #f8fafc;
            }
        }
        .table {
            width: 100%;
        }
        .table td, .table th {
            padding: 0.75rem;
        }

        /* Mobile: show task timer rows as modern 2-col cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            .table-responsive {
                overflow-x: visible !important;
            }

            #taskTable thead {
                display: none;
            }

            #taskTable tbody { display: block; }

            #taskTable tbody tr.task-group-row {
                margin: 14px 0 10px;
                border: 0;
                border-radius: 0;
                background: transparent;
                overflow: visible;
            }

            #taskTable tbody tr.task-group-row td {
                padding: 6px 2px;
                border: 0;
                background: transparent;
                text-align: left !important;
            }

            #taskTable tbody tr.task-data-row {
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

            #taskTable tbody tr.task-data-row td {
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

            #taskTable tbody tr.task-data-row td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #taskTable tbody tr.task-data-row td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b;
                margin-bottom: 6px;
            }

            /* Mobile/tablet accordion: collapsed card shows only header */
            #taskTable tbody tr.task-data-row.is-collapsed td {
                display: none !important;
            }
            #taskTable tbody tr.task-data-row.is-collapsed td.task-toggle-cell {
                display: block !important;
                grid-column: span 2 / span 2;
            }
            #taskTable tbody tr.task-data-row td.task-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            #taskTable tbody tr.task-data-row td.task-toggle-cell[data-label]::before {
                content: none !important;
            }
            #taskTable tbody tr.task-data-row td.task-toggle-cell .task-pointage-chevron {
                transition: transform 180ms ease;
            }
            #taskTable tbody tr.task-data-row[aria-expanded="true"] td.task-toggle-cell .task-pointage-chevron {
                transform: rotate(180deg);
            }

            /* Actions inline on mobile */
            #taskTable tbody tr.task-data-row td.task-pointage-actions .flex {
                flex-direction: row !important;
                justify-content: center !important;
                align-items: center !important;
                flex-wrap: nowrap !important;
                gap: 8px !important;
            }
            #taskTable tbody tr.task-data-row td.task-pointage-actions form,
            #taskTable tbody tr.task-data-row td.task-pointage-actions button,
            #taskTable tbody tr.task-data-row td.task-pointage-actions a {
                flex: 0 0 auto;
            }

            /* Date total row as a compact card */
            #taskTable tbody tr.date-total-row {
                display: block;
                margin: 10px 0 18px;
                padding: 12px;
                border: 1px solid rgba(148, 163, 184, 0.55);
                border-radius: 16px;
                background: #ffffff;
                box-shadow: 0 12px 30px rgba(15,23,42,0.10), 0 2px 8px rgba(15,23,42,0.08);
            }
            #taskTable tbody tr.date-total-row td {
                display: block;
                width: 100%;
                padding: 8px 10px;
                border: 0;
                background: transparent;
                text-align: left !important;
                white-space: normal !important;
            }
        }
        
        [x-cloak] { display: none !important; }
    </style>
</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden font-sans" x-data>

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8 space-y-5">

            {{-- 1. Header Section (same design as other Reports pages) --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4">
                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Reports') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">{{ __('messages.Tasks Timer') }}</h1>
                </div>
                <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-center gap-2 w-full sm:w-auto">
                    <button type="button" id="exportBtn" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                        {{ __('messages.Export to Excel') }}
                    </button>
                    <button type="button" id="exportPDFBtn" class="dt-btn dt-btn-primary w-full sm:w-auto">
                        {{ __('messages.Export to PDF') }}
                    </button>
                    <button type="button" id="exportPDFBtnCustomer" class="dt-btn dt-btn-teal w-full sm:w-auto">
                        {{ __('messages.Export to PDF for Customer') }}
                    </button>
                    @if(Auth::user()->hasRole('Admin') && Route::has('admin.pointage.time-transfer.export'))
                    <form method="POST" action="{{ route('admin.pointage.time-transfer.export') }}" id="timeTransferExportForm" class="inline-flex w-full sm:w-auto">
                        @csrf
                        <input type="hidden" name="start_date" id="timeTransferStartDate">
                        <input type="hidden" name="end_date" id="timeTransferEndDate">
                        <input type="hidden" name="project_id" id="timeTransferProjectId">
                        <button type="submit" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m-4-4 4 4 4-4M5 17v3a1 1 0 001 1h12a1 1 0 001-1v-3" /></svg>
                            {{ __('messages.Export time transfer') }}
                        </button>
                    </form>
                    <a href="#" id="timeTransferImportLink" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 16V4m-4 4 4-4 4 4M5 17v3a1 1 0 001 1h12a1 1 0 001-1v-3" /></svg>
                        {{ __('messages.Import time transfer') }}
                    </a>
                    @endif
                </div>
            </div>
            
            {{-- Session Message --}}
            @if(session('alertmessage'))
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                <strong class="text-[13px] font-bold">{{ __('messages.Error!') }}</strong>
                <span class="block sm:inline text-[13px] font-medium">{{ session('alertmessage') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl text-red-600 hover:text-red-800" aria-label="{{ __('messages.Close') }}">&times;</button>
            </div>
            @endif

            {{-- 2. KPI summary (same live values, dashboard stat-card style) --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="dt-card dt-card-hover p-4 flex items-center gap-3.5">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h3m6 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Total Duration:') }}</p>
                        <p class="text-[22px] leading-none font-bold text-[#0B2638] tracking-tight mt-1" id="totalDurationLabel">0h:00m</p>
                    </div>
                </div>
                <div class="dt-card dt-card-hover p-4 flex items-center gap-3.5">
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.598 1M12 8V6m0 10v2m8-8a8 8 0 11-16 0 8 8 0 0116 0z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Total Operating Cost:') }}</p>
                        <p class="text-[22px] leading-none font-bold text-emerald-700 tracking-tight mt-1" id="totalOperatingCostLabel">0.00€</p>
                    </div>
                </div>
                <div class="dt-card dt-card-hover p-4 flex items-center gap-3.5 relative overflow-hidden">
                    <span class="absolute left-0 top-0 h-full w-1 bg-[#E7B84B]"></span>
                    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 6a2 2 0 012-2h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V6zm3 4h4v4H6v-4zm6 0h4v4h-4v-4z" /></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-slate-500 uppercase tracking-[0.07em] truncate">{{ __('messages.Total Sales:') }}</p>
                        <p class="text-[22px] leading-none font-bold text-[#0B2638] tracking-tight mt-1" id="totalSalesLabel">0.00€</p>
                    </div>
                </div>
            </div>

            {{-- 3. Guided controls: period, filters, invoicing in one card --}}
            <div class="dt-card px-5 sm:px-6 py-5">
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-[#0B2638] text-[11px] font-bold text-white">01</span>
                    <h6 class="dt-section-title">{{ __('messages.Fetch Data') }}</h6>
                    <span class="h-px flex-1 bg-slate-100"></span>
                </div>
                @php $dateFormAction = ''; @endphp
                @if(Auth::user()->hasRole('Admin')) @php $dateFormAction = route('admin.pointage.filterPointageByDate'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet')) @php $dateFormAction = route('Responsable_de_projet.pointage.filterPointageByDate'); @endphp @endif
                @if(Auth::user()->hasRole('Employee')) @php $dateFormAction = route('employee.pointage.filterPointageByDate'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $dateFormAction = route('Responsable_gestionnaire.pointage.filterPointageByDate'); @endphp @endif
                
                <form method="GET" action="{{ $dateFormAction }}" id="dateForm" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-3 items-end">
                    @csrf
                    <div>
                        <label for="monthFilterBtn" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Month') }}</label>
                        <div class="relative" id="monthFilterDropdown" data-filter-dropdown="monthFilter">
                            <button type="button" id="monthFilterBtn" aria-haspopup="listbox" aria-expanded="false"
                                class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                <span id="monthFilterBtnText" class="min-w-0 flex-1 truncate text-left">{{ __('messages.Select Month') }}</span>
                                <svg class="h-4 w-4 flex-shrink-0 text-slate-400 transition-transform duration-200" data-chevron fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                            </button>
                            <div id="monthFilterPanel" class="hidden absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl">
                                <div id="monthFilterList" role="listbox" class="max-h-56 overflow-y-auto p-1.5"></div>
                            </div>
                        </div>
                        <select id="monthFilter" name="month" class="hidden" tabindex="-1" aria-hidden="true">
                            <option value="">{{ __('messages.Select Month') }}</option>
                            @for($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label for="startDateInput" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Start Date') }}</label>
                        <input type="date" id="startDateInput" name="start_date" class="block w-full rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                    </div>
                    <div>
                        <label for="endDateInput" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.End Date') }}</label>
                        <input type="date" id="endDateInput" name="end_date" class="block w-full rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="submit"class=" dt-btn-teal px-14 py-1.5 rounded-xl ">{{ __('messages.Get') }}</button>
                        @if(Auth::user()->hasRole('Admin'))
                        <a id="employeesDailyTotalsBtn" class="dt-btn dt-btn-ghost w-full" target="_blank">{{ __('messages.Employees PDF') }}</a>
                        @endif
                    </div>
                </form>

                <div class="my-5 border-t border-slate-100"></div>

                {{-- 4. Filter Form --}}
                <div class="flex items-center gap-3 mb-4">
                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-[#0B2638] text-[11px] font-bold text-white">02</span>
                    <h6 class="dt-section-title">{{ __('messages.Filters') }}</h6>
                    <span class="h-px flex-1 bg-slate-100"></span>
                </div>
                @php $filterFormAction = ''; @endphp
                @if(Auth::user()->hasRole('Admin')) @php $filterFormAction = route('admin.pointage.filterPointageTable'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet')) @php $filterFormAction = route('Responsable_de_projet.pointage.filterPointageTable'); @endphp @endif
                @if(Auth::user()->hasRole('Employee')) @php $filterFormAction = route('employee.pointage.filterPointageTable'); @endphp @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $filterFormAction = route('Responsable_gestionnaire.pointage.filterPointageTable'); @endphp @endif
                
                <form method="GET" action="{{ $filterFormAction }}" id="filterForm" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-3 items-end">
                    @csrf
                       <input type="hidden" id="hiddenStartDateInput" name="start_date">
                       <input type="hidden" id="hiddenEndDateInput" name="end_date">

                       {{-- Filters --}}
                        <div class="lg:col-span-1">
                            <label for="clientFilterBtn" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Client') }}</label>
                            <div class="relative" id="clientFilterDropdown" data-filter-dropdown="clientFilter">
                                <button type="button" id="clientFilterBtn" aria-haspopup="listbox" aria-expanded="false"
                                    class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                    <span id="clientFilterBtnText" class="min-w-0 flex-1 truncate text-left">{{ __('messages.All Clients') }}</span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-slate-400 transition-transform duration-200" data-chevron fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                </button>
                                <div id="clientFilterPanel" class="hidden absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl">
                                    <div class="p-1.5 pb-1">
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-slate-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                            </span>
                                            <input data-filter-search type="text" autocomplete="off" placeholder="{{ __('messages.search_placeholder') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-2 text-[13px] text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#2D7080] focus:bg-white focus:ring-2 focus:ring-[#2D7080]/25">
                                        </div>
                                    </div>
                                    <div id="clientFilterList" role="listbox" class="max-h-56 overflow-y-auto p-1.5 pt-0"></div>
                                    <div id="clientFilterEmpty" class="hidden px-2.5 pb-3 text-center text-xs text-slate-400" data-no-results="{{ __('messages.No results found for') }}"></div>
                                </div>
                            </div>
                            <select id="clientFilter" name="client" class="hidden" tabindex="-1" aria-hidden="true">
                            <option value="">{{ __('messages.All Clients') }}</option>
                                @foreach($clients as $client)
                            <option value="{{ $client->id }}" {{ request('client') == $client->id ? 'selected' : '' }}>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label for="employeeFilterBtn" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Employee') }}</label>
                            <div class="relative" id="employeeFilterDropdown" data-filter-dropdown="employeeFilter">
                                <button type="button" id="employeeFilterBtn" aria-haspopup="listbox" aria-expanded="false"
                                    class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                    <span id="employeeFilterBtnText" class="min-w-0 flex-1 truncate text-left">{{ __('messages.All Employees') }}</span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-slate-400 transition-transform duration-200" data-chevron fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                </button>
                                <div id="employeeFilterPanel" class="hidden absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl">
                                    <div class="p-1.5 pb-1">
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-slate-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                            </span>
                                            <input data-filter-search type="text" autocomplete="off" placeholder="{{ __('messages.search_placeholder') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-2 text-[13px] text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#2D7080] focus:bg-white focus:ring-2 focus:ring-[#2D7080]/25">
                                        </div>
                                    </div>
                                    <div id="employeeFilterList" role="listbox" class="max-h-56 overflow-y-auto p-1.5 pt-0"></div>
                                    <div id="employeeFilterEmpty" class="hidden px-2.5 pb-3 text-center text-xs text-slate-400" data-no-results="{{ __('messages.No results found for') }}"></div>
                                </div>
                            </div>
                                <select id="employeeFilter" name="employee" class="hidden" tabindex="-1" aria-hidden="true">
                                <option value="">{{ __('messages.All Employees') }}</option>
                                    @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ request('employee') == $employee->id ? 'selected' : '' }}>{{ $employee->name }}</option>
                                    @endforeach
                                </select>
                        </div>

                        <div class="lg:col-span-1">
                            <label for="projectFilterBtn" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Project') }}</label>
                            <div class="relative" id="projectFilterDropdown" data-filter-dropdown="projectFilter">
                                <button type="button" id="projectFilterBtn" aria-haspopup="listbox" aria-expanded="false"
                                    class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                    <span id="projectFilterBtnText" class="min-w-0 flex-1 truncate text-left">{{ __('messages.All Projects') }}</span>
                                    <svg class="h-4 w-4 flex-shrink-0 text-slate-400 transition-transform duration-200" data-chevron fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                </button>
                                <div id="projectFilterPanel" class="hidden absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl">
                                    <div class="p-1.5 pb-1">
                                        <div class="relative">
                                            <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-slate-400">
                                                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                            </span>
                                            <input data-filter-search type="text" autocomplete="off" placeholder="{{ __('messages.search_placeholder') }}"
                                                class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-2 text-[13px] text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#2D7080] focus:bg-white focus:ring-2 focus:ring-[#2D7080]/25">
                                        </div>
                                    </div>
                                    <div id="projectFilterList" role="listbox" class="max-h-56 overflow-y-auto p-1.5 pt-0"></div>
                                    <div id="projectFilterEmpty" class="hidden px-2.5 pb-3 text-center text-xs text-slate-400" data-no-results="{{ __('messages.No results found for') }}"></div>
                                </div>
                            </div>
                                <select id="projectFilter" name="project" class="hidden" tabindex="-1" aria-hidden="true">
                                    <option value="">{{ __('messages.All Projects') }}</option>
                                        @foreach($projects as $project)
                                        @php
                                            $hasTasksInRange = isset($projectsWithTasks) && in_array($project->id, $projectsWithTasks);
                                            $hasExactInvoice = isset($projectsWithExactInvoices) && in_array($project->id, $projectsWithExactInvoices);
                                        @endphp
                                        <option
                                            value="{{ $project->id }}"
                                            {{ request('project') == $project->id ? 'selected' : '' }}
                                            data-tva="{{ $project->tva ?? 0 }}"
                                            @if($hasTasksInRange && !$hasExactInvoice)
                                                class="has-tasks" style="color: #16a34a; font-weight: 500;"
                                            @endif
                                        >
                                            {{ strtoupper($project->name) }}
                                            @if($hasExactInvoice)
                                                (INVOICED)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
    
                            <div class="lg:col-span-2">
                                <label for="taskFilterBtn" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Task Filter Task') }}</label>
                                <div class="relative" id="taskFilterDropdown">
                                    <button type="button" id="taskFilterBtn" aria-haspopup="listbox" aria-expanded="false"
                                        class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                        <span id="taskFilterBtnText" class="min-w-0 flex-1 truncate text-left">{{ __('messages.All Tasks option') }}</span>
                                        <svg id="taskFilterChevron" class="h-4 w-4 flex-shrink-0 text-slate-400 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                    </button>
                                    <div id="taskFilterPanel" class="hidden absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl">
                                        <div class="p-1.5 pb-1">
                                            <div class="relative">
                                                <span class="pointer-events-none absolute inset-y-0 left-2.5 flex items-center text-slate-400">
                                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" /></svg>
                                                </span>
                                                <input id="taskFilterSearch" type="text" autocomplete="off" placeholder="{{ __('messages.search_placeholder') }}"
                                                    class="w-full rounded-lg border border-slate-200 bg-slate-50 py-1.5 pl-8 pr-2 text-[13px] text-slate-700 outline-none transition placeholder:text-slate-400 focus:border-[#2D7080] focus:bg-white focus:ring-2 focus:ring-[#2D7080]/25">
                                            </div>
                                        </div>
                                        <div id="taskFilterList" role="listbox" aria-multiselectable="true" class="max-h-56 overflow-y-auto p-1.5 pt-0"></div>
                                        <div id="taskFilterEmpty" class="hidden px-2.5 pb-3 text-center text-xs text-slate-400" data-no-results="{{ __('messages.No results found for') }}"></div>
                                    </div>
                                </div>
                                <select id="taskFilter" name="task[]" multiple class="hidden" tabindex="-1" aria-hidden="true">
                                    <option value="all" {{ in_array('all', (array)request('task', [])) || empty(request('task')) ? 'selected' : '' }}>{{ __('messages.All Tasks option') }}</option>
                                    @foreach($allTasks as $task)
                                    <option value="{{ $task->id }}" {{ in_array($task->id, (array)request('task', [])) ? 'selected' : '' }}>
                                        {{ $task->title }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="lg:col-span-1">
                                <label for="projectTypeFilterBtn" class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Type Filter') }}</label>
                                <div class="relative" id="projectTypeFilterDropdown" data-filter-dropdown="projectTypeFilter">
                                    <button type="button" id="projectTypeFilterBtn" aria-haspopup="listbox" aria-expanded="false"
                                        class="flex w-full items-center justify-between gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm outline-none transition hover:border-slate-300 focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25">
                                        <span id="projectTypeFilterBtnText" class="min-w-0 flex-1 truncate text-left">{{ __('messages.All Types') }}</span>
                                        <svg class="h-4 w-4 flex-shrink-0 text-slate-400 transition-transform duration-200" data-chevron fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
                                    </button>
                                    <div id="projectTypeFilterPanel" class="hidden absolute z-30 mt-2 w-full rounded-xl border border-slate-200 bg-white shadow-xl">
                                        <div id="projectTypeFilterList" role="listbox" class="max-h-56 overflow-y-auto p-1.5"></div>
                                    </div>
                                </div>
                                    <select id="projectTypeFilter" name="project_type" class="hidden" tabindex="-1" aria-hidden="true">
                                    <option value="">{{ __('messages.All Types') }}</option>
                                    <option value="regie" {{ request('project_type') == 'regie' ? 'selected' : '' }}>{{ __('messages.Regie') }}</option>
                                    <option value="devis" {{ request('project_type') == 'devis' ? 'selected' : '' }}>{{ __('messages.Devis type') }}</option>
                                </select>
                            </div>

                            {{-- Filter Button --}}
                            <div class="lg:col-span-1 flex items-end">
                                <button type="submit" id="applyFiltersBtn" class="dt-btn dt-btn-teal w-full">{{ __('messages.Apply Filters') }}</button>
                            </div>

                            {{-- Comment and Button Row --}}
                            <div class="lg:col-span-7 mt-2 border-t border-slate-100 pt-5">
                                <div class="flex items-center gap-3 mb-4">
                                    <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-[#E7B84B] text-[11px] font-bold text-[#0B2638]">03</span>
                                    <h6 class="dt-section-title">{{ __('messages.Invoice Comment') }}</h6>
                                    <span class="h-px flex-1 bg-slate-100"></span>
                                </div>
                                <div class="grid grid-cols-1 lg:grid-cols-7 gap-3 items-end">
                                    <div class="lg:col-span-3">
                                        <textarea rows="3" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm" id="periodInvoiceComment" placeholder="{{ __('messages.Optional comment for the period invoice...') }}"></textarea>
                                    </div>
                                    <div class="">
                                        <button type="button" id="generatePeriodInvoiceBtn" class="dt-btn dt-btn-teal w-full">{{ __('messages.Generate Period Invoice') }}</button>
                                    </div>
                                </div>
                            </div>
                </form>
            </div>

            {{-- 5. Table Section --}}
            <div class="dt-card overflow-hidden">
                <div class="table-responsive">
                    <table id="taskTable" class="w-full divide-y divide-slate-100 table table-auto">
                        <thead class="bg-slate-50/80">
                            <tr class="dt-table-head">
                                <th class="px-4 py-3 text-left whitespace-nowrap min-w-[170px]">{{ __('messages.Project') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap min-w-[190px]">{{ __('messages.Task Filter Task') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap min-w-[150px]">{{ __('messages.Employee') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Start Date') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.End Date') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Duration') }}</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('messages.Op. Cost') }}</th>
                                <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('messages.Sales column') }}</th>
                                <th class="px-4 py-3 text-left whitespace-nowrap min-w-[180px]">
                                    {{ __('messages.Comment column') }}
                                    <span class="block text-[10px] font-normal text-slate-400 normal-case tracking-normal">{{ __('messages.Double-click to add comment') }}</span>
                                </th>
                                <th class="px-4 py-3 text-center whitespace-nowrap min-w-[130px]">{{ __('messages.Actions') }}</th>
                            </tr>
                        </thead>

                        <tbody id="taskTableBody" class="bg-white divide-y divide-slate-100">
                            @php
                                function formatDuration($minutes) {
                                    $hours = floor($minutes / 60);
                                    $minutes = $minutes % 60;
                                    $seconds = 0; 
                                    return sprintf('%02dh:%02dm:%02ds', $hours, $minutes, $seconds);
                                }
                            @endphp
                            
                            @foreach($tasks_timer as $start_date => $tasks)
                                @php
                                    try {
                                        if (is_string($start_date) && strpos($start_date, ' - ') !== false) {
                                            list($dayName, $datePart) = explode(' - ', $start_date);
                                            $dateObject = \Carbon\Carbon::createFromFormat('Y-m-d', $datePart);
                                            $displayDate = $dayName . ' - ' . $dateObject->format('d-m-Y');
                                        } else {
                                            $dateObject = is_string($start_date) 
                                                ? \Carbon\Carbon::createFromFormat('Y-m-d', $start_date)
                                                : $start_date;
                                            $displayDate = $dateObject->format('l - d-m-Y');
                                        }
                                        $groupTotal = 0;
                                        $groupTotalRecette = 0;
                                        $groupTotalDuration = 0;
                                    } catch (\Exception $e) {
                                        Log::error("Invalid date format: " . $start_date . ". Error: " . $e->getMessage());
                                        continue;
                                    }
                                @endphp

                                @if($dateObject)
                                <tr class="task-group-row">
                                    <td colspan="10" class="px-4 py-2 bg-[#0B2638]/[0.05] text-[#0B2638] font-bold text-[13px] border-y border-[#0B2638]/10">
                                        <strong>{{ $displayDate }}</strong>
                                    </td>
                                </tr>
                                @endif
                            
                                @foreach($tasks as $task)
                                    @php 
                                        // Use cached relationships if present; fall back to DB lookups safely
                                        $taskModel = App\Models\Tasks::find($task->task_id);
                                        $task_project_id = $task->project_id ?? optional($taskModel)->project_id;
                                        $project = $task_project_id ? App\Models\Project::find($task_project_id) : null;
                                        $employee = App\Models\Employees::find($task->employee_id);

                                        // Skip rendering if any critical relation is missing to avoid runtime errors
                                        if (!$taskModel || !$project || !$employee) {
                                            continue;
                                        }
                                        $groupTotalDuration += $task->duration;
                                        $groupTotal += $task->end_date ? $task->cost : 0;
                                        $groupTotalRecette += $task->end_date ? (($task->duration / 60) * $project->cost_per_hour) : 0;
                                        
                                        $hasInvoice = in_array($task_project_id, $periodInvoices);
                                        $isRegie = $project->regie === 1;
                                    @endphp
                                
                                    <tr class="task-data-row {{ $hasInvoice ? 'highlighted-task' : '' }} {{ $isRegie ? 'regie-task' : '' }} hover:bg-slate-50/70 transition-colors"
                                        id="{{ $task->id }}"
                                        data-employee-id="{{ $task->employee_id }}"
                                        data-project-id="{{ $task_project_id}}"
                                        data-task-id="{{ $task->task_id }}"
                                        data-task-row
                                        aria-expanded="true">

                                        <td data-label="{{ __('messages.Project') }}" data-span="2" class="px-4 py-3 text-[13px] font-medium text-[#0B2638] whitespace-normal break-words task-toggle-cell" data-task-toggle>
                                            {{-- Desktop: keep original (project link only) --}}
                                            <div class="hidden lg:block">
                                                @if(Auth::user()->can('View project'))
                                                    @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.projects.show',$task_project_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $project->name }}</a> @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.projects.show',$task_project_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $project->name }}</a> @endif
                                                    @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.projects.show',$task_project_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $project->name }}</a> @endif
                                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.projects.show',$task_project_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $project->name }}</a> @endif
                                                @else
                                                    {{ $project->name }}
                                                @endif
                                            </div>

                                            {{-- Mobile/tablet: accordion header (project + task + chevron) --}}
                                            <div class="flex items-start justify-between gap-3 lg:hidden">
                                                <div class="min-w-0">
                                                    <div class="text-[11px] font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                                        {{ __('messages.Project') }}
                                                    </div>
                                                    <div class="min-w-0 truncate font-extrabold text-slate-900">
                                                        {{ $project->name }}
                                                    </div>
                                                    <div class="mt-1 text-[11px] font-bold tracking-[.06em] uppercase text-slate-500 whitespace-nowrap">
                                                        {{ __('messages.Task Filter Task') }}
                                                    </div>
                                                    <div class="min-w-0 truncate text-sm text-slate-900">
                                                        {{ $task->task_name }}
                                                    </div>
                                                </div>
                                                <span class="shrink-0 mt-1 text-slate-400" aria-hidden="true">
                                                    <svg class="task-pointage-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                                        <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                    </svg>
                                                </span>
                                            </div>
                                        </td>
                                        <td data-label="{{ __('messages.Task Filter Task') }}" data-span="2" class="px-4 py-3 text-[13px] font-medium text-[#0B2638] whitespace-normal break-words">
                                            @if(auth()->user()->hasRole('Admin')) <a href="{{route('admin.tasks.show', $task->task_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline" @click.stop title="{{ $task->task_name }}">{{ $task->task_name }}</a> @endif
                                            @if(auth()->user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.tasks.show', $task->task_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline" @click.stop title="{{ $task->task_name }}">{{ $task->task_name }}</a> @endif
                                            @if(auth()->user()->hasRole('Employee')) <a href="{{route('employee.tasks.show', $task->task_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline" @click.stop title="{{ $task->task_name }}">{{ $task->task_name }}</a> @endif
                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.tasks.show', $task->task_id)}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline" @click.stop title="{{ $task->task_name }}">{{ $task->task_name }}</a> @endif
                                        </td>
                                        <td data-label="{{ __('messages.Employee') }}" class="px-4 py-3 text-[13px] text-slate-600 whitespace-normal break-words">
                                            @if(Auth::user()->can('View employee'))
                                                @if(Auth::user()->hasRole('Admin')) <a href="{{route('admin.employees.show',$employee->id )}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $employee->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Responsable de projet')) <a href="{{route('Responsable_de_projet.employees.show',$employee->id )}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $employee->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Employee')) <a href="{{route('employee.employees.show',$employee->id )}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $employee->name }}</a> @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) <a href="{{route('Responsable_gestionnaire.employees.show',$employee->id )}}" class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium" @click.stop>{{ $employee->name }}</a> @endif
                                            @else
                                                {{ $employee->name }}
                                            @endif
                                        </td>
                                        
                                        <td data-label="{{ __('messages.Start Date') }}" class="px-4 py-3 whitespace-nowrap text-[13px] text-slate-600" data-field="start_date"
                                            @click.prevent.stop="$dispatch('open-edit-date-modal', { 
                                                taskId: {{ $task->id }}, 
                                                field: 'start_date', 
                                                datetime: '{{ \Carbon\Carbon::parse($task->start_date)->format('Y-m-d\TH:i') }}' 
                                            })">
                                            {{ \Carbon\Carbon::parse($task->start_date)->format('d-m-Y H:i:s') }}
                                        </td>
                                        <td data-label="{{ __('messages.End Date') }}" class="px-4 py-3 whitespace-nowrap text-[13px] text-slate-600" data-field="end_date"
                                            @click.prevent.stop="$dispatch('open-edit-date-modal', { 
                                                taskId: {{ $task->id }}, 
                                                field: 'end_date', 
                                                datetime: '{{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('Y-m-d\TH:i') : '' }}' 
                                            })">
                                            {{ $task->end_date ? \Carbon\Carbon::parse($task->end_date)->format('d-m-Y H:i:s') : 'Still Working' }}
                                        </td>
                                        
                                        <td data-label="{{ __('messages.Duration') }}" id="duration-{{ $task->id }}" class="px-4 py-3 whitespace-nowrap text-[13px] font-semibold text-[#0B2638]" data-task-id="{{ $task->id }}">
                                            @if (is_null($task->end_date))
                                                <script>
                                                    document.addEventListener('DOMContentLoaded', function () {
                                                        function startTimer(taskId, startDate) {
                                                            const durationCellId = 'duration-' + taskId;
                                                            var intervalId = setInterval(function() {
                                                                var durationCell = document.getElementById(durationCellId);
                                                                if (durationCell) {
                                                                    var durationInMilliseconds = new Date() - new Date(startDate);
                                                                    var hours = Math.floor(durationInMilliseconds / 3600000);
                                                                    var minutes = Math.floor((durationInMilliseconds % 3600000) / 60000);
                                                                    var seconds = Math.floor((durationInMilliseconds % 60000) / 1000);
                                                                    var durationFormatted = (hours < 10 ? '0' : '') + hours + 'h:' +
                                                                        (minutes < 10 ? '0' : '') + minutes + 'm:' +
                                                                        (seconds < 10 ? '0' : '') + seconds + 's';
                                                                    durationCell.textContent = durationFormatted;
                                                                } else {
                                                                    clearInterval(intervalId);
                                                                }
                                                            }, 1000);
                                                        }
                                                        startTimer('{{ $task->id }}', '{{ $task->start_date }}');
                                                    });
                                                </script>
                                            @else
                                                {{ formatDuration($task->duration) }}
                                            @endif
                                        </td>
                                        <td data-label="{{ __('messages.Op. Cost') }}" class="px-4 py-3 whitespace-nowrap text-[13px] font-semibold text-emerald-700 text-right">{{ $task->end_date ? number_format((float)$task->cost, 3, '.', '') : '0.00' }}€</td>
                                        <td data-label="{{ __('messages.Sales column') }}" class="px-4 py-3 whitespace-nowrap text-[13px] font-semibold text-[#0B2638] text-right">{{ $task->end_date ? number_format((($task->duration / 60) * $project->cost_per_hour), 3, '.', '') : '0.00' }}€</td>

                                        <td data-label="{{ __('messages.Comment column') }}" data-span="2" class="px-4 py-3 text-[13px] text-slate-600 whitespace-normal break-words editable-comment"
                                            data-id="{{ $task->id }}" 
                                            data-field="comment" 
                                            data-update-url="{{ route('admin.tasks.updateField', ['field' => 'comment', 'id' => $task->id]) }}">
                                            <span class="comment-text">{{ $task->comment }}</span>
                                        </td>
                                        
                                        <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-4 py-3 task-pointage-actions">
                                            <div class="flex flex-col items-stretch gap-2">
                                                @if($task->end_date)
                                                <button type="button" class="dt-btn dt-btn-ghost !text-[11px] !py-1.5 w-full"
                                                    @click.prevent.stop="$dispatch('open-move-modal', {
                                                        timerId: {{$task->id}}, 
                                                        taskId: {{$task->task_id}}, 
                                                        projectId: {{$task_project_id}}, 
                                                        employeeId: {{$task->employee_id}}, 
                                                        startDate: '{{\Carbon\Carbon::parse($task->start_date)->format('Y-m-d\TH:i')}}', 
                                                        endDate: '{{\Carbon\Carbon::parse($task->end_date)->format('Y-m-d\TH:i')}}', 
                                                        duration: {{$task->duration}}
                                                    })">
                                                    {{ __('messages.Move Duration') }}
                                                </button>
                                                @endif
                                            </div>
                                            <div class="flex flex-col items-stretch gap-2">
                                                @if(Auth::user()->hasRole('Admin'))
                                                    <form action="{{ route('admin.tasks.timer.destroy', $task->id) }}" method="POST" onsubmit="return confirm(@js(__('messages.Are you sure you want to delete this timer entry?')));">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="inline-flex items-center justify-center gap-1.5 w-full rounded-lg border border-red-200 bg-white px-2 py-1.5 text-[11px] font-semibold text-red-600 hover:bg-red-50 transition-colors">
                                                            {{ __('messages.Delete') }}
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                                
                                <tr class="bg-slate-50 font-bold date-total-row border-t border-slate-200">
                                    <td colspan="5" class="px-4 py-3 text-[13px] text-[#0B2638] text-right">{{ __('messages.Date Total:') }}</td>
                                    <td class="px-4 py-3 text-[13px] text-[#0B2638] text-left">{{ formatDuration($groupTotalDuration) }}</td>
                                    <td class="px-4 py-3 text-[13px] text-emerald-700 text-right">{{ number_format($groupTotal, 3, ',', '') }}€</td>
                                    <td class="px-4 py-3 text-[13px] text-[#0B2638] text-right">{{ number_format($groupTotalRecette, 3, ',', '') }}€</td>
                                    <td colspan="2"></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
        </main>
    </div>

    {{-- 
    =============================================
    ALPINE.JS MODALS (Replacing Bootstrap)
    =============================================
    --}}

    {{-- 1. Edit Date/Time Modal --}}
    <div x-data="editDateModal()" x-cloak x-show="show" 
         @open-edit-date-modal.window="open($event.detail)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B2638]/50 backdrop-blur-[2px]">
        <div @click.outside="show = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="dt-section-title">{{ __('messages.Edit Date and Time') }}</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="dateInput" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Date label') }}</label>
                    <input type="date" x-model="date" id="dateInput" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
                <div>
                    <label for="timeInput" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Time (HH:MM)') }}</label>
                    <input type="time" x-model="time" id="timeInput" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
            </div>
            <div class="flex items-center justify-end p-4 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl space-x-2">
                <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close button') }}</button>
                <button @click="save()" type="button" class="dt-btn dt-btn-teal">{{ __('messages.Save changes button') }}</button>
            </div>
        </div>
    </div>

    {{-- 2. Move Duration Modal --}}
    <div x-data="moveDurationModal()" x-cloak x-show="show"
         @open-move-modal.window="open($event.detail)"
         class="fixed inset-0 z-50 flex items-center justify-center bg-[#0B2638]/50 backdrop-blur-[2px]">
        <div @click.outside="show = false" class="bg-white rounded-2xl shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-4 border-b">
                <h5 class="dt-section-title">{{ __('messages.Move Duration') }}</h5>
                <button @click="show = false" type="button" class="text-gray-400 hover:text-gray-600">&times;</button>
            </div>
            <div class="p-6 space-y-4">
                <div>
                    <label for="projectSelect" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Project') }}</label>
                    <select x-model="projectId" @change="loadTasks()" id="projectSelect" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3 form-select-custom">
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="taskSelect" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Task Filter Task') }}</label>
                    <select x-model="taskId" id="taskSelect" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3 form-select-custom">
                        </select>
                </div>
                <div>
                    <label for="employeeSelect" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Employee') }}</label>
                    <select x-model="employeeId" id="employeeSelect" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3 form-select-custom">
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="moveStartDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Start Date') }}</label>
                    <input type="datetime-local" x-model="startDate" @input="calculateDuration()" id="moveStartDate" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveEndDate" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.End Date') }}</label>
                    <input type="datetime-local" x-model="endDate" @input="calculateDuration()" id="moveEndDate" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                </div>
                <div>
                    <label for="moveDuration" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Duration (HH:MM)') }}</label>
                    <input type="text" x-model="duration" id="moveDuration" @input="validateDurationFormat()"
                           class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                           placeholder="{{ __('messages.Duration placeholder') }}" pattern="^(\d+)h:(\d{2})m$">
                </div>
            </div>
            <div class="flex items-center justify-end p-4 bg-slate-50/70 border-t border-slate-100 rounded-b-2xl space-x-2">
                <button @click="show = false" type="button" class="dt-btn dt-btn-ghost">{{ __('messages.Close') }}</button>
                <button @click="saveMove()" type="button" class="dt-btn dt-btn-teal">{{ __('messages.Save changes button') }}</button>
            </div>
        </div>
    </div>
    



    {{-- SCRIPTS --}}
    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.3.1/jspdf.umd.min.js"></script>
    <script src="https://unpkg.com/jspdf-autotable@3.5.17/dist/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- Main Page Logic --}}
    <script>
        // --- PAGE SCRIPT LOGIC ---
        document.addEventListener('DOMContentLoaded', function() {
            
            // --- Ensure all links in the table work correctly ---
            // This is a safety net to catch any invalid navigation attempts
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (link && link.closest('#taskTable')) {
                    const href = link.getAttribute('href');
                    // If href is invalid or contains [object, prevent navigation
                    if (!href || href === '#' || href.includes('[object')) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        console.error('Blocked invalid navigation attempt. Link:', link, 'href:', href);
                        // Try to get a valid href from the link's text or data attributes
                        const validHref = link.href || link.getAttribute('data-href');
                        if (validHref && !validHref.includes('[object')) {
                            window.location.href = validHref;
                        }
                        return false;
                    }
                }
            }, true); // Use capture phase to catch early

            // --- Mobile/tablet accordion for task cards ---
            (function initTaskPointageAccordions() {
                const mql = window.matchMedia('(max-width: 1024px)');

                function setRowCollapsed(row, collapsed) {
                    if (!row) return;
                    row.classList.toggle('is-collapsed', !!collapsed);
                    row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                }

                function initForViewport() {
                    const isCompact = mql.matches;
                    const rows = Array.from(document.querySelectorAll('#taskTableBody tr.task-data-row[data-task-row]'));

                    for (const row of rows) {
                        const toggleCell = row.querySelector('td.task-toggle-cell[data-task-toggle]');
                        if (!toggleCell) continue;

                        if (!toggleCell.dataset.pointageToggleBound) {
                            toggleCell.dataset.pointageToggleBound = '1';
                            toggleCell.addEventListener('click', (e) => {
                                const tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
                                if (tag === 'a' || tag === 'button' || tag === 'input' || tag === 'select' || tag === 'textarea' || tag === 'label') return;

                                e.preventDefault();
                                e.stopPropagation();

                                const isCollapsed = row.classList.contains('is-collapsed');
                                setRowCollapsed(row, !isCollapsed);
                            });
                        }

                        setRowCollapsed(row, isCompact);
                    }
                }

                initForViewport();
                if (mql.addEventListener) mql.addEventListener('change', initForViewport);
                else mql.addListener(initForViewport);
            })();
            
            // --- NO AUTO-SUBMIT - Users must click "Apply Filters" button to submit ---
            // Handle "All Tasks" option - if "all" is selected, deselect all other tasks
            const taskFilter = document.getElementById('taskFilter');
            if (taskFilter) {
                taskFilter.addEventListener('change', function(e) {
                    const selectedOptions = Array.from(this.selectedOptions);
                    const hasAllOption = selectedOptions.some(opt => opt.value === 'all');
                    
                    if (hasAllOption) {
                        // If "All Tasks" is selected, deselect everything else
                        Array.from(this.options).forEach(option => {
                            if (option.value !== 'all') {
                                option.selected = false;
                            }
                        });
                    } else {
                        // If a specific task is selected, deselect "All Tasks"
                        const allOption = Array.from(this.options).find(opt => opt.value === 'all');
                        if (allOption) {
                            allOption.selected = false;
                        }
                    }
                });
            }

            // --- Comment editing ---
            document.querySelectorAll('.editable-comment').forEach(function(cell) {
                cell.addEventListener('dblclick', function() {
                    var span = cell.querySelector('.comment-text');
                    if (!span) return;
                    var originalText = span.textContent || '';
                    if (cell.getAttribute('data-editing') === '1') return;
                    cell.setAttribute('data-editing', '1');
                    var input = document.createElement('textarea');
                    input.className = 'block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-1 px-2';
                    input.value = originalText;
                    input.rows = 2;
                    span.style.display = 'none';
                    cell.appendChild(input);
                    input.focus();
                    input.select();
                    var save = function(newValue) {
                        var trimmed = (newValue || '').trim();
                        if (trimmed === (originalText || '').trim()) { cancel(); return; }
                        var url = cell.getAttribute('data-update-url');
                        var field = cell.getAttribute('data-field') || 'comment';
                        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        fetch(url, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': token, 'Content-Type': 'application/x-www-form-urlencoded' },
                            body: `${field}=${encodeURIComponent(trimmed)}`
                        }).then(resp => resp.json())
                        .then(json => {
                            if (json.status === 'success') span.textContent = trimmed;
                            else { span.textContent = originalText; alert(json.message || 'Failed to update'); }
                        }).catch(err => { span.textContent = originalText; alert(@js(__('messages.Network error'))); })
                        .finally(() => cleanup());
                    };
                    var cancel = () => cleanup();
                    var cleanup = () => {
                        if (input && input.parentNode) input.parentNode.removeChild(input);
                        span.style.display = '';
                        cell.removeAttribute('data-editing');
                    };
                    input.addEventListener('keydown', (e) => {
                        if (e.key === 'Enter' && (e.ctrlKey || e.metaKey)) { e.preventDefault(); save(input.value); }
                        else if (e.key === 'Escape') { e.preventDefault(); cancel(); }
                    });
                    input.addEventListener('blur', () => save(input.value));
                });
            });

            // Employees Daily Totals PDF Button
            var btn = document.getElementById('employeesDailyTotalsBtn');
            if (btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    var startDate = document.getElementById('startDateInput').value;
                    var endDate = document.getElementById('endDateInput').value;
                    if(!startDate || !endDate){ alert(@js(__('messages.Please pick start and end dates first.'))); return; }
                    var url = "{{ route('admin.Pointagerepports.employeesDailyTotals', ['startDate' => 'START', 'endDate' => 'END']) }}";
                    url = url.replace('START', startDate).replace('END', endDate);
                    window.open(url, '_blank');
                });
            }
            
            // Excel Export Button
            document.getElementById('exportBtn').addEventListener('click', function() {
                var table = document.getElementById('taskTable');
                var wb = XLSX.utils.table_to_book(table, { sheet: 'Sheet1' });
                var startDate = document.getElementById('startDateInput').value;
                var endDate = document.getElementById('endDateInput').value;
                var fileName = `${startDate || 'Report'}_to_${endDate || 'Today'}_TasksInfo.xlsx`;
                XLSX.writeFile(wb, fileName);
            });

            // Date Form Local Storage
            // Check URL parameters first, then localStorage
            const urlParams = new URLSearchParams(window.location.search);
            var startDate = urlParams.get('start_date') || localStorage.getItem('startDate');
            var endDate = urlParams.get('end_date') || localStorage.getItem('endDate');
            var selectedMonth = localStorage.getItem('selectedMonth');
            if (startDate) document.getElementById('startDateInput').value = startDate;
            if (endDate) document.getElementById('endDateInput').value = endDate;
            if (selectedMonth) document.getElementById('monthFilter').value = selectedMonth;

            document.getElementById('monthFilter').addEventListener('change', function() {
                const selectedMonth = this.value;
                if (selectedMonth) {
                    const currentYear = new Date().getFullYear();
                    const firstDay = new Date(currentYear, selectedMonth - 1, 1);
                    const lastDay = new Date(currentYear, selectedMonth, 0);
                    const formatDate = (date) => {
                        const year = date.getFullYear();
                        const month = String(date.getMonth() + 1).padStart(2, '0');
                        const day = String(date.getDate()).padStart(2, '0');
                        return `${year}-${month}-${day}`;
                    };
                    document.getElementById('startDateInput').value = formatDate(firstDay);
                    document.getElementById('endDateInput').value = formatDate(lastDay);
                }
            });

            document.getElementById('dateForm').addEventListener('submit', function() {
                localStorage.setItem('startDate', document.getElementById('startDateInput').value);
                localStorage.setItem('endDate', document.getElementById('endDateInput').value);
                localStorage.setItem('selectedMonth', document.getElementById('monthFilter').value);
            });
            
            // Filter Form Local Storage
            document.getElementById('hiddenStartDateInput').value = startDate;
            document.getElementById('hiddenEndDateInput').value = endDate;

            document.getElementById('filterForm').addEventListener('submit', function(event) {
                localStorage.setItem('employeeFilter', document.getElementById('employeeFilter').value);
                localStorage.setItem('projectFilter', document.getElementById('projectFilter').value);
                // Store multiple selected tasks as JSON array
                const selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions).map(opt => opt.value);
                localStorage.setItem('taskFilter', JSON.stringify(selectedTasks));
                localStorage.setItem('projectTypeFilter', document.getElementById('projectTypeFilter').value);
            });
            
            function setSelectValueIfOptionExists(selectId, value) {
                if (!value) return;
                const select = document.getElementById(selectId);
                if (!select) return;
                const exists = Array.from(select.options).some(opt => opt.value === String(value));
                if (exists) {
                    select.value = String(value);
                }
            }

            function getActiveProjectId() {
                const select = document.getElementById('projectFilter');
                if (!select) return '';

                const fromSelect = select.value || '';
                if (fromSelect) return fromSelect;

                const fromUrl = new URLSearchParams(window.location.search).get('project');
                if (fromUrl && Array.from(select.options).some(opt => opt.value === fromUrl)) {
                    return fromUrl;
                }

                if (select.selectedIndex > 0) {
                    return select.options[select.selectedIndex].value || '';
                }

                return '';
            }

            function restoreFilterValues() {
                const urlParams = new URLSearchParams(window.location.search);
                const employeeValue = urlParams.get('employee') || localStorage.getItem('employeeFilter');
                const projectValue = urlParams.get('project') || localStorage.getItem('projectFilter');
                
                // Handle multiple task selections from URL params
                const taskParams = urlParams.getAll('task[]');
                let taskValues = taskParams.length > 0 ? taskParams : null;
                if (!taskValues) {
                    const storedTasks = localStorage.getItem('taskFilter');
                    if (storedTasks) {
                        try {
                            taskValues = JSON.parse(storedTasks);
                        } catch (e) {
                            // Fallback for old single value format
                            taskValues = storedTasks ? [storedTasks] : null;
                        }
                    }
                }
                
                const projectTypeValue = urlParams.get('project_type') || localStorage.getItem('projectTypeFilter');

                setSelectValueIfOptionExists('employeeFilter', employeeValue);
                setSelectValueIfOptionExists('projectFilter', projectValue);
                
                // Restore multiple task selections
                const taskFilter = document.getElementById('taskFilter');
                if (taskValues && taskValues.length > 0) {
                    Array.from(taskFilter.options).forEach(option => {
                        option.selected = taskValues.includes(option.value);
                    });
                } else {
                    // If no tasks selected, default to "All Tasks"
                    const allOption = Array.from(taskFilter.options).find(opt => opt.value === 'all');
                    if (allOption) {
                        allOption.selected = true;
                    }
                }
                
                if (projectTypeValue) document.getElementById('projectTypeFilter').value = projectTypeValue;

                syncTimeTransferFilters();
            }
            // Time transfer export/import (uses current date + project filters)
            const timeTransferExportForm = document.getElementById('timeTransferExportForm');
            const timeTransferImportLink = document.getElementById('timeTransferImportLink');
            const importBaseUrl = @json(url('/admin/projects'));

            function syncTimeTransferFilters() {
                const start = document.getElementById('startDateInput')?.value || '';
                const end = document.getElementById('endDateInput')?.value || '';
                const project = getActiveProjectId();

                if (document.getElementById('timeTransferStartDate')) {
                    document.getElementById('timeTransferStartDate').value = start;
                }
                if (document.getElementById('timeTransferEndDate')) {
                    document.getElementById('timeTransferEndDate').value = end;
                }
                if (document.getElementById('timeTransferProjectId')) {
                    document.getElementById('timeTransferProjectId').value = project;
                }
                if (timeTransferImportLink) {
                    timeTransferImportLink.href = project
                        ? `${importBaseUrl}/${project}/time-transfer/import`
                        : '#';
                    timeTransferImportLink.classList.toggle('opacity-50', !project);
                    timeTransferImportLink.classList.toggle('pointer-events-none', !project);
                }
            }

            restoreFilterValues();
            document.getElementById('startDateInput')?.addEventListener('change', syncTimeTransferFilters);
            document.getElementById('endDateInput')?.addEventListener('change', syncTimeTransferFilters);
            document.getElementById('projectFilter')?.addEventListener('change', function() {
                localStorage.setItem('projectFilter', getActiveProjectId());
                syncTimeTransferFilters();
            });

            if (timeTransferExportForm) {
                timeTransferExportForm.addEventListener('submit', function(e) {
                    syncTimeTransferFilters();
                    const project = document.getElementById('timeTransferProjectId')?.value;
                    if (!project) {
                        e.preventDefault();
                        alert(@json(__('messages.Please select a project to export transfer CSV.')));
                    }
                });
            }

            if (timeTransferImportLink) {
                timeTransferImportLink.addEventListener('click', function(e) {
                    const project = getActiveProjectId();
                    if (!project) {
                        e.preventDefault();
                        alert(@json(__('messages.Please select a project to import transfer CSV.')));
                    }
                });
            }

            // Total Calculator
            function updateTotals() {
                let totalDuration = 0, totalOperatingCost = 0, totalSales = 0;
                const rows = document.querySelectorAll('#taskTableBody tr[data-task-id]');
                
                rows.forEach(row => {
                    if (row.style.display === 'none') return; 
                    
                    const durationCell = row.cells[5];
                    if (durationCell) {
                        const durationText = durationCell.textContent.trim();
                        if (durationText !== 'Still Working') {
                            const matches = durationText.match(/(\d+)h:(\d+)m:(\d+)s/);
                            if (matches) {
                                totalDuration += (parseInt(matches[1]) * 60) + parseInt(matches[2]);
                            }
                        }
                    }
                    const operatingCostCell = row.cells[6];
                    if (operatingCostCell) {
                        totalOperatingCost += parseFloat(operatingCostCell.textContent.trim().replace('€', '').replace(',', '.')) || 0;
                    }
                    const salesCell = row.cells[7];
                    if (salesCell) {
                        totalSales += parseFloat(salesCell.textContent.trim().replace('€', '').replace(',', '.')) || 0;
                    }
                });
                const hours = Math.floor(totalDuration / 60);
                const minutes = totalDuration % 60;
                document.getElementById('totalDurationLabel').textContent = `${hours}h:${minutes.toString().padStart(2, '0')}m`;
                document.getElementById('totalOperatingCostLabel').textContent = totalOperatingCost.toFixed(2) + '€';
                document.getElementById('totalSalesLabel').textContent = totalSales.toFixed(2) + '€';
            }
            updateTotals();
            
            // Re-calculate totals after filters are applied
            document.getElementById('filterForm').addEventListener('submit', () => setTimeout(updateTotals, 500));
            document.getElementById('dateForm').addEventListener('submit', () => setTimeout(updateTotals, 500));

            // Dynamic Filter Logic
            async function fetchAndPopulate(url, selectElementId, defaultOptionText, preserveSelections = false) {
                const selectElement = document.getElementById(selectElementId);
                
                // Preserve selected values if needed (for multiple select like taskFilter)
                let selectedValues = [];
                let hadAllOption = false;
                if (preserveSelections && selectElement.multiple) {
                    selectedValues = Array.from(selectElement.selectedOptions).map(opt => opt.value);
                    hadAllOption = selectedValues.includes('all');
                }
                
                // Only add default option if it's not a multiple select
                if (!selectElement.multiple) {
                    selectElement.innerHTML = `<option value="">${defaultOptionText}</option>`;
                } else {
                    selectElement.innerHTML = '';
                    // For taskFilter (multiple select), add "All Tasks" option
                    if (selectElementId === 'taskFilter') {
                        const allOption = new Option('All Tasks', 'all');
                        if (hadAllOption || selectedValues.length === 0) {
                            allOption.selected = true;
                        }
                        selectElement.appendChild(allOption);
                    }
                }
                
                try {
                    const response = await fetch(url);
                    if (!response.ok) {
                        console.error('Failed to fetch data:', response.status, response.statusText);
                        return;
                    }
                    const data = await response.json();
                    if (!Array.isArray(data)) {
                        console.error('Expected array but got:', typeof data, data);
                        return;
                    }
                    data.forEach(item => {
                        if (!item || !item.id) {
                            console.warn('Invalid item in response:', item);
                            return;
                        }
                        const option = new Option(item.name || item.title, item.id);
                        if (item.tva) option.setAttribute('data-tva', item.tva);
                        // Restore selection if it was previously selected
                        if (preserveSelections && selectedValues.includes(item.id.toString())) {
                            option.selected = true;
                        }
                        // Apply green styling for projects with tasks in date range
                        if (selectElementId === 'projectFilter') {
                            applyProjectStyling(option, item.id);
                        }
                        selectElement.appendChild(option);
                    });
                } catch (error) {
                    console.error('Error fetching data:', error);
                }
            }

            const roleRoutes = {
                'Admin': '/admin/filter',
                'admin': '/admin/filter',
                'Responsable de projet': '/Responsable_de_projet/filter',
                'Employee': '/employee/filter',
                'employee': '/employee/filter',
                'Responsable de projet et gestionnaire': '/Responsable_gestionnaire/filter'
            };
            const userRole = "{{ Auth::user()->roles->first()->name }}";
            let baseUrl = roleRoutes[userRole] || roleRoutes[userRole.trim()] || '';
            
            // Fallback: Try to determine baseUrl from current path if not set
            if (!baseUrl) {
                const currentPath = window.location.pathname;
                if (currentPath.includes('/admin/')) {
                    baseUrl = '/admin/filter';
                } else if (currentPath.includes('/Responsable_de_projet/')) {
                    baseUrl = '/Responsable_de_projet/filter';
                } else if (currentPath.includes('/employee/')) {
                    baseUrl = '/employee/filter';
                } else if (currentPath.includes('/Responsable_gestionnaire/')) {
                    baseUrl = '/Responsable_gestionnaire/filter';
                } else {
                    // Default to admin if we can't determine
                    baseUrl = '/admin/filter';
                }
            }
            
            // Debug: Log baseUrl to ensure it's set correctly
            console.log('baseUrl set to:', baseUrl, 'for role:', userRole, 'trimmed:', userRole.trim());
            
            const rolePrefixes = {
                'Admin': 'admin',
                'admin': 'admin',
                'SuperAdmin': 'admin',
                'Responsable de projet': 'Responsable_de_projet',
                'Employee': 'employee',
                'employee': 'employee',
                'Responsable de projet et gestionnaire': 'Responsable_gestionnaire'
            };
            var rolePrefix = window.rolePrefix || rolePrefixes[userRole] || (userRole ? rolePrefixes[userRole.trim()] : '') || '';
            if (!rolePrefix) {
                const currentPath = window.location.pathname;
                if (currentPath.includes('/admin/')) {
                    rolePrefix = 'admin';
                } else if (currentPath.includes('/Responsable_de_projet/')) {
                    rolePrefix = 'Responsable_de_projet';
                } else if (currentPath.includes('/employee/')) {
                    rolePrefix = 'employee';
                } else if (currentPath.includes('/Responsable_gestionnaire/')) {
                    rolePrefix = 'Responsable_gestionnaire';
                } else {
                    rolePrefix = 'admin';
                }
            }
            window.rolePrefix = rolePrefix;
            
            // Store projects with tasks in the selected date range
            const projectsWithTasks = @json(isset($projectsWithTasks) ? $projectsWithTasks : []);

            // Store projects that have an exact period invoice for the selected date range
            const projectsWithExactInvoices = @json(isset($projectsWithExactInvoices) ? $projectsWithExactInvoices : []);
            
            // Function to check if a project has tasks and/or an exact invoice and apply styling / label
            function applyProjectStyling(option, projectId) {
                const id = parseInt(projectId);
                const hasTasks = projectsWithTasks.includes(id);
                const hasExactInvoice = projectsWithExactInvoices.includes(id);

                // Reset any previous inline styles / classes we may have applied
                option.classList.remove('has-tasks');
                option.style.color = '';
                option.style.fontWeight = '';

                // If there is an exact invoice for this project and range, append label and do NOT make it green
                if (hasExactInvoice) {
                    if (!option.text.includes('(INVOICED)')) {
                        option.text = option.text + ' (INVOICED)';
                    }
                    return;
                }

                // If project has tasks in range but no exact invoice yet, mark it in green
                if (hasTasks) {
                    option.classList.add('has-tasks');
                    option.style.color = '#16a34a'; // green-600
                    option.style.fontWeight = '500';
                }
            }
            
            // Apply styling to existing project options on page load
            (function() {
                const projectFilter = document.getElementById('projectFilter');
                if (projectFilter) {
                    Array.from(projectFilter.options).forEach(option => {
                        if (option.value) {
                            applyProjectStyling(option, option.value);
                        }
                    });
                }
            })();

            document.getElementById('clientFilter').addEventListener('change', async () => {
                const clientId = document.getElementById('clientFilter').value;
                const url = clientId ? `${baseUrl}/clients/${clientId}/projects` : `${baseUrl}/all-projects?include_tva=true`;
                await fetchAndPopulate(url, 'projectFilter', 'All Projects');
                syncTimeTransferFilters();
                // Reset task filter when client changes
                const taskFilter = document.getElementById('taskFilter');
                taskFilter.innerHTML = ('<option value="all" selected>' + @js(__('messages.All Tasks')) + '</option>');
            });

            document.getElementById('employeeFilter').addEventListener('change', async () => {
                const employeeId = document.getElementById('employeeFilter').value;
                if (employeeId) {
                    await fetchAndPopulate(`${baseUrl}/employees/${employeeId}/projects?include_tva=true`, 'projectFilter', 'All Projects');
                    await fetchAndPopulate(`${baseUrl}/employees/${employeeId}/tasks`, 'taskFilter', 'All Tasks', true);
                } else {
                    await fetchAndPopulate(`${baseUrl}/all-projects?include_tva=true`, 'projectFilter', 'All Projects');
                    await fetchAndPopulate(`${baseUrl}/all-tasks`, 'taskFilter', 'All Tasks', true);
                }
                syncTimeTransferFilters();
            });
            
            // Update tasks when project is selected (without submitting form)
            // Also keep time transfer import/export buttons in sync, even if other listeners fail.
            document.getElementById('projectFilter').addEventListener('change', async () => {
                syncTimeTransferFilters();
                const projectId = document.getElementById('projectFilter').value;
                if (!projectId) {
                    // If no project selected, load all tasks
                    const url = baseUrl ? `${baseUrl}/all-tasks` : '/admin/filter/all-tasks';
                    await fetchAndPopulate(url, 'taskFilter', 'All Tasks', false);
                    syncTimeTransferFilters();
                    return;
                }
                
                // Construct the correct URL with baseUrl
                const url = baseUrl ? `${baseUrl}/projects/${projectId}/tasks` : `/admin/filter/projects/${projectId}/tasks`;
                console.log('Fetching tasks from URL:', url, 'baseUrl:', baseUrl); // Debug log
                
                if (!baseUrl) {
                    console.error('baseUrl is empty! Using fallback URL.');
                }
                
                // Don't preserve selections when project changes - reset to show all tasks for the new project
                await fetchAndPopulate(url, 'taskFilter', 'All Tasks', false);
                syncTimeTransferFilters();
            });
            
            // Filter tasks on page load if a project is selected
            // This ensures tasks are filtered by project even on initial page load
            (async () => {
                // Wait a bit to ensure DOM is fully rendered
                await new Promise(resolve => setTimeout(resolve, 50));
                
                const initialProjectId = document.getElementById('projectFilter')?.value;
                const taskFilter = document.getElementById('taskFilter');
                
                if (!taskFilter) return;
                
                // Count actual task options (excluding "all" option)
                const taskOptionsCount = Array.from(taskFilter.options).filter(opt => opt.value !== '' && opt.value !== 'all').length;
                
                // If a project is selected, always fetch tasks for that project to ensure they're filtered
                // This handles both initial page load and after form submission
                if (initialProjectId) {
                    const url = baseUrl ? `${baseUrl}/projects/${initialProjectId}/tasks` : `/admin/filter/projects/${initialProjectId}/tasks`;
                    console.log('Initial load - fetching tasks from URL:', url, 'baseUrl:', baseUrl); // Debug log
                    await fetchAndPopulate(url, 'taskFilter', 'All Tasks', true);
                    
                    // Restore selected tasks from URL params
                    restoreFilterValues();
                } else if (taskOptionsCount === 0) {
                    // If no project is selected and no tasks are shown, load all tasks
                    const url = `${baseUrl}/all-tasks`;
                    await fetchAndPopulate(url, 'taskFilter', 'All Tasks', true);
                    restoreFilterValues();
                }

                syncTimeTransferFilters();
            })();
            
            // Logic for Generate Period Invoice Button
            document.getElementById('generatePeriodInvoiceBtn').addEventListener('click', function(e) {
                e.preventDefault();
                var projectId = $('#projectFilter').val();
                var startDate = $('#startDateInput').val();
                var endDate = $('#endDateInput').val();
                var comment = encodeURIComponent($('#periodInvoiceComment').val());
                // Get multiple selected tasks (excluding "all" option)
                var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions)
                    .map(option => option.value)
                    .filter(taskId => taskId !== 'all');

                if (!projectId) { alert(@js(__('messages.Please select a project from the filter dropdown.'))); return; }
                if (!startDate || !endDate) { alert(@js(__('messages.Please enter both start and end dates in the date filter section.'))); return; }

                var downloadUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet')) downloadUrl = "{{ route('Responsable_de_projet.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif
                @if(Auth::user()->hasRole('Admin')) downloadUrl = "{{ route('admin.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif
                @if(Auth::user()->hasRole('Employee')) downloadUrl = "{{ route('employee.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) downloadUrl = "{{ route('Responsable_gestionnaire.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}"; @endif

                downloadUrl = downloadUrl.replace(':projectId', projectId).replace(':startDate', startDate).replace(':endDate', endDate);
                
                var queryParams = [];
                if (comment) {
                    queryParams.push('comment=' + comment);
                }
                // Add multiple tasks to query params (only if specific tasks are selected, not "all")
                if (selectedTasks.length > 0) {
                    selectedTasks.forEach(taskId => {
                        queryParams.push('task[]=' + encodeURIComponent(taskId));
                    });
                }
                
                if (queryParams.length > 0) {
                    downloadUrl += '?' + queryParams.join('&');
                }

                window.location.href = downloadUrl;
            });


            // --- PDF Export Logic (Vanilla JS) ---
    document.getElementById('exportPDFBtn').addEventListener('click', function() {
                var startDate = document.getElementById('startDateInput').value;
                var endDate = document.getElementById('endDateInput').value;
                var employeeId = document.getElementById('employeeFilter').value;
                var projectId = document.getElementById('projectFilter').value;
                var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions)
                    .map(option => option.value)
                    .filter(taskId => taskId !== 'all');
                var clientId = document.getElementById('clientFilter').value;
                var projectName = '';
                var projectSelect = document.getElementById('projectFilter');
                if (projectSelect.selectedIndex > 0) {
                    projectName = projectSelect.options[projectSelect.selectedIndex].text;
                }

                // Get CSRF token from meta tag
                var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        if (startDate) {
            var url;
            if (startDate && endDate) {
                url = '/' + rolePrefix + '/Pointagerepports/downloadRepports/' + startDate + '/' + endDate;
            } else {
                url = '/' + rolePrefix + '/Pointagerepports/downloadOneDayRepports/' + startDate;
            }

                    var queryParams = [];
                    if (employeeId) {
                        queryParams.push('employeeId=' + encodeURIComponent(employeeId));
                    }
                    if (projectId) {
                        queryParams.push('projectId=' + encodeURIComponent(projectId));
                    }
                    if (selectedTasks.length > 0) {
                        queryParams.push('taskIds=' + encodeURIComponent(JSON.stringify(selectedTasks)));
                    }
                    if (clientId) {
                        queryParams.push('clientId=' + encodeURIComponent(clientId));
                    }
                    
                    // Generate a meaningful filename
                    var fileName = 'Report_';
                    if (projectName) {
                        fileName += projectName.replace(/[^a-z0-9]/gi, '_') + '_';
                    }
                    if (startDate) {
                        fileName += startDate;
                    }
                    if (endDate) {
                        fileName += '_to_' + endDate;
                    }
                    fileName += '.pdf';
                    
                    // Add filename to query params
                    queryParams.push('fileName=' + encodeURIComponent(fileName));
                    
                    if (queryParams.length > 0) {
                        url += '?' + queryParams.join('&');
                    }

                    fetch(url, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        }
                    })
                    .then(async response => {
                        const skippedHeader = response.headers.get('X-Skipped-Tasks');
                        var skippedTasks = null;

                        // Parse skipped tasks if available
                        if (skippedHeader) {
                            try {
                                skippedTasks = JSON.parse(atob(skippedHeader));
                            } catch (e) {
                                console.error('Failed to parse skipped tasks header', e);
                            }
                        }

                        if (!response.ok) {
                            // Even if response is not OK, we might have skipped tasks to show
                            var errorMessage = 'An error occurred while generating the PDF. Please try again.';
                            try {
                                const errorText = await response.text();
                                if (errorText) {
                                    try {
                                        const errorData = JSON.parse(errorText);
                                        errorMessage = errorData.message || errorData.error || errorMessage;
                                        if (errorData.detail) {
                                            errorMessage += ' [' + errorData.detail + ']';
                                        }
                                        // Check if skipped tasks are in the response body (for 400 errors when all tasks are skipped)
                                        if (errorData.skippedTasks && Array.isArray(errorData.skippedTasks)) {
                                            skippedTasks = errorData.skippedTasks;
                                        }
                                    } catch (e) {
                                        // Not JSON, use as is
                                        if (errorText.length < 200) {
                                            errorMessage = errorText;
                                        }
                                    }
                                }
                            } catch (e) {
                                // Ignore error reading response
                            }
                            
                            // Show modal with skipped tasks if available
                            if (window.showPdfErrorModal) {
                                window.showPdfErrorModal(skippedTasks, errorMessage);
                            } else {
                                alert(errorMessage);
                            }
                            return;
                        }

                        return response.blob().then(blob => ({
                            blob: blob,
                            skippedTasks: skippedTasks
                        }));
                    })
                    .then((result) => {
                        if (!result) return; // Error was already handled
                        
                        var url = window.URL.createObjectURL(result.blob);
                        var a = document.createElement('a');
                        a.href = url;
                        a.download = fileName;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        window.URL.revokeObjectURL(url);

                        if (result.skippedTasks && Array.isArray(result.skippedTasks) && result.skippedTasks.length > 0) {
                            showSkippedTasksModal(result.skippedTasks);
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        // Show modal with error message
                        if (window.showPdfErrorModal) {
                            window.showPdfErrorModal(null, 'An error occurred while generating the PDF. Please try again. If the problem persists, check your filters and network connection, or contact an administrator.');
                        } else {
                            alert(@js(__('messages.An error occurred while generating the PDF. Please try again.')));
                        }
                    });
                } else {
                    alert(@js(__('messages.Please select a start date first.')));
                }
            });

            document.getElementById('exportPDFBtnCustomer').addEventListener('click', function() {
                var startDate = document.getElementById('startDateInput').value;
                var endDate = document.getElementById('endDateInput').value;
                var employeeId = document.getElementById('employeeFilter').value;
                var projectId = document.getElementById('projectFilter').value;
                var selectedTasks = Array.from(document.getElementById('taskFilter').selectedOptions)
                    .map(option => option.value)
                    .filter(taskId => taskId !== 'all');
                var clientId = document.getElementById('clientFilter').value;
                var projectName = '';
                var projectSelect = document.getElementById('projectFilter');
                if (projectSelect.selectedIndex > 0) {
                    projectName = projectSelect.options[projectSelect.selectedIndex].text;
                }

                if (startDate) {
            var url;
            if (startDate && endDate) {
                url = '/' + rolePrefix + '/CustomerPointagerepports/preview/' + startDate + '/' + endDate;
            } else {
                url = '/' + rolePrefix + '/CustomerPointagerepports/previewOneDay/' + startDate;
            }

                    var queryParams = [];
                    if (employeeId) {
                        queryParams.push('employeeId=' + encodeURIComponent(employeeId));
                    }
                    if (projectId) {
                        queryParams.push('projectId=' + encodeURIComponent(projectId));
                    }
                    if (selectedTasks.length > 0) {
                        queryParams.push('taskIds=' + encodeURIComponent(JSON.stringify(selectedTasks)));
                    }
                    if (clientId) {
                        queryParams.push('clientId=' + encodeURIComponent(clientId));
                    }
                    
                    if (queryParams.length > 0) {
                        url += '?' + queryParams.join('&');
                    }

                    window.location.href = url;
                }
            });

            // --- Skipped tasks modal helpers ---
            window.showSkippedTasksModal = function(skippedTasks) {
                var modal = document.getElementById('skippedTasksModal');
                var tbody = document.getElementById('skippedTasksTableBody');

                if (!modal || !tbody) {
                    console.error('Skipped tasks modal elements not found');
                    return;
                }

                // Clear previous rows
                tbody.innerHTML = '';

                skippedTasks.forEach(function(task) {
                    var tr = document.createElement('tr');

                    function createCell(text) {
                        var td = document.createElement('td');
                        td.className = 'px-3 py-2 text-sm text-gray-700 border-b border-gray-200';
                        td.textContent = text || '';
                        return td;
                    }

                    // Format duration (minutes -> HH:MM)
                    var durationText = '';
                    var durationMinutes = null;

                    if (typeof task.duration === 'number') {
                        durationMinutes = task.duration;
                    } else if (typeof task.duration === 'string' && task.duration.trim() !== '') {
                        var parsed = parseInt(task.duration, 10);
                        if (!isNaN(parsed)) {
                            durationMinutes = parsed;
                        }
                    }

                    if (durationMinutes !== null) {
                        var hours = Math.floor(durationMinutes / 60);
                        var minutes = durationMinutes % 60;
                        durationText = (hours < 10 ? '0' + hours : hours) + 'h:' +
                                       (minutes < 10 ? '0' + minutes : minutes) + 'm';
                    }

                    tr.appendChild(createCell(task.task_name));
                    tr.appendChild(createCell(task.project_name));
                    tr.appendChild(createCell(task.employee_name));
                    tr.appendChild(createCell(task.start_date));
                    tr.appendChild(createCell(task.end_date));
                    tr.appendChild(createCell(durationText));

                    tbody.appendChild(tr);
                });

                modal.classList.remove('hidden');
            };

            window.closeSkippedTasksModal = function() {
                var modal = document.getElementById('skippedTasksModal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };

            // --- Generic PDF error modal helpers (with skipped tasks table) ---
            window.showPdfErrorModal = function(skippedTasks, errorMessage) {
                var modal = document.getElementById('pdfErrorModal');
                var tbody = document.getElementById('pdfErrorTableBody');
                var descriptionEl = document.getElementById('pdfErrorDescription');

                if (!modal) {
                    // Fallback if modal markup is missing
                    alert(errorMessage || 'An error occurred while generating the PDF. Please try again.');
                    return;
                }

                // Update description message
                if (descriptionEl) {
                    if (errorMessage) {
                        descriptionEl.textContent = errorMessage;
                    } else if (skippedTasks && Array.isArray(skippedTasks) && skippedTasks.length > 0) {
                        descriptionEl.textContent = {!! json_encode(__('messages.Skipped tasks description')) !!};
                    } else {
                        descriptionEl.textContent = {!! json_encode(__('messages.An error occurred while generating the PDF. Please try again. If the problem persists, check your filters and network connection, or contact an administrator.')) !!};
                    }
                }

                // Clear previous rows
                if (tbody) {
                    tbody.innerHTML = '';
                } else {
                    console.error('Table body element not found!');
                }

                // Populate table with skipped tasks if available
                console.log('showPdfErrorModal called with:', { skippedTasks, errorMessage, skippedTasksLength: skippedTasks ? skippedTasks.length : 0, tbodyExists: !!tbody });
                if (skippedTasks && Array.isArray(skippedTasks) && skippedTasks.length > 0 && tbody) {
                    console.log('Populating table with', skippedTasks.length, 'skipped tasks');
                    skippedTasks.forEach(function(task) {
                        console.log('Processing task:', task);
                        var tr = document.createElement('tr');

                        function createCell(text) {
                            var td = document.createElement('td');
                            td.className = 'px-3 py-2 text-sm text-gray-700 border-b border-gray-200';
                            td.textContent = text || '';
                            return td;
                        }

                        // Format duration (minutes -> HH:MM)
                        var durationText = '';
                        var durationMinutes = null;

                        if (typeof task.duration === 'number') {
                            durationMinutes = task.duration;
                        } else if (typeof task.duration === 'string' && task.duration.trim() !== '') {
                            var parsed = parseInt(task.duration, 10);
                            if (!isNaN(parsed)) {
                                durationMinutes = parsed;
                            }
                        }

                        if (durationMinutes !== null) {
                            var hours = Math.floor(durationMinutes / 60);
                            var minutes = durationMinutes % 60;
                            durationText = (hours < 10 ? '0' + hours : hours) + 'h:' +
                                           (minutes < 10 ? '0' + minutes : minutes) + 'm';
                        }

                        tr.appendChild(createCell(task.task_name || ''));
                        tr.appendChild(createCell(task.project_name || ''));
                        tr.appendChild(createCell(task.employee_name || ''));
                        tr.appendChild(createCell(task.start_date || ''));
                        tr.appendChild(createCell(task.end_date || ''));
                        tr.appendChild(createCell(durationText));

                        tbody.appendChild(tr);
                    });
                }

                modal.classList.remove('hidden');
            };

            window.closePdfErrorModal = function() {
                var modal = document.getElementById('pdfErrorModal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };

        });
    </script>

    <script>
    // Collapsible checkbox dropdown for the task multi-filter.
    // The native #taskFilter select stays the source of truth (form submit,
    // selectedOptions reads, dynamic repopulation all keep working untouched);
    // this UI only mirrors it and writes back through it.
    (function () {
        var select = document.getElementById('taskFilter');
        var dropdown = document.getElementById('taskFilterDropdown');
        var btn = document.getElementById('taskFilterBtn');
        var btnText = document.getElementById('taskFilterBtnText');
        var chevron = document.getElementById('taskFilterChevron');
        var panel = document.getElementById('taskFilterPanel');
        var list = document.getElementById('taskFilterList');
        var search = document.getElementById('taskFilterSearch');
        var emptyNote = document.getElementById('taskFilterEmpty');
        if (!select || !dropdown || !btn || !panel || !list) return;

        function selectedOptions() {
            return Array.from(select.options).filter(function (o) { return o.selected; });
        }

        function refreshButton() {
            var selected = selectedOptions();
            var allOpt = Array.from(select.options).find(function (o) { return o.value === 'all'; });
            if (selected.length === 0 || (allOpt && allOpt.selected)) {
                btnText.textContent = allOpt ? allOpt.text : '';
            } else {
                btnText.textContent = selected.map(function (o) { return o.text; }).join(', ');
            }
            btnText.title = btnText.textContent;
        }

        function rebuildList() {
            list.innerHTML = '';
            Array.from(select.options).forEach(function (opt) {
                var row = document.createElement('label');
                row.className = 'flex cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 text-[13px] text-slate-700 transition-colors hover:bg-slate-50';
                var box = document.createElement('input');
                box.type = 'checkbox';
                box.checked = opt.selected;
                box.className = 'h-4 w-4 flex-shrink-0 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/25';
                box.setAttribute('data-value', opt.value);
                var label = document.createElement('span');
                label.className = 'min-w-0 flex-1 truncate';
                label.textContent = opt.text;
                label.title = opt.text;
                box.addEventListener('change', function () {
                    opt.selected = box.checked;
                    enforceExclusivity(opt.value);
                    // Let the existing handlers run exactly as before
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    refreshFromNative();
                });
                row.appendChild(box);
                row.appendChild(label);
                list.appendChild(row);
            });
            refreshButton();
            applySearch();
        }

        function findAllOption() {
            return Array.from(select.options).find(function (o) { return o.value === 'all'; });
        }

        // Single source of truth for the "All vs specific" rule (mirrors
        // the server, which drops 'all' and filters by the remaining ids):
        // - selecting "All" clears every specific task;
        // - selecting any specific task clears "All";
        // - with nothing selected, fall back to "All" so the state is defined.
        function enforceExclusivity(changedValue) {
            var allOpt = findAllOption();
            if (!allOpt) return;
            var specifics = Array.from(select.options).filter(function (o) {
                return o.value !== 'all' && o.selected;
            });
            if (changedValue === 'all') {
                if (allOpt.selected) {
                    specifics.forEach(function (o) { o.selected = false; });
                }
            } else {
                if (specifics.length > 0) {
                    allOpt.selected = false;
                } else if (!allOpt.selected) {
                    allOpt.selected = true;
                }
            }
        }

        function refreshFromNative() {
            enforceExclusivity(null);
            var boxes = list.querySelectorAll('input[type="checkbox"]');
            boxes.forEach(function (box) {
                var opt = Array.from(select.options).find(function (o) { return o.value === box.getAttribute('data-value'); });
                if (opt) box.checked = opt.selected;
            });
            // Rebuild if the option set changed (dynamic repopulation)
            if (boxes.length !== select.options.length) rebuildList();
            else refreshButton();
        }

        function applySearch() {
            if (!search) return;
            var q = search.value.trim().toLowerCase();
            var visible = 0;
            list.querySelectorAll(':scope > label').forEach(function (row) {
                var text = (row.querySelector('span:last-child')?.textContent || '').toLowerCase();
                var show = q === '' || text.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            if (emptyNote) {
                var none = visible === 0 && q !== '';
                emptyNote.classList.toggle('hidden', !none);
                if (none) emptyNote.textContent = (emptyNote.getAttribute('data-no-results') || '') + ' "' + search.value.trim() + '"';
            }
        }

        // Open downward by default; flip upward when there is not enough
        // room below but more room above, so the panel never gets cut off.
        function positionPanel() {
            panel.classList.remove('bottom-full', 'mb-2');
            panel.classList.add('mt-2');
            var rect = btn.getBoundingClientRect();
            var estH = Math.min(320, panel.scrollHeight > 0 ? panel.scrollHeight : 280);
            var spaceBelow = window.innerHeight - rect.bottom;
            var spaceAbove = rect.top;
            if (spaceBelow < estH + 16 && spaceAbove > spaceBelow) {
                panel.classList.remove('mt-2');
                panel.classList.add('bottom-full', 'mb-2');
            }
        }

        function setOpen(open) {
            panel.classList.toggle('hidden', !open);
            if (open) positionPanel();
            btn.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : '';
            if (!open && search) {
                search.value = '';
                applySearch();
            } else if (open && search) {
                applySearch();
                setTimeout(function () { search.focus({ preventScroll: true }); }, 30);
            }
        }

        window.addEventListener('resize', function () {
            if (!panel.classList.contains('hidden')) positionPanel();
        });

        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            refreshFromNative();
            setOpen(panel.classList.contains('hidden'));
        });

        document.addEventListener('click', function (e) {
            if (!panel.classList.contains('hidden') && !dropdown.contains(e.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && !panel.classList.contains('hidden')) setOpen(false);
        });

        if (search) search.addEventListener('input', applySearch);

        // Native select changed elsewhere (restore, dynamic reload) -> refresh UI
        select.addEventListener('change', refreshFromNative);
        if (window.MutationObserver) {
            new MutationObserver(refreshFromNative).observe(select, { childList: true });
        }

        rebuildList();
        // Refresh after the existing restore logic runs on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', refreshFromNative);
        if (document.readyState !== 'loading') refreshFromNative();
    })();
    </script>

    <script>
    // Custom searchable dropdowns for the single-value filters
    // (Client, Employee, Project, Type). The native selects stay the source
    // of truth: dynamic repopulation, restore, persistence and form submit
    // all keep talking to them untouched; this UI only mirrors them.
    (function () {
        var openInstance = null;

        function closeAllSingle(except) {
            document.querySelectorAll('[data-filter-dropdown]').forEach(function (dd) {
                if (dd !== except) {
                    var p = dd.querySelector('[id$="Panel"]');
                    var b = dd.querySelector('button[id$="Btn"]');
                    if (p) p.classList.add('hidden');
                    if (b) b.setAttribute('aria-expanded', 'false');
                    var c = dd.querySelector('[data-chevron]');
                    if (c) c.style.transform = '';
                }
            });
            if (openInstance && openInstance !== except) openInstance = null;
        }

        function makeSingleFilterDropdown(prefix) {
            var select = document.getElementById(prefix);
            var dropdown = document.getElementById(prefix + 'Dropdown');
            if (!select || !dropdown) return;
            var btn = document.getElementById(prefix + 'Btn');
            var btnText = document.getElementById(prefix + 'BtnText');
            var chevron = dropdown.querySelector('[data-chevron]');
            var panel = document.getElementById(prefix + 'Panel');
            var list = document.getElementById(prefix + 'List');
            var emptyNote = document.getElementById(prefix + 'Empty');
            var search = dropdown.querySelector('[data-filter-search]');
            if (!btn || !panel || !list) return;

            function options() { return Array.from(select.options); }

            function refreshButton() {
                var sel = options().find(function (o) { return o.value === select.value && select.value !== ''; });
                var allOpt = options().find(function (o) { return o.value === ''; });
                var text = sel ? sel.text : (allOpt ? allOpt.text : '');
                if (btnText) {
                    btnText.textContent = text;
                    btnText.title = text;
                }
            }

            function applySearch() {
                if (!search) return;
                var q = search.value.trim().toLowerCase();
                var visible = 0;
                list.querySelectorAll(':scope > button').forEach(function (row) {
                    var text = (row.querySelector('span:first-child')?.textContent || '').toLowerCase();
                    var show = q === '' || text.includes(q);
                    row.style.display = show ? '' : 'none';
                    if (show) visible++;
                });
                if (emptyNote) {
                    var none = visible === 0 && q !== '';
                    emptyNote.classList.toggle('hidden', !none);
                    if (none) emptyNote.textContent = (emptyNote.getAttribute('data-no-results') || '') + ' "' + search.value.trim() + '"';
                }
            }

            function rebuildList() {
                list.innerHTML = '';
                options().forEach(function (opt) {
                    var row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 text-left text-[13px] transition-colors hover:bg-slate-50';
                    row.setAttribute('role', 'option');
                    row.setAttribute('aria-selected', opt.selected && opt.value !== '' ? 'true' : 'false');
                    var label = document.createElement('span');
                    label.className = 'min-w-0 flex-1 truncate text-slate-700';
                    label.textContent = opt.text;
                    label.title = opt.text;
                    if (opt.style.color) label.style.color = opt.style.color;
                    if (opt.style.fontWeight) label.style.fontWeight = opt.style.fontWeight;
                    var check = document.createElement('span');
                    check.className = 'flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full ' + (opt.selected && opt.value !== '' ? 'bg-[#2D7080] text-white' : 'border border-slate-300 text-transparent');
                    check.innerHTML = '<svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>';
                    row.appendChild(label);
                    row.appendChild(check);
                    row.addEventListener('click', function () {
                        select.value = opt.value;
                        select.dispatchEvent(new Event('change', { bubbles: true }));
                        refreshFromNative();
                        setOpen(false);
                    });
                    list.appendChild(row);
                });
                applySearch();
                refreshButton();
            }

            function refreshFromNative() {
                var rows = list.querySelectorAll(':scope > button');
                if (rows.length !== select.options.length) {
                    rebuildList();
                    return;
                }
                rows.forEach(function (row, i) {
                    var opt = select.options[i];
                    var check = row.querySelector('span:last-child');
                    var selected = opt && opt.selected && opt.value !== '';
                    row.setAttribute('aria-selected', selected ? 'true' : 'false');
                    if (check) {
                        check.className = 'flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full ' + (selected ? 'bg-[#2D7080] text-white' : 'border border-slate-300 text-transparent');
                    }
                    var label = row.querySelector('span:first-child');
                    if (label && opt) {
                        if (label.textContent !== opt.text) {
                            label.textContent = opt.text;
                            label.title = opt.text;
                        }
                        label.style.color = opt.style.color || '';
                        label.style.fontWeight = opt.style.fontWeight || '';
                    }
                });
                applySearch();
                refreshButton();
            }

            function positionPanel() {
                panel.classList.remove('bottom-full', 'mb-2');
                panel.classList.add('mt-2');
                var rect = btn.getBoundingClientRect();
                var estH = Math.min(340, panel.scrollHeight > 0 ? panel.scrollHeight : 300);
                var spaceBelow = window.innerHeight - rect.bottom;
                var spaceAbove = rect.top;
                if (spaceBelow < estH + 16 && spaceAbove > spaceBelow) {
                    panel.classList.remove('mt-2');
                    panel.classList.add('bottom-full', 'mb-2');
                }
            }

            function setOpen(open) {
                if (open) {
                    closeAllSingle(dropdown);
                    openInstance = dropdown;
                } else {
                    if (openInstance === dropdown) openInstance = null;
                }
                panel.classList.toggle('hidden', !open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (chevron) chevron.style.transform = open ? 'rotate(180deg)' : '';
                if (open) {
                    positionPanel();
                    refreshFromNative();
                    if (search) setTimeout(function () { search.focus({ preventScroll: true }); }, 30);
                } else if (search) {
                    search.value = '';
                    applySearch();
                }
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                refreshFromNative();
                setOpen(panel.classList.contains('hidden'));
            });

            if (search) search.addEventListener('input', applySearch);

            // Native select changed elsewhere (restore, dynamic reload) -> refresh UI
            select.addEventListener('change', refreshFromNative);
            if (window.MutationObserver) {
                new MutationObserver(refreshFromNative).observe(select, { childList: true, subtree: true, characterData: true });
            }

            rebuildList();
            document.addEventListener('DOMContentLoaded', refreshFromNative);
            if (document.readyState !== 'loading') refreshFromNative();
        }

        document.addEventListener('click', function (e) {
            document.querySelectorAll('[data-filter-dropdown]').forEach(function (dd) {
                var p = dd.querySelector('[id$="Panel"]');
                if (p && !p.classList.contains('hidden') && !dd.contains(e.target)) {
                    p.classList.add('hidden');
                    var b = dd.querySelector('button[id$="Btn"]');
                    if (b) b.setAttribute('aria-expanded', 'false');
                    var c = dd.querySelector('[data-chevron]');
                    if (c) c.style.transform = '';
                    var s = dd.querySelector('[data-filter-search]');
                    if (s) { s.value = ''; }
                    if (openInstance === dd) openInstance = null;
                }
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key !== 'Escape') return;
            document.querySelectorAll('[data-filter-dropdown]').forEach(function (dd) {
                var p = dd.querySelector('[id$="Panel"]');
                if (p && !p.classList.contains('hidden')) {
                    p.classList.add('hidden');
                    if (openInstance === dd) openInstance = null;
                }
            });
        });

        window.addEventListener('resize', function () {
            if (openInstance) {
                var p = openInstance.querySelector('[id$="Panel"]');
                var b = openInstance.querySelector('button[id$="Btn"]');
                if (p && b && !p.classList.contains('hidden')) {
                    p.classList.remove('bottom-full', 'mb-2');
                    p.classList.add('mt-2');
                    var rect = b.getBoundingClientRect();
                    var estH = Math.min(340, p.scrollHeight > 0 ? p.scrollHeight : 300);
                    if (window.innerHeight - rect.bottom < estH + 16 && rect.top > window.innerHeight - rect.bottom) {
                        p.classList.remove('mt-2');
                        p.classList.add('bottom-full', 'mb-2');
                    }
                }
            }
        });

        ['clientFilter', 'employeeFilter', 'projectFilter', 'projectTypeFilter', 'monthFilter'].forEach(makeSingleFilterDropdown);
    })();
    </script>

    <!-- PDF error modal with skipped tasks table (matches customer-preview.blade.php structure) -->
    <div id="pdfErrorModal" class="fixed inset-0 bg-[#0B2638]/50 backdrop-blur-[2px] flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="dt-section-title">
                    {{ __('messages.Tasks not included in the PDF') }}
                </h3>
                <button type="button"
                        onclick="closePdfErrorModal()"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <span class="sr-only">{{ __('messages.Close') }}</span>
                    &times;
                </button>
            </div>
            <div class="px-4 py-3 text-sm text-gray-700 border-b border-gray-100">
                    <p id="pdfErrorDescription">
                        {{ __('messages.Skipped tasks description') }}
                    </p>
            </div>
            <div class="px-4 py-3 max-h-96 overflow-y-auto">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50/80">
                        <tr class="dt-table-head">
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Task') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Project') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Employee') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Start date') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.End date') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Duration') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody id="pdfErrorTableBody" class="bg-white">
                        <!-- Rows injected dynamically -->
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 flex justify-end border-t border-gray-200">
                    <button type="button"
                        onclick="closePdfErrorModal()"
                        class="dt-btn dt-btn-ghost">
                    {{ __('messages.Close') }}
                </button>
            </div>
        </div>
    </div>

    <!-- Skipped tasks modal -->
    <div id="skippedTasksModal" class="fixed inset-0 bg-[#0B2638]/50 backdrop-blur-[2px] flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-2xl shadow-xl max-w-4xl w-full mx-4 overflow-hidden">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
                <h3 class="dt-section-title">
                    {{ __('messages.Tasks not included in the PDF') }}
                </h3>
                <button type="button"
                        onclick="closeSkippedTasksModal()"
                        class="text-gray-400 hover:text-gray-600 focus:outline-none">
                    <span class="sr-only">{{ __('messages.Close') }}</span>
                    &times;
                </button>
            </div>
            <div class="px-4 py-3 text-sm text-gray-700 border-b border-gray-100">
                {{ __('messages.Some tasks were skipped because their data is incomplete or inconsistent (for example: missing project, missing hourly rate, or missing links). You can review them below and fix them later; the rest of the PDF has been generated correctly.') }}
            </div>
            <div class="px-4 py-3 max-h-96 overflow-y-auto">
                <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                    <thead class="bg-slate-50/80">
                        <tr class="dt-table-head">
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Task') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Project') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Employee') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Start date') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.End date') }}
                            </th>
                            <th class="px-3 py-2 text-left whitespace-nowrap border-b border-slate-100">
                                {{ __('messages.Duration') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody id="skippedTasksTableBody" class="bg-white">
                        <!-- Rows injected dynamically -->
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 flex justify-end border-t border-gray-200">
                    <button type="button"
                        onclick="closeSkippedTasksModal()"
                        class="dt-btn dt-btn-ghost">
                    {{ __('messages.Close') }}
                </button>
            </div>
        </div>
    </div>
</body>
</html>


