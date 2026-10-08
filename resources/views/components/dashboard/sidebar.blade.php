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
    class="hidden fixed inset-y-0 left-0 z-50 bg-[#0B2638] border-r border-white/10 flex-col justify-between h-screen overflow-y-auto dt-sidebar-scroll transform -translate-x-full transition-all duration-300 ease-out lg:relative lg:inset-auto lg:translate-x-0 lg:flex lg:shrink-0"
>
        <div class="flex-1 flex flex-col min-h-0">
            {{-- Brand + collapse toggle --}}
            <div class=" px-4 pt-5 pb-5 flex items-center">
                <a href="#top" aria-label="DalyTrac, accueil" class="flex items-center gap-2.5 min-w-0 flex-1">
                    <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-[#E7B84B] flex items-center justify-center text-[#0B2638] font-extrabold text-[15px] tracking-tight">D</span>
                    <span class="sidebar-logo-text inline-flex items-baseline text-[1.35rem] font-extrabold tracking-[-0.04em] text-white no-underline leading-none"><span>Daly</span><strong class="font-extrabold text-[#7fb3bf]">Trac</strong></span>
                </a>
                {{-- Desktop collapse toggle --}}
                <!-- <button
                    type="button"
                    id="sidebarCollapseBtn"
                    onclick="toggleDesktopSidebar()"
                    aria-label="Collapse sidebar"
                    title="Collapse / Expand"
                    class="hidden lg:inline-flex flex-shrink-0 w-8 h-8 items-center justify-center rounded-lg border border-white/15 text-slate-300 hover:text-white hover:bg-white/10 transition-colors"
                >
                    <svg id="sidebarCollapseIcon" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                </button> -->
            </div>

            <nav class="px-3 pb-6 space-y-5 overflow-y-auto dt-sidebar-scroll flex-1">
                {{-- OVERVIEW --}}
                <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Overview') }}</p>
                    <div class="space-y-1">
                <a href="{{route('admin.index') }}"
                    title="{{ __('messages.Dashboard') }}"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('admin.index') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @if(request()->routeIs('admin.index'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span class="sidebar-label truncate">{{ __('messages.Dashboard') }}</span>
                    <span class="sidebar-tip">{{ __('messages.Dashboard') }}</span>
                </a>
                {{-- Projects — directly below Dashboard --}}
                @can('View project')
                <a href="{{ route('admin.projects.index') }}" title="{{ __('messages.projects_page_title') }}"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] transition-colors {{ request()->routeIs('admin.projects.index') || request()->routeIs('admin.projects.show') || request()->routeIs('admin.projects.edit') || request()->routeIs('admin.projects.create') || request()->routeIs('admin.tasks.create') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @if(request()->routeIs('admin.projects.index') || request()->routeIs('admin.projects.show') || request()->routeIs('admin.projects.edit') || request()->routeIs('admin.projects.create') || request()->routeIs('admin.tasks.create'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/>
                    </svg>
                    <span class="sidebar-label truncate">{{ __('messages.projects_page_title') }}</span>
                    <span class="sidebar-tip">{{ __('messages.projects_page_title') }}</span>
                </a>
                @endcan
                {{-- Tasks — directly below Projects --}}
                @can('View task')
                <a href="{{ route('admin.tasks.board') }}" title="{{ __('messages.Tasks') }}"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] transition-colors {{ request()->routeIs('admin.tasks.board') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @if(request()->routeIs('admin.tasks.board'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="sidebar-label truncate">{{ __('messages.Tasks') }}</span>
                    <span class="sidebar-tip">{{ __('messages.Tasks') }}</span>
                </a>
                @endcan
                    </div>
                </div>

                {{-- WORKSPACE --}}
                <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Workspace') }}</p>
                    <div class="space-y-1">
                @canany(['View client', 'View employee', 'View teams', 'View project', 'View task', 'Pointage reports', 'View pointage', 'Manage workDaysReports'])
                <div>
                    <button type="button" onclick="toggleDropdown('reports')" title="{{ __('messages.Reports') }}"
                        class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                        @php
                            $isReportsPage = request()->routeIs('admin.clients.index') ||
                                request()->routeIs('admin.employees.index') ||
                                request()->routeIs('admin.teams.index') ||
                                request()->routeIs('admin.projects.index') ||
                                request()->routeIs('admin.projects.report') ||
                                request()->routeIs('admin.tasks.index') ||
                                request()->routeIs('admin.tasks.pointage') ||
                                request()->routeIs('admin.workDaysReports.*');
                        @endphp
                        @if($isReportsPage)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span class="sidebar-label truncate">{{ __('messages.Reports') }}</span>
                        </span>
                        <svg id="reports-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="sidebar-tip">{{ __('messages.Reports') }}</span>
                    </button>
                    <div id="reports-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                        @can('View client')
                        <a href="{{ route('admin.clients.index') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.clients.index'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Clients Reports') }}
                        </a>
                        @endcan
                        @can('View employee')
                        <a href="{{ route('admin.employees.index') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.employees.index'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Employees Reports') }}
                        </a>
                        @endcan
                        @can('View teams')
                        <a href="{{ route('admin.teams.index') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.teams.index'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Teams Reports') }}
                        </a>
                        @endcan
                        @can('View project')
                        <a href="{{ route('admin.projects.report') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.projects.report'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Projects Reports') }}
                        </a>
                        @endcan
                        @can('View task')
                        <a href="{{ route('admin.tasks.index') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.tasks.index'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Tasks Reports') }}
                        </a>
                        @endcan
                        @canany(['Pointage reports', 'View pointage'])
                        <a href="{{ route('admin.tasks.pointage') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.tasks.pointage'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Pointage Reports') }}
                        </a>
                        @endcanany
                        @can('Manage workDaysReports')
                        <a href="{{ route('admin.workDaysReports.index') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.workDaysReports.*'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Works Days Report') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                @canany(['Create invoice', 'View invoices', 'Send invoice'])
                <div>
                    <button type="button" onclick="toggleDropdown('invoices')" title="{{ __('messages.Invoices') }}"
                        class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                        @php
                            $isInvoicesPage = request()->routeIs('admin.invoices.fullinvoice') ||
                                request()->routeIs('admin.invoices.generate.*') ||
                                request()->routeIs('admin.invoices.invoicehistory') ||
                                request()->routeIs('admin.invoices.parameters') ||
                                request()->routeIs('admin.invoice.creditNoteHistory');
                        @endphp
                        @if($isInvoicesPage)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span class="sidebar-label truncate">{{ __('messages.Invoices') }}</span>
                        </span>
                        <svg id="invoices-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="sidebar-tip">{{ __('messages.Invoices') }}</span>
                    </button>
                    <div id="invoices-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                        @can('Create invoice')
                        <a href="{{ route('admin.invoices.fullinvoice') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.fullinvoice'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Generate') }}
                        </a>
                        <a href="{{ route('admin.invoices.generate.Detailed') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.generate.*'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Generate Detailed') }}
                        </a>
                        <a href="{{ route('admin.invoices.parameters') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.parameters'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Parameters') }}
                        </a>
                        @endcan
                        @can('View invoices')
                        <a href="{{ route('admin.invoices.invoicehistory') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.invoicehistory'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.History') }}
                        </a>
                        <a href="{{ route('admin.invoice.creditNoteHistory') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoice.creditNoteHistory'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Credit Note History') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                @canany(['Create devis', 'view devis', 'Send devis'])
                <div>
                    <button type="button" onclick="toggleDropdown('quotes')" title="{{ __('messages.Quotes') }}"
                        class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                        @php
                            $isDevisPage = request()->routeIs('admin.devis.generateDevis') ||
                                request()->routeIs('admin.devis.devishistory') ||
                                request()->routeIs('admin.devis.parameters');
                        @endphp
                        @if($isDevisPage)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.586a2 2 0 010 2.828L11.828 15H9v-2.828l8.162-8.162a2 2 0 012.828 0z" />
                            </svg>
                            <span class="sidebar-label truncate">{{ __('messages.Quotes') }}</span>
                        </span>
                        <svg id="quotes-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="sidebar-tip">{{ __('messages.Quotes') }}</span>
                    </button>
                    <div id="quotes-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                        @can('Create devis')
                        <a href="{{ route('admin.devis.generateDevis') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.devis.generateDevis'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Generate') }}
                        </a>
                        <a href="{{ route('admin.devis.parameters') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.devis.parameters'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Parameters') }}
                        </a>
                        @endcan
                        @can('view devis')
                        <a href="{{ route('admin.devis.devishistory') }}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.devis.devishistory'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.History') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                @can('Create expense note')
                <div>
                    <button type="button" onclick="toggleDropdown('expense')" title="{{ __('messages.Expense Notes') }}"
                        class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                        @php
                            $isExpensePage = request()->routeIs('admin.notedefrais.generateNoteDeFrais') ||
                                request()->routeIs('admin.notedefrais.notedefraishistory') ||
                                request()->routeIs('admin.notedefrais.parameters');
                        @endphp
                        @if($isExpensePage)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18v2.2" />
                            </svg>
                            <span class="sidebar-label truncate">{{ __('messages.Expense Notes') }}</span>
                        </span>
                        <svg id="expense-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="sidebar-tip">{{ __('messages.Expense Notes') }}</span>
                    </button>
                    <div id="expense-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                        <a href="{{route('admin.notedefrais.generateNoteDeFrais')}}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.notedefrais.generateNoteDeFrais'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Generate') }}
                        </a>
                        <a href="{{route('admin.notedefrais.notedefraishistory')}}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.notedefrais.notedefraishistory'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.History') }}
                        </a>
                        <a href="{{route('admin.notedefrais.parameters')}}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.notedefrais.parameters'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Parameters') }}
                        </a>
                    </div>
                </div>
                @endcan

                @canany(['Pointage', 'View pointage'])
                <a href="{{route('admin.pointage.index')}}" title="{{ __('messages.Time Tracking Menu') }}"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] transition-colors {{ request()->routeIs('admin.pointage.*') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @if(request()->routeIs('admin.pointage.*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="sidebar-label truncate">{{ __('messages.Time Tracking Menu') }}</span>
                    <span class="sidebar-tip">{{ __('messages.Time Tracking Menu') }}</span>
                </a>
                @endcanany

                @can('Planning')
                <a href="{{route('admin.calendar.index')}}" title="{{ __('messages.Planning Menu') }}"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] transition-colors {{ request()->routeIs('admin.calendar.*') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @if(request()->routeIs('admin.calendar.*'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                    @endif
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="sidebar-label truncate">{{ __('messages.Planning Menu') }}</span>
                    <span class="sidebar-tip">{{ __('messages.Planning Menu') }}</span>
                </a>
                @endcan
                    </div>
                </div>

                {{-- SYSTEM --}}
                <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.System') }}</p>
                    <div class="space-y-1">
                @canany(['Assign Superadmin role', 'Edit employee roles'])
                <div>
                    <button type="button" onclick="toggleDropdown('admin')" title="{{ __('messages.Administration Menu') }}"
                        class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                        @php
                            $isAdminPage = request()->routeIs('admin.roles.index') ||
                                request()->routeIs('admin.permissions.index');
                        @endphp
                        @if($isAdminPage)
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <span class="flex items-center gap-3 min-w-0">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span class="sidebar-label truncate">{{ __('messages.Administration Menu') }}</span>
                        </span>
                        <svg id="admin-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                        <span class="sidebar-tip">{{ __('messages.Administration Menu') }}</span>
                    </button>
                    <div id="admin-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu">
                        @canany(['Assign Superadmin role', 'Edit employee roles'])
                        <a href="{{route('admin.roles.index')}}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.roles.index'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Roles') }}
                        </a>
                        @endcanany
                        @can('Assign Superadmin role')
                        <a href="{{route('admin.permissions.index')}}"
                            class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.permissions.index'))
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            {{ __('messages.Permissions') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                <a href="{{route('admin.Enterprise_infos.index')}}" title="{{ __('messages.Company Info') }}"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] transition-colors {{ request()->routeIs('admin.Enterprise_infos.index') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @if(request()->routeIs('admin.Enterprise_infos.index'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span class="sidebar-label truncate">{{ __('messages.Company Info') }}</span>
                    <span class="sidebar-tip">{{ __('messages.Company Info') }}</span>
                </a>

                @can('Billings')
                <a href="{{route('admin.Enterprise_infos.create')}}" title="{{ __('messages.Billing Menu') }}"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] transition-colors {{ request()->routeIs('admin.Enterprise_infos.create') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                    @if(request()->routeIs('admin.Enterprise_infos.create'))
                        <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span class="sidebar-label truncate">{{ __('messages.Billing Menu') }}</span>
                    <span class="sidebar-tip">{{ __('messages.Billing Menu') }}</span>
                </a>
                @endcan

                @php
                    $currentUser = Auth::user();
                @endphp
                @if($currentUser && (int) ($currentUser->entreprise_id ?? 0) === 43)
                <a href="{{ route('manager.direct-dashboard') }}" title="Manager Dashboard"
                    class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] text-white bg-white/10 hover:bg-white/20 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.8" stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span class="sidebar-label truncate">Manager Dashboard</span>
                    <span class="sidebar-tip">Manager Dashboard</span>
                </a>
                @endif

                {{-- Help / Support --}}
                <a href="{{ route('admin.support.create') }}" title="Help & Support"
                    class="dt-nav-item group relative mt-2 flex items-center gap-3 px-3 py-2.5 rounded-xl bg-[#E7B84B] text-[#0B2638] hover:bg-[#d9a93c] transition-colors text-[13.5px] font-semibold">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                        stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.879 7.519c1.171-1.172 3.071-1.172 4.242 0 1.171 1.17 1.171 3.07 0 4.242-.414.414-.879.746-1.379 1.005-.56.29-.743.79-.743 1.234M12 17h.007v.008H12V17z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="sidebar-label truncate">Help &amp; Support</span>
                    <span class="sidebar-tip">Help &amp; Support</span>
                </a>
                    </div>
                </div>
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
                        <p class="text-[11.5px] text-slate-400 truncate">{{ Auth::user()->getRoleNames()->first() ?? 'Admin' }}</p>
                    </div>
                </div>
                <svg id="user-icon" class="sidebar-label w-4 h-4 flex-shrink-0 text-gray-400 user-dropdown-icon" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <div id="user-submenu"
                class="absolute bottom-20 left-3 right-3 bg-white border border-slate-200 rounded-xl shadow-xl py-2 hidden z-50">
                <a href="{{ route('profile') }}"
                    class="block px-4 py-2 text-[13px] text-slate-700 hover:bg-[#2D7080]/10 hover:text-[#2D7080] transition-colors">{{ __('messages.Profile') }}</a>
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
                if (menu.querySelector('.bg-brand-accent, span.bg-\\[\\#E7B84B\\]')) {
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

