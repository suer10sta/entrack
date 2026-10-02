@php
    $manager = auth('manager')->user();
@endphp

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
        <div class="sidebar-logo-wrap px-4 pt-5 pb-5 flex items-center gap-3">
            <span class="flex-shrink-0 w-9 h-9 rounded-xl bg-[#E7B84B] flex items-center justify-center text-[#0B2638] font-extrabold text-[15px] tracking-tight">DT</span>
            <span class="sidebar-logo-text flex-1 min-w-0 inline-flex flex-col leading-tight">
                <span class="inline-flex items-baseline text-[1.1rem] font-extrabold tracking-[-0.03em] text-white leading-none"><span>Daly</span><strong class="font-extrabold text-[#7fb3bf]">Trac</strong></span>
                <span class="sidebar-logo-text text-[10px] uppercase tracking-[0.12em] text-slate-400 font-semibold mt-1">{{ __('messages.Manager') }}</span>
            </span>
        </div>

        <nav class="px-3 pb-6 space-y-5 overflow-y-auto dt-sidebar-scroll flex-1">
            <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Overview') }}</p>
                <div class="space-y-1">
                    <a href="{{ route('manager.dashboard') }}" title="Dashboard"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('manager.dashboard') ? 'bg-[#2D7080] text-white shadow-[0_8px_20px_-8px_rgba(45,112,128,0.7)]' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
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

            <div>
                    <p class="sidebar-section px-3 mb-2 text-[10.5px] font-semibold uppercase tracking-[0.1em] text-slate-400/80">{{ __('messages.Workspace') }}</p>
                <div class="space-y-1">
                    <a href="{{ route('manager.subscriptions.index') }}" title="Subscriptions"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('manager.subscriptions.*') ? 'bg-[#2D7080] text-white shadow-[0_8px_20px_-8px_rgba(45,112,128,0.7)]' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
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

                    <a href="{{ route('manager.blog.index') }}" title="Blog"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium transition-colors {{ request()->routeIs('manager.blog.*') ? 'bg-[#2D7080] text-white shadow-[0_8px_20px_-8px_rgba(45,112,128,0.7)]' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
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

                    @php
                        $enterpriseUser = Auth::guard('web')->user();
                    @endphp
                    @if($enterpriseUser && (int) ($enterpriseUser->entreprise_id ?? 0) === 43)
                    <a href="{{ route('admin.index') }}" title="Back to Enterprise Dashboard"
                       class="dt-nav-item group relative flex items-center gap-3 px-3 py-2.5 rounded-xl text-[13.5px] font-medium text-white bg-white/10 hover:bg-white/20 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="w-5 h-5 flex-shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                        </svg>
                        <span class="sidebar-label truncate">Back to Enterprise Dashboard</span>
                        <span class="sidebar-tip">Back to Enterprise Dashboard</span>
                    </a>
                    @endif
                </div>
            </div>
        </nav>
    </div>

    <div class="p-3 border-t border-white/10 relative">
        <div class="flex items-center gap-3 cursor-pointer hover:bg-white/10 rounded-xl p-2 transition-colors"
            onclick="toggleDropdown('user')">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <div class="w-9 h-9 flex-shrink-0 rounded-full bg-[#E7B84B] flex items-center justify-center text-[#0B2638] text-[13px] font-bold">
                    {{ strtoupper(substr($manager?->name ?? 'M', 0, 1)) }}
                </div>
                <div class="sidebar-user-meta min-w-0">
                    <h4 class="text-[13px] font-semibold text-white truncate leading-tight">{{ $manager?->name ?? 'Manager' }}</h4>
                    <p class="text-[11.5px] text-slate-400 truncate">{{ $manager?->email }}</p>
                </div>
            </div>
            <svg id="user-icon" class="sidebar-label w-4 h-4 flex-shrink-0 text-gray-400 user-dropdown-icon" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div id="user-submenu"
            class="absolute bottom-20 left-3 right-3 bg-white border border-slate-200 rounded-xl shadow-xl py-2 hidden z-50">
            <a href="{{ route('manager.profile.index') }}"
                class="block px-4 py-2 text-[13px] text-slate-700 hover:bg-[#2D7080]/10 hover:text-[#2D7080] transition-colors">{{ __('messages.Profile') }}</a>
            <form method="POST" action="{{ route('manager.logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left px-4 py-2 text-[13px] text-slate-700 hover:bg-[#2D7080]/10 hover:text-[#2D7080] transition-colors">{{ __('messages.Logout') }}</button>
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
        if (!submenu) return;

        const isActive = !submenu.classList.contains('hidden');

        if (isActive) {
            submenu.classList.add('hidden');
            if (icon) icon.classList.remove('rotated');
        } else {
            submenu.classList.remove('hidden');
            if (icon) icon.classList.add('rotated');
        }
    }

    function toggleManagerCollapse(force) {
        const collapsed = typeof force === 'boolean' ? force : !document.body.classList.contains('dt-manager-collapsed');
        document.body.classList.toggle('dt-manager-collapsed', collapsed);
        try { localStorage.setItem('dalytrac-manager-collapsed', collapsed ? '1' : '0'); } catch (e) {}
    }

    (function () {
        try {
            if (localStorage.getItem('dalytrac-manager-collapsed') === '1' && window.innerWidth >= 1024) {
                document.body.classList.add('dt-manager-collapsed');
            }
        } catch (e) {}
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

