{{-- We need to load the correct Tailwind-aware CSS and JS for DataTables --}}
<style>
    @keyframes fadeInSlideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    .animate-fadeInSlideUp {
        opacity: 0;
        animation: fadeInSlideUp 0.5s ease-out forwards;
    }

    /* Override default DataTables styles to match Tailwind */
    #employeesTable_wrapper .dt-search input {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.5rem 0.75rem;
        font-size: 0.875rem;
        line-height: 1.25rem;
        margin-left: 0.5rem;
    }
    #employeesTable_wrapper .dt-search input:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2px #bfdbfe;
        outline: none;
    }
    
    /* FIXED: Better DataTables pagination styling */
    #employeesTable_wrapper .dt-paging {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    #employeesTable_wrapper .dt-paging a,
    #employeesTable_wrapper .dt-paging button {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        transition: all 0.2s;
    }
    
    #employeesTable_wrapper .dt-paging a:hover,
    #employeesTable_wrapper .dt-paging button:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
    }
    
    #employeesTable_wrapper .dt-paging a.current,
    #employeesTable_wrapper .dt-paging button.current {
        background-color: #1E9FD8;
        color: #ffffff;
        border-color: #1E9FD8;
        font-weight: 600;
    }
    
    #employeesTable_wrapper .dt-paging a.disabled,
    #employeesTable_wrapper .dt-paging button.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
    }
    
    #employeesTable_wrapper .dt-info {
        font-size: 0.875rem;
        color: #6b7280;
    }

    /* FIXED: Laravel pagination styling - force horizontal layout */
    .pagination {
        display: flex !important;
        flex-direction: row !important;
        align-items: center;
        gap: 0.25rem;
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .pagination li {
        display: inline-flex !important;
        margin: 0 !important;
    }
    
    .pagination a,
    .pagination span {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        min-width: 2rem;
        height: 2rem;
        padding: 0.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        color: #374151;
        background-color: #ffffff;
        border: 1px solid #e5e7eb;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .pagination a:hover {
        background-color: #f3f4f6;
        border-color: #d1d5db;
    }
    
    .pagination .active span {
        background-color: #1E9FD8;
        color: #ffffff;
        border-color: #1E9FD8;
        font-weight: 600;
    }
    
    .pagination .disabled span {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    /* SVG icons in pagination */
    .pagination svg {
        width: 1.25rem;
        height: 1.25rem;
    }
</style>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.tailwindcss.min.css">
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>

<div class="flex-1 overflow-auto bg-gray-50">
    {{-- Main content --}}
    <div class="p-8">

    {{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

    {{-- Clients --}}
    <a href="{{ route('admin.clients.index') }}" 
       class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" 
       style="animation-delay: 100ms;">
        <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V10H2v10h5m10 0V8a5 5 0 00-10 0v12" />
                        </svg>
                    </div>
                    <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Clients') }}</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $data['clients']->count() }}</p>
            </div>
            <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+12%</span>
        </div>
        <div class="relative">
            <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                <div class="w-2/3 h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
            </div>
        </div>
    </a>

    {{-- Employees --}}
    <a href="{{ route('admin.employees.index') }}" 
       class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" 
       style="animation-delay: 200ms;">
        <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-8 0v2m8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Employees') }}</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $data['employees']->count()}}</p>
            </div>
            <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+5%</span>
        </div>
        <div class="relative">
            <div class="h-2 bg-green-100 rounded-full overflow-hidden">
                <div class="w-1/2 h-2 bg-gradient-to-r from-green-500 to-green-600 rounded-full animate-pulse"></div>
            </div>
        </div>
    </a>

    {{-- Projects --}}
    <a href="{{ route('admin.projects.index') }}" 
       class="block bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" 
       style="animation-delay: 200ms;">
       <div class="flex items-center justify-between mb-4">
            <div class="flex-1">
                <div class="flex items-center space-x-2 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                        <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0H4m16 0l-2 9H6l-2-9" />
                        </svg>
                    </div>
                    <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Ongoing Projects') }}</h3>
                </div>
                <p class="text-3xl font-bold text-gray-800">{{ $data['projects']->count()}}</p>
                <p class="text-sm text-gray-500 mt-1">{{ $data['projects']->count()}} active projects</p>
            </div>
        </div>
        <div class="relative">
            <div class="h-2 bg-orange-100 rounded-full overflow-hidden">
                <div class="w-3/4 h-2 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full animate-pulse"></div>
            </div>
        </div>
    </a>

    {{-- Revenue --}}
    <a href="{{ route('admin.invoices.invoicehistory') }}" 
       class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group relative overflow-hidden animate-fadeInSlideUp" 
       style="animation-delay: 200ms;">
        <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
        <div class="relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.38 0 2.63.56 3.54 1.46M15.54 9.46A5 5 0 1112 8z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-500 text-sm font-medium">{{ __('messages.Monthly Revenue') }}</h3>
                    </div>
                    <p class="text-3xl font-bold text-gray-800">€{{ number_format($data['monthlyRevenue'], 2, ',', ' ') }}</p>
                </div>
                <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+18%</span>
            </div>
            <div class="flex justify-center mt-4">
                <div class="relative w-14 h-14">
                    <svg class="w-14 h-14 transform -rotate-90">
                        <circle cx="28" cy="28" r="24" stroke="#e5e7eb" stroke-width="4" fill="none" />
                        <circle cx="28" cy="28" r="24" stroke="#10b981" stroke-width="4" fill="none" 
                                stroke-dasharray="150.8" stroke-dashoffset="37.7" 
                                class="transition-all duration-1000 ease-out" />
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-xs font-bold text-green-600">75%</span>
                    </div>
                </div>
            </div>
        </div>
    </a>
</div>


{{-- Team employees Overview --}}
<div class="mt-8 border-0 shadow-sm bg-white rounded-lg overflow-hidden hover:shadow-xl animate-fadeInSlideUp" style="animation-delay: 500ms;">
    {{-- Header and action button --}}
    <div class="p-6 flex items-center justify-between border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">{{ __('messages.Team employees Overview') }}</h2>
        <a href="{{ route('admin.employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
            + {{__('messages.Add employee')}}
        </a>
    </div>
    
    {{-- Table --}}
    <div class="p-6 overflow-x-auto">
        <table id="employeesTable" class="w-full text-left" style="width:100%">
            <thead class="bg-gray-50">
                <tr class="border-b border-gray-200">
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.EMPLOYEE INFO')}}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.ROLE & STATUS')}}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.TEAMS & PROJECTS')}}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.CURRENT TASK')}}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.PROGRESS')}}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.MONTHLY STATS')}}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.UTILIZATION')}}</th>
                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">{{__('messages.ACTIONS')}}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            @foreach($data['employees'] as $employee)
                <tr class="hover:bg-gray-50">
                    {{-- EMPLOYEE INFO --}}
                    <td class="px-6 py-4 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $employee['color'] }}">
                            {{ $employee['initials'] }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $employee['name'] }}</p>
                            <p class="text-xs text-gray-500">{{ $employee['email'] }}</p>
                            <p class="text-xs text-gray-500">{{ $employee['phone'] }}</p>
                            <p class="text-xs font-medium text-emerald-600">{{ $employee['salary'] }}</p>
                        </div>
                    </td>
                    
                    {{-- ROLE & STATUS --}}
                    <td class="px-6 py-4 space-y-2 align-top">
                        <div class="flex flex-wrap gap-2">
                            @foreach($employee['roles'] as $role)
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $role=='SuperAdmin'?'bg-fuchsia-100 text-fuchsia-700':'bg-purple-100 text-purple-700'}}">{{ $role }}</span>
                            @endforeach
                        </div>
                        @php
                            $user = \App\Models\User::find($employee->user_id);
                            $statusRoute = '#';
                            if ($user && $user->is_activate) {
                                $statusText = 'Active';
                                $buttonClasses = 'bg-green-100 text-green-800 hover:bg-green-200';
                                $statusConfirmText = 'Deactivate this employee?';
                                if (Auth::user()->hasRole('Admin')) $statusRoute = route('admin.employee.deactivate', $employee->id);
                                elseif (Auth::user()->hasRole('Responsable de projet')) $statusRoute = route('Responsable_de_projet.employee.deactivate', $employee->id);
                            } else {
                                $statusText = 'Inactive';
                                $buttonClasses = 'bg-red-100 text-red-800 hover:bg-red-200';
                                $statusConfirmText = 'Activate this employee?';
                                if (Auth::user()->hasRole('Admin')) $statusRoute = route('admin.employee.activate', $employee->id);
                                elseif (Auth::user()->hasRole('Responsable de projet')) $statusRoute = route('Responsable_de_projet.employee.activate', $employee->id);
                                elseif (Auth::user()->hasRole('Employee')) $statusRoute = route('employee.employee.activate', $employee->id);
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $statusRoute = route('Responsable_gestionnaire.employee.activate', $employee->id);
                            }
                        @endphp
                        <form action="{{ $statusRoute }}" method="post" onsubmit="return confirm('{{ $statusConfirmText }}');" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $buttonClasses }} border-none cursor-pointer transition-colors">
                                {{ $statusText }}
                            </button>
                        </form>
                        <p class="text-xs text-gray-600 pt-1">{{ $employee['type'] }}</p>
                    </td>

                    {{-- TEAMS & PROJECTS --}}
                    <td class="px-6 py-4 align-top">
                        @forelse($employee->teams as $team)
                        <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded mb-1">
                            {{ $team->name }}
                        </span>
                        @empty
                        <span class="text-xs text-gray-400">{{__('messages.No team')}}</span>
                        @endforelse
                    </td>

                    {{-- CURRENT TASK --}}
                    <td class="px-6 py-4 align-top">
                        <span class="inline-block px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded">
                            {{ $employee['currentTask'] }}
                        </span>
                    </td>

                    {{-- PROGRESS --}}
                    <td class="px-6 py-4 align-top">-</td>

                    {{-- MONTHLY STATS --}}
                    <td class="px-6 py-4 space-y-1 text-xs text-gray-600 align-top">
                        <p>Hours: <span class="font-medium text-gray-800">{{ $employee['hours'] }}</span></p>
                        <p>Tasks: <span class="font-medium text-gray-800">{{ $employee['tasks'] }}</span></p>
                        <p>Days: <span class="font-medium text-gray-800">{{ $employee['days'] }}</span></p>
                    </td>

                    {{-- UTILIZATION --}}
                    <td class="px-6 py-4 align-top">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex-grow bg-gray-200 rounded-full h-1.5">
                                <div class="bg-gray-400 h-1.5 rounded-full" style="width: {{ $employee['progress'] }}"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-700">{{ $employee['progress'] }}</span>
                        </div>
                        <p class="text-xs text-gray-600">Limit: {{ $employee['limit'] }}</p>
                    </td>

                    {{-- ACTIONS --}}
                    <td class="px-6 py-4 whitespace-nowrap align-top">
                        <div class="flex items-center justify-start gap-2">
                            <a href="{{ route('admin.employees.show', $employee->id) }}" class="p-1 rounded-md bg-purple-100 text-purple-700 hover:bg-purple-200" title="View Details">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                            </a>
                            <a href="{{ route('admin.employees.edit', $employee->id) }}" class="p-1 rounded-md bg-blue-100 text-blue-700 hover:bg-blue-200" title="Edit Employee">
                                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                </svg>
                            </a>
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this employee?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded-md bg-red-100 text-red-700 hover:bg-red-200" title="Delete Employee">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
        </table>
    </div>
