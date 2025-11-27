<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyTrac Dashboard</title>
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
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <!-- CHANGED: main padding from p-6 to p-8 -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Employee Profile</h1>
                <p class="text-sm text-gray-600">View and manage employee information</p>
            </div>

            <!-- This div stacks all the new cards vertically -->
            <div class="space-y-8">

                <!-- Info Cards Grid -->
                <!-- CHANGED: Added mb-8 for spacing below the grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    
                    <!-- Card 1: Employee Infos -->
                    <!-- CHANGED: Applied new card style -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center">
                                <div>
                                    @php
                                        $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', 
                                        '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F']; 
                                    @endphp 
                                    @if($employee->image)
                                        <img class="w-12 h-12 rounded-full mr-3"  src="{{ Storage::disk('do_spaces')->url('employee_images/' . $employee->image) }}" alt="employee Image">
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
                                        <h5 class="text-lg font-semibold text-gray-900 mb-0">Employee Infos</h5>
                                    </div>
                                    <p class="text-sm text-gray-500 ml-8">Mandatory information</p>
                                </div>
                            </div>
                        </div>
                        <!-- Card Body -->
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Name</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="name"  type="text"
                                            name="name" value="{{ $employee->name }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Login Email</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="email"  type="text"
                                            name="email" value="{{ $employee->email }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Phone Number</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="phoneNumber"  type="text"
                                            name="phoneNumber" value="{{ $employee->phoneNumber }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Contact Email</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="contact_email"  type="text"
                                            name="contact_email" value="{{ $employee->contact_email }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Age</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="age"  type="text"
                                            name="age" value="{{ $employee->age }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Password</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="password"  type="text"
                                            name="password" value="{{ $employee->password }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-700 mb-1">Work Hours Limit</label>
                                        <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"    
                                            data-id="{{$employee->id}}" data-column="workhours_limit"  type="text"
                                            name="workHours_limit" value="{{ $employee->workHours_limit }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">&nbsp;</label>
                                        <button type="button" id="openEmailModalBtn" class="w-full px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                            Send Email
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Professional Infos -->
                    <!-- CHANGED: Applied new card style -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">💼</span>
                                <div>
                                    <h5 class="text-lg font-semibold text-gray-900 mb-0">Professional Infos</h5>
                                    <p class="text-sm text-gray-500">Mandatory information</p>
                                </div>
                            </div>
                        </div>
                        <!-- Card Body -->
                        <div class="p-6">
                            <div class="space-y-4">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">CNSS Number</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="cnss" type="text"
                                            name="cnss" value="{{ $employee->cnss }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Bank Account</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="bankAccountNumber" type="text"
                                            name="bankAccountNumber" value="{{ $employee->bankAccountNumber }}" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Release Date</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="Date_Release" type="text"
                                            name="Date_Release" value="{{ $employee->Date_Release }}" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Salary</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="salary" type="text"
                                            name="salary" value="{{ $employee->salary }}€" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Cost Per Hour</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="cost_per_hour" type="text"
                                            name="cost_per_hour" value="{{ $employee->cost_per_hour }}€" readonly/>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Cotisation CNSS</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="Cotisation_CNSS" 
                                            type="text" name="Cotisation_CNSS"
                                            value="{{ $employee->Cotisation_CNSS }}€" readonly/>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Cotisation AMO</label>
                                        <input class="editable w-full border-gray-300 rounded-lg shadow-sm" 
                                            data-id="{{$employee->id}}" data-column="Cotisation_AMO"  
                                            type="text" name="Cotisation_AMO"
                                            value="{{ $employee->Cotisation_AMO }}€" readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Roles -->
                    <!-- CHANGED: Applied new card style -->
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        <!-- Card Header -->
                        <div class="p-6 border-b border-gray-200">
                            <div class="flex items-center">
                                <span class="text-xl mr-2">🔐</span>
                                <div>
                                    <h5 class="text-lg font-semibold text-gray-900 mb-0">Roles</h5>
                                    <p class="text-sm text-gray-500">Roles information</p>
                                </div>
                            </div>
                        </div>
                        <!-- Card Body -->
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
                                <a href="{{ $editRolesRoute }}" class="inline-block px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">Edit Roles</a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div> <!-- End of Info Cards Grid -->

                <!-- Card 4: Work Days Reports -->
                <!-- CHANGED: Applied new card style and added mb-8 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Work Days Reports</h2>
                    </div>
                    <!-- CHANGED: Added p-6 wrapper for table -->
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            <table id="editableTable" class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jours Travaillés Interne</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jours Maladie</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jours Fériés</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reilquat Congé Payé</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Congé Payé Acquis</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Congé Payé Pris</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Solde Congés</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jours d'Absence</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dates Congé Payé</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Remarque</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mois</th>
                                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Année</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @if($work_days_repports)
                                    <tr class="hover:bg-gray-50">
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium" data-id="{{$work_days_repports->id}}" data-column="days_worked">
                                            @php
                                                $totalMinutes = $totalHours * 60;
                                                $hours = floor($totalMinutes / 60);
                                                $minutes = $totalMinutes % 60;
                                            @endphp
                                            {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m - <b>{{$work_days_repports->days_worked}} jrs</b>
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="sick_days">
                                            {{$work_days_repports->sick_days}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="holidays">
                                            {{$work_days_repports->holidays}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave">
                                            {{$work_days_repports->remaining_days_of_paid_leave}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired">
                                            {{$work_days_repports->number_of_days_of_paid_leave_acquired}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken">
                                            {{$work_days_repports->number_of_days_of_paid_leave_taken}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="holiday_balance">
                                            {{$work_days_repports->holiday_balance}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence">
                                            {{$work_days_repports->days_of_unpaid_absence}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays">
                                            {{$work_days_repports->dates_of_holidays}}
                                        </td>
                                        <td class="editable px-6 py-4 text-sm text-gray-700 max-w-xs whitespace-normal" data-id="{{$work_days_repports->id}}" data-column="remarque">
                                            {{$work_days_repports->remarque}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="month">
                                            {{$work_days_repports->month}}
                                        </td>
                                        <td class="editable px-6 py-4 whitespace-nowrap text-sm text-gray-700" data-id="{{$work_days_repports->id}}" data-column="year">
                                            {{$work_days_repports->year}}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Teams -->
                <!-- CHANGED: Applied new card style and added mb-8 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Card Header -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <span class="text-xl mr-2">👥</span>
                            <h5 class="text-lg font-semibold text-gray-900 mb-0">Teams</h5>
                        </div>
                    </div>
                    <!-- Card Body -->
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
                                    <span class="text-sm font-medium text-gray-600">Team Members:</span>
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
                                                src="{{ Storage::disk('do_spaces')->url('employee_images/' . $member_employee->image) }}" 
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
                                            View
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


                <!-- Card 6: Projects -->
                <!-- CHANGED: Applied new card style and added mb-8 -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Projects</h2>
                    </div>
                    <!-- CHANGED: Added p-6 wrapper for table -->
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            @php
                                $team_project_id = App\Models\project_teams::whereIn('team_id', $team_id)->pluck('project_id');
                                $projects_tasks_id = App\Models\employees_tasks::where('employee_id', $employee->id)->pluck('task_id');
                                $projects_tasks = App\Models\Tasks::whereIn('id', $projects_tasks_id)->pluck('project_id');
                                $projects1 = App\Models\Project::whereIn('id', $projects_tasks)->get();
                                $projects = App\Models\Project::whereIn('id', $team_project_id)->get();
                                $combined_project_ids = $projects1->concat($projects)->unique();
                                $projectsByClient = $combined_project_ids->groupBy('client_id');
                            @endphp
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client / Project</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Budget</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($projectsByClient as $client_id => $clientProjects)
                                        @php $client = App\Models\Client::find($client_id); @endphp
                                        <tr class="bg-gray-100">
                                            <td colspan="4" class="px-6 py-3">
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
                                            </td>
                                        </tr>
                                        @foreach($clientProjects as $project)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">€{{$project->budget}}</td>
                                            @php 
                                                $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                                $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->where('employee_id', $employee->id)->sum('duration');
                                                $hours = floor(abs($totalduration) / 60);
                                                $minutes = abs($totalduration) % 60;
                                                $estimatedMinutes = $project->estimated_hours * 60;
                                                $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                                                $formattedProgressPercentage = number_format($progressPercentage, 2);
                                            @endphp
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{$hours}}h:{{$minutes}}m</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
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

                <!-- Card 7: Tasks -->
                <!-- CHANGED: Applied new card style. No mb-8 as it's the last item. -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Tasks</h2>
                    </div>
                    <!-- CHANGED: Added p-6 wrapper for table -->
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            @php
                                $tasks_collection = collect([]);
                                $employee_tasks_ids = App\Models\employees_tasks::where('employee_id', $employee->id)->get();
                                foreach($employee_tasks_ids as $employee_task_id){
                                    $task = App\Models\Tasks::where('id', $employee_task_id->task_id)->first();
                                    if ($task) { // Added check in case task is null
                                        $tasks_collection->push($task);
                                    }
                                }
                                $tasksByProject = $tasks_collection->groupBy('project_id');
                            @endphp
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Project / Task</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difference</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($tasksByProject as $project_id => $projectTasks)
                                        @php $project = App\Models\Project::find($project_id); @endphp
                                        @if($project) <!-- Added check in case project is null -->
                                        <tr class="bg-gray-100">
                                            <td colspan="5" class="px-6 py-3">
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
                                            </td>
                                        </tr>
                                        @foreach($projectTasks as $task)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{$task->estimated_hours}}h</td>
                                            @php
                                                $taskdifference = $task->estimated_hours * 60 - $tasktotalduration;
                                                $hours = floor(abs($taskdifference) / 60);
                                                $minutes = abs($taskdifference) % 60;
                                                $taskestimatedMinutes = $task->estimated_hours * 60;
                                                $taskprogressPercentage = $taskestimatedMinutes > 0 ? ($tasktotalduration / $taskestimatedMinutes) * 100 : 0;
                                                $taskformattedProgressPercentage = number_format($taskprogressPercentage, 2);
                                            @endphp
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
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
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
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
                                        </tr>
                                        @endforeach
                                        @endif <!-- End project check -->
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> <!-- End of space-y-8 wrapper -->

        </main>
    </div>

    <div id="contactModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300 hidden">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4 p-6 transform transition-all duration-300">
            
            <div class="flex justify-between items-center pb-3 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Send Email to {{ $employee->name }}</h3>
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
                            <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">Subject</label>
                            <input type="text" class="w-full border-gray-300 rounded-lg shadow-sm" id="subject" name="subject" required>
                        </div>
                        <div>
                            <label for="body" class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                            <textarea class="w-full border-gray-300 rounded-lg shadow-sm" id="body" name="body" rows="6" required>Bonjour, 
Veuillez trouver ci-joint le document, pour votre information.

N'hésitez pas à me contacter si vous avez la moindre question.

Bien cordialement,

Pour Stabel</textarea>
                        </div>
                        <div>
                            <label for="attachments" class="block text-sm font-medium text-gray-700 mb-1">Attachments</label>
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
                    Close
                </button>
                <button type="button" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm" id="sendEmailBtn">
                    Send Email
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
                        alert('Email sent successfully!');
                    },
                    error: function(xhr) {
                        // Use a custom modal for error instead of alert
                        alert('Error sending email. Please try again.');
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
        });
    </script>
    
    @livewireScripts
</body>
</html>
