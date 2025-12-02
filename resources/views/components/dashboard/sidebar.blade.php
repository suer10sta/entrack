<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DailyTrac Sidebar</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
</head>
<body class="bg-gray-100">
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between h-screen">
        <div>
            <div class="p-6 flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">DailyTrac</h1>
                    <p class="text-xs text-gray-500">Pro</p>
                </div>
            </div>
            <nav class="px-4 space-y-1 mt-2">
                <a href="{{route('admin.index') }}" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-blue-500 text-white font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <div>
                    <button type="button" onclick="toggleDropdown('reports')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Reports</span>
                        </span> 
                        <svg id="reports-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="reports-submenu" class="ml-8 space-y-1 dropdown-menu">
                        <a href="{{ route('admin.clients.index') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Clients Reports</a>
                        <a href="{{ route('admin.employees.index') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Employees Reports</a>
                        <a href="{{ route('admin.teams.index') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Teams Reports</a>
                        <a href="{{ route('admin.projects.index') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Projects Reports</a>
                        <a href="{{ route('admin.tasks.index') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Tasks Reports</a>
                        <a href="{{ route('admin.tasks.pointage') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Pointage Reports</a>
                        <a href="{{ route('admin.workDaysReports.index') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Works Days Report</a>
                    </div>
                </div>

                <div>
                    <button type="button" onclick="toggleDropdown('invoices')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                            <span>Invoices</span>
                        </span>
                        <svg id="invoices-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="invoices-submenu" class="ml-8 space-y-1 dropdown-menu">
                        <a href="{{ route('admin.invoices.fullinvoice') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Generate</a>
                        <a href="{{ route('admin.invoices.generate.Detailed') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Generates Reports</a>
                        <a href="{{ route('admin.invoices.invoicehistory') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">History</a>
                        <a href="{{ route('admin.invoices.parameters') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Parametres</a>
                        <a href="{{ route('admin.invoice.creditNoteHistory') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Credit Note History</a>
                    </div>
                </div>

                <div>
                    <button type="button" onclick="toggleDropdown('quotes')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.586a2 2 0 010 2.828L11.828 15H9v-2.828l8.162-8.162a2 2 0 012.828 0z" />
                            </svg>
                            <span>Devis</span>
                        </span>
                        <svg id="quotes-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="quotes-submenu" class="ml-8 space-y-1 dropdown-menu">
                        <a href="{{ route('admin.devis.generateDevis') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Generate</a>
                        <a href="{{ route('admin.devis.devishistory') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">History</a>
                        <a href="{{ route('admin.devis.parameters') }}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Parametre</a>
                    </div>
                </div>

                <div>
                    <button type="button" onclick="toggleDropdown('expense')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2zM12 9v6m3-3h-6" />
                        </svg>
                            <span>Expense Notes</span>
                        </span>
                        <svg id="expense-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="expense-submenu" class="ml-8 space-y-1 dropdown-menu">
                        <a href="{{route('admin.notedefrais.generateNoteDeFrais')}}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Generate</a>
                        <a href="{{route('admin.notedefrais.notedefraishistory')}}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">History</a>
                        <a href="{{route('admin.notedefrais.parameters')}}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Parametre</a>
                    </div>
                </div>

                <a href="{{route('admin.pointage.index')}}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Time Tracking</span>
                </a>

                <div>
                    <button type="button" onclick="toggleDropdown('admin')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <span>Administration</span>
                        </span>
                        <svg id="admin-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="admin-submenu" class="ml-8 space-y-1 dropdown-menu">
                        <a href="{{route('admin.roles.index')}}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Roles</a>
                        <a href="{{route('admin.permissions.index')}}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">Permissions</a>
                    </div>
                </div>

                <div>
                    <button type="button" onclick="toggleDropdown('company')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <span>Company Info</span>
                        </span>
                        <svg id="company-icon" class="w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="company-submenu" class="ml-8 space-y-1 dropdown-menu">
                        <a href="{{route('admin.Enterprise_infos.index')}}" class="submenu-item block px-4 py-2 text-sm text-gray-600 hover:text-blue-500 hover:bg-blue-50 rounded-lg transition-all duration-200">reports</a>
                    </div>
                </div>

                <a href="{{route('admin.Enterprise_infos.create')}}" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                    <span>Billing</span>
                </a>
            </nav>
        </div>

        <div class="p-4 border-t border-gray-200 relative">
            <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 rounded-xl p-2 -m-2 transition-colors" onclick="toggleDropdown('user')">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                        JD 
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">{{ Auth::user()->name ?? 'John Doe' }}</h4> 
                        <p class="text-xs text-gray-500">Admin</p> 
                    </div>
                </div>
                <svg id="user-icon" class="w-4 h-4 text-gray-400 user-dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                </svg>
            </div>

            <div id="user-submenu" class="absolute bottom-full mb-2 left-4 right-4 bg-white border border-gray-200 rounded-xl shadow-lg py-2 hidden z-10">
                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">Profile</a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">Deconnexion</button>
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
        document.addEventListener('click', function(event) {
            const userSubmenu = document.getElementById('user-submenu');
            const userIcon = document.getElementById('user-icon');
            const userArea = userSubmenu.previousElementSibling;
            
            if (!userArea.contains(event.target) && !userSubmenu.contains(event.target)) {
                userSubmenu.classList.add('hidden');
                userIcon.classList.remove('rotated');
            }
        });
</script>
</body>
</html>