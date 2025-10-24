<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  td[data-column="remarque"] {
    max-width: 200px; /* Adjust the width as needed */
    white-space: normal;
    word-wrap: break-word;
  }
</style>

<style>
  a {
    color: blue;
}
.font-weight-bolder{
    color : black !important;
}
.text-sm{
    color: #332f2f;
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

                <div class="container-fluid py-4">
                    <div class="row mb-5">
                        <div class="col-12">
                            <div class="multisteps-form mb-5">

                                <div class="row">
                                    <div class="col-12 col-md-4">
                                        <div class="card  p-3 border-radius-xl bg-white mb-3 " >
                                                <div style="display: flex; align-items: center;">
                                                    <div>
                                                        @php
                                                            $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', 
                                                            '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F']; 
                                                        @endphp 
                                                        @if($employee->image)
                                                            <img class="avatar avatar-m me-3"  src="{{ Storage::disk('do_spaces')->url('employee_images/' . $employee->image) }}" alt="employee Image">
                                                        @else
                                                        <div class="avatar avatar-sm rounded-circle me-2"
                                                            style="background-color: {{ $colors[0] }};">
                                                            <span class="initial-letter">{{ strtoupper(substr($employee->name,
                                                                0, 1)) }}</span>
                                                        </div>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h5 class="font-weight-bolder mb-0">Employee Infos</h5> 
                                                        <p class="mb-0 text-sm">Mandatory informations</p>
                                                    </div>
                                                </div>
                                            <div class="multisteps-form__content">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Name : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="name"  type="text"
                                                            name="name" value="{{ $employee->name }}" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Login Email : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="email"  type="text"
                                                            name="email" value="{{ $employee->email }}" readonly/>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Phone Number : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="phoneNumber"  type="text"
                                                            name="phoneNumber" value="{{ $employee->phoneNumber }}" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Contact Email : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="contact_email"  type="text"
                                                            name="contact_email" value="{{ $employee->contact_email }}" readonly/>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Age : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="age"  type="text"
                                                            name="age" value="{{ $employee->age }}" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Password : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="password"  type="text"
                                                            name="password" value="{{ $employee->password }}" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Work Hours Limit : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="workhours_limit"  type="text"
                                                            name="workHours_limit" value="{{ $employee->workHours_limit }}" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#contactModal" style="margin-top: 31px;">
                                                            Send Email
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="card  p-3 border-radius-xl bg-white mb-3" >
                                            <h5 class="font-weight-bolder">Professional Infos :</h5>
                                            <div class="multisteps-form__content">
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>CNSS Number : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="cnss"  type="text"
                                                            name="cnss" value="{{ $employee->cnss }}" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Bank Account Number : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="bankAccountNumber"  type="text"
                                                            name="bankAccountNumber"
                                                            value="{{ $employee->bankAccountNumber }}" readonly/>
                                                    </div>
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12 col-sm-6">
                                                        <label>Release Date : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="Date_Release"  type="text"
                                                            name="Date_Release" value="{{ $employee->Date_Release }}" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Salary : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="salary"  type="text"
                                                            name="salary"
                                                            value="{{ $employee->salary }}€" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                        <label>Cost Per Hour : </label>
                                                        <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="cost_per_hour"  type="text"
                                                            name="cost_per_hour"
                                                            value="{{ $employee->cost_per_hour }}€" readonly/>
                                                    </div>
                                                    <div class="col-12 col-sm-6">
                                                            <label>Cotisation CNSS : </label>
                                                            <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="Cotisation_CNSS" 
                                                                type="text" name="Cotisation_CNSS"
                                                                value="{{ $employee->Cotisation_CNSS }}€" readonly/>
                                                        </div>
                                                </div>
                                                <div class="row mt-3">
                                                        
                                                        <div class="col-12 col-sm-6 mt-3 mt-sm-0">
                                                            <label>Cotisation AMO : </label>
                                                            <input class="multisteps-form__input form-control editable"  data-id="{{$employee->id}}" data-column="Cotisation_AMO"  
                                                                type="text" name="Cotisation_AMO"
                                                                value="{{ $employee->Cotisation_AMO }}€" readonly/>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <div class="card  p-3 border-radius-xl bg-white mb-3" >
                                            <h5 class="font-weight-bolder">Roles :</h5> 
                                            <div class="multisteps-form__content">
                                                <div class="row mt-3">
                                                    @foreach($role_name as $role)
                                                    <div class="form-check">
                                                        <input id="vue-checkbox-{{ $role }}" name="roles[]"
                                                            type="checkbox" value="{{ $role }}" disabled
                                                            @if($user->hasRole($role)) checked @endif>
                                                        <label for="vue-checkbox-{{ $role }}" class="form-check-label">
                                                            {{ $role }}
                                                        </label>
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <div class="row mt-3">
                                                    <div class="col-12">
                                                        @if(Auth::user()->hasRole('Admin'))
                                                        @can('Edit employee roles')
                                                        <a href="{{route('admin.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm">Edit Roles</a>
                                                        @endcan
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                                        @can('Edit employee roles')
                                                        <a href="{{route('Responsable_de_projet.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm">Edit Roles</a>
                                                        @endcan
                                                        @endif
                                                        @if(Auth::user()->hasRole('Employee'))
                                                        @can('Edit employee roles')
                                                        <a href="{{route('employee.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm">Edit Roles</a>
                                                        @endcan
                                                        @endif
                                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                        @can('Edit employee roles')
                                                        <a href="{{route('Responsable_gestionnaire.employees.edit', $employee->id)}}" class="btn btn-primary btn-sm">Edit Roles</a>
                                                        @endcan
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card table-responsive p-3 border-radius-xl bg-white mb-3" >
                                    <h5 class="font-weight-bolder">Work Days Repports :</h5>
                                        <br>
                                    <div class="col-12 col-lg-12">
                                        <div class="card">
                                            <div class="card-body">
                                                <div class="table-responsive" style="overflow: visible;"> 
                                                    <table id="editableTable" class="table align-items-center mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1">
                                                                    Nombre jours <br> travaillés interne
                                                                </th>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1">
                                                                    Nombre jours <br> maladie
                                                                </th>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1">
                                                                    Nombre jours <br> fériés
                                                                </th>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                                                                    Reilquat jours <br> congé <br> payé
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
                                                                    Jours <br> d'absence
                                                                </th>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                                                                    Dates des jours <br> congé payé
                                                                </th>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                                                                    Remarque
                                                                </th>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                                                                    Mois
                                                                </th>
                                                                <th
                                                                    class="text-uppercase text-dark text-x font-weight-bolder opacity-10  ps-1"> 
                                                                    Annee
                                                                </th>
                                                                <th class="text-center text-uppercase text-secondary text-x font-weight-bolder opacity-10"></th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @if($work_days_repports)
                                                            <tr >
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="days_worked" >
                                                                    @php
                                                                        $totalMinutes = $totalHours * 60; // Convert total hours to minutes
                                                                        $hours = floor($totalMinutes / 60); // Calculate hours
                                                                        $minutes = $totalMinutes % 60; // Calculate remaining minutes
                                                                    @endphp
                                                                    <p class="text-sm font-weight-bold mb-0">{{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m - <b>{{$work_days_repports->days_worked}} jrs</b></p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="sick_days" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->sick_days}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="holidays" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->holidays}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->remaining_days_of_paid_leave}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->number_of_days_of_paid_leave_acquired}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->number_of_days_of_paid_leave_taken}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="holiday_balance" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->holiday_balance}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->days_of_unpaid_absence}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->dates_of_holidays}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="remarque" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->remarque}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="month" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->month}}</p>
                                                                </td>
                                                                <td class="editable" data-id="{{$work_days_repports->id}}" data-column="year" >
                                                                    <p class="text-sm font-weight-bold mb-0">{{$work_days_repports->year}}</p>
                                                                </td>
                                                            </tr>
                                                            @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
