<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Archived Employees') }} - DalyTrac</title>
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-8">

        {{-- Container for the table --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

            {{-- Header Section --}}
            <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
              <h6 class="text-lg font-semibold text-gray-900">{{ __('messages.Archived Employees') }}</h6>
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
                  {{ __('messages.Return Back') }}
                </a>
              </div>
            </div>

            {{-- Session message --}}
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
                    <table class="w-full text-left border-collapse" style="table-layout: fixed; min-width: 100%;">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 2.5%;"></th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;">{{ __('messages.Name') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 10%;">{{ __('messages.Email') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5.5%;">{{ __('messages.Phone') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 4%;">{{ __('messages.Status') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 3%;">{{ __('messages.Age') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.CNSS') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 5%;">{{ __('messages.Bank Account') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right" style="width: 5%;">{{ __('messages.Salary') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right" style="width: 5%;">{{ __('messages.Cost/Hour') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">{{ __('messages.Work Hours Limit') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right" style="width: 5%;">{{ __('messages.Cotisation CNSS') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right" style="width: 5%;">{{ __('messages.Cotisation AMO') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5.5%;">{{ __('messages.Date Release') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;">{{ __('messages.Role') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 9%;">{{ __('messages.Action') }}</th>
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
                <td class="px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="name">
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
                <td class="px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-all" data-id="{{$employee->id}}" data-column="email">{{$employee->email}}</td>
                <td class="px-2 py-2 text-xs text-gray-700 editable whitespace-nowrap" data-id="{{$employee->id}}" data-column="phoneNumber">{{$employee->phoneNumber}}</td>
                {{-- Status --}}
                <td class="px-2 py-2 text-xs text-gray-700 text-center">
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
                <td class="px-2 py-2 text-xs text-gray-700 text-center editable" data-id="{{$employee->id}}" data-column="age">{{$employee->age}}</td>
                <td class="px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="cnss">{{$employee->cnss}}</td>
                <td class="px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="bankAccountNumber">{{$employee->bankAccountNumber}}</td>
                <td class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="salary">{{$employee->salary}} €</td>
                <td class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="cost_per_hour">{{number_format($employee->cost_per_hour, 2, '.', '')}} €</td>
                <td class="px-2 py-2 text-xs text-gray-700 text-center editable" data-id="{{$employee->id}}" data-column="workHours_limit">{{$employee->workHours_limit}} h</td>
                <td class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="Cotisation_CNSS">{{$employee->Cotisation_CNSS}} €</td>
                <td class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="Cotisation_AMO">{{$employee->Cotisation_AMO}} €</td>
                <td class="px-2 py-2 text-xs text-gray-700 text-center editable" data-id="{{$employee->id}}" data-column="Date_Release">{{$employee->Date_Release}}</td>
                {{-- Roles --}}
                <td class="px-1 py-2 text-xs text-gray-700">
                    <div class="flex flex-col gap-0.5 max-h-[60px] overflow-hidden leading-tight">
                        @forelse($employee->roles as $role)
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded {{ in_array($role, ['Admin','SuperAdmin']) ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700' }}">
                                {{ $role }}
                            </span>
                        @empty
                            <span class="inline-block px-1.5 py-0.5 text-[10px] font-medium rounded bg-red-100 text-red-700">{{ __('messages.No Role') }}</span>
                        @endforelse
                    </div>
                </td>
                {{-- Actions --}}
                <td class="px-2 py-2 text-xs text-gray-700">
                    @can('Edit employee roles')
                        <a href="{{ route('admin.employees.edit', $employee->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-blue-600 hover:bg-blue-700" title="{{ __('messages.Edit roles') }}">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M15.232 5.232l3.536 3.536M4 20h4l9.5-9.5-4-4L4 16v4z" />
                            </svg>
                        </a>
                    @endcan
                    <form action="{{ route('admin.employees.unarchive', $employee->id) }}" method="post" class="inline">@csrf
                        <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-gray-500 hover:bg-gray-600" title="{{ __('messages.Unarchive') }}">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </button>
                    </form>
                    @can('Delete employee')
                        @if($user && $user->id != auth()->user()->id)
                            <form action="{{ route('admin.employees.destroy', $employee->id) }}" method="post" class="inline">@csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700" title="{{ __('messages.Delete') }}">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862A2 2 0 015.995 19.142L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                    </svg>
                                </button>
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
