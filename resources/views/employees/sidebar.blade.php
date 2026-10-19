@php
    $empProfile = App\Models\Employees::where('user_id', Auth::user()->id)->first();
    $empName = optional($empProfile)->name ?? Auth::user()->name;
    $empRole = Auth::user()->hasRole('Responsable de projet') ? 'Project Manager' : (Auth::user()->hasRole('Employee') ? 'Employee' : 'Manager');
    $dashboardRoute = '#';
    if (Auth::user()->hasRole('Responsable de projet')) $dashboardRoute = route('Responsable_de_projet.index');
    elseif (Auth::user()->hasRole('Employee')) $dashboardRoute = route('employee.index');
    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $dashboardRoute = route('Responsable_gestionnaire.index');
    $planningRoute = '#';
    if (Auth::user()->hasRole('Responsable de projet')) $planningRoute = route('Responsable_de_projet.calendar.index');
    elseif (Auth::user()->hasRole('Employee')) $planningRoute = route('employee.calendar.index');
    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $planningRoute = route('Responsable_gestionnaire.calendar.index');
    $billRoute = '#';
    if (Auth::user()->hasRole('Responsable de projet')) $billRoute = route('Responsable_de_projet.Enterprise_infos.create');
    elseif (Auth::user()->hasRole('Employee')) $billRoute = route('employee.Enterprise_infos.create');
    elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $billRoute = route('Responsable_gestionnaire.Enterprise_infos.create');
    $routeFor = function ($base, $suffix = 'index') {
        $role = Auth::user()->hasRole('Responsable de projet') ? 'Responsable_de_projet' : (Auth::user()->hasRole('Employee') ? 'employee' : 'Responsable_gestionnaire');
        try { return route($role . '.' . $base . '.' . $suffix); } catch (\Throwable $e) { return '#'; }
    };