<br>
                                <div class="card  p-3 border-radius-xl bg-white mb-3" >
                                    <h5 class="font-weight-bolder">Teams :</h5>
                                </div>
                                <div class="row mb-3">
                                    @php
                                        $team_id = App\Models\team_members::where('employee_id', $employee->id)->pluck('team_id')->unique();
                                        $teams = App\Models\Teams::whereIn('id', $team_id)->get();
                                    @endphp
                                    @foreach($teams as $team)
                                    <div class="col-lg-4 col-md-6 mb-3 mb-lg-0">
                                        <div class="card h-100">
                                            <div class="card-header d-flex flex-column align-items-start">
                                                <div class="d-flex justify-content-between w-100">
                                                    <h5 class="mb-0 text-capitalize">{{$team->name}}</h5>
                                                </div>
                                                <span class="font-weight-bold" style="font-size: 0.8em; padding-left: 10px;">Team
                                                    Members :</span>
                                            </div>

                                            <div class="card-body pt-0">
                                                <ul class="list-group list-group-flush">
                                                    @foreach(App\Models\team_members::where('team_id', $team->id)->get() as $team_member)
                                                    <li class="list-group-item px-0">
                                                        <div class="row align-items-center">
                                                            <div class="col-auto d-flex align-items-center">
                                                                @php
                                                                    $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                                                                    '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                                    $employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                                                @endphp
                                                                @if(App\Models\Employees::where('id', $team_member->employee_id)->first()->image)
                                                                <img class="avatar avatar-sm rounded-circle me-2" 
                                                                    src="{{ Storage::disk('do_spaces')->url('employee_images/' . $employee->image) }}" 
                                                                    alt="employee Image">                                                                
                                                                @else
                                                                <div class="avatar avatar-sm rounded-circle me-2"
                                                                    style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                                                                    <span class="initial-letter">{{ strtoupper(substr($team_member->employee_name, 0, 1)) }}</span>
                                                                </div>
                                                                @endif   
                                                            </div>
                                                            <div class="col ml-2">
                                                                <h6 class="mb-0">
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                    @if(Auth::user()->can('View employee'))
                                                                    <a href="{{route('admin.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                                    @else
                                                                    {{$team_member->employee_name}}
                                                                    @endif
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                                    @if(Auth::user()->can('View employee'))
                                                                    <a href="{{route('Responsable_de_projet.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                                    @else
                                                                    {{$team_member->employee_name}}
                                                                    @endif
                                                                @endif
                                                                @if(Auth::user()->hasRole('Employee'))
                                                                    @if(Auth::user()->can('View employee'))
                                                                    <a href="{{route('employee.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                                    @else
                                                                    {{$team_member->employee_name}}
                                                                    @endif
                                                                @endif
                                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                    @if(Auth::user()->can('View employee'))
                                                                    <a href="{{route('Responsable_gestionnaire.employees.show', $team_member->employee_id)}}">{{$team_member->employee_name}}</a>
                                                                    @else
                                                                    {{$team_member->employee_name}}
                                                                    @endif
                                                                @endif
                                                                </h6>
                                                                @php
                                                                    $employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                                                    $employee_user=App\Models\User::where('id', $employee->user_id)->first();
                                                                @endphp
                                                                @if($employee_user->is_loggedIn == 0)
                                                                    <span class="badge badge-danger badge-sm">Off Work</span>
                                                                @elseif($employee_user->is_loggedIn == 1)
                                                                    <span class="badge badge-success badge-sm">On Work</span>
                                                                @else
                                                                    <span class="badge badge-warning badge-sm">On Pause</span>
                                                                @endif
                                                            </div>
                                                            <div class="col-auto">
                                                            @if(Auth::user()->hasRole('Admin'))
                                                                @can('View employee')
                                                                    <a href="{{route('admin.employees.show', $team_member->employee_id)}}"
                                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                                        view
                                                                    </a>
                                                                @endcan
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                                @can('View employee')
                                                                    <a href="{{route('Responsable_de_projet.employees.show', $team_member->employee_id)}}"
                                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                                        view
                                                                    </a>
                                                                @endcan
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                                @can('View employee')
                                                                    <a href="{{route('employee.employees.show', $team_member->employee_id)}}"
                                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                                        view
                                                                    </a>
                                                                @endcan
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                                @can('View employee')
                                                                    <a href="{{route('Responsable_gestionnaire.employees.show', $team_member->employee_id)}}"
                                                                        type="button" class="btn btn-outline-primary btn-xs mb-0">
                                                                        view
                                                                    </a>
                                                                @endcan
                                                            @endif
                                                            </div>
                                                        </div>
                                                    </li>
                                                    @endforeach

                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    @if ($loop->iteration % 3 == 0 && !$loop->last)
                                    <div class="row mb-3">
                                    </div>
                                    @endif
                                    @endforeach
                                </div>
                                

                                <div class="card p-3 border-radius-xl bg-white mb-3">
                                    <h5 class="font-weight-bolder">Projects :</h5>
                                    <br>
                                    @php
                                        $team_project_id = App\Models\project_teams::whereIn('team_id', $team_id)->pluck('project_id');
                                        $projects_tasks_id = App\Models\employees_tasks::where('employee_id', $employee->id)->pluck('task_id');
                                        $projects_tasks = App\Models\Tasks::whereIn('id', $projects_tasks_id)->pluck('project_id');
                                        $projects1 = App\Models\Project::whereIn('id', $projects_tasks)->get();
                                        $projects = App\Models\Project::whereIn('id', $team_project_id)->get();
                                        $combined_project_ids = $projects1->concat($projects)->unique();
                                        
                                        // Group projects by client
                                        $projectsByClient = $combined_project_ids->groupBy('client_id');
                                    @endphp

                                    <div class="table-responsive p-0">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10">Client / Project</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Budget</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">CPH</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Duration</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Estimated Hours</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Difference</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Progress</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">TVA</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Teams</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($projectsByClient as $client_id => $clientProjects)
                                                    @php
                                                        $client = App\Models\Client::find($client_id);
                                                    @endphp
                                                    
                                                    <tr>
                                                        <td colspan="9" class="bg-light" style="background-color: lightgray !important;">
                                                            <h6 class="font-weight-bolder mb-0" style="color: black !important;">
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                    @if(Auth::user()->can('View client'))
                                                                        <a href="{{route('admin.clients.show', $client_id)}}">{{$client->name}}</a>
                                                                    @else
                                                                        {{$client->name}}
                                                                    @endif
                                                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                                    @if(Auth::user()->can('View client'))
                                                                        <a href="{{route('Responsable_de_projet.clients.show', $client_id)}}">{{$client->name}}</a>
                                                                    @else
                                                                        {{$client->name}}
                                                                    @endif
                                                                @elseif(Auth::user()->hasRole('Employee'))
                                                                    @can('View client')
                                                                        <a href="{{route('employee.clients.show', $client_id)}}">{{$client->name}}</a>
                                                                    @else
                                                                        {{$client->name}}
                                                                    @endcan
                                                                @endif
                                                            </h6>
                                                        </td>
                                                    </tr>

                                                    @foreach($clientProjects as $project)
                                                    <tr>
                                                        <td>
                                                            <div class="d-flex px-2">
                                                                <div class="my-auto">
                                                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                                                        @if(Auth::user()->can('View project'))
                                                                            <a href="{{route('Responsable_de_projet.projects.show',$project->id)}}">
                                                                                <h6 class="mb-0 text-sm">{{$project->name}}</h6>
                                                                            </a>
                                                                        @else   
                                                                            {{$project->name}}
                                                                        @endif
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Admin'))
                                                                        @if(Auth::user()->can('View project'))
                                                                            <a href="{{route('admin.projects.show',$project->id)}}">
                                                                                <h6 class="mb-0 text-sm">{{$project->name}}</h6>
                                                                            </a>
                                                                        @else   
                                                                            {{$project->name}}
                                                                        @endif
                                                                    @endif
                                                                    @if(Auth::user()->hasRole('Employee'))
                                                                        @can('View project')
                                                                            <a href="{{route('employee.projects.show',$project->id)}}">
                                                                                <h6 class="mb-0 text-sm">{{$project->name}}</h6>
                                                                            </a>
                                                                        @else   
                                                                            {{$project->name}}
                                                                        @endif
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><p class="text-sm font-weight-bold mb-0">€{{$project->budget}}</p></td>
                                                        <td><p class="text-sm font-weight-bold mb-0">€{{$project->cost_per_hour}}</p></td>
                                                        @php 
                                                            $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                            $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->where('employee_id', $employee->id)->sum('duration');
                                                            $difference = $project->estimated_hours * 60 - $totalduration;
                                                            
                                                            $hours = floor(abs($totalduration) / 60);
                                                            $minutes = abs($totalduration) % 60;
                                                            
                                                            $diffhours = floor(abs($difference) / 60);
                                                            $diffminutes = abs($difference) % 60;

                                                            $estimatedMinutes = $project->estimated_hours * 60;
                                                            $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                                                            $formattedProgressPercentage = number_format($progressPercentage, 2);
                                                        @endphp
                                                        <td><p class="text-sm font-weight-bold mb-0">{{$hours}}h:{{$minutes}}m</p></td>
                                                        <td class="editable" data-id="{{$project->id}}" data-column="estimated_hours">
                                                            <p class="text-sm font-weight-bold mb-0">{{$project->estimated_hours}}h</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-sm font-weight-bold"> 
                                                                @if($project->estimated_hours !== null)
                                                                    @if($difference > 0)
                                                                        <span class="text-success">+{{$diffhours}}h:{{$diffminutes}}m</span>
                                                                    @elseif($difference < 0)
                                                                        <span class="text-danger">-{{$diffhours}}h:{{$diffminutes}}m</span>
                                                                    @else
                                                                        <span style="color: blue;">{{$diffhours}}h:{{$diffminutes}}m</span>
                                                                    @endif
                                                                @endif
                                                            </p>
                                                        </td>
                                                        <td>
                                                            <div class="progress" style="height: 12px; background-color: #e9ecef; border-radius: 0.25rem;">
                                                                <div class="progress-bar 
                                                                    @if($progressPercentage < 50) bg-success 
                                                                    @elseif($progressPercentage < 75) bg-warning 
                                                                    @else bg-danger 
                                                                    @endif" 
                                                                    role="progressbar" 
                                                                    style="width: {{$formattedProgressPercentage}}%; font-size: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #fff; text-align: center; white-space: nowrap; transition: width 0.6s ease;" 
                                                                    aria-valuenow="{{$formattedProgressPercentage}}" 
                                                                    aria-valuemin="0" 
                                                                    aria-valuemax="100">
                                                                    {{$formattedProgressPercentage}}%
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td><p class="text-sm font-weight-bold mb-0">%{{$project->tva}}</p></td>
                                                        <td>
                                                            <p class="text-sm font-weight-bold mb-0">
                                                                @forEach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                                                    {{$project_team->team_name}}<br>
                                                                @endforEach
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="card p-3 border-radius-xl bg-white mb-3">
                                    <h5 class="font-weight-bolder">Tasks :</h5>
                                    <br>
                                    @php
                                        $tasks_collection = collect([]);
                                        $employee_tasks_ids = App\Models\employees_tasks::where('employee_id', $employee->id)->get();
                                        foreach($employee_tasks_ids as $employee_task_id){
                                            $task = App\Models\Tasks::where('id', $employee_task_id->task_id)->first();
                                            $tasks_collection->push($task);
                                        }
                                        
                                        // Group tasks by project
                                        $tasksByProject = $tasks_collection->groupBy('project_id');
                                    @endphp

                                    <div class="table-responsive p-0">
                                        <table class="table align-items-center mb-0">
                                            <thead>
                                                <tr class="text-center">
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10">Project / Task</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Duration</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Estimated Hours</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Difference</th>
                                                    <th class="text-uppercase text-dark text-x font-weight-bolder opacity-10 ps-2">Progress</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($tasksByProject as $project_id => $projectTasks)
                                                    @php
                                                        $project = App\Models\Project::find($project_id);
                                                    @endphp
                                                    
                                                    <tr>
                                                        <td colspan="5" class="bg-light" style="background-color: lightgray !important;">
                                                            <h6 class="font-weight-bolder mb-0" style="color: black !important;">
                                                                @if(Auth::user()->hasRole('Admin'))
                                                                    @if(Auth::user()->can('View project'))
                                                                        <a href="{{route('admin.projects.show', $project_id)}}">{{$project->name}}</a>
                                                                    @else
                                                                        {{$project->name}}
                                                                    @endif
                                                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                                    @if(Auth::user()->can('View project'))
                                                                        <a href="{{route('Responsable_de_projet.projects.show', $project_id)}}">{{$project->name}}</a>
                                                                    @else
                                                                        {{$project->name}}
                                                                    @endif
                                                                @elseif(Auth::user()->hasRole('Employee'))
                                                                    @can('View project')
                                                                        <a href="{{route('employee.projects.show', $project_id)}}">{{$project->name}}</a>
                                                                    @else
                                                                        {{$project->name}}
                                                                    @endcan
                                                                @endif
                                                            </h6>
                                                        </td>
                                                    </tr>

                                                    @foreach($projectTasks as $task)
                                                    <tr>
                                                        <td>
                                                            <div class="text-center">
                                                                <div class="my-auto">
                                                                    <h6 class="mb-0 text-sm">{{$task->title}}</h6>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <p class="text-sm font-weight-bold mb-0">
                                                            @php 
                                                                $tasktotalduration = App\Models\tasks_timer::where('task_id', $task->id)->where('employee_id', $employee->id)->sum('duration');
                                                                $taskhours = floor($tasktotalduration / 60);
                                                                $taskminutes = $tasktotalduration % 60;
                                                            @endphp
                                                            @if(Auth::user()->hasRole('Admin'))
                                                                <a href="{{ route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Responsable de projet'))
                                                                <a href="{{route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id])}}">
                                                            @endif
                                                            @if(Auth::user()->hasRole('Employee'))
                                                                <a href="{{route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id])}}">
                                                            @endif
                                                            {{$taskhours}}h:{{$taskminutes}}m
                                                            </a>
                                                            </p>
                                                        </td>
                                                        <td>
                                                            <p class="text-sm font-weight-bold mb-0">{{$task->estimated_hours}}h</p>
                                                        </td>
                                                        <td>
                                                            <p class="text-sm font-weight-bold">
                                                            @php
                                                                $taskdifference = $task->estimated_hours * 60 - $tasktotalduration;
                                                                $hours = floor(abs($taskdifference) / 60);
                                                                $minutes = abs($taskdifference) % 60;

                                                                $taskestimatedMinutes = $task->estimated_hours * 60;
                                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                                            @endphp
                                                            @if($task->estimated_hours !== null)
                                                                @if($taskdifference > 0)
                                                                    <p class="text-sm font-weight-bold mb-0 text-success">+{{$hours}}h:{{$minutes}}m</p>
                                                                @elseif($taskdifference < 0)
                                                                    <p class="text-sm font-weight-bold mb-0 text-danger">-{{$hours}}h:{{$minutes}}m</p>
                                                                @else
                                                                    <span style="color: blue;">{{$hours}}h:{{$minutes}}m</span>
                                                                @endif
                                                            @endif
                                                            </p>
                                                        </td>
                                                        <td>
                                                            <div class="progress" style="height: 12px; background-color: #e9ecef; border-radius: 0.25rem;">
                                                                <div class="progress-bar 
                                                                    @if($taskprogressPercentage < 50) bg-success 
                                                                    @elseif($taskprogressPercentage < 75) bg-warning 
                                                                    @else bg-danger 
                                                                    @endif" 
                                                                    role="progressbar" 
                                                                    style="width: {{$taskformattedProgressPercentage}}%; font-size: 10px; display: flex; align-items: center; justify-content: center; overflow: hidden; color: #fff; text-align: center; white-space: nowrap; transition: width 0.6s ease;" 
                                                                    aria-valuenow="{{$taskformattedProgressPercentage}}" 
                                                                    aria-valuemin="0" 
                                                                    aria-valuemax="100">
                                                                    {{$taskformattedProgressPercentage}}%
                                                                </div>
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
                    </div>

                </div>

            </div>

    </main>

    <!-- Email Modal -->
    <div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="contactModalLabel">Send Email</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="emailForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="employee_id" value="{{ $employee->id }}">
                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject</label>
                            <input type="text" class="form-control" id="subject" name="subject" required>
                        </div>
                        <div class="mb-3">
                            <label for="body" class="form-label">Message</label>
                            <textarea class="form-control" id="body" name="body" rows="6" required>Bonjour, 
