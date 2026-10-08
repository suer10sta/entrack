{{-- Employee sidebar — identical to x-dashboard.sidebar (Admin reference):
     same brand row (no sidebar toggle; collapse is driven by the navbar toggle),
     same sections, nav-item pattern, gold active indicators, tooltips, submenu
     pattern and user footer. Only the menu entries differ (role-based routes).
     The Profile link renders solely for roles allowed to access profile routes. --}}
@php
    $canAccessProfile = Auth::check() && method_exists(Auth::user(), 'hasAnyRole') && Auth::user()->hasAnyRole(['Admin', 'SuperAdmin', 'super_admin', 'Responsable de projet', 'Responsable de projet et gestionnaire']);

    $rolePrefix = Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet' : (Auth::user()->hasRole('Employee') ? 'employee' : 'Responsable_gestionnaire');

    $dashboardRoute = route($rolePrefix . '.index');
    $planningRoute = $routeForPlanning = '#';
    try {
        if (Auth::user()->hasRole('Responsable de projet')) $planningRoute = route('Responsable_de_projet.calendar.index');
        elseif (Auth::user()->hasRole('Employee')) $planningRoute = route('employee.calendar.index');
        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $planningRoute = route('Responsable_gestionnaire.calendar.index');
    } catch (\Throwable $e) { $planningRoute = '#'; }

    $routeFor = function ($base, $suffix = 'index') use ($rolePrefix) {
        try { return route($rolePrefix . '.' . $base . '.' . $suffix); } catch (\Throwable $e) { return '#'; }
    };

    $billRoute = $routeFor('Enterprise_infos', 'create');

    $clientRoute = $routeFor('clients');
    $empRoute = $routeFor('employees');
    $projRoute = $routeFor('projects');
    $taskRoute = $routeFor('tasks');
    $teamRoute = $routeFor('teams');
    $wdRoute = $routeFor('workDaysReports');
    $pointageRoute = $routeFor('tasks', 'pointage');

    $isReportingPage = request()->routeIs('*.clients.*') ||
        request()->routeIs('*.employees.*') ||
        request()->routeIs('*.teams.*') ||
        request()->routeIs('*.projects.*') ||
        request()->routeIs('*.tasks.*') ||
        request()->routeIs('*.workDaysReports.*');
    $isInvoicesPage = request()->routeIs('*.invoices.*') || request()->routeIs('*.invoice.*');
    $isDevisPage = request()->routeIs('*.devis.*');
    $isExpensePage = request()->routeIs('*.notedefrais.*');
@endphp
<style>
        .dropdown-menu {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            visibility: hidden;
            pointer-events: none;
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out, margin-top 0.3s ease-out, visibility 0.3s;
        }

        .dropdown-menu.active {
            max-height: 500px;
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
            margin-top: 0.25rem;
        }

        .dropdown-icon {
            transition: transform 0.3s ease;
        }

        .dropdown-icon.rotated {
            transform: rotate(90deg);
        }

        .user-dropdown-icon {
            transition: transform 0.3s ease;
        }

        .user-dropdown-icon.rotated {
            transform: rotate(180deg);
        }

        .submenu-item {
            position: relative;
            overflow: hidden;
        }

        .submenu-item::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 2px;
            height: 0;
            background: #E7B84B;
            transition: height 0.2s ease;
        }

        .submenu-item:hover::before {
            height: 60%;
        }

        .dt-nav-item.is-active {
            background: #2D7080;
            color: #fff;
            box-shadow: 0 8px 20px -8px rgba(45, 112, 128, 0.7);
        }
        .dt-nav-item.is-active svg {
            color: #fff;
        }
</style>

{{-- Mobile backdrop --}}
<div id="appSidebarBackdrop" class="fixed inset-0 bg-[#0B2638]/50 backdrop-blur-[2px] z-40 hidden lg:hidden" onclick="toggleAppSidebar(false)"></div>

