{{-- 
    This is the new layout structure.
    It includes the new sidebar and navbar.
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Days Reports | Entrack entrp</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    @vite('resources/css/app.css')
    {{-- Include Alpine.js from CDN --}}
    <script src="//unpkg.com/alpinejs" defer></script>
    @include('dashboard_layouts.head') 
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-hidden"> 
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <div class="flex-1 overflow-y-auto bg-gray-50 p-8"> 
            
            
            <style>
                @keyframes fadeInSlideUp {
                    from { opacity: 0; transform: translateY(20px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .animate-fadeInSlideUp {
                    opacity: 0;
                    animation: fadeInSlideUp 0.5s ease-out forwards;
                }
                /* Ensure modals are on top */
                .modal { display: none; z-index: 1050; /* Ensure modals appear above content */}
                .modal.fade { transition: opacity 0.15s linear; }
                .modal.show { display: block; }
                .modal-backdrop { z-index: 1040; } /* Ensure backdrop is behind modal */

                /* Align text center like image */
                 #reportsTable th, #reportsTable td { text-align: center; padding: 0.75rem 0.5rem; } /* Adjusted padding */
                 #reportsTable th:first-child, #reportsTable td:first-child { text-align: left; padding-left: 1rem; } /* Align first column left with more padding */
                 #reportsTable th:last-child, #reportsTable td:last-child { padding-right: 1rem; }
                 #reportsTable th { font-weight: 600; font-size: 0.75rem; color: #4a5568; letter-spacing: 0.05em; } /* Adjusted header style */
                 #reportsTable td { font-size: 0.875rem; color: #2d3748; }
                 #reportsTable tbody tr:last-child { border-bottom: none; } /* Remove border from last row */
                 #reportsTable tbody tr { border-bottom: 1px solid #e2e8f0; } /* Add border between rows */
            </style>

            @php
                $year = request()->query('year', date('Y')); 
                $month = request()->query('month', date('m')); 
                $monthInt = is_numeric($month) ? (int)$month : date('m'); // Ensure month is integer, default to current
                $yearInt = is_numeric($year) ? (int)$year : date('Y'); // Ensure year is integer, default to current
                
                $currentMonthName = 'unknown'; // Default
                $startDate = null;
                $endDate = null;

                try {
                    // Create Carbon date using integer month and year
                    $dateForMonthName = Carbon\Carbon::create($yearInt, $monthInt, 1);
                    $currentMonthName = strtolower($dateForMonthName->format('F'));
                    $currentYear = $yearInt;

                    // Define $startDate and $endDate based on $currentYear and $monthInt
                    $startDate = $dateForMonthName->copy()->startOfMonth();
                    $endDate = $dateForMonthName->copy()->endOfMonth();

                } catch (\Exception $e) {
                    // Fallback to current month if year/month are invalid
                    $startDate = Carbon\Carbon::now()->startOfMonth();
                    $endDate = Carbon\Carbon::now()->endOfMonth();
                    $currentYear = $startDate->year;
                    $currentMonthName = strtolower($startDate->format('F'));
                    $monthInt = $startDate->month; // Update monthInt to reflect fallback
                    Log::error("Error creating dates in Blade: " . $e->getMessage() . " | Year: " . $year . ", Month: " . $month); // Log error
                }
            @endphp

            {{-- Header Section --}}
            <div class="flex flex-wrap items-center justify-between mb-6 gap-4 animate-fadeInSlideUp" style="animation-delay: 50ms;">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Work Days Reports</h1>
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
                                         <option value="{{ $i }}" {{ $i == $currentYear ? 'selected' : ''}}>{{ $i }}</option> {{-- Use $currentYear --}}
                                     @endfor
                                 </select>
                             </div>
                             <div class="flex-shrink-0">
                                 <select name="month" class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm text-gray-700 focus:border-blue-500 focus:outline-none" x-model="month">
                                     <option value="">Month</option>
                                     @for($i = 1; $i <= 12; $i++)
                                         <option value="{{ $i }}" {{ $i == $monthInt ? 'selected' : ''}}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option> {{-- Use $monthInt --}}
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

            {{-- The main card for the table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden animate-fadeInSlideUp" style="animation-delay: 100ms;">
                
                 @if(session('message'))
                 <div class="m-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                     <strong class="font-bold">Success!</strong>
                     <span class="block sm:inline">{{ session('message') }}</span>
                     <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" data-bs-dismiss="alert" aria-label="Close">
                        <svg class="fill-current h-6 w-6 text-green-500" role="button" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><title>Close</title><path d="M14.348 14.849a1.2 1.2 0 0 1-1.697 0L10 11.819l-2.651 2.03a1.2 1.2 0 1 1-1.697-1.697l2.03-2.651-2.03-2.651a1.2 1.2 0 1 1 1.697-1.697L10 8.183l2.651-2.03a1.2 1.2 0 1 1 1.697 1.697l-2.03 2.651 2.03 2.651a1.2 1.2 0 0 1 0 1.697z"/></svg>
                     </button>
                 </div>
                 @endif

                {{-- Table --}}
                <div class="overflow-x-auto">
                    <table id="reportsTable" class="w-full text-sm text-gray-700">
                         <thead class="text-xs text-gray-800 uppercase bg-gray-100">
                            <tr>
                                {{-- Simplified Headers based on image --}}
                                <th scope="col" class="px-4 py-3 font-semibold">NOM D'EMPLOYÉ</th>
                                <th scope="col" class="px-4 py-3 font-semibold">JOURS BRUT</th>
                                <th scope="col" class="px-4 py-3 font-semibold">HEURES INTERNE</th>
                                <th scope="col" class="px-4 py-3 font-semibold">JOURS TRAVAIL</th>
                                <th scope="col" class="px-4 py-3 font-semibold">ABSENCE</th>
                                <th scope="col" class="px-4 py-3 font-semibold">MALADIE</th>
                                <th scope="col" class="px-4 py-3 font-semibold">FÉRIÉS</th>
                                <th scope="col" class="px-4 py-3 font-semibold">CONGÉS PAYÉS</th>
                                <th scope="col" class="px-4 py-3 font-semibold">ACQUIS</th>
                                <th scope="col" class="px-4 py-3 font-semibold">PAYÉS</th> {{-- Assuming this is 'Pris' --}}
                                <th scope="col" class="px-4 py-3 font-semibold">SOLDE</th>
                                <th scope="col" class="px-4 py-3 font-semibold">À RÉCUPÉRER</th>
                                <th scope="col" class="px-4 py-3 font-semibold">REMARQUES</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($workDaysRepports as $workDay)
                                @php 
                                    $employee = \App\Models\Employees::where('id', $workDay->employee_id)->where('archived', 0)->first();
                                    if (!$employee) continue; 

                                    // Internal Hours Calculation (Using $startDate, $endDate defined above)
                                    $totalMinutes = \App\Models\tasks_timer::where('employee_id', $workDay->employee_id)
                                        ->whereBetween('start_date', [$startDate, $endDate])
                                        ->whereNotNull('end_date')
                                        ->sum('duration');
                                    $totalHours = floor($totalMinutes / 60);
                                    $remainingMinutes = $totalMinutes % 60;
                                    $internalHoursFormatted = sprintf('%02dh:%02dm', $totalHours, $remainingMinutes);

                                    // Jours Travail Calculation (Includes Saturdays and recovered days)
                                    $monthNumForCalc = is_numeric($workDay->month) ? (int)$workDay->month : $monthInt; // Use workDay month if numeric, else fallback
                                    $yearForCalc = is_numeric($workDay->year) ? (int)$workDay->year : $currentYear; // Use workDay year if numeric, else fallback

                                    try {
                                        $firstDayLoop = Carbon\Carbon::create($yearForCalc, $monthNumForCalc, 1)->startOfDay();
                                        $lastDayLoop = $firstDayLoop->copy()->endOfMonth()->endOfDay();
                                        $saturdays = 0;
                                        for($dateLoop = $firstDayLoop->copy(); $dateLoop->lte($lastDayLoop); $dateLoop->addDay()) {
                                            if($dateLoop->isSaturday()) {
                                                $saturdays++;
                                            }
                                        }
                                        // Ensure values are numeric before adding, default to 0
                                        $joursTravail = (float)($workDay->days_worked ?? 0) + $saturdays + (float)($workDay->days_to_recover ?? 0); 
                                    } catch (\Exception $e) {
                                         Log::error("Error calculating Jours Travail for workDay ID {$workDay->id}: " . $e->getMessage());
                                         $joursTravail = 'Error'; // Display error in table
                                    }
                                    

                                @endphp
                            <tr class="bg-white border-b border-gray-200 hover:bg-gray-50"> 
                                <td class="px-4 py-3 font-medium text-gray-900 whitespace-nowrap">{{ $employee->name }}</td>
                                <td class="px-4 py-3">{{ $workDay->work_days_gross ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $internalHoursFormatted }} - {{ $workDay->days_worked ?? 0 }} jrs</td>
                                <td class="px-4 py-3">{{ $joursTravail }}</td>
                                <td class="px-4 py-3">{{ $workDay->days_of_unpaid_absence ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $workDay->sick_days ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $workDay->holidays ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $workDay->remaining_days_of_paid_leave ?? 0 }}</td> 
                                <td class="px-4 py-3">{{ $workDay->number_of_days_of_paid_leave_acquired ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $workDay->number_of_days_of_paid_leave_taken ?? 0 }}</td> 
                                <td class="px-4 py-3 font-semibold {{ ($workDay->holiday_balance ?? 0) < 0 ? 'text-red-600' : 'text-blue-600' }}">{{ $workDay->holiday_balance ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $workDay->days_to_recover ?? 0 }}</td>
                                <td class="px-4 py-3">{{ $workDay->remarque }}</td> 
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class="text-center px-4 py-6 text-gray-500">No work day reports found for this period.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                 {{-- Pagination Placeholder --}}
                 <div class="flex items-center justify-between p-4 border-t border-gray-200">
                    <button class="text-gray-400 hover:text-gray-600 disabled:opacity-50" disabled> {{-- Example: Disable previous --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M11.78 5.22a.75.75 0 0 1 0 1.06L8.06 10l3.72 3.72a.75.75 0 1 1-1.06 1.06l-4.25-4.25a.75.75 0 0 1 0-1.06l4.25-4.25a.75.75 0 0 1 1.06 0Z" clip-rule="evenodd" /></svg>
                    </button>
                    <span class="text-sm text-gray-600">Page 1 of 1</span> {{-- Placeholder text --}}
                     <button class="text-gray-400 hover:text-gray-600 disabled:opacity-50" disabled> {{-- Example: Disable next --}}
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="w-5 h-5"><path fill-rule="evenodd" d="M8.22 5.22a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06l-4.25 4.25a.75.75 0 0 1-1.06-1.06L11.94 10 8.22 6.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" /></svg>
                    </button>
                 </div>

                <div class="modal fade" id="employeeSelectionModal" tabindex="-1" aria-labelledby="employeeSelectionModalLabel" aria-hidden="true" style="z-index: 1050;">
                    {{-- Content remains the same as previous version --}}
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <div class="fixed inset-0 z-50 overflow-y-auto">
                         <div class="flex min-h-full items-center justify-center p-4 text-center">
                             <div class="relative bg-white rounded-lg shadow-xl transform transition-all sm:my-8 sm:w-full sm:max-w-3xl text-left"> 
                                 {{-- modal-content --}}
                                 <div class="flex items-center justify-between p-4 border-b rounded-t">
                                     {{-- modal-header --}}
                                     <h5 class="text-lg font-semibold text-gray-900" id="employeeSelectionModalLabel">Select Employees and Columns</h5>
                                     <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center" data-bs-dismiss="modal" aria-label="Close">
                                         <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                     </button>
                                 </div>
                                 <div class="p-6 space-y-4">
                                     {{-- modal-body --}}
                                     <div class="mb-3">
                                         <div class="flex items-center">
                                             <input id="selectAllEmployees" type="checkbox" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                             <label for="selectAllEmployees" class="ml-2 block text-sm font-medium text-gray-900">Select All Employees</label>
                                         </div>
                                     </div>
                                     <div class="employee-list max-h-48 overflow-y-auto space-y-2 mb-4 p-2 border rounded">
                                         {{-- Ensure this loops correctly --}}
                                         @php $uniqueEmployees = $workDaysRepports->pluck('employee_id')->unique(); @endphp
                                         @foreach($uniqueEmployees as $employeeId)
                                             @php
                                                 $employee = \App\Models\Employees::find($employeeId); // Find by ID
                                             @endphp
                                             @if($employee && !$employee->archived) {{-- Check if employee exists and not archived --}}
                                                 <div class="flex items-center">
                                                     <input id="employee{{ $employee->id }}" value="{{ $employee->id }}" type="checkbox" class="employee-checkbox h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                     <label for="employee{{ $employee->id }}" class="ml-2 block text-sm text-gray-900">{{ $employee->name }}</label>
                                                 </div>
                                             @endif
                                         @endforeach
                                     </div>

                                     <hr class="my-4">

                                     <h6 class="text-md font-semibold text-gray-900 mb-2">Unselect Columns to Exclude</h6>
                                     <form id="columnSelectionForm">
                                         <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2">
                                             {{-- Checkboxes matching the visible table columns --}}
                                             <div class="flex items-center">
                                                 <input id="col_work_days_gross" value="work_days_gross" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_work_days_gross" class="ml-2 block text-sm text-gray-900">Jours Brut</label>
                                             </div>
                                             <div class="flex items-center">
                                                 <input id="col_days_worked" value="days_worked" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_days_worked" class="ml-2 block text-sm text-gray-900">Heures Interne / Jours Travaillés</label>
                                             </div>
                                             <div class="flex items-center">
                                                <input id="col_days_worked_gross" value="days_worked_gross" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                <label for="col_days_worked_gross" class="ml-2 block text-sm text-gray-900">Jours Travail (incl. Samedi/Recup)</label>
                                             </div>
                                             <div class="flex items-center">
                                                 <input id="col_days_of_unpaid_absence" value="days_of_unpaid_absence" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_days_of_unpaid_absence" class="ml-2 block text-sm text-gray-900">Absence</label>
                                             </div>
                                             <div class="flex items-center">
                                                 <input id="col_sick_days" value="sick_days" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_sick_days" class="ml-2 block text-sm text-gray-900">Maladie</label>
                                             </div>
                                              <div class="flex items-center">
                                                 <input id="col_holidays" value="holidays" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_holidays" class="ml-2 block text-sm text-gray-900">Fériés</label>
                                             </div>
                                              <div class="flex items-center">
                                                 <input id="col_remaining_days_of_paid_leave" value="remaining_days_of_paid_leave" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_remaining_days_of_paid_leave" class="ml-2 block text-sm text-gray-900">Congés Payés (Restants)</label>
                                             </div>
                                             <div class="flex items-center">
                                                 <input id="col_number_of_days_of_paid_leave_acquired" value="number_of_days_of_paid_leave_acquired" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_number_of_days_of_paid_leave_acquired" class="ml-2 block text-sm text-gray-900">Acquis</label>
                                             </div>
                                             <div class="flex items-center">
                                                 <input id="col_number_of_days_of_paid_leave_taken" value="number_of_days_of_paid_leave_taken" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_number_of_days_of_paid_leave_taken" class="ml-2 block text-sm text-gray-900">Payés (Pris)</label>
                                             </div>
                                             <div class="flex items-center">
                                                 <input id="col_holiday_balance" value="holiday_balance" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_holiday_balance" class="ml-2 block text-sm text-gray-900">Solde</label>
                                             </div>
                                               <div class="flex items-center">
                                                 <input id="col_days_to_recover" value="days_to_recover" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_days_to_recover" class="ml-2 block text-sm text-gray-900">À Récupérer</label>
                                             </div>
                                              <div class="flex items-center">
                                                 <input id="col_remarque" value="remarque" type="checkbox" checked class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                                 <label for="col_remarque" class="ml-2 block text-sm text-gray-900">Remarques</label>
                                             </div>
                                         </div>
                                     </form>
                                 </div>
                                 <div class="flex items-center justify-between p-4 border-t border-gray-200 rounded-b space-x-2">
                                     {{-- modal-footer --}}
                                     <button type="button" class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 text-sm font-medium px-4 py-2 rounded-lg" data-bs-dismiss="modal">Close</button>
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
                 </div> {{-- End modal --}}

            </div> {{-- End Card --}}
        </div> {{-- End Main Content Padding --}}
    </div> {{-- End Main Content Area --}}
</div> {{-- End Flex Col --}}
{{-- Bootstrap/jQuery Scripts (Ensure these are loaded correctly) --}}
    @include('dashboard_layouts.scripts') 
    
    {{-- Livewire scripts --}}
    @livewireScripts

    {{-- Custom Page Scripts --}}
    <script>
        var userRole = "{{ Auth::user()->getRoleNames()->first() }}"; 
    </script>
    
    {{-- Alpine JS functions --}}
    <script>
        function PrjMngdatePicker() {
            return { year: '{{ $currentYear }}', month: '{{ $monthInt }}' } // Use current values
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

    {{-- Modal JS --}}
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
                    var month = @json($monthInt); // Use numeric month

                    let route;
                    // Role-based route logic
                    if (userRole === 'Responsable de projet') { route = "{{ route('Responsable_de_projet.workDaysReports.downloadRepports') }}"; } 
                    else if (userRole === 'Admin' || userRole === 'SuperAdmin') { route = "{{ route('admin.workDaysReports.downloadRepports') }}"; } 
                    else if (userRole === 'Employee') { route = "{{ route('employee.workDaysReports.downloadRepports') }}"; } 
                    else if (userRole === 'Responsable de projet et gestionnaire') { route = "{{ route('Responsable_gestionnaire.workDaysReports.downloadRepports') }}"; } 
                    else { console.error("User role invalid"); return; }

                    const url = `${route}?year=${year}&month=${month}&excluded_columns=${excludedColumns.join(',')}&employees=${selectedEmployees.join(',')}`;
                    window.location.href = url; // Redirect to download

                    // Close modal
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
                     var month = @json($monthInt); // Use numeric month

                    let route;
                    // Role-based route logic
                     if (userRole === 'Responsable de projet') { route = "{{ route('Responsable_de_projet.workDaysReports.downloadRepports') }}"; } 
                    else if (userRole === 'Admin' || userRole === 'SuperAdmin') { route = "{{ route('admin.workDaysReports.downloadRepports') }}"; } 
                    else if (userRole === 'Employee') { route = "{{ route('employee.workDaysReports.downloadRepports') }}"; } 
                    else if (userRole === 'Responsable de projet et gestionnaire') { route = "{{ route('Responsable_gestionnaire.workDaysReports.downloadRepports') }}"; } 
                    else { console.error("User role invalid"); return; }

                    // Add preview=1 parameter
                    const url = `${route}?year=${year}&month=${month}&excluded_columns=${excludedColumns.join(',')}&employees=${selectedEmployees.join(',')}&preview=1`; 
                    window.open(url, '_blank'); // Open preview in new tab

                    // Close modal
                     const modalElement = document.getElementById('employeeSelectionModal');
                     if (modalElement && typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                         if (modal) { modal.hide(); } 
                    }
                });
            }
        });
    </script>

</body>
</html>