Veuillez trouver ci-joint le document, pour votre information.
N'hésitez pas à me contacter si vous avez la moindre question.
Bien cordialement,
Pour Stabel</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="attachments" class="form-label">Attachments</label>
                            <input type="file" class="form-control" id="attachments" name="attachments[]" multiple>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" id="sendEmailBtn">Send Email</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    @include('dashboard_layouts.scripts')
    @can('Update employee')
    <script>
        $(document).ready(function(){
            $(".editable").dblclick(function(){
                $(this).prop('readonly', false);
            });

            $(".editable").blur(function(){
                $(this).prop('readonly', true);
                var newContent = $(this).val();
                var employeeId = $(this).data('id');
                var columnName = $(this).attr('name');
                // Make AJAX call to update the database here

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
        });
    </script>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function(){
    $("#editableTable .editable").dblclick(function(){
        var originalContent = $(this).text().trim();
        var width = $(this).width();
        $(this).html("<input type='text' value='" + originalContent + "' style='width: 100%; min-width: " + width + "px;' />");
        var input = $(this).children().first();
        input.focus();

        // Place cursor at the beginning of the input
        input[0].setSelectionRange(0, 0);

        input.blur(function(){
            var newContent = $(this).val();
            var workDays_Id = $(this).parent().data('id');
            var columnName = $(this).parent().data('column');
            $(this).parent().text(newContent);
            $(this).parent().removeClass("cellEditing");
            
            // Make AJAX call to update the database here
            var updatedUrl = '';

            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/workdaysrepports/updateWorkdaysRepport';
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/workdaysrepports/updateWorkdaysRepport';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/workdaysrepports/updateWorkdaysRepport';
            @endif 

            $.ajax({
                url: updatedUrl, 
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
        });
    });
});
</script>
@endcan

<script>
    $(document).ready(function() {
        $('#sendEmailBtn').click(function() {
            var formData = new FormData($('#emailForm')[0]);
            
            $.ajax({
                url: '{{ route("admin.employees.send-email") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#contactModal').modal('hide');
                    alert('Email sent successfully!');
                },
                error: function(xhr) {
                    alert('Error sending email. Please try again.');
                }
            });
        });
    });
</script>
</body>

</html>