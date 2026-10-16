<!DOCTYPE html>
<html lang="en">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Employees - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles

    <style>
        /* Mobile: show employee rows as stacked cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            #employeesTable thead {
                display: none;
            }
            #employeesTable tbody {
                display: block;
            }
            #employeesTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55); /* slate-400/55 */
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow:
                    0 12px 30px rgba(15, 23, 42, 0.08),
                    0 2px 6px rgba(15, 23, 42, 0.06);
                transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
                padding: 12px;
            }
            #employeesTable tbody tr:hover {
                transform: translateY(-1px);
                border-color: rgba(45, 112, 128, 0.45);
                box-shadow:
                    0 16px 40px rgba(15, 23, 42, 0.10),
                    0 3px 10px rgba(15, 23, 42, 0.08);
            }
            #employeesTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9); /* slate-200 */
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
            }
            #employeesTable tbody td:last-child {
                border-bottom: 0;
            }
            #employeesTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 4px;
            }
            #employeesTable tbody td[data-label] {
                color: #0f172a; /* slate-900 */
            }
            #employeesTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }
            /* On mobile: name is shown next to avatar, so hide the separate Name cell */
            #employeesTable tbody td.employee-name-cell {
                display: none !important;
            }
            /* Keep avatar compact */
            #employeesTable tbody td[data-label=""]::before,
            #employeesTable tbody td:not([data-label])::before {
                display: none;
            }
            /* On mobile: left-align action button *row*, but keep avatars/icons centered */
            #employeesTable tbody td[data-label] .employee-actions {
                justify-content: flex-start !important;
            }

            /* Mobile accordion: collapsed row shows only header (avatar + name) */
            #employeesTable tbody tr.employee-row.is-collapsed td:not(.employee-toggle-cell) {
                display: none !important;
            }
            #employeesTable tbody tr.employee-row td.employee-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            #employeesTable tbody tr.employee-row td.employee-toggle-cell .employee-chevron {
                transition: transform 180ms ease;
            }
            #employeesTable tbody tr.employee-row[aria-expanded="true"] td.employee-toggle-cell .employee-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">

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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            {{-- Page heading (same design as Clients Reports page) --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Reports') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Employees Table') }}
                    </h1>
                </div>

                {{-- Buttons --}}
                @php
                    $user = Auth::user();
                    $isSoloPlan = optional(optional($user)->entreprise)->type_compt === 'solo';
                @endphp

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full sm:w-auto">
                    {{-- Report Button --}}
                    @php
                        $reportRoute = '#';
                        if (Auth::user()->hasRole('Responsable de projet')) $reportRoute = route('Responsable_de_projet.Employeesrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Admin')) $reportRoute = route('admin.Employeesrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Employee')) $reportRoute = route('employee.Employeesrepports.downloadRepports');
                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportRoute = route('Responsable_gestionnaire.Employeesrepports.downloadRepports');
                    @endphp
                    <a href="{{ $reportRoute }}" class="dt-btn dt-btn-ghost w-full sm:w-auto">
                       <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        {{ __('messages.Reports PDF') }}
                    </a>

                    {{-- Add Team Button --}}
                    @can('Add teams')
                       <a href="{{ route('admin.teams.create') }}" class="dt-btn dt-btn-teal w-full sm:w-auto">
                          <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                          {{ __('messages.Add Team') }}
                       </a>
                    @endcan

                    {{-- Add Employee Button --}}
                    @unless($isSoloPlan)
                        @can('Add employee')
                            <a href="{{ route('admin.employees.create') }}" class="dt-btn dt-btn-teal w-full sm:w-auto">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                {{ __('messages.Add Employee') }}
                            </a>
                        @endcan
                    @endunless

                     {{-- View Archived Employees Button (opens the archived list) --}}
                     @if(Auth::user()->hasRole('Admin'))
                        <a href="{{ route('admin.employeesArchived.archiveIndex') }}" class="dt-btn dt-btn-ghost w-full sm:w-auto" title="{{ __('messages.Archived Employees') }}">
                           <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                            {{ __('messages.Archived Employees') }}
                        </a>
                     @endif
                </div>
            </div>

            {{-- Container for the table --}}
            <div class="dt-card overflow-hidden">

                {{-- Session Message Alert --}}
                @if(session('message'))
                    <div class="m-4 sm:m-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                        <span class="block sm:inline text-[13px] font-medium">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-emerald-600 hover:text-emerald-800 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif

                <div class="flex justify-end px-5 sm:px-6 py-3 border-b border-slate-100 bg-[#f8fafc]">
                    <label class="flex items-center gap-2 text-[12px] text-slate-500" for="employeesPerPage">
                        <span>{{ __('messages.Rows per page') }}:</span>
                        <select id="employeesPerPage" class="rounded-lg border border-slate-200 bg-white py-1 pl-2 pr-6 text-[12px] text-slate-700 focus:border-[#2D7080] focus:ring-1 focus:ring-[#2D7080]">
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </label>
                </div>

                {{-- Table Wrapper --}}
                <div class="overflow-x-auto custom-scrollbar">
                        <table id="employeesTable" class="w-full text-left border-collapse table-auto">
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="dt-table-head">
                                    <th class="px-4 py-3 text-left whitespace-nowrap"></th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[150px]">{{ __('messages.Name') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[140px]">{{ __('messages.Email') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Phone') }}</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">{{ __('messages.Status') }}</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">{{ __('messages.Age') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.CNSS') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap min-w-[130px]">{{ __('messages.Bank Acc') }}</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('messages.Salary') }}</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('messages.Cost/Hr') }}</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">{{ __('messages.WorkHour Limit') }}</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('messages.CNSS Cot') }}</th>
                                    <th class="px-4 py-3 text-right whitespace-nowrap">{{ __('messages.AMO Cot') }}</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap">{{ __('messages.Release') }}</th>
                                    <th class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Role') }}</th>
                                    <th class="px-4 py-3 text-center whitespace-nowrap min-w-[110px]">{{ __('messages.Action') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse($employees as $employee)
                                <tr class="employee-row hover:bg-slate-50/70 transition-colors" data-employee-row aria-expanded="true">
                                    {{-- Avatar/Initials --}}
                                    <td data-label="" data-span="2" class="px-4 py-2.5 whitespace-nowrap employee-toggle-cell" data-employee-toggle>
                                        @php
                                            $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                            $bgColor = $colors[$loop->index % count($colors)];
                                        @endphp
                                        {{-- Mobile: avatar + name together --}}
                                        <div class="flex items-center justify-between gap-3 lg:hidden">
                                            <div class="flex items-center gap-3 min-w-0">
                                            @if($employee->image)
                                                <img class="h-8 w-8 rounded-full object-cover"
                                                     src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}"
                                                     alt="{{ $employee->name }} image">
                                            @else
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-medium text-xs"
                                                     style="background-color: {{ $bgColor }};">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </div>
                                            @endif

                                            <a href="{{ route('admin.employees.show', $employee->id) }}"
                                               class="min-w-0 truncate text-[13px] font-bold text-[#0B2638] hover:underline">
                                                {{ $employee->name }}
                                            </a>
                                            </div>
                                            <span class="shrink-0 text-slate-400">
                                                <svg class="employee-chevron w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                    <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </span>
                                        </div>

                                        {{-- Desktop: keep original (avatar only) --}}
                                        <div class="hidden lg:block">
                                            @if($employee->image)
                                                <img class="h-8 w-8 rounded-full object-cover"
                                                     src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}"
                                                     alt="{{ $employee->name }} image">
                                            @else
                                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-medium text-xs"
                                                     style="background-color: {{ $bgColor }};">
                                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    {{-- Name with Link --}}
                                    <td data-label="{{ __('messages.Name') }}" class="employee-name-cell px-4 py-2.5 text-[13px] text-slate-700 editable whitespace-normal break-words font-medium"
                                        data-id="{{$employee->id}}" data-column="name" title="Double-click to edit">
                                        <a href="{{ route('admin.employees.show', $employee->id) }}"
                                           class="text-[#2D7080] hover:text-[#0B2638] hover:underline font-medium">
                                            {{ $employee->name }}
                                        </a>
                                    </td>
                                    {{-- Email --}}
                                    <td data-label="{{ __('messages.Email') }}" class="px-4 py-2.5 text-[13px] text-slate-600 editable whitespace-normal break-all max-w-[220px]" data-id="{{$employee->id}}" data-column="email">{{$employee->email}}</td>
                                    {{-- Phone --}}
                                    <td data-label="{{ __('messages.Phone') }}" class="px-4 py-2.5 text-[13px] text-slate-600 editable whitespace-nowrap" data-id="{{$employee->id}}" data-column="phoneNumber">{{$employee->phoneNumber}}</td>
                                    
                                    {{-- Status Badge/Button --}}
                                    <td data-label="{{ __('messages.Status') }}" class="px-4 py-2.5 text-[13px] text-slate-600 text-center whitespace-nowrap">
                                            @php
                                                $user = \App\Models\User::find($employee->user_id);
                                                $statusRoute = '#';
                                                $statusText = $user && $user->is_activate ? __('messages.Active') : __('messages.Inactive');
                                                $statusBadge = $user && $user->is_activate ? 'dt-badge-success' : 'dt-badge-danger';

                                                if ($user && $user->is_activate) {
                                                    if (Auth::user()->hasRole('Admin')) $statusRoute = route('admin.employee.deactivate', $employee->id);
                                                    elseif (Auth::user()->hasRole('Responsable de projet')) $statusRoute = route('Responsable_de_projet.employee.deactivate', $employee->id);
                                                } else {
                                                    if (Auth::user()->hasRole('Admin')) $statusRoute = route('admin.employee.activate', $employee->id);
                                                    elseif (Auth::user()->hasRole('Responsable de projet')) $statusRoute = route('Responsable_de_projet.employee.activate', $employee->id);
                                                    elseif (Auth::user()->hasRole('Employee')) $statusRoute = route('employee.employee.activate', $employee->id);
                                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $statusRoute = route('Responsable_gestionnaire.employee.activate', $employee->id);
                                                }
                                            @endphp

                                            <form action="{{ $statusRoute }}" method="post"
                                                  onsubmit="return confirm('{{ $user && $user->is_activate ? __('messages.Deactivate this employee?') : __('messages.Activate this employee?') }}');"
                                                  class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="dt-badge {{ $statusBadge }} border-none cursor-pointer whitespace-nowrap">
                                                    <span class="dt-dot"></span>{{ $statusText }}
                                                </button>
                                            </form>
                                        </td>

                                    {{-- Age --}}
                                    <td data-label="{{ __('messages.Age') }}" class="px-4 py-2.5 whitespace-nowrap text-[13px] text-slate-600 text-center editable" data-id="{{$employee->id}}" data-column="age">{{$employee->age}}</td>
                                    {{-- CNSS --}}
                                    <td data-label="{{ __('messages.CNSS') }}" class="px-4 py-2.5 text-[13px] text-slate-600 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="cnss">{{$employee->cnss}}</td>
                                    {{-- Bank Account --}}
                                    <td data-label="{{ __('messages.Bank Acc') }}" class="px-4 py-2.5 text-[13px] text-slate-600 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="bankAccountNumber">{{$employee->bankAccountNumber}}</td>
                                    {{-- Salary --}}
                                    <td data-label="{{ __('messages.Salary') }}" class="px-4 py-2.5 whitespace-nowrap text-[13px] text-emerald-700 font-semibold text-right editable" data-id="{{$employee->id}}" data-column="salary">{{$employee->salary}} €</td>
                                    {{-- Cost per hour --}}
                                    <td data-label="{{ __('messages.Cost/Hr') }}" class="px-4 py-2.5 whitespace-nowrap text-[13px] text-emerald-700 font-semibold text-right editable" data-id="{{$employee->id}}" data-column="cost_per_hour">{{number_format($employee->cost_per_hour, 2, '.', '')}} €</td>
                                    {{-- Hour Limit --}}
                                    <td data-label="{{ __('messages.WorkHour Limit') }}" class="px-4 py-2.5 whitespace-nowrap text-[13px] text-slate-600 text-center editable" data-id="{{$employee->id}}" data-column="workHours_limit">{{$employee->workHours_limit}} h</td>
                                    {{-- CNSS Cotisation --}}
                                    <td data-label="{{ __('messages.CNSS Cot') }}" class="px-4 py-2.5 whitespace-nowrap text-[13px] text-emerald-700 font-semibold text-right editable" data-id="{{$employee->id}}" data-column="Cotisation_CNSS">{{$employee->Cotisation_CNSS}} €</td>
                                    {{-- AMO Cotisation --}}
                                    <td data-label="{{ __('messages.AMO Cot') }}" class="px-4 py-2.5 whitespace-nowrap text-[13px] text-emerald-700 font-semibold text-right editable" data-id="{{$employee->id}}" data-column="Cotisation_AMO">{{$employee->Cotisation_AMO}} €</td>
                                    {{-- Release Date --}}
                                    <td data-label="{{ __('messages.Release') }}" class="px-4 py-2.5 text-[13px] text-slate-600 text-center editable whitespace-normal" data-id="{{$employee->id}}" data-column="Date_Release">{{$employee->Date_Release}}</td>

                                    {{-- Role Badges --}}
                                    <td data-label="{{ __('messages.Role') }}" class="px-4 py-2.5 text-[13px] text-slate-600">
                                         <div class="flex flex-col gap-0.5 max-h-[60px] overflow-hidden leading-tight">
                                             @forelse($employee->roles as $role)
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded {{ $role=='SuperAdmin' || $role=='Admin' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700' }}">
                                                    {{ $role }}
                                                </span>
                                             @empty
                                                <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded bg-red-100 text-red-700">
                                                   {{ __('messages.No Role') }}
                                                </span>
                                             @endforelse
                                         </div>
                                    </td>

                                    {{-- Action Buttons (icons) --}}
                                    <td data-label="{{ __('messages.Action') }}" data-span="2" class="px-4 py-2.5 text-[13px] text-slate-600">
                                        @php
                                            $canEditRoles = Auth::user()->can('Edit employee roles');
                                            $canDelete = Auth::user()->can('Delete employee');
                                            $isSelf = $user && $user->id == auth()->user()->id;
                                            
                                            $editRoute = '#';
                                            $archiveRoute = '#';
                                            $deleteRoute = '#';

                                            if (Auth::user()->hasRole('Admin')) {
                                                $editRoute = route('admin.employees.edit', $employee->id);
                                                $archiveRoute = route('admin.employees.archive', $employee->id);
                                                $deleteRoute = route('admin.employees.destroy', $employee->id);
                                            } elseif (Auth::user()->hasRole('Responsable de projet')) {
                                                $editRoute = route('Responsable_de_projet.employees.edit', $employee->id);
                                                $archiveRoute = route('Responsable_de_projet.employees.archive', $employee->id);
                                                $deleteRoute = route('Responsable_de_projet.employees.destroy', $employee->id);
                                            } elseif (Auth::user()->hasRole('Employee')) {
                                                $editRoute = route('employee.employees.edit', $employee->id);
                                                $archiveRoute = route('employee.employees.archive', $employee->id);
                                                $deleteRoute = route('employee.employees.destroy', $employee->id);
                                            } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                $editRoute = route('Responsable_gestionnaire.employees.edit', $employee->id);
                                                $archiveRoute = route('Responsable_gestionnaire.employees.archive', $employee->id);
                                                $deleteRoute = route('Responsable_gestionnaire.employees.destroy', $employee->id);
                                            }
                                        @endphp

                                        <div class="employee-actions flex items-center justify-center gap-2">
                                            

                                            {{-- Archive --}}
                                            <form action="{{ $archiveRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Archive this employee?') }}');" class="inline">
                                                @csrf
                                                <button type="submit" title="{{ __('messages.Archive employee') }}"
                                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-slate-500 hover:bg-slate-100 hover:border-slate-300 transition-colors">
                                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                              d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                                    </svg>
                                                </button>
                                            </form>

                                            {{-- Delete --}}
                                            @if($canDelete && !$isSelf)
                                                <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure you want to delete this employee?') }}');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" title="{{ __('messages.Delete employee') }}"
                                                            class="inline-flex items-center justify-center w-8 h-8 rounded-lg border border-slate-200 bg-white text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors">
                                                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                     <td colspan="16" class="px-6 py-10 text-center">
                                         <div class="dt-empty-icon mx-auto mb-3">
                                             <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-8 0v2m8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z"/></svg>
                                         </div>
                                         <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No employees found.') }}</p>
                                     </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                </div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-5 sm:px-6 py-3 border-t border-slate-100 bg-[#f8fafc]">
                    <span id="employeesPaginationInfo" class="text-[12px] text-slate-500 text-center sm:text-left"></span>
                    <nav id="employeesPagination" class="flex flex-wrap items-center justify-center gap-1" aria-label="{{ __('messages.Pagination Navigation') }}"></nav>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const rows = Array.from(document.querySelectorAll('#employeesTable tbody tr.employee-row'));
            const select = document.getElementById('employeesPerPage');
            const info = document.getElementById('employeesPaginationInfo');
            const controls = document.getElementById('employeesPagination');
            let currentPage = 1;
            let perPage = parseInt(select.value, 10) || 10;

            function renderPagination() {
                const total = rows.length;
                const totalPages = Math.max(1, Math.ceil(total / perPage));
                currentPage = Math.min(currentPage, totalPages);
                const start = (currentPage - 1) * perPage;
                const end = Math.min(start + perPage, total);

                rows.forEach((row, index) => {
                    row.style.display = index >= start && index < end ? '' : 'none';
                });
                info.textContent = total ? `${start + 1}–${end} / ${total}` : '';
                controls.replaceChildren();

                const base = 'inline-flex items-center justify-center min-w-[28px] h-[28px] px-1.5 rounded-lg text-[12px] font-medium transition-colors border select-none';
                const normal = `${base} bg-white border-slate-200 text-slate-600 hover:bg-slate-50`;
                const active = `${base} bg-[#2D7080] border-[#2D7080] text-white`;
                const disabled = `${base} bg-slate-50 border-slate-100 text-slate-300 cursor-not-allowed`;

                function addArrow(direction, isDisabled) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = isDisabled ? disabled : normal;
                    button.setAttribute('aria-label', direction === 'previous' ? @json(__('messages.Previous')) : @json(__('messages.Next')));
                    button.disabled = isDisabled;
                    button.innerHTML = direction === 'previous'
                        ? '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>'
                        : '<svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>';
                    button.addEventListener('click', () => {
                        currentPage += direction === 'previous' ? -1 : 1;
                        renderPagination();
                    });
                    controls.appendChild(button);
                }

                addArrow('previous', currentPage === 1);
                const pageRange = [];
                for (let page = 1; page <= totalPages; page++) {
                    if (page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)) pageRange.push(page);
                    else if (pageRange[pageRange.length - 1] !== '…') pageRange.push('…');
                }
                pageRange.forEach(page => {
                    const button = document.createElement('button');
                    button.type = 'button';
                    if (page === '…') {
                        button.className = `${base} bg-white border-slate-100 text-slate-400 cursor-default`;
                        button.textContent = '…';
                        button.disabled = true;
                    } else {
                        button.className = page === currentPage ? active : normal;
                        button.textContent = page;
                        button.setAttribute('aria-label', `${@json(__('messages.Page'))} ${page}`);
                        if (page === currentPage) button.setAttribute('aria-current', 'page');
                        button.addEventListener('click', () => {
                            currentPage = page;
                            renderPagination();
                        });
                    }
                    controls.appendChild(button);
                });
                addArrow('next', currentPage === totalPages);
            }

            select.addEventListener('change', function () {
                perPage = parseInt(this.value, 10) || 10;
                currentPage = 1;
                renderPagination();
            });
            renderPagination();
        });
    </script>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    @can('Update employee')
    $(document).ready(function(){
        $('body').on('dblclick', '.editable', function(){
            if ($(this).find('input').length > 0) {
                return;
            }

            var originalContent = $(this).text().trim();
            var cellWidth = $(this).outerWidth();

            $(this).html("<input type='text' value='" + originalContent + "' class='w-full border-slate-300 rounded-lg shadow-sm focus:border-[#2D7080] focus:ring-[#2D7080]/25 sm:text-sm p-1 outline-none' style='min-width: " + cellWidth + "px;' />");

            var input = $(this).children('input').first();
            input.focus();
            input[0].select();

            input.blur(function(){
                var newContent = $(this).val();
                var $parentCell = $(this).parent();
                var employeeId = $parentCell.data('id');
                var columnName = $parentCell.data('column');

                $parentCell.text(originalContent);

                $.ajax({
                    url: '/admin/employees/updateEmployee',
                    type: 'POST',
                    data: {
                        content: newContent,
                        id: employeeId,
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $parentCell.text(newContent);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $parentCell.text(originalContent);
                        alert('{{ __('messages.Error updating employee') }}: ' + errorThrown);
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            });

            input.keypress(function(e){
                if(e.which == 13){
                    $(this).blur();
                    return false;
                }
            });
        });
    });
    @endcan
</script>

<script>
    // Mobile accordion per employee row (show only header when collapsed)
    document.addEventListener('DOMContentLoaded', function () {
        const mql = window.matchMedia('(max-width: 1024px)');

        function setRowCollapsed(row, collapsed) {
            if (!row) return;
            row.classList.toggle('is-collapsed', collapsed);
            row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        function initAccordions() {
            const rows = document.querySelectorAll('#employeesTable tbody tr.employee-row');
            const isMobile = mql.matches;
            rows.forEach(row => setRowCollapsed(row, isMobile));
        }

        document.addEventListener('click', function (e) {
            const toggleCell = e.target.closest('[data-employee-toggle]');
            if (!toggleCell) return;

            // Let normal link clicks navigate without toggling.
            if (e.target.closest('a')) return;

            const row = toggleCell.closest('tr.employee-row');
            if (!row) return;

            const collapsed = row.classList.contains('is-collapsed');
            setRowCollapsed(row, !collapsed);
        });

        if (mql.addEventListener) mql.addEventListener('change', initAccordions);
        else if (mql.addListener) mql.addListener(initAccordions);

        initAccordions();
    });
</script>

</body>
</html>
