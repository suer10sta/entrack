<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Client') }} {{ __('messages.Details') ?? 'Details' }}: {{ $client->name }}</title>
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
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 p-8">
            <div class="max-w-8xl mx-auto space-y-6">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    
                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-slate-900 mb-1">{{ __('messages.Personal Infos') }}</h2>
                        <p class="text-sm text-slate-500 mb-4">{{ __('messages.Mandatory informations') }}</p>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Name') }}</label>
                                    <input type="text" name="name" value="{{$client->name}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="name" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Client Number') }}</label>
                                    <input type="text" name="client_number" value="{{$client->client_number}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="client_number" readonly />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                    <input type="text" name="email" value="{{$client->email}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="email" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Phone') }}</label>
                                    <input type="number" name="phone" value="{{$client->phone}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="phone" readonly />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-slate-900 mb-1">{{ __('messages.Address Infos') }}</h2>
                        <p class="text-sm text-slate-500 mb-4">{{ __('messages.Mandatory informations') }}</p>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Street') }}</label>
                                    <input type="text" name="address" value="{{$client->address}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="address" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.City') }}</label>
                                    <input type="text" name="city" value="{{$client->city}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="city" readonly />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Zip Code') }}</label>
                                    <input type="number" name="zip" value="{{$client->zip}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="zip" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Country') }}</label>
                                    <input type="text" name="country" value="{{$client->country}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="country" readonly />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-lg p-6">
                        <h2 class="text-xl font-bold text-slate-900 mb-1">{{ __('messages.Billing Infos') }}</h2>
                        <p class="text-sm text-slate-500 mb-4">{{ __('messages.Mandatory informations') }}</p>
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.TVA Number') }}</label>
                                    <input type="text" name="TVA" value="{{$client->TVA}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="TVA" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Due Date (Days)') }}</label>
                                    <input type="text" name="due_date" value="{{$client->due_date}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="due_date" readonly />
                                </div>
                            </div>

                            <div class="flex items-center pt-2">
                                <input id="billingSameAsClient" type="checkbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="billingSameAsClient" class="ml-2 block text-sm font-medium text-gray-800">{{ __('messages.Billing address is same as client') }}</label>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Billing Street') }}</label>
                                    <input type="text" name="billing_street" value="{{$client->billing_street}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="billing_street" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Billing City') }}</label>
                                    <input type="text" name="billing_city" value="{{$client->billing_city}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="billing_city" readonly />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Billing Zip Code') }}</label>
                                    <input type="text" name="billing_zip" value="{{$client->billing_zip}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="billing_zip" readonly />
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Billing Country') }}</label>
                                    <input type="text" name="billing_country" value="{{$client->billing_country}}"
                                           class="editable w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                           data-id="{{$client->id}}" data-column="billing_country" readonly />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                            <div>
                                <h2 class="text-xl font-bold text-slate-900 mb-1">{{ __('messages.Projects Infos') }}</h2>
                                <p class="text-sm text-slate-500">{{ __('messages.Mandatory informations') }}</p>
                            </div>
                            @can('Add project')
                                @php
                                    $createProjectRoute = '';
                                    if(Auth::user()->hasRole('Admin')) $createProjectRoute = route('admin.projects.create', $client->id);
                                    elseif(Auth::user()->hasRole('Employee')) $createProjectRoute = route('employee.projects.create', $client->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet')) $createProjectRoute = route('Responsable_de_projet.projects.create', $client->id);
                                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $createProjectRoute = route('Responsable_gestionnaire.projects.create', $client->id);
                                @endphp
                                <a href="{{ $createProjectRoute }}" class="mt-3 sm:mt-0 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm">
                                    {{ __('messages.Add Project') }}
                                </a>
                            @endcan
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Project') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Budget') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPH</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Duration') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Estimated Hours') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Difference') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Progress') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.VAT') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Client column') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Teams column') }}</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($projects as $project)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @php
                                            // Resolve project show route based on role; fallback to admin if available
                                            $roleRouteMap = [
                                                'Admin' => 'admin.projects.show',
                                                'Responsable de projet' => 'Responsable_de_projet.projects.show',
                                                'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire.projects.show',
                                                'Employee' => 'employee.projects.show',
                                            ];

                                            $projectShowRoute = null;
                                            foreach ($roleRouteMap as $role => $routeName) {
                                                if (Auth::user()->hasRole($role) && Route::has($routeName)) {
                                                    $projectShowRoute = route($routeName, $project->id);
                                                    break;
                                                }
                                            }

                                            if (!$projectShowRoute && Route::has('admin.projects.show')) {
                                                $projectShowRoute = route('admin.projects.show', $project->id);
                                            }
                                        @endphp

                                        @if($projectShowRoute)
                                            <a href="{{ $projectShowRoute }}" class="text-blue-600 hover:text-blue-800">{{ strtoupper($project->name) }}</a>
                                        @else
                                            <span class="text-gray-900">{{ strtoupper($project->name) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">€{{$project->budget}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-semibold">€{{$project->cost_per_hour}}</td>
                                    
                                    @php
                                        $projecttasksId = App\Models\Tasks::where('project_id', $project->id)->pluck('id');
                                        $totalduration = App\Models\tasks_timer::whereIn('task_id', $projecttasksId)->sum('duration');
                                        $difference = $project->estimated_hours * 60 - $totalduration;
                                        $hours = floor(abs($totalduration) / 60);
                                        $minutes = abs($totalduration) % 60;
                                        $diffhours = floor(abs($difference) / 60);
                                        $diffminutes = abs($difference) % 60;
                                        $estimatedMinutes = $project->estimated_hours * 60;
                                        $progressPercentage = $estimatedMinutes > 0 ? ($totalduration / $estimatedMinutes) * 100 : 0;
                                        $formattedProgressPercentage = number_format($progressPercentage, 2);
                                    @endphp

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{$hours}}h:{{$minutes}}m</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-800 font-medium">{{$project->estimated_hours}}h</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold">
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 min-w-[150px]">
                                        <div class="w-full bg-gray-200 rounded-full h-3 relative">
                                            @php
                                                $progressColor = 'bg-green-500'; // Default: < 50%
                                                if ($progressPercentage >= 75) $progressColor = 'bg-red-500';
                                                elseif ($progressPercentage >= 50) $progressColor = 'bg-yellow-500';
                                            @endphp
                                            <div class="absolute inset-0 flex items-center justify-center">
                                                <span class="text-xs font-semibold {{ $progressPercentage > 40 ? 'text-white' : 'text-gray-700' }}">{{$formattedProgressPercentage}}%</span>
                                            </div>
                                            <div class="{{ $progressColor }} h-3 rounded-full" style="width: {{$formattedProgressPercentage}}%"></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">%{{$project->tva}}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{-- Client link logic truncated for brevity, assuming it's the current client --}}
                                        {{ App\Models\Client::where('id', $project->client_id)->first()->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        @foreach(App\Models\project_teams::where('project_id', $project->id)->get() as $project_team)
                                            <span class="block">{{$project_team->team_name}}</span>
                                        @endforeach
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                        @php
                                            $editRoute = '#'; $archiveRoute = '#'; $deleteRoute = '#';
                                            if(Auth::user()->hasRole('Admin')) {
                                                $editRoute = route('admin.projects.edit', $project->id);
                                                $archiveRoute = route('admin.projects.archive', $project->id);
                                                $deleteRoute = route('admin.projects.destroy', $project->id);
                                            } elseif(Auth::user()->hasRole('Responsable de projet')) {
                                                $editRoute = route('Responsable_de_projet.projects.edit', $project->id);
                                                $archiveRoute = route('Responsable_de_projet.projects.archive', $project->id);
                                                $deleteRoute = route('Responsable_de_projet.projects.destroy', $project->id);
                                            } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                                                $editRoute = route('Responsable_gestionnaire.projects.edit', $project->id);
                                                $archiveRoute = route('Responsable_gestionnaire.projects.archive', $project->id);
                                                $deleteRoute = route('Responsable_gestionnaire.projects.destroy', $project->id);
                                            } elseif(Auth::user()->hasRole('Employee')) {
                                                $editRoute = route('employee.projects.edit', $project->id);
                                                $archiveRoute = route('employee.projects.archive', $project->id);
                                                $deleteRoute = route('employee.projects.destroy', $project->id);
                                            }
                                        @endphp

                                        @if($editRoute != '#')
                                        <a href="{{ $editRoute }}" class="inline-block px-3 py-1 bg-blue-500 hover:bg-blue-600 text-white rounded-md text-xs font-medium">Edit</a>
                                        
                                <form action="{{ $archiveRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                            @csrf
                                            <button type="submit" class="px-3 py-1 bg-gray-500 hover:bg-gray-600 text-white rounded-md text-xs font-medium">{{ __('messages.Archive') }}</button>
                                        </form>

                                        <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('{{ __('messages.Are you sure?') }}');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded-md text-xs font-medium">{{ __('messages.Delete') }}</button>
                                        </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-2">
                                @php
                                    $filterRoute = Auth::user()->hasRole('Admin') ? route('admin.clients.filterInvoices', $client->id) : 
                                                (Auth::user()->hasRole('Employee') ? route('employee.clients.filterInvoices', $client->id) : 
                                                (Auth::user()->hasRole('Responsable de projet') ? route('Responsable_de_projet.clients.filterInvoices', $client->id) : 
                                                route('Responsable_gestionnaire.clients.filterInvoices', $client->id)));
                                @endphp
                                <form action="{{ $filterRoute }}" method="POST" class="flex flex-wrap items-center gap-2" id="filterForm">
                                    @csrf
                                    <input type="hidden" name="client_id" value="{{ $client->id }}">
                                    <input type="date" name="start_date" id="start_date" class="rounded-lg border-gray-300 shadow-sm text-sm" value="{{ request()->session()->get('start_date') }}">
                                    <input type="date" name="end_date" id="end_date" class="rounded-lg border-gray-300 shadow-sm text-sm" value="{{ request()->session()->get('end_date') }}">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium text-sm">{{ __('messages.Filter') }}</button>
                                </form>
                                <a href="#" onclick="downloadReport(event)" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm">{{ __('messages.Download Report') }}</a>
                            </div>
                            
                            <div class="grid grid-cols-2 lg:flex lg:flex-wrap gap-x-6 gap-y-2 lg:justify-end text-left lg:text-right">
                                <div>
                                    <div class="text-sm text-gray-600">{{ __('messages.Total Paid') }}</div>
                                    <div class="text-lg font-bold text-green-600">€{{ number_format($totalPaid, 2, ',', ' ') }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">{{ __('messages.Total Unpaid') }}</div>
                                    <div class="text-lg font-bold text-red-600">€{{ number_format($totalUnpaid, 2, ',', ' ') }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">{{ __('messages.Total TVA') }}</div>
                                    <div class="text-lg font-bold text-gray-800">€{{ number_format($totalTVA, 2, ',', ' ') }}</div>
                                </div>
                                <div>
                                    <div class="text-sm text-gray-600">{{ __('messages.Total Budget') }}</div>
                                    <div class="text-lg font-bold text-gray-800">€{{ number_format($totalBudget, 2, ',', ' ') }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200" id="invoicesTable">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Invoice N') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Project Name') }}</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ __('messages.Cost Total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($invoices as $invoice)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @php
                                            $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                                            $today = \Carbon\Carbon::now();
                                            $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                                            $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                                            
                                            $invoiceRoute = '#';
                                            if(Auth::user()->hasRole('Responsable de projet')) $invoiceRoute = route('Responsable_de_projet.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Admin')) $invoiceRoute = route('admin.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Employee')) $invoiceRoute = route('employee.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $invoiceRoute = route('Responsable_gestionnaire.invoices.servePdf', ['invoiceName' => $invoice->id]);
                                        @endphp
                                        <a href="{{ $invoiceRoute }}" target="_blank" class="{{ $isOverdue ? 'text-red-600 font-bold' : 'text-blue-600 hover:text-blue-800' }}">
                                            {{$entreprise_infos->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                                            @if($isOverdue)
                                                <span class="ml-2">({{ $daysOverdue }} {{ __('messages.days overdue') }})</span>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ strtoupper($invoice->project_name) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-bold {{ $invoice->Payment_status==1 ? 'text-green-600' : 'text-red-600' }}">
                                        €{{ number_format($invoice->total_ttc, 2, ',', ' ') }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                @php
                    $comptable = App\Models\client_contacts::where('client_id', $client->id)->where('is_comptable', 1)->first();
                @endphp
                
                @if($comptable)
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-slate-900 mb-1">{{ __('messages.Comptable Infos') }}</h2>
                    <p class="text-sm text-slate-500 mb-4">{{ __('messages.Mandatory informations') }}</p>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                                <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$comptable->id}}" data-column="name" type="text" value="{{$comptable->name}}" name="name" readonly />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$comptable->id}}" data-column="email" type="text" value="{{$comptable->email}}" name="email" readonly />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Mobile Fix') }}</label>
                                <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$comptable->id}}" data-column="mobile_fix" type="number" value="{{$comptable->mobile_fix}}" name="mobile_fix" readonly />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Address') }}</label>
                                <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$comptable->id}}" data-column="address" type="text" value="{{$comptable->address}}" name="address" readonly />
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                
                @php
                    $contacts = App\Models\client_contacts::where('client_id', $client->id)->get();
                    $i=1;
                @endphp
                
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h2 class="text-xl font-bold text-slate-900 mb-1">{{ __('messages.Contacts Infos') }}</h2>
                    <p class="text-sm text-slate-500 mb-4">{{ __('messages.Mandatory informations') }}</p>
                    
                    <div id="accordion" class="space-y-3">
                        @foreach ($contacts as $contact)
                        @if($contact->is_comptable==0)
                        <div class="border border-gray-200 rounded-lg overflow-hidden">
                            <div class="accordion-trigger flex justify-between items-center p-4 cursor-pointer" aria-expanded="false" aria-controls="collapse{{$i}}">
                                <span class="flex items-center gap-3">
                                    <svg class="h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2 3a1 1 0 011-1h1.78a1 1 0 01.95.684l1.11 3.109a1 1 0 01-.523 1.28L5.2 8.281a14.95 14.95 0 007.518 7.518l1.212-1.121a1 1 0 011.28-.523l3.109 1.11a1 1 0 01.684.95V17a1 1 0 01-1 1H3a1 1 0 01-1-1V3z"></path></svg>
                                    <span class="text-lg font-semibold text-slate-800">{{ ucfirst($contact->name) }}</span>
                                </span>
                                <div class="flex items-center gap-4">
                                    <form action="{{route('admin.clients.destroyContact',$contact->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-full text-xs font-medium">Delete</button>
                                    </form>
                                    <svg class="arrow h-5 w-5 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>
                                </div>
                            </div>
                            <div id="collapse{{$i}}" class="accordion-content hidden p-6 bg-gray-50 border-t border-gray-200">
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                                            <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$contact->id}}" data-column="name" type="text" value="{{$contact->name}}" name="name" readonly />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Functionality') }}</label>
                                            <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$contact->id}}" data-column="functionality" type="text" value="{{$contact->functionality}}" name="functionality" readonly />
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Mobile Fix') }}</label>
                                            <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$contact->id}}" data-column="mobile_fix" type="number" value="{{$contact->mobile_fix}}" name="mobile_fix" readonly />
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                            <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$contact->id}}" data-column="email" type="text" value="{{$contact->email}}" name="email" readonly />
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Address') }}</label>
                                        <input class="editable-contact w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" data-id="{{$contact->id}}" data-column="address" type="text" value="{{$contact->address}}" name="address" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @php $i++; @endphp
                        @endforeach
                    </div>

                    <form class="mt-6 pt-6 border-t border-gray-200" method="POST" action="{{ route('admin.clients.addContact', $client->id) }}">
                        @csrf
                        @method('PUT')
                        <h3 class="text-lg font-semibold text-slate-800 mb-4">{{ __('messages.Add New Contact') }}</h3>
                        
                        {{-- This div is the one that gets cloned --}}
                        <div id="contact_infos" class="space-y-4">
                            <div class="contact-entry space-y-4 p-4 border border-gray-200 rounded-lg">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Full Name') }}</label>
                                        <input class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" type="text" placeholder="{{ __('messages.Full Name') }}" name="contact_name[]" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Functionality') }}</label>
                                        <input class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" type="text" placeholder="{{ __('messages.Functionality') }}" name="contact_function[]" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Mobile Fix') }}</label>
                                        <input class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" type="number" placeholder="{{ __('messages.Mobile Fix') }}" name="contact_mobile_fix[]" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                        <input class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" type="text" placeholder="*****@gmail.com" name="contact_email[]" />
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-700 mb-1">{{ __('messages.Address') }}</label>
                                    <input class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" type="text" placeholder="{{ __('messages.Address') }}" name="contact_address[]" />
                                </div>
                            </div>
                            {{-- Cloned entries will be appended here --}}
                        </div>
                        
                        <div class="flex justify-between items-center mt-4">
                            <button id="addContact" type="button" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium text-sm">{{ __('messages.Add Another') }}</button>
                        <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-semibold shadow">{{ __('messages.Save New Contacts') }}</button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    {{-- SCRIPTS --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    {{-- I have removed the Bootstrap JS include, as it's no longer needed for the accordion and could conflict --}}
    @include('dashboard_layouts.scripts')


    {{-- Editable Fields Script --}}
    <script>
    @can('Update client')
    $(document).ready(function() {
        // --- Editable Client Fields ---
        $(document).on("dblclick", ".editable", function() {
            $(this).prop('readonly', false).focus();
        });

        $(document).on("blur", ".editable", function() {
            $(this).prop('readonly', true);
            var newContent = $(this).val();
            var clientId = $(this).data('id');
            var columnName = $(this).attr('name');
            
            var updatedUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/clients/updateClient';
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/clients/updateClient';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/clients/updateClient';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/clients/updateClient';
            @endif

            $.ajax({
                url: updatedUrl,
                type: 'post',
                data: {
                    content: newContent,
                    id: clientId,
                    column: columnName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload(); // Reload to confirm changes
                },
                error: function(response) {
                    console.error("Update failed:", response);
                }
            });
        });

        // --- Editable Contact Fields ---
        $(document).on("dblclick", ".editable-contact", function() {
            $(this).prop('readonly', false).focus();
        });

        $(document).on("blur", ".editable-contact", function() {
            $(this).prop('readonly', true);
            var newContent = $(this).val();
            var contactId = $(this).data('id');
            var columnName = $(this).attr('name');
            
            var updatedUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/clients/updateContact';
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/clients/updateContact';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/clients/updateContact';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/clients/updateContact';
            @endif

            $.ajax({
                url: updatedUrl,
                type: 'post',
                data: {
                    content: newContent,
                    id: contactId,
                    column: columnName,
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    // No reload here to avoid closing accordion
                    console.log("Contact updated");
                },
                error: function(response) {
                    console.error("Contact update failed:", response);
                }
            });
        });
    });
    @endcan
    </script>

    {{-- Add New Contact Script --}}
    <script>
    $(document).ready(function() {
        // Your "Add Contact" script had two identical click handlers. I've removed the broken one.
        $("#addContact").click(function(e) {
            e.preventDefault();
            var container = $("#contact_infos");
            var entryToClone = container.children(".contact-entry").first();
            var newEntry = entryToClone.clone();

            newEntry.find('input').val(''); // Clear values
            
            // Add a styled delete button
            var deleteButton = $('<button type="button" class="remove-contact w-full mt-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium text-sm">Remove</button>');
            
            newEntry.append(deleteButton);
            container.append(newEntry);
        });

        // Event delegation for removing cloned entries
        $("#contact_infos").on("click", ".remove-contact", function() {
            $(this).closest(".contact-entry").remove();
        });
    });
    </script>

    {{-- Billing Address Checkbox Script --}}
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const billingCheckbox = document.getElementById('billingSameAsClient');
        if (!billingCheckbox) return;

        const fields = {
            street: document.querySelector('input[name="billing_street"]'),
            city: document.querySelector('input[name="billing_city"]'),
            zip: document.querySelector('input[name="billing_zip"]'),
            country: document.querySelector('input[name="billing_country"]'),
        };

        const clientData = {
            street: "{{$client->address}}",
            city: "{{$client->city}}",
            zip: "{{$client->zip}}",
            country: "{{$client->country}}",
            id: "{{$client->id}}"
        };

        billingCheckbox.addEventListener('change', function() {
            if (this.checked) {
                // Set values
                fields.street.value = clientData.street;
                fields.city.value = clientData.city;
                fields.zip.value = clientData.zip;
                fields.country.value = clientData.country;

                // Make readonly and trigger updates
                Object.entries(fields).forEach(([key, element]) => {
                    element.readOnly = true;
                    // Trigger the blur event to save the data
                    $(element).trigger('blur');
                });
            } else {
                Object.values(fields).forEach(element => {
                    element.readOnly = false;
                });
            }
        });
    });
    </script>

    {{-- New Tailwind-based Accordion Script --}}
    <script>
    $(document).ready(function() {
        $('#accordion').on('click', '.accordion-trigger', function(e) {
            // Don't toggle if the delete button or its form was clicked
            if ($(e.target).closest('form').length > 0) {
                return;
            }
            
            var content = $(this).next('.accordion-content');
            var isExpanded = $(this).attr('aria-expanded') === 'true';

            // Toggle the current one
            content.toggleClass('hidden');
            $(this).attr('aria-expanded', !isExpanded);
        });
    });
    </script>

    {{-- Report Download & Filter Script (Unchanged) --}}
    <script>
    function downloadReport(e) {
        e.preventDefault();
        
        const startDate = sessionStorage.getItem('filterStartDate') || document.getElementById('start_date').value;
        const endDate = sessionStorage.getItem('filterEndDate') || document.getElementById('end_date').value;
        
        let url = '{{ Auth::user()->hasRole("Admin") ? 
            route("admin.clients.downloadInvoicesRepports", $client->id) : 
            (Auth::user()->hasRole("Employee") ? 
                route("employee.clients.downloadInvoicesRepports", $client->id) : 
                (Auth::user()->hasRole("Responsable de projet") ? 
                    route("Responsable_de_projet.clients.downloadInvoicesRepports", $client->id) : 
                    route("Responsable_gestionnaire.clients.downloadInvoicesRepports", $client->id))) }}';

        if (startDate && endDate) {
            url += `?startDate=${startDate}&endDate=${endDate}`;
        }
        window.open(url, '_blank');
    }

    document.getElementById('filterForm').addEventListener('submit', function() {
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        sessionStorage.setItem('filterStartDate', startDate);
        sessionStorage.setItem('filterEndDate', endDate);
    });

    window.addEventListener('load', function() {
        const startDate = sessionStorage.getItem('filterStartDate');
        const endDate = sessionStorage.getItem('filterEndDate');
        if (startDate) document.getElementById('start_date').value = startDate;
        if (endDate) document.getElementById('end_date').value = endDate;
    });
    </script>

</body>
</html>
