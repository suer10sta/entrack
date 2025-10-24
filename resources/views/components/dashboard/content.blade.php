{{-- resources/views/dashboard/content.blade.php --}}

<div class="flex-1 overflow-auto bg-gray-50">
    {{-- Main content --}}
    <div class="p-8">

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6">
        {{-- Clients --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
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
                    <p class="text-3xl font-bold text-gray-800">23</p>
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
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
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
                    <p class="text-3xl font-bold text-gray-800">8</p>
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
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group">
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
                    <p class="text-3xl font-bold text-gray-800">3</p>
                    <p class="text-sm text-gray-500 mt-1">3 actifs</p>
                </div>
            </div>
            <div class="relative">
                <div class="h-2 bg-orange-100 rounded-full overflow-hidden">
                    <div class="w-3/4 h-2 bg-gradient-to-r from-orange-400 to-orange-500 rounded-full animate-pulse"></div>
                </div>
            </div>
        </div>

        {{-- Revenue --}}
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-xl hover:scale-105 transition-all duration-300 group relative overflow-hidden">
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
                        <p class="text-3xl font-bold text-gray-800">€45,230</p>
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


    {{-- Team Members Overview --}}
    @php
    $members = [
        ['name'=>'Admin','email'=>'Admin@gmail.com','phone'=>'0666666661','salary'=>'€200.00/month','roles'=>['SuperAdmin','Admin'],'status'=>'Active','type'=>'Full-time','teams'=>'Not assigned','currentTask'=>'No active task','hours'=>'0h 0m','tasks'=>'0/0','days'=>'0','progress'=>'0%','limit'=>'100h','initials'=>'AD','color'=>'bg-blue-500'],
        ['name'=>'Alice Martin','email'=>'alice@example.com','phone'=>'0666666661','salary'=>'€2,000.00/month','roles'=>['Employee'],'status'=>'Active','type'=>'Contractor','teams'=>'Not assigned','currentTask'=>'No active task','hours'=>'0h 0m','tasks'=>'0/0','days'=>'0','progress'=>'0%','limit'=>'160h','initials'=>'AL','color'=>'bg-blue-500'],
    ];
@endphp

<div class="mt-8 border-0 shadow-sm bg-white rounded-lg overflow-x-auto">
    {{-- Titre --}}
    <div class="p-6">
        <h2 class="text-lg font-semibold text-gray-900">Team Members Overview</h2>
    </div>
    
    {{-- Barre de recherche et Bouton --}}
    <div class="p-6 flex items-center justify-between">
        <input type="text" placeholder="Search employees..."
               class="w-full max-w-xs rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 placeholder-gray-500 focus:border-blue-500 focus:outline-none" />
        <button class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">+ Add employee</button>
    </div>
    
    {{-- Table --}}
    <table class="w-full text-left border-collapse">
        {{-- Correction: En-tête blanc, sans 'bg-gray-50' --}}
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
        @foreach($members as $member)
            <tr class="border-b border-gray-200 hover:bg-gray-50">
                <td class="px-6 py-4 flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $member['color'] }}">
                        {{ $member['initials'] }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $member['name'] }}</p>
                        <p class="text-xs text-gray-500">{{ $member['email'] }}</p>
                        <p class="text-xs text-gray-500">{{ $member['phone'] }}</p>
                        <p class="text-xs font-medium text-emerald-600">{{ $member['salary'] }}</p>
                    </div>
                </td>
                <td class="px-6 py-4 space-y-2">
                    <div class="flex gap-2">
                        @foreach($member['roles'] as $role)
                            <span class="inline-block px-2 py-1 text-xs font-medium rounded {{ $role=='SuperAdmin'?'bg-fuchsia-100 text-fuchsia-700':'bg-purple-100 text-purple-700'}}">{{ $role }}</span>
                        @endforeach
                    </div>
                    <span class="inline-block px-2 py-1 text-xs font-medium bg-emerald-100 text-emerald-700 rounded">{{ $member['status'] }}</span>
                    <p class="text-xs text-gray-600">{{ $member['type'] }}</p>
                </td>
                <td class="px-6 py-4">{{ $member['teams'] }}</td>
                {{-- Correction: "No active task" avec un badge gris --}}
                <td class="px-6 py-4">
                    <span class="inline-block px-2 py-1 text-xs text-gray-700 bg-gray-100 rounded">
                        {{ $member['currentTask'] }}
                    </span>
                </td>
                <td class="px-6 py-4">-</td>
                {{-- Correction: Label en gris clair, valeur en gris foncé + gras --}}
                <td class="px-6 py-4 space-y-1 text-xs text-gray-600">
                    <p>Hours: <span class="font-medium text-gray-800">{{ $member['hours'] }}</span></p>
                    <p>Tasks: <span class="font-medium text-gray-800">{{ $member['tasks'] }}</span></p>
                    <p>Days: <span class="font-medium text-gray-800">{{ $member['days'] }}</span></p>
                </td>
                {{-- Correction: "Limit" aligné à gauche et barre de progression grise --}}
                <td class="px-6 py-4">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="flex-grow bg-gray-200 rounded-full h-1.5">
                            <div class="bg-gray-400 h-1.5 rounded-full" style="width: {{ $member['progress'] }}"></div>
                        </div>
                        <span class="text-xs font-medium text-gray-700">{{ $member['progress'] }}</span>
                    </div>
                    <p class="text-xs text-gray-600">Limit: {{ $member['limit'] }}</p>
                </td>
                {{-- Les icônes correspondent à l'image (Engrenage bleu, Crayon vert) --}}
                <td class="px-6 py-4 flex gap-2">
                    <button class="text-blue-500 hover:text-blue-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </button>
                    <button class="text-green-500 hover:text-green-700">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </button>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

        {{-- Project Overview + Events --}}
        <div class="mt-8 grid gap-8 lg:grid-cols-3">

            {{-- Project Overview --}}
            @php
                $projects = [
                    ['name'=>'WEBSITE REDESIGN','initials'=>'WE','color'=>'bg-blue-500','estimate'=>'200h','budget'=>'€10,000.00','cph'=>'€50.00','tva'=>'20%','duration'=>'0h 0m','difference'=>'+200h 0m','progress'=>'0.00%','client'=>'client1','teams'=>['Frontend Team','Backend Team']],
                    ['name'=>'MOBILE APP DEVELOPMENT','initials'=>'MO','color'=>'bg-purple-500','estimate'=>'250h','budget'=>'€15,000.00','cph'=>'€60.00','tva'=>'20%','duration'=>'0h 0m','difference'=>'+250h 0m','progress'=>'0.00%','client'=>'Beta Ltd','teams'=>['Mobile Team']],
                    ['name'=>'E-COMMERCE SETUP','initials'=>'E','color'=>'bg-blue-600','estimate'=>'180h','budget'=>'€12,000.00','cph'=>'€55.00','tva'=>'20%','duration'=>'0h 0m','difference'=>'+180h 0m','progress'=>'0.00%','client'=>'Delta Inc','teams'=>[]],
                    ['name'=>'CLOUD MIGRATION','initials'=>'CM','color'=>'bg-emerald-500','estimate'=>'300h','budget'=>'€20,000.00','cph'=>'€70.00','tva'=>'20%','duration'=>'0h 0m','difference'=>'+300h 0m','progress'=>'0.00%','client'=>'Gamma Corp','teams'=>['Cloud Team']],
                ];
            @endphp

            <div class="lg:col-span-2 border-0 shadow-sm bg-white rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Project Overview</h2>
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">PROJECT</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">ESTIMATE</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">BUDGET</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">DURATION</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">PROGRESS</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">CLIENT</th>
                        <th class="px-6 py-3 text-xs font-semibold text-gray-600 uppercase tracking-wider">TEAMS</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($projects as $project)
                        <tr class="border-b border-gray-200 hover:bg-gray-50">
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-full text-white font-semibold {{ $project['color'] }}">
                                    {{ $project['initials'] }}
                                </div>
                                <p class="text-sm font-medium text-gray-900">{{ $project['name'] }}</p>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $project['estimate'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $project['budget'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $project['duration'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $project['progress'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $project['client'] }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                @foreach($project['teams'] as $team)
                                    <span class="inline-block px-2 py-1 text-xs font-medium bg-gray-100 text-gray-600 rounded">{{ $team }}</span>
                                @endforeach
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Todays Events --}}
            <div class="border-0 shadow-sm bg-white rounded-lg p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Today's Events</h2>
                <ul class="space-y-3 text-sm text-gray-600">
                    <li class="flex items-center justify-between">
                        <span>Team Meeting</span>
                        <span class="text-gray-400">09:00 AM</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>Project Deadline</span>
                        <span class="text-gray-400">01:00 PM</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <span>Client Call</span>
                        <span class="text-gray-400">04:00 PM</span>
                    </li>
                </ul>
            </div>

        </div>

    </div>
</div>