<aside
    id="appSidebar"
    class="hidden fixed inset-y-0 left-0 z-50 bg-[#0B2638] border-r border-white/10 flex-col justify-between h-screen overflow-y-auto dt-sidebar-scroll transform -translate-x-full transition-all duration-300 ease-out lg:static lg:sticky lg:top-0 lg:translate-x-0 lg:flex"
>
        <div class="flex-1 flex flex-col min-h-0">
            {{-- Brand --}}
            <div class="px-4 pt-5 pb-5 flex items-center">
                <a href="{{ $dashboardRoute }}" aria-label="DalyTrac, accueil" class="flex items-center gap-2.5 min-w-0 flex-1">
                    <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-[#E7B84B] flex items-center justify-center text-[#0B2638] font-extrabold text-[15px] tracking-tight">D</span>
                    <span class="sidebar-logo-text inline-flex items-baseline text-[1.35rem] font-extrabold tracking-[-0.04em] text-white no-underline leading-none"><span>Daly</span><strong class="font-extrabold text-[#7fb3bf]">Trac</strong></span>
                </a>
            </div>

            <nav class="px-3 pb-6 space-y-5 overflow-y-auto dt-sidebar-scroll flex-1">
                {{-- OVERVIEW --}}
                <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Overview') }}</p>
                    <div class="space-y-1">
                        <a href="{{ $dashboardRoute }}"
                            title="Dashboard"
                            class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs($rolePrefix . '.index') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @if(request()->routeIs($rolePrefix . '.index'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                            <span class="sidebar-label truncate">{{ __('messages.Dashboard') }}</span>
                            <span class="sidebar-tip">{{ __('messages.Dashboard') }}</span>
                        </a>
                    </div>
                </div>

                {{-- WORKSPACE --}}
                <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Workspace') }}</p>
                    <div class="space-y-1">
                        @can('Planning')
                        <a href="{{ $planningRoute }}"
                            title="Planning"
                            class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('*.calendar.*') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @if(request()->routeIs('*.calendar.*'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="sidebar-label truncate">{{ __('messages.Planning') }}</span>
                            <span class="sidebar-tip">{{ __('messages.Planning') }}</span>
                        </a>
                        @endcan

                        @canany(['View client', 'View employee', 'View teams', 'View project', 'View task', 'Pointage reports', 'View pointage', 'Manage workDaysReports'])
                        <div>
                            <button type="button" onclick="toggleDropdown('reporting')" title="Reporting"
                                class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                                @if($isReportingPage)
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                    </svg>
                                    <span class="sidebar-label truncate">{{ __('messages.Reports') }}</span>
                                </span>
                                <svg id="reporting-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="sidebar-tip">{{ __('messages.Reports') }}</span>
                            </button>
                            <div id="reporting-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                                @can('View client')
                                <a href="{{ $clientRoute }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.clients.*'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Clients Reports
                                </a>
                                @endcan
                                @can('View employee')
                                <a href="{{ $empRoute }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.employees.*'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Employees Reports
                                </a>
                                @endcan
                                @can('View project')
                                <a href="{{ $projRoute }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.projects.*'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Projects Reports
                                </a>
                                @endcan
                                @can('View task')
                                <a href="{{ $taskRoute }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.tasks.index'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Tasks Reports
                                </a>
                                @endcan
                                @can('View task')
                                <a href="{{ $routeFor('tasks', 'board') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.tasks.board'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    {{ __('messages.Tasks') }}
                                </a>
                                @endcan
                                @can('View team')
                                <a href="{{ $teamRoute }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.teams.*'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Teams Reports
                                </a>
                                @endcan
                                @canany(['Pointage reports', 'View pointage'])
                                <a href="{{ $pointageRoute }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.tasks.pointage'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Pointage Reports
                                </a>
                                @endcanany
                                @can('Manage workDaysReports')
                                <a href="{{ $wdRoute }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.workDaysReports.*'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Work Days Reports
                                </a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        @can('Create invoice')
                        <div>
                            <button type="button" onclick="toggleDropdown('invoices')" title="Invoices"
                                class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                                @if($isInvoicesPage)
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                    </svg>
                                    <span class="sidebar-label truncate">{{ __('messages.Invoices') }}</span>
                                </span>
                                <svg id="invoices-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="sidebar-tip">{{ __('messages.Invoices') }}</span>
                            </button>
                            <div id="invoices-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                                <a href="{{ $routeFor('invoices', 'fullinvoice') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.invoices.fullinvoice'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Generate
                                </a>
                                <a href="{{ $routeFor('invoices', 'invoicehistory') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.invoices.invoicehistory'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    History
                                </a>
                                <a href="{{ $routeFor('invoices', 'parameters') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.invoices.parameters'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Parameters
                                </a>
                            </div>
                        </div>
                        @endcan

                        @can('Create devis')
                        <div>
                            <button type="button" onclick="toggleDropdown('devis')" title="Devis"
                                class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                                @if($isDevisPage)
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.586a2 2 0 010 2.828L11.828 15H9v-2.828l8.162-8.162a2 2 0 012.828 0z" />
                                    </svg>
                                    <span class="sidebar-label truncate">{{ __('messages.Devis') }}</span>
                                </span>
                                <svg id="devis-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="sidebar-tip">{{ __('messages.Devis') }}</span>
                            </button>
                            <div id="devis-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                                <a href="{{ $routeFor('devis', 'generateDevis') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.devis.generateDevis'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Generate
                                </a>
                                <a href="{{ $routeFor('devis', 'devishistory') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.devis.devishistory'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    History
                                </a>
                                <a href="{{ $routeFor('devis', 'parameters') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.devis.parameters'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Parameters
                                </a>
                            </div>
                        </div>
                        @endcan

                        @can('Create expense note')
                        <div>
                            <button type="button" onclick="toggleDropdown('expense')" title="Expense Note"
                                class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                                @if($isExpensePage)
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                <span class="flex items-center gap-3 min-w-0">
                                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18v2.2" />
                                    </svg>
                                    <span class="sidebar-label truncate">{{ __('messages.Expense Note') }}</span>
                                </span>
                                <svg id="expense-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                                <span class="sidebar-tip">{{ __('messages.Expense Note') }}</span>
                            </button>
                            <div id="expense-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                                <a href="{{ $routeFor('notedefrais', 'generateNoteDeFrais') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.notedefrais.generateNoteDeFrais'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Generate
                                </a>
                                <a href="{{ $routeFor('notedefrais', 'notedefraishistory') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.notedefrais.notedefraishistory'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    History
                                </a>
                                <a href="{{ $routeFor('notedefrais', 'parameters') }}"
                                    class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                                    @if(request()->routeIs('*.notedefrais.parameters'))
                                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                    @endif
                                    Parameters
                                </a>
                            </div>
                        </div>
                        @endcan
                    </div>
                </div>

                {{-- SYSTEM --}}
                @can('Billings')
                <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.System') }}</p>
                    <div class="space-y-1">
                        <a href="{{ $billRoute }}"
                            title="Billings"
                            class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('*.Enterprise_infos.create') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                            @if(request()->routeIs('*.Enterprise_infos.create'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span class="sidebar-label truncate">{{ __('messages.Billings') }}</span>
                            <span class="sidebar-tip">{{ __('messages.Billings') }}</span>
                        </a>
                    </div>
                </div>
                @endcan
            </nav>
        </div>

        <div class="p-3 border-t border-white/10">
            <div class="sidebar-user-row flex items-center gap-3 cursor-pointer hover:bg-white/10 rounded-xl p-2 transition-colors"
                onclick="toggleDropdown('user')">
                <div class="flex items-center gap-3 min-w-0 flex-1">
                    <div
                        class="w-9 h-9 flex-shrink-0 rounded-full bg-[#E7B84B] flex items-center justify-center text-[#0B2638] text-[13px] font-bold">
                        {{ strtoupper(substr(trim(Auth::user()->name ?? ''), 0, 2)) ?: 'U' }}
                    </div>
                    <div class="sidebar-user-meta min-w-0">
                        <h4 class="text-[13px] font-semibold text-white truncate leading-tight">{{ Auth::user()->name ?? 'John Doe' }}</h4>
                        <p class="text-[11.5px] text-slate-400 truncate">{{ Auth::user()->getRoleNames()->first() ?? 'User' }}</p>
                    </div>
                </div>
                <svg id="user-icon" class="sidebar-label w-4 h-4 flex-shrink-0 text-gray-400 user-dropdown-icon" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <div id="user-submenu"
                class="absolute bottom-20 left-3 right-3 bg-white border border-slate-200 rounded-xl shadow-xl py-2 hidden z-50">
                @if($canAccessProfile)
                <a href="{{ route('profile') }}"
                    class="block px-4 py-2 text-[13px] text-slate-700 hover:bg-[#2D7080]/10 hover:text-[#2D7080] transition-colors">{{ __('messages.Profile') }}</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-4 py-2 text-[13px] text-slate-700 hover:bg-[#2D7080]/10 hover:text-[#2D7080] transition-colors">{{ __('messages.Logout') }}</button>
                </form>
            </div>
        </div>
    </aside>

    <script>
        function toggleDropdown(id) {
            // If desktop sidebar is collapsed and user opens a group, expand first for usability
            if (window.innerWidth >= 1024 && document.body.classList.contains('dt-sidebar-collapsed') && id !== 'user') {
                setDesktopSidebar(false);
            }

            const submenu = document.getElementById(id + '-submenu');
            const icon = document.getElementById(id + '-icon');
            if (!submenu) return;

            const isActive = submenu.classList.contains('active');

            if (isActive) {
                submenu.classList.remove('active');
                if (icon) icon.classList.remove('rotated');
            } else {
                submenu.classList.add('active');
                if (icon) icon.classList.add('rotated');
            }

            // Special handling for user menu (popup style)
            if (id === 'user') {
                if (isActive) {
                    submenu.classList.add('hidden');
                } else {
                    submenu.classList.remove('hidden');
                }
            }
        }

        function setDesktopSidebar(collapsed) {
            const btn = document.getElementById('sidebarCollapseBtn');
            const icon = document.getElementById('sidebarCollapseIcon');
            document.body.classList.toggle('dt-sidebar-collapsed', collapsed);
            try { localStorage.setItem('dalytrac-sidebar-collapsed', collapsed ? '1' : '0'); } catch (e) {}
            if (icon) icon.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
            if (btn) btn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        }

        function toggleDesktopSidebar(force) {
            const current = document.body.classList.contains('dt-sidebar-collapsed');
            const next = typeof force === 'boolean' ? force : !current;
            setDesktopSidebar(next);
        }

        (function initDesktopSidebar() {
            try {
                const saved = localStorage.getItem('dalytrac-sidebar-collapsed') === '1';
                if (saved && window.innerWidth >= 1024) setDesktopSidebar(true);
            } catch (e) {}

            // Auto-open submenu containing the active route
            document.querySelectorAll('#appSidebar .sidebar-submenu').forEach(function (menu) {
                if (menu.querySelector('span.bg-\\[\\#E7B84B\\]')) {
                    menu.classList.add('active');
                    const btn = menu.previousElementSibling;
                    const chev = btn ? btn.querySelector('.dropdown-icon') : null;
                    if (chev) chev.classList.add('rotated');
                }
            });
        })();

        // Close user dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const userSubmenu = document.getElementById('user-submenu');
            if (!userSubmenu || userSubmenu.classList.contains('hidden')) return;
            const userIcon = document.getElementById('user-icon');
            const userArea = userSubmenu.previousElementSibling;
            if (userArea && !userArea.contains(event.target) && !userSubmenu.contains(event.target)) {
                userSubmenu.classList.add('hidden');
                if (userIcon) userIcon.classList.remove('rotated');
            }
        });
    </script>

