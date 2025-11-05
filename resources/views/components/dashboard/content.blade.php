{{-- resources/views/dashboard/content.blade.php --}}

{{-- 
    ANIMATION: 
    Ajout de ce bloc <style> pour définir l'animation 'fadeInSlideUp'.
    Cette animation est ensuite appliquée aux blocs principaux ci-dessous.
--}}
<style>
    @keyframes fadeInSlideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    /* Classe utilitaire pour appliquer l'animation */
    .animate-fadeInSlideUp {
        opacity: 0; /* Commence caché */
        animation: fadeInSlideUp 0.5s ease-out forwards;
    }
</style>

<div class="flex-1 overflow-auto bg-gray-50">
    {{-- Main content --}}
    <div class="p-8">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">

        {{-- Clients --}}
        {{-- ANIMATION: Ajout de la classe et du délai --}}
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
                    <p class="text-3xl font-bold text-gray-800">{{ $data['clients']->count() }}</p>
                </div>
                <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+12%</span>
            </div>
            <div class="relative">
                <div class="h-2 bg-blue-100 rounded-full overflow-hidden">
                    <div class="w-2/3 h-2 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full animate-pulse"></div>
                </div>
            </div>
        </div>

        {{-- Employees --}}
        {{-- ANIMATION: Ajout de la classe et du délai --}}
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
                    <p class="text-3xl font-bold text-gray-800">{{ $data['employees']->count()}}</p>
                </div>
                <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+5%</span>
            </div>
            <div class="relative">
                <div class="h-2 bg-green-100 rounded-full overflow-hidden">
                    <div class="w-1/2 h-2 bg-gradient-to-r from-green-500 to-green-600 rounded-full animate-pulse"></div>
                </div>
            </div>
        </div>

        {{-- Projects --}}
        {{-- ANIMATION: Ajout de la classe et du délai --}}
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
                    <p class="text-3xl font-bold text-gray-800">{{ $data['projects']->count()}}</p>
                    <p class="text-sm text-gray-500 mt-1">{{ $data['projects']->count()}} actifs</p>
                </div>
            </div>
            <div class="relative">
                <div class="h-2 bg-orange-100 rounded-full overflow-hidden">
                    <div class="w-3/4 h-2 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full animate-pulse"></div>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        {{-- ANIMATION: Ajout de la classe et du délai --}}
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
                        <p class="text-3xl font-bold text-gray-800">€{{ number_format($data['monthlyRevenue'], 2, ',', ' ') }}</p></p>
                    </div>
                    <span class="px-3 py-1.5 bg-green-50 text-green-600 font-semibold text-sm rounded-full">+18%</span>
                </div>
                <div class="flex justify-center mt-4">
                    <div class="relative w-14 h-14">
                        <svg class="w-14 h-14 transform -rotate-90">
                            <circle cx="28" cy="28" r="24" stroke="#e5e7eb" stroke-width="4" fill="none" />
                            <circle cx="28" cy="28" r="24" stroke="#10b981" stroke-width="4" fill="none" 
                                    stroke-dasharray="150.8" stroke-dashoffset="37.7" 
                                    class="transition-all duration-1000 ease-out" />
                        </svg>
                        <div class="absolute inset-0 flex items-center justify-center">
                            <span class="text-xs font-bold text-green-600">75%</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Team employees Overview --}}
 
