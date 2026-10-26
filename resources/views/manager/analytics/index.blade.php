@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/manager-analytics.css') }}">
<div id="ga-dashboard" data-api="{{ url('/api/seo/google-analytics') }}" data-connected="{{ $connected ? '1' : '0' }}">
    <header class="ga-header">
        <div><h1>SEO · Google Analytics</h1><p>Comprenez vos visiteurs et les performances de vos pages · cache de 3 heures</p></div>
        <div class="ga-actions">
            <a class="ga-button ga-secondary" href="{{ route('manager.seo.connect', ['integration' => 'analytics']) }}">{{ $connected ? 'Autoriser Analytics' : 'Connecter Google' }}</a>
            @if($propertyId)<a class="ga-button ga-primary" href="https://analytics.google.com/analytics/web/#/p{{ urlencode($propertyId) }}/reports/intelligenthome" target="_blank" rel="noopener noreferrer">Ouvrir Google Analytics ↗</a>@endif
        </div>
    </header>
    @if(session('gsc_error'))<p class="ga-alert" role="alert">{{ session('gsc_error') }}</p>@endif
    @if(session('gsc_success'))<p class="ga-notice" role="status">{{ session('gsc_success') }}</p>@endif
    @if(!$propertyId)<p class="ga-alert" role="alert">Renseignez le Property ID GA4 numérique dans la configuration serveur.</p>@endif
    @if(!$connected || !$propertyId)<section class="ga-card ga-empty"><h2>Vos données Analytics, au même endroit</h2><p>{{ !$connected ? 'Connectez votre compte Google avec un accès à la propriété GA4. DalyTrac utilise uniquement des autorisations de lecture.' : 'La propriété GA4 doit être configurée sur le serveur pour afficher les rapports.' }}</p></section>@else
    <div class="ga-toolbar">
        <div class="ga-property"><span>Propriété GA4</span><strong>{{ $propertyId }}</strong></div>
        <div class="ga-periods" role="group" aria-label="Période du rapport">@foreach([7 => '7 jours', 28 => '28 jours', 90 => '3 mois', 180 => '6 mois'] as $days => $label)<button type="button" data-days="{{ $days }}" aria-pressed="{{ $days === 28 ? 'true' : 'false' }}">{{ $label }}</button>@endforeach</div>
        <button id="ga-refresh" class="ga-button ga-secondary" type="button"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M20 7v5h-5M4 17v-5h5M6 6a8 8 0 0 1 13 2M5 16a8 8 0 0 0 13 2"/></svg><span>Actualiser</span></button>
    </div>
    <p id="ga-status" role="status" aria-live="polite"></p>
    <div class="ga-kpis">
        @foreach(['activeUsers' => ['Utilisateurs actifs', '◉'], 'sessions' => ['Sessions', '↗'], 'newUsers' => ['Nouveaux utilisateurs', '+'], 'screenPageViews' => ['Vues', '▤'], 'engagementRate' => ['Taux d’engagement', '%'], 'averageEngagement' => ['Engagement / utilisateur', '◷']] as $key => $item)
        <section class="ga-card ga-kpi"><div><h2>{{ $item[0] }}</h2><span aria-hidden="true">{{ $item[1] }}</span></div><strong id="ga-{{ $key }}">—</strong></section>
        @endforeach
    </div>
    <div class="ga-chart-grid">
        <section class="ga-card"><div class="ga-card-heading"><h2>Évolution du trafic</h2><span>Par jour</span></div><p class="ga-caption">Utilisateurs actifs et sessions · valeurs réelles</p><div id="ga-traffic">Chargement…</div></section>
        <section class="ga-card"><div class="ga-card-heading"><h2>Pages vues</h2><span>Par jour</span></div><p class="ga-caption">Nombre de vues quotidiennes</p><div id="ga-views-chart">Chargement…</div></section>
    </div>
    <section class="ga-card"><div class="ga-card-heading"><h2>Sources de trafic</h2><span>Acquisition des sessions</span></div><div class="ga-table-wrap" id="ga-sources">Chargement…</div></section>
    <section class="ga-card"><div class="ga-card-heading"><h2>Campagnes marketing</h2><span>Campagne et source / support</span></div><div class="ga-table-wrap" id="ga-campaigns">Chargement…</div></section>
    <div class="ga-report-grid">
        @foreach(['events' => ['Événements', 'Événements et événements clés'], 'pages' => ['Pages les plus visitées', 'Classement par vues'], 'countries' => ['Pays', 'Origine des visiteurs'], 'devices' => ['Appareils', 'Répartition de l’audience']] as $key => $item)
        <section class="ga-card"><div class="ga-card-heading"><h2>{{ $item[0] }}</h2><span>{{ $item[1] }}</span></div><div class="ga-table-wrap" id="ga-{{ $key }}">Chargement…</div></section>
        @endforeach
    </div>
    <p class="ga-footnote">Périodes jusqu’à hier, selon le fuseau de la propriété GA4. Les données récentes peuvent évoluer. Les utilisateurs sont dédupliqués sur la période : les totaux des tableaux ne s’additionnent pas nécessairement. Engagement / utilisateur = durée totale d’engagement ÷ utilisateurs actifs. Les tableaux affichent jusqu’à 1 000 lignes.</p>
    @endif
</div>
@if($connected && $propertyId)<script src="{{ asset('js/manager-analytics.js') }}" defer></script>@endif
@endsection