</div>
    
        {{-- Project Overview + Events --}}
        <div class="mt-8 grid grid-cols-1 lg:grid-cols-4 gap-8 ">
        
            {{-- Project Overview (col-span-3) --}}
            <div class="lg:col-span-3 border-0 shadow-sm bg-white rounded-lg hover:shadow-xl animate-fadeInSlideUp" style="animation-delay: 600ms;">
                <h2 class="p-6 text-lg font-semibold text-gray-900 border-b border-gray-200">{{__('messages.Project Overview')}}</h2>
                {{-- Column headers --}}
                <div class="px-6 py-3 grid grid-cols-12 gap-4 border-b border-gray-200">
                <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.PROJECT INFO')}}</span>
                <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.BUDGET & COSTS')}}</span>
                <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.TIME TRACKING')}}</span>
                <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.PROGRESS')}}</span>
                <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">{{__('messages.CLIENT & TEAMS')}}</span>
                <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">{{__('messages.ACTIONS')}}</span>
            </div>
        
            {{-- Project list --}}
            <div class="divide-y divide-gray-200">
                @foreach($data['projects'] as $project)
                <div class="px-6 py-4 grid grid-cols-12 gap-4 items-center">
                    
                    {{-- PROJECT INFO (col-span-3) --}}
                    <div class="col-span-3 flex items-center gap-3">
                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full text-white font-semibold {{ $project['color'] }}">
                            {{ strtoupper(substr($project->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $project->name}}</p>
                            <p class="text-xs text-gray-500">Est: {{ $project->estimated_hours ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- BUDGET & COSTS (col-span-2) --}}
                    <div class="col-span-2 text-sm space-y-1">
                        <p class="text-gray-600"><span class="text-gray-500">{{__('messages.Budget')}}:</span> <span class="font-medium text-gray-800">€{{ number_format($project->budget ?? 0, 2, ',', ' ') }}</span></p>
                        <p class="text-gray-600"><span class="text-gray-500">{{__('messages.CPH')}}:</span> <span class="font-medium text-gray-800">€{{ number_format($project->cost_per_hour ?? 0, 2, ',', ' ') }}</span></p>
                        <p class="text-gray-600"><span class="text-gray-500">{{__('messages.VAT')}}:</span> <span class="font-medium text-gray-800">{{ $project->tva ?? 20 }}</span></p>
                    </div>

                    {{-- TIME TRACKING (col-span-2) --}}
                    <div class="col-span-2 text-sm space-y-1">
                        <p class="text-gray-600"><span class="text-gray-500">{{translate_text('Duration')}}:</span> <span class="font-bold text-gray-800">{{ $project->duration ?? '0h 0m' }}</span></p>
                        <p class="font-medium text-green-500 border-b-2 border-green-500 border-dotted w-fit">{{ $project->time_difference ?? '+0h 0m' }}</p>
                    </div>

                    {{-- PROGRESS (col-span-1) --}}
                    <div class="col-span-1">
                         <div class="flex items-center gap-2">
                            <div class="flex-grow bg-gray-200 rounded-full h-1.5">
                                <div class="bg-gray-400 h-1.5 rounded-full" style="width: {{ $project['progress'] }}"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-700">{{ $project->progress ?? '0%' }}</span>
                        </div>
                    </div>

                    {{-- CLIENT & TEAMS (col-span-3) --}}
                    <div class="col-span-3 space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">{{translate_text('Client')}}:</p>
                            <p class="text-sm font-medium text-blue-600">{{ $project->client->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">{{translate_text('Teams:')}}</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @forelse($project->teams as $team)
                                    <span class="inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">{{ $team->name }}</span>
                                @empty
                                    <span class="text-xs text-gray-500">No teams assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS (col-span-1) --}}
                    <div class="col-span-1 flex justify-end gap-2">
                            {{-- Link to project show page --}}
                            <a href="{{route('admin.projects.show', $project->id)}}" class="text-blue-500 hover:text-blue-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.07.207-.07.431 0 .639C20.268 16.057 16.477 19 12 19c-4.478 0-8.268-2.943-9.542-7a1.186 1.186 0 010-.639z" />
                                </svg>
                            </a>
                            {{-- Delete Project Form --}}
                            <form action="{{ route('admin.projects.destroy', $project->id) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure?');"
                            class="inline">
                            @csrf
                            @method('DELETE')
                            
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                </div>
                @endforeach
            </div>
            
            {{-- FIXED: Pagination with proper horizontal styling --}}
            <div class="p-6 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    {{ $data['projects']->links() }}
                </div>
            </div>
        </div>

    {{-- Today's Events --}}
    <div class="lg:col-span-1 border-0 shadow-sm bg-white rounded-2xl hover:shadow-xl p-6 h-fit animate-fadeInSlideUp" style="animation-delay: 700ms;">
        {{-- Header with "Add Event" button --}}
        <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-5 bg-blue-600 rounded"></div>
                <h2 class="text-lg font-semibold text-gray-900">Today's Events</h2>
            </div>
            
            <button onclick="window.Livewire.dispatch('openCalendarForm')" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Add Event
            </button>
        </div>
            
        {{-- Date --}}
        <p class="text-sm text-gray-500 mb-6">{{ now()->format('d F Y') }}</p>
            
        {{-- Events list --}}
        <ul class="space-y-5">
            @forelse($data['dayEvents'] as $event)
            <li class="flex gap-3">
                <div class="w-1 flex-shrink-0 bg-blue-500 rounded-full"></div>
                
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $event->title }}</p>
                    <p class="text-sm text-gray-500 flex items-center gap-1.5 mt-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                        </span>
                    </p>
                </div>
            </li>
        @empty
            <li class="text-sm text-gray-500">
                No events scheduled for today.
            </li>
        @endforelse
        </ul>

        {{-- Centered "View full calendar" link --}}
        <div class="text-center mt-6">
            <button onclick="window.Livewire.dispatch('openCalendarModal')" class="text-sm font-medium text-blue-600 hover:underline">
            See the full calendar
            </button>
        </div>
    </div>
</div>

    </div>
</div>

@livewire('full-calendar-modal')

<script>
$(document).ready(function() {
    $('#employeesTable').DataTable({
        pageLength: 5,
        lengthChange: false,
        language: {
            search: "",
            searchPlaceholder: "{{translate_text('Search employees...')}}",
            info: "_START_–_END_ of _TOTAL_ employees",
            paginate: {
                previous: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>',
                next: '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>'
            }
        },
        dom: `
            <"flex justify-between items-center mb-4"f>
            t
            <"flex justify-between items-center mt-4"ip>
        `
    });
});
</script>