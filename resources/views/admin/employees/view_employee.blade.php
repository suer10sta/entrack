<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        {{-- FIX: Corrected the main layout structure. --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-1">Employee Profile</h1>
                <p class="text-sm text-gray-600">View and manage employee information</p>
            </div>

            <div class="multisteps-form mb-5">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white rounded-lg shadow-sm border-l-4 border-blue-500 p-4 card-hover">
                        <div class="flex items-center mb-4">
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
                                    <span class="font-semibold">{{ strtoupper(substr($employee->name, 0, 1)) }}</span>
                                </div>
                                @endif
                            </div>
                            <div>
                                <div class="flex items-center">
                                    <span class="text-blue-500 mr-2">👤</span>
                                    <h5 class="text-base font-semibold text-gray-900 mb-0">Employee Infos</h5>
                                </div>
                                <p class="text-xs text-gray-500 ml-6">Mandatory information</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Name</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="name"  type="text"
                                        name="name" value="{{ $employee->name }}" readonly/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Login Email</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="email"  type="text"
                                        name="email" value="{{ $employee->email }}" readonly/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Phone Number</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="phoneNumber"  type="text"
                                        name="phoneNumber" value="{{ $employee->phoneNumber }}" readonly/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Contact Email</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="contact_email"  type="text"
                                        name="contact_email" value="{{ $employee->contact_email }}" readonly/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Age</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="age"  type="text"
                                        name="age" value="{{ $employee->age }}" readonly/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Password</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="password"  type="text"
                                        name="password" value="{{ $employee->password }}" readonly/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Work Hours Limit</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="workhours_limit"  type="text"
                                        name="workHours_limit" value="{{ $employee->workHours_limit }}" readonly/>
                                </div>
                                <div>
                                    <button type="button" class="w-full mt-5 px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition-colors" 
                                        data-bs-toggle="modal" data-bs-target="#contactModal">
                                        Send Email
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border-l-4 border-yellow-500 p-4 card-hover">
                        <div class="mb-4">
                            <div class="flex items-center">
                                <span class="text-yellow-500 mr-2">💼</span>
                                <h5 class="text-base font-semibold text-gray-900 mb-0">Professional Infos</h5>
                            </div>
                            <p class="text-xs text-gray-500 ml-6">Mandatory information</p>
                        </div>
                        <div class="space-y-3">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">CNSS Number</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="cnss"  type="text"
                                        name="cnss" value="{{ $employee->cnss }}" readonly/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Bank Account Number</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="bankAccountNumber"  type="text"
                                        name="bankAccountNumber" value="{{ $employee->bankAccountNumber }}" readonly/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Release Date</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="Date_Release"  type="text"
                                        name="Date_Release" value="{{ $employee->Date_Release }}" readonly/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Salary</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="salary"  type="text"
                                        name="salary" value="{{ $employee->salary }}€" readonly/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Cost Per Hour</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="cost_per_hour"  type="text"
                                        name="cost_per_hour" value="{{ $employee->cost_per_hour }}€" readonly/>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Cotisation CNSS</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="Cotisation_CNSS"  type="text"
                                        name="Cotisation_CNSS" value="{{ $employee->Cotisation_CNSS }}€" readonly/>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 mb-1">Cotisation AMO</label>
                                    <input class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent editable"   
                                        data-id="{{$employee->id}}" data-column="Cotisation_AMO"  type="text"
                                        name="Cotisation_AMO" value="{{ $employee->Cotisation_AMO }}€" readonly/>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm border-l-4 border-green-500 p-4 card-hover">
                        <div class="mb-4">
                            <div class="flex items-center">
                                <span class="text-green-500 mr-2">🔐</span>
                                <h5 class="text-base font-semibold text-gray-900 mb-0">Roles</h5>
                            </div>
                            <p class="text-xs text-gray-500 ml-6">Roles information</p>
                        </div>
                        <div class="space-y-2">
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
                        <div class="mt-4">
                            @if(Auth::user()->hasRole('Admin'))
                            @can('Edit employee roles')
                            <a href="{{route('admin.employees.edit', $employee->id)}}" class="inline-block px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition-colors">Edit Roles</a>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            @can('Edit employee roles')
                            <a href="{{route('Responsable_de_projet.employees.edit', $employee->id)}}" class="inline-block px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition-colors">Edit Roles</a>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            @can('Edit employee roles')
                            <a href="{{route('employee.employees.edit', $employee->id)}}" class="inline-block px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition-colors">Edit Roles</a>
                            @endcan
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            @can('Edit employee roles')
                            <a href="{{route('Responsable_gestionnaire.employees.edit', $employee->id)}}" class="inline-block px-4 py-2 bg-blue-500 text-white text-sm font-medium rounded-md hover:bg-blue-600 transition-colors">Edit Roles</a>
                            @endcan
                            @endif
                        </div>
                    </div>
                </div>

                {{-- FIX: Applied target container style --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-4">
                    {{-- FIX: Added target header style --}}
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <span class="text-purple-500 mr-2">📊</span>
                            <h5 class="text-base font-semibold text-gray-900 mb-0">Work Days Reports</h5>
                        </div>
                    </div>
                    {{-- FIX: Added target padding wrapper --}}
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            {{-- FIX: Updated table, thead, and tbody classes --}}
                            <table id="editableTable" class="w-full text-left border-collapse min-w-full">
                                <thead class="border-b border-gray-200 bg-gray-50">
                                    <tr>
                                        {{-- FIX: Updated th classes --}}
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Nombre jours <br> travaillés interne
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Nombre jours <br> maladie
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Nombre jours <br> fériés
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Reilquat jours <br> congé <br> payé
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Nombre jours <br> congé payé <br> acquis
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Nombre jours <br> congé payé <br> pris
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Solde <br> congés
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Jours <br> d'absence
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Dates des jours <br> congé payé
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Remarque
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Mois
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                            Annee
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @if($work_days_repports)
                                    <tr class="hover:bg-gray-50">
                                        {{-- FIX: Updated td classes --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="days_worked">
                                            @php
                                                $totalMinutes = $totalHours * 60;
                                                $hours = floor($totalMinutes / 60);
                                                $minutes = $totalMinutes % 60;
                                            @endphp
                                            {{ str_pad($hours, 2, '0', STR_PAD_LEFT) }}h:{{ str_pad($minutes, 2, '0', STR_PAD_LEFT) }}m - <b>{{$work_days_repports->days_worked}} jrs</b>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="sick_days">
                                            {{$work_days_repports->sick_days}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="holidays">
                                            {{$work_days_repports->holidays}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="remaining_days_of_paid_leave">
                                            {{$work_days_repports->remaining_days_of_paid_leave}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_acquired">
                                            {{$work_days_repports->number_of_days_of_paid_leave_acquired}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="number_of_days_of_paid_leave_taken">
                                            {{$work_days_repports->number_of_days_of_paid_leave_taken}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="holiday_balance">
                                            {{$work_days_repports->holiday_balance}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="days_of_unpaid_absence">
                                            {{$work_days_repports->days_of_unpaid_absence}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="dates_of_holidays">
                                            {{$work_days_repports->dates_of_holidays}}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="remarque">
                                            {{$work_days_repports->remarque}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="month">
                                            {{$work_days_repports->month}}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$work_days_repports->id}}" data-column="year">
                                            {{$work_days_repports->year}}
                                        </td>
                                    </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- FIX: Applied target container style --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-4">
                    {{-- FIX: Added target header style --}}
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <span class="text-pink-500 mr-2">👥</span>
                            <h5 class="text-base font-semibold text-gray-900 mb-0">Teams</h5>
                        </div>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            @php
                                $team_id = App\Models\team_members::where('employee_id', $employee->id)->pluck('team_id')->unique();
                                $teams = App\Models\Teams::whereIn('id', $team_id)->get();
                            @endphp
                            @foreach($teams as $team)
                            {{-- This is a sub-card, it should keep its own style --}}
                            <div class="bg-white rounded-lg shadow-sm p-4 card-hover border border-gray-200">
                                <div class="mb-3">
                                    <h5 class="text-base font-semibold text-gray-900 capitalize">{{$team->name}}</h5>
                                    <span class="text-xs font-medium text-gray-600">Team Members:</span>
                                </div>
                                <ul class="space-y-2">
                                    @foreach(App\Models\team_members::where('team_id', $team->id)->get() as $team_member)
                                    <li class="flex items-center justify-between py-2 border-b border-gray-100 last:border-0">
                                        <div class="flex items-center flex-1">
                                            @php
                                                $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6',
                                                '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                                $employee = App\Models\Employees::where('id', $team_member->employee_id)->first();
                                            @endphp
                                            @if(App\Models\Employees::where('id', $team_member->employee_id)->first()->image)
                                            <img class="w-8 h-8 rounded-full mr-2" 
                                                src="{{ Storage::disk('do_spaces')->url('employee_images/' . $employee->image) }}" 
                                                alt="employee Image">                                    
                                            @else
                                            <div class="w-8 h-8 rounded-full mr-2 flex items-center justify-center text-white text-sm"
                                                style="background-color: {{ $colors[$loop->index % count($colors)] }};">
                                                <span>{{ strtoupper(substr($team_member->employee_name, 0, 1)) }}</span>
                                            </div>
                                            @endif
                                            <div>
                                                <h6 class="text-sm font-medium text-gray-900">
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @if(Auth::user()->can('View employee'))
                                                    <a href="{{route('admin.employees.show', $team_member->employee_id)}}" class="hover:text-blue-500">{{$team_member->employee_name}}</a>
                                                    @else
                                                    {{$team_member->employee_name}}
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @if(Auth::user()->can('View employee'))
                                                    <a href="{{route('employee.employees.show', $team_member->employee_id)}}" class="hover:text-blue-500">{{$team_member->employee_name}}</a>
                                                    @else
                                                    {{$team_member->employee_name}}
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                                    @if(Auth::user()->can('View employee'))
                                                    <a href="{{route('Responsable_gestionnaire.employees.show', $team_member->employee_id)}}" class="hover:text-blue-500">{{$team_member->employee_name}}</a>
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
                                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-800">Off Work</span>
                                                @elseif($employee_user->is_loggedIn == 1)
                                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-green-100 text-green-800">On Work</span>
                                                @else
                                                    <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-yellow-100 text-yellow-800">On Pause</span>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                        @if(Auth::user()->hasRole('Admin'))
                                            @can('View employee')
                                                <a href="{{route('admin.employees.show', $team_member->employee_id)}}"
                                                    class="px-3 py-1 text-xs font-medium text-blue-600 border border-blue-600 rounded hover:bg-blue-50">
                                                    view
                                                </a>
                                            @endcan
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet'))
                                            @can('View employee')
                                                <a href="{{route('Responsable_de_projet.employees.show', $team_member->employee_id)}}"
                                                    class="px-3 py-1 text-xs font-medium text-blue-600 border border-blue-600 rounded hover:bg-blue-50">
                                                    view
                                                </a>
                                            @endcan
                                        @endif
                                        @if(Auth::user()->hasRole('Employee'))
                                            @can('View employee')
                                                <a href="{{route('employee.employees.show', $team_member->employee_id)}}"
                                                    class="px-3 py-1 text-xs font-medium text-blue-600 border border-blue-600 rounded hover:bg-blue-50">
                                                    view
                                                </a>
                                            @endcan
                                        @endif
                                        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                            @can('View employee')
                                                <a href="{{route('Responsable_gestionnaire.employees.show', $team_member->employee_id)}}"
                                                    class="px-3 py-1 text-xs font-medium text-blue-600 border border-blue-600 rounded hover:bg-blue-50">
                                                    view
                                                </a>
                                            @endcan
                                        @endif
                                        </div>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- FIX: Applied target container style --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-4">
                    {{-- FIX: Added target header style --}}
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <span class="text-teal-500 mr-2">📁</span>
                            <h5 class="text-base font-semibold text-gray-900 mb-0">Projects</h5>
                        </div>
                    </div>

                    @php
                        $team_project_id = App\Models\project_teams::whereIn('team_id', $team_id)->pluck('project_id');
                        $projects_tasks_id = App\Models\employees_tasks::where('employee_id', $employee->id)->pluck('task_id');
                        $projects_tasks = App\Models\Tasks::whereIn('id', $projects_tasks_id)->pluck('project_id');
                        $projects1 = App\Models\Project::whereIn('id', $projects_tasks)->get();
                        $projects = App\Models\Project::whereIn('id', $team_project_id)->get();
                        $combined_project_ids = $projects1->concat($projects)->unique();
                        
                        $projectsByClient = $combined_project_ids->groupBy('client_id');
                    @endphp

                    {{-- FIX: Added target padding wrapper --}}
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            {{-- FIX: Updated table, thead, and tbody classes --}}
                            <table class="w-full text-left border-collapse min-w-full">
                                <thead class="border-b border-gray-200 bg-gray-50">
                                    <tr>
                                        {{-- FIX: Updated th classes --}}
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Client / Project</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Budget</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">CPH</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estimated Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Difference</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Progress</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">TVA</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Teams</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($projectsByClient as $client_id => $clientProjects)
                                        @php
                                            $client = App\Models\Client::find($client_id);
                                        @endphp
                                        
                                        <tr class="bg-gray-100">
                                            {{-- FIX: Updated padding and text color --}}
                                            <td colspan="9" class="px-6 py-3">
                                                <h6 class="text-sm font-semibold text-gray-900"> {{-- Kept 900 for header --}}
                                                    @if(Auth::user()->hasRole('Admin'))
                                                        @if(Auth::user()->can('View client'))
                                                            <a href="{{route('admin.clients.show', $client_id)}}" class="hover:text-blue-500">{{$client->name}}</a>
                                                        @else
                                                            {{$client->name}}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                        @if(Auth::user()->can('View client'))
                                                            <a href="{{route('Responsable_de_projet.clients.show', $client_id)}}" class="hover:text-blue-500">{{$client->name}}</a>
                                                        @else
                                                            {{$client->name}}
                                                        @endif
                                                    @elseif(Auth::user()->hasRole('Employee'))
                                                        @can('View client')
                                                            <a href="{{route('employee.clients.show', $client_id)}}" class="hover:text-blue-500">{{$client->name}}</a>
                                                        @else
                                                            {{$client->name}}
                                                        @endcan
                                                    @endif
                                                </h6>
                                            </td>
                                        </tr>

                                        @foreach($clientProjects as $project)
                                        <tr class="hover:bg-gray-50">
                                            {{-- FIX: Updated td classes --}}
                                            <td class="px-6 py-4">
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                    @if(Auth::user()->can('View project'))
                                                        <a href="{{route('Responsable_de_projet.projects.show',$project->id)}}" class="text-sm text-blue-600 hover:text-blue-800">
                                                            {{$project->name}}
                                                        </a>
                                                    @else   
                                                        <span class="text-sm text-gray-700">{{$project->name}}</span>
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Admin'))
                                                    @if(Auth::user()->can('View project'))
                                                        <a href="{{route('admin.projects.show',$project->id)}}" class="text-sm text-blue-600 hover:text-blue-800">
                                                            {{$project->name}}
                                                        </a>
                                                    @else   
                                                        <span class="text-sm text-gray-700">{{$project->name}}</span>
                                                    @endif
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                    @can('View project')
                                                        <a href="{{route('employee.projects.show',$project->id)}}" class="text-sm text-blue-600 hover:text-blue-800">
                                                            {{$project->name}}
                                                        </a>
                                                    @else   
                                                        <span class="text-sm text-gray-700">{{$project->name}}</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700">€{{$project->budget}}</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">€{{$project->cost_per_hour}}</td>
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
                                            <td class="px-6 py-4 text-sm text-gray-700">{{$hours}}h:{{$minutes}}m</td>
                                            <td class="px-6 py-4 text-sm text-gray-700 editable" data-id="{{$project->id}}" data-column="estimated_hours">
                                                {{$project->estimated_hours}}h
                                            </td>
                                            <td class="px-6 py-4 text-sm font-medium">
                                                @if($project->estimated_hours !== null)
                                                    @if($difference > 0)
                                                        <span class="text-green-600">+{{$diffhours}}h:{{$diffminutes}}m</span>
                                                    @elseif($difference < 0)
                                                        <span class="text-red-600">-{{$diffhours}}h:{{$diffminutes}}m</span>
                                                    @else
                                                        <span class="text-blue-600">{{$diffhours}}h:{{$diffminutes}}m</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                                    <div class="h-3 text-xs leading-none text-center text-white flex items-center justify-center
                                                        @if($progressPercentage < 50) bg-green-500 
                                                        @elseif($progressPercentage < 75) bg-yellow-500 
                                                        @else bg-red-500 
                                                        @endif" 
                                                        style="width: {{min($formattedProgressPercentage, 100)}}%">
                                                        {{$formattedProgressPercentage}}%
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-700">%{{$project->tva}}</td>
                                            <td class="px-6 py-4 text-sm text-gray-700">
                                                @forEach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                                    {{$project_team->team_name}}<br>
                                                @endforEach
                                            </td>
                                        </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- FIX: Applied target container style --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mt-4">
                    {{-- FIX: Added target header style --}}
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex items-center">
                            <span class="text-orange-500 mr-2">✓</span>
                            <h5 class="text-base font-semibold text-gray-900 mb-0">Tasks</h5>
                        </div>
                    </div>

                    @php
                        $tasks_collection = collect([]);
                        $employee_tasks_ids = App\Models\employees_tasks::where('employee_id', $employee->id)->get();
                        foreach($employee_tasks_ids as $employee_task_id){
                            $task = App\Models\Tasks::where('id', $employee_task_id->task_id)->first();
                            $tasks_collection->push($task);
                        }
                        
                        $tasksByProject = $tasks_collection->groupBy('project_id');
                    @endphp
                    
                    {{-- FIX: Added target padding wrapper --}}
                    <div class="p-6">
                        <div class="overflow-x-auto">
                            {{-- FIX: Updated table, thead, and tbody classes --}}
                        <table class="w-full text-left border-collapse min-w-full">
                                <thead class="border-b border-gray-200 bg-gray-50">
                                    <tr>
                                        {{-- FIX: Updated th classes --}}
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Project / Task</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Duration</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Estimated Hours</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Difference</th>
                                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach($tasksByProject as $project_id => $projectTasks)
                                    @php
                                    $project = App\Models\Project::find($project_id);
                                    @endphp
                                    <tr class="bg-gray-100">
                                        <td colspan="5" class="px-6 py-3">
                                            <h6 class="text-sm font-semibold text-gray-900">
                                                @if(Auth::user()->hasRole('Admin'))
                                                @if(Auth::user()->can('View project'))
                                                <a href="{{route('admin.projects.show', $project_id)}}" class="hover:text-blue-500">{{$project->name}}</a>
                                                @else
                                                {{$project->name}}
                                                @endif
                                                @elseif(Auth::user()->hasRole('Responsable de projet'))
                                                @if(Auth::user()->can('View project'))
                                                <a href="{{route('Responsable_de_projet.projects.show', $project_id)}}" class="hover:text-blue-500">{{$project->name}}</a>
                                                @else
                                                {{$project->name}}
                                                @endif
                                                @elseif(Auth::user()->hasRole('Employee'))
                                                @can('View project')
                                                <a href="{{route('employee.projects.show', $project_id)}}" class="hover:text-blue-500">{{$project->name}}</a>
                                                @else
                                                {{$project->name}}
                                                @endcan
                                                @endif
                                            </h6>
                                        </td>
                                    </tr>
                                    @foreach($projectTasks as $task)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 text-sm text-blue-600 hover:text-blue-800">
                                            @if(Auth::user()->hasRole('Admin'))
                                            <a href="{{ route('admin.tasks.show', $task->id) }}">{{$task->title}}</a>
                                            @elseif(Auth::user()->hasRole('Responsable de projet'))
                                            <a href="{{ route('Responsable_de_projet.tasks.show', $task->id) }}">{{$task->title}}</a>
                                            @elseif(Auth::user()->hasRole('Employee'))
                                            <a href="{{ route('employee.tasks.show', $task->id) }}">{{$task->title}}</a>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @php 
                                            $tasktotalduration = App\Models\tasks_timer::where('task_id', $task->id)->where('employee_id', $employee->id)->sum('duration');
                                            $taskhours = floor($tasktotalduration / 60);
                                            $taskminutes = $tasktotalduration % 60;
                                            @endphp
                                            @if(Auth::user()->hasRole('Admin'))
                                            <a href="{{ route('admin.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id]) }}" class="text-sm text-blue-600 hover:text-blue-800">
                                                @endif
                                                @if(Auth::user()->hasRole('Responsable de projet'))
                                                <a href="{{route('Responsable_de_projet.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id])}}" class="text-sm text-blue-600 hover:text-blue-800">
                                                @endif
                                                @if(Auth::user()->hasRole('Employee'))
                                                <a href="{{route('employee.tasks.durationInfos', ['employee' => $employee->id, 'task' => $task->id])}}" class="text-sm text-blue-600 hover:text-blue-800">
                                                @endif
                                                {{$taskhours}}h:{{$taskminutes}}m
                                            </a>
                                        </td>
                                            <td class="px-6 py-4 text-sm text-gray-700">{{$task->estimated_hours}}h</td>
                                            <td class="px-6 py-4 text-sm font-medium">
                            
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
                                            <span class="text-green-600">+{{$hours}}h:{{$minutes}}m</span>
                                            @elseif($taskdifference < 0)
                                        
                                            <span class="text-red-600">-{{$hours}}h:{{$minutes}}m</span>
                                        
                                            @else
                                        
                                            <span class="text-blue-600">{{$hours}}h:{{$minutes}}m</span>
                                        
                                            @endif
                                        
                                            @endif
                                    
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                                                <div class="h-3 text-xs leading-none text-center text-white flex items-center justify-center
                                                @if($taskprogressPercentage < 50) bg-green-500 
                                                @elseif($taskprogressPercentage < 75) bg-yellow-500 
                                                @else bg-red-500 
                                                @endif" 
                                                style="width: {{min($taskformattedProgressPercentage, 100)}}%">
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
        </main>
    </div>
    @livewireScripts
</body>
</html>