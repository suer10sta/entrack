@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/manager-seo.css') }}"> 
<div class="space-y-6 p-4 md:p-6" id="seo-dashboard" data-connected="{{ $connected ? '1' : '0' }}" data-api="{{ url('/api/seo') }}">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-[#0B2638]">SEO · Google Search Console</h1><p class="text-sm text-slate-500">Performances Google · dates inclusives en heure du Pacifique · cache de 3 heures</p></div>
        <a class="rounded-xl bg-[#2D7080] px-4 py-2 text-white font-semibold" href="{{ route('manager.seo.connect') }}">{{ $connected ? 'Reconnecter Google' : 'Connecter Google' }}</a>
    </div>
    @if(session('gsc_error'))<p role="alert" class="dt-card p-4 text-red-700">{{ session('gsc_error') }}</p>@endif
    @if(session('gsc_success'))<p role="status" class="dt-card p-4 text-[#2D7080]">{{ session('gsc_success') }}</p>@endif
    @if(!$connected)
        <div class="dt-card p-8"><h2 class="font-semibold text-lg text-[#0B2638]">Connectez votre propriété Search Console</h2><p class="mt-2 text-slate-500">Utilisez un compte Google disposant d’un accès à votre site. Les autorisations demandées permettent uniquement la lecture.</p></div>
    @else
        <form id="seo-filters" class="dt-card p-4 flex flex-wrap items-end gap-4">
            <label class="flex-1 min-w-0">Propriété<select id="seo-site" required class="block w-full rounded-lg border-slate-300"><option value="">Chargement…</option></select></label>
            <label>Du<input id="seo-start" type="date" value="{{ now('America/Los_Angeles')->subDays(28)->toDateString() }}" min="{{ now('America/Los_Angeles')->subMonths(16)->toDateString() }}" max="{{ now('America/Los_Angeles')->toDateString() }}" required class="block rounded-lg border-slate-300"></label>
            <label>Au<input id="seo-end" type="date" value="{{ now('America/Los_Angeles')->subDay()->toDateString() }}" max="{{ now('America/Los_Angeles')->toDateString() }}" required class="block rounded-lg border-slate-300"></label>
            <div class="seo-filter-actions">
                <button id="seo-submit" type="submit">Afficher</button>
                <button id="seo-refresh" type="button" title="Récupérer les dernières données depuis Google" aria-label="Actualiser les données depuis Google">
                    <svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7v5h-5M4 17v-5h5" stroke-linecap="round" stroke-linejoin="round"/><path d="M6.1 7a7 7 0 0 1 11.6-1L20 9M4 15l2.3 3A7 7 0 0 0 17.9 17" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    <span id="seo-refresh-label">Actualiser</span>
                </button>
            </div>
        </form>
        <p id="seo-status" role="status" aria-live="polite" class="text-sm text-[#2D7080]"></p>
        <details class="dt-card p-4"><summary>Source et diagnostic des KPI</summary><pre id="seo-diagnostics" style="white-space:pre-wrap;overflow-wrap:anywhere;font-size:11px;margin-top:12px">Chargez les rapports pour comparer la réponse Google aux KPI affichés.</pre></details>
        <div class="seo-kpis">
            @foreach(['clicks' => 'Clics', 'impressions' => 'Impressions', 'ctr' => 'CTR', 'position' => 'Position moyenne'] as $key => $label)
                <div class="dt-card p-5 border-t-4 border-[#E7B84B]"><span class="seo-kpi-mark" aria-hidden="true">{{ ["clicks" => "↗", "impressions" => "◉", "ctr" => "%", "position" => "#"][$key] }}</span><p class="dt-stat-label">{{ $label }}</p><p id="seo-{{ $key }}" class="dt-stat-value">—</p></div>
            @endforeach
        </div>
        <div class="seo-charts">
            <section class="dt-card p-5"><h2 class="font-semibold text-[#0B2638]">Évolution clics / impressions</h2><p class="text-xs text-slate-500">Volumes quotidiens · échelle commune en nombre.</p><div id="seo-traffic"></div></section>
            <section class="dt-card p-5"><h2 class="font-semibold text-[#0B2638]">Position moyenne</h2><p class="text-xs text-slate-500">Une position plus basse est meilleure.</p><div id="seo-position-chart"></div></section>
        </div>
        <div class="seo-reports">
            @foreach(['queries' => 'Top requêtes', 'pages' => 'Top pages', 'countries' => 'Pays', 'devices' => 'Appareils', 'sitemaps' => 'État des sitemaps'] as $key => $label)
                <section class="dt-card p-5 min-w-0"><h2 class="font-semibold text-[#0B2638] mb-3">{{ $label }}</h2><div id="seo-{{ $key }}" class="overflow-x-auto text-sm">En attente…</div></section>
            @endforeach
        </div>
        <p class="seo-footnote">Les rapports détaillés affichent au plus 1 000 résultats par dimension. Google peut omettre certaines requêtes et les jours sans données. Les sitemaps correspondent aux envois connus de Search Console, indépendamment de la période sélectionnée.</p>
    @endif
</div>
@if($connected)<script src="{{ asset('js/manager-seo.js') }}" defer></script>@endif
@endsection
