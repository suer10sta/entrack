<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DalyTrac Dashboard</title>
    @vite('resources/css/app.css')
    @livewireStyles {{-- Added for consistency --}}
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

        {{-- Main Content Area - Changed <div> to <main> and matched classes --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">

            <style>
                /* Keep modal styles as they are functional */
                .modal { display: none; z-index: 1050; }
                .modal.fade { transition: opacity 0.15s linear; }
                .modal.show { display: block; }
                .modal-backdrop { z-index: 1040; }

                /* Table styles from original */
                 #editableTable th, #editableTable td { text-align: center; padding: 0.75rem 0.5rem; }
                 #editableTable th:first-child, #editableTable td:first-child { text-align: left; padding-left: 1rem; }
                 #editableTable th:last-child, #editableTable td:last-child { padding-right: 1rem; }
                 #editableTable th { font-weight: 600; font-size: 0.75rem; color: #4a5568; letter-spacing: 0.05em; }
                 #editableTable td { font-size: 0.875rem; color: #2d3748; }
                 #editableTable tbody tr:last-child { border-bottom: none; }
                 #editableTable tbody tr { border-bottom: 1px solid #e2e8f0; }
                 .editable { cursor: pointer; background-color: #e6f3ff !important; }
                 .editable:hover { background-color: #cce7ff !important; }
                 #editableTable th.editable-header { background-color: #b3d9ff !important; }
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

            {{-- This is now the SINGLE container, wrapping header and table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                
                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        {{-- Changed <h1> to <h6> and text-2xl to text-lg to match target style --}}
                        <h6 class="text-lg font-semibold text-gray-900">Work Days Reports</h6>
                        <p class="text-sm text-gray-500">For: {{ $currentMonthName }}-{{ $currentYear }}</p>
                    </div>
                    
                    {{-- Filters and Buttons --}}
                    <div class="flex flex-wrap items-center gap-2">
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
                            <form method="GET" action="{{ $filterRoute }}" class="flex items-center gap-2">
                                <div class="flex-shrink-0">
                                    <select name="year" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" x-model="year">
                                        <option value="">Year</option>
                                        @for($i = date('Y'); $i >= 2000; $i--)
                                            <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : ''}}>{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="flex-shrink-0">
                                    <select name="month" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" x-model="month">
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}" {{ $i == $monthInt ? 'selected' : ''}}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Filter</button>
                            </form>
                            <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg" data-bs-toggle="modal" data-bs-target="#employeeSelectionModal">
                                PDF
                            </button>
                            <form method="POST" action="{{ $nextMonthRoute }}" class="m-0">
                                @csrf
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">Next Month</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Session Message - Moved here to match target structure --}}
                @if(session('message'))
                <div class="m-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl" aria-label="Close">
                        &times;
                    </button>
                </div>
                @endif
                
                {{-- Table Wrapper - Added p-6 div to match target --}}
                <div class="p-6">
                    {{-- Table --}}
                    <div class="overflow-x-auto">
                        <table id="editableTable" class="w-full text-left border-collapse min-w-full">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">NOM D'EMPLOYÉ</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">JOURS BRUT</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">HEURES INTERNE</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">JRS TRAVAIL SANS JRS FERIES ET SANS JRS CONGÉS PAYÉS</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">ABSENCE</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">MALADIE</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">FÉRIÉS</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">RELIQUAT CONGÉS PAYÉS</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">JRS CONGÉ PAYÉS ACQUIS</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">JRS CONGÉ PAYÉS PRIS</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">SOLDE CONGÉS PAYÉS</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">DATES CONGÉS PAYÉS</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">À RÉCUPÉRER</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">REMARQUES</th>
                                    <th scope="col" class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left editable-header">REMARQUE INTERN</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
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
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $employee->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $workDay->work_days_gross ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $internalHoursFormatted }} - {{ $workDay->days_worked ?? 0 }} jrs</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $joursTravail }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-column="days_of_unpaid_absence" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->days_of_unpaid_absence ?? 0 }}</p></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-column="sick_days" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->sick_days ?? 0 }}</p></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-column="holidays" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->holidays ?? 0 }}</p></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $workDay->remaining_days_of_paid_leave ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ $workDay->number_of_days_of_paid_leave_acquired ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-column="number_of_days_of_paid_leave_taken" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->number_of_days_of_paid_leave_taken ?? 0 }}</p></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold {{ ($workDay->holiday_balance ?? 0) < 0 ? 'text-red-600' : 'text-blue-600' }}">{{ $workDay->holiday_balance ?? 0 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-column="dates_of_holidays" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->dates_of_holidays ?? '' }}</p></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-column="days_to_recover" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->days_to_recover ?? 0 }}</p></td>
                                    <td class="px-6 py-4 text-sm text-gray-700 editable" data-column="remarque" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->remarque ?? '' }}</p></td>
                                    <td class="px-6 py-4 text-sm text-gray-700 editable" data-column="interne_remarque" data-id="{{ $workDay->id }}"><p class="text-sm font-weight-bold mb-0">{{ $workDay->interne_remarque ?? '' }}</p></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="15" class="px-6 py-4 text-center text-sm text-gray-500">No work day reports found for this period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination Placeholder  --}}
                    <div class="flex items-center justify-between p-4 border-t border-gray-200">
                        <button class="text-gray-400 hover:text-gray-600 disabled:opacity-50" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                        </button>
                        <span class="text-sm text-gray-600">Page 1 of 1</span>
                        <button class="text-gray-400 hover:text-gray-600 disabled:opacity-50" disabled>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>

                    {{-- Modal  --}}
                    <div class="modal fade" id="employeeSelectionModal" tabindex="-1" aria-labelledby="employeeSelectionModalLabel" aria-hidden="true" style="z-index: 1050;">
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                        <div class="fixed inset-0 z-50 overflow-y-auto">
                            <div class="flex min-h-full items-center justify-center p-4 text-center">
                                <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-3xl text-left">
                                    {{-- modal-content --}}
                                    <div class="flex items-center justify-between p-4 border-b rounded-t">
                                        {{-- modal-header --}}
                                        <h5 class="text-lg font-semibold text-gray-900" id="employeeSelectionModalLabel">Select Employees and Columns</h5>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        {{-- modal-body --}}
                                        <div class="mb-3">
                                            <div class="flex items-center">
                                                <input id="selectAllEmployees" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <label for="selectAllEmployees" class="ml-2 block text-sm font-medium text-gray-900">Select All Employees</label>
                                            </div>
                                        </div>
                                        <div class="employee-list max-h-48 overflow-y-auto space-y-2 mb-4 p-2 border rounded">
                                            @php $uniqueEmployees = $workDaysRepports->pluck('employee_id')->unique(); @endphp
                                            @foreach($uniqueEmployees as $employeeId)
                                                @php
                                                    $employee = \App\Models\Employees::find($employeeId);
                                                @endphp
                                                @if($employee && !$employee->archived)
                                                    <div class="flex items-center">
                                                        <input id="employee{{ $employee->id }}" value="{{ $employee->id }}" type="checkbox" checked class="employee-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                        <label for="employee{{ $employee->id }}" class="ml-2 block text-sm text-gray-900">{{ $employee->name }}</label>
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>

                                        <hr class="my-4">

                                        <h6 class="text-md font-semibold text-gray-900 mb-2">Unselect Columns to Exclude</h6>
                                        <form id="columnSelectionForm">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                                                <div class="flex items-center">
                                                    <input id="col_work_days_gross" value="work_days_gross" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_work_days_gross" class="ml-2 block text-sm text-gray-900">JOURS BRUT</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_days_worked" value="days_worked" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_days_worked" class="ml-2 block text-sm text-gray-900">HEURES INTERNE </label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_days_worked_gross" value="days_worked_gross" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_days_worked_gross" class="ml-2 block text-sm text-gray-900">JRS TRAVAIL SANS JRS FERIES ET SANS JRS CONGÉS PAYÉS</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_days_of_unpaid_absence" value="days_of_unpaid_absence" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_days_of_unpaid_absence" class="ml-2 block text-sm text-gray-900">ABSENCE</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_sick_days" value="sick_days" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_sick_days" class="ml-2 block text-sm text-gray-900">MALADIE</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_holidays" value="holidays" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_holidays" class="ml-2 block text-sm text-gray-900">FÉRIÉS</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_remaining_days_of_paid_leave" value="remaining_days_of_paid_leave" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_remaining_days_of_paid_leave" class="ml-2 block text-sm text-gray-900">RELIQUAT CONGÉS PAYÉS</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_number_of_days_of_paid_leave_acquired" value="number_of_days_of_paid_leave_acquired" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_number_of_days_of_paid_leave_acquired" class="ml-2 block text-sm text-gray-900">JRS CONGÉ PAYÉS ACQUIS</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_number_of_days_of_paid_leave_taken" value="number_of_days_of_paid_leave_taken" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_number_of_days_of_paid_leave_taken" class="ml-2 block text-sm text-gray-900">JRS CONGÉ PAYÉS PRIS</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_holiday_balance" value="holiday_balance" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_holiday_balance" class="ml-2 block text-sm text-gray-900">SOLDE CONGÉS PAYÉS</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_days_to_recover" value="days_to_recover" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_days_to_recover" class="ml-2 block text-sm text-gray-900">À RÉCUPÉRER</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_remarque" value="remarque" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_remarque" class="ml-2 block text-sm text-gray-900">REMARQUES</label>
                                                </div>
                                                <div class="flex items-center">
                                                    <input id="col_interne_remarque" value="interne_remarque" type="checkbox"  class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                    <label for="col_interne_remarque" class="ml-2 block text-sm text-gray-900">REMARQUE INTERN</label>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                    <div class="flex items-center justify-between p-4 border-t border-gray-200 rounded-b space-x-2">
                                        <button type="button" id="closeModalBtn" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg" data-bs-dismiss="modal">Close</button>
                                        <div>
                                            <button type="button" class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg mr-2" id="previewReportBtn">
                                                Preview & Email
                                            </button>
                                            <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg" id="generateReportBtn">
                                                Download PDF
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
