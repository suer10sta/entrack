<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BizManager Sidebar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col justify-between h-screen">
        <div>
            <!-- Logo Section -->
            <div class="p-6 flex items-center space-x-3">
                <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">BizManager</h1>
                    <p class="text-xs text-gray-500">Pro</p>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="px-4 space-y-1 mt-2">
                <!-- Dashboard -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl bg-blue-500 text-white font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Reports with Dropdown -->
                <div>
                    <button type="button" onclick="toggleDropdown('reports')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                            </svg>
                            <span>Reports</span>
                        </span>
                        <svg id="reports-icon" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div id="reports-submenu" class="ml-11 mt-1 space-y-1 hidden overflow-hidden">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Client Reports</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Employees Reports</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Teams Reports</a>
                    </div>
                </div>

                <!-- Invoices with Dropdown -->
                <div>
                    <button type="button" onclick="toggleDropdown('invoices')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Invoices</span>
                        </span>
                        <svg id="invoices-icon" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="invoices-submenu" class="ml-11 mt-1 space-y-1 hidden overflow-hidden">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Generate</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Generates Reports</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">History</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Parametres</a>
                    </div>
                </div>

                <!-- Devis (Quotes) with Dropdown -->
                <div>
                    <button type="button" onclick="toggleDropdown('quotes')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Devis</span>
                        </span>
                        <svg id="quotes-icon" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="quotes-submenu" class="ml-11 mt-1 space-y-1 hidden overflow-hidden">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Generate</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">History</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Parametre</a>
                    </div>
                </div>

                <!-- Expense Notes with Dropdown -->
                <div>
                    <button type="button" onclick="toggleDropdown('expense')" class="flex justify-between items-center w-full px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors">
                        <span class="flex items-center space-x-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span>Expense Notes</span>
                        </span>
                        <svg id="expense-icon" class="w-4 h-4 flex-shrink-0 transition-transform duration-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                    <div id="expense-submenu" class="ml-11 mt-1 space-y-1 hidden overflow-hidden">
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Generate</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">History</a>
                        <a href="#" class="block px-4 py-2 text-sm text-gray-500 hover:text-blue-500">Parametre</a>
                    </div>
                </div>

                <!-- Time Tracking -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>Time Tracking</span>
                </a>

                <!-- Administration -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>Administration</span>
                </a>

                <!-- Company Info -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Company Info</span>
                </a>

                <!-- Billing -->
                <a href="#" class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                    <span>Billing</span>
                </a>
            </nav>
        </div>

        <!-- User Profile Section -->
        <div class="p-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                        JD
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-900">John Doe</h4>
                        <p class="text-xs text-gray-500">Admin</p>
                    </div>
                </div>
                <svg class="w-5 h-5 text-gray-400 cursor-pointer hover:text-gray-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
            </div>
        </div>
    </aside>

    <script>
        function toggleDropdown(id) {
            const submenu = document.getElementById(id + '-submenu');
            const icon = document.getElementById(id + '-icon');
            
            if (submenu.classList.contains('hidden')) {
                submenu.classList.remove('hidden');
                // Rotate the chevron icon
                if (id === 'reports') {
                    icon.style.transform = 'rotate(180deg)';
                } else {
                    icon.style.transform = 'rotate(90deg)';
                }
            } else {
                submenu.classList.add('hidden');
                icon.style.transform = 'rotate(0deg)';
            }
        }
    </script>