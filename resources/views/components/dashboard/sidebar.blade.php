<style>
        .dropdown-menu {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out, opacity 0.3s ease-out, margin-top 0.3s ease-out;
        }

        .dropdown-menu.active {
            max-height: 500px;
            opacity: 1;
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
            background: #3b82f6;
            transition: height 0.2s ease;
        }

        .submenu-item:hover::before {
            height: 60%;
        }
</style>

{{-- Mobile backdrop --}}
<div id="appSidebarBackdrop" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden" onclick="toggleAppSidebar(false)"></div>

<aside
    id="appSidebar"
    class="hidden fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex-col justify-between h-screen overflow-y-auto transform -translate-x-full transition-transform duration-200 ease-out lg:static lg:sticky lg:top-0 lg:translate-x-0 lg:flex"
>
        <div>
            <div class="px-6 pt-3 pb-1 flex items-center overflow-hidden">
                <img
                    src="{{ asset('img/dalytraclogo.png') }}"
                    alt="{{ __('messages.DalyTrac Logo') }}"
                    class="h-18 w-auto object-contain"
                /> 
            </div>
            <nav class="px-4 space-y-1 mt-2">
                <a href="{{route('admin.index') }}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-blue-500 text-white font-medium transition-colors relative">
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                    </div>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>{{ __('messages.Dashboard') }}</span>
                </a>

                @canany(['View client', 'View employee', 'View teams', 'View project', 'View task', 'Pointage reports', 'View pointage', 'Manage workDaysReports'])
                <div>
                    <button type="button" onclick="toggleDropdown('reports')"
                        class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                        @php
                            $isReportsPage = request()->routeIs('admin.clients.index') ||
                                request()->routeIs('admin.employees.index') ||
                                request()->routeIs('admin.teams.index') ||
                                request()->routeIs('admin.projects.index') ||
                                request()->routeIs('admin.tasks.index') ||
                                request()->routeIs('admin.tasks.pointage') ||
                                request()->routeIs('admin.workDaysReports.*');
                        @endphp
                        @if($isReportsPage)
                            <div
                                class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                            </div>
                        @endif
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>{{ __('messages.Reports') }}</span>
                        </span>
                        <svg id="reports-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="reports-submenu" class="ml-8 space-y-1 dropdown-menu">
                        @can('View client')
                        <a href="{{ route('admin.clients.index') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.clients.index'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Clients Reports') }}
                        </a>
                        @endcan
                        @can('View employee')
                        <a href="{{ route('admin.employees.index') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.employees.index'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Employees Reports') }}
                        </a>
                        @endcan
                        @can('View teams')
                        <a href="{{ route('admin.teams.index') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.teams.index'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Teams Reports') }}
                        </a>
                        @endcan
                        @can('View project')
                        <a href="{{ route('admin.projects.index') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.projects.index'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Projects Reports') }}
                        </a>
                        @endcan
                        @can('View task')
                        <a href="{{ route('admin.tasks.index') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.tasks.index'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Tasks Reports') }}
                        </a>
                        @endcan
                        @canany(['Pointage reports', 'View pointage'])
                        <a href="{{ route('admin.tasks.pointage') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.tasks.pointage'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Pointage Reports') }}
                        </a>
                        @endcanany
                        @can('Manage workDaysReports')
                        <a href="{{ route('admin.workDaysReports.index') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.workDaysReports.*'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Works Days Report') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                @canany(['Create invoice', 'View invoices', 'Send invoice'])
                <div>
                    <button type="button" onclick="toggleDropdown('invoices')"
                        class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                        @php
                            $isInvoicesPage = request()->routeIs('admin.invoices.fullinvoice') ||
                                request()->routeIs('admin.invoices.generate.*') ||
                                request()->routeIs('admin.invoices.invoicehistory') ||
                                request()->routeIs('admin.invoices.parameters') ||
                                request()->routeIs('admin.invoice.creditNoteHistory');
                        @endphp
                        @if($isInvoicesPage)
                            <div
                                class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                            </div>
                        @endif
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>{{ __('messages.Invoices') }}</span>
                        </span>
                        <svg id="invoices-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="invoices-submenu" class="ml-8 space-y-1 dropdown-menu">
                        @can('Create invoice')
                        <a href="{{ route('admin.invoices.fullinvoice') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.fullinvoice'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Generate') }}
                        </a>
                        <a href="{{ route('admin.invoices.generate.Detailed') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.generate.*'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Generate Detailed') }}
                        </a>
                        <a href="{{ route('admin.invoices.parameters') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.parameters'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Parameters') }}
                        </a>
                        @endcan
                        @can('View invoices')
                        <a href="{{ route('admin.invoices.invoicehistory') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoices.invoicehistory'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.History') }}
                        </a>
                        <a href="{{ route('admin.invoice.creditNoteHistory') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.invoice.creditNoteHistory'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Credit Note History') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                @canany(['Create devis', 'view devis', 'Send devis'])
                <div>
                    <button type="button" onclick="toggleDropdown('quotes')"
                        class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                        @php
                            $isDevisPage = request()->routeIs('admin.devis.generateDevis') ||
                                request()->routeIs('admin.devis.devishistory') ||
                                request()->routeIs('admin.devis.parameters');
                        @endphp
                        @if($isDevisPage)
                            <div
                                class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                            </div>
                        @endif
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.586a2 2 0 010 2.828L11.828 15H9v-2.828l8.162-8.162a2 2 0 012.828 0z" />
                            </svg>
                            <span>{{ __('messages.Quotes') }}</span>
                        </span>
                        <svg id="quotes-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="quotes-submenu" class="ml-8 space-y-1 dropdown-menu">
                        @can('Create devis')
                        <a href="{{ route('admin.devis.generateDevis') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.devis.generateDevis'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Generate') }}
                        </a>
                        <a href="{{ route('admin.devis.parameters') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.devis.parameters'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Parameters') }}
                        </a>
                        @endcan
                        @can('view devis')
                        <a href="{{ route('admin.devis.devishistory') }}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.devis.devishistory'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.History') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                @can('Create expense note')
                <div>
                    <button type="button" onclick="toggleDropdown('expense')"
                        class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                        @php
                            $isExpensePage = request()->routeIs('admin.notedefrais.generateNoteDeFrais') ||
                                request()->routeIs('admin.notedefrais.notedefraishistory') ||
                                request()->routeIs('admin.notedefrais.parameters');
                        @endphp
                        @if($isExpensePage)
                            <div
                                class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                            </div>
                        @endif
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 17.345a4.76 4.76 0 0 0 2.558 1.618c2.274.589 4.512-.446 4.999-2.31.487-1.866-1.273-3.9-3.546-4.49-2.273-.59-4.034-2.623-3.547-4.488.486-1.865 2.724-2.899 4.998-2.31.982.236 1.87.793 2.538 1.592m-3.879 12.171V21m0-18v2.2" />
                            </svg>
                            <span>{{ __('messages.Expense Notes') }}</span>
                        </span>
                        <svg id="expense-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="expense-submenu" class="ml-8 space-y-1 dropdown-menu">
                        <a href="{{route('admin.notedefrais.generateNoteDeFrais')}}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.notedefrais.generateNoteDeFrais'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Generate') }}
                        </a>
                        <a href="{{route('admin.notedefrais.notedefraishistory')}}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.notedefrais.notedefraishistory'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.History') }}
                        </a>
                        <a href="{{route('admin.notedefrais.parameters')}}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.notedefrais.parameters'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Parameters') }}
                        </a>
                    </div>
                </div>
                @endcan

                @canany(['Pointage', 'View pointage'])
                <a href="{{route('admin.pointage.index')}}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                    @if(request()->routeIs('admin.pointage.*'))
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                        </div>
                    @endif
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ __('messages.Time Tracking Menu') }}</span>
                </a>
                @endcanany

                @can('Planning')
                <a href="{{route('admin.calendar.index')}}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                    @if(request()->routeIs('admin.calendar.*'))
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                        </div>
                    @endif
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>{{ __('messages.Planning Menu') }}</span>
                </a>
                @endcan

               
                @canany(['Assign Superadmin role', 'Edit employee roles'])
                <div>
                    <button type="button" onclick="toggleDropdown('admin')"
                        class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                        @php
                            $isAdminPage = request()->routeIs('admin.roles.index') ||
                                request()->routeIs('admin.permissions.index');
                        @endphp
                        @if($isAdminPage)
                            <div
                                class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                            </div>
                        @endif
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>{{ __('messages.Administration Menu') }}</span>
                        </span>
                        <svg id="admin-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="admin-submenu" class="ml-8 space-y-1 dropdown-menu">
                        @canany(['Assign Superadmin role', 'Edit employee roles'])
                        <a href="{{route('admin.roles.index')}}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.roles.index'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Roles') }}
                        </a>
                        @endcanany
                        @can('Assign Superadmin role')
                        <a href="{{route('admin.permissions.index')}}"
                            class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200 relative">
                            @if(request()->routeIs('admin.permissions.index'))
                                <div
                                    class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-6 bg-blue-600 rounded-r-full">
                                </div>
                            @endif
                            {{ __('messages.Permissions') }}
                        </a>
                        @endcan
                    </div>
                </div>
                @endcanany

                <a href="{{route('admin.Enterprise_infos.index')}}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                    @if(request()->routeIs('admin.Enterprise_infos.index'))
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                        </div>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span>{{ __('messages.Company Info') }}</span>
                </a>

                @can('Billings')
                <a href="{{route('admin.Enterprise_infos.create')}}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative">
                    @if(request()->routeIs('admin.Enterprise_infos.create'))
                        <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full">
                        </div>
                    @endif
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>{{ __('messages.Billing Menu') }}</span>
                </a>
                @endcan

                @php
                    $currentUser = Auth::user();
                @endphp
                @if($currentUser && (int) ($currentUser->entreprise_id ?? 0) === 43)
                <a href="{{ route('manager.direct-dashboard') }}"
                    class="flex items-center space-x-3 px-4 py-3 rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    <span>Manager Dashboard</span>
                </a>
                @endif

                {{-- Help / Support: enterprise-optimized ticket interface --}}
                <a href="{{ route('admin.support.create') }}"
                    class="mt-2 flex items-center space-x-3 px-4 py-3 rounded-xl text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors relative">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9.879 7.519c1.171-1.172 3.071-1.172 4.242 0 1.171 1.17 1.171 3.07 0 4.242-.414.414-.879.746-1.379 1.005-.56.29-.743.79-.743 1.234M12 17h.007v.008H12V17z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="font-medium">Help &amp; Support</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-200 relative">
            <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 rounded-xl p-2 -m-2 transition-colors"
                onclick="toggleDropdown('user')">
                <div class="flex items-center space-x-3">
                    <div
                        class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                        JD
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'John Doe' }}</h4>
                        <p class="text-xs text-gray-500">Admin</p>
                    </div>
                </div>
                <svg id="user-icon" class="w-4 h-4 text-gray-400 user-dropdown-icon" fill="none" stroke="currentColor"
                    stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <div id="user-submenu"
                class="absolute bottom-full mb-2 left-4 right-4 bg-white border border-gray-200 rounded-xl shadow-lg py-2 hidden z-10">
                <a href="{{ route('profile') }}"
                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">{{ __('messages.Profile') }}</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">{{ __('messages.Logout') }}</button>
                </form>
            </div>
        </div>
    </aside>

    <script>
        function toggleDropdown(id) {
            const submenu = document.getElementById(id + '-submenu');
            const icon = document.getElementById(id + '-icon');

            const isActive = submenu.classList.contains('active');

            if (isActive) {
                submenu.classList.remove('active');
                icon.classList.remove('rotated');
            } else {
                submenu.classList.add('active');
                icon.classList.add('rotated');
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

        // Close user dropdown when clicking outside
        document.addEventListener('click', function (event) {
            const userSubmenu = document.getElementById('user-submenu');
            const userIcon = document.getElementById('user-icon');
            const userArea = userSubmenu.previousElementSibling;

            if (!userArea.contains(event.target) && !userSubmenu.contains(event.target)) {
                userSubmenu.classList.add('hidden');
                userIcon.classList.remove('rotated');
            }
        });
    </script>