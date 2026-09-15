<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Archived Employees') }} - DalyTrac</title>
    @vite('resources/css/app.css')

    <style>
        /* Mobile/tablet: show employee rows as stacked cards (no horizontal scroll) */
        @media (max-width: 1024px) {
            #employeesTable thead {
                display: none;
            }
            #employeesTable tbody {
                display: block;
            }
            #employeesTable tbody tr {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                margin-bottom: 16px;
                border: 1px solid rgba(148, 163, 184, 0.55); /* slate-400/55 */
                border-radius: 16px;
                background: linear-gradient(180deg, rgba(255,255,255,0.98) 0%, rgba(248,250,252,0.98) 100%);
                overflow: hidden;
                box-shadow:
                    0 12px 30px rgba(15, 23, 42, 0.08),
                    0 2px 6px rgba(15, 23, 42, 0.06);
                transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
                padding: 12px;
            }
            #employeesTable tbody tr:hover {
                transform: translateY(-1px);
                border-color: rgba(59, 130, 246, 0.45); /* blue-500/45 */
                box-shadow:
                    0 16px 40px rgba(15, 23, 42, 0.10),
                    0 3px 10px rgba(15, 23, 42, 0.08);
            }
            #employeesTable tbody td {
                display: block;
                width: 100%;
                padding: 10px 10px;
                border: 1px solid rgba(226, 232, 240, 0.9); /* slate-200 */
                border-radius: 12px;
                background: rgba(255,255,255,0.75);
                text-align: left !important;
                white-space: normal !important;
            }
            #employeesTable tbody td:last-child {
                border-bottom: 0;
            }
            #employeesTable tbody td[data-label]::before {
                content: attr(data-label);
                display: block;
                font-size: 11px;
                font-weight: 700;
                letter-spacing: .04em;
                text-transform: uppercase;
                color: #64748b; /* slate-500 */
                margin-bottom: 4px;
            }
            #employeesTable tbody td[data-label] {
                color: #0f172a; /* slate-900 */
            }
            #employeesTable tbody td[data-span="2"] {
                grid-column: 1 / -1;
            }
            /* On mobile/tablet: name is shown next to avatar, so hide the separate Name cell */
            #employeesTable tbody td.employee-name-cell {
                display: none !important;
            }
            /* Keep avatar compact and avoid empty labels */
            #employeesTable tbody td[data-label=""]::before,
            #employeesTable tbody td:not([data-label])::before {
                display: none;
            }

            /* Mobile accordion: collapsed row shows only header (avatar + name) */
            #employeesTable tbody tr.employee-row.is-collapsed td:not(.employee-toggle-cell) {
                display: none !important;
            }
            #employeesTable tbody tr.employee-row td.employee-toggle-cell {
                cursor: pointer;
                user-select: none;
            }
            #employeesTable tbody tr.employee-row td.employee-toggle-cell .employee-chevron {
                transition: transform 180ms ease;
            }
            #employeesTable tbody tr.employee-row[aria-expanded="true"] td.employee-toggle-cell .employee-chevron {
                transform: rotate(180deg);
            }
        }
    </style>
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
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-4 sm:p-6 lg:p-8">

        {{-- Container for the table --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

            {{-- Header Section --}}
            <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
              <h6 class="text-lg font-semibold text-gray-900">{{ __('messages.Archived Employees') }}</h6>
              <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2 w-full sm:w-auto mt-2 sm:mt-0">
                {{-- Return Back Button --}}
                @php
                $backRoute = '#';
                if (Auth::user()->hasRole('Admin')) $backRoute = route('admin.employees.index');
                elseif (Auth::user()->hasRole('Responsable de projet')) $backRoute = route('Responsable_de_projet.employees.index');
                elseif (Auth::user()->hasRole('Employee')) $backRoute = route('employee.employees.index');
                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $backRoute = route('Responsable_gestionnaire.employees.index');
                @endphp
                <a href="{{ $backRoute }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg w-full sm:w-auto text-center">
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
            <div class="p-4 sm:p-6">
                <div class="overflow-x-visible" style="-webkit-overflow-scrolling: touch;">
                    <table id="employeesTable" class="w-full text-left border-collapse table-auto">
                        <thead class="border-b border-gray-200 bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap"></th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Name') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Email') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Phone') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Status') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Age') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.CNSS') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Bank Account') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">{{ __('messages.Salary') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">{{ __('messages.Cost/Hour') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Work Hours Limit') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">{{ __('messages.Cotisation CNSS') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-right whitespace-nowrap">{{ __('messages.Cotisation AMO') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Date Release') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-left whitespace-nowrap">{{ __('messages.Role') }}</th>
                                <th class="px-2 py-2 text-xs font-bold text-gray-600 uppercase tracking-wider text-center whitespace-nowrap">{{ __('messages.Action') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
            @foreach($employees as $employee)
            @php
                $user = \App\Models\User::find($employee->user_id);
                $colors = ['#2ECC71','#BDC3C7','#F39C12','#E74C3C','#9B59B6','#2E86C1','#641E16','#2C3E50','#F4D03F','#F4D03F'];
                $bgColor = $colors[$loop->index % count($colors)];
            @endphp
            <tr class="employee-row hover:bg-gray-50" data-employee-row aria-expanded="true">
                {{-- Avatar/Initials (toggle header on mobile/tablet) --}}
                <td data-label="" data-span="2" class="px-2 py-2 whitespace-nowrap employee-toggle-cell" data-employee-toggle>
                    {{-- Mobile/tablet: avatar + name together --}}
                    <div class="flex items-center justify-between gap-3 lg:hidden">
                        <div class="flex items-center gap-3 min-w-0">
                            @if($employee->image)
                                <img class="h-8 w-8 rounded-full object-cover"
                                     src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}"
                                     alt="{{ $employee->name }}">
                            @else
                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-medium text-xs"
                                     style="background-color: {{ $bgColor }};">
                                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                                </div>
                            @endif

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
                                <a href="{{ $showRoute }}"
                                   class="min-w-0 truncate text-sm font-extrabold text-slate-900 hover:underline">
                                    {{ $employee->name }}
                                </a>
                            @else
                                <span class="min-w-0 truncate text-sm font-extrabold text-slate-900">
                                    {{ $employee->name }}
                                </span>
                            @endif
                        </div>
                        <span class="shrink-0 text-slate-400" aria-hidden="true">
                            <svg class="employee-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </span>
                    </div>

                    {{-- Desktop: keep original (avatar only) --}}
                    <div class="hidden lg:block">
                        @if($employee->image)
                            <img class="h-8 w-8 rounded-full object-cover"
                                 src="{{ Storage::disk('public')->url('employee_images/' . $employee->image) }}"
                                 alt="{{ $employee->name }}">
                        @else
                            <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-medium text-xs"
                                 style="background-color: {{ $bgColor }};">
                                {{ strtoupper(substr($employee->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                </td>
                {{-- Name --}}
                <td data-label="{{ __('messages.Name') }}" class="employee-name-cell px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="name">
                    @if($canView)
                        <a href="{{ $showRoute }}" class="text-blue-600 hover:underline font-medium">{{$employee->name}}</a>
                    @else
                        <span class="font-medium text-gray-900">{{$employee->name}}</span>
                    @endif
                </td>
                <td data-label="{{ __('messages.Email') }}" data-span="2" class="px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-all" data-id="{{$employee->id}}" data-column="email">{{$employee->email}}</td>
                <td data-label="{{ __('messages.Phone') }}" class="px-2 py-2 text-xs text-gray-700 editable whitespace-nowrap" data-id="{{$employee->id}}" data-column="phoneNumber">{{$employee->phoneNumber}}</td>
                {{-- Status --}}
                <td data-label="{{ __('messages.Status') }}" class="px-2 py-2 text-xs text-gray-700 text-center">
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
                <td data-label="{{ __('messages.Age') }}" class="px-2 py-2 text-xs text-gray-700 text-center editable" data-id="{{$employee->id}}" data-column="age">{{$employee->age}}</td>
                <td data-label="{{ __('messages.CNSS') }}" class="px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="cnss">{{$employee->cnss}}</td>
                <td data-label="{{ __('messages.Bank Account') }}" data-span="2" class="px-2 py-2 text-xs text-gray-700 editable whitespace-normal break-words" data-id="{{$employee->id}}" data-column="bankAccountNumber">{{$employee->bankAccountNumber}}</td>
                <td data-label="{{ __('messages.Salary') }}" class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="salary">{{$employee->salary}} €</td>
                <td data-label="{{ __('messages.Cost/Hour') }}" class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="cost_per_hour">{{number_format($employee->cost_per_hour, 2, '.', '')}} €</td>
                <td data-label="{{ __('messages.Work Hours Limit') }}" class="px-2 py-2 text-xs text-gray-700 text-center editable" data-id="{{$employee->id}}" data-column="workHours_limit">{{$employee->workHours_limit}} h</td>
                <td data-label="{{ __('messages.Cotisation CNSS') }}" class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="Cotisation_CNSS">{{$employee->Cotisation_CNSS}} €</td>
                <td data-label="{{ __('messages.Cotisation AMO') }}" class="px-2 py-2 text-xs text-green-600 font-medium text-right editable" data-id="{{$employee->id}}" data-column="Cotisation_AMO">{{$employee->Cotisation_AMO}} €</td>
                <td data-label="{{ __('messages.Date Release') }}" class="px-2 py-2 text-xs text-gray-700 text-center editable" data-id="{{$employee->id}}" data-column="Date_Release">{{$employee->Date_Release}}</td>
                {{-- Roles --}}
                <td data-label="{{ __('messages.Role') }}" data-span="2" class="px-1 py-2 text-xs text-gray-700">
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
                <td data-label="{{ __('messages.Action') }}" data-span="2" class="px-2 py-2 text-xs text-gray-700">
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

<script>
    // Mobile/tablet accordion per employee row (show only header when collapsed)
    document.addEventListener('DOMContentLoaded', function () {
        const mql = window.matchMedia('(max-width: 1024px)');

        function setRowCollapsed(row, collapsed) {
            if (!row) return;
            row.classList.toggle('is-collapsed', collapsed);
            row.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        }

        function initAccordions() {
            const rows = document.querySelectorAll('#employeesTable tbody tr.employee-row');
            const isMobile = mql.matches;
            rows.forEach(row => setRowCollapsed(row, isMobile));
        }

        document.addEventListener('click', function (e) {
            const toggleCell = e.target.closest('[data-employee-toggle]');
            if (!toggleCell) return;

            // Let normal link clicks navigate without toggling.
            if (e.target.closest('a')) return;

            const row = toggleCell.closest('tr.employee-row');
            if (!row) return;

            const collapsed = row.classList.contains('is-collapsed');
            setRowCollapsed(row, !collapsed);
        });

        if (mql.addEventListener) mql.addEventListener('change', initAccordions);
        else if (mql.addListener) mql.addListener(initAccordions);

        initAccordions();
    });
</script>
  </body>
</html>
