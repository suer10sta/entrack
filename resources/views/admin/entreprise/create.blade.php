<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DalyTrac - Expenses</title>
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- Use the new styling system --}}
    @vite('resources/css/app.css')
    @livewireStyles
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    {{-- <noscript>...iframe...</noscript> --}}

    {{-- Layout powered by Blade components --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto w-full">
        <x-dashboard.navbar />

        {{-- Main content area using Tailwind styling --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            <div class="max-w-7xl mx-auto">

                <div class="mb-8">
                    <h1 class="text-4xl font-bold text-gray-900 mb-2">Expenses</h1>
                    <p class="text-gray-600">Track spending and manage the company budget.</p>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
                    
                    <div class="lg:col-span-1 bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">Current Budget</h2>
                        </div>
                        <div class="p-6 space-y-4">
                            @if($latestBudget)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Cashbox (double-click to edit):</label>
                                    <input id="budget-input-{{ $latestBudget->id }}"
                                           type="number" name="monthBudget"
                                           value="{{ $latestBudget->budget }}" readonly
                                           ondblclick="enableEditing(this, {{ $latestBudget->id }})"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-50 cursor-pointer focus:bg-white focus:cursor-text focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Remaining Balance:</label>
                                    <input id="sold-input" type="number" name="sold"
                                           value="{{$latestBudget->budget_remaining}}" readonly
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100 cursor-not-allowed text-gray-500">
                                </div>
                            @else
                                <p class="text-sm text-gray-500">No budget has been defined yet.</p>
                            @endif
                        </div>
                    </div>

                    <div class="lg:col-span-2 space-y-8">
                        <div x-data="datePicker()" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Export Report</h3>
                            <div class="flex flex-col md:flex-row md:items-end gap-4">
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Start date:</label>
                                    <input id="start-date" type="date" name="startDate" x-model="startDate"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">End date:</label>
                                    <input id="end-date" type="date" name="endDate" x-model="endDate"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                                <a href="#" @click.prevent="updateHref" id="downloadLink"
                                   class="flex-shrink-0 text-center px-4 py-2 h-10 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                    Print PDF
                                </a>
                            </div>
                        </div>

                        @php
                            $addBudgetRoute = '#';
                            if (Auth::user()->hasRole('Admin')) $addBudgetRoute = route('admin.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Responsable de projet')) $addBudgetRoute = route('Responsable_de_projet.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Employee')) $addBudgetRoute = route('employee.entreprise.addBudget');
                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $addBudgetRoute = route('Responsable_gestionnaire.entreprise.addBudget');
                        @endphp
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Budget to Cashbox</h3>
                            <form action="{{ $addBudgetRoute }}" method="post" class="flex flex-col md:flex-row md:items-end gap-4">
                                @csrf
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">New budget:</label>
                                    <input type="number" name="newBudget" step="0.0001"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           placeholder="Amount to add">
                                </div>
                                <button type="submit"
                                        class="flex-shrink-0 text-center px-4 py-2 h-10 bg-gray-800 text-white rounded-lg hover:bg-gray-900 transition-colors text-sm font-medium">
                                    Add
                                </button>
                            </form>
                            @if(session('successNewBudget'))
                                <div class="mt-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                                    {{ session('successNewBudget') }}
                                </div>
                            @endif
                            @if(session('error'))
                                <div class="mt-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                                    {{ session('error') }} {{-- Corrected from "erro" to "error" --}}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                @php
                    $storeExpenseRoute = '#';
                    $cancelRoute = '#';
                    if (Auth::user()->hasRole('Admin')) {
                        $storeExpenseRoute = route('admin.Enterprise_infos.store');
                        $cancelRoute = route('admin.index');
                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                        $storeExpenseRoute = route('Responsable_de_projet.Enterprise_infos.store');
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif (Auth::user()->hasRole('Employee')) {
                        $storeExpenseRoute = route('employee.Enterprise_infos.store');
                        $cancelRoute = route('employee.index');
                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $storeExpenseRoute = route('Responsable_gestionnaire.Enterprise_infos.store');
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp
                <form class="mb-8" method="POST" action="{{ $storeExpenseRoute }}" enctype="multipart/form-data" autocomplete="off">
                    @csrf
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-xl font-semibold text-gray-900">New Expense</h2>
                        </div>
                        <div class="p-6">
                            @if(session('successNewDepense'))
                                <div class="mb-4 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg text-sm">
                                    {{ session('successNewDepense') }}
                                </div>
                            @endif
                            @if(session('errorNewDepense'))
                                <div class="mb-4 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
                                    {{ session('errorNewDepense') }}
                                </div>
                            @endif

                            <div id="billings" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Expense description:</label>
                                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" type="text" placeholder="Expense name" name="name[]" />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Prix de la dépense :</label>
                                        <input class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" type="number" name="price[]" step="0.0001" placeholder="0.00"/>
                                    </div>
                                </div>
                            </div>
                            <button type="button" id="add-billing" 
                                    class="mt-4 px-4 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors text-sm font-medium">
                                Ajouter une autre ligne
                            </button>
                        </div>
                        
                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex justify-end items-center gap-4">
                            <a href="{{ $cancelRoute }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors text-sm font-medium">
                                Annuler
                            </a>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium">
                                Submit
                            </button>
                        </div>
                    </div>
                </form>

                <div class="space-y-8">
                    @foreach($billings_budget as $billing)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                            <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                                <div>
                                    <h3 class="text-xl font-semibold text-gray-900">Budget : {{$billing->budget}}€</h3>
                                    <p class="text-sm text-gray-500">Créé le : {{$billing->created_at->format('d-m-Y')}}</p>
                                </div>
                                @php
                                    $deleteBudgetRoute = '#';
                                    if (Auth::user()->hasRole('Admin')) $deleteBudgetRoute = route('admin.billing.destroy', $billing->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet')) $deleteBudgetRoute = route('Responsable_de_projet.billing.destroy', $billing->id);
                                    elseif (Auth::user()->hasRole('Employee')) $deleteBudgetRoute = route('employee.billing.destroy', $billing->id);
                                    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteBudgetRoute = route('Responsable_gestionnaire.billing.destroy', $billing->id);
                                @endphp
                                <form method="post" action="{{ $deleteBudgetRoute }}" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-medium">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                    </button>
                                </form>
                            </div>

                            <div class="divide-y divide-gray-200">
                                @php 
                                    $entrepriseCharges = App\Models\entrepriseCharges::where('entreprise_id', $entreprise->id)->where('Billings_budget_id', $billing->id)->orderBy('created_at')->get();
                                @endphp
                                
                                @if($entrepriseCharges->isEmpty())
                                    <p class="p-6 text-sm text-gray-500 text-center">Aucune dépense pour ce budget.</p>
                                @else
                                    @foreach($entrepriseCharges as $entrepriseCharge)
                                        <div class="p-6 space-y-4 md:space-y-0 md:grid md:grid-cols-12 md:gap-4 md:items-center">
                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Objet</label>
                                                <input id="charge-name-{{ $entrepriseCharge->id }}" type="text" name="chargename" value="{{ $entrepriseCharge->charge_name }}" readonly
                                                       class="editable w-full px-3 py-2 border border-gray-300 rounded-lg text-sm
                                                              {{ $entrepriseCharge->validated != 1 ? 'bg-gray-50 cursor-pointer focus:bg-white focus:cursor-text' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}">
                                            </div>
                                            <div class="md:col-span-2">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Prix</label>
                                                <input id="price-{{ $entrepriseCharge->id }}" type="text" name="price" value="{{ $entrepriseCharge->price }}€" readonly
                                                       class="editable w-full px-3 py-2 border border-gray-300 rounded-lg text-sm
                                                              {{ $entrepriseCharge->validated != 1 ? 'bg-gray-50 cursor-pointer focus:bg-white focus:cursor-text' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}">
                                            </div>
                                            <div class="md:col-span-3">
                                                <label class="block text-xs font-medium text-gray-500 mb-1">Date</label>
                                                <input id="created_at-{{ $entrepriseCharge->id }}" type="text" name="created_at" value="{{ $entrepriseCharge->created_at->format('d-m-Y') }}" readonly
                                                       class="editable w-full px-3 py-2 border border-gray-300 rounded-lg text-sm
                                                              {{ $entrepriseCharge->validated != 1 ? 'bg-gray-50 cursor-pointer focus:bg-white focus:cursor-text' : 'bg-gray-100 text-gray-500 cursor-not-allowed' }}">
                                            </div>
                                            <div class="md:col-span-4 flex items-center justify-end gap-2">
                                                @if($billing->sold_out == 0)
                                                    @if($entrepriseCharge->validated != 1)
                                                        @php
                                                            $validateRoute = '#';
                                                            if (Auth::user()->hasRole('Admin')) $validateRoute = route('admin.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $validateRoute = route('Responsable_de_projet.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Employee')) $validateRoute = route('employee.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $validateRoute = route('Responsable_gestionnaire.entreprise.validateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                        @endphp
                                                        <form method="post" action="{{ $validateRoute }}">
                                                            @csrf
                                                            <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-xs font-medium">Valider</button>
                                                        </form>
                                                        @php
                                                            $deleteExpenseRoute = '#';
                                                            if (Auth::user()->hasRole('Admin')) $deleteExpenseRoute = route('admin.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $deleteExpenseRoute = route('Responsable_de_projet.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                            elseif (Auth::user()->hasRole('Employee')) $deleteExpenseRoute = route('employee.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $deleteExpenseRoute = route('Responsable_gestionnaire.Enterprise_infos.destroy', $entrepriseCharge->id);
                                                        @endphp
                                                        <form method="post" action="{{ $deleteExpenseRoute }}" onsubmit="return confirm('Are you sure?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-medium">Delete</button>
                                                        </form>
                                                    @else
                                                        @php
                                                            $unvalidateRoute = '#';
                                                            if (Auth::user()->hasRole('Admin')) $unvalidateRoute = route('admin.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet')) $unvalidateRoute = route('Responsable_de_projet.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Employee')) $unvalidateRoute = route('employee.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                            elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $unvalidateRoute = route('Responsable_gestionnaire.entreprise.unvalidateBilling', ['chargeid' => $entrepriseCharge->id, 'budget_id' => $billing->id]);
                                                        @endphp
                                                        <form method="post" action="{{ $unvalidateRoute }}">
                                                            @csrf
                                                            <button type="submit" class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition-colors text-xs font-medium">Annuler validation</button>
                                                        </form>
                                                    @endif
                                                @else
                                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium
                                                                {{ $entrepriseCharge->validated == 0 ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                                                        {{ $entrepriseCharge->validated == 0 ? 'Non Validé' : 'Validé' }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div> </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    {{-- CONSERVATION DES SCRIPTS ORIGINAUX --}}
    @include('dashboard_layouts.scripts') 
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    @livewireScripts 

    <script>
        // Script pour désactiver l'autocomplete
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });

        // Script pour ajouter dynamiquement des champs de dépense
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('add-billing').addEventListener('click', function() {
                var billings = document.getElementById('billings');
                var newBilling = billings.firstElementChild.cloneNode(true);
                
                // Réinitialiser les valeurs des nouveaux champs
                newBilling.querySelector('input[name="name[]"]').value = '';
                newBilling.querySelector('input[name="price[]"]').value = '';

                // Ajouter un bouton de suppression au nouveau champ
                if (billings.children.length >= 0) { // Logique originale autorisait la suppression du premier
                    var deleteButton = document.createElement('button');
                    deleteButton.textContent = 'Delete';
                    deleteButton.type = 'button';
                    // Ajout des classes Tailwind pour le bouton de suppression
                    deleteButton.className = 'mt-2 px-3 py-1 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 transition-colors text-xs font-medium remove-billing';
                    deleteButton.addEventListener('click', function() {
                        billings.removeChild(newBilling);
                    });
                    newBilling.appendChild(deleteButton);
                }

                billings.appendChild(newBilling);
            });
        });

        // Script pour la modification des champs de DÉPENSE (editable)
        document.addEventListener('DOMContentLoaded', function() {
            const editableInputs = document.querySelectorAll('.editable');

            editableInputs.forEach(input => {
                input.addEventListener('dblclick', function() {
                    if (this.readOnly) {
                        this.readOnly = false;
                        this.focus();
                        // Ajout des classes Tailwind pour le mode édition
                        this.classList.remove('bg-gray-50', 'cursor-pointer');
                        this.classList.add('bg-white', 'cursor-text');
                    }
                });

                input.addEventListener('blur', function() {
                    this.readOnly = true;
                    // Ajout des classes Tailwind pour le mode lecture
                    this.classList.add('bg-gray-50', 'cursor-pointer');
                    this.classList.remove('bg-white', 'cursor-text');
                    updateField(this);
                });

                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        this.blur();
                    }
                });
            });

            function updateField(input) {
                const id = input.id.split('-')[2]; // l'id est en 3ème partie (ex: charge-name-123)
                const column = input.id.split('-')[0]; // ex: 'charge-name' ou 'price' ou 'created_at'
                const content = input.value;
                let fetchUrl = '';
                
                @if(Auth::user()->hasRole('Admin'))
                    fetchUrl = '{{ route("admin.Enterprise_Billings.updateBillings") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    fetchUrl = '{{ route("Responsable_de_projet.Enterprise_Billings.updateBillings") }}';
                @elseif(Auth::user()->hasRole('Employee'))
                    fetchUrl = '{{ route("employee.Enterprise_Billings.updateBillings") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    fetchUrl = '{{ route("Responsable_gestionnaire.Enterprise_Billings.updateBillings") }}';
                @endif
                
                if (!fetchUrl) return; // Ne fait rien si aucune route n'est définie

                fetch(fetchUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        id: id,
                        column: column, // 'charge-name', 'price', 'created_at'
                        content: content
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('Update successful');
                        location.reload();
                    } else {
                        console.error('Update failed');
                        location.reload(); // La logique originale rechargeait aussi en cas d'erreur
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    location.reload(); // La logique originale rechargeait aussi en cas d'erreur
                });
            }
        });

        // Script pour le datePicker (Alpine.js)
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
                    @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        baseUrl = "{{route('Responsable_gestionnaire.entrepriseBillings.downloadRepports')}}";
                    @endif
                    
                    let urlWithParams = baseUrl;
                    if (this.startDate && this.endDate) {
                        urlWithParams += `?startDate=${this.startDate}&endDate=${this.endDate}`;
                        window.location.href = urlWithParams; // Redirige seulement si les dates sont valides
                    } else {
                        alert('Veuillez sélectionner une date de début et une date de fin.');
                    }
                }
            }
        }

        // Script pour la modification du BUDGET (enableEditing)
        function enableEditing(input, budgetId) {
            input.removeAttribute('readonly');
            input.focus();
            // Ajout des classes Tailwind pour le mode édition
            input.classList.remove('bg-gray-50', 'cursor-pointer');
            input.classList.add('bg-white', 'cursor-text');

            const onBlur = function() {
                updateBudget(input, budgetId);
                input.removeEventListener('blur', onBlur);
                input.removeEventListener('keypress', onKeypress);
            };

            const onKeypress = function(event) {
                if (event.key === 'Enter') {
                    updateBudget(input, budgetId);
                    input.removeEventListener('blur', onBlur);
                    input.removeEventListener('keypress', onKeypress);
                }
            };

            input.addEventListener('blur', onBlur);
            input.addEventListener('keypress', onKeypress);
        }

        function updateBudget(input, budgetId) {
            input.setAttribute('readonly', true);
            // Ajout des classes Tailwind pour le mode lecture
            input.classList.add('bg-gray-50', 'cursor-pointer');
            input.classList.remove('bg-white', 'cursor-text');
            
            const budget = input.value;
            let fetchUrl = '';

            @if(Auth::user()->hasRole('Admin'))
                fetchUrl = `/admin/budget/update/${budgetId}`;
            @elseif(Auth::user()->hasRole('Responsable de projet'))
                fetchUrl = `/Responsable_de_projet/budget/update/${budgetId}`;
            @elseif(Auth::user()->hasRole('Employee'))
                fetchUrl = `/employee/budget/update/${budgetId}`;
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                fetchUrl = `/Responsable_gestionnaire/budget/update/${budgetId}`;
            @endif

            if (!fetchUrl) return;

            fetch(fetchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ budget: budget })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Please delete all charges before updating the budget');
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the budget. Please try again.');
                location.reload();
            });
        }
    </script>
</body>
</html>
