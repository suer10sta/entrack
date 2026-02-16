<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DalyTrac Dashboard</title>
    @vite('resources/css/app.css')
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

        {{-- Main Content --}}
        <main class="flex-1 p-6 bg-[#eaebef]">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center bg-white p-6 rounded-lg shadow border border-gray-200 mb-4">
          <h1 class="text-lg font-semibold text-gray-900">Archived Employees</h1>
          <div class="flex flex-wrap gap-2 mt-4 sm:mt-0">
            {{-- Return Back Button --}}
            @php
            $backRoute = '#';
            if (Auth::user()->hasRole('Admin')) $backRoute = route('admin.employees.index');
            elseif (Auth::user()->hasRole('Responsable de projet')) $backRoute = route('Responsable_de_projet.employees.index');
            elseif (Auth::user()->hasRole('Employee')) $backRoute = route('employee.employees.index');
            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $backRoute = route('Responsable_gestionnaire.employees.index');
            @endphp
            <a href="{{ $backRoute }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
              Return Back
            </a>
          </div>
        </div>

{{-- Session message --}}
@if(session('message'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
        {{ session('message') }}
        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl">&times;</button>
    </div>
@endif

{{-- Table --}}
<div class="bg-white rounded-lg shadow border border-gray-200 overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="border-b border-gray-200 bg-gray-50">
            <tr class="text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                <th class="px-1 py-1 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 0.5%;"></th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">Name</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 10%;">Email</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5.5%;">Phone</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 4%;">Status</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 3%;">Age</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">CNSS</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">Bank Account</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 8%;">Salary</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;" >Cost/Hour</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;">Work Hours Limit</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">Cotisation CNSS</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">Cotisation AMO</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">Date Release</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">Role</th>
                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 9%;">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @foreach($employees as $employee)
            @php
                $user = \App\Models\User::find($employee->user_id);
                $colors = ['#2ECC71','#BDC3C7','#F39C12','#E74C3C','#9B59B6','#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                $bgColor = $colors[$loop->index % count($colors)];
            @endphp
            <tr class="hover:bg-gray-50">
                {{-- Avatar --}}
                <td class="px-2 py-2 whitespace-nowrap">
                    @if($employee->image)
                        <img class="h-8 w-8 rounded-full object-cover" src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}" alt="{{ $employee->name }}">
                    @else
                        <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-medium" style="background-color: {{ $bgColor }};">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                    @endif
                </td>
                {{-- Name --}}
                <td class="px-4 py-4 text-xs text-gray-700 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="name">
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
                        <a href="{{ $showRoute }}" class="text-blue-600 hover:underline font-medium">{{$employee->name}}</a>
                    @else
                        <span class="font-medium text-gray-900">{{$employee->name}}</span>
                    @endif
                </td>
                <td class="px-6 py-4 text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="email">{{$employee->email}}</td>
                <td class="px-6 py-4 text-sm text-gray-700 editable" data-id="{{$employee->id}}" data-column="phoneNumber">{{$employee->phoneNumber}}</td>
                {{-- Status --}}
                <td class="px-6 py-4 text-sm">
                    @if($user && $user->is_activate == 1)
                        @php
                            $statusRoute = $user->is_activate ? ($user->hasRole('Admin') ? route('admin.employee.deactivate', $employee->id) : route('Responsable_de_projet.employee.deactivate', $employee->id)) : '#';
                        @endphp
                        <form action="{{ $statusRoute }}" method="post" class="inline">@csrf
                            <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</button>
                        </form>
                    @else
                        @php
                            $statusRoute = Auth::user()->hasRole('Admin') ? route('admin.employee.activate', $employee->id) : route('Responsable_de_projet.employee.activate', $employee->id);
                        @endphp
                        <form action="{{ $statusRoute }}" method="post" class="inline">@csrf
                            <button type="submit" class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</button>
                        </form>
                    @endif
                </td>
                {{-- Other fields --}}
                <td class="px-6 py-4 text-sm editable" data-id="{{$employee->id}}" data-column="age">{{$employee->age}}</td>
                <td class="px-6 py-4 text-sm editable" data-id="{{$employee->id}}" data-column="cnss">{{$employee->cnss}}</td>
                <td class="px-6 py-4 text-sm editable" data-id="{{$employee->id}}" data-column="bankAccountNumber">{{$employee->bankAccountNumber}}</td>
                <td class="px-6 py-4 text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="salary">{{$employee->salary}} €</td>
                <td class="px-6 py-4 text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="cost_per_hour">{{number_format($employee->cost_per_hour, 2, '.', '')}} €</td>
                <td class="px-6 py-4 text-sm editable" data-id="{{$employee->id}}" data-column="workHours_limit">{{$employee->workHours_limit}} h</td>
                <td class="px-6 py-4 text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="Cotisation_CNSS">{{$employee->Cotisation_CNSS}} €</td>
                <td class="px-6 py-4 text-sm text-green-600 font-medium editable" data-id="{{$employee->id}}" data-column="Cotisation_AMO">{{$employee->Cotisation_AMO}} €</td>
                <td class="px-6 py-4 text-sm editable" data-id="{{$employee->id}}" data-column="Date_Release">{{$employee->Date_Release}}</td>
                {{-- Roles --}}
                <td class="px-6 py-4 text-sm">
                    <div class="flex flex-wrap gap-1">
                        @forelse($employee->roles as $role)
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded {{ in_array($role, ['Admin','SuperAdmin']) ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $role }}
                            </span>
                        @empty
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded bg-red-100 text-red-700">No Role</span>
                        @endforelse
                    </div>
                </td>
                {{-- Actions --}}
                <td class="px-2 py-2 text-sm space-x-1">
                    @can('Edit employee roles')
                        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white px-2 py-1 rounded text-xs">Edit Roles</a>
                    @endcan
                    <form action="{{ route('admin.employees.unarchive', $employee->id) }}" method="post" class="inline">@csrf
                        <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-2 py-1 rounded text-xs">Unarchive</button>
                    </form>
                    @can('Delete employee')
                        @if($user && $user->id != auth()->user()->id)
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="post" class="inline">@csrf @method('DELETE')
                                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-2 py-1 rounded text-xs">Delete</button>
                            </form>
                        @endif
                    @endcan
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

</main>
</div>

@include('dashboard_layouts.scripts')
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

@can('Update project')
<script>
$(document).ready(function(){
$(".editable").dblclick(function(){
    var originalContent = $(this).text().trim();
    var width = $(this).width();
    $(this).html("<input type='text' value='" + originalContent + "' style='width: 100%; min-width: " + width + "px;' />");
    var input = $(this).children().first();
    input.focus();
    input[0].setSelectionRange(0, 0);
    input.blur(function(){
        var newContent = $(this).val();
        var clientId = $(this).parent().data('id');
        var columnName = $(this).parent().data('column');
        $(this).parent().text(newContent);
        $.ajax({
            url: 'projects/updateProject',
            type: 'post',
            data: {
                content: newContent,
                id: clientId,
                column: columnName,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            }
        });
    });
});
});
</script>
@endcan
  </body>
</html>
