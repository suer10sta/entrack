<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la Permission - DailyTrac</title>
    @vite('resources/css/app.css')
    @livewireStyles {{-- Ajouté pour la cohérence --}}
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden font-sans">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>

    {{-- Sidebar --}}
    <x-dashboard.sidebar />

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Contenu Principal --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">

            <div class="w-full max-w-3xl mx-auto"> {{-- Centrer le formulaire --}}

                {{-- Déterminer les routes en premier --}}
                @php
                    $formAction = '';
                    $cancelRoute = '';
                    if(Auth::user()->hasRole('Admin')) {
                        $formAction = route('admin.permissions.update', $permission);
                        $cancelRoute = route('admin.index'); // Mettez à jour vers la page des rôles si nécessaire
                    } elseif(Auth::user()->hasRole('Responsable de projet')) {
                        $formAction = route('Responsable_de_projet.permissions.update', $permission);
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $formAction = route('employee.permissions.update', $permission);
                        $cancelRoute = route('employee.index');
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $formAction = route('Responsable_gestionnaire.permissions.update', $permission);
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp

                <form class="multisteps-form__form" method="POST" action="{{ $formAction }}" autocomplete="off">
                    @csrf
                    @method('PUT')
                    
                    {{-- Carte de formulaire (style Tailwind) --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        
                        {{-- En-tête de la carte --}}
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Infos Permission</h2>
                            <p class="text-sm text-gray-600 mt-1">Informations obligatoires</p>
                        </div>

                        {{-- Corps de la carte --}}
                        <div class="p-6 space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom :</label>
                                <input id="name" name="name" type="text"
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"
                                       value="{{$permission->name}}" 
                                       required />
                            </div>
                        </div>

                        {{-- Pied de page de la carte (Actions) --}}
                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                            <a href="javascript:history.back()" class="inline-flex justify-center bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm">
                                Annuler
                            </a>
                            <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </main>
    </div>

    @livewireScripts
    @include('dashboard_layouts.scripts') {{-- Inclut vos scripts de base --}}

    {{-- Suppression du script jQuery et multisteps inutile pour cette page --}}
    <script>
        // Script simple pour désactiver l'autocomplétion
        window.addEventListener('load', function() {
            var inputs = document.getElementsByTagName('input');
            for (var i = 0; i < inputs.length; i++) {
                inputs[i].setAttribute('autocomplete', 'none');
            }
        });
    </script>
</body>
</html>