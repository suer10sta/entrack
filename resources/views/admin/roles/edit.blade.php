<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Role - BizManager</title>
    @vite('resources/css/app.css')
    @livewireStyles
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">

    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Contenu Principal --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">

            <div class="w-full max-w-3xl mx-auto">
                
                {{-- Déterminer les routes en premier --}}
                @php
                    $formAction = '';
                    $cancelRoute = '';
                    if(Auth::user()->hasRole('Admin')) {
                        $formAction = route('admin.roles.assign_permission', $role->id);
                        $cancelRoute = route('admin.index'); // Mettez à jour vers la page des rôles si nécessaire
                    } elseif(Auth::user()->hasRole('Responsable de projet')) {
                        $formAction = route('Responsable_de_projet.roles.assign_permission', $role->id);
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $formAction = route('employee.roles.assign_permission', $role->id);
                        $cancelRoute = route('employee.index');
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $formAction = route('Responsable_gestionnaire.roles.assign_permission', $role->id);
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp

                <form method="POST" action="{{ $formAction }}">
                    @csrf
                    {{-- Carte Principale --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        
                        {{-- En-tête de la Carte --}}
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                Modifier le Rôle : {{ $role->name }}
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Informations obligatoires</p>
                        </div>

                        {{-- Corps de la Carte --}}
                        <div class="p-6 space-y-6">
                            
                            {{-- Champ Nom (Lecture seule) --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom :</label>
                                <input type="text" name="name" id="name"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm sm:text-sm py-2 px-3 bg-gray-100 cursor-not-allowed" 
                                       value="{{$role->name}}" readonly />
                            </div>

                            <hr class="border-gray-200" />

                            {{-- Section des Permissions --}}
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Assigner les Permissions :</h3>
                                
                                {{-- NOUVEAU : Case "Tout sélectionner" --}}
                                <div class="flex items-center mt-4">
                                    <input id="select-all" 
                                           type="checkbox" 
                                           class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                    <label for="select-all" class="ml-2 block text-sm font-bold text-gray-900">
                                        Tout sélectionner
                                    </label>
                                </div>
                                <hr class="my-3 border-gray-200">
                                {{-- FIN DE L'AJOUT --}}

                                <div class="mt-4 space-y-3">
                                    @foreach($permissions as $per)
                                        @php
                                            $hasPermission = \App\Models\EnterpriseRolePermission::where('entreprise_id', Auth::user()->entreprise_id)
                                                ->where('role_id', $role->id)
                                                ->where('permission_id', $per->id)
                                                ->exists();
                                        @endphp
                                        
                                        {{-- Case à cocher stylée Tailwind --}}
                                        <div class="flex items-center">
                                            <input id="vue-checkbox-{{ $per->id }}" 
                                                   name="permissions[]" 
                                                   type="checkbox" 
                                                   value="{{ $per->id }}"
                                                   {{-- CLASSE AJOUTÉE ICI --}}
                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 permission-checkbox"
                                                   {{ $hasPermission ? 'checked' : '' }}>
                                            <label for="vue-checkbox-{{ $per->id }}" class="ml-2 block text-sm text-gray-900">
                                                {{ $per->name }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Pied de page de la Carte (Actions) --}}
                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                            <a href="{{ $cancelRoute }}" class="inline-flex justify-center bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm">
                                Annuler
                            </a>
                            <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
                                Assigner
                            </button>
                        </div>
                        
                    </div>
                </form>
            </div>
        </main>
    </div>

    @livewireScripts
    
    <script>
        // Script simple pour désactiver l'autocomplétion
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });
    </script>
    
    {{-- NOUVEAU : Script pour "Tout sélectionner" --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAll = document.getElementById('select-all');
            const permissions = document.querySelectorAll('.permission-checkbox');
    
            // 1. Clic sur "Tout sélectionner"
            selectAll.addEventListener('click', function() {
                // Coche ou décoche toutes les permissions pour qu'elles correspondent à "Select All"
                permissions.forEach(checkbox => {
                    checkbox.checked = selectAll.checked;
                });
            });
    
            // 2. Mettre à jour "Select All" si vous cliquez sur les cases individuelles
            permissions.forEach(checkbox => {
                checkbox.addEventListener('click', function() {
                    // Si vous décochez une case, "Select All" doit être décoché
                    if (!this.checked) {
                        selectAll.checked = false;
                    } else {
                        // Vérifie si toutes les autres cases sont cochées
                        const allChecked = Array.from(permissions).every(c => c.checked);
                        selectAll.checked = allChecked;
                    }
                });
            });
        });
    </script>
</body>
</html>