{{-- ANIMATION: Ajout de la classe et du délai --}}
<div class="mt-8 border-0 shadow-sm bg-white rounded-lg overflow-x-auto hover:shadow-xl animate-fadeInSlideUp" style="animation-delay: 500ms;">
    {{-- Titre --}}
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900">Team employees Overview</h2>
    </div>
    
    {{-- Barre de recherche et Bouton --}}
    <div class="p-6 flex items-center justify-between">
        <input type="text" placeholder="Search employees..."
               class="w-full max-w-xs rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 placeholder-gray-500 focus:border-blue-500 focus:outline-none" />
               <a href="{{ route('admin.employees.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">
                + Add employee
            </a>
        </div>
    
    {{-- Table --}}
    <table class="w-full text-left border-collapse">
        <thead class="border-b border-gray-200">
        <tr>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">EMPLOYEE INFO</th>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">ROLE & STATUS</th>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">TEAMS & PROJECTS</th>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">CURRENT TASK</th>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">PROGRESS</th>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">MONTHLY STATS</th>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">UTILIZATION</th>
            <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">ACTIONS</th>
        </tr>
        </thead>
        <tbody>
        @foreach($data['employees'] as $employee)
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="px-6 py-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $employee['color'] }}">
                        {{ $employee['initials'] }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $employee['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $employee['email'] }}</p>
                        <p class="text-xs text-gray-500">{{ $employee['phone'] }}</p>
                        <p class="text-xs font-medium text-emerald-600">{{ $employee['salary'] }}</p>
                    </div>
                </td>
                <td class="px-6 py-4 space-y-2">
                    <div class="flex gap-2">
                        @foreach($employee['roles'] as $role)
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $role=='SuperAdmin'?'bg-fuchsia-100 text-fuchsia-700':'bg-purple-100 text-purple-700'}}">{{ $role }}</span>
                        @endforeach
                    </div>
                    <span class="inline-block px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded">{{ $employee['status'] }}</span>
                    <p class="text-xs text-gray-600">{{ $employee['type'] }}</p>
                </td>
                <td class="px-6 py-4">
                    @forelse($employee->teams as $team)
                    <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-700 rounded mb-1">
                        {{ $team->name }}
                    </span>
                    @empty
                    <span class="text-xs text-gray-400">No team</span>
                    @endforelse
                </td>
                <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded">
                        {{ $employee['currentTask'] }}
                    </span>
                </td>
                <td class="px-6 py-4">-</td>
                <td class="px-6 py-4 space-y-1 text-xs text-gray-600">
                    <p>Hours: <span class="font-medium text-gray-800">{{ $employee['hours'] }}</span></p>
                    <p>Tasks: <span class="font-medium text-gray-800">{{ $employee['tasks'] }}</span></p>
                    <p>Days: <span class="font-medium text-gray-800">{{ $employee['days'] }}</span></p>
                </td>
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="flex-grow bg-gray-200 rounded-full h-1.5">
                            <div class="bg-gray-400 h-1.5 rounded-full" style="width: {{ $employee['progress'] }}"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-700">{{ $employee['progress'] }}</span>
                    </div>
                    <p class="text-xs text-gray-600">Limit: {{ $employee['limit'] }}</p>
                </td>
                <td class="px-6 py-4 flex gap-2">
                    <button>  
    
    {{-- Link to employee settings/show page --}}
    <a href="{{ route('admin.employees.show', $employee->id) }}" class="text-blue-500 hover:text-blue-700">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
    </a>
    
    {{-- Link to employee edit page --}}
    <a href="{{ route('admin.employees.updateEmployee', $employee->id) }}#" class="text-green-500 hover:text-green-700">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
        </svg>
    </a>
</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
{{-- Project Overview + Events --}}

