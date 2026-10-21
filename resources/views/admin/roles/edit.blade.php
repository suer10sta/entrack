<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Edit Role - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <style>
        .role-edit-form .role-permission input { margin-top: 4px; }
        .role-edit-form .role-permission:has(input:checked) { border-color: #2D7080; background: rgba(45,112,128,.06); }
    </style>
@include('partials.edit-layout-styles')
</head>
<body class="dt-app-shell bg-[#f8fafc] text-[#0B2638] flex h-screen overflow-hidden font-sans antialiased">

    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Contenu Principal --}}
        <main class="dt-edit-page flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:p-8">

            <div class="dt-edit-container w-full max-w-4xl mx-auto">
                
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

                <form class="role-edit-form" method="POST" action="{{ $formAction }}">
                    @csrf
                    {{-- Carte Principale --}}
                    <div class="dt-card overflow-hidden">
                        
                        {{-- En-tête de la Carte --}}
                        <div class="p-5 sm:p-7 border-b border-[#2D7080]/15 border-t-4 border-t-[#2D7080]">
                            @php
                                $roleLabelKey = 'messages.' . $role->name;
                                $roleLabel = __($roleLabelKey);
                            @endphp
                            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#0B2638]">
                                {{ __('messages.Edit Role label') }} {{ $roleLabel !== $roleLabelKey ? $roleLabel : $role->name }}
                            </h2>
                            <p class="text-sm text-[#2D7080] mt-1">{{ __('messages.Mandatory informations') }}</p>
                        </div>

                        {{-- Corps de la Carte --}}
                        <div class="p-5 sm:p-7 space-y-7">
                            
                            {{-- Champ Nom (Lecture seule) --}}
                            <div>
                                <label for="name" class="block text-sm font-medium text-[#0B2638] mb-1">{{ __('messages.Name') }} :</label>
                                <input type="text" name="name" id="name"
                                       class="block w-full rounded-xl border-[#2D7080]/20 text-[#0B2638] sm:text-sm py-3 px-4 bg-[#2D7080]/5 cursor-not-allowed focus:border-[#2D7080] focus:ring-[#2D7080]/25" 
                                       value="{{$role->name}}" readonly />
                            </div>

                            <hr class="border-[#2D7080]/15" />

                            {{-- Section des Permissions --}}
                            <div>
                                <h3 class="text-base font-semibold text-[#0B2638]">{{ __('messages.Assign Permissions') }}</h3>
                                
                                {{-- NOUVEAU : Case "Tout sélectionner" --}}
                                <div class="flex items-center mt-4 rounded-xl border border-[#E7B84B]/50 bg-[#E7B84B]/10 px-4 py-3">
                                    <input id="select-all" 
                                           type="checkbox" 
                                           class="h-4 w-4 shrink-0 text-[#2D7080] border-[#2D7080]/40 rounded focus:ring-[#2D7080] focus:ring-offset-2">
                                    <label for="select-all" class="ml-2 block text-sm font-bold text-[#0B2638]">
                                        {{ __('messages.Select All Permissions') }}
                                    </label>
                                </div>
                                <hr class="my-3 border-[#2D7080]/15">
                                {{-- FIN DE L'AJOUT --}}

                                {{-- [CHANGED] This div now uses grid --}}
                                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                                    @foreach($permissions as $per)
                                        @php
                                            $hasPermission = \App\Models\EnterpriseRolePermission::where('entreprise_id', Auth::user()->entreprise_id)
                                                ->where('role_id', $role->id)
                                                ->where('permission_id', $per->id)
                                                ->exists();
                                        @endphp
                                        
                                        {{-- Case à cocher stylée Tailwind --}}
                                        <div class="role-permission flex items-start gap-3 rounded-xl border border-[#2D7080]/15 px-4 py-3 transition-colors hover:border-[#2D7080]/40 hover:bg-[#2D7080]/5 focus-within:ring-2 focus-within:ring-[#E7B84B]">
                                            <input id="vue-checkbox-{{ $per->id }}" 
                                                   name="permissions[]" 
                                                   type="checkbox" 
                                                   value="{{ $per->id }}"
                                                   {{-- CLASSE AJOUTÉE ICI --}}
                                                   class="h-4 w-4 shrink-0 text-[#2D7080] border-[#2D7080]/40 rounded focus:ring-[#2D7080] focus:ring-offset-2 permission-checkbox"
                                                   {{ $hasPermission ? 'checked' : '' }}>
                                            <label for="vue-checkbox-{{ $per->id }}" class="block flex-1 min-w-0 text-sm leading-6 text-[#0B2638] cursor-pointer break-words">
                                                @php
                                                    $permissionRawName = trim($per->name);
                                                    $permissionCandidates = array_values(array_unique([
                                                        $permissionRawName,
                                                        strtolower($permissionRawName),
                                                        strtoupper($permissionRawName),
                                                        str_replace(['_', '-'], ' ', $permissionRawName),
                                                        ucwords(strtolower(str_replace(['_', '-'], ' ', $permissionRawName))),
                                                        str_replace(' ', '_', $permissionRawName),
                                                        strtolower(str_replace(' ', '_', $permissionRawName)),
                                                        strtoupper(str_replace(' ', '_', $permissionRawName)),
                                                    ]));
                                                    $permissionLabel = $permissionRawName;
                                                    foreach ($permissionCandidates as $candidate) {
                                                        $candidateKey = 'messages.' . $candidate;
                                                        $translated = __($candidateKey);
                                                        if ($translated !== $candidateKey) {
                                                            $permissionLabel = $translated;
                                                            break;
                                                        }
                                                    }
                                                @endphp
                                                {{ $permissionLabel }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Pied de page de la Carte (Actions) --}}
                        <div class="p-5 sm:px-7 bg-[#2D7080]/5 border-t border-[#2D7080]/15 flex flex-wrap items-center justify-end gap-3">
                            <a href="javascript:history.back()" class="inline-flex justify-center bg-white hover:bg-[#2D7080]/5 text-[#0B2638] font-medium px-5 py-2.5 rounded-xl text-sm transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#E7B84B] border border-[#2D7080]/25 shadow-sm">
                                {{ __('messages.Cancel') }}
                            </a>
                            <button type="submit" class="inline-flex justify-center bg-[#2D7080] hover:bg-[#0B2638] text-white font-medium px-5 py-2.5 rounded-xl text-sm transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#E7B84B] shadow-sm">
                                {{ __('messages.Assign') }}
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
