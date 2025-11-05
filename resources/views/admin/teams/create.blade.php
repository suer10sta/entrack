<!DOCTYPE html>
<html lang="fr"> {{-- Langue mise à jour en 'fr' --}}
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nouvelle Équipe - BizManager</title>
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

            <div class="w-full max-w-3xl mx-auto"> 

                @php
                    $formAction = '';
                    $cancelRoute = '';
                    if(Auth::user()->hasRole('Admin')) {
                        $formAction = route('admin.teams.store');
                        $cancelRoute = route('admin.index'); // Mettez à jour vers la page des équipes si nécessaire
                    } elseif(Auth::user()->hasRole('Responsable de projet')) {
                        $formAction = route('Responsable_de_projet.teams.store');
                        $cancelRoute = route('Responsable_de_projet.index');
                    } elseif(Auth::user()->hasRole('Employee')) {
                        $formAction = route('employee.teams.store');
                        $cancelRoute = route('employee.index');
                    } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $formAction = route('Responsable_gestionnaire.teams.store');
                        $cancelRoute = route('Responsable_gestionnaire.index');
                    }
                @endphp

                <form class="multisteps-form__form" method="POST" action="{{ $formAction }}" autocomplete="off">
                    @csrf
                    
                    {{-- Carte de formulaire (style Tailwind) --}}
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                        
                        {{-- En-tête de la carte --}}
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">Nouvelle Équipe</h2>
                            <p class="text-sm text-gray-600 mt-1">Créer une nouvelle équipe</p>
                        </div>

                        {{-- Corps de la carte --}}
                        <div class="p-6 space-y-6">
                            
                            {{-- Champ Nom --}}
                            <div>
                                <label for="teamName" class="block text-sm font-medium text-gray-700 mb-1">Nom de l'équipe</label>
                                <input type="text" id="teamName" name='name'
                                       class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" 
                                       required />
                            </div>

                            {{-- Membres de l'équipe --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Membres de l'équipe :</label>
                                <div class="space-y-3">
                                    @foreach($employee_users as $employee)
                                        {{-- Case à cocher stylée Tailwind --}}
                                        <div class="flex items-center">
                                            <input id="employee{{$employee->id}}" 
                                                   name="members[]" 
                                                   type="checkbox" 
                                                   value="{{$employee->name}}"
                                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <label for="employee{{$employee->id}}" class="ml-2 block text-sm text-gray-900">
                                                {{$employee->name}}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Pied de page de la carte (Actions) --}}
                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex items-center justify-end gap-3">
                            <a href="{{ $cancelRoute }}" class="inline-flex justify-center bg-white hover:bg-gray-100 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm">
                                Annuler
                            </a>
                            <button type="submit" class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
                                Créer l'équipe
                            </button>
                        </div>

                    </div>
                </form>

            </div>
        </main>
    </div>
    
    @livewireScripts
    @include('dashboard_layouts.scripts') 

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
</html>!