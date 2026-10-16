<aside class="sidenav dt-sidebar" id="sidenav-main" aria-label="Main navigation">
    <div class="dt-sidebar__brand">
        <a class="dt-brand" href="{{ route('superadmin.index') }}" aria-label="DalyTrac home">
            <span class="dt-brand__mark">D</span>
            <span class="dt-brand__copy"><x-application-logo /><small>{{ __('messages.Admin console') }}</small></span>
        </a>
        <button class="dt-sidebar__toggle" type="button" data-sidebar-toggle aria-label="Collapse sidebar" title="Collapse sidebar"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
    </div>
    <div class="dt-sidebar__section-label">{{ __('messages.Workspace') }}</div>
    <nav class="dt-sidebar__nav">
        <a class="dt-nav-link {{ request()->routeIs('superadmin.index') ? 'is-active' : '' }}" href="{{ route('superadmin.index') }}" title="{{ __('messages.Dashboard') }}"><span class="dt-nav-link__icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span><span class="dt-nav-link__label">{{ __('messages.Dashboard') }}</span></a>

        @php($navGroups = [
            ['key' => 'users', 'label' => __('messages.Users'), 'icon' => 'fa-regular fa-user', 'active' => request()->routeIs('superadmin.users.*'), 'links' => [['route' => 'superadmin.users.index', 'label' => __('messages.Reports')], ['route' => 'superadmin.users.create', 'label' => __('messages.New user')]]],
            ['key' => 'enterprises', 'label' => __('messages.Enterprises'), 'icon' => 'fa-solid fa-building', 'active' => request()->routeIs('superadmin.entreprise.*'), 'links' => [['route' => 'superadmin.entreprise.index', 'label' => __('messages.Reports')]]],
            ['key' => 'roles', 'label' => __('messages.Roles'), 'icon' => 'fa-solid fa-shield-halved', 'active' => request()->routeIs('superadmin.roles.*'), 'links' => [['route' => 'superadmin.roles.index', 'label' => __('messages.Reports')], ['route' => 'superadmin.roles.create', 'label' => __('messages.New role')]]],
            ['key' => 'permissions', 'label' => __('messages.Permissions'), 'icon' => 'fa-solid fa-key', 'active' => request()->routeIs('superadmin.permissions.*'), 'links' => [['route' => 'superadmin.permissions.index', 'label' => __('messages.Reports')], ['route' => 'superadmin.permissions.create', 'label' => __('messages.New permission')]]],
        ])
        @foreach($navGroups as $group)
            <div class="dt-nav-group">
                <button class="dt-nav-link dt-nav-link--group {{ $group['active'] ? 'is-active' : '' }}" type="button" data-sidebar-group="{{ $group['key'] }}" aria-expanded="{{ $group['active'] ? 'true' : 'false' }}" title="{{ $group['label'] }}"><span class="dt-nav-link__icon"><i class="{{ $group['icon'] }}" aria-hidden="true"></i></span><span class="dt-nav-link__label">{{ $group['label'] }}</span><i class="fa-solid fa-chevron-down dt-nav-link__chevron" aria-hidden="true"></i></button>
                <div class="dt-nav-submenu {{ $group['active'] ? 'is-open' : '' }}" data-sidebar-submenu="{{ $group['key'] }}">
                    @foreach($group['links'] as $link)<a href="{{ route($link['route']) }}" title="{{ $link['label'] }}"><span>{{ $link['label'] }}</span></a>@endforeach
                </div>
            </div>
        @endforeach
    </nav>
    <div class="dt-sidebar__footer"><span class="dt-status-dot"></span><span class="dt-sidebar__label">{{ __('messages.System operational') }}</span></div>
</aside>

<script>
(() => {
    const body = document.body, toggle = document.querySelector('[data-sidebar-toggle]'), key = 'dalytrac-sidebar-collapsed';
    if (!toggle) return;
    const setCollapsed = (collapsed) => { body.classList.toggle('dt-sidebar-collapsed', collapsed); toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar'); toggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar'); };
    if (window.innerWidth >= 1200) setCollapsed(localStorage.getItem(key) === 'true');
    toggle.addEventListener('click', () => { const collapsed = !body.classList.contains('dt-sidebar-collapsed'); setCollapsed(collapsed); if (window.innerWidth >= 1200) localStorage.setItem(key, collapsed); });
    document.querySelectorAll('[data-sidebar-group]').forEach((group) => group.addEventListener('click', () => { const submenu = document.querySelector(`[data-sidebar-submenu="${group.dataset.sidebarGroup}"]`); const open = submenu.classList.toggle('is-open'); group.setAttribute('aria-expanded', open); }));
})();
</script>
