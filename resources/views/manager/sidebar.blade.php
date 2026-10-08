@php
    $manager = auth('manager')->user();
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
    .dropdown-icon { transition: transform 0.3s ease; }
    .dropdown-icon.rotated { transform: rotate(90deg); }
    .user-dropdown-icon { transition: transform 0.3s ease; }
    .user-dropdown-icon.rotated { transform: rotate(180deg); }
    .submenu-item { position: relative; overflow: hidden; }
    .submenu-item::before {
        content: '';
        position: absolute;
        left: 0; top: 50%;
        transform: translateY(-50%);
        width: 2px; height: 0;
        background: #E7B84B;
        transition: height 0.2s ease;
    }
    .submenu-item:hover::before { height: 60%; }
    .dt-nav-item.is-active {
        background: #2D7080;
        color: #fff;
        box-shadow: 0 8px 20px -8px rgba(45,112,128,0.7);
    }
    .dt-nav-item.is-active svg { color: #fff; }
</style>

{{-- Mobile backdrop --}}
<div
    id="managerSidebarBackdrop"
    class="fixed inset-0 bg-[#0B2638]/50 backdrop-blur-[2px] z-40 hidden lg:hidden"
    onclick="toggleManagerSidebar(false)"
></div>

<aside
    id="managerSidebar"
    class="hidden fixed inset-y-0 left-0 z-50 bg-[#0B2638] border-r border-white/10 flex-col justify-between h-screen overflow-y-auto dt-sidebar-scroll transform -translate-x-full transition-all duration-300 ease-out lg:static lg:sticky lg:top-0 lg:translate-x-0 lg:flex"
>
    <div class="flex-1 flex flex-col min-h-0">
        {{-- Brand --}}
        <div class="sidebar-logo-wrap px-4 pt-5 pb-5 flex items-center">
            <a href="{{ route('manager.dashboard') }}" aria-label="DalyTrac Manager" class="flex items-center gap-2.5 min-w-0 flex-1 no-underline">
                <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-[#E7B84B] flex items-center justify-center text-[#0B2638] font-extrabold text-[15px] tracking-tight">DT</span>
                <span class="sidebar-logo-text inline-flex flex-col leading-tight">
                    <span class="inline-flex items-baseline text-[1.1rem] font-extrabold tracking-[-0.03em] text-white leading-none"><span>Daly</span><strong class="font-extrabold text-[#7fb3bf]">Trac</strong></span>
                    <span class="text-[10px] uppercase tracking-[0.12em] text-slate-400 font-semibold mt-1">{{ __('messages.Manager') }}</span>
                </span>
            </a>
        </div>

        <nav class="px-3 pb-6 space-y-5 overflow-y-auto dt-sidebar-scroll flex-1">

            {{-- OVERVIEW --}}
            <div>
                <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Overview') }}</p>
                <div class="space-y-1">
                    <a href="{{ route('manager.dashboard') }}" title="{{ __('messages.Dashboard') }}"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('manager.dashboard') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        @if(request()->routeIs('manager.dashboard'))
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="sidebar-label truncate">{{ __('messages.Dashboard') }}</span>
                        <span class="sidebar-tip">{{ __('messages.Dashboard') }}</span>
                    </a>
                </div>
            </div>

            {{-- WORKSPACE --}}
            <div>
                <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Workspace') }}</p>
                <div class="space-y-1">

                    {{-- Subscriptions --}}
                    <a href="{{ route('manager.subscriptions.index') }}" title="{{ __('messages.Subscriptions') }}"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('manager.subscriptions.*') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        @if(request()->routeIs('manager.subscriptions.*'))
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.598 1M12 8V6m0 10v2m8-8a8 8 0 11-16 0 8 8 0 0116 0z" />
                        </svg>
                        <span class="sidebar-label truncate">{{ __('messages.Subscriptions') }}</span>
                        <span class="sidebar-tip">{{ __('messages.Subscriptions') }}</span>
                    </a>

                    {{-- Analytics dropdown --}}
                    <div>
                        <button type="button" onclick="toggleDropdown('analytics')" title="{{ __('messages.Analytics') }}"
                            class="dt-nav-item group relative flex justify-between items-center w-full px-3 py-2.5 rounded-xl text-[13.5px] text-slate-300 hover:bg-white/10 hover:text-white transition-colors">
                            @php
                                $isAnalyticsPage = request()->routeIs('manager.dashboard.entreprises')
                                    || request()->routeIs('manager.dashboard.active-subscriptions')
                                    || request()->routeIs('manager.dashboard.users')
                                    || request()->routeIs('manager.dashboard.users.*')
                                    || request()->routeIs('manager.dashboard.visits.*')
                                    || request()->routeIs('manager.dashboard.invoices');
                            @endphp
                            @if($isAnalyticsPage)
                                <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                            @endif
                            <span class="flex items-center gap-3 min-w-0">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span class="sidebar-label truncate">{{ __('messages.Analytics') }}</span>
                            </span>
                            <svg id="analytics-icon" class="sidebar-label sidebar-chevron w-4 h-4 flex-shrink-0 dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                            <span class="sidebar-tip">{{ __('messages.Analytics') }}</span>
                        </button>
                        <div id="analytics-submenu" class="sidebar-submenu ml-9 space-y-1 dropdown-menu {{ $isAnalyticsPage ? 'active' : '' }}">
                            <a href="{{ route('manager.dashboard.entreprises') }}"
                               class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative {{ request()->routeIs('manager.dashboard.entreprises') ? 'text-white' : '' }}">
                                @if(request()->routeIs('manager.dashboard.entreprises'))
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                {{ __('messages.Total entreprises') }}
                            </a>
                            <a href="{{ route('manager.dashboard.active-subscriptions') }}"
                               class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative {{ request()->routeIs('manager.dashboard.active-subscriptions') ? 'text-white' : '' }}">
                                @if(request()->routeIs('manager.dashboard.active-subscriptions'))
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                {{ __('messages.Active subscriptions') }}
                            </a>
                            <a href="{{ route('manager.dashboard.users') }}"
                               class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative {{ request()->routeIs('manager.dashboard.users') && !request()->routeIs('manager.dashboard.users.*') ? 'text-white' : '' }}">
                                @if(request()->routeIs('manager.dashboard.users') && !request()->routeIs('manager.dashboard.users.*'))
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                {{ __('messages.Total users') }}
                            </a>
                            <a href="{{ route('manager.dashboard.users.new-signups') }}"
                               class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative {{ request()->routeIs('manager.dashboard.users.new-signups') ? 'text-white' : '' }}">
                                @if(request()->routeIs('manager.dashboard.users.new-signups'))
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                {{ __('messages.New signups (30d)') }}
                            </a>
                            <a href="{{ route('manager.dashboard.visits.non-client-unique') }}"
                               class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative {{ request()->routeIs('manager.dashboard.visits.*') ? 'text-white' : '' }}">
                                @if(request()->routeIs('manager.dashboard.visits.*'))
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                {{ __('messages.Visits (non client)') }}
                            </a>
                            <a href="{{ route('manager.dashboard.invoices') }}"
                               class="submenu-item block px-3 py-2 text-[13px] text-slate-400 hover:text-white hover:bg-white/10 rounded-lg transition-all duration-200 relative {{ request()->routeIs('manager.dashboard.invoices') ? 'text-white' : '' }}">
                                @if(request()->routeIs('manager.dashboard.invoices'))
                                    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 bg-[#E7B84B] rounded-r-full"></span>
                                @endif
                                {{ __('messages.Stripe invoices') }}
                            </a>
                        </div>
                    </div>

                    {{-- Blog --}}
                    <a href="{{ route('manager.blog.index') }}" title="{{ __('messages.Blog') }}"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('manager.blog.*') ? 'is-active' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        @if(request()->routeIs('manager.blog.*'))
                            <span class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 bg-[#E7B84B] rounded-r-full"></span>
                        @endif
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.121 2.121 0 013 3L9 17l-4 1 1-4 10.5-10.5z" />
                        </svg>
                        <span class="sidebar-label truncate">{{ __('messages.Blog') }}</span>
                        <span class="sidebar-tip">{{ __('messages.Blog') }}</span>
                    </a>

                    {{-- Back to Enterprise (only for entreprise 43) --}}
                    @php
                        $enterpriseUser = Auth::guard('web')->user();
                    @endphp
                    @if($enterpriseUser && (int)($enterpriseUser->entreprise_id ?? 0) === 43)
                    <a href="{{ route('admin.index') }}" title="Back to Enterprise Dashboard"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-white bg-white/10 hover:bg-white/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        <span class="sidebar-label truncate">{{ __('messages.Back to Enterprise') }}</span>
                        <span class="sidebar-tip">{{ __('messages.Back to Enterprise') }}</span>
                    </a>
                    @endif
                </div>
            </div>
        </nav>
    </div>

    {{-- User footer --}}
    <div class="p-3 border-t border-white/10 relative">
        <div class="sidebar-user-row flex items-center gap-3 cursor-pointer hover:bg-white/10 rounded-xl p-2 transition-colors"
             onclick="toggleDropdown('manager-user')">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <div class="w-9 h-9 flex-shrink-0 rounded-full bg-[#E7B84B] flex items-center justify-center text-[#0B2638] text-[13px] font-bold">
                    {{ strtoupper(substr(trim($manager?->name ?? 'M'), 0, 1)) }}
                </div>
                <div class="sidebar-user-meta min-w-0">
                    <h4 class="text-[13px] font-semibold text-white truncate leading-tight">{{ $manager?->name ?? 'Manager' }}</h4>
                    <p class="text-[11.5px] text-slate-400 truncate">{{ $manager?->email ?? '' }}</p>
                </div>
            </div>
            <svg id="manager-user-icon" class="sidebar-label w-4 h-4 flex-shrink-0 text-gray-400 user-dropdown-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div id="manager-user-submenu"
             class="absolute bottom-20 left-3 right-3 bg-white border border-slate-200 rounded-xl shadow-xl py-2 hidden z-50">
            <a href="{{ route('manager.profile.index') }}"
               class="block px-4 py-2 text-[13px] text-slate-700 hover:bg-[#2D7080]/10 hover:text-[#2D7080] transition-colors">
                {{ __('messages.Profile') }}
            </a>
            <form method="POST" action="{{ route('manager.logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 text-[13px] text-slate-700 hover:bg-[#2D7080]/10 hover:text-[#2D7080] transition-colors">
                    {{ __('messages.Logout') }}
                </button>
            </form>
        </div>
    </div>
</aside>

<script>
    function toggleDropdown(id) {
        if (window.innerWidth >= 1024 && document.body.classList.contains('dt-manager-collapsed') && id !== 'manager-user') {
            toggleManagerCollapse(false);
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

        if (id === 'manager-user') {
            submenu.classList.toggle('hidden', isActive);
        }
    }

    function toggleManagerCollapse(force) {
        const collapsed = typeof force === 'boolean' ? force : !document.body.classList.contains('dt-manager-collapsed');
        document.body.classList.toggle('dt-manager-collapsed', collapsed);
        try { localStorage.setItem('dalytrac-manager-collapsed', collapsed ? '1' : '0'); } catch(e) {}
    }

    (function initManagerSidebar() {
        try {
            if (localStorage.getItem('dalytrac-manager-collapsed') === '1' && window.innerWidth >= 1024) {
                document.body.classList.add('dt-manager-collapsed');
            }
        } catch(e) {}

        // Auto-open submenu with active item
        document.querySelectorAll('#managerSidebar .sidebar-submenu').forEach(function(menu) {
            if (menu.querySelector('.text-white:not([class*="text-white "]), span.bg-\\[\\#E7B84B\\]')) {
                menu.classList.add('active');
                const btn = menu.previousElementSibling;
                const chev = btn ? btn.querySelector('.dropdown-icon') : null;
                if (chev) chev.classList.add('rotated');
            }
        });
    })();

    // Close user dropdown on outside click
    document.addEventListener('click', function(event) {
        const userSubmenu = document.getElementById('manager-user-submenu');
        if (!userSubmenu || userSubmenu.classList.contains('hidden')) return;
        const userArea = userSubmenu.previousElementSibling;
        if (userArea && !userArea.contains(event.target) && !userSubmenu.contains(event.target)) {
            userSubmenu.classList.add('hidden');
            const icon = document.getElementById('manager-user-icon');
            if (icon) icon.classList.remove('rotated');
        }
    });
</script>
