<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
.font-weight-bolder{
    color : black !important;
}
th {
    font-size: 14px !important; 
}
.text-sm{
    color: #332f2f;
}

/* Add these new styles */
#editableTable th,
#editableTable td {
    border-right: 1px solid #dee2e6; /* Add vertical lines between columns */
    padding: 8px;
}

#editableTable th:last-child,
#editableTable td:last-child {
    border-right: none; /* Remove border from last column */
}

#editableTable {
    border: 1px solid #dee2e6; /* Add border around table */
}

#editableTable thead th {
    border-bottom: 2px solid #dee2e6; /* Add thicker bottom border for header */
}
</style>
<body class="g-sidenav-show bg-gray-100">
  <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
      style="display: none; visibility: hidden"></iframe></noscript>
  <div class="min-height-300 bg-primary position-absolute w-100"></div>

  @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif

  <main class="main-content position-relative border-radius-lg">
    <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
      id="navbarBlur" data-scroll="false">
      <div class="container-fluid py-1 px-3">

        @include('dashboard_layouts.navbar')

      </div>

      @php
        $year = request()->query('year', date('Y')); // Get year from URL or default to current year
        $month = request()->query('month', date('m')); // Get month from URL or default to current month
    @endphp


      <div class="container-fluid py-4">

        <div class="row mt-3">
          <div class="col-12">
            <div class="card">
              <div class="card-header p-3 pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Work Days Reports Table For: <strong>{{ strtolower(Carbon\Carbon::create()->month($currentMonth)->format('F')) }}-{{ $currentYear }}</strong></h6>
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        <div x-data="PrjMngdatePicker()" class="d-flex align-items-center">
                            
                            
                            <form method="GET" action="{{ route('Responsable_de_projet.workDaysReports.filterReports') }}" class="d-flex align-items-end me-2">
                                <div class="form-group me-2">
                                    <label for="year" class="me-1">Year</label>
                                    <select name="year" id="year" class="form-select" x-model="year">
                                        <option value="">Year</option>
                                        @for($i = date('Y'); $i >= 2000; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group me-2">
                                    <label for="month" class="me-1">Month</label>
                                    <select name="month" id="month" class="form-select" x-model="month">
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                            
                            
                            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#employeeSelectionModal">
                                Work Days Reports PDF
                            </button>
                            <form method="POST" action="{{ route('Responsable_de_projet.workDaysReports.nextMonthRepports') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-primary" id="nextMonth">Next Month Reports</button>
                            </form>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                        <div x-data="AdmindatePicker()" class="d-flex align-items-center">
                            
                            <form method="GET" action="{{ route('admin.workDaysReports.filterReports') }}" class="d-flex align-items-end me-2">
                                <div class="form-group me-2">
                                    <label for="year" class="me-1">Year</label>
                                    <select name="year" id="year" class="form-select" x-model="year">
                                        <option value="">Year</option>
                                        @for($i = date('Y'); $i >= 2000; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group me-2">
                                    <label for="month" class="me-1">Month</label>
                                    <select name="month" id="month" class="form-select" x-model="month">
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                            
                            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#employeeSelectionModal">
                                Work Days Reports PDF
                            </button>
                            <form method="POST" action="{{ route('admin.workDaysReports.nextMonthRepports') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-primary" id="nextMonth">Next Month Reports</button>
                            </form>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                        <div x-data="EmployeeDatePicker()" class="d-flex align-items-center">
                            
                            <form method="GET" action="{{ route('employee.workDaysReports.filterReports') }}" class="d-flex align-items-end me-2">
                                <div class="form-group me-2">
                                    <label for="year" class="me-1">Year</label>
                                    <select name="year" id="year" class="form-select" x-model="year">
                                        <option value="">Year</option>
                                        @for($i = date('Y'); $i >= 2000; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group me-2">
                                    <label for="month" class="me-1">Month</label>
                                    <select name="month" id="month" class="form-select" x-model="month">
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                            
                            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#employeeSelectionModal">
                                Work Days Reports PDF
                            </button>
                            <form method="POST" action="{{ route('employee.workDaysReports.nextMonthRepports') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-primary" id="nextMonth">Next Month Reports</button>
                            </form>
                        </div>
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        <div x-data="RespGestDatePicker()" class="d-flex align-items-center">
                            <form method="GET" action="{{ route('Responsable_gestionnaire.workDaysReports.filterReports') }}" class="d-flex align-items-end me-2">
                                <div class="form-group me-2">
                                    <label for="year" class="me-1">Year</label>
                                    <select name="year" id="year" class="form-select" x-model="year">
                                        <option value="">Year</option>
                                        @for($i = date('Y'); $i >= 2000; $i--)
                                            <option value="{{ $i }}">{{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <div class="form-group me-2">
                                    <label for="month" class="me-1">Month</label>
                                    <select name="month" id="month" class="form-select" x-model="month">
                                        <option value="">Month</option>
                                        @for($i = 1; $i <= 12; $i++)
                                            <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                                        @endfor
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary">Filter</button>
                            </form>
                            <button type="button" class="btn btn-primary me-2" data-bs-toggle="modal" data-bs-target="#employeeSelectionModal">
                                Work Days Reports PDF
                            </button>
                            <form method="POST" action="{{ route('Responsable_gestionnaire.workDaysReports.nextMonthRepports') }}" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-primary" id="nextMonth">Next Month Reports</button>
                            </form>
                        </div>
                    @endif
                </div>
                  @if(session('message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                  @endif

              </div>
              <div class="table-responsive p-4 pt-1">
                <table id="editableTable" class="table table-flush" style="width:100%">
                  <thead class="thead-light">
                    <tr class="text-center">
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ">
                        Nom d'employé
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1" style="color: #344CB7 !important;">
                        Nombre de jours <br>de travail <br> brut
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1" style="color: #344CB7 !important;">
                        Nombre jours <br> travaillés interne
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1" style="color: #344CB7 !important;">
                        NOMBRE DE JOURS <br> DE TRAVAIL SANS <br> LES JOURS FERIES <br> ET SANS CONGES PAYES
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1">
                        Nombre jours <br> d'absence
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1">
                        Nombre jours <br> maladie
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1" >
                        Nombre jours <br> fériés
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Reliquat jours <br> congé <br> payé
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Nombre jours <br> congé payé <br> acquis
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Nombre jours <br> congé payé <br> pris
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Solde <br> congés
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Dates des <br> jours congé <br> payé
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1" style="color: #344CB7 !important;"> 
                        Jours a récupérer
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Remarque
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1" style="color: #344CB7 !important;"> 
                        Interne remarque
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Mois
                    </th>
                    <th
                        class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                        Année
                    </th>
                    </tr>
                  </thead>
                  <tbody>
                    @foreach($workDaysRepports as $workDay)
                    <tr >
                        <td >
                            <p class="text-sm font-weight-bold mb-0">
                              @php 
                                $employee = \App\Models\Employees::where('id', $workDay->employee_id)->where('archived', 0)->first();
                              @endphp
                              @continue(!$employee)
                              <b>{{ optional($employee)->name }}</b>
                            </p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="work_days_gross" >
                            <p class="text-sm font-weight-bold mb-0" style="color: #344CB7 !important;">{{$workDay->work_days_gross}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="days_worked" >
                            @php
                                $totalMinutes = \App\Models\tasks_timer::where('employee_id', $workDay->employee_id)
                                ->whereBetween('start_date', [$startDate, $endDate])
                                ->whereNotNull('end_date')
                                ->sum('duration');

                                $totalHours = floor($totalMinutes / 60); // Get total hours
                                $remainingMinutes = $totalMinutes % 60; // Get remaining minutes
                            @endphp
                            <p class="text-sm font-weight-bold mb-0" style="color: #344CB7 !important;">{{ str_pad($totalHours, 2, '0', STR_PAD_LEFT) }}h:{{ str_pad($remainingMinutes, 2, '0', STR_PAD_LEFT) }}m - <b>{{$workDay->days_worked}} jrs</b></p>
                        </td>
                        <td class="" data-id="{{$workDay->id}}" data-column="days_worked_gross" >
                            @php
                                // Get the first and last day of the month
                                $firstDay = Carbon\Carbon::create($workDay->year, $workDay->month, 1);
                                $lastDay = $firstDay->copy()->endOfMonth();
                                
                                // Count Saturdays in the month
                                $saturdays = 0;
                                for($date = $firstDay; $date->lte($lastDay); $date->addDay()) {
                                    if($date->isSaturday()) {
                                        $saturdays++;
                                    }
                                }
                                
                                // Add Saturdays to worked days
                                $totalDays = $workDay->days_worked + $saturdays + $workDay->days_to_recover;
                            @endphp
                            <p class="text-sm font-weight-bold mb-0" style="color: #344CB7 !important;">{{$totalDays}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="days_of_unpaid_absence" >
                            <p class="text-sm font-weight-bold mb-0" >{{$workDay->days_of_unpaid_absence}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="sick_days" >
                            <p class="text-sm font-weight-bold mb-0" >{{$workDay->sick_days}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="holidays" >
                            <p class="text-sm font-weight-bold mb-0" >{{$workDay->holidays}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="remaining_days_of_paid_leave" >
                            <p class="text-sm font-weight-bold mb-0">{{$workDay->remaining_days_of_paid_leave}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="number_of_days_of_paid_leave_acquired">
                            <p class="text-sm font-weight-bold mb-0">{{$workDay->number_of_days_of_paid_leave_acquired}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="number_of_days_of_paid_leave_taken" >
                            <p class="text-sm font-weight-bold mb-0"  style="color: #344CB7 !important;">{{$workDay->number_of_days_of_paid_leave_taken}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="holiday_balance" >
                            <p class="text-sm font-weight-bold mb-0">{{$workDay->holiday_balance}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="dates_of_holidays" >
                            <p class="text-sm font-weight-bold mb-0">{{$workDay->dates_of_holidays}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="days_to_recover" >
                            <p class="text-sm font-weight-bold mb-0"  style="color: #344CB7 !important;">{{$workDay->days_to_recover}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="remarque" >
                            <p class="text-sm font-weight-bold mb-0">{{$workDay->remarque}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="interne_remarque" >
                            <p class="text-sm font-weight-bold mb-0" style="color: #344CB7 !important;">{{$workDay->interne_remarque}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="month" >
                            <p class="text-sm font-weight-bold mb-0">{{$workDay->month}}</p>
                        </td>
                        <td class="editable" data-id="{{$workDay->id}}" data-column="year" >
                            <p class="text-sm font-weight-bold mb-0">{{$workDay->year}}</p>
                        </td>
                    </tr>
                    @endforeach
                  </tbody>

                </table>

                <!-- Employee and Column Selection Modal -->
                <div class="modal fade" id="employeeSelectionModal" tabindex="-1" aria-labelledby="employeeSelectionModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="employeeSelectionModalLabel">Select Employees and Columns</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="selectAllEmployees">
                                        <label class="form-check-label" for="selectAllEmployees">Select All Employees</label>
                                    </div>
                                </div>
                                <div class="employee-list">
                                    @foreach($workDaysRepports->unique('employee_id') as $report)
                                        @php
                                        $employee = \App\Models\Employees::where('id', $report->employee_id)->where('archived', 0)->first();
                                        @endphp
                                        @if($employee)
                                        <div class="form-check">
                                            <input class="form-check-input employee-checkbox" type="checkbox" value="{{ $employee->id }}" id="employee{{ $employee->id }}">
                                            <label class="form-check-label" for="employee{{ $employee->id }}">
                                                {{ $employee->name }}
                                            </label>
                                        </div>
                                        @endif
                                    @endforeach
                                </div>

                                <hr>

                                <h6>Unselect Columns to Exclude</h6>
                                <form id="columnSelectionForm">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="work_days_gross" id="col_work_days_gross">
                                                <label class="form-check-label" for="col_work_days_gross">
                                                    Nombre jours de travail brut
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="days_worked" id="col_days_worked">
                                                <label class="form-check-label" for="col_days_worked">
                                                    Nombre jours travaillés interne
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="days_worked_gross" id="col_days_worked_gross">
                                                <label class="form-check-label" for="col_days_worked_gross">
                                                    Nombre de jours de travail sans les jours fériés et sans les congés payés
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="sick_days" id="col_sick_days" checked>
                                                <label class="form-check-label" for="col_sick_days">
                                                    Nombre jours maladie
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="holidays" id="col_holidays" checked>
                                                <label class="form-check-label" for="col_holidays">
                                                    Nombre jours fériés
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="days_of_unpaid_absence" id="col_days_of_unpaid_absence" checked>
                                                <label class="form-check-label" for="col_days_of_unpaid_absence">
                                                    Jours d'absence
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="dates_of_holidays" id="col_dates_of_holidays" checked>
                                                <label class="form-check-label" for="col_dates_of_holidays">
                                                    Dates des jours congé payé
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="days_to_recover" id="col_days_to_recover">
                                                <label class="form-check-label" for="col_days_to_recover">
                                                    Jours à récupérer
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="remaining_days_of_paid_leave" id="col_remaining_days_of_paid_leave" checked>
                                                <label class="form-check-label" for="col_remaining_days_of_paid_leave">
                                                    Reliquat jours congé payé
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="number_of_days_of_paid_leave_acquired" id="col_number_of_days_of_paid_leave_acquired" checked>
                                                <label class="form-check-label" for="col_number_of_days_of_paid_leave_acquired">
                                                    Nombre jours congé payé acquis
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="number_of_days_of_paid_leave_taken" id="col_number_of_days_of_paid_leave_taken" checked>
                                                <label class="form-check-label" for="col_number_of_days_of_paid_leave_taken">
                                                    Nombre jours congé payé pris
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="holiday_balance" id="col_holiday_balance" checked>
                                                <label class="form-check-label" for="col_holiday_balance">
                                                    Solde congés
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="remarque" id="col_remarque" checked>
                                                <label class="form-check-label" for="col_remarque">
                                                    Remarque
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="interne_remarque" id="col_interne_remarque">
                                                <label class="form-check-label" for="col_interne_remarque">
                                                    Interne remarque
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="month" id="col_month" checked>
                                                <label class="form-check-label" for="col_month">
                                                    Mois
                                                </label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" value="year" id="col_year" checked>
                                                <label class="form-check-label" for="col_year">
                                                    Année
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <div>
                                    <button type="button" class="btn btn-success me-2" id="previewReportBtn">
                                        <i class="fa fa-eye"></i> Preview & Email
                                    </button>
                                    <button type="button" class="btn btn-primary" id="generateReportBtn">
                                        <i class="fa fa-download"></i> Download PDF
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
              </div>

            </div>
          </div>
        </div>



  </main>

<style>
  .delete-warning {
    color: #D8000C; /* Red color for the warning text */
    background-color: #FFD2D2; /* Light red background */
    padding: 5px; /* Padding around the text */
    margin: 8px 0; /* Margin to separate it from other elements */
    border-radius: 5px; /* Rounded corners */
    border: 1px solid #D8000C; /* Red border */
    font-weight: bold; /* Make the text bold */
}
</style>
  @include('dashboard_layouts.scripts')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script>
    var userRole = "{{ Auth::user()->getRoleNames()->first() }}";
</script>
<script>
    $(document).ready(function(){
        $("#editableTable .editable").dblclick(function(){
            var columnName = $(this).data('column');
            var originalContent = $(this).text();

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
                    }); SuperAdmin
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

<script src="//unpkg.com/alpinejs" defer></script>
<script>
    function PrjMngdatePicker() {
        return {
            year: '',
            month: '',
            updateHref() {
                const baseUrl = "{{route('Responsable_de_projet.workDaysReports.downloadRepports')}}";
                let urlWithParams = baseUrl;
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0'); // Months are zero-based

                if (!this.year) {
                    this.year = currentYear;
                }
                if (!this.month) {
                    this.month = currentMonth;
                }

                urlWithParams += `?year=${this.year}&month=${this.month}`;
                window.location.href = urlWithParams; // Redirect to the updated URL
            }
        }
    }

    function AdmindatePicker() {
        return {
            year: '',
            month: '',
            updateHref() {
                const baseUrl = "{{route('admin.workDaysReports.downloadRepports')}}";
                let urlWithParams = baseUrl;
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0'); // Months are zero-based

                if (!this.year) {
                    this.year = currentYear;
                }
                if (!this.month) {
                    this.month = currentMonth;
                }

                urlWithParams += `?year=${this.year}&month=${this.month}`;
                window.location.href = urlWithParams; // Redirect to the updated URL
            }
        }
    }
    function EmployeeDatePicker() {
        return {
            year: '',
            month: '',
            updateHref() {
                const baseUrl = "{{route('employee.workDaysReports.downloadRepports')}}";
                let urlWithParams = baseUrl;
                const currentDate = new Date();
                const currentYear = currentDate.getFullYear();
                const currentMonth = String(currentDate.getMonth() + 1).padStart(2, '0'); // Months are zero-based

                if (!this.year) {
                    this.year = currentYear;
                }
                if (!this.month) {
                    this.month = currentMonth;
                }

                urlWithParams += `?year=${this.year}&month=${this.month}`;
                window.location.href = urlWithParams; // Redirect to the updated URL
            }
        }
    }
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Select All functionality
        document.getElementById('selectAllEmployees').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.employee-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });

    // Generate Report button functionality
    document.getElementById('generateReportBtn').addEventListener('click', function() {
        const selectedColumns = Array.from(document.querySelectorAll('#columnSelectionForm input[type="checkbox"]:not(:checked)'))
            .map(cb => cb.value);

        const selectedEmployees = Array.from(document.querySelectorAll('.employee-checkbox:checked'))
            .map(cb => cb.value);

        //const year = document.querySelector('#year').value || new Date().getFullYear();
        //const month = document.querySelector('#month').value || (new Date().getMonth() + 1);

        var year = @json($currentYear);
        var month = @json($currentMonth);

        // Determine the route based on user role
        let route;
        if (userRole === 'Responsable de projet') {
            route = "{{ route('Responsable_de_projet.workDaysReports.downloadRepports') }}";
        } else if (userRole === 'Admin' || userRole === 'SuperAdmin') {
            route = "{{ route('admin.workDaysReports.downloadRepports') }}";
        } else if (userRole === 'Employee') {
            route = "{{ route('employee.workDaysReports.downloadRepports') }}";
        } else if (userRole === 'Responsable de projet et gestionnaire'){
            route = "{{ route('Responsable_gestionnaire.workDaysReports.downloadRepports') }}";
        } else {
            console.error("User role does not match any condition");
            return; // Exit if no valid role is found
        }

        const url = `${route}?year=${year}&month=${month}&excluded_columns=${selectedColumns.join(',')}&employees=${selectedEmployees.join(',')}`;
        window.location.href = url;

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('employeeSelectionModal'));
        modal.hide();
    });
    
    // Preview Report button functionality
    document.getElementById('previewReportBtn').addEventListener('click', function() {
        const selectedColumns = Array.from(document.querySelectorAll('#columnSelectionForm input[type="checkbox"]:not(:checked)'))
            .map(cb => cb.value);

        const selectedEmployees = Array.from(document.querySelectorAll('.employee-checkbox:checked'))
            .map(cb => cb.value);

        var year = @json($currentYear);
        var month = @json($currentMonth);

        // Determine the route based on user role
        let route;
        if (userRole === 'Admin' || userRole === 'SuperAdmin') {
            route = "{{ route('admin.workDaysReports.downloadRepports') }}";
        } else if (userRole === 'Responsable de projet') {
            route = "{{ route('Responsable_de_projet.workDaysReports.downloadRepports') }}";
        } else if (userRole === 'Employee') {
            route = "{{ route('employee.workDaysReports.downloadRepports') }}";
        } else if (userRole === 'Responsable de projet et gestionnaire'){
            route = "{{ route('Responsable_gestionnaire.workDaysReports.downloadRepports') }}";
        } else {
            console.error("User role does not match any condition");
            return; // Exit if no valid role is found
        }

        const url = `${route}?year=${year}&month=${month}&excluded_columns=${selectedColumns.join(',')}&employees=${selectedEmployees.join(',')}&preview=1`;
        window.location.href = url;

        // Close the modal
        const modal = bootstrap.Modal.getInstance(document.getElementById('employeeSelectionModal'));
        modal.hide();
    });
});
</script>

</body>

</html>