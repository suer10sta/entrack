<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Work Days Reports Title') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles {{-- Added for consistency --}}
</head>
<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content Area - Changed <div> to <main> and matched classes --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            <style>
                /* Keep modal styles as they are functional */
                .modal { display: none; z-index: 1050; }
                .modal.fade { transition: opacity 0.15s linear; }
                .modal.show { display: block; }
                .modal-backdrop { z-index: 1040; }

                /* Table base (alignment comes from the Tailwind utilities on each
                   cell so numeric columns can right-align while names stay left) */
                  #editableTable th, #editableTable td { padding: 0.75rem 1rem; }
                  #editableTable th { font-weight: 600; font-size: 0.75rem; color: #4a5568; letter-spacing: 0.05em; }
                  #editableTable td { font-size: 0.875rem; color: #2d3748; }
                  #editableTable tbody tr:last-child { border-bottom: none; }
                  #editableTable tbody tr { border-bottom: 1px solid #e2e8f0; }
                 .editable { cursor: pointer; background-color: rgba(45, 112, 128, 0.08) !important; }
                 .editable:hover { background-color: rgba(45, 112, 128, 0.16) !important; }
                 .editable-no-bg,
                 .editable-no-bg:hover { background-color: transparent !important; }
                 #editableTable th.editable-header { background-color: rgba(45, 112, 128, 0.16) !important; }

                /* Mobile: convert table rows into modern stacked cards (no side-scroll) */
                @media (max-width: 1024px) {
                    .workdays-table-responsive { overflow-x: visible !important; }

                    #editableTable thead { display: none; }
                    #editableTable tbody { display: block; width: 100%; }

                    #editableTable tbody tr {
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

                    #editableTable tbody td {
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

                    #editableTable tbody td[data-span="2"] { grid-column: span 2 / span 2; }

                    #editableTable tbody td[data-label]::before {
                        content: attr(data-label);
                        display: block;
                        font-size: 11px;
                        font-weight: 700;
                        letter-spacing: .06em;
                        text-transform: uppercase;
                        color: #64748b; /* slate-500 */
                        margin-bottom: 6px;
                    }
                }
            </style>

            @php
                $year = request()->query('year', date('Y'));
                $month = request()->query('month', date('m'));
                $monthInt = is_numeric($month) ? (int)$month : date('m');
                $yearInt = is_numeric($year) ? (int)$year : date('Y');
                
                $currentMonthName = 'unknown';
                $startDate = null;
                $endDate = null;

                try {
                    $dateForMonthName = Carbon\Carbon::create($yearInt, $monthInt, 1);
                    $currentMonthName = strtolower($dateForMonthName->format('F'));
                    $currentYear = $yearInt;
                    $startDate = $dateForMonthName->copy()->startOfMonth();
                    $endDate = $dateForMonthName->copy()->endOfMonth();
                } catch (\Exception $e) {
                    $startDate = Carbon\Carbon::now()->startOfMonth();
                    $endDate = Carbon\Carbon::now()->endOfMonth();
                    $currentYear = $startDate->year;
                    $currentMonthName = strtolower($startDate->format('F'));
                    $monthInt = $startDate->month;
                    Log::error("Error creating dates in Blade: " . $e->getMessage() . " | Year: " . $year . ", Month: " . $month);
                }
            @endphp

            {{-- Page heading (same design as other Reports pages) --}}
            <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-5">
                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Reports') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
                        {{ __('messages.Work Days Reports') }}
                    </h1>
                    <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.For period label') }} {{ $currentMonthName }}-{{ $currentYear }}</p>
                </div>

                    {{-- Filters and Buttons --}}
                    <div class="flex flex-col sm:flex-row sm:flex-wrap sm:items-end gap-2 w-full sm:w-auto">
                        @php
                            $filterRoute = '#';
                            $nextMonthRoute = '#';
                            $alpineComponent = '{}';
                            
                            if (Auth::user()->hasRole('Responsable de projet')) {
                                $filterRoute = route('Responsable_de_projet.workDaysReports.filterReports');
                                $nextMonthRoute = route('Responsable_de_projet.workDaysReports.nextMonthRepports');
                                $alpineComponent = 'PrjMngdatePicker()';
                            } elseif (Auth::user()->hasRole('Admin')) {
                                $filterRoute = route('admin.workDaysReports.filterReports');
                                $nextMonthRoute = route('admin.workDaysReports.nextMonthRepports');
                                $alpineComponent = 'AdmindatePicker()';
                            } elseif (Auth::user()->hasRole('Employee')) {
                                $filterRoute = route('employee.workDaysReports.filterReports');
                                $nextMonthRoute = route('employee.workDaysReports.nextMonthRepports');
                                $alpineComponent = 'EmployeeDatePicker()';
                            } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                $filterRoute = route('Responsable_gestionnaire.workDaysReports.filterReports');
                                $nextMonthRoute = route('Responsable_gestionnaire.workDaysReports.nextMonthRepports');
                                $alpineComponent = 'RespGestDatePicker()';
                            }
                        @endphp

                        <div x-data="{{ $alpineComponent }}" class="contents">
                            <form method="GET" action="{{ $filterRoute }}" class="flex flex-col sm:flex-row sm:items-end gap-2 w-full sm:w-auto">
                                <div class="flex-1 sm:flex-none">
                                    <!-- <label class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Year') }}</label> -->
                                    <select name="year" class="w-full sm:w-28 h-10 rounded-xl border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" x-model="year">
                                        <option value="">{{ __('messages.Year') }}</option>
                                        @for($i = date('Y'); $i >= 2000; $i--)
                                            <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : ''}}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="flex-1 sm:flex-none">
                                    <!-- <label class="block text-[13px] font-medium text-slate-700 mb-1">{{ __('messages.Month') }}</label> -->
                                    <select name="month" class="w-full sm:w-36 h-10 rounded-xl border-slate-200 bg-white px-3 text-sm text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25" x-model="month">
                                        <option value="">{{ __('messages.Month') }}</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ $i == $monthInt ? 'selected' : ''}}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="dt-btn dt-btn-teal h-10 w-full sm:w-auto">{{ __('messages.Filter') }}</button>
                            </form>
                            <button type="button" class="dt-btn dt-btn-primary h-10 w-full sm:w-auto" data-bs-toggle="modal" data-bs-target="#employeeSelectionModal">
                                {{ __('messages.PDF button') }}
                            </button>
                            <form method="POST" action="{{ $nextMonthRoute }}" class="m-0 w-full sm:w-auto">
                                @csrf
                                <input type="hidden" name="currentMonth" value="{{ $currentMonth }}">
                                <input type="hidden" name="currentYear" value="{{ $currentYear }}">
                                <button type="submit" class="dt-btn dt-btn-ghost h-10 w-full sm:w-auto">{{ __('messages.Next Month') }}</button>
                            </form>
                        </div>
                    </div>
            </div>

            {{-- Container for the table --}}
            <div class="dt-card overflow-hidden">

                {{-- Session Message - Moved here to match target structure --}}
                @if(session('message'))
                <div class="m-4 sm:m-5 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                    <span class="block sm:inline text-[13px] font-medium">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-emerald-600 hover:text-emerald-800 font-bold text-xl" aria-label="Close">
                        &times;
                    </button>
                </div>
                @endif

                {{-- Table Wrapper - Added p-6 div to match target --}}
                <div class="overflow-x-auto workdays-table-responsive custom-scrollbar">
                    {{-- Table --}}
                        <table id="editableTable" class="w-full text-left border-collapse min-w-full">
                            @unless($workDaysRepports->isEmpty())
                            <thead class="border-b border-slate-100 bg-slate-50/80">
                                <tr class="dt-table-head">
                                    <th scope="col" class="px-4 py-3 text-left whitespace-nowrap min-w-[180px] sticky left-0 z-20 bg-slate-50">{{ __('messages.Employee Name header') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums">{{ __('messages.Gross Days') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums">{{ __('messages.Internal Work Days') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums">{{ __('messages.Work Days Without Holidays') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums editable-header">{{ __('messages.Unpaid Absence Days') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums editable-header">{{ __('messages.Sick Days') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums editable-header">{{ __('messages.Holidays') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums">{{ __('messages.Remaining Paid Leave') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums">{{ __('messages.Paid Leave Acquired') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums editable-header">{{ __('messages.Paid Leave Taken') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums">{{ __('messages.Leave Balance') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-normal leading-tight min-w-[140px] max-w-[200px] editable-header">{{ __('messages.Paid Leave Dates') }}</th>
                                    <th scope="col" class="px-4 py-3 text-right whitespace-nowrap tabular-nums editable-header">{{ __('messages.Days To Recover') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-normal leading-tight min-w-[140px] max-w-[200px] editable-header">{{ __('messages.Remark') }}</th>
                                    <th scope="col" class="px-4 py-3 text-left whitespace-normal leading-tight min-w-[140px] max-w-[200px] editable-header">{{ __('messages.Internal Remark') }}</th>
                                </tr>
                            </thead>
                            @endunless
                            <tbody class="divide-y divide-slate-100">
                                @forelse($workDaysRepports as $workDay)
                                    @php
                                        $employee = \App\Models\Employees::where('id', $workDay->employee_id)->where('archived', 0)->first();
                                        if (!$employee) continue;

                                        // Internal Hours Calculation
                                        $totalMinutes = \App\Models\tasks_timer::where('employee_id', $workDay->employee_id)
                                            ->whereBetween('start_date', [$startDate, $endDate])
                                            ->whereNotNull('end_date')
                                            ->sum('duration');
                                        $totalHours = floor($totalMinutes / 60);
                                        $remainingMinutes = $totalMinutes % 60;
                                        $internalHoursFormatted = sprintf('%02dh:%02dm', $totalHours, $remainingMinutes);

                                        // Jours Travail Calculation
                                        $monthNumForCalc = is_numeric($workDay->month) ? (int)$workDay->month : $monthInt;
                                        $yearForCalc = is_numeric($workDay->year) ? (int)$workDay->year : $currentYear;

                                        try {
                                            $firstDayLoop = Carbon\Carbon::create($yearForCalc, $monthNumForCalc, 1)->startOfDay();
                                            $lastDayLoop = $firstDayLoop->copy()->endOfMonth()->endOfDay();
                                            $saturdays = 0;
                                            for($dateLoop = $firstDayLoop->copy(); $dateLoop->lte($lastDayLoop); $dateLoop->addDay()) {
                                                if($dateLoop->isSaturday()) {
                                                    $saturdays++;
                                                }
                                            }
                                            $joursTravail = (float)($workDay->days_worked ?? 0) + $saturdays + (float)($workDay->days_to_recover ?? 0);
                                        } catch (\Exception $e) {
                                            Log::error("Error calculating Jours Travail for workDay ID {$workDay->id}: " . $e->getMessage());
                                            $joursTravail = 'Error';
                                        }
                                    @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td data-label="{{ __('messages.Employee Name header') }}" data-span="2" class="px-4 py-3 whitespace-nowrap text-[13px] font-semibold text-[#0B2638] sticky left-0 z-10 bg-white border-r border-slate-100">{{ $employee->name }}</td>
                                    <td data-label="{{ __('messages.Gross Days') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600">{{ $workDay->work_days_gross ?? 0 }}</td>
                                    <td data-label="{{ __('messages.Internal Work Days') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600">{{ $internalHoursFormatted }} - {{ $workDay->days_worked ?? 0 }} {{ __('messages.days') }}</td>
                                    <td data-label="{{ __('messages.Work Days Without Holidays') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] font-semibold text-[#0B2638]">{{ $joursTravail }}</td>
                                    <td data-label="{{ __('messages.Unpaid Absence Days') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600 editable" data-column="days_of_unpaid_absence" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->days_of_unpaid_absence ?? 0 }}</p></td>
                                    <td data-label="{{ __('messages.Sick Days') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600 editable" data-column="sick_days" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->sick_days ?? 0 }}</p></td>
                                    <td data-label="{{ __('messages.Holidays') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600 editable" data-column="holidays" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->holidays ?? 0 }}</p></td>
                                    <td data-label="{{ __('messages.Remaining Paid Leave') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600 editable editable-no-bg" data-column="remaining_days_of_paid_leave" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->remaining_days_of_paid_leave ?? 0 }}</p></td>
                                    <td data-label="{{ __('messages.Paid Leave Acquired') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600">{{ $workDay->number_of_days_of_paid_leave_acquired ?? 0 }}</td>
                                    <td data-label="{{ __('messages.Paid Leave Taken') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600 editable" data-column="number_of_days_of_paid_leave_taken" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->number_of_days_of_paid_leave_taken ?? 0 }}</p></td>
                                    <td data-label="{{ __('messages.Leave Balance') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] font-semibold text-emerald-700 editable editable-no-bg" data-column="holiday_balance" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->holiday_balance ?? 0 }}</p></td>
                                    <td data-label="{{ __('messages.Paid Leave Dates') }}" data-span="2" class="px-4 py-3 whitespace-normal break-words min-w-[140px] max-w-[220px] text-[13px] text-slate-600 editable" data-column="dates_of_holidays" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->dates_of_holidays ?? '' }}</p></td>
                                    <td data-label="{{ __('messages.Days To Recover') }}" class="px-4 py-3 whitespace-nowrap tabular-nums text-right text-[13px] text-slate-600 editable" data-column="days_to_recover" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->days_to_recover ?? 0 }}</p></td>
                                    <td data-label="{{ __('messages.Remark') }}" data-span="2" class="px-4 py-3 whitespace-normal break-words min-w-[140px] max-w-[220px] text-[13px] text-slate-600 editable" data-column="remarque" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->remarque ?? '' }}</p></td>
                                    <td data-label="{{ __('messages.Internal Remark') }}" data-span="2" class="px-4 py-3 whitespace-normal break-words min-w-[140px] max-w-[220px] text-[13px] text-slate-600 editable" data-column="interne_remarque" data-id="{{ $workDay->id }}"><p class="text-[13px] font-medium mb-0">{{ $workDay->interne_remarque ?? '' }}</p></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="px-6 py-14 text-center">
                                        <div class="dt-empty-icon mx-auto mb-3">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No work day reports') }}</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Placeholder  --}}
                    <div class="flex items-center justify-between px-4 py-2.5 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl">
                        <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed" disabled aria-label="Previous page">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                        </button>
                        <span class="text-[12px] font-semibold text-slate-500">{{ __('messages.Single page label') }}</span>
                        <button class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-300 disabled:opacity-40 disabled:cursor-not-allowed" disabled aria-label="Next page">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-4 h-4"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>

                    {{-- Modal  --}}
                    <div class="modal fade" id="employeeSelectionModal" tabindex="-1" aria-labelledby="employeeSelectionModalLabel" aria-hidden="true" style="z-index: 1050;">
                        <div class="fixed inset-0 bg-[#0B2638]/50 backdrop-blur-[2px] transition-opacity" aria-hidden="true"></div>
                        <div class="fixed inset-0 z-50 overflow-y-auto">
                            <div class="flex min-h-full items-center justify-center p-4 text-center">
                                <div class="relative bg-white rounded-2xl shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-3xl text-left overflow-hidden">
                                    {{-- modal-content --}}
                                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 rounded-t">
                                        {{-- modal-header --}}
                                        <h5 class="dt-section-title" id="employeeSelectionModalLabel">{{ __('messages.Select Employees and Columns') }}</h5>
                                    </div>
                                    <div class="p-5 sm:p-6 space-y-4">
                                        {{-- modal-body --}}
                                        <div class="mb-3">
                                            <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                <input id="selectAllEmployees" type="checkbox" checked class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                <label for="selectAllEmployees" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Select All Employees') }}</label>
                                            </div>
                                        </div>
                                        <div class="employee-list max-h-48 overflow-y-auto space-y-1 mb-4 p-3 border border-slate-200 rounded-xl bg-slate-50/50 custom-scrollbar">
                                            @php $uniqueEmployees = $workDaysRepports->pluck('employee_id')->unique(); @endphp
                                            @foreach($uniqueEmployees as $employeeId)
                                                @php
                                                    $employee = \App\Models\Employees::find($employeeId);
                                                @endphp
                                                @if($employee && !$employee->archived)
                                                    <div class="flex items-center rounded-xl px-3 py-2 hover:bg-white border border-transparent hover:border-slate-200 hover:shadow-sm transition-colors">
                                                        <input id="employee{{ $employee->id }}" value="{{ $employee->id }}" type="checkbox" checked class="employee-checkbox h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                        <label for="employee{{ $employee->id }}" class="ml-2 block text-sm font-medium text-slate-700">{{ $employee->name }}</label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <hr class="my-4">

                                        <h6 class="dt-section-title mb-2">{{ __('messages.Unselect Columns to Exclude') }}</h6>
                                        <form id="columnSelectionForm">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_work_days_gross" value="work_days_gross" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_work_days_gross" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Gross Days') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_days_worked" value="days_worked" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_days_worked" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Internal Work Days') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_days_worked_gross" value="days_worked_gross" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_days_worked_gross" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Work Days Without Holidays') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_days_of_unpaid_absence" value="days_of_unpaid_absence" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_days_of_unpaid_absence" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Unpaid Absence Days') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_sick_days" value="sick_days" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_sick_days" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Sick Days') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_holidays" value="holidays" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_holidays" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Holidays') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_remaining_days_of_paid_leave" value="remaining_days_of_paid_leave" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_remaining_days_of_paid_leave" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Remaining Paid Leave') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_number_of_days_of_paid_leave_acquired" value="number_of_days_of_paid_leave_acquired" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_number_of_days_of_paid_leave_acquired" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Paid Leave Acquired') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_number_of_days_of_paid_leave_taken" value="number_of_days_of_paid_leave_taken" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_number_of_days_of_paid_leave_taken" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Paid Leave Taken') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_holiday_balance" value="holiday_balance" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_holiday_balance" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Leave Balance') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_days_to_recover" value="days_to_recover" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_days_to_recover" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Days To Recover') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_remarque" value="remarque" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_remarque" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Remark') }}</label>
                                                </div>
                                                <div class="flex items-center rounded-xl px-3 py-2 hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-colors">
                                                    <input id="col_interne_remarque" value="interne_remarque" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-[#2D7080] focus:ring-[#2D7080]/25">
                                                    <label for="col_interne_remarque" class="ml-2 block text-sm font-medium text-slate-700">{{ __('messages.Internal Remark') }}</label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2 p-4 border-t border-slate-100 rounded-b-2xl bg-slate-50/50">
                                        <button type="button" id="closeModalBtn" class="dt-btn dt-btn-ghost w-full sm:w-auto" data-bs-dismiss="modal">{{ __('messages.Close') }}</button>
                                        <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                                            <button type="button" class="dt-btn dt-btn-teal w-full sm:w-auto" id="previewReportBtn">
                                                {{ __('messages.Preview & Email') }}
                                            </button>
                                            <button type="button" class="dt-btn dt-btn-primary w-full sm:w-auto" id="generateReportBtn">
                                                {{ __('messages.Download PDF') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div> {{-- End of p-6 Table Wrapper --}}
                
            </div> {{-- End of single main container --}}

        </main> {{-- End of main content area --}}
    </div> {{-- End of navbar + content wrapper --}}

    @include('dashboard_layouts.scripts')
    
    <script>
        var userRole = "{{ Auth::user()->getRoleNames()->first() }}";
    </script>
    
    <script>
        function PrjMngdatePicker() {
            return { year: '{{ $currentYear }}', month: '{{ $monthInt }}' }
        }
        function AdmindatePicker() {
            return { year: '{{ $currentYear }}', month: '{{ $monthInt }}' }
        }
        function EmployeeDatePicker() {
            return { year: '{{ $currentYear }}', month: '{{ $monthInt }}' }
        }
        function RespGestDatePicker() {
            return { year: '{{ $currentYear }}', month: '{{ $monthInt }}' }
        }
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Select All functionality
            const selectAllCheckbox = document.getElementById('selectAllEmployees');
            const employeeCheckboxes = document.querySelectorAll('.employee-checkbox');
            if(selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    employeeCheckboxes.forEach(checkbox => { checkbox.checked = this.checked; });
                });
            }
            employeeCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    if (!this.checked && selectAllCheckbox) { selectAllCheckbox.checked = false; }
                    let allChecked = true;
                    employeeCheckboxes.forEach(cb => { if (!cb.checked) allChecked = false; });
                    if (selectAllCheckbox) { selectAllCheckbox.checked = allChecked; }
                });
            });

            // --- Generate Report button functionality ---
            const generateReportBtn = document.getElementById('generateReportBtn');
            if(generateReportBtn) {
                generateReportBtn.addEventListener('click', function() {
                    const excludedColumns = Array.from(document.querySelectorAll('#columnSelectionForm input[type="checkbox"]:not(:checked)'))
                        .map(cb => cb.value);
                    const selectedEmployees = Array.from(document.querySelectorAll('.employee-checkbox:checked'))
                        .map(cb => cb.value);

                    var year = @json($currentYear);
                    var month = @json($monthInt); 

                    let route;
                    if (userRole === 'Responsable de projet') { route = "{{ route('Responsable_de_projet.workDaysReports.downloadRepports') }}"; }
                    else if (userRole === 'Admin' || userRole === 'SuperAdmin') { route = "{{ route('admin.workDaysReports.downloadRepports') }}"; }
                    else if (userRole === 'Employee') { route = "{{ route('employee.workDaysReports.downloadRepports') }}"; }
                    else if (userRole === 'Responsable de projet et gestionnaire') { route = "{{ route('Responsable_gestionnaire.workDaysReports.downloadRepports') }}"; }
                    else { console.error("User role invalid"); return; }

                    const url = `${route}?year=${year}&month=${month}&excluded_columns=${excludedColumns.join(',')}&employees=${selectedEmployees.join(',')}`;
                    window.location.href = url;

                    const modalElement = document.getElementById('employeeSelectionModal');
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        if (modal) { modal.hide(); }
                    }
                });
            }
            
            // --- Preview Report button functionality ---
            const previewReportBtn = document.getElementById('previewReportBtn');
            if(previewReportBtn) {
                previewReportBtn.addEventListener('click', function() {
                    const excludedColumns = Array.from(document.querySelectorAll('#columnSelectionForm input[type="checkbox"]:not(:checked)'))
                        .map(cb => cb.value);
                    const selectedEmployees = Array.from(document.querySelectorAll('.employee-checkbox:checked'))
                        .map(cb => cb.value);

                    var year = @json($currentYear);
                    var month = @json($monthInt);

                    let route;
                    if (userRole === 'Responsable de projet') { route = "{{ route('Responsable_de_projet.workDaysReports.downloadRepports') }}"; }
                    else if (userRole === 'Admin' || userRole === 'SuperAdmin') { route = "{{ route('admin.workDaysReports.downloadRepports') }}"; }
                    else if (userRole === 'Employee') { route = "{{ route('employee.workDaysReports.downloadRepports') }}"; }
                    else if (userRole === 'Responsable de projet et gestionnaire') { route = "{{ route('Responsable_gestionnaire.workDaysReports.downloadRepports') }}"; }
                    else { console.error("User role invalid"); return; }

                    const url = `${route}?year=${year}&month=${month}&excluded_columns=${excludedColumns.join(',')}&employees=${selectedEmployees.join(',')}&preview=1`;
                    window.location.href = url;

                    const modalElement = document.getElementById('employeeSelectionModal');
                    if (modalElement && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                        if (modal) { modal.hide(); }
                    }
                });
            }

            // --- Close button functionality ---
            const closeModalBtn = document.getElementById('closeModalBtn');
            if(closeModalBtn) {
                closeModalBtn.addEventListener('click', function() {
                    var year = @json($currentYear);
                    var month = @json($monthInt);

                    let route;
                    if (userRole === 'Responsable de projet') { route = "{{ route('Responsable_de_projet.workDaysReports.index') }}"; }
                    else if (userRole === 'Admin' || userRole === 'SuperAdmin') { route = "{{ route('admin.workDaysReports.index') }}"; }
                    else if (userRole === 'Employee') { route = "{{ route('employee.workDaysReports.index') }}"; }
                    else if (userRole === 'Responsable de projet et gestionnaire') { route = "{{ route('Responsable_gestionnaire.workDaysReports.index') }}"; }
                    else { console.error("User role invalid"); return; }

                    const url = `${route}?year=${year}&month=${month}`;
                    window.location.href = url;
                });
            }
        });
    </script>

    <script>
        $(document).ready(function(){
            $("#editableTable .editable").dblclick(function(){
                var columnName = $(this).data('column');
                var originalContent = $(this).text().trim();

                console.log("Double-clicked on column: " + columnName);

                if (columnName === 'remarque' || columnName === 'interne_remarque') {
                    $(this).html("<textarea rows='4' cols='50'>" + originalContent + "</textarea>");
                } else {
                    $(this).html("<input type='text' value='" + originalContent + "' />");
                }

                $(this).children().first().focus();

                $(this).children().first().blur(function(){
                    var newContent = $(this).val();
                    var workDays_Id = $(this).parent().data('id');
                    var columnName = $(this).parent().data('column');
                    $(this).parent().html("<p class='text-sm font-weight-bold mb-0'>" + newContent + "</p>");
                    $(this).parent().removeClass("cellEditing");

                    console.log("Updated content: " + newContent);
                    console.log("User role: " + userRole);

                    if (userRole === 'Responsable de projet') {
                        console.log("Making AJAX call for Responsable de projet");
                        // Make AJAX call to update the database here
                        $.ajax({
                            url: '/Responsable_de_projet/workdaysrepports/updateWorkdaysRepport', 
                            type: 'post',
                            data: {
                                content: newContent,
                                id: workDays_Id,
                                column: columnName,
                                _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                            },
                            success: function(response) {
                                // Refresh the page after the update is successful
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating content: " + error);
                                // Optionally, you can handle errors here
                            }
                        });
                    } else if (userRole === 'Admin') {
                        console.log("Making AJAX call for admin");
                        // Make AJAX call to update the database here
                        $.ajax({
                            url: '/admin/workdaysrepports/updateWorkdaysRepport', 
                            type: 'post',
                            data: {
                                content: newContent,
                                id: workDays_Id,
                                column: columnName,
                                _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                            },
                            success: function(response) {
                                // Refresh the page after the update is successful
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating content: " + error);
                                // Optionally, you can handle errors here
                            }
                        });
                    } else if (userRole === 'SuperAdmin') {
                        console.log("Making AJAX call for SuperAdmin");
                        // Make AJAX call to update the database here
                        $.ajax({
                            url: '/SuperAdmin/workdaysrepports/updateWorkdaysRepport', 
                            type: 'post',
                            data: {
                                content: newContent,
                                id: workDays_Id,
                                column: columnName,
                                _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                            },
                            success: function(response) {
                                // Refresh the page after the update is successful
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating content: " + error);
                                // Optionally, you can handle errors here
                            }
                        }); 
                    } else if (userRole === 'Employee') {
                        console.log("Making AJAX call for Employee");
                        // Make AJAX call to update the database here
                        $.ajax({
                            url: '/employee/workdaysrepports/updateWorkdaysRepport', 
                            type: 'post',
                            data: {
                                content: newContent,
                                id: workDays_Id,
                                column: columnName,
                                _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                            },
                            success: function(response) {
                                // Refresh the page after the update is successful
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating content: " + error);
                                // Optionally, you can handle errors here
                            }
                        });
                    }
                    else if (userRole === 'Responsable de projet et gestionnaire') {
                        console.log("Making AJAX call for Responsable de projet et gestionnaire");
                        // Make AJAX call to update the database here
                        $.ajax({
                            url: '/Responsable_gestionnaire/workdaysrepports/updateWorkdaysRepport', 
                            type: 'post',
                            data: {
                                content: newContent,
                                id: workDays_Id,
                                column: columnName,
                                _token: '{{ csrf_token() }}' // add CSRF token for Laravel
                            },
                            success: function(response) {
                                // Refresh the page after the update is successful
                                location.reload();
                            },
                            error: function(xhr, status, error) {
                                console.error("Error updating content: " + error);
                                // Optionally, you can handle errors here
                            }
                        });
                    }
                    else {
                        console.log("User role does not match any condition");
                    }
                });
            });
        });
    </script>
</body>
</html>


