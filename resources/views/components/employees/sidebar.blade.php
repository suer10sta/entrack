<aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between h-screen overflow-y-auto custom-scrollbar">
    <div>
        <div class="p-6 flex items-center space-x-3">
            <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center shadow-md shadow-blue-200">
                <span class="text-white font-bold text-xl">
                    {{ substr(App\Models\Employees::where('user_id', Auth::user()->id)->first()->name ?? 'E', 0, 1) }}
                </span>
            </div>
            <div class="overflow-hidden">
                <h1 class="text-sm font-bold text-gray-900 truncate">
                    {{ App\Models\Employees::where('user_id', Auth::user()->id)->first()->name }}
                </h1>
                <p class="text-xs text-gray-500">
                    @if(Auth::user()->hasRole('Responsable de projet')) Project Manager
                    @elseif(Auth::user()->hasRole('Employee')) Employee
                    @else Manager @endif
                </p>
            </div>
        </div>

        <nav class="px-4 space-y-1 mt-2 pb-20">
            
            @php
                $dashboardRoute = '#';
                if(Auth::user()->hasRole('Responsable de projet')) $dashboardRoute = route('Responsable_de_projet.index');
                elseif(Auth::user()->hasRole('Employee')) $dashboardRoute = route('employee.index');
                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $dashboardRoute = route('Responsable_gestionnaire.index');
            @endphp
            <a href="{{ $dashboardRoute }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('*.index') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <span>Dashboard</span>
            </a>

            @can('Planning')
            @php
                $planningRoute = '#';
                if(Auth::user()->hasRole('Responsable de projet')) $planningRoute = route('Responsable_de_projet.calendar.index');
                elseif(Auth::user()->hasRole('Employee')) $planningRoute = route('employee.calendar.index');
                elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $planningRoute = route('Responsable_gestionnaire.calendar.index');
            @endphp
            <a href="{{ $planningRoute }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ request()->routeIs('*.calendar.*') ? 'bg-blue-50 text-blue-600' : 'text-gray-600 hover:bg-gray-50' }} transition-colors font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Planning</span>
            </a>
            @endcan

            <div>
                <button type="button" onclick="toggleDropdown('reporting')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Reporting</span>
                    </span>
                    <svg id="reporting-icon" class="w-4 h-4 dropdown-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                
                <div id="reporting-submenu" class="ml-4 border-l-2 border-gray-100 pl-2 space-y-1 dropdown-menu hidden">
                    @can('View client')
                        @php
                            $clientRoute = '#';
                            if(Auth::user()->hasRole('Responsable de projet')) $clientRoute = route('Responsable_de_projet.clients.index');
                            elseif(Auth::user()->hasRole('Employee')) $clientRoute = route('employee.clients.index');
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $clientRoute = route('Responsable_gestionnaire.clients.index');
                        @endphp
                        <a href="{{ $clientRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Clients Reports</a>
                    @endcan

                    @can('View employee')
                        @php
                            $empRoute = '#';
                            if(Auth::user()->hasRole('Responsable de projet')) $empRoute = route('Responsable_de_projet.employees.index');
                            elseif(Auth::user()->hasRole('Employee')) $empRoute = route('employee.employees.index');
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $empRoute = route('Responsable_gestionnaire.employees.index');
                        @endphp
                        <a href="{{ $empRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Employees Reports</a>
                    @endcan

                    @can('View project')
                        @php
                            $projRoute = '#';
                            if(Auth::user()->hasRole('Responsable de projet')) $projRoute = route('Responsable_de_projet.projects.index');
                            elseif(Auth::user()->hasRole('Employee')) $projRoute = route('employee.projects.index');
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $projRoute = route('Responsable_gestionnaire.projects.index');
                        @endphp
                        <a href="{{ $projRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Projects Reports</a>
                    @endcan

                    @can('View task')
                        @php
                            $taskRoute = '#';
                            if(Auth::user()->hasRole('Responsable de projet')) $taskRoute = route('Responsable_de_projet.tasks.index');
                            elseif(Auth::user()->hasRole('Employee')) $taskRoute = route('employee.tasks.index');
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $taskRoute = route('Responsable_gestionnaire.tasks.index');
                        @endphp
                        <a href="{{ $taskRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Tasks Reports</a>
                    @endcan

                    @can('View team')
                        @php
                            $teamRoute = '#';
                            if(Auth::user()->hasRole('Responsable de projet')) $teamRoute = route('Responsable_de_projet.teams.index');
                            elseif(Auth::user()->hasRole('Employee')) $teamRoute = route('employee.teams.index');
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $teamRoute = route('Responsable_gestionnaire.teams.index');
                        @endphp
                        <a href="{{ $teamRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Teams Reports</a>
                    @endcan

                    @can('View pointage')
                        @php
                            $pointageRoute = '#';
                            if(Auth::user()->hasRole('Responsable de projet')) $pointageRoute = route('Responsable_de_projet.tasks.pointage');
                            elseif(Auth::user()->hasRole('Employee')) $pointageRoute = route('employee.tasks.pointage');
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $pointageRoute = route('Responsable_gestionnaire.tasks.pointage');
                        @endphp
                        <a href="{{ $pointageRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Pointage Reports</a>
                    @endcan

                    @can('Manage workDaysReports')
                        @php
                            $wdRoute = '#';
                            if(Auth::user()->hasRole('Responsable de projet')) $wdRoute = route('Responsable_de_projet.workDaysReports.index');
                            elseif(Auth::user()->hasRole('Employee')) $wdRoute = route('employee.workDaysReports.index');
                            elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $wdRoute = route('Responsable_gestionnaire.workDaysReports.index');
                        @endphp
                        <a href="{{ $wdRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Work Days Reports</a>
                    @endcan
                </div>
            </div>

            @can('Create invoice')
            <div>
                <button type="button" onclick="toggleDropdown('invoices')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Invoices</span>
                    </span>
                    <svg id="invoices-icon" class="w-4 h-4 dropdown-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                
                <div id="invoices-submenu" class="ml-4 border-l-2 border-gray-100 pl-2 space-y-1 dropdown-menu hidden">
                    @php
                        $genInvRoute = '#';
                        $histInvRoute = '#';
                        $paramInvRoute = '#';
                        $creditInvRoute = '#';
                        
                        if(Auth::user()->hasRole('Responsable de projet')) {
                            $genInvRoute = route('Responsable_de_projet.invoices.fullinvoice');
                            $histInvRoute = route('Responsable_de_projet.invoices.invoicehistory');
                            $paramInvRoute = route('Responsable_de_projet.invoices.parameters');
                            $creditInvRoute = route('Responsable_de_projet.invoice.creditNoteHistory');
                        } elseif(Auth::user()->hasRole('Employee')) {
                            $genInvRoute = route('employee.invoices.fullinvoice');
                            $histInvRoute = route('employee.invoices.invoicehistory');
                            $paramInvRoute = route('employee.invoices.parameters');
                            $creditInvRoute = route('employee.invoice.creditNoteHistory');
                        } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                            $genInvRoute = route('Responsable_gestionnaire.invoices.fullinvoice');
                            $histInvRoute = route('Responsable_gestionnaire.invoices.invoicehistory');
                            $paramInvRoute = route('Responsable_gestionnaire.invoices.parameters');
                            $creditInvRoute = route('Responsable_gestionnaire.invoice.creditNoteHistory');
                        }
                    @endphp
                    <a href="{{ $genInvRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Generate</a>
                    <a href="{{ $histInvRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">History</a>
                    <a href="{{ $paramInvRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Parameters</a>
                    <a href="{{ $creditInvRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Credit Note History</a>
                </div>
            </div>
            @endcan

            @can('Create devis')
            <div>
                <button type="button" onclick="toggleDropdown('devis')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Devis</span>
                    </span>
                    <svg id="devis-icon" class="w-4 h-4 dropdown-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                
                <div id="devis-submenu" class="ml-4 border-l-2 border-gray-100 pl-2 space-y-1 dropdown-menu hidden">
                    @php
                        $genDevisRoute = '#';
                        $histDevisRoute = '#';
                        $paramDevisRoute = '#';
                        
                        if(Auth::user()->hasRole('Responsable de projet')) {
                            $genDevisRoute = route('Responsable_de_projet.devis.generateDevis');
                            $histDevisRoute = route('Responsable_de_projet.devis.devishistory');
                            $paramDevisRoute = route('Responsable_de_projet.devis.parameters');
                        } elseif(Auth::user()->hasRole('Employee')) {
                            $genDevisRoute = route('employee.devis.generateDevis');
                            $histDevisRoute = route('employee.devis.devishistory');
                            $paramDevisRoute = route('employee.devis.parameters');
                        } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                            $genDevisRoute = route('Responsable_gestionnaire.devis.generateDevis');
                            $histDevisRoute = route('Responsable_gestionnaire.devis.devishistory');
                            $paramDevisRoute = route('Responsable_gestionnaire.devis.parameters');
                        }
                    @endphp
                    <a href="{{ $genDevisRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Generate</a>
                    <a href="{{ $histDevisRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">History</a>
                    <a href="{{ $paramDevisRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Parameters</a>
                </div>
            </div>
            @endcan

            @can('Create expense note')
            <div>
                <button type="button" onclick="toggleDropdown('expense')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Expense Note</span>
                    </span>
                    <svg id="expense-icon" class="w-4 h-4 dropdown-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>
                
                <div id="expense-submenu" class="ml-4 border-l-2 border-gray-100 pl-2 space-y-1 dropdown-menu hidden">
                    @php
                        $genNoteRoute = '#';
                        $histNoteRoute = '#';
                        $paramNoteRoute = '#';
                        
                        if(Auth::user()->hasRole('Responsable de projet')) {
                            $genNoteRoute = route('Responsable_de_projet.notedefrais.generateNoteDeFrais');
                            $histNoteRoute = route('Responsable_de_projet.notedefrais.notedefraishistory');
                            $paramNoteRoute = route('Responsable_de_projet.notedefrais.parameters');
                        } elseif(Auth::user()->hasRole('Employee')) {
                            $genNoteRoute = route('employee.notedefrais.generateNoteDeFrais');
                            $histNoteRoute = route('employee.notedefrais.notedefraishistory');
                            $paramNoteRoute = route('employee.notedefrais.parameters');
                        } elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                            $genNoteRoute = route('Responsable_gestionnaire.notedefrais.generateNoteDeFrais');
                            $histNoteRoute = route('Responsable_gestionnaire.notedefrais.notedefraishistory');
                            $paramNoteRoute = route('Responsable_gestionnaire.notedefrais.parameters');
                        }
                    @endphp
                    <a href="{{ $genNoteRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Generate</a>
                    <a href="{{ $histNoteRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">History</a>
                    <a href="{{ $paramNoteRoute }}" class="block px-4 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors">Parameters</a>
                </div>
            </div>
            @endcan

            @can('Billings')
                @php
                    $billRoute = '#';
                    if(Auth::user()->hasRole('Responsable de projet')) $billRoute = route('Responsable_de_projet.Enterprise_infos.create');
                    elseif(Auth::user()->hasRole('Employee')) $billRoute = route('employee.Enterprise_infos.create');
                    elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire')) $billRoute = route('Responsable_gestionnaire.Enterprise_infos.create');
                @endphp
                <a href="{{ $billRoute }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Billings</span>
                </a>
            @endcan

        </nav>
    </div>

    <div class="p-4 border-t border-gray-200 bg-white relative">
        <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 rounded-xl p-2 -m-2 transition-colors" onclick="toggleDropdown('user')">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                    {{ substr(Auth::user()->name ?? 'U', 0, 2) }}
                </div>
                <div class="overflow-hidden">
                    <h4 class="text-sm font-medium text-gray-900 truncate max-w-[100px]">{{ Auth::user()->name }}</h4>
                    <p class="text-xs text-gray-500">
                        {{ Auth::user()->roles->pluck('name')->first() ?? 'User' }}
                    </p>
                </div>
            </div>
            <svg id="user-icon" class="w-4 h-4 text-gray-400 user-dropdown-icon transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </div>

        <div id="user-submenu" class="absolute bottom-full mb-2 left-4 right-4 bg-white border border-gray-200 rounded-xl shadow-lg py-2 hidden z-50">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-600 transition-colors">Deconnexion</button>
            </form>
        </div>
    </div>
</aside>

<script>
    function toggleDropdown(id) {
        const submenu = document.getElementById(id + '-submenu');
        const icon = document.getElementById(id + '-icon');

        if (submenu.classList.contains('hidden')) {
            // Open
            submenu.classList.remove('hidden');
            icon.classList.add('rotate-180');
        } else {
            // Close
            submenu.classList.add('hidden');
            icon.classList.remove('rotate-180');
        }
    }

    // Close user dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userSubmenu = document.getElementById('user-submenu');
        const userIcon = document.getElementById('user-icon');
        // We need to find the trigger button element (div with onclick)
        // This logic assumes the trigger is the immediate sibling before the submenu in the DOM
        const userTrigger = userSubmenu.previousElementSibling; 
        
        if (userSubmenu && !userSubmenu.classList.contains('hidden')) {
             if (!userTrigger.contains(event.target) && !userSubmenu.contains(event.target)) {
                userSubmenu.classList.add('hidden');
                if(userIcon) userIcon.classList.remove('rotate-180');
            }
        }
    });
</script>
