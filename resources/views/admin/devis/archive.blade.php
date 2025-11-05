<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-M">
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
        <main class="flex-1 p-4 md:p-6 lg:p-8">
            
            {{-- On définit le préfixe de route une seule fois --}}
            @php
                $userRole = Auth::user()->getRoleNames()->first();
                $prefix = 'admin'; // Préfixe par défaut

                switch ($userRole) {
                    case 'Responsable de projet':
                        $prefix = 'Responsable_de_projet';
                        break;
                    case 'Employee':
                        $prefix = 'employee';
                        break;
                    case 'Responsable de projet et gestionnaire':
                        $prefix = 'Responsable_gestionnaire';
                        break;
                    case 'Admin':
                    default:
                        $prefix = 'admin';
                        break;
                }
            @endphp

            <div class="w-full">
                <div class="bg-white shadow-lg rounded-xl overflow-hidden">
                    
                    <div class="px-6 py-5 border-b border-gray-200">
                        <div class="flex justify-between items-center flex-wrap gap-3">
                            <h6 class="text-xl font-semibold text-gray-800">Archived Devis Table</h6>
                            <div>
                                @hasanyrole(['Admin', 'Responsable de projet', 'Employee', 'Responsable de projet et gestionnaire'])
                                    <a type="button" href="{{ url()->previous() }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50">
                                        Return Back
                                    </a>
                                @endhasanyrole
                            </div>
                        </div>
                    </div>

                    @php
                        $Total_Accpeted = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 1)->where('archived', 1)->sum('budget');
                        $Total_Declined = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 2)->where('archived', 1)->sum('budget');
                        $Total_Pending = App\Models\devis_history::where('entreprise_id', Auth::user()->entreprise_id)->where('acceptanceStatus', 0)->where('archived', 1)->sum('budget');
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 p-4 lg:p-6 bg-gray-50">
                        <div class="bg-green-50 p-4 rounded-lg shadow-sm">
                            <span class="text-sm font-medium text-green-700">Total Accepted</span>
                            <p class="text-2xl font-bold text-green-900">€{{ number_format($Total_Accpeted, 2, ',', ' ') }}</p>
                        </div>
                        <div class="bg-red-50 p-4 rounded-lg shadow-sm">
                            <span class="text-sm font-medium text-red-700">Total Declined</span>
                            <p class="text-2xl font-bold text-red-900">€{{ number_format($Total_Declined, 2, ',', ' ') }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg shadow-sm">
                            <span class="text-sm font-medium text-yellow-700">Total Pending</span>
                            <p class="text-2xl font-bold text-yellow-900">€{{ number_format($Total_Pending, 2, ',', ' ') }}</p>
                        </div>
                    </div>

                    <div class="p-4 lg:p-6 grid grid-cols-1 md:grid-cols-3 gap-4 border-t border-gray-200">
                        <div>
                            <label for="clientFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Client:</label>
                            <select id="clientFilter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Clients</option>
                                @foreach($clients as $client)
                                <option value="{{$client->name}}">{{$client->name}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="acceptanceFilter" class="block text-sm font-medium text-gray-700 mb-1">Filter by Acceptance:</label>
                            <select id="acceptanceFilter" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">All Devis</option>
                                <option value="Accepted">Accepted</option>
                                <option value="Declined">Declined</option>
                                <option value="Pending">Pending</option>
                            </select>
                        </div>
                    </div>
                    
                    @if(session('message'))
                    <div class="m-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" class="absolute top-0 bottom-0 right-0 px-4 py-3" data-bs-dismiss="alert" aria-label="Close">X</button>
                    </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Devis N</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Project Name</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Client Name / Emails</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Budget</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Unbilled Budget</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Created at</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Acceptance</th>
                                    <th class="px-6 py-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($devis as $d)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        <a href="{{ route($prefix . '.devis.downloadDevis', $d->devis_number) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                                            {{$entreprise->devis_prefix}}-{{date('ym')}}{{$d->devis_number}}
                                        </a>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                        @php $project = \App\Models\Project::where('id', $d->project_id)->first(); @endphp
                                        
                                        @if($d->acceptanceStatus == 1 || $d->Existing_project_name != null)
                                            <a href="{{ route($prefix . '.projects.show', $project->id) }}" class="text-indigo-600 hover:text-indigo-900">
                                                {{$project->name}}
                                            </a>
                                        @else
                                            {{$project->name}}
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                        @php
                                            $client = \App\Models\Client::where('id', $d->client_id)->first();
                                            $emails = explode(',', $d->emails);
                                        @endphp
                                        @if (empty($client))
                                            @foreach ($emails as $email)
                                            {{ $email }}<br />
                                            @endforeach
                                        @else
                                            {{$client->name}}
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium text-green-600">
                                        €{{ number_format($d->budget, 2, ',', ' ') }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-700">
                                        @php
                                            $devisInvoicesTotal = \App\Models\invoices_history::where('devis_id', $d->id)->sum('total_htva');
                                            $unbilledBudget = ($d->budget ?? 0) - ($devisInvoicesTotal ?? 0);
                                        @endphp
                                        €{{ number_format(max($unbilledBudget, 0), 2, ',', ' ') }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm text-gray-500">
                                        {{$d->created_at}}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm">
                                        @if($d->acceptanceStatus == 1)
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800"> Accepted </span>
                                        @elseif($d->acceptanceStatus == 2 )
                                            <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-medium text-red-800"> Declined </span>
                                        @else
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800"> Pending </span>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center items-center space-x-2">
                                            <form action="{{ route($prefix . '.devis.acceptDevis', $d->id) }}" method="post" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-3 py-1 text-xs font-medium text-white shadow-sm hover:bg-green-700">
                                                    Accepted
                                                </button>
                                            </form>
                                            <form action="{{ route($prefix . '.devis.declineDevis', $d->id) }}" method="post" onsubmit="return confirm('Are you sure?');">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-1 text-xs font-medium text-white shadow-sm hover:bg-red-700">
                                                    Declined
                                                </button>
                                            </form>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div class="flex justify-center items-center flex-wrap gap-2">
                                            <a href="{{ route($prefix . '.devis.downloadDevis', $d->devis_number) }}" class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-3 py-1 text-xs font-medium text-white shadow-sm hover:bg-blue-700">
                                                download
                                            </a>
                                            
                                            @can('Send devis')
                                            <form action="{{ route($prefix . '.devis.sendDevis') }}" method="post" class="inline">
                                                @csrf
                                                <input type="hidden" name="devis_id" value="{{ $d->id }}">
                                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-green-600 px-3 py-1 text-xs font-medium text-white shadow-sm hover:bg-green-700">Send</button>
                                            </form>
                                            @endcan

                                            @can('Delete devis')
                                            <form action="{{ route($prefix . '.devis.destroy', $d->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-transparent bg-red-600 px-3 py-1 text-xs font-medium text-white shadow-sm hover:bg-red-700">Delete</button>
                                            </form>
                                            @endcan

                                            <form action="{{ route($prefix . '.devis.unarchive', $d->id) }}" method="post" onsubmit="return confirm('Are you sure?');" class="inline">
                                                @csrf
                                                <button type="submit" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-3 py-1 text-xs font-medium text-gray-700 shadow-sm hover:bg-gray-50">Unarchive</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </main>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>
    <script>
    // ... (Votre script d'export XLSX, il semble correct) ...
    </script>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const clientFilter = document.getElementById('clientFilter');
        const acceptanceFilter = document.getElementById('acceptanceFilter');

        clientFilter.addEventListener('change', filterTable);
        acceptanceFilter.addEventListener('change', filterTable);

        function filterTable() {
            const clientValue = clientFilter.value.toLowerCase();
            const acceptanceValue = acceptanceFilter.value.toLowerCase();
            
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const clientName = row.querySelector('td:nth-child(3)').textContent.trim().toLowerCase();
                
                // CORRECTION: Cible la 7ème colonne (Status)
                const acceptanceStatus = row.querySelector('td:nth-child(7)').textContent.trim().toLowerCase();

                const clientMatch = (clientValue === '' || clientName.includes(clientValue));
                const acceptanceMatch = (acceptanceValue === '' || acceptanceStatus.includes(acceptanceValue));

                if (clientMatch && acceptanceMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    });
    </script>

    <script src="//unpkg.com/alpinejs" defer></script>
    <script>
        function datePicker() {
            return {
                startDate: '',
                endDate: '',
                updateHref() {
                    // Script JS CORRIGÉ: Utilise la variable $prefix de Blade
                    const baseUrl = "{{ route($prefix . '.Devissrepports.downloadRepports') }}";
                    
                    let urlWithParams = baseUrl;
                    if (this.startDate && this.endDate) {
                        urlWithParams += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                    }
                    window.location.href = urlWithParams; // Redirect
                }
            }
        }
    </script>
</body>
</html>