<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Dashboard - Dépense</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}
        <main class="flex-1 p-8 bg-gradient-to-br from-slate-50 to-slate-100">
            <div class="max-w-6xl mx-auto">

                {{-- Header --}}
                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">Dépense</h1>
                    <p class="text-slate-600">Gérez vos budgets et dépenses</p>
                </div>

                {{-- Date Range and PDF --}}
                <div x-data="datePicker()" class="mb-6 p-6 bg-white shadow rounded-2xl border border-slate-200">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">Start Date</label>
                            <input type="date" x-model="startDate"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-2">End Date</label>
                            <input type="date" x-model="endDate"
                                   class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <button @click="updateHref()"
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg flex items-center justify-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M12 4v16m8-8H4" />
                            </svg>
                            Print PDF
                        </button>
                    </div>
                </div>

                {{-- Budgets List --}}
                <div class="space-y-4">
                    @foreach($billings_budget as $billing)
                        @php 
                            $isValidated = $billing->sold_out == 1;
                            $bgColor = $isValidated ? 'bg-green-100' : 'bg-slate-50';
                            $textColor = $isValidated ? 'text-green-800' : 'text-slate-700';

                            $entrepriseCharges = App\Models\entrepriseCharges::where('entreprise_id', $entreprise->id)
                                ->where('Billings_budget_id', $billing->id)->orderBy('created_at')->get();

                            $remainingBudget = $billing->budget_remaining;
                        @endphp

                        <div class="flex flex-col p-4 rounded-lg border border-slate-200 {{ $bgColor }}">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <span class="font-medium {{ $textColor }}">Budget : {{ $billing->budget }}€</span>
                                    <span class="ml-4 text-sm font-semibold {{ $remainingBudget > 0 ? 'text-red-600' : 'text-green-700' }}">
                                        Remaining : {{ $remainingBudget }}€
                                    </span>
                                </div>
                                @if($isValidated)
                                    <span class="text-sm font-semibold text-green-700 px-2 py-1 bg-green-200 rounded-full">Validé</span>
                                @endif
                            </div>

                            {{-- Charges --}}
                            @foreach($entrepriseCharges as $charge)
                                <div class="flex items-center justify-between p-3 rounded-lg border border-slate-200 {{ $isValidated ? 'bg-green-50' : 'bg-white' }} mt-1">
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $charge->charge_name }}</p>
                                        <p class="text-sm text-slate-600">{{ $charge->price }}€</p>
                                    </div>
                                    <div class="flex gap-2">
                                        @if(!$isValidated)
                                            <form method="POST" 
                                                @if(Auth::user()->hasRole('Admin')) action="{{route('admin.Enterprise_infos.destroy', $charge->id)}}" 
                                                @elseif(Auth::user()->hasRole('Responsable de projet')) action="{{route('Responsable_de_projet.Enterprise_infos.destroy', $charge->id)}}" 
                                                @elseif(Auth::user()->hasRole('Employee')) action="{{route('employee.Enterprise_infos.destroy', $charge->id)}}" 
                                                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) action="{{route('Responsable_gestionnaire.Enterprise_infos.destroy', $charge->id)}}" 
                                                @endif
                                                onsubmit="return confirm('Are you sure?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm bg-gradient-danger">Supprimer</button>
                                            </form>

                                            <form method="POST"
                                                @if(Auth::user()->hasRole('Admin')) action="{{ route('admin.entreprise.validateBilling', ['chargeid' => $charge->id, 'budget_id' => $billing->id]) }}" 
                                                @elseif(Auth::user()->hasRole('Responsable de projet')) action="{{ route('Responsable_de_projet.entreprise.validateBilling', ['chargeid' => $charge->id, 'budget_id' => $billing->id]) }}" 
                                                @elseif(Auth::user()->hasRole('Employee')) action="{{ route('employee.entreprise.validateBilling', ['chargeid' => $charge->id, 'budget_id' => $billing->id]) }}" 
                                                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) action="{{ route('Responsable_gestionnaire.entreprise.validateBilling', ['chargeid' => $charge->id, 'budget_id' => $billing->id]) }}" 
                                                @endif>
                                                @csrf
                                                <button type="submit" class="btn btn-sm bg-gradient-success">Valider</button>
                                            </form>
                                        @else
                                            <span class="text-green-700 font-semibold">Validé</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                {{-- Nouvelle dépense --}}
                <div class="mt-8 p-6 bg-white shadow rounded-2xl border border-slate-200">
                    <h2 class="text-xl font-bold text-slate-900 mb-6">Nouvelle dépense</h2>

                    <form method="POST"
                        @if(Auth::user()->hasRole('Admin')) action="{{route('admin.Enterprise_infos.store')}}" 
                        @elseif(Auth::user()->hasRole('Responsable de projet')) action="{{route('Responsable_de_projet.Enterprise_infos.store')}}" 
                        @elseif(Auth::user()->hasRole('Employee')) action="{{route('employee.Enterprise_infos.store')}}" 
                        @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) action="{{route('Responsable_gestionnaire.Enterprise_infos.store')}}" 
                        @endif
                        enctype="multipart/form-data">
                        @csrf

                        <div id="billings" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Objet de la dépense</label>
                                <input type="text" name="name[]" placeholder="Billing Name"
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-2">Prix de la dépense</label>
                                <input type="number" name="price[]" step="0.0001"
                                       class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                        </div>

                        <button type="button" id="add-billing"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg mb-4">Ajouter</button>

                        <div class="flex gap-3">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">Submit</button>
                            <a href="{{ route('dashboard') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-900 px-6 py-2 rounded-lg">Cancel</a>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

    @vite('resources/js/app.js')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <script>

function datePicker() {
            return {
                startDate: '',
                endDate: '',
                updateHref() {
                    let baseUrl = '';
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        baseUrl = "{{route('Responsable_de_projet.entrepriseBillings.downloadRepports')}}";
                    @elseif(Auth::user()->hasRole('Admin'))
                        baseUrl = "{{route('admin.entrepriseBillings.downloadRepports')}}";
                    @elseif(Auth::user()->hasRole('Employee'))
                        baseUrl = "{{route('employee.entrepriseBillings.downloadRepports')}}";
                    @endif
                    if(this.startDate && this.endDate) baseUrl += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                    window.location.href = baseUrl;
                }
            }}
        // Add more billing inputs
        document.getElementById('add-billing').addEventListener('click', function() {
            const container = document.getElementById('billings');
            const newBilling = document.createElement('div');
            newBilling.classList.add('grid','grid-cols-1','md:grid-cols-2','gap-4','mb-4');
            newBilling.innerHTML = `
                <div>
                    <input type="text" name="name[]" placeholder="Billing Name" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <input type="number" name="price[]" step="0.0001" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            `;
            container.appendChild(newBilling);
        });
    </script>
</body>
</html>