@endphp
<aside class="sidenav dt-sidebar" id="sidenav-main" aria-label="Main navigation">
    <div class="dt-sidebar__brand">
        <a class="dt-brand" href="{{ $dashboardRoute }}" aria-label="DalyTrac home">
            <x-application-mark />
            <span class="dt-brand__copy"><x-application-logo /><strong style="font-size:11px;margin-top:4px">{{ $empName }}</strong><small>{{ $empRole }}</small></span>
        </a>
        <button class="dt-sidebar__toggle" type="button" data-sidebar-toggle aria-label="Collapse sidebar" title="Collapse sidebar"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button>
    </div>
    <div class="dt-sidebar__section-label">Workspace</div>
    <nav class="dt-sidebar__nav">
        <a class="dt-nav-link {{ request()->routeIs('*.index') && !request()->routeIs('*.calendar.*') ? 'is-active' : '' }}" href="{{ $dashboardRoute }}" title="Dashboard"><span class="dt-nav-link__icon"><i class="fa-solid fa-chart-line" aria-hidden="true"></i></span><span class="dt-nav-link__label">Dashboard</span></a>

        @can('Planning')
        <a class="dt-nav-link {{ request()->routeIs('*.calendar.*') ? 'is-active' : '' }}" href="{{ $planningRoute }}" title="Planning"><span class="dt-nav-link__icon"><i class="fa-regular fa-calendar" aria-hidden="true"></i></span><span class="dt-nav-link__label">Planning</span></a>
        @endcan

        @canany(['View client', 'View employee', 'View teams', 'View project', 'View task', 'Pointage reports', 'View pointage', 'Manage workDaysReports'])
        @php($reportingActive = request()->routeIs('*.clients.*') || request()->routeIs('*.employees.*') || request()->routeIs('*.teams.*') || request()->routeIs('*.projects.*') || request()->routeIs('*.tasks.*') || request()->routeIs('*.workDaysReports.*'))
        <div class="dt-nav-group">
            <button class="dt-nav-link dt-nav-link--group {{ $reportingActive ? 'is-active' : '' }}" type="button" data-sidebar-group="reporting" aria-expanded="{{ $reportingActive ? 'true' : 'false' }}" title="Reporting"><span class="dt-nav-link__icon"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span><span class="dt-nav-link__label">Reporting</span><i class="fa-solid fa-chevron-down dt-nav-link__chevron" aria-hidden="true"></i></button>
            <div class="dt-nav-submenu {{ $reportingActive ? 'is-open' : '' }}" data-sidebar-submenu="reporting">
                @can('View client')<a href="{{ $routeFor('clients') }}" title="Clients Reports"><span>Clients Reports</span></a>@endcan
                @can('View employee')<a href="{{ $routeFor('employees') }}" title="Employees Reports"><span>Employees Reports</span></a>@endcan
                @can('View project')<a href="{{ $routeFor('projects') }}" title="Projects Reports"><span>Projects Reports</span></a>@endcan
                @can('View task')<a href="{{ $routeFor('tasks') }}" title="Tasks Reports"><span>Tasks Reports</span></a>@endcan
                @can('View team')<a href="{{ $routeFor('teams') }}" title="Teams Reports"><span>Teams Reports</span></a>@endcan
                @canany(['Pointage reports', 'View pointage'])
                    @php
                        $pointageRoute = '#';
                        if (Auth::user()->hasRole('Responsable de projet')) $pointageRoute = route('Responsable_de_projet.tasks.pointage');
                        elseif (Auth::user()->hasRole('Employee')) $pointageRoute = route('employee.tasks.pointage');
                        elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) $pointageRoute = route('Responsable_gestionnaire.tasks.pointage');
                    @endphp
                    <a href="{{ $pointageRoute }}" title="Pointage Reports"><span>Pointage Reports</span></a>
                @endcanany
                @can('Manage workDaysReports')<a href="{{ $routeFor('workDaysReports') }}" title="Work Days Reports"><span>Work Days Reports</span></a>@endcan
            </div>
        </div>
        @endcanany

        @can('Create invoice')
        <div class="dt-nav-group">
            <button class="dt-nav-link dt-nav-link--group {{ request()->routeIs('*.invoices.*') || request()->routeIs('*.invoice.*') ? 'is-active' : '' }}" type="button" data-sidebar-group="invoices" aria-expanded="false" title="Invoices"><span class="dt-nav-link__icon"><i class="fa-solid fa-receipt" aria-hidden="true"></i></span><span class="dt-nav-link__label">Invoices</span><i class="fa-solid fa-chevron-down dt-nav-link__chevron" aria-hidden="true"></i></button>
            <div class="dt-nav-submenu" data-sidebar-submenu="invoices">
                <a href="{{ $routeFor('invoices', 'fullinvoice') }}" title="Generate"><span>Generate</span></a>
                <a href="{{ $routeFor('invoices', 'invoicehistory') }}" title="History"><span>History</span></a>
                <a href="{{ $routeFor('invoices', 'parameters') }}" title="Parameters"><span>Parameters</span></a>
            </div>
        </div>
        @endcan

        @can('Create devis')
        <div class="dt-nav-group">
            <button class="dt-nav-link dt-nav-link--group {{ request()->routeIs('*.devis.*') ? 'is-active' : '' }}" type="button" data-sidebar-group="devis" aria-expanded="false" title="Devis"><span class="dt-nav-link__icon"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i></span><span class="dt-nav-link__label">Devis</span><i class="fa-solid fa-chevron-down dt-nav-link__chevron" aria-hidden="true"></i></button>
            <div class="dt-nav-submenu" data-sidebar-submenu="devis">
                <a href="{{ $routeFor('devis', 'generateDevis') }}" title="Generate"><span>Generate</span></a>
                <a href="{{ $routeFor('devis', 'devishistory') }}" title="History"><span>History</span></a>
                <a href="{{ $routeFor('devis', 'parameters') }}" title="Parameters"><span>Parameters</span></a>
            </div>
        </div>
        @endcan

        @can('Create expense note')
        <div class="dt-nav-group">
            <button class="dt-nav-link dt-nav-link--group {{ request()->routeIs('*.notedefrais.*') ? 'is-active' : '' }}" type="button" data-sidebar-group="expense" aria-expanded="false" title="Expense Note"><span class="dt-nav-link__icon"><i class="fa-solid fa-wallet" aria-hidden="true"></i></span><span class="dt-nav-link__label">Expense Note</span><i class="fa-solid fa-chevron-down dt-nav-link__chevron" aria-hidden="true"></i></button>
            <div class="dt-nav-submenu" data-sidebar-submenu="expense">
                <a href="{{ $routeFor('notedefrais', 'generateNoteDeFrais') }}" title="Generate"><span>Generate</span></a>
                <a href="{{ $routeFor('notedefrais', 'notedefraishistory') }}" title="History"><span>History</span></a>
                <a href="{{ $routeFor('notedefrais', 'parameters') }}" title="Parameters"><span>Parameters</span></a>
            </div>
        </div>
        @endcan

        @can('Billings')
        <a class="dt-nav-link" href="{{ $billRoute }}" title="Billings"><span class="dt-nav-link__icon"><i class="fa-solid fa-credit-card" aria-hidden="true"></i></span><span class="dt-nav-link__label">Billings</span></a>
        @endcan
    </nav>
    <div class="dt-sidebar__footer" style="position:static;margin:12px 14px 8px;">
        <span class="dt-brand__mark" style="width:30px;height:30px;font-size:14px;">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
        <span class="dt-sidebar__label" style="flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Auth::user()->name }}</span>
        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
            @csrf
            <button type="submit" title="{{ __('messages.Logout') }}" style="background:none;border:0;color:#8fb5bd;cursor:pointer;font-size:13px;"><i class="fa-solid fa-right-from-bracket" aria-hidden="true"></i></button>
        </form>
    </div>
    <div class="dt-sidebar__footer" style="position:static;margin:0 14px 17px;padding:8px 12px;">
        <span class="dt-sidebar__label" style="display:flex;gap:8px;align-items:center;">
            <img src="https://flagcdn.com/gb.svg" alt="EN" style="width:16px;height:12px;border-radius:2px;">
            <a href="{{ route('changeLang', ['lang' => 'en']) }}" style="color:inherit;text-decoration:none;">EN</a>
            <a href="{{ route('changeLang', ['lang' => 'fr']) }}" style="color:inherit;text-decoration:none;">FR</a>
            <a href="{{ route('changeLang', ['lang' => 'es']) }}" style="color:inherit;text-decoration:none;">ES</a>
            <a href="{{ route('changeLang', ['lang' => 'nl']) }}" style="color:inherit;text-decoration:none;">NL</a>
        </span>
    </div>
</aside>

<script>
(() => {
    const body = document.body, toggle = document.querySelector('[data-sidebar-toggle]'), key = 'dalytrac-sidebar-collapsed';
    if (toggle) {
        const setCollapsed = (collapsed) => { body.classList.toggle('dt-sidebar-collapsed', collapsed); toggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar'); toggle.setAttribute('title', collapsed ? 'Expand sidebar' : 'Collapse sidebar'); };
        if (window.innerWidth >= 1200) setCollapsed(localStorage.getItem(key) === 'true');
        toggle.addEventListener('click', () => { const collapsed = !body.classList.contains('dt-sidebar-collapsed'); setCollapsed(collapsed); if (window.innerWidth >= 1200) localStorage.setItem(key, collapsed); });
    }
    document.querySelectorAll('[data-sidebar-group]').forEach((group) => group.addEventListener('click', () => { const submenu = document.querySelector(`[data-sidebar-submenu="${group.dataset.sidebarGroup}"]`); if (!submenu) return; const open = submenu.classList.toggle('is-open'); group.setAttribute('aria-expanded', open); }));
})();
</script>
