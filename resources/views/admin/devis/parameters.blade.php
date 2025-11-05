<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devis Parameters - BizManager</title> {{-- Titre mis à jour --}}
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Style des inputs/textareas (non-readonly par défaut) */
        .entrepriseInfos {
            text-align: left;
            background-color: #ffffff; /* Fond blanc normal */
        }
        /* Style pour le feedback visuel de la sauvegarde */
        input.border-green-500, textarea.border-green-500 {
            border-color: #22c55e;
            box-shadow: 0 0 0 1px #22c55e;
        }
        input.border-red-500, textarea.border-red-500 {
            border-color: #ef4444;
            box-shadow: 0 0 0 1px #ef4444;
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />
        
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-8"> {{-- Espace mis à jour --}}
            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Devis Parameters</h1> {{-- Titre mis à jour --}}
                    <p class="text-sm text-gray-600">Configure devis settings and templates</p>
                </div>
            </div>
            
            @php
            $updateUrl = '';
            if(Auth::user()->hasRole('Responsable de projet'))
                $updateUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos';
            elseif(Auth::user()->hasRole('Admin') || Auth::user()->hasRole('SuperAdmin'))
                $updateUrl = '/admin/Enterprise_infos/updateInfos';
            elseif(Auth::user()->hasRole('Employee'))
                $updateUrl = '/employee/Enterprise_infos/updateInfos';
            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                $updateUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos';
            @endphp

            {{-- ID "invoiceParamsForm" AJOUTÉ pour que le JS fonctionne --}}
            <form id="invoiceParamsForm" class="space-y-8">
                @csrf
                <input type="hidden" name="entreprise_id" value="{{ Auth::user()->entreprise_id }}">

                {{-- 2. Devis Information Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">Devis Information</h5>
                        <p class="text-sm text-gray-500 mt-1">Mandatory information</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="devis_prefix" class="block text-sm font-medium text-gray-700 mb-1">Prefix</label>
                            <input id="devis_prefix" 
                                   {{-- Classe "editable" supprimée --}}
                                   class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                   type="text" 
                                   name="devis_prefix" 
                                   value="{{ $entreprise->devis_prefix ?? '' }}" />
                        </div>
                        <div>
                            <label for="devis_start_number" class="block text-sm font-medium text-gray-700 mb-1">Start Number</label>
                            <input id="devis_start_number" 
                                   {{-- Classe "editable" supprimée --}}
                                   class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                   type="text" 
                                   name="devis_start_number"
                                   value="{{$entreprise->devis_start_number}}" />
                        </div>
                    </div>
                    <div>
                        <label for="general_conditions_Devis" class="block text-sm font-medium text-gray-700 mb-1">General Condition</label>
                        <textarea id="general_conditions_Devis" rows="6"
                            {{-- Classe "editable" supprimée --}}
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="general_conditions_Devis" >{{ $entreprise->general_conditions_Devis }}</textarea>
                    </div>
                </div>

                {{-- 3. Devis Body Message Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 space-y-5">
                    <div>
                        <h5 class="text-lg font-semibold text-gray-900">Devis Body Message</h5>
                        <p class="text-sm text-gray-500 mt-1">Message</p>
                    </div>
                    <div>
                        <textarea id="devis_body" rows="6" {{-- ID ajouté pour la clarté --}}
                            {{-- Classe "editable" supprimée --}}
                            class="entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="devis_body" >{{ $entreprise->devis_body }}</textarea>
                    </div>
                </div>

                {{-- 4. Action Buttons --}}
                <div class="flex justify-between items-center mt-8 pt-6 border-t border-gray-200">
                    @php
                        $backRoute = route('admin.index'); // Fallback
                        if(Auth::user()->hasRole('Responsable de projet')) $backRoute = route('Responsable_de_projet.index');
                        elseif(Auth::user()->hasRole('Employee')) $backRoute = route('employee.index');
                        elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $backRoute = route('Responsable_gestionnaire.index');
                    @endphp

                    <a href="{{ url()->previous() ?: $backRoute }}" 
                       class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 border border-gray-300 text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Back
                    </a>
                    
                    {{-- ID mis à jour pour correspondre au script JS --}}
                    <button type="submit" 
                            id="saveButton" 
                            class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm disabled:opacity-50"
                            {{ !$updateUrl ? 'disabled' : '' }}>
                        {{-- L'icône et le texte sont maintenant dans le JS, mais nous mettons un fallback --}}
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
                        </svg>
                        Save All Changes
                    </button>
                </div>
            </form>
        </main>
    </div>

    {{-- SCRIPTS --}}
    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}} {{-- Assurez-vous que cela n'inclut pas un autre jQuery --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="//unpkg.com/alpinejs" defer></script>

    {{-- SCRIPT LOGIQUE DE SAUVEGARDE DE VOTRE EXEMPLE (invoice.parameters) --}}
    <script>
        $(document).ready(function() {
            
            // Stocker la valeur originale au focus pour savoir si elle a changé
            $(".entrepriseInfos").focus(function() {
                 $(this).data('originalValue', $(this).val());
                 // Effacer les anciens statuts de bordure
                 $(this).removeClass('border-green-500 border-red-500'); 
            });

            $('#invoiceParamsForm').on('submit', function(e) {
                e.preventDefault();
                
                // C'est le bouton de votre nouveau fichier
                const $button = $('#saveButton'); 
                const originalText = $button.html(); 
                
                $button.prop('disabled', true).html('<svg class="animate-spin h-4 w-4 mr-2 inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Saving...');

                const entrepriseId = "{{ Auth::user()->entreprise_id }}";
                const updateUrl = "{{ $updateUrl }}";
                const formData = $(this).serializeArray();
                
                // Filtrer les champs qui ne sont pas des données à sauvegarder
                const fieldsToUpdate = formData.filter(field => 
                    field.name !== '_token' && field.name !== 'entreprise_id'
                );
                
                let completed = 0;
                let errors = 0;
                const total = fieldsToUpdate.length;
                let hasChanges = false; // Flag pour voir si quelque chose a changé

                if (total === 0) {
                    $button.prop('disabled', false).html(originalText);
                    return;
                }

                fieldsToUpdate.forEach(function(field) {
                    const $input = $(`[name="${field.name}"]`);
                    const originalValue = $input.data('originalValue');
                    const newValue = field.value;

                    // Si la valeur n'a pas changé, sauter cet appel
                    if (newValue === originalValue || typeof originalValue === 'undefined') {
                        completed++;
                        checkComplete();
                        return; // continue
                    }
                    
                    // Si nous arrivons ici, il y a un changement
                    hasChanges = true;

                    $.ajax({
                        url: updateUrl,
                        type: 'POST',
                        data: {
                            content: newValue,
                            id: entrepriseId,
                            column: field.name,
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            completed++;
                            $input.data('originalValue', newValue); // Mettre à jour la valeur originale
                            $input.addClass('border-green-500'); // Feedback de succès
                            checkComplete();
                        },
                        error: function(xhr) {
                            completed++;
                            errors++;
                            $input.addClass('border-red-500'); // Feedback d'erreur
                            console.error('Error saving ' + field.name + ':', xhr);
                            checkComplete();
                        }
                    });
                });
 
                // Fonction pour gérer l'animation du bouton
                function checkComplete() {
                    if (completed === total) {
                        $button.prop('disabled', false); 
                        
                        // S'il n'y avait aucun changement à sauvegarder
                        if (!hasChanges) {
                             $button.html(originalText); // Remettre le bouton à la normale
                             // (Pas besoin d'alerte, l'utilisateur a juste cliqué sur "sauvegarder" sans rien changer)
                             return;
                        }

                        if (errors === 0) {
                            // --- ANIMATION SUCCÈS ---
                            $button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-green-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>Saved!');
                        
                        } else {
                            // --- ANIMATION ERREUR ---
                            $button.removeClass('bg-blue-600 hover:bg-blue-700').addClass('bg-red-600');
                            $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>Error!');
                            alert('Some fields failed to save. Check fields marked in red.');
                        }

                        // --- Réinitialiser le bouton ---
                        setTimeout(() => {
                            $button.removeClass('bg-green-600 bg-red-600').addClass('bg-blue-600 hover:bg-blue-700');
                            $button.html(originalText); // Restaurer l'icône et le texte d'origine
                            $('.entrepriseInfos').removeClass('border-green-500 border-red-500'); // Nettoyer tous les feedbacks
                        }, 2000); // Délai de 2 secondes
                    }
                }
            });
        });
    </script> 
</body> 
</html>