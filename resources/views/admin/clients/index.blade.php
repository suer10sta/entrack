<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - DalyTrac</title> 
    @vite('resources/css/app.css')
    @livewireStyles
    
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#eaebef] p-8">

            {{-- Container for the table --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header Section --}}
                <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <h6 class="text-lg font-semibold text-gray-900">Clients Table</h6>

                    <div class="flex flex-wrap gap-2">
                        {{-- Add Client Button --}}
                        @can('Add client')
                            @php
                                $createRoute = '#'; // Default fallback
                                if (Auth::user()->hasRole('Responsable de projet')) $createRoute = route('Responsable_de_projet.clients.create');
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $createRoute = route('Responsable_gestionnaire.clients.create');
                                elseif (Auth::user()->hasRole('Admin')) $createRoute = route('admin.clients.create');
                            @endphp
                            <a href="{{ $createRoute }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                                Add Client
                            </a>
                        @endcan

                        {{-- Report Button --}}
                        @php
                            $reportRoute = '#'; // Default fallback
                            if (Auth::user()->hasRole('Responsable de projet')) $reportRoute = route('Responsable_de_projet.Clientrepports.downloadRepports');
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $reportRoute = route('Responsable_gestionnaire.Clientrepports.downloadRepports');
                            elseif (Auth::user()->hasRole('Admin')) $reportRoute = route('admin.Clientrepports.downloadRepports');
                        @endphp
                        <a href="{{ $reportRoute }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg flex items-center gap-1.5">
                           <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                           Client Reports PDF
                        </a>
                    </div>
                </div>

                {{-- Session Message Alert --}}
                @if(session('message'))
                    <div class="m-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none'" class="absolute top-0 bottom-0 right-0 px-4 py-3 text-green-700 hover:text-green-900 font-bold text-xl" aria-label="Close">
                           &times; {{-- Better looking 'X' --}}
                        </button>
                    </div>
                @endif

                {{-- Table Wrapper --}}
                <div class="p-6">
    <div class="overflow-x-auto">
        <table id="example" class="w-full text-left border-collapse table-fixed">
            <thead class="border-b border-gray-200 bg-gray-50">
                <tr>
                    {{-- Optimized column widths to fit all columns on screen --}}
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">N°</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;">Name</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 14%;">Email</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;">Phone</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 11%;">Billing Address</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;">VAT</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 6%;">Due Date</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 12%;">Address</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 8%;">City</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 5%;">Zip</th>
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-left" style="width: 7%;">Country</th>
                    @can('Delete client')
                    <th class="px-3 py-3 text-sm font-bold text-gray-600 uppercase tracking-wider text-center" style="width: 8%;">Action</th>
                    @endcan
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($clients as $client)
                <tr class="hover:bg-gray-50">
                    
                    {{-- ID --}}
                    <td class="px-3 py-5 text-sm text-gray-700 font-medium text-center">
                        {{$client->client_number}}
                    </td>
                    
                    {{-- Name --}}
                    <td class="px-3 py-5 text-sm text-gray-700 font-medium whitespace-normal break-words">
                         @php
                            // Prefer a concrete route per role; if none, fall back to admin route
                            $roleRouteMap = [
                                'Responsable de projet' => 'Responsable_de_projet.clients.show',
                                'Responsable de projet et gestionnaire' => 'Responsable_gestionnaire.clients.show',
                                'Admin' => 'admin.clients.show',
                            ];

                            $showRoute = null;
                            foreach ($roleRouteMap as $role => $routeName) {
                                if (Auth::user()->hasRole($role) && Route::has($routeName)) {
                                    $showRoute = route($routeName, $client->id);
                                    break;
                                }
                            }

                            if (!$showRoute && Route::has('admin.clients.show')) {
                                $showRoute = route('admin.clients.show', $client->id);
                            }
                         @endphp
                         @if($showRoute)
                            <a href="{{ $showRoute }}" class="text-blue-600 hover:underline">
                                {{$client->name}}
                            </a>
                         @else
                            {{$client->name}}
                         @endif
                    </td>

                    {{-- Email --}}
                    <td class="px-3 py-5 text-sm text-gray-700 whitespace-normal break-all">
                        {{$client->email}}
                    </td>

                    {{-- Phone --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 whitespace-nowrap">
                        {{$client->phone}}
                    </td>

                    {{-- Billing Address --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal break-words">
                        {{$client->Billing_address}}
                    </td>

                    {{-- VAT --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 text-center whitespace-nowrap">
                        {{$client->TVA}}
                    </td>

                    {{-- Due Date --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 text-center whitespace-nowrap">
                        {{$client->due_date}}
                    </td>

                    {{-- Address --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal break-words">
                        {{$client->address}}
                    </td>

                    {{-- City --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal">
                        {{$client->city}}
                    </td>

                    {{-- Zip --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 text-center whitespace-nowrap">
                        {{$client->zip}}
                    </td>

                    {{-- Country --}}
                    <td class="px-2 py-2.5 text-sm text-gray-700 whitespace-normal">
                        {{$client->country}}
                    </td>

                    @can('Delete client')
                    <td class="px-2 py-2.5 text-sm text-gray-700 text-center">
                        @php
                            $client_has_invoice = \App\Models\invoices_history::where('client_id', $client->id)->exists();
                        @endphp
                        @if($client_has_invoice)
                           <div class="bg-red-50 border border-red-200 text-red-600 px-2 py-1 rounded text-[10px] font-medium leading-tight">
                                Cannot delete:<br>Has invoices
                           </div>
                        @else
                            @php
                                $deleteRoute = '#';
                                if (Auth::user()->hasRole('Responsable de projet')) $deleteRoute = route('Responsable_de_projet.clients.destroy', $client->id);
                                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteRoute = route('Responsable_gestionnaire.clients.destroy', $client->id);
                                elseif (Auth::user()->hasRole('Admin')) $deleteRoute = route('admin.clients.destroy', $client->id);
                            @endphp
                            <form action="{{ $deleteRoute }}" method="post" onsubmit="return confirm('Are you sure?');" class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete Client"
                                        class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-red-600 hover:bg-red-700">
                                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        @endif
                    </td>
                    @endcan
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-6 py-4 text-center text-sm text-gray-500">
                         No clients found.
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
    {{-- Include dashboard_layouts.scripts if needed --}}
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Keep jQuery and inline editing script --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
    $(document).ready(function(){
        $(".editable").dblclick(function(){
            if ($(this).find('input').length > 0) {
                return;
            }

            var originalContent = $(this).text().trim();
            var cellWidth = $(this).outerWidth();

            $(this).html("<input type='text' value='" + originalContent + "' class='w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm p-1' style='min-width: " + cellWidth + "px;' />");

            var input = $(this).children('input').first();
            input.focus();
            input[0].select();

            input.blur(function(){
                var newContent = $(this).val();
                var $parentCell = $(this).parent();
                var clientId = $parentCell.data('id');
                var columnName = $parentCell.data('column');

                $parentCell.text(originalContent);

                $.ajax({
                    url: '/admin/clients/updateClient', 
                    type: 'POST',
                    data: {
                        content: newContent,
                        id: clientId,
                        column: columnName,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $parentCell.text(newContent);
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        $parentCell.text(originalContent);
                        alert('Error updating client: ' + errorThrown);
                        console.error("AJAX Error:", textStatus, errorThrown, jqXHR.responseText);
                    }
                });
            });

            input.keypress(function(e){
                if(e.which == 13){ 
                    $(this).blur();
                    return false;
                }
            });
        });
    });
    </script>

</body>
</html>
