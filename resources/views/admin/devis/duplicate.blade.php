<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dupliquer Devis - BizManager</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Styles personnalisés de votre fichier --}}
    <style>
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
        select:invalid {
            color: #9ca3af;
        }
        option {
            color: #111827;
        }
        /* Requis pour votre menu déroulant client Bootstrap JS */
        .dropdown-menu {
            display: none; 
            position: absolute;
            z-index: 1000;
        }
        .dropdown-menu.show {
            display: block; 
        }
        #clientInputs {
            display: none;
        }
    </style>
</head>

<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">

    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Conteneur principal (Navbar + Contenu) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Zone de contenu principal --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">

            <div class="w-full max-w-4xl mx-auto">

                {{-- Message de session (style Tailwind) --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Succès !</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
                </div>
                @endif
                
                {{-- Déterminer les routes --}}
                @php
                    $saveRoute = '';
                    $cancelRoute = '';
                    if(Auth::user()->hasRole('Responsable de projet')) {
                        $saveRoute = route('Responsable_de_projet.devis.saveDuplicateDevis');
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif(Auth::user()->hasRole('Admin')) {
                        $saveRoute = route('admin.devis.saveDuplicateDevis');
                        $cancelRoute = route('admin.index');
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $saveRoute = route('employee.devis.saveDuplicateDevis');
                        $cancelRoute = route('employee.index');
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $saveRoute = route('Responsable_gestionnaire.devis.saveDuplicateDevis');
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp

                {{-- Carte principale (style Tailwind) --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    
                    {{-- En-tête de la carte --}}
                    <div class="p-6 border-b border-gray-200">
                        <h6 class="text-lg font-semibold text-gray-900">Dupliquer un Devis</h6>
                        <p class="text-sm text-gray-600 mt-1">Dupliquer un devis existant</p>
                    </div>

                    {{-- Corps de la carte --}}
                    <div class="p-6">
                        
                        <form id="devisForm" method="POST" action="{{ $saveRoute }}" autocomplete="off">
                            @csrf

                            {{-- Ligne Type de Projet (Grille Tailwind) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="projectType" class="block text-sm font-medium text-gray-700 mb-1">Sélectionner le type de projet <span class="text-red-600">*</span></label>
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom" id="projectType" name="project_type" onchange="toggleProjectInputs()" required>
                                        <option value="">Sélectionner</option>
                                        <option value="new">Nouveau Projet</option>
                                        <option value="existing" selected>Projet Existant</option> {{-- 'selected' par défaut pour la duplication --}}
                                    </select>
                                </div>
                                <div>
                                    <label for="projectName" class="block text-sm font-medium text-gray-700 mb-1">Nom du nouveau projet <span class="text-red-600">*</span></label>
                                    <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-100" id="projectName" name="Project_name" value="{{ $devis->Project_name ?? '' }}" disabled required />
                                </div>
                                <div>
                                    <label for="existingProjectId" class="block text-sm font-medium text-gray-700 mb-1">Nom du projet existant <span class="text-red-600">*</span></label>
                                    {{-- NOTE : 'existingProjectName' a été renommé en 'existingProjectId' pour correspondre à votre 'name' --}}
                                    <select class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom" id="existingProjectId" name="Existing_project_id" required onchange="populateProjectDetails()">
                                        <option value="">Sélectionner un projet</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" 
                                                    {{ $project->id == $devis->project_id ? 'selected' : '' }}
                                                    data-tva="{{ $project->tva }}" 
                                                    data-cost-per-hour="{{ $project->cost_per_hour }}">
                                                {{ $project->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Ligne Détails Projet (Grille Tailwind) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                                <div>
                                    <label for="projectBudget" class="block text-sm font-medium text-gray-700 mb-1">Budget Total</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 bg-gray-100" id="projectBudget" name='budget' readonly/>
                                </div>
                                <div>
                                    <label for="costPerHour" class="block text-sm font-medium text-gray-700 mb-1">Coût par heure</label>
                                    <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="costPerHour" name='cost_per_hour' value="{{$devis->cost_per_hour}}"/>
                                </div>
                                <div>
                                    <label for="projectTva" class="block text-sm font-medium text-gray-700 mb-1">TVA Projet</label>
                                    <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectTva" name='project_tva' value="{{$devis->tva}}" />
                                </div>
                            </div>

                            {{-- Description --}}
                            <div class="mt-6">
                                <label for="projectDescription" class="block text-sm font-medium text-gray-700 mb-1">Description du Devis <span class="text-red-600">*</span></label>
                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="projectDescription" rows="5" name="description" required>{{$devis->description}}</textarea>
                            </div>
                            
                            {{-- Entrées Dynamiques --}}
                            <div class="mt-6">
                                <label class="block text-base font-semibold text-gray-800 mb-2">Détails du Devis</label>
                                <div id="dynamicInput" class="space-y-4">
                                    @forelse($devis_details as $detail)
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border border-gray-200 rounded-lg">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Détails <span class="text-red-600">*</span></label>
                                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3" required>{{ $detail->task_details }}</textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Budget <span class="text-red-600">*</span></label>
                                                <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" value="{{ $detail->task_budget }}" required />
                                            </div>
                                        </div>
                                    @empty
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border border-gray-200 rounded-lg">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Détails <span class="text-red-600">*</span></label>
                                                <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3" required></textarea>
                                            </div>
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Budget <span class="text-red-600">*</span></label>
                                                <input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" required />
                                            </div>
                                        </div>
                                    @endforelse
                                </div>
                                {{-- Boutons Ajouter/Supprimer (style Tailwind) --}}
                                <div class="flex space-x-2 mt-4">
                                    <button type="button" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm" onclick="addInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" /></svg>
                                        Ajouter
                                    </button>
                                    <button type="button" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium px-4 py-2 rounded-lg text-sm" onclick="removeInput('dynamicInput');">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" /></svg>
                                        Supprimer
                                    </button>
                                </div>
                            </div>

                            {{-- Séparateur --}}
                            <hr class="border-gray-200 my-6" />

                            {{-- Informations Client --}}
                            <h5 class="text-lg font-semibold text-gray-900 mb-4">Informations Client</h5>
                            
                            {{-- Checkbox Nouveau Client (style Tailwind) --}}
                            <div class="flex items-center mb-4">
                                <input type="checkbox" id="newClientCheckbox" class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <label for="newClientCheckbox" class="ml-2 block text-sm font-medium text-gray-900">Nouveau Client</label>
                            </div>

                            {{-- Formulaire Nouveau Client --}}
                            <div id="clientInputs" style="display: none;" class="p-4 border border-gray-200 rounded-lg space-y-4 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Nom : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="Nom complet" name="newclient_name" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Numéro Client : <span class="text-red-600">*</span></label>
                                        @error('client_number')
                                            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                                        @enderror
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" pattern="[0-9]*" inputmode="numeric" placeholder="Numéro Client" name="newclient_number" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone : </label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" pattern="[0-9]*" inputmode="numeric" placeholder="Numéro de téléphone" name="newclient_phone" />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Rue : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="Votre adresse complète ici" name="newclient_street" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Ville : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="Ville" name="newclient_city" required />
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Code Postal : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" pattern="[0-9]*" inputmode="numeric" placeholder="Code Postal" name="newclient_zip" required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Pays : <span class="text-red-600">*</span></label>
                                        <input class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" type="text" placeholder="Pays" name="newclient_country" required />
                                    </div>
                                </div>
                                <div class="pt-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Emails </label>
                                    <textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" id="emails" name="emails" rows="4"></textarea>
                                    <p class="text-sm text-gray-500 mt-1">Entrez les adresses Email de vos différents destinataires séparés par virgule</p>
                                </div>
                            </div>
                            
                            {{-- Client Existant & Contacts --}}
                            <div class="existing-client-section grid grid-cols-1 md:grid-cols-3 gap-6" style="display: {{ old('emails') === null ? 'grid' : 'none' }}">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="task-for-project">Client pour le Projet :</label>
                                    <input type="hidden" name="client" id="selectedClient" value="{{ $devis->client_id }}">
                                    
                                    {{-- Votre menu déroulant Bootstrap JS. --}}
                                    <div class="relative dropdown">
                                        <button class="relative w-full bg-white border border-gray-300 rounded-lg shadow-sm py-2 px-3 text-left cursor-default focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500 sm:text-sm" type="button" id="ClientDropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <span class="block truncate {{ $client ? 'text-gray-900' : 'text-gray-500' }}">
                                                {{ $client ? $client->name . ' - ' . $client->client_number : 'Choose Client' }}
                                            </span>
                                            <span class="absolute inset-y-0 right-0 flex items-center pr-2 pointer-events-none">
                                                <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.25 4.25a.75.75 0 01-1.06 0L5.23 8.29a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                            </span>
                                        </button>
                                        <ul class="dropdown-menu absolute z-10 mt-1 w-full bg-white shadow-lg max-h-60 rounded-md py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm" aria-labelledby="ClientDropdown">
                                            <li><a class="dropdown-item text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 block" href="#" data-value="none">Aucun</a></li>
                                            @foreach($clients as $c)
                                            <li>
                                                <a class="dropdown-item text-gray-900 cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-100 block" href="#" data-value="{{$c->id}}">
                                                    <span class="font-semibold">{{$c->client_number}}</span>-<span class="truncate">{{$c->name}}</span>
                                                </a>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1" for="project-for-project">Contacts :</label>
                                    <div id="contactsDiv" class="space-y-2 mt-2">
                                        {{-- Les contacts seront ajoutés ici par JS --}}
                                    </div>
                                </div>
                            </div>

                            {{-- Actions du Formulaire (style Tailwind) --}}
                            <div class="flex justify-end items-center mt-8 pt-6 border-t border-gray-200 space-x-3">
                                <a href="{{ $cancelRoute }}" class='bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm'>
                                    Annuler
                                </a>
                                <button type="submit" name="button" class="inline-flex justify-center bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2.5 rounded-lg text-sm shadow-sm">
                                    Générer le devis
                                </button>
                            </div>

                        </form>
                    </div> {{-- Fin p-6 --}}
                </div> {{-- Fin Card --}}
            </div> {{-- Fin max-w-4xl --}}

        </main>
    </div> {{-- Fin Main Wrapper --}}

    @livewireScripts
    
    {{-- ORDRE DES SCRIPTS CORRIGÉ --}}
    {{-- 1. jQuery (requis pour Bootstrap et AJAX) --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    
    {{-- 2. Vos scripts de layout (qui doivent inclure Bootstrap JS pour le menu déroulant) --}}
    @include('dashboard_layouts.scripts')
    
    {{-- 3. Le SCRIPT PERSONNALISÉ CORRECT pour CETTE page --}}
    <script>
        // Désactiver l'autocomplétion
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });

        // **MIS À JOUR** Fonction AddInput pour générer du HTML Tailwind
        function addInput(divName) {
            var newdiv = document.createElement('div');
            newdiv.className = 'grid grid-cols-1 md:grid-cols-2 gap-6 p-4 border border-gray-200 rounded-lg';
            newdiv.innerHTML =
                '<div>' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">Détails <span class="text-red-600">*</span></label>' +
                    '<textarea class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" name="task[]" rows="3" required></textarea>' +
                '</div>' +
                '<div>' +
                    '<label class="block text-sm font-medium text-gray-700 mb-1">Budget <span class="text-red-600">*</span></label>' +
                    '<input type="number" step="0.0001" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 taskBudget" name="taskBudget[]" required />' +
                '</div>';
            
            document.getElementById(divName).appendChild(newdiv);
            
            const newTaskBudgetInput = newdiv.querySelector('.taskBudget');
            if (newTaskBudgetInput) {
                 newTaskBudgetInput.addEventListener('input', updateProjectBudget);
            }
        }

        // Fonction de suppression
        function removeInput(divName) {
            var div = document.getElementById(divName);
            if (div.children.length > 1) { 
                div.removeChild(div.lastChild);
                updateProjectBudget();
            }
        }

        // Mettre à jour le budget total lorsque les budgets des tâches changent
        function updateProjectBudget() {
            let totalBudget = 0;
            document.querySelectorAll('.taskBudget').forEach(function(input) {
                let value = parseFloat(input.value);
                if (!isNaN(value)) {
                    totalBudget += value;
                }
            });
            document.getElementById('projectBudget').value = totalBudget.toFixed(2);
        }
        // Ajouter des écouteurs d'événements à toutes les entrées de budget de tâche initiales
        document.querySelectorAll('.taskBudget').forEach(function(input) {
            input.addEventListener('input', updateProjectBudget);
        });
        // Calculer le budget au chargement de la page
        updateProjectBudget(); 

        // Basculer entre les entrées Nouveau/Existant projet
        function toggleProjectInputs() {
            var projectType = document.getElementById('projectType').value;
            var projectNameInput = document.getElementById('projectName');
            var existingProjectSelect = document.getElementById('existingProjectId');

            if (projectType === 'new') {
                projectNameInput.disabled = false;
                projectNameInput.classList.remove('bg-gray-100'); 
                existingProjectSelect.disabled = true;
                existingProjectSelect.classList.add('bg-gray-100'); 
                existingProjectSelect.value = '';
                document.getElementById('projectTva').value = '';
                document.getElementById('costPerHour').value = '';
            } else if (projectType === 'existing') {
                projectNameInput.disabled = true;
                projectNameInput.classList.add('bg-gray-100');
                projectNameInput.value = '';
                existingProjectSelect.disabled = false;
                existingProjectSelect.classList.remove('bg-gray-100');
                populateProjectDetails(); // Remplir les détails pour la sélection actuelle
            } else {
                projectNameInput.disabled = true;
                projectNameInput.classList.add('bg-gray-100');
                projectNameInput.value = '';
                existingProjectSelect.disabled = true;
                existingProjectSelect.classList.add('bg-gray-100');
                existingProjectSelect.value = '';
                document.getElementById('projectTva').value = '';
                document.getElementById('costPerHour').value = '';
            }
        }

        // Remplir les détails lorsqu'un projet existant est sélectionné
        function populateProjectDetails() {
            var existingProjectSelect = document.getElementById('existingProjectId');
            var selectedOption = existingProjectSelect.options[existingProjectSelect.selectedIndex];
            
            var tva = selectedOption.getAttribute('data-tva') || '';
            var costPerHour = selectedOption.getAttribute('data-cost-per-hour') || '';
            
            document.getElementById('projectTva').value = tva;
            document.getElementById('costPerHour').value = costPerHour;
        }

        // Logique pour le menu déroulant Bootstrap et le chargement des contacts
        document.addEventListener('DOMContentLoaded', function() {
            // Déclencher le toggle au chargement pour définir le bon état initial
            toggleProjectInputs();
            
            const dropdownButton = document.getElementById('ClientDropdown');
            const dropdownButtonText = dropdownButton.querySelector('span');
            const selectedClientIdInput = document.getElementById('selectedClient');
            const newClientCheckbox = document.getElementById('newClientCheckbox');
            const clientInputs = document.getElementById('clientInputs');
            const existingClientSection = document.querySelector('.existing-client-section');

            // Gérer la case à cocher nouveau client
            newClientCheckbox.addEventListener('change', function() {
                const newClientInputs = document.querySelectorAll('#clientInputs input, #clientInputs textarea');
                if (this.checked) {
                    clientInputs.style.display = 'block';
                    existingClientSection.style.display = 'none';
                    selectedClientIdInput.value = '';
                    document.getElementById('contactsDiv').innerHTML = '';
                    dropdownButtonText.textContent = 'Choisir un Client';
                    dropdownButtonText.classList.add('text-gray-500');
                    newClientInputs.forEach(input => input.disabled = false); // Activer les champs nouveau client
                } else {
                    clientInputs.style.display = 'none';
                    existingClientSection.style.display = 'grid'; 
                    newClientInputs.forEach(input => input.disabled = true); // Désactiver les champs nouveau client
                }
            });

            // Gérer la sélection du client dans le menu déroulant
            document.querySelectorAll('.dropdown-item').forEach(item => {
                item.addEventListener('click', function(event) {
                    event.preventDefault();
                    const clientId = event.currentTarget.getAttribute('data-value');
                    const clientName = event.currentTarget.textContent.trim(); 

                    if (clientId === 'none') {
                        dropdownButtonText.textContent = 'Choisir un Client';
                        dropdownButtonText.classList.add('text-gray-500');
                        selectedClientIdInput.value = '';
                        document.getElementById('contactsDiv').innerHTML = '';
                    } else {
                        dropdownButtonText.textContent = clientName;
                        dropdownButtonText.classList.remove('text-gray-500');
                        selectedClientIdInput.value = clientId;
                        loadContacts(clientId);
                    }
                });
            });

            // **MIS À JOUR** Fonction pour charger les contacts et générer des cases à cocher Tailwind
            function loadContacts(clientId) {
                let baseUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    baseUrl = '/Responsable_de_projet/get-contacts/';
                @elseif(Auth::user()->hasRole('Admin'))
                    baseUrl = '/admin/get-contacts/';
                @elseif(Auth::user()->hasRole('Employee'))
                    baseUrl = '/employee/get-contacts/';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    baseUrl = '/Responsable_gestionnaire/get-contacts/';
                @endif

                $.ajax({
                    url: baseUrl + clientId,
                    type: 'GET',
                    success: function(data) {
                        const contactsDiv = $('#contactsDiv');
                        contactsDiv.empty(); 
                        const selectedContacts = {!! json_encode($selectedContacts ?? []) !!};

                        data.forEach(function(contact) {
                            const isChecked = selectedContacts.includes(contact.id) ? 'checked' : '';
                            const checkbox = `
                                <div class="flex items-center">
                                    <input class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" type="checkbox" 
                                           name="contacts[]" 
                                           id="contact${contact.id}" 
                                           value="${contact.id}"
                                           ${isChecked}>
                                    <label class="ml-2 block text-sm text-gray-900" for="contact${contact.id}">
                                        ${contact.name}
                                    </label>
                                </div>`;
                            contactsDiv.append(checkbox);
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Erreur lors du chargement des contacts :', error);
                        $('#contactsDiv').html('<div class="text-red-600 p-2">Erreur lors du chargement des contacts</div>');
                    }
                });
            }
            
            if(selectedClientIdInput.value) {
                loadContacts(selectedClientIdInput.value);
            }
        });
    </script>

</body>
</html>