<div class="mt-8 grid grid-cols-1 lg:grid-cols-4 gap-8 ">

    {{-- Project Overview (col-span-3) --}}
    {{-- ANIMATION: Ajout de la classe et du délai --}}
    <div class="lg:col-span-3 border-0 shadow-sm bg-white rounded-lg hover:shadow-xl animate-fadeInSlideUp" style="animation-delay: 600ms;">
        
        <h2 class="p-6 text-lg font-semibold text-gray-900 border-b border-gray-200">Project Overview</h2>

        {{-- EN-TÊTES DE COLONNE (Correction des col-span) --}}
        <div class="px-6 py-3 grid grid-cols-12 gap-4 border-b border-gray-200">
            <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">PROJECT INFO</span>
            <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">BUDGET & COSTS</span>
            <span class="col-span-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">TIME TRACKING</span>
            <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider">PROGRESS</span>
            <span class="col-span-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">CLIENT & TEAMS</span>
            <span class="col-span-1 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">ACTIONS</span>
        </div>

        {{-- LISTE DES PROJETS --}}
        <div class="divide-y divide-gray-200">
            @foreach($data['projects'] as $project)
                <div class="px-6 py-4 grid grid-cols-12 gap-4 items-center">
                    
                    {{-- PROJECT INFO (col-span-3) --}}
                    <div class="col-span-3 flex items-center gap-3">
                        <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-full text-white font-semibold {{ $project['color'] }}">
                            {{ strtoupper(substr($project->name, 0, 2)) }}
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $project->name}}</p>
                            <p class="text-xs text-gray-500">Est: {{ $project->estimated_hours ?? 'N/A' }}</p>
                        </div>
                    </div>

                    {{-- BUDGET & COSTS (col-span-2) --}}
                    <div class="col-span-2 text-sm space-y-1">
                        <p class="text-gray-600"><span class="text-gray-500">Budget:</span> <span class="font-medium text-gray-800">€{{ number_format($project->budget ?? 0, 2, ',', ' ') }}</span></p>
                        <p class="text-gray-600"><span class="text-gray-500">CPH:</span> <span class="font-medium text-gray-800">€{{ number_format($project->cost_per_hour ?? 0, 2, ',', ' ') }}</span></p>
                        <p class="text-gray-600"><span class="text-gray-500">TVA:</span> <span class="font-medium text-gray-800">{{ $project->tva ?? 20 }}</span></p>
                    </div>

                    {{-- TIME TRACKING (col-span-2) --}}
                    <div class="col-span-2 text-sm space-y-1">
                        <p class="text-gray-600"><span class="text-gray-500">Duration:</span> <span class="font-bold text-gray-800">{{ $project->duration ?? '0h 0m' }}</span></p>
                        <p class="font-medium text-green-500 border-b-2 border-green-500 border-dotted w-fit">{{ $project->time_difference ?? '+0h 0m' }}</p>
                    </div>

                    {{-- PROGRESS (col-span-1) --}}
                    <div class="col-span-1">
                         <div class="flex items-center gap-2">
                            <div class="flex-grow bg-gray-200 rounded-full h-1.5">
                                <div class="bg-gray-400 h-1.5 rounded-full" style="width: {{ $project['progress'] }}"></div>
                            </div>
                            <span class="text-xs font-medium text-gray-700">{{ $project->progress ?? '0%' }}</span>
                        </div>
                    </div>

                    {{-- CLIENT & TEAMS (col-span-3) --}}
                    <div class="col-span-3 space-y-2">
                        <div>
                            <p class="text-xs text-gray-500">Client:</p>
                            <p class="text-sm font-medium text-blue-600">{{ $project->client->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Teams:</p>
                            <div class="flex flex-wrap gap-1 mt-1">
                                @forelse($project->teams as $team)
                                    <span class="inline-block px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">{{ $team }}</span>
                                @empty
                                    <span class="text-xs text-gray-500">No teams assigned</span>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    {{-- ACTIONS (col-span-1) --}}
                    <div class="col-span-1 flex justify-end gap-2">
                            {{-- Link to project show page --}}
                            <a href="{{route('admin.projects.show', $project->id)}}" class="text-blue-500 hover:text-blue-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-.07.207-.07.431 0 .639C20.268 16.057 16.477 19 12 19c-4.478 0-8.268-2.943-9.542-7a1.186 1.186 0 010-.639z" />
                                </svg>
                            </a>
                            {{-- Delete Project Form --}}
                            <form action="{{ route('admin.projects.destroy', $project->id) }}"
                            method="POST"
                            onsubmit="return confirm('Are you sure?');"
                            class="inline"> {{-- 'inline' class keeps the form from breaking the layout --}}
                            @csrf
                            @method('DELETE')
                            
                            {{-- This is your original button, but with type="submit" --}}
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>
                        </form>
                    </div>
                    </div>
            @endforeach
        </div>
        {{-- ADD PAGINATION LINKS --}}
        <div class="p-6 border-t border-gray-200">
            {{ $data['projects']->links() }}
        </div>
    </div>

    {{-- Today's Events (Barre latérale) --}}
    {{-- ANIMATION: Ajout de la classe et du délai --}}
    <div class="lg:col-span-1 border-0 shadow-sm bg-white rounded-2xl hover:shadow-xl p-6 h-fit animate-fadeInSlideUp" style="animation-delay: 700ms;">
        {{-- En-tête avec bouton "Add Event" --}}
        <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
                <div class="w-1.5 h-5 bg-blue-600 rounded"></div>
                <h2 class="text-lg font-semibold text-gray-900">Événements du jour</h2>
            </div>
            
            {{-- NOUVEAU: Bouton "Add Event" (visuel pour l'instant) --}}
            <button onclick="window.Livewire.dispatch('openCalendarForm')" class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-3 py-1.5 rounded-lg">
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                 Add Event
                </button>
            </div>
            
            {{-- Date --}}
            <p class="text-sm text-gray-500 mb-6">{{ now()->format('d F Y') }}</p>
            
            {{-- NOUVEAU: Liste des événements (nouveau style) --}}
            <ul class="space-y-5">
                @forelse($data['dayEvents'] as $event)
            <li class="flex gap-3">
                <div class="w-1 flex-shrink-0 bg-blue-500 rounded-full"></div>
                
                <div>
                    <p class="text-sm font-medium text-gray-800">{{ $event->title }}</p>
                    <p class="text-sm text-gray-500 flex items-center gap-1.5 mt-1">
                        <svg class="w-3.5 h-3.5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        <span>
                            {{ \Carbon\Carbon::parse($event->start_date)->format('H:i') }} - 
                            {{ \Carbon\Carbon::parse($event->end_date)->format('H:i') }}
                        </span>
                    </p>
                </div>
            </li>
        @empty
            <li class="text-sm text-gray-500">
                Aucun événement prévu aujourd'hui.
            </li>
        @endforelse
    </ul>

    {{-- NOUVEAU: Lien "Voir le calendrier complet" centré --}}
    <div class="text-center mt-6">
        <button onclick="window.Livewire.dispatch('openCalendarModal')" class="text-sm font-medium text-blue-600 hover:underline">
            Voir le calendrier complet
        </button>
    </div>

</div>
</div>

    </div>
</div>
@livewire('full-calendar-modal')