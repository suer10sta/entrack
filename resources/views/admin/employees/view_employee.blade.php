<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Employee Profile') }} - DalyTrac</title>
    @vite('resources/css/app.css')
    
    {{-- This CSS block styles the "editable" fields to look like text until double-clicked --}}
    <style>
        .editable[readonly] {
            border-color: #E5E7EB; /* gray-200 */
            background-color: #F9FAFB; /* gray-50 */
            padding-left: 0.75rem; /* Added padding to align with non-readonly */
            padding-right: 0.75rem; /* Added padding to align with non-readonly */
            cursor: pointer;
            font-weight: 500;
            color: #111827; /* text-gray-900 */
        }
        .editable[readonly]:hover {
            color: #2563EB; /* text-blue-600 */
            background-color: #F3F4F6; /* gray-100 */
        }
        .editable:not([readonly]) {
            border-color: #2563EB !important;
            box-shadow: 0 0 0 2px #BFDBFE !important; /* ring-blue-200 */
            background-color: #ffffff !important;
        }
        
        /* Style for editable table cells */
        td.editable {
            cursor: pointer;
        }
        td.editable:hover {
            background-color: #f9fafb; /* gray-50 */
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
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
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
        <!-- CHANGED: main padding from p-6 to p-8 -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">
            
            <div class="mb-6">
                <div class="flex items-center gap-2 mb-1">
                    <h1 class="text-2xl font-bold text-gray-900">{{ __('messages.Employee Profile') }}</h1>
                    @if(!empty($employee->archived))
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                            Archived
                        </span>
                    @endif
                </div>
                <p class="text-sm text-gray-600">{{ __('messages.View and manage employee information') }}</p>
            </div>

            <!-- This div stacks all the new cards vertically -->
            <div class="space-y-8">

                <!-- Info Cards Grid -->
                <!-- CHANGED: Added mb-8 for spacing below the grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Card 1: Employee Infos -->
                    <!-- CHANGED: Applied new card style -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div class="flex items-center">
                                <div>
                                    @php
                                        $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', 
                                        '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F']; 
                                    @endphp 
                                    @if($employee->image)
                                        <img class="w-12 h-12 rounded-full mr-3"  src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}" alt="employee Image">
                                    @else
                                    <div class="w-12 h-12 rounded-full mr-3 flex items-center justify-center text-white"
                                        style="background-color: {{ $colors[0] }};">
                                        <span class="font-semibold text-xl">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                    </div>
                                    @endif
                                </div>
                                <div>
                                    <div class="flex items-center">
                                        <span class="text-xl mr-2">👤</span>
                                        <h5 class="text-lg font-semibold text-gray-900 mb-0">{{ __('messages.Employee Infos') }}</h5>
                                    </div>
                                    <p class="text-sm text-gray-500 ml-8">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    data-target="employeeInfosBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                                <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <!-- Card Body -->
                        <div id="employeeInfosBody" class="hidden">
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Name') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="name"  type="text"
                                            name="name" value="{{ $employee->name }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Login Email') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="email"  type="text"
                                            name="email" value="{{ $employee->email }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Phone Number') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="phoneNumber"  type="text"
                                            name="phoneNumber" value="{{ $employee->phoneNumber }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Contact Email') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="contact_email"  type="text"
                                            name="contact_email" value="{{ $employee->contact_email }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Age') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="age"  type="text"
                                            name="age" value="{{ $employee->age }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Password') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="password"  type="text"
                                            name="password" value="{{ $employee->password }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('messages.Work Hours Limit') }}</label>
                                        <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"    
                                            data-id="{{$employee->id}}" data-column="workhours_limit"  type="text"
                                            name="workHours_limit" value="{{ $employee->workHours_limit }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">{{ __('messages.Timezone') }}</label>
                                        <select class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable-select" 
                                            data-id="{{$employee->id}}" data-column="timezone" type="text"
                                            name="timezone">
                                            @foreach (timezone_identifiers_list() as $tz)
                                                <option value="{{ $tz }}" @if($user->timezone == $tz) selected @endif>
                                                    {{ $tz }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">&nbsp;</label>
                                        <button type="button" id="openEmailModalBtn" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            {{ __('messages.Send Email') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- Card 2: Professional Infos -->
                    <!-- CHANGED: Applied new card style -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">💼</span>
                                <div>
                                    <h5 class="text-lg font-semibold text-gray-900 mb-0">{{ __('messages.Professional Infos') }}</h5>
                                    <p class="text-sm text-gray-500">{{ __('messages.Mandatory informations') }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    data-target="employeeProfessionalBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                                <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <!-- Card Body -->
                        <div id="employeeProfessionalBody" class="hidden">
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.CNSS Number') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="cnss" type="text"
                                            name="cnss" value="{{ $employee->cnss }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Bank Account') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="bankAccountNumber" type="text"
                                            name="bankAccountNumber" value="{{ $employee->bankAccountNumber }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Release Date') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="Date_Release" type="text"
                                            name="Date_Release" value="{{ $employee->Date_Release }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Salary') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="salary" type="text"
                                            name="salary" value="{{ $employee->salary }}€" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Cost Per Hour') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="cost_per_hour" type="text"
                                            name="cost_per_hour" value="{{ $employee->cost_per_hour }}€" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Cotisation CNSS') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="Cotisation_CNSS" 
                                            type="text" name="Cotisation_CNSS"
                                            value="{{ $employee->Cotisation_CNSS }}€" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Cotisation AMO') }}</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
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
                    <!-- CHANGED: Applied new card style -->
                    <div class="rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-gray-200 flex items-start justify-between gap-3">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">🔐</span>
                                <div>
                                    <h5 class="text-lg font-semibold text-gray-900 mb-0">{{ __('messages.Roles') }}</h5>
                                    <p class="text-sm text-gray-500">{{ __('messages.Roles information') }}</p>
                                </div>
                            </div>
                            <button type="button"
                                    class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                    data-target="employeeRolesBody" aria-expanded="false">
                                <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                                <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                </svg>
                            </button>
                        </div>
                        <!-- Card Body -->
                        <div id="employeeRolesBody" class="hidden">
                        <div class="p-6">
                            <div class="space-y-3">
                                @foreach($role_name as $role)
                                <div class="flex items-center">
                                    <input id="vue-checkbox-{{ $role }}" name="roles[]"
                                        type="checkbox" value="{{ $role }}" disabled
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        @if($user->hasRole($role)) checked @endif>
                                    <label for="vue-checkbox-{{ $role }}" class="ml-2 text-sm text-gray-700">
                                        {{ $role }}
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            <div class="mt-6">
                                @php
                                    $editRolesRoute = '#';
                                    if(Auth::user()->hasRole('Admin')) $editRolesRoute = route('admin.employees.edit', $employee->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet')) $editRolesRoute = route('Responsable_de_projet.employees.edit', $employee->id);
                                    elseif(Auth::user()->hasRole('Employee')) $editRolesRoute = route('employee.employees.edit', $employee->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $editRolesRoute = route('Responsable_gestionnaire.employees.edit', $employee->id);
                                @endphp
                                
                                @can('Edit employee roles')
                                <a href="{{ $editRolesRoute }}" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">{{ __('messages.Edit roles') }}</a>
                                @endcan
                            </div>
                        </div>
                        </div>
                    </div>
                </div> <!-- End of Info Cards Grid -->

                <!-- Card 4: Work Days Reports -->
                <!-- CHANGED: Applied new card style and added mb-8 -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Work Days Reports') }}</h2>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                data-target="employeeWorkDaysBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <div id="employeeWorkDaysBody" class="hidden">
                    <!-- CHANGED: Added p-6 wrapper for table -->
                    <div class="p-6">
                        <div class="overflow-x-auto table-responsive">
                            <table id="editableTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Internal Work Days') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Sick Days') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Holidays') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Remaining Paid Leave') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Paid Leave Acquired') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Paid Leave Taken') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Leave Balance') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Unpaid Absence Days') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Paid Leave Dates') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Remark') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Month') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Year') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if($work_days_repports)
                                    <tr class="hover:bg-gray-50">
                                        <td data-label="{{ __('messages.Internal Work Days') }}" data-span="2" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" data-id="{{$work_days_repports->id}}" data-column="days_worked">
                                            @php
                                                $totalMinutes = $totalHours * 60;
                                                $hours = floor($totalMinutes / 60);
                                                $minutes = $totalMinutes % 60;
                                            @endphp
                                            {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m - <b>{{$work_days_repports->days_worked}} jrs</b>
                                        </td>
                                        <td data-label="{{ __('messages.Sick Days') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="sick_days">
                                            {{$work_days_repports->sick_days}}
                                        </td>
                                        <td data-label="{{ __('messages.Holidays') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="holidays">
                                            {{$work_days_repports->holidays}}
                                        </td>
                                        <td data-label="{{ __('messages.Remaining Paid Leave') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave">
                                            {{$work_days_repports->remaining_days_of_paid_leave}}
                                        </td>
                                        <td data-label="{{ __('messages.Paid Leave Acquired') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired">
                                            {{$work_days_repports->number_of_days_of_paid_leave_acquired}}
                                        </td>
                                        <td data-label="{{ __('messages.Paid Leave Taken') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken">
                                            {{$work_days_repports->number_of_days_of_paid_leave_taken}}
                                        </td>
                                        <td data-label="{{ __('messages.Leave Balance') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="holiday_balance">
                                            {{$work_days_repports->holiday_balance}}
                                        </td>
                                        <td data-label="{{ __('messages.Unpaid Absence Days') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence">
                                            {{$work_days_repports->days_of_unpaid_absence}}
                                        </td>
                                        <td data-label="{{ __('messages.Paid Leave Dates') }}" data-span="2" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays">
                                            {{$work_days_repports->dates_of_holidays}}
                                        </td>
                                        <td data-label="{{ __('messages.Remark') }}" data-span="2" class="editable px-6 py-4 text-sm text-gray-700 max-w-xs whitespace-normal" data-id="{{$work_days_repports->id}}" data-column="remarque">
                                            {{$work_days_repports->remarque}}
                                        </td>
                                        <td data-label="{{ __('messages.Month') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="month">
                                            {{$work_days_repports->month}}
                                        </td>
                                        <td data-label="{{ __('messages.Year') }}" class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="year">
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
                <!-- CHANGED: Applied new card style and added mb-8 -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] overflow-hidden">
                    <!-- Card Header -->
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between gap-3">
                        <div class="flex items-center">
                            <span class="text-xl mr-2">👥</span>
                            <h5 class="text-lg font-semibold text-gray-900 mb-0">{{ __('messages.Teams') }}</h5>
                        </div>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                data-target="employeeTeamsBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <!-- Card Body -->
                    <div id="employeeTeamsBody" class="hidden">
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @php
                                $team_id = App\Models\team_members::where('employee_id', $employee->id)->pluck('team_id')->unique();
                                $teams = App\Models\Teams::whereIn('id', $team_id)->get();
                            @endphp
                            @foreach($teams as $team)
                            <div class="bg-white rounded-lg shadow-md p-4 border border-gray-200">
                                <div class="mb-3">
                                    <h5 class="text-lg font-semibold text-gray-900 capitalize">{{$team->name}}</h5>
                                    <span class="text-sm font-medium text-gray-600">{{ __('messages.Team Members:') }}</span>
                                </div>
                                <ul class="space-y-3">
                                    @foreach(App\Models\team_members::where('team_id', $team->id)->get() as $team_member)
                                    <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-b-0">
                                        <div class="flex items-center flex-1 min-w-0">
                                            @php
                                                $member_employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                                $employee_user = App\Models\User::where('id', $member_employee->user_id)->first();
                                            @endphp
                                            @if($member_employee->image)
                                            <img class="w-8 h-8 rounded-full mr-3 flex-shrink-0" 
                                                src="{{ Storage::disk('public')->url('employee_images/' . $member_employee->image) }}" 
                                                alt="employee Image">
                                            @else
                                            <div class="w-8 h-8 rounded-full mr-3 flex items-center justify-center text-white text-sm flex-shrink-0"
                                                style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                                                <span>{{ strtoupper(substr($team_member->employee_name, 0, 1)) }}</span>
                                            </div>
                                            @endif
                                            <div class="min-w-0">
                                                <h6 class="text-sm font-medium text-gray-900 truncate">
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
                                                        <a href="{{ $memberRoute }}" class="hover:text-blue-500">{{$team_member->employee_name}}</a>
                                                    @else
                                                        {{$team_member->employee_name}}
                                                    @endif
                                                </h6>
                                                @if($employee_user->is_loggedIn == 0)
                                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800">Off Work</span>
                                                @elseif($employee_user->is_loggedIn == 1)
                                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800">On Work</span>
                                                @else
                                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-yellow-100 text-yellow-800">On Pause</span>
                                                @endif
                                            </div>
                                        </div>
                                        @if($memberRoute != '#')
                                        <a href="{{ $memberRoute }}" class="flex-shrink-0 px-3 py-1 text-xs font-medium text-blue-600 border border-blue-600 rounded-full hover:bg-blue-50">
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
                <!-- CHANGED: Applied new card style and added mb-8 -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Projects Section') }}</h2>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                data-target="employeeProjectsBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <div id="employeeProjectsBody" class="hidden">
                    <!-- CHANGED: Added p-6 wrapper for table -->
                    <div class="p-6">
                        <div class="overflow-x-auto table-responsive">
                            @php
                                $team_project_id = App\Models\project_teams::whereIn('team_id', $team_id)->pluck('project_id');
                                $projects_tasks_id = App\Models\employees_tasks::where('employee_id', $employee->id)->pluck('task_id');
                                $projects_tasks = App\Models\Tasks::whereIn('id', $projects_tasks_id)->pluck('project_id');
                                $projects1 = App\Models\Project::whereIn('id', $projects_tasks)->get();
                                $projects = App\Models\Project::whereIn('id', $team_project_id)->get();
                                $combined_project_ids = $projects1->concat($projects)->unique();
                                $projectsByClient = $combined_project_ids->groupBy('client_id');
                            @endphp
                            <table id="employeeProjectsTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Client / Project') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Budget') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Duration') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Progress') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($projectsByClient as $client_id => $clientProjects)
                                        @php $client = App\Models\Client::find($client_id); @endphp
                                        <tr class="bg-gray-100 emp-group-row employee-client-group cursor-pointer select-none"
                                            data-client-group="{{ $client_id }}" aria-expanded="true">
                                            <td colspan="4" class="px-6 py-3">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h6 class="text-sm font-semibold text-gray-900">
                                                    @php $clientRoute = '#'; @endphp
                                                    @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View client'))
                                                        @php $clientRoute = route('admin.clients.show', $client_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View client'))
                                                        @php $clientRoute = route('Responsable_de_projet.clients.show', $client_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View client'))
                                                        @php $clientRoute = route('employee.clients.show', $client_id); @endphp
                                                    @endif
                                                    
                                                    @if($clientRoute != '#')
                                                        <a href="{{ $clientRoute }}" class="hover:text-blue-500">{{$client->name}}</a>
                                                    @else
                                                        {{$client->name}}
                                                    @endif
                                                    </h6>

                                                    <svg class="employee-group-chevron w-5 h-5 text-slate-500 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                                    </svg>
                                                </div>
                                            </td>
                                        </tr>
                                        @foreach($clientProjects as $project)
                                        @continue($project->archived == 1)
                                        <tr class="hover:bg-gray-50 employee-client-item" data-client-parent="{{ $client_id }}">
                                            <td data-label="{{ __('messages.Client / Project') }}" data-span="2" class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @php $projectRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                                    @php $projectRoute = route('admin.projects.show', $project->id); @endphp
                                                @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                                    @php $projectRoute = route('Responsable_de_projet.projects.show', $project->id); @endphp
                                                @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                                    @php $projectRoute = route('employee.projects.show', $project->id); @endphp
                                                @endif

                                                @if($projectRoute != '#')
                                                    <a href="{{ $projectRoute }}" class="text-blue-600 hover:text-blue-800">{{$project->name}}</a>
                                                @else
                                                    <span class="text-gray-700">{{$project->name}}</span>
                                                @endif
                                            </td>
                                            <td data-label="{{ __('messages.Budget') }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">€{{$project->budget}}</td>
                                            @php 
                                                $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->where('employee_id', $employee->id)->sum('duration');
                                                $hours = floor(abs($totalduration) / 60);
                                                $minutes = abs($totalduration) % 60;
                                                $estimatedMinutes = $project->estimated_hours * 60;
                                                $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                                                $formattedProgressPercentage = number_format($progressPercentage, 2);
                                            @endphp
                                            <td data-label="{{ __('messages.Duration') }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{$hours}}h:{{$minutes}}m</td>
                                            <td data-label="{{ __('messages.Progress') }}" data-span="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 min-w-[150px]">
                                                <div class="w-full bg-gray-200 rounded-full h-3 relative">
                                                    @php
                                                        $progressColor = 'bg-green-500';
                                                        if ($progressPercentage >= 75) $progressColor = 'bg-red-500';
                                                        elseif ($progressPercentage >= 50) $progressColor = 'bg-yellow-500';
                                                    @endphp
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <span class="text-xs font-semibold {{ $progressPercentage > 40 ? 'text-white' : 'text-gray-700' }}">{{$formattedProgressPercentage}}%</span>
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
                <!-- CHANGED: Applied new card style. No mb-8 as it's the last item. -->
                <div class="rounded-2xl border border-slate-200 bg-white shadow-[0_20px_50px_rgba(15,23,42,0.12),0_4px_12px_rgba(15,23,42,0.08)] overflow-hidden">
                    <div class="p-6 border-b border-gray-200 flex items-center justify-between gap-3">
                        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Tasks Section') }}</h2>
                        <button type="button"
                                class="section-toggle inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900"
                                data-target="employeeTasksBody" aria-expanded="false">
                            <span class="hidden lg:inline">{{ __('messages.Show') }}</span>
                            <svg class="section-chevron w-5 h-5 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                            </svg>
                        </button>
                    </div>
                    <div id="employeeTasksBody" class="hidden">
                    <!-- CHANGED: Added p-6 wrapper for table -->
                    <div class="p-6">
                        <div class="overflow-x-auto table-responsive">
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
                            <table id="employeeTasksTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Project / Task') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Duration') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Est. Hours') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Difference') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Progress') }}</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider text-center">{{ __('messages.Chrono') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tasksByProject as $project_id => $projectTasks)
                                        @php $project = App\Models\Project::find($project_id); @endphp
                                        @if($project) <!-- Added check in case project is null -->
                                        <tr class="bg-gray-100 emp-group-row employee-project-group cursor-pointer select-none"
                                            data-project-group="{{ $project_id }}" aria-expanded="true">
                                            <td colspan="6" class="px-6 py-3">
                                                <div class="flex items-center justify-between gap-3">
                                                    <h6 class="text-sm font-semibold text-gray-900">
                                                    @php $projectRoute = '#'; @endphp
                                                    @if(Auth::user()->hasRole('Admin') && Auth::user()->can('View project'))
                                                        @php $projectRoute = route('admin.projects.show', $project_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Responsable de projet') && Auth::user()->can('View project'))
                                                        @php $projectRoute = route('Responsable_de_projet.projects.show', $project_id); @endphp
                                                    @elseif(Auth::user()->hasRole('Employee') && Auth::user()->can('View project'))
                                                        @php $projectRoute = route('employee.projects.show', $project_id); @endphp
                                                    @endif

                                                    @if($projectRoute != '#')
                                                        <a href="{{ $projectRoute }}" class="hover:text-blue-500">{{$project->name}}</a>
                                                    @else
                                                        {{$project->name}}
                                                    @endif
                                                    </h6>

                                                    <svg class="employee-group-chevron w-5 h-5 text-slate-500 transition-transform" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9l6 6 6-6" />
                                                    </svg>
                                                </div>
                                            </td>
                                        </tr>
                                        @foreach($projectTasks as $task)
                                        <tr class="hover:bg-gray-50 employee-project-item" data-project-parent="{{ $project_id }}">
                                            <td data-label="{{ __('messages.Project / Task') }}" data-span="2" class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                @php $taskRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @php $taskRoute = route('admin.tasks.show', $task->id); @endphp
                                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                    @php $taskRoute = route('Responsable_de_projet.tasks.show', $task->id); @endphp
                                                @elseif(Auth::user()->hasRole('Employee'))
                                                    @php $taskRoute = route('employee.tasks.show', $task->id); @endphp
                                                @endif
                                                <a href="{{ $taskRoute }}" class="text-blue-600 hover:text-blue-800">{{$task->title}}</a>
                                            </td>
                                            @php 
                                                $tasktotalduration = App\Models\tasks_timer::where('task_id', $task->id)->where('employee_id', $employee->id)->sum('duration');
                                                $taskhours = floor($tasktotalduration / 60);
                                                $taskminutes = $tasktotalduration % 60;
                                            @endphp
                                            <td data-label="{{ __('messages.Duration') }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                                @php $durationRoute = '#'; @endphp
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @php $durationRoute = route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]); @endphp
                                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                    @php $durationRoute = route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]); @endphp
                                                @elseif(Auth::user()->hasRole('Employee'))
                                                    @php $durationRoute = route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]); @endphp
                                                @endif
                                                <a href="{{ $durationRoute }}" class="text-blue-600 hover:text-blue-800">
                                                    {{$taskhours}}h:{{$taskminutes}}m
                                                </a>
                                            </td>
                                            <td data-label="{{ __('messages.Est. Hours') }}" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{$task->estimated_hours}}h</td>
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
                                            <td data-label="{{ __('messages.Difference') }}" class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
                                            <td data-label="{{ __('messages.Progress') }}" data-span="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 min-w-[150px]">
                                                <div class="w-full bg-gray-200 rounded-full h-3 relative">
                                                    @php
                                                        $taskProgressColor = 'bg-green-500';
                                                        if ($taskprogressPercentage >= 75) $taskProgressColor = 'bg-red-500';
                                                        elseif ($taskprogressPercentage >= 50) $taskProgressColor = 'bg-yellow-500';
                                                    @endphp
                                                    <div class="absolute inset-0 flex items-center justify-center">
                                                        <span class="text-xs font-semibold {{ $taskprogressPercentage > 40 ? 'text-white' : 'text-gray-700' }}">{{$taskformattedProgressPercentage}}%</span>
                                                    </div>
                                                    <div class="{{ $taskProgressColor }} h-3 rounded-full" style="width: {{min($taskformattedProgressPercentage, 100)}}%"></div>
                                                </div>
                                            </td>
                                            <td data-label="{{ __('messages.Chrono') }}" data-span="2" class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
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
                                                        <form class="flex flex-col gap-1" action="{{ route($taskRoutePrefix . 'tasks.start', ['tasks' => $employeeTask->id, 'employee_id' => $employee->id]) }}" method="POST">
                                                            @csrf
                                                            <textarea
                                                                name="comment"
                                                                class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-xs py-1 px-2"
                                                                placeholder="{{ __('messages.Description') }}"
                                                                rows="1"
                                                            ></textarea>
                                                            <button
                                                                type="submit"
                                                                class="mt-1 inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-lg hover:bg-green-700"
                                                            >
                                                                {{ __('messages.Start') }}
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form
                                                            class="flex items-center justify-center @if($is_own_task) stop-form @endif"
                                                            action="{{ route($taskRoutePrefix . 'tasks.stop', ['tasks' => $employeeTask->id, 'employee_id' => $employee->id]) }}"
                                                            method="POST"
                                                        >
                                                            @csrf
                                                            @if($is_own_task)
                                                                <input type="hidden" name="stop_comment" class="stop-comment-field">
                                                            @endif
                                                            <button
                                                                type="submit"
                                                                class="@if($is_own_task) stop-button @endif inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-lg hover:bg-red-700"
                                                                @if($is_own_task) data-task-id="{{ $task->id }}" @endif
                                                            >
                                                                {{ __('messages.Stop') }}
                                                            </button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="text-xs text-gray-400">-</span>
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

            </div> <!-- End of space-y-8 wrapper -->

        </main>
    </div>

    <div id="contactModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 p-6 transform transition-all duration-300">
            
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">{{ __('messages.Send Email to') }} {{ $employee->name }}</h3>
                <button type="button" id="closeEmailModalBtn" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="mt-4">
                <form id="emailForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                    <div class="space-y-4">
                        <div>
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Subject') }}</label>
                            <input type="text" class="w-full border-gray-300 rounded-lg shadow-sm" id="subject" name="subject" required>
                        </div>
                        <div>
                    <label for="body" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Message') }}</label>
                            <textarea class="w-full border-gray-300 rounded-lg shadow-sm" id="body" name="body" rows="6" required>Bonjour, 
Veuillez trouver ci-joint le document, pour votre information.

N'hésitez pas à me contacter si vous avez la moindre question.

Bien cordialement,

Pour Stabel</textarea>
                        </div>
                        <div>
                            <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Attachments') }}</label>
                            <input type="file" class="w-full text-sm text-gray-500
                                file:mr-4 file:py-2 file:px-4
                                file:rounded-lg file:border-0
                                file:text-sm file:font-semibold
                                file:bg-blue-50 file:text-blue-700
                                hover:file:bg-blue-100" 
                                id="attachments" name="attachments[]" multiple>
                        </div>
                    </div>
                </form>
            </div>

            <div class="mt-6 pt-4 border-t border-gray-200 flex justify-end gap-3">
                <button type="button" id="cancelEmailModalBtn" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg font-medium text-sm">
                    {{ __('messages.Close') }}
                </button>
                <button type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm" id="sendEmailBtn">
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
                        <button type="button" id="stopCommentTranslateBtn" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 transition-colors opacity-50 cursor-not-allowed" title="{{ __('messages.Translate to Dutch') }}" disabled>
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
                <button type="button" id="spellCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-indigo-500 rounded-lg hover:bg-indigo-600 transition-colors flex items-center justify-center gap-2">
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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.006 3.125-1.026 6.042-2.751 8.498m-1.782-2.5A18.022 18.022 0 014.888 5h7.863z" />
                    </svg>
                </div>
            </div>
            <h2 class="text-lg font-semibold text-slate-800 mb-2">{{ __('messages.Translate to Dutch?') }}</h2>
            <p class="text-sm text-slate-500 mb-5">{{ __('messages.Would you like to translate your comment into Dutch before submitting?') }}</p>
            <div class="flex flex-col gap-2">
                <button type="button" id="translateCheckVerifyBtn" class="w-full px-4 py-2.5 text-sm font-medium text-white bg-blue-500 rounded-lg hover:bg-blue-600 transition-colors flex items-center justify-center gap-2">
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

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        $(document).ready(function() {
            var activeStopForm = null;
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
                activeStopForm = $(this).closest('.stop-form')[0];
                
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
                    stopCommentError.classList.add('hidden');
                } else {
                    stopConfirmBtn.classList.add('opacity-50', 'cursor-not-allowed');
                    stopCommentAiBtn.classList.add('opacity-50', 'cursor-not-allowed');
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
                if (activeStopForm) {
                    activeStopForm.querySelector('.stop-comment-field').value = comment;
                    stopModal.style.display = 'none';
                    activeStopForm.submit();
                }
            }

            // Confirm stop: show spell-check or translate popups if they haven't been completed yet
            stopConfirmBtn.addEventListener('click', function() {
                var comment = stopCommentText.value.trim();
                if (!comment) {
                    stopCommentError.classList.remove('hidden');
                    stopCommentText.focus();
                    return;
                }
                
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
                activeStopForm = null;
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
    @livewireScripts
</body>
</html>
