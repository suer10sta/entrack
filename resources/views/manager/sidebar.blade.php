@php
    $manager = auth('manager')->user();
@endphp

{{-- Mobile backdrop --}}
<div
    id="managerSidebarBackdrop"
    class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden opacity-0 transition-opacity duration-200"
    onclick="toggleManagerSidebar(false)"
></div>

<aside
    id="managerSidebar"
    class="hidden fixed inset-y-0 left-0 z-50 w-64 bg-white border-r border-gray-200 flex-col justify-between h-screen overflow-y-auto custom-scrollbar transform -translate-x-full transition-transform duration-200 ease-out lg:static lg:sticky lg:top-0 lg:translate-x-0 lg:flex"
>
    <div>
        <div class="p-6 flex items-center space-x-3">
        <img
                    src="{{ asset('img/dalytraclogo.png') }}"
                    alt="DalyTrac logo"
                    class="h-18 w-auto object-contain"
                    style="transform: scale(1.8);transform-origin: left center;margin-left: -90px;margin-top: -22px;margin-bottom: -38px;"
                />
        </div>

        <nav class="px-4 space-y-1 mt-2">
            <a href="{{ route('manager.dashboard') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative
                    {{ request()->routeIs('manager.dashboard') ? 'bg-blue-500 text-white' : '' }}">
                @if(request()->routeIs('manager.dashboard'))
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full"></div>
                @endif
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Dashboard</span>
            </a>


            <a href="{{ route('manager.subscriptions.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative
                    {{ request()->routeIs('manager.subscriptions.*') ? 'bg-blue-500 text-white' : '' }}">
                @if(request()->routeIs('manager.subscriptions.*'))
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full"></div>
                @endif
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.598 1M12 8V6m0 10v2m8-8a8 8 0 11-16 0 8 8 0 0116 0z" />
                </svg>
                <span>Subscriptions</span>
            </a>

            <a href="{{ route('manager.blog.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-gray-600 hover:bg-gray-50 transition-colors relative
                    {{ request()->routeIs('manager.blog.*') ? 'bg-blue-500 text-white' : '' }}">
                @if(request()->routeIs('manager.blog.*'))
                    <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-1 h-8 bg-blue-600 rounded-r-full"></div>
                @endif
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 20h9" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M16.5 3.5a2.121 2.121 0 013 3L9 17l-4 1 1-4 10.5-10.5z" />
                </svg>
                <span>Blog</span>
            </a>

            @php
                $enterpriseUser = Auth::guard('web')->user();
            @endphp
            @if($enterpriseUser && (int) ($enterpriseUser->entreprise_id ?? 0) === 43)
            <a href="{{ route('admin.index') }}"
               class="flex items-center space-x-3 px-4 py-3 rounded-xl text-indigo-600 bg-indigo-50 hover:bg-indigo-100 transition-colors relative">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                     stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M15.75 19.5L8.25 12l7.5-7.5" />
                </svg>
                <span>Back to Enterprise Dashboard</span>
            </a>
            @endif
        </nav>
    </div>

    <div class="p-4 border-t border-gray-200 relative">
        <div class="flex items-center justify-between cursor-pointer hover:bg-gray-50 rounded-xl p-2 -m-2 transition-colors"
            onclick="toggleDropdown('user')">
            <div class="flex items-center space-x-3">
                <div
                    class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center text-white font-semibold">
                    {{ strtoupper(substr($manager?->name ?? 'M', 0, 1)) }}
                </div>
                <div>
                    <h4 class="text-sm font-medium text-gray-900">{{ $manager?->name ?? 'Manager' }}</h4>
                    <p class="text-xs text-gray-500">{{ $manager?->email }}</p>
                </div>
            </div>
            <svg id="user-icon" class="w-4 h-4 text-gray-400 user-dropdown-icon" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div id="user-submenu"
            class="absolute bottom-full mb-2 left-4 right-4 bg-white border border-gray-200 rounded-xl shadow-lg py-2 hidden z-10">
            <a href="{{ route('manager.profile.index') }}"
                class="block px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">Profile</a>
            <form method="POST" action="{{ route('manager.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-500 transition-colors">Deconnexion</button>
            </form>
        </div>
    </div>
</aside>

<style>
    .user-dropdown-icon {
        transition: transform 0.3s ease;
    }

    .user-dropdown-icon.rotated {
        transform: rotate(180deg);
    }
</style>

<script>
    function toggleDropdown(id) {
        const submenu = document.getElementById(id + '-submenu');
        const icon = document.getElementById(id + '-icon');

        const isActive = !submenu.classList.contains('hidden');

        if (isActive) {
            submenu.classList.add('hidden');
            icon.classList.remove('rotated');
        } else {
            submenu.classList.remove('hidden');
            icon.classList.add('rotated');
        }
    }

    // Close user dropdown when clicking outside
    document.addEventListener('click', function (event) {
        const userSubmenu = document.getElementById('user-submenu');
        const userIcon = document.getElementById('user-icon');
        const userArea = userSubmenu.previousElementSibling;

        if (userArea && !userArea.contains(event.target) && !userSubmenu.contains(event.target)) {
            userSubmenu.classList.add('hidden');
            userIcon.classList.remove('rotated');
        }
    });
</script>


