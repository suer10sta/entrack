<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Employee Profile') }} - DalyTrac</title>
    @vite('resources/css/app.css')

    {{-- This CSS block styles the "editable" fields to look like text until double-clicked --}}
    <style>
        .editable[readonly] {
            border-color: #e2e8f0; /* slate-200 */
            background-color: #f8fafc; /* slate-50 */
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            cursor: pointer;
            font-weight: 500;
            color: #0f172a; /* slate-900 */
        }
        .editable[readonly]:hover {
            color: #2D7080; /* brand-secondary */
            background-color: #f1f5f9; /* slate-100 */
        }
        .editable:not([readonly]) {
            border-color: #2D7080 !important;
            box-shadow: 0 0 0 2px rgba(45, 112, 128, 0.25) !important;
            background-color: #ffffff !important;
        }

        /* Style for editable table cells */
        td.editable {
            cursor: pointer;
        }
        td.editable:hover {
            background-color: #f8fafc; /* slate-50 */
        }

        /* Custom styles to make <select> match <input> */
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
        select:invalid {
            color: #9ca3af;
        }
        option {
            color: #111827;
        }

        /* Mobile: convert tables to modern stacked cards (no side-scroll) */
        @media (max-width: 1024px) {
            .table-responsive {
                overflow-x: visible !important;
            }

            /* Ensure Tailwind's .hidden always wins over table layout rules */
            #editableTable tbody tr.hidden,
            #employeeProjectsTable tbody tr.hidden,
            #employeeTasksTable tbody tr.hidden {
                display: none !important;
            }

            #editableTable thead,
            #employeeProjectsTable thead,
            #employeeTasksTable thead {
                display: none;
            }

            #editableTable tbody,
            #employeeProjectsTable tbody,
            #employeeTasksTable tbody {
                display: block;
                width: 100%;
            }

            #editableTable tbody tr,
            #employeeProjectsTable tbody tr,
            #employeeTasksTable tbody tr {
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

            /* Keep grouping rows as simple separators */
            #employeeProjectsTable tbody tr.emp-group-row,
            #employeeTasksTable tbody tr.emp-group-row {
                display: block !important;
                margin: 10px 0 6px 0 !important;
                padding: 0 !important;
                border: 0 !important;
                border-radius: 0 !important;
                background: transparent !important;
                box-shadow: none !important;
            }

            #editableTable tbody td,
            #employeeProjectsTable tbody td,
            #employeeTasksTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 12px !important;
                border: 1px solid rgba(226, 232, 240, 0.9);
                border-radius: 12px;
                background: rgba(255,255,255,0.78);
                text-align: left !important;
                white-space: normal !important;
                word-break: break-word;
                overflow: visible !important;
            }

            #editableTable tbody td[data-span="2"],
            #employeeProjectsTable tbody td[data-span="2"],
            #employeeTasksTable tbody td[data-span="2"] {
                grid-column: span 2 / span 2;
            }

            #editableTable tbody td[data-label]::before,
            #employeeProjectsTable tbody td[data-label]::before,
            #employeeTasksTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .06em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 6px;
            }

            /* Center the chrono actions inside mobile cards */
            #employeeTasksTable tbody td[data-label] .emp-task-actions {
                display: flex;
                justify-content: center;
                align-items: center;
            }
        }

        /* Also enforce hidden rows on desktop/table layout */
        #editableTable tbody tr.hidden,
        #employeeProjectsTable tbody tr.hidden,
        #employeeTasksTable tbody tr.hidden {
            display: none !important;
        }

        .modal { display: none; position: fixed; z-index: 50; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(4px); }
        .modal-content { animation: slideDown 0.3s ease-out; }
        @keyframes slideDown { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .modal-content-custom {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #e2e8f0;
            width: 80%;
            max-width: 600px;
            text-align: center;
            position: relative;
            border-radius: 1rem;
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
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden font-sans">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            {{-- Back route computation --}}
            @php
                $backRoute = route('admin.employees.index');
                if (Auth::user()->hasRole('Responsable de projet'))
                    $backRoute = route('Responsable_de_projet.employees.index');
                elseif (Auth::user()->hasRole('Employee'))
                    $backRoute = route('employee.employees.index');
                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    $backRoute = route('Responsable_gestionnaire.employees.index');
            @endphp

            {{-- Page Header --}}
            <div class="flex items-center gap-3 mb-5">

                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Employees') }}</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">{{ __('messages.Employee Profile') }}</h1>
                        @if(!empty($employee->archived))
                            <span class="dt-badge dt-badge-danger">
                                {{ __('messages.Archived') }}
                            </span>
                        @endif
                    </div>
                    <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.View and manage employee information') }}</p>
                </div>
            </div>

            <!-- This div stacks all the cards vertically -->
            <div class="space-y-5">

                @php
                    $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                    '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                @endphp

                <!-- Info Cards Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Card 1: Employee Infos -->
                    <div class="dt-card overflow-hidden">
                        <!-- Card Header -->
                        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($employee->image)
                                    <img class="w-9 h-9 rounded-xl object-cover ring-1 ring-slate-200 flex-shrink-0" src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}" alt="employee Image">
                                @else
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white font-semibold text-sm flex-shrink-0"
                                    style="background-color: {{ $colors[0] }};">
                                    <span>{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                </div>
                                @endif
                                <div class="min-w-0">
                                    <h5 class="dt-section-title truncate">{{ __('messages.Employee Infos') }}</h5>
                                    <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                    data-target="employeeInfosBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                                <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <!-- Card Body -->
                        <div id="employeeInfosBody" class="hidden">
                        <div class="px-5 sm:px-6 py-5">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Name') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="name"  type="text"
                                            name="name" value="{{ $employee->name }}" readonly/>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Login Email') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="email"  type="text"
                                            name="email" value="{{ $employee->email }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Phone Number') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="phoneNumber"  type="text"
                                            name="phoneNumber" value="{{ $employee->phoneNumber }}" readonly/>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Contact Email') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="contact_email"  type="text"
                                            name="contact_email" value="{{ $employee->contact_email }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Age') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="age"  type="text"
                                            name="age" value="{{ $employee->age }}" readonly/>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Password') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="password"  type="text"
                                            name="password" value="{{ $employee->password }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Work Hours Limit') }}</label>
                                        <input class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3 editable"
                                            data-id="{{$employee->id}}" data-column="workhours_limit"  type="text"
                                            name="workHours_limit" value="{{ $employee->workHours_limit }}" readonly/>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Timezone') }}</label>
                                        <select class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3 form-select-custom editable-select"
                                            data-id="{{$employee->id}}" data-column="timezone" type="text"
                                            name="timezone">
                                            @foreach (timezone_identifiers_list() as $tz)
                                                <option value="{{ $tz }}" @if($user->timezone == $tz) selected @endif>
                                                    {{ $tz }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <button type="button" id="openEmailModalBtn" class="dt-btn dt-btn-teal w-full justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                        {{ __('messages.Send Email') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Card 2: Professional Infos -->
                    <div class="dt-card overflow-hidden">
                        <!-- Card Header -->
                        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 7H4a2 2 0 00-2 2v10a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2zM16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h5 class="dt-section-title truncate">{{ __('messages.Professional Infos') }}</h5>
                                    <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                    data-target="employeeProfessionalBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                                <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <!-- Card Body -->
                        <div id="employeeProfessionalBody" class="hidden">
                        <div class="px-5 sm:px-6 py-5">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.CNSS Number') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="cnss" type="text"
                                            name="cnss" value="{{ $employee->cnss }}" readonly/>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Bank Account') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="bankAccountNumber" type="text"
                                            name="bankAccountNumber" value="{{ $employee->bankAccountNumber }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Release Date') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="Date_Release" type="text"
                                            name="Date_Release" value="{{ $employee->Date_Release }}" readonly/>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Salary') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="salary" type="text"
                                            name="salary" value="{{ $employee->salary }}€" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Cost Per Hour') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="cost_per_hour" type="text"
                                            name="cost_per_hour" value="{{ $employee->cost_per_hour }}€" readonly/>
                                    </div>
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Cotisation CNSS') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="Cotisation_CNSS"
                                            type="text" name="Cotisation_CNSS"
                                            value="{{ $employee->Cotisation_CNSS }}€" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="min-w-0">
                                        <label class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Cotisation AMO') }}</label>
                                        <input class="editable w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                            data-id="{{$employee->id}}" data-column="Cotisation_AMO"
                                            type="text" name="Cotisation_AMO"
                                            value="{{ $employee->Cotisation_AMO }}€" readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Card 3: Roles -->
                    <div class="dt-card overflow-hidden">
                        <!-- Card Header -->
                        <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4" />
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <h5 class="dt-section-title truncate">{{ __('messages.Roles') }}</h5>
                                    <p class="dt-section-subtitle mt-0.5 truncate">{{ __('messages.Roles information') }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                    data-target="employeeRolesBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                                <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <!-- Card Body -->
                        <div id="employeeRolesBody" class="hidden">
                        <div class="px-5 sm:px-6 py-5">
                            <div class="flex flex-wrap gap-2">
                                @foreach($role_name as $role)
                                <label for="vue-checkbox-{{ $role }}" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-[13px] font-medium text-slate-700 cursor-pointer">
                                    <input id="vue-checkbox-{{ $role }}" name="roles[]"
                                        type="checkbox" value="{{ $role }}" disabled
                                        class="w-4 h-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/30"
                                        @if($user->hasRole($role)) checked @endif>
                                    {{ $role }}
                                </label>
                                @endforeach
                            </div>
                            <div class="mt-4">
                                @php
                                    $editRolesRoute = '#';
                                    if(Auth::user()->hasRole('Admin')) $editRolesRoute = route('admin.employees.edit', $employee->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet')) $editRolesRoute = route('Responsable_de_projet.employees.edit', $employee->id);
                                    elseif(Auth::user()->hasRole('Employee')) $editRolesRoute = route('employee.employees.edit', $employee->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $editRolesRoute = route('Responsable_gestionnaire.employees.edit', $employee->id);
                                @endphp

                                @can('Edit employee roles')
                                <a href="{{ $editRolesRoute }}" class="dt-btn dt-btn-primary">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                    {{ __('messages.Edit roles') }}
                                </a>
                                @endcan
                            </div>
                        </div>
                        </div>
                    </div>
                </div> <!-- End of Info Cards Grid -->

                <!-- Card 4: Work Days Reports -->
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <h2 class="dt-section-title truncate">{{ __('messages.Work Days Reports') }}</h2>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                data-target="employeeWorkDaysBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <div id="employeeWorkDaysBody" class="hidden">
                    <div class="px-3 sm:px-4 py-4">
                        <div class="overflow-x-auto table-responsive rounded-xl border border-slate-200">
                            <table id="editableTable" class="min-w-full table-auto">
                                <thead class="bg-slate-50/80 dt-table-head">
                                    <tr class="border-b border-slate-200">
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Internal Work Days') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Sick Days') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Holidays') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Remaining Paid Leave') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Paid Leave Acquired') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Paid Leave Taken') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Leave Balance') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Unpaid Absence Days') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Paid Leave Dates') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Remark') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Month') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Year') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @if($work_days_repports)
                                    <tr class="hover:bg-slate-50/70">
                                        <td data-label="{{ __('messages.Internal Work Days') }}" data-span="2" class="editable px-4 py-2.5 text-sm text-slate-900 font-medium" data-id="{{$work_days_repports->id}}" data-column="days_worked">
                                            @php
                                                $totalMinutes = $totalHours * 60;
                                                $hours = floor($totalMinutes / 60);
                                                $minutes = $totalMinutes % 60;
                                            @endphp
                                            {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m - <b>{{$work_days_repports->days_worked}} jrs</b>
                                        </td>
                                        <td data-label="{{ __('messages.Sick Days') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="sick_days">
                                            {{$work_days_repports->sick_days}}
                                        </td>
                                        <td data-label="{{ __('messages.Holidays') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="holidays">
                                            {{$work_days_repports->holidays}}
                                        </td>
                                        <td data-label="{{ __('messages.Remaining Paid Leave') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave">
                                            {{$work_days_repports->remaining_days_of_paid_leave}}
                                        </td>
                                        <td data-label="{{ __('messages.Paid Leave Acquired') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired">
                                            {{$work_days_repports->number_of_days_of_paid_leave_acquired}}
                                        </td>
                                        <td data-label="{{ __('messages.Paid Leave Taken') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken">
                                            {{$work_days_repports->number_of_days_of_paid_leave_taken}}
                                        </td>
                                        <td data-label="{{ __('messages.Leave Balance') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="holiday_balance">
                                            {{$work_days_repports->holiday_balance}}
                                        </td>
                                        <td data-label="{{ __('messages.Unpaid Absence Days') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence">
                                            {{$work_days_repports->days_of_unpaid_absence}}
                                        </td>
                                        <td data-label="{{ __('messages.Paid Leave Dates') }}" data-span="2" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays">
                                            {{$work_days_repports->dates_of_holidays}}
                                        </td>
                                        <td data-label="{{ __('messages.Remark') }}" data-span="2" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="remarque">
                                            {{$work_days_repports->remarque}}
                                        </td>
                                        <td data-label="{{ __('messages.Month') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="month">
                                            {{$work_days_repports->month}}
                                        </td>
                                        <td data-label="{{ __('messages.Year') }}" class="editable px-4 py-2.5 text-sm text-slate-700" data-id="{{$work_days_repports->id}}" data-column="year">
                                            {{$work_days_repports->year}}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Card 5: Teams -->
                <div class="dt-card overflow-hidden">
                    <!-- Card Header -->
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#2D7080]/10 text-[#2D7080] ring-1 ring-[#2D7080]/20 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a4 4 0 11-4-4" />
                                </svg>
                            </div>
                            <h5 class="dt-section-title truncate">{{ __('messages.Teams') }}</h5>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                data-target="employeeTeamsBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <!-- Card Body -->
                    <div id="employeeTeamsBody" class="hidden">
                    <div class="px-5 sm:px-6 py-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                            @php
                                $team_id = App\Models\team_members::where('employee_id', $employee->id)->pluck('team_id')->unique();
                                $teams = App\Models\Teams::whereIn('id', $team_id)->get();
                            @endphp
                            @foreach($teams as $team)
                            <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                                <div class="mb-2">
                                    <h5 class="text-[14px] font-bold text-[#0B2638] capitalize truncate">{{$team->name}}</h5>
                                    <span class="text-[12px] font-medium text-slate-500">{{ __('messages.Team Members:') }}</span>
                                </div>
                                <ul class="space-y-1">
                                    @foreach(App\Models\team_members::where('team_id', $team->id)->get() as $team_member)
                                    <li class="flex items-center justify-between gap-2 py-2 border-b border-slate-100 last:border-b-0">
                                        <div class="flex items-center flex-1 min-w-0">
                                            @php
                                                $member_employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                                $employee_user = App\Models\User::where('id', $member_employee->user_id)->first();
                                            @endphp
                                            @if($member_employee->image)
                                            <img class="w-8 h-8 rounded-full mr-3 flex-shrink-0 object-cover ring-1 ring-slate-200"
                                                src="{{ Storage::disk('public')->url('employee_images/' . $member_employee->image) }}"
                                                alt="employee Image">
                                            @else
                                            <div class="w-8 h-8 rounded-full mr-3 flex items-center justify-center text-white text-sm font-semibold flex-shrink-0"
                                                style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                                                <span>{{ strtoupper(substr($team_member->employee_name, 0, 1)) }}</span>
                                            </div>
                                            @endif
                                            <div class="min-w-0">
                                                <h6 class="text-[13px] font-semibold text-slate-900 truncate">
                                                    @php $memberRoute = '#'; @endphp
                                                    @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View employee'))
                                                        @php $memberRoute = route('admin.employees.show', $team_member->employee_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View employee'))
                                                        @php $memberRoute = route('Responsable_de_projet.employees.show', $team_member->employee_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View employee'))
                                                        @php $memberRoute = route('employee.employees.show', $team_member->employee_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire') && Auth::user()->can('View employee'))
                                                        @php $memberRoute = route('Responsable_gestionnaire.employees.show', $team_member->employee_id); @endphp
                                                    @endif

                                                    @if($memberRoute != '#')
                                                        <a href="{{ $memberRoute }}" class="hover:text-[#2D7080]">{{$team_member->employee_name}}</a>
                                                    @else
                                                        {{$team_member->employee_name}}
                                                    @endif
                                                </h6>
                                                @if($employee_user->is_loggedIn == 0)
                                                    <span class="dt-badge dt-badge-neutral mt-1"><span class="dt-dot"></span>{{ __('messages.Off Work') }}</span>
                                                @elseif($employee_user->is_loggedIn == 1)
                                                    <span class="dt-badge dt-badge-success mt-1"><span class="dt-dot"></span>{{ __('messages.On Work') }}</span>
                                                @else
                                                    <span class="dt-badge dt-badge-warning mt-1"><span class="dt-dot"></span>{{ __('messages.On Pause') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($memberRoute != '#')
                                        <a href="{{ $memberRoute }}" class="flex-shrink-0 px-3 py-1 text-xs font-semibold text-[#2D7080] border border-[#2D7080]/30 rounded-full hover:bg-[#2D7080]/5 transition-colors">
                                            {{ __('messages.View') }}
                                        </a>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Card 6: Projects -->
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" />
                                </svg>
                            </div>
                            <h2 class="dt-section-title truncate">{{ __('messages.Projects Section') }}</h2>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                data-target="employeeProjectsBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <div id="employeeProjectsBody" class="hidden">
                    <div class="px-3 sm:px-4 py-4">
                        <div class="overflow-x-auto table-responsive rounded-xl border border-slate-200">
                            @php
                                $team_project_id = App\Models\project_teams::whereIn('team_id', $team_id)->pluck('project_id');
                                $projects_tasks_id = App\Models\employees_tasks::where('employee_id', $employee->id)->pluck('task_id');
                                $projects_tasks = App\Models\Tasks::whereIn('id', $projects_tasks_id)->pluck('project_id');
                                $projects1 = App\Models\Project::whereIn('id', $projects_tasks)->get();
                                $projects = App\Models\Project::whereIn('id', $team_project_id)->get();
                                $combined_project_ids = $projects1->concat($projects)->unique();
                                $projectsByClient = $combined_project_ids->groupBy('client_id');
                            @endphp
                            <table id="employeeProjectsTable" class="min-w-full table-auto">
                                <thead class="bg-slate-50/80 dt-table-head">
                                    <tr class="border-b border-slate-200">
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Client / Project') }}</th>
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Budget') }}</th>
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Duration') }}</th>
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Progress') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($projectsByClient as $client_id => $clientProjects)
                                        @php $client = filled($client_id) ? App\Models\Client::find($client_id) : null; @endphp
                                        <tr class="bg-slate-100/70 emp-group-row employee-client-group cursor-pointer select-none"
                                            data-client-group="{{ $client_id }}" aria-expanded="true">
                                            <td colspan="4" class="px-4 py-2.5">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h6 class="text-[13px] font-bold text-[#0B2638]">
                                                    @php $clientRoute = '#'; @endphp
                                                    @if(filled($client_id) && Auth::user()->hasRole('Admin') && Auth::user()->can('View client'))
                                                        @php $clientRoute = route('admin.clients.show', $client_id); @endphp
                                                    @elseif(filled($client_id) && Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View client'))
                                                        @php $clientRoute = route('Responsable_de_projet.clients.show', $client_id); @endphp
                                                    @elseif(filled($client_id) && Auth::user()->hasRole('Employee') && Auth::user()->can('View client'))
                                                        @php $clientRoute = route('employee.clients.show', $client_id); @endphp
                                                    @endif

                                                    @if($clientRoute != '#')
                                                        <a href="{{ $clientRoute }}" class="hover:text-[#2D7080]">{{ $client->name ?? __('messages.Unknown Client') }}</a>
                                                    @else
                                                        {{ $client->name ?? __('messages.Unknown Client') }}
                                                    @endif
                                                    </h6>

                                                    <svg class="employee-group-chevron w-4 h-4 text-slate-400 transition-transform flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                                    </svg>
                                                </div>
                                            </td>
                                        </tr>
                                        @foreach($clientProjects as $project)
                                        @continue($project->archived == 1)
                                        <tr class="hover:bg-slate-50/70 employee-client-item" data-client-parent="{{ $client_id }}">
                                            <td data-label="{{ __('messages.Client / Project') }}" data-span="2" class="px-4 py-2.5 text-sm font-medium">
                                                @php $projectRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                                    @php $projectRoute = route('admin.projects.show', $project->id); @endphp
                                                @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                                    @php $projectRoute = route('Responsable_de_projet.projects.show', $project->id); @endphp
                                                @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                                    @php $projectRoute = route('employee.projects.show', $project->id); @endphp
                                                @endif

                                                @if($projectRoute != '#')
                                                    <a href="{{ $projectRoute }}" class="text-[#2D7080] hover:text-[#0B2638] font-medium">{{$project->name}}</a>
                                                @else
                                                    <span class="text-slate-700">{{$project->name}}</span>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('messages.Budget') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700">€{{$project->budget}}</td>
                                            @php
                                                $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->where('employee_id', $employee->id)->sum('duration');
                                                $hours = floor(abs($totalduration) / 60);
                                                $minutes = abs($totalduration) % 60;
                                                $estimatedMinutes = $project->estimated_hours * 60;
                                                $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                                                $formattedProgressPercentage = number_format($progressPercentage, 2);
                                            @endphp
                                            <td data-label="{{ __('messages.Duration') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700">{{$hours}}h:{{$minutes}}m</td>
                                            <td data-label="{{ __('messages.Progress') }}" data-span="2" class="px-4 py-2.5 text-sm text-slate-700 min-w-[150px]">
                                                <div class="w-full bg-slate-200 rounded-full h-3 relative">
                                                    @php
                                                        $progressColor = 'bg-green-500';
                                                        if ($progressPercentage >= 75) $progressColor = 'bg-red-500';
                                                        elseif ($progressPercentage >= 50) $progressColor = 'bg-yellow-500';
                                                    @endphp
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <span class="text-xs font-semibold {{ $progressPercentage > 40 ? 'text-white' : 'text-slate-700' }}">{{$formattedProgressPercentage}}%</span>
                                                    </div>
                                                    <div class="{{ $progressColor }} h-3 rounded-full" style="width: {{min($formattedProgressPercentage, 100)}}%"></div>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                </div>

                <!-- Card 7: Tasks -->
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="flex items-center justify-center w-9 h-9 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                            </div>
                            <h2 class="dt-section-title truncate">{{ __('messages.Tasks Section') }}</h2>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-1.5 text-[13px] font-semibold text-slate-400 hover:text-[#0B2638] transition-colors flex-shrink-0"
                                data-target="employeeTasksBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-4 h-4 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <div id="employeeTasksBody" class="hidden">
                    <div class="px-3 sm:px-4 py-4">
                        <div class="overflow-x-auto table-responsive rounded-xl border border-slate-200">
                        @php
                            $tasks_collection = collect([]);
                            $employee_tasks_ids = App\Models\employees_tasks::where('employee_id', $employee->id)->get();
                            foreach($employee_tasks_ids as $employee_task_id){
                            $task = App\Models\Tasks::where('id', $employee_task_id->task_id)->first();
                            if($task){
                                $project = App\Models\Project::find($task->project_id);
                                // Only include tasks from non-archived projects
                                if($project && $project->archived == 0){
                                    $tasks_collection->push($task);
                                        }
                                    }
                                }

                                // Group tasks by project
                                $tasksByProject = $tasks_collection->groupBy('project_id');
                        @endphp
                            <table id="employeeTasksTable" class="min-w-full table-auto">
                                <thead class="bg-slate-50/80 dt-table-head">
                                    <tr class="border-b border-slate-200">
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Project / Task') }}</th>
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Duration') }}</th>
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Est. Hours') }}</th>
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Difference') }}</th>
                                        <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Progress') }}</th>
                                        <th class="px-4 py-3 text-center whitespace-nowrap">{{ __('messages.Chrono') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($tasksByProject as $project_id => $projectTasks)
                                        @php $project = App\Models\Project::find($project_id); @endphp
                                        @if($project) <!-- Added check in case project is null -->
                                        <tr class="bg-slate-100/70 emp-group-row employee-project-group cursor-pointer select-none"
                                            data-project-group="{{ $project_id }}" aria-expanded="true">
                                            <td colspan="6" class="px-4 py-2.5">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h6 class="text-[13px] font-bold text-[#0B2638]">
                                                    @php $projectRoute = '#'; @endphp
                                                    @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                                        @php $projectRoute = route('admin.projects.show', $project_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                                        @php $projectRoute = route('Responsable_de_projet.projects.show', $project_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                                        @php $projectRoute = route('employee.projects.show', $project_id); @endphp
                                                    @endif

                                                    @if($projectRoute != '#')
                                                        <a href="{{ $projectRoute }}" class="hover:text-[#2D7080]">{{$project->name}}</a>
                                                    @else
                                                        {{$project->name}}
                                                    @endif
                                                    </h6>

                                                    <svg class="employee-group-chevron w-4 h-4 text-slate-400 transition-transform flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                                    </svg>
                                                </div>
                                            </td>
                                        </tr>
                                        @foreach($projectTasks as $task)
                                        <tr class="hover:bg-slate-50/70 employee-project-item" data-project-parent="{{ $project_id }}">
                                            <td data-label="{{ __('messages.Project / Task') }}" data-span="2" class="px-4 py-2.5 text-sm font-medium">
                                                @php $taskRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @php $taskRoute = route('admin.tasks.show', $task->id); @endphp
                                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                    @php $taskRoute = route('Responsable_de_projet.tasks.show', $task->id); @endphp
                                                @elseif(Auth::user()->hasRole('Employee'))
                                                    @php $taskRoute = route('employee.tasks.show', $task->id); @endphp
                                                @endif
                                                <a href="{{ $taskRoute }}" class="text-[#2D7080] hover:text-[#0B2638] font-medium">{{$task->title}}</a>
                                            </td>
                                            @php
                                                $tasktotalduration = App\Models\tasks_timer::where('task_id', $task->id)->where('employee_id', $employee->id)->sum('duration');
                                                $taskhours = floor($tasktotalduration / 60);
                                                $taskminutes = $tasktotalduration % 60;
                                            @endphp
                                            <td data-label="{{ __('messages.Duration') }}" class="px-4 py-2.5 text-sm text-slate-700">
                                                @php $durationRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @php $durationRoute = route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]); @endphp
                                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                    @php $durationRoute = route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]); @endphp
                                                @elseif(Auth::user()->hasRole('Employee'))
                                                    @php $durationRoute = route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]); @endphp
                                                @endif
                                                <a href="{{ $durationRoute }}" class="text-[#2D7080] hover:text-[#0B2638] font-medium">
                                                    {{$taskhours}}h:{{$taskminutes}}m
                                                </a>
                                            </td>
                                            <td data-label="{{ __('messages.Est. Hours') }}" class="px-4 py-2.5 whitespace-nowrap text-sm text-slate-700">{{$task->estimated_hours}}h</td>
                                            @php
                                                $taskestimated = ($task->estimated_hours ?? 0) * 60;
                                                $taskconsumed = ($task->consumed_hours ?? 0) * 60;
                                                $taskdifference = $taskestimated - $taskconsumed - $tasktotalduration;
                                                $hours = floor(abs($taskdifference) / 60);
                                                $minutes = abs($taskdifference) % 60;
                                                $taskestimatedMinutes = max(0, (($task->estimated_hours ?? 0) - ($task->consumed_hours ?? 0)) * 60);
                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                            @endphp
                                            <td data-label="{{ __('messages.Difference') }}" class="px-4 py-2.5 whitespace-nowrap text-sm font-medium">
                                                @if($task->estimated_hours !== null)
                                                    @if($taskdifference > 0)
                                                    <span class="text-green-600">+{{$hours}}h:{{$minutes}}m</span>
                                                    @elseif($taskdifference < 0)
                                                    <span class="text-red-600">-{{$hours}}h:{{$minutes}}m</span>
                                                    @else
                                                    <span class="text-blue-600">{{$hours}}h:{{$minutes}}m</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td data-label="{{ __('messages.Progress') }}" data-span="2" class="px-4 py-2.5 text-sm text-slate-700 min-w-[150px]">
                                                <div class="w-full bg-slate-200 rounded-full h-3 relative">
                                                    @php
                                                        $taskProgressColor = 'bg-green-500';
                                                        if ($taskprogressPercentage >= 75) $taskProgressColor = 'bg-red-500';
                                                        elseif ($taskprogressPercentage >= 50) $taskProgressColor = 'bg-yellow-500';
                                                    @endphp
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <span class="text-xs font-semibold {{ $taskprogressPercentage > 40 ? 'text-white' : 'text-slate-700' }}">{{$taskformattedProgressPercentage}}%</span>
                                                    </div>
                                                    <div class="{{ $taskProgressColor }} h-3 rounded-full" style="width: {{min($taskformattedProgressPercentage, 100)}}%"></div>
                                                </div>
                                            </td>
                                            <td data-label="{{ __('messages.Chrono') }}" data-span="2" class="px-4 py-2.5 text-sm text-slate-700">
                                                @php
                                                    // Determine route prefix based on current user's role
                                                    $taskRoutePrefix = 'admin.'; // Default for Admin / SuperAdmin
                                                    if (Auth::user()->hasRole('Responsable de projet')) {
                                                        $taskRoutePrefix = 'Responsable_de_projet.';
                                                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                        $taskRoutePrefix = 'Responsable_gestionnaire.';
                                                    } elseif (Auth::user()->hasRole('Employee')) {
                                                        $taskRoutePrefix = 'employee.';
                                                    }

                                                    // Link employees_tasks row for this employee & task
                                                    $employeeTask = App\Models\employees_tasks::where('employee_id', $employee->id)
                                                        ->where('task_id', $task->id)
                                                        ->first();

                                                    $taskTimer = null;
                                                    if ($employeeTask) {
                                                        $taskTimer = App\Models\tasks_timer::where('task_employee_id', $employeeTask->id)
                                                            ->whereNull('end_date')
                                                            ->first();
                                                    }
                                                @endphp

                                                @php
                                                    $current_user_employee_id = \App\Models\Employees::where('user_id', Auth::user()->id)->first()?->id;
                                                    $is_own_task = ($employee->id == $current_user_employee_id);
                                                @endphp
                                                <div class="emp-task-actions">
                                                @if($employeeTask && (Auth::user()->hasRole('Admin') || Auth::user()->hasRole('SuperAdmin') || Auth::user()->hasRole('super_admin')))
                                                    @if(!$taskTimer)
                                                        <form class="flex flex-col gap-1" action="{{ route($taskRoutePrefix . 'tasks.start', ['tasks' => $employeeTask->id, 'employee_id' => $employee->id]) }}" method="POST"
                                                            data-chrono-start data-chrono-employee="{{ $employee->id }}"
                                                            data-chrono-emp-task="{{ $employeeTask->id }}" data-chrono-task="{{ $task->id }}">
                                                            @csrf
                                                            <textarea
                                                                name="comment"
                                                                class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-xs py-1.5 px-2.5"
                                                                placeholder="{{ __('messages.Description') }}"
                                                                rows="1"
                                                            ></textarea>
                                                            <button
                                                                type="submit"
                                                                class="mt-1 inline-flex items-center justify-center px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700"
                                                            >
                                                                {{ __('messages.Start') }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form
                                                            class="flex items-center justify-center @if($is_own_task) stop-form @endif"
                                                            action="{{ route($taskRoutePrefix . 'tasks.stop', ['tasks' => $employeeTask->id, 'employee_id' => $employee->id]) }}"
                                                            method="POST"
                                                            data-chrono-stop data-chrono-employee="{{ $employee->id }}"
                                                            data-chrono-emp-task="{{ $employeeTask->id }}" data-chrono-task="{{ $task->id }}"
                                                        >
                                                            @csrf
                                                            @if($is_own_task)
                                                                <input type="hidden" name="stop_comment" class="stop-comment-field">
                                                            @endif
                                                            <button
                                                                type="submit"
                                                                class="@if($is_own_task) stop-button @endif inline-flex items-center px-3 py-1.5 text-xs font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700"
                                                                @if($is_own_task) data-task-id="{{ $task->id }}" @endif
                                                            >
                                                                {{ __('messages.Stop') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-slate-400">-</span>
                                                @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif <!-- End project check -->
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>
                </div>

            </div> <!-- End of space-y-5 wrapper -->

        </main>
    </div>

    <div id="contactModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-5 sm:p-6 transform transition-all duration-300">

            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="text-[15px] font-bold text-[#0B2638]">{{ __('messages.Send Email to') }} {{ $employee->name }}</h3>
                <button type="button" id="closeEmailModalBtn" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-4">
                <form id="emailForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <div class="space-y-4">
                        <div>
                    <label for="subject" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Subject') }}</label>
                            <input type="text" class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" id="subject" name="subject" required>
                        </div>
                        <div>
                    <label for="body" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Message') }}</label>
                            <textarea class="w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" id="body" name="body" rows="6" required>Bonjour,
Veuillez trouver ci-joint le document, pour votre information.

N'hésitez pas à me contacter si vous avez la moindre question.

Bien cordialement,

Pour Stabel</textarea>
                        </div>
                        <div>
                            <label for="attachments" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Attachments') }}</label>
                            <input type="file" class="w-full text-sm text-slate-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-[#2D7080]/10 file:text-[#2D7080]
                                hover:file:bg-[#2D7080]/20"
                                id="attachments" name="attachments[]" multiple>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-2">
                <button type="button" id="cancelEmailModalBtn" class="dt-btn dt-btn-ghost">
                    {{ __('messages.Close') }}
                </button>
                <button type="button" class="dt-btn dt-btn-teal" id="sendEmailBtn">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    {{ __('messages.Send Email') }}
                </button>
            </div>
        </div>
    </div>

    @include('dashboard_layouts.scripts')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    @can('Update employee')
    <script>
        $(document).ready(function(){
            // --- Script for Employee Info Inputs ---
            // Use delegation for inputs added dynamically if any, or just direct binding
            $(document).on("dblclick", ".editable", function(){
                // Check if it's an input or a td
                if ($(this).is('input')) {
                    $(this).prop('readonly', false).focus();
                }
            });

            $(document).on("blur", ".editable", function(){
                 if ($(this).is('input')) {
                    $(this).prop('readonly', true);
                    var newContent = $(this).val();
                    var employeeId = $(this).data('id');
                    var columnName = $(this).attr('name');

                    var updatedUrl = '';
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        updatedUrl = '/Responsable_de_projet/employees/updateEmployee';
                    @elseif(Auth::user()->hasRole('Admin'))
                        updatedUrl = '/admin/employees/updateEmployee';
                    @elseif(Auth::user()->hasRole('Employee'))
                        updatedUrl = '/employee/employees/updateEmployee';
                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        updatedUrl = '/Responsable_gestionnaire/employees/updateEmployee';
                    @endif

                    $.ajax({
                        url: updatedUrl,
                        type: 'post',
                        data: {
                            content: newContent,
                            id: employeeId,
                            column: columnName,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(response) {
                            location.reload();
                        }
                    });
                }
            });

            $(".editable-select").change(function(){
                    var newContent = $(this).val();
                    var employeeId = $(this).data('id');
                    var columnName = $(this).attr('name');

                    var updatedUrl = '';

                    @if(Auth::user()->hasRole('Responsable de projet'))
                        updatedUrl = '/Responsable_de_projet/employees/updateEmployee';
                    @elseif(Auth::user()->hasRole('Admin'))
                        updatedUrl = '/admin/employees/updateEmployee';
                    @elseif(Auth::user()->hasRole('Employee'))
                        updatedUrl = '/employee/employees/updateEmployee';
                    @endif

                    $.ajax({
                        url: updatedUrl, // your update route
                        type: 'post',
                        data: {
                            content: newContent,
                            id: employeeId,
                            column: columnName,
                            _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                        },
                        success: function(response) {
                            location.reload();
                        },
                        error: function(response) {
                            location.reload();
                        }
                    });
                });

            // --- Script for Work Days Table ---
            $("#editableTable").on("dblclick", ".editable", function(){
                if ($(this).is('td')) { // Only apply to table cells
                    var originalContent = $(this).text().trim();
                    // This logic is slightly different for the first cell
                    if ($(this).data('column') === 'days_worked') {
                        originalContent = originalContent.split(' - ')[1].replace(' jrs', '');
                    }

                    var width = $(this).width();
                    $(this).html("<input type='text' value='" + originalContent + "' class='w-full border-gray-300 rounded-md shadow-sm' style='min-width: " + width + "px;' />");
                    var input = $(this).children().first();
                    input.focus();

                    if(input[0].setSelectionRange) {
                        input[0].setSelectionRange(0, 0);
                    }

                    input.blur(function(){
                        var newContent = $(this).val();
                        var $parentCell = $(this).parent();
                        var workDays_Id = $parentCell.data('id');
                        var columnName = $parentCell.data('column');

                        $parentCell.text(newContent); // Temporarily set text

                        var updatedUrl = '';
                        @if(Auth::user()->hasRole('Responsable de projet'))
                            updatedUrl = '/Responsable_de_projet/workdaysrepports/updateWorkdaysRepport';
                        @elseif(Auth::user()->hasRole('Admin'))
                            updatedUrl = '/admin/workdaysrepports/updateWorkdaysRepport';
                        @elseif(Auth::user()->hasRole('Employee'))
                            updatedUrl = '/employee/workdaysrepports/updateWorkdaysRepport';
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            updatedUrl = '/Responsable_gestionnaire/workdaysrepports/updateWorkdaysRepport';
                        @endif

                        $.ajax({
                            url: updatedUrl,
                            type: 'post',
                            data: {
                                content: newContent,
                                id: workDays_Id,
                                column: columnName,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating content: " + error);
                                // Revert on error - need to refetch original content logic
                                location.reload(); // Simple solution: just reload
                            }
                        });
                    });

                    input.keypress(function(e) {
                        if(e.which == 13) { $(this).blur(); }
                    });
                }
            });
        });
    </script>
    @endcan

    {{-- Send Email Modal Script --}}
    <script>
        $(document).ready(function() {
            // --- AJAX for Sending Email ---
            $('#sendEmailBtn').click(function() {
                var formData = new FormData($('#emailForm')[0]);

                var emailUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    emailUrl = '{{ route("Responsable_de_projet.employees.send-email") }}';
                @elseif(Auth::user()->hasRole('Admin'))
                    emailUrl = '{{ route("admin.employees.send-email") }}';
                @elseif(Auth::user()->hasRole('Employee'))
                    emailUrl = '{{ route("employee.employees.send-email") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    emailUrl = '{{ route("Responsable_gestionnaire.employees.send-email") }}';
                @endif

                $.ajax({
                    url: emailUrl,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        $('#contactModal').addClass('hidden');
                        // Use a custom modal for success instead of alert
                        alert('{{ __('messages.Email sent successfully!') }}');
                    },
                    error: function(xhr) {
                        // Use a custom modal for error instead of alert
                        alert('{{ __('messages.Error sending email. Please try again.') }}');
                    }
                });
            });

            // --- JS for Opening/Closing Tailwind Modal ---
            const openBtn = document.getElementById('openEmailModalBtn');
            const modal = document.getElementById('contactModal');
            const closeBtn = document.getElementById('closeEmailModalBtn');
            const cancelBtn = document.getElementById('cancelEmailModalBtn');

            if (openBtn && modal && closeBtn && cancelBtn) {
                const closeModal = () => modal.classList.add('hidden');

                openBtn.addEventListener('click', () => modal.classList.remove('hidden'));
                closeBtn.addEventListener('click', closeModal);
                cancelBtn.addEventListener('click', closeModal);

                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }

            // --- Collapsible sections (mobile collapsed, desktop open) ---
            const toggles = Array.from(document.querySelectorAll('.section-toggle'));

            const setExpanded = (btn, expanded) => {
                const targetId = btn.getAttribute('data-target');
                const body = document.getElementById(targetId);
                const chevron = btn.querySelector('.section-chevron');
                const label = btn.querySelector('span');

                if (!body) return;

                btn.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                body.classList.toggle('hidden', !expanded);
                if (chevron) chevron.classList.toggle('rotate-180', expanded);
                if (label) label.textContent = expanded ? "{{ __('messages.Hide') }}" : "{{ __('messages.Show') }}";
            };

            const initForViewport = () => {
                const isMobile = window.matchMedia('(max-width: 1024px)').matches;
                toggles.forEach((btn) => setExpanded(btn, !isMobile));
            };

            toggles.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const expanded = btn.getAttribute('aria-expanded') === 'true';
                    setExpanded(btn, !expanded);
                });
            });

            // --- Nested table accordions (Projects by Client, Tasks by Project) ---
            const setGroupExpanded = (groupRow, expanded, type) => {
                groupRow.setAttribute('aria-expanded', expanded ? 'true' : 'false');
                const chevron = groupRow.querySelector('.employee-group-chevron');
                if (chevron) chevron.classList.toggle('rotate-180', expanded);

                if (type === 'client') {
                    const id = groupRow.getAttribute('data-client-group');
                    document
                        .querySelectorAll(`tr[data-client-parent="${id}"]`)
                        .forEach((row) => row.classList.toggle('hidden', !expanded));
                } else if (type === 'project') {
                    const id = groupRow.getAttribute('data-project-group');
                    document
                        .querySelectorAll(`tr[data-project-parent="${id}"]`)
                        .forEach((row) => row.classList.toggle('hidden', !expanded));
                }
            };

            const initNestedAccordionsForViewport = () => {
                const isMobile = window.matchMedia('(max-width: 1024px)').matches;

                document.querySelectorAll('tr.employee-client-group').forEach((row) => {
                    setGroupExpanded(row, !isMobile, 'client');
                    row.addEventListener('click', (e) => {
                        // Don't toggle when user clicks the actual link
                        if (e.target && e.target.closest('a')) return;
                        const expanded = row.getAttribute('aria-expanded') === 'true';
                        setGroupExpanded(row, !expanded, 'client');
                    });
                });

                document.querySelectorAll('tr.employee-project-group').forEach((row) => {
                    setGroupExpanded(row, !isMobile, 'project');
                    row.addEventListener('click', (e) => {
                        if (e.target && e.target.closest('a')) return;
                        const expanded = row.getAttribute('aria-expanded') === 'true';
                        setGroupExpanded(row, !expanded, 'project');
                    });
                });
            };

            initForViewport();
            initNestedAccordionsForViewport();
            const mq = window.matchMedia('(max-width: 1024px)');
            if (mq.addEventListener) mq.addEventListener('change', () => { initForViewport(); initNestedAccordionsForViewport(); });
            else mq.addListener(() => { initForViewport(); initNestedAccordionsForViewport(); });
        });
    </script>
    {{-- Stop Comment Modal --}}
    <x-chrono-stop-modal />

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
$(document).ready(function() {
$(document).on('click', '.stop-button', function(event) {
                event.preventDefault(); event.stopPropagation();
                const form = this.closest('.stop-form');
                if (!form) return;
                const action = form.getAttribute('action');
                const token = form.querySelector('input[name="_token"]')?.value;
                window.ChronoStopModal.open(action, async function(comment) {
                    form.querySelector('.stop-comment-field').value = comment;
                    HTMLFormElement.prototype.submit.call(form);
                });
            });
});
</script>
    @livewireScripts
</body>
</html>
