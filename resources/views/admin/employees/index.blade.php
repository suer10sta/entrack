<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - BizManager</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    @livewireStyles
    {{-- Include dashboard_layouts.head if it contains essential meta tags or CSS links not covered by Vite --}}
    {{-- @include('dashboard_layouts.head') --}}
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">

            {{-- Container for the table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h6 class="text-lg font-semibold text-gray-900">Employees Table</h6>

                    {{-- Buttons --}}
                    <div class="flex flex-wrap gap-2">
                        {{-- Report Button --}}
                        @php
                            $reportRoute = '#'; // Default fallback
                            if (Auth::user()->hasRole('Responsable de projet')) $reportRoute = route('Responsable_de_projet.Employeesrepports.downloadRepports');
                            elseif (Auth::user()->hasRole('Admin')) $reportRoute = route('admin.Employeesrepports.downloadRepports');
                            elseif (Auth::user()->hasRole('Employee')) $reportRoute = route('employee.Employeesrepports.downloadRepports');
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportRoute = route('Responsable_gestionnaire.Employeesrepports.downloadRepports');
                        @endphp
                        <a href="{{ $reportRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                           <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                           Employees Reports PDF
                        </a>

                        {{-- Add Team Button (Admin only based on original logic?) --}}
                        @can('Add teams') {{-- Assuming only Admin has this from original code --}}
                           <a href="{{ route('admin.teams.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                              Add Team
                           </a>
                        @endcan

                        {{-- Add Employee Button (Admin only based on original logic?) --}}
                         @can('Add employee') {{-- Assuming only Admin has this from original code --}}
                           <a href="{{ route('admin.employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                              Add Employee
                           </a>
                        @endcan

                         {{-- View Archive Button (Admin only based on original logic?) --}}
                         @if(Auth::user()->hasRole('Admin')) {{-- Check explicitly if needed --}}
                            <a href="{{ route('admin.employeesArchived.archiveIndex') }}" class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                               <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" /></svg>
                                View Archive
                            </a>
                         @endif
                    </div>
                </div>

                {{-- Session Message Alert --}}
                @if(session('message'))
                    <div class="m-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl" aria-label="Close">
                           &times;
                        </button>
                    </div>
                @endif

                {{-- Table Wrapper --}}
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table id="example" class="w-full text-left border-collapse min-w-full">
                            <thead class="border-b border-gray-200 bg-gray-50">
                                <tr>
                                    {{-- Adjusted headers based on your content --}}
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left"></th> 
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Name</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Login Email</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Phone</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Status</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Age</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">CNSS</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Bank Acc.</th> 
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Salary</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Cost/Hour</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Hour Limit</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">CNSS Contr.</th> 
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">AMO Contr.</th> 
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Release Date</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Role</th>
                                    <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider text-left">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse($employees as $employee)
                                <tr class="hover:bg-gray-50">
                                    {{-- Avatar/Initials --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $colors = ['#2ECC71', '#BDC3C7', '#F39C12', '#E74C3C', '#9B59B6', '#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                                            $bgColor = $colors[$loop->index % count($colors)];
                                        @endphp
                                        @if($employee->image)
                                            <img class="h-10 w-10 rounded-full object-cover"
                                                 src="{{ Storage::disk('do_spaces')->url('employee_images/' . $employee->image) }}"
                                                 alt="{{ $employee->name }} image">
                                        @else
                                            <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-medium"
                                                 style="background-color: {{ $bgColor }};">
                                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                                            </div>
                                        @endif
                                    </td>
                                    {{-- Name with Link --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="name">
                                         @php
                                            $showRoute = '#';
                                            $canView = Auth::user()->can('View employee');
                                            if ($canView) {
                                                if (Auth::user()->hasRole('Responsable de projet')) $showRoute = route('Responsable_de_projet.employees.show', $employee->id);
                                                elseif (Auth::user()->hasRole('Admin')) $showRoute = route('admin.employees.show', $employee->id);
                                                elseif (Auth::user()->hasRole('Employee')) $showRoute = route('employee.employees.show', $employee->id);
                                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $showRoute = route('Responsable_gestionnaire.employees.show', $employee->id);
                                            }
                                         @endphp
                                         @if($canView)
                                            <a href="{{ $showRoute }}" class="text-blue-600 hover:underline font-medium">
                                              {{$employee->name}}
                                            </a>
                                         @else
                                            <span class="font-medium text-gray-900">{{$employee->name}}</span>
                                         @endif
                                    </td>
                                    {{-- Other Editable Fields --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="email">{{$employee->email}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="phoneNumber">{{$employee->phoneNumber}}</td>
                                    
                                    {{-- Status Badge/Button --}}
                                    <td class="px-6 py-4 text-sm text-gray-700">
                                            @php
                                                $user = \App\Models\User::find($employee->user_id);
                                                $statusRoute = '#';
                                                $statusText = $user && $user->is_activate ? 'Active' : 'Inactive';
                                                $buttonColor = $user && $user->is_activate ? 'green' : 'red';

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
                                                  onsubmit="return confirm('{{ $user && $user->is_activate ? 'Deactivate this employee?' : 'Activate this employee?' }}');"
                                                  class="inline">
                                                @csrf
                                                <button type="submit"
                                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $buttonColor }}-100 text-{{ $buttonColor }}-800 border-none cursor-pointer">
                                                    {{ $statusText }}
                                                </button>
                                            </form>
                                        </td>

                                    {{-- More Editable Fields --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="age">{{$employee->age}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="cnss">{{$employee->cnss}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="bankAccountNumber">{{$employee->bankAccountNumber}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="salary">{{$employee->salary}} €</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="cost_per_hour">{{number_format($employee->cost_per_hour, 2, '.', '')}} €</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="workHours_limit">{{$employee->workHours_limit}} h</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="Cotisation_CNSS">{{$employee->Cotisation_CNSS}} €</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="Cotisation_AMO">{{$employee->Cotisation_AMO}} €</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="Date_Release">{{$employee->Date_Release}}</td>
                                    
                                    {{-- Role Badges --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                         <div class="flex flex-wrap gap-1">
                                             @forelse($employee->roles as $role)
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded {{ $role=='SuperAdmin' || $role=='Admin' ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700' }}">
                                                    {{ $role }}
                                                </span>
                                             @empty
                                                <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-700">
                                                   No Role
                                                </span>
                                             @endforelse
                                         </div>
                                    </td>

                                    {{-- Action Buttons --}}
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 space-x-1"> {{-- Add space between buttons --}}
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

                                        @if($canEditRoles)
                                            <a href="{{ $editRoute }}" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Edit Roles
                                            </a>
                                        @endif
                                        
                                        {{-- Archive Button (as form) --}}
                                        <form action="{{ $archiveRoute }}" method="post" onsubmit="return confirm('Archive this employee?');" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center px-2.5 py-1 border border-gray-300 text-xs font-medium rounded shadow-sm text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Archive
                                            </button>
                                        </form>

                                        @if($canDelete && !$isSelf)
                                            <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('Are you sure you want to delete this employee?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center px-2.5 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                                    Delete
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                     <td colspan="16" class="px-6 py-4 text-center text-sm text-gray-500"> {{-- Adjusted colspan --}}
                                         No employees found.
                                     </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}}

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    @can('Update employee') // Keep the permission check around the script
    $(document).ready(function(){
        // Use event delegation for dynamically added/reloaded content if needed
        $('body').on('dblclick', '.editable', function(){
            if ($(this).find('input').length > 0) {
                return; // Already editing
            }

            var originalContent = $(this).text().trim();
            var cellWidth = $(this).outerWidth();

            // Add Tailwind classes to the input
            $(this).html("<input type='text' value='" + originalContent + "' class='w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-1' style='min-width: " + cellWidth + "px;' />");

            var input = $(this).children('input').first();
            input.focus();
            input[0].select(); // Select text

            input.blur(function(){
                var newContent = $(this).val();
                var $parentCell = $(this).parent();
                var employeeId = $parentCell.data('id');
                var columnName = $parentCell.data('column');

                // Revert text temporarily
                $parentCell.text(originalContent);

                $.ajax({
                    // IMPORTANT: Ensure this route is correct and accessible via POST
                    url: '/admin/employees/updateEmployee', // Example route - ADJUST THIS
                    type: 'POST',
                    data: {
                        content: newContent,
                        id: employeeId,
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $parentCell.text(newContent);
                        // Consider a success message
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $parentCell.text(originalContent); // Revert on error
                        alert('Error updating employee: ' + errorThrown);
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            });

            // Allow saving with Enter key
            input.keypress(function(e){
                if(e.which == 13){ // Enter key pressed
                    $(this).blur(); // Trigger save
                    return false;
                }
            });
        });
    });
    @endcan
</script>

</body>
</html>