<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices History - BizManager</title>
    @vite('resources/css/app.css') 
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"> --}}

    <style>
        /* Text alignment for inputs/textareas */
        .entrepriseInfos {
            text-align: left;
        }
        /* Style readonly inputs */
        input:read-only, textarea:read-only {
            background-color: #f9fafb; /* bg-gray-50 */
            cursor: default;
        }
    </style>
</head>

<body class="bg-gray-100 flex h-screen overflow-hidden font-sans">


<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
    style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component --}}
        <x-dashboard.navbar />
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8 space-y-6">

            {{-- 1. Header Section --}}
            <div class="flex flex-wrap justify-between items-center gap-4">
                <div>
                    {{-- Adjusted header style --}}
                    <h1 class="text-xl font-semibold text-gray-900">Devis Parameters</h1>
                    <p class="text-sm text-gray-500">Configure devis settings and templates</p>
                </div>
            </div>

            {{-- 2. Devis Information Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">Devis Information</h2>
                    <p class="text-sm text-gray-500 mb-6">Mandatory information</p>

                    {{-- Devis Prefix & Start Number --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Prefix</label>
                            <input
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                type="text" name="devis_prefix"
                                value="{{$entreprise->devis_prefix}}" readonly />
                            {{-- Optional: Add help text if needed --}}
                            {{-- <small class="text-xs text-gray-500 mt-1">Prefix used for devis numbering (e.g., DEV-)</small> --}}
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Number</label>
                            <input
                                class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                type="text" name="devis_start_number"
                                value="{{$entreprise->devis_start_number}}" readonly />
                             {{-- Optional: Add help text if needed --}}
                             {{-- <small class="text-xs text-gray-500 mt-1">The next devis number will start from here.</small> --}}
                        </div>
                    </div>

                    {{-- General Conditions --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">General Condition</label>
                        <textarea rows="6"
                            class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="general_conditions_Devis" readonly>{{ $entreprise->general_conditions_Devis }}</textarea>
                         {{-- Optional: Add help text if needed --}}
                         {{-- <small class="text-xs text-gray-500 mt-1">Default terms and conditions added to the devis PDF.</small> --}}
                    </div>
                </div>
            </div>

             {{-- 3. Devis Body Message Card --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-1">Devis Body Message</h2>
                    <p class="text-sm text-gray-500 mb-6">Message</p>

                    <div>
                        {{-- <label class="block text-sm font-medium text-gray-700 mb-1">Message</label> --}} {{-- Label removed as per screenshot --}}
                        <textarea rows="6"
                            class="editable entrepriseInfos block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                            name="devis_body" readonly>{{ $entreprise->devis_body }}</textarea>
                        {{-- Optional: Add help text if needed --}}
                        {{-- <small class="text-xs text-gray-500 mt-1">Default message body used when sending devis via email.</small> --}}
                    </div>
                </div>
            </div>

            {{-- 4. Action Buttons --}}
            <div class="flex justify-end items-center gap-3 pt-4">
                 @php $backRoute = '#'; // Default fallback @endphp
                 @if(Auth::user()->hasRole('Responsable de projet')) @php $backRoute = route('Responsable_de_projet.index'); @endphp @endif
                 @if(Auth::user()->hasRole('Admin')) @php $backRoute = route('admin.index'); @endphp @endif
                 @if(Auth::user()->hasRole('Employee')) @php $backRoute = route('employee.index'); @endphp @endif
                 @if(Auth::user()->hasRole('Responsable de projet et gestionnaire')) @php $backRoute = route('Responsable_gestionnaire.index'); @endphp @endif

                 {{-- Back Button --}}
                 <a href="{{ $backRoute }}" class="inline-flex items-center bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2 rounded-lg border border-gray-300 shadow-sm">
                     Back
                 </a>
                 {{-- Save Changes Button (Note: Saving happens on blur, this button doesn't trigger save but could be used for other actions or feedback) --}}
                 <button type="button" id="saveChangesBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg shadow-sm">
                     Save Changes
                 </button>
            </div>


        </main>
    </div>

    {{-- SCRIPTS --}}
    @livewireScripts
    {{-- Include Bootstrap JS ONLY if needed for other components on the page --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script> --}}
    @include('dashboard_layouts.scripts') {{-- Keep if it includes necessary base scripts --}}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script> {{-- Keep jQuery for your edit script --}}
    <script src="//unpkg.com/alpinejs" defer></script> {{-- Keep if needed elsewhere --}}

    {{-- Your existing jQuery script for inline editing --}}
    <script>
    $(document).ready(function() {

        // --- 1. Ajout : Stocker la valeur originale au focus ---
        // (Nécessaire pour savoir ce qui a réellement changé)
        $(".entrepriseInfos").focus(function() {
            $(this).data('originalValue', $(this).val());
            // Effacer les anciennes bordures de statut
            $(this).removeClass('border-green-500 border-red-500'); 
        });

        // --- 2. Double-clic pour éditer ---
        // (J'ajoute un style visuel pour montrer que c'est éditable)
        $(".entrepriseInfos").dblclick(function() {
            $(this).prop('readonly', false)
                   .addClass('bg-white ring-1 ring-blue-500') // Style éditable
                   .removeClass('bg-gray-50');
            $(this).focus();
        });

        // --- 3. Clic à l'extérieur (Blur) ---
        // (Ne fait plus que verrouiller le champ, ne sauvegarde plus)
        $(".entrepriseInfos").blur(function() {
            $(this).prop('readonly', true)
                   .removeClass('bg-white ring-1 ring-blue-500') // Retour au style normal
                   .addClass('bg-gray-50');
        });

        // --- 4. Clic sur le bouton "Save Changes" ---
        // (MODIFIÉ : Gère maintenant la logique de sauvegarde et d'animation)
        $("#saveChangesBtn").click(function() {
            
            var $button = $(this);
            // Sauvegarde le contenu HTML original du bouton (avec l'icône)
            var originalHtml = $button.html(); 
            
            // 1. Mettre le bouton en état "Saving..."
            $button.prop('disabled', true)
                   .removeClass('bg-blue-600 hover:bg-blue-700')
                   .addClass('bg-yellow-500') // Couleur "en cours"
                   .text('Saving...'); // Texte simple pendant le chargement

            var EntrepriseId = "{{ Auth::user()->entreprise_id }}";
            var updatedUrl = '';
            @if(Auth::user()->hasRole('Responsable de projet'))
                updatedUrl = '/Responsable_de_projet/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Admin'))
                updatedUrl = '/admin/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Employee'))
                updatedUrl = '/employee/Enterprise_infos/updateInfos';
            @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                updatedUrl = '/Responsable_gestionnaire/Enterprise_infos/updateInfos';
            @endif

            var hasErrors = false;

            // 2. Boucler sur CHAQUE champ
            $(".entrepriseInfos").each(function() {
                var $input = $(this);
                var newContent = $input.val();
                var originalValue = $input.data('originalValue');

                // Verrouiller le champ (au cas où il serait encore en focus)
                $input.prop('readonly', true)
                      .removeClass('bg-white ring-1 ring-blue-500')
                      .addClass('bg-gray-50');

                if (newContent === originalValue || typeof originalValue === 'undefined') {
                    return; // "continue" dans une boucle .each()
                }

                // 3. Sauvegarder le champ (en mode synchrone pour la boucle)
                $input.addClass('border-yellow-400'); // Feedback sur le champ

                $.ajax({
                    url: updatedUrl,
                    type: 'post',
                    async: false, // Force la boucle à attendre la fin de l'appel
                    data: {
                        content: newContent,
                        id: EntrepriseId,
                        column: $input.attr('name'),
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        console.log('Successfully saved: ' + $input.attr('name'));
                        $input.data('originalValue', newContent); // Mettre à jour la valeur stockée
                        $input.removeClass('border-yellow-400 border-red-500').addClass('border-green-500');
                        // Enlever le vert après 2s
                        setTimeout(() => $input.removeClass('border-green-500'), 2000);
                    },
                    error: function(response) {
                        console.error('Error saving: ' + $input.attr('name'));
                        hasErrors = true;
                        $input.removeClass('border-yellow-400').addClass('border-red-500');
                        $input.val(originalValue); // Annuler le changement en cas d'erreur
                    }
                });
            });

            // 4. La boucle EST TERMINÉE (grâce à async: false)
            $button.prop('disabled', false); // Réactiver le bouton

            if (hasErrors) {
                // --- Animation d'erreur ---
                $button.removeClass('bg-yellow-500').addClass('bg-red-600');
                $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>Error!');
            } else {
                // --- Animation de succès ---
                $button.removeClass('bg-yellow-500').addClass('bg-green-600');
                $button.html('<svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>Saved!');
            }

            setTimeout(() => {
                $button.removeClass('bg-green-600 bg-red-600').addClass('bg-blue-600 hover:bg-blue-700');
                $button.html(originalHtml); /
            }, 2000);
        });
    });
</script> 
</body>