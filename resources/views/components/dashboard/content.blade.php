{{-- resources/views/dashboard/content.blade.php --}}

<style>
    @keyframes fadeInSlideUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeInSlideUp {
        opacity: 0; 
        animation: fadeInSlideUp 0.5s ease-out forwards;
    }
    /* Style pour le soulignement pointillé */
    .dotted-underline {
         border-bottom: 2px dotted;
         /* La couleur sera définie par text-green-500 ou text-red-500 */
         padding-bottom: 1px; 
         line-height: 1;
         display: inline-block; /* Ou w-fit si Tailwind v3+ */
    }
</style>

<div class="flex-1 overflow-auto bg-gray-50">
    {{-- Main content --}}
    <div class="p-8">
    
    {{-- Stats Cards - DECOMMENTE --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

        {{-- Clients --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" style="animation-delay: 100ms;">
            <div class="flex items-center justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:bg-blue-100 transition-colors">
                             <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5V10H2v10h5m10 0V8a5 5 0 00-10 0v12" />
                            </svg>
                        </div>
                        <h3 class="text-gray-500 text-sm font-medium">Clients</h3>
                    </div>
                    {{-- Utilisation de la variable du contrôleur --}}
                    <p class="text-3xl font-bold text-gray-800">{{ $stat_clients ?? 0 }}</p> 
                </div>
                {{-- Vous pouvez ajouter une logique pour le % si nécessaire --}}
                {{-- <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+12%</span> --}}
            </div>
            <div class="relative">
                <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                    {{-- Vous pouvez dynamiser la largeur si vous avez le % --}}
                    <div class="w-2/3 h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div> 
                </div>
            </div>
        </div>

        {{-- Employees --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" style="animation-delay: 200ms;">
             <div class="flex items-center justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                           <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 21v-2a4 4 0 00-8 0v2m8 0H5a2 2 0 01-2-2V7a2 2 0 012-2h5l2 2h5a2 2 0 012 2v12a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 class="text-gray-500 text-sm font-medium">Employés</h3>
                    </div>
                    {{-- Utilisation de la variable du contrôleur --}}
                    <p class="text-3xl font-bold text-gray-800">{{ $stat_employees ?? 0 }}</p>
                </div>
                 {{-- Vous pouvez ajouter une logique pour le % si nécessaire --}}
                {{-- <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+5%</span> --}}
            </div>
            <div class="relative">
                <div class="h-2 bg-green-100 rounded-full overflow-hidden">
                    <div class="w-1/2 h-2 bg-gradient-to-r from-green-500 to-green-600 rounded-full animate-pulse"></div>
                </div>
            </div>
        </div>

        {{-- Projects --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group animate-fadeInSlideUp" style="animation-delay: 300ms;">
             <div class="flex items-center justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center space-x-2 mb-3">
                        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center group-hover:bg-orange-100 transition-colors">
                           <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0H4m16 0l-2 9H6l-2-9" />
                            </svg>
                        </div>
                        <h3 class="text-gray-500 text-sm font-medium">Projets en cours</h3>
                    </div>
                     {{-- Utilisation de la variable du contrôleur --}}
                    <p class="text-3xl font-bold text-gray-800">{{ $stat_projects_in_progress ?? 0 }}</p> 
                    <p class="text-sm text-gray-500 mt-1">{{ $stat_projects_in_progress ?? 0 }} actifs</p>
                </div>
            </div>
             <div class="relative">
                <div class="h-2 bg-orange-100 rounded-full overflow-hidden">
                    {{-- Vous pouvez dynamiser la largeur si nécessaire --}}
                    <div class="w-3/4 h-2 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full animate-pulse"></div> 
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group relative overflow-hidden animate-fadeInSlideUp" style="animation-delay: 400ms;">
            <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full -mr-16 -mt-16 opacity-50"></div>
            <div class="relative z-10">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex-1">
                        <div class="flex items-center space-x-2 mb-3">
                            <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center group-hover:bg-green-100 transition-colors">
                                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c1.38 0 2.63.56 3.54 1.46M15.54 9.46A5 5 0 1112 8z" />
                                </svg>
                            </div>
                            <h3 class="text-gray-500 text-sm font-medium">Revenu mensuel</h3>
                        </div>
                         {{-- Utilisation de la variable du contrôleur --}}
                        <p class="text-3xl font-bold text-gray-800">{{ $stat_revenue_formatted ?? '€0,00' }}</p>
                    </div>
                    {{-- Vous pouvez ajouter une logique pour le % si nécessaire --}}
                    {{-- <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+18%</span> --}}
                </div>
                {{-- Ce cercle peut être dynamisé si vous avez un objectif de revenu --}}
                <div class="flex justify-center mt-4"> 
                    <div class="relative w-14 h-14">
                        <svg class="w-14 h-14 transform -rotate-90">
                            <circle cx="28" cy="28" r="24" stroke="#e5e7eb" stroke-width="4" fill="none" />
                            <circle cx="28" cy="28" r="24" stroke="#10b981" stroke-width="4" fill="none" 
                                    stroke-dasharray="150.8" stroke-dashoffset="37.7" {{-- offset = (1 - pourcentage/100) * dasharray --}}
                                    class="transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold text-green-600">75%</span> {{-- A dynamiser --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Team Members Overview - DECOMMENTE --}}
    <div class="mt-8 border-0 shadow-sm bg-white rounded-lg overflow-x-auto animate-fadeInSlideUp" style="animation-delay: 500ms;">
        <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900">Team Members Overview</h2>
        </div>
        <div class="p-6 flex items-center justify-between">
            <input type="text" placeholder="Search employees..." class="w-full max-w-xs rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 placeholder-gray-500 focus:border-blue-500 focus:outline-none" />
            <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Add employee</button>
        </div>
        <table class="w-full text-left border-collapse">
            <thead class="border-b border-gray-200">
            <tr>
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">EMPLOYEE INFO</th>
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">ROLE & STATUS</th>
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">TEAMS</th> {{-- Modifié pour afficher juste Teams --}}
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">CURRENT TASK</th>
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">PROGRESS</th> {{-- Reste vide --}}
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">MONTHLY STATS</th>
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">UTILIZATION</th>
                <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">ACTIONS</th>
            </tr>
            </thead>
            <tbody>
            {{-- Boucle sur la variable $team_members du contrôleur --}}
            @forelse($team_members as $member)
                <tr class="border-b border-gray-200 hover:bg-gray-50">
                    {{-- EMPLOYEE INFO --}}
                    <td class="px-6 py-4 flex items-center gap-3">
                        @php
                            $initials = strtoupper(substr($member->name, 0, 1) . (strpos($member->name, ' ') ? substr($member->name, strpos($member->name, ' ') + 1, 1) : ''));
                            $colors = ['bg-blue-500', 'bg-purple-500', 'bg-emerald-500', 'bg-orange-500', 'bg-red-500', 'bg-indigo-500'];
                            $color = $colors[$loop->index % count($colors)];
                        @endphp
                        <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $color }}">
                            {{ $initials }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $member->name }}</p>
                            <p class="text-xs text-gray-500">{{ $member->email }}</p>
                            <p class="text-xs text-gray-500">{{ $member->phoneNumber }}</p>
                            <p class="text-xs font-medium text-emerald-600">{{ $member->salary ? '€' . number_format($member->salary, 2, ',', ' ') . '/month' : '' }}</p>
                        </div>
                    </td>
                    {{-- ROLE & STATUS --}}
                    <td class="px-6 py-4 space-y-2">
                        <div class="flex gap-2 flex-wrap">
                             {{-- Utilise la propriété role_names ajoutée dans le contrôleur --}}
                            @forelse($member->role_names as $roleName)
                                <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ Str::contains($roleName, 'Admin') ? 'bg-fuchsia-100 text-fuchsia-700' : 'bg-purple-100 text-purple-700' }}">
                                    {{ $roleName }}
                                </span>
                            @empty
                                <span class="text-xs text-gray-500">No role</span>
                            @endforelse
                        </div>
                        <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $member->current_status === 'Active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                            {{ $member->current_status }}
                        </span>
                        <p class="text-xs text-gray-600">{{ $member->employment_type }}</p>
                    </td>
                    {{-- TEAMS --}}
                    <td class="px-6 py-4 text-xs text-gray-600">
                        {{ $member->assigned_teams }}
                    </td>
                    {{-- CURRENT TASK --}}
                    <td class="px-6 py-4">
                        <span class="inline-block px-2 py-1 text-xs rounded {{ $member->current_task_name === 'No active task' ? 'bg-gray-100 text-gray-700' : 'bg-blue-100 text-blue-700' }}">
                             {{ $member->current_task_name }}
                        </span>
                    </td>
                    {{-- PROGRESS --}}
                    <td class="px-6 py-4">-</td>
                    {{-- MONTHLY STATS --}}
                    <td class="px-6 py-4 space-y-1 text-xs text-gray-600">
                        <p>Hours: <span class="font-medium text-gray-800">{{ $member->monthly_hours }}</span></p>
                        <p>Tasks: <span class="font-medium text-gray-800">{{ $member->monthly_tasks }}</span></p>
                        <p>Days: <span class="font-medium text-gray-800">{{ $member->monthly_days }}</span></p>
                    </td>
                    {{-- UTILIZATION --}}
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2 mb-1">
                            <div class="flex-grow bg-gray-200 rounded-full h-1.5">
                                <div class="{{ $member->utilization === '0%' ? 'bg-gray-400' : 'bg-blue-500' }} h-1.5 rounded-full" style="width: {{ $member->utilization }}"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-700">{{ $member->utilization }}</span>
                        </div>
                        <p class="text-xs text-gray-600">Limit: {{ $member->limit_formatted }}</p>
                    </td>
                    {{-- ACTIONS --}}
                     <td class="px-6 py-4 flex gap-2">
                        <a href="{{ route('admin.employees.show', $member->id) }}" class="text-blue-500 hover:text-blue-700"> 
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </a>
                        {{-- Adaptez la route pour l'édition --}}
                        <a href="#" class="text-green-500 hover:text-green-700"> 
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">No team members found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Project Overview + Events - DECOMMENTE --}}
    <div class="mt-8 grid grid-cols-1 lg:grid-cols-4 gap-8">
        {{-- Project Overview (col-span-3) --}}
        <div class="lg:col-span-3 border-0 shadow-sm bg-white rounded-lg animate-fadeInSlideUp" style="animation-delay: 600ms;">
            <h2 class="p-6 text-lg font-semibold text-gray-900 border-b border-gray-200">Project Overview</h2>
            <div class="px-6 py-3 grid grid-cols-12 gap-4 border-b border-gray-200">
                <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">PROJECT INFO</span>
                <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">BUDGET & COSTS</span>
                <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">TIME TRACKING</span>
                <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">PROGRESS</span>
                <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CLIENT & TEAMS</span>
                <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">ACTIONS</span>
            </div>
            <div class="divide-y divide-gray-200">
                {{-- Boucle sur la variable $projects_overview du contrôleur --}}
                @forelse($projects_overview as $project)
                    <div class="px-6 py-4 grid grid-cols-12 gap-4 items-center">
                        {{-- PROJECT INFO --}}
                        <div class="col-span-3 flex items-center gap-3">
                            @php
                                $projInitials = strtoupper(substr($project->name, 0, 1) . (strpos($project->name, ' ') ? substr($project->name, strpos($project->name, ' ') + 1, 1) : ''));
                                $projColors = ['bg-blue-500', 'bg-purple-500', 'bg-emerald-500', 'bg-orange-500', 'bg-red-500', 'bg-indigo-500'];
                                $projColor = $projColors[$loop->index % count($projColors)];
                            @endphp
                             <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full text-white font-semibold {{ $projColor }}">
                                {{ $projInitials }}
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ strtoupper($project->name) }}</p>
                                <p class="text-xs text-gray-500">Est: {{ $project->estimated_hours ?? 0 }}h</p>
                            </div>
                        </div>
                        {{-- BUDGET & COSTS --}}
                        <div class="col-span-2 text-sm space-y-1">
                            <p class="text-gray-600"><span class="text-gray-500">Budget:</span> <span class="font-medium text-gray-800">€{{ number_format($project->budget ?? 0, 2, ',', ' ') }}</span></p>
                            <p class="text-gray-600"><span class="text-gray-500">CPH:</span> <span class="font-medium text-gray-800">€{{ number_format($project->cost_per_hour ?? 0, 2, ',', ' ') }}</span></p>
                            <p class="text-gray-600"><span class="text-gray-500">TVA:</span> <span class="font-medium text-gray-800">{{ $project->tva ?? 0 }}%</span></p>
                        </div>
                        {{-- TIME TRACKING --}}
                        <div class="col-span-2 text-sm space-y-1">
                            <p class="text-gray-600"><span class="text-gray-500">Duration:</span> <span class="font-bold text-gray-800">{{ $project->duration_formatted }}</span></p>
                             {{-- Utilise la classe CSS et la couleur calculée --}}
                             <p class="font-medium {{ $project->difference_color }} dotted-underline {{ $project->difference_color === 'text-green-500' ? 'border-green-500' : 'border-red-500' }}">{{ $project->difference_formatted }}</p>
                        </div>
                         {{-- PROGRESS --}}
                        <div class="col-span-1">
                             <div class="flex items-center gap-2">
                                <div class="flex-grow bg-gray-200 rounded-full h-1.5">
                                    <div class="{{ $project->progress_formatted === '0.00%' ? 'bg-gray-400' : 'bg-blue-500' }} h-1.5 rounded-full" style="width: {{ $project->progress_formatted }}"></div>
                                </div>
                                <span class="text-xs font-medium text-gray-700">{{ $project->progress_formatted }}</span>
                            </div>
                        </div>
                         {{-- CLIENT & TEAMS --}}
                        <div class="col-span-3 space-y-2">
                            <div>
                                <p class="text-xs text-gray-500">Client:</p>
                                {{-- Utilise la relation client chargée --}}
                                <p class="text-sm font-medium text-blue-600">{{ $project->client->name ?? 'N/A' }}</p> 
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">Teams:</p>
                                <div class="flex flex-wrap gap-1 mt-1">
                                     {{-- Utilise la relation teams chargée --}}
                                    @forelse($project->teams as $team)
                                        <span class="inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">{{ $team->name }}</span>
                                    @empty 
                                        <span class="text-xs text-gray-500">No teams assigned</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        {{-- ACTIONS --}}
                         <td class="col-span-1 flex justify-end gap-2">
                             {{-- Adaptez les liens (ex: vers la page du projet) --}}
                            <a href="{{ route('admin.projects.show', $project->id) }}" class="text-blue-500 hover:text-blue-700"> {{-- Exemple --}}
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.07.207-.07.431 0 .639C20.268 16.057 16.477 19 12 19c-4.478 0-8.268-2.943-9.542-7a1.186 1.186 0 010-.639z"/></svg>
                            </a>
                            {{-- Bouton supprimer (si nécessaire) --}}
                            {{-- <form action="{{ route('admin.projects.destroy', $project->id) }}" method="POST" onsubmit="return confirm('Are you sure?');"> 
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form> --}}
                        </td>
                    </div>
                @empty
                    <div class="px-6 py-4 text-center text-gray-500">No projects found.</div>
                @endforelse
            </div>
        </div>

        {{-- Today's Events (Barre latérale) --}}
        <div class="lg:col-span-1 border-0 shadow-sm bg-white rounded-lg p-6 h-fit animate-fadeInSlideUp" style="animation-delay: 700ms;">
            <div class="flex items-center gap-2">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <h2 class="text-lg font-semibold text-gray-900">Événements du jour</h2>
            </div>
            {{-- Utilise Carbon pour afficher la date actuelle formatée --}}
            <p class="text-sm text-gray-500 mt-4 mb-6">{{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p> 

            <ul class="space-y-4">
                 {{-- Boucle sur la variable $dayEvents du contrôleur --}}
                @forelse($dayEvents as $event)
                    <li class="flex items-start gap-3">
                        <div class="w-1.5 h-1.5 rounded-full mt-2" style="background-color: {{ $event->color ?? '#3B82F6' }};"></div>
                        <div class="border-l-2 pl-3" style="border-color: {{ $event->color ?? '#3B82F6' }};">
                            <p class="text-sm font-medium text-gray-800">{{ $event->title }}</p>
                            <p class="text-sm text-gray-500">
                                {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} - {{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                            </p>
                        </div>
                    </li>
                @empty
                     <li class="text-sm text-gray-500">Aucun événement aujourd'hui.</li>
                @endforelse
            </ul>
             {{-- Adaptez le lien vers votre calendrier complet --}}
            <a href="{{ route('admin.calendar.index') }}" class="inline-block mt-6 text-sm font-medium text-blue-600 hover:underline"> 
                Voir le calendrier complet
            </a>
        </div>
    </div>

    </div> {{-- Fin div p-8 --}}
</div> {{-- Fin div flex-1 --}}