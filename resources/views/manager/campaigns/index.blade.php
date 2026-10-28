@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/crm-campaigns.css') }}">
<div class="cc-page p-4 md:p-6">
    <header class="cc-header"><div><h1>Campagnes</h1><p>Créez vos emails et suivez les envois à vos prospects.</p></div></header>
    @include('manager.prospects.partials.feedback')
    <div class="cc-campaign-overview">
        <div class="cc-global-kpis"><div class="cc-global-kpi cc-rate-total"><span class="cc-rate-icon">@include('manager.prospects.partials.icon', ['icon' => 'mail'])</span><span class="cc-rate-label">Total campagnes</span><strong>{{ number_format($campaigns->total(), 0, ',', ' ') }}</strong></div>@foreach($globalRates as $label => $rate)<div class="cc-global-kpi {{ $loop->first ? 'cc-rate-open' : 'cc-rate-click' }}"><span class="cc-rate-icon">@include('manager.prospects.partials.icon', ['icon' => $loop->first ? 'eye' : 'link'])</span><span class="cc-rate-label">{{ $label }}</span><strong>{{ $rate }}</strong></div>@endforeach</div>
        <a href="{{ route('manager.campaigns.create') }}" class="cc-button cc-primary">@include('manager.prospects.partials.icon', ['icon' => 'new']) Créer une campagne</a>
    </div>
    <!-- <p class="cc-help cc-tracking-help">Taux globaux sur l’ensemble des campagnes envoyées : destinataires uniques ouverts ou cliqués par campagne, divisés par le total des emails envoyés avec succès. Les répétitions ne sont comptées qu’une fois par destinataire et campagne.</p> -->
    <div class="cc-history-layout">
        <section class="cc-card cc-history"><h2>@include('manager.prospects.partials.icon', ['icon' => 'clock']) Historique des campagnes</h2>
            <!-- <p class="cc-help cc-results-help">« Envoyé » signifie accepté par le serveur de messagerie. Les emails en attente et les envois incertains ne sont pas comptés comme réussis.</p> -->
            <div class="cc-scroll cc-history-scroll" tabindex="0" role="region" aria-label="Historique des campagnes">
                <table class="cc-table cc-campaign-history">
                    <thead><tr><th scope="col">Campagne</th><th scope="col">Statut</th><th scope="col" title="Total destinataires">Destinataires</th><th scope="col" title="Envoyés avec succès">Envoyés</th><th scope="col">Ouverts</th><th scope="col">Cliqués</th><th scope="col">Échecs</th><th scope="col">En attente</th><th scope="col">Date d’envoi</th><th scope="col">Actions</th></tr></thead>
                    <tbody>
                        @forelse($campaigns as $campaign)
                            <tr>
                                <td><a class="cc-name" href="{{ route('manager.campaigns.show', $campaign) }}">{{ $campaign->name }}</a><p class="cc-help">{{ $campaign->subject }}</p></td>
                                <td><span class="cc-badge cc-{{ $campaign->status }}">{{ in_array($campaign->status, ['completed_with_errors', 'failed'], true) ? 'Erreur' : $campaign->statusLabel() }}</span></td>
                                <td class="cc-history-number">{{ $campaign->recipients_count }}</td>
                                <td class="cc-history-number cc-result-success">{{ $campaign->sent_count }}</td>
                                <td class="cc-history-number">{{ $campaign->opened_count }}</td>
                                <td class="cc-history-number">{{ $campaign->clicked_count }}</td>
                                <td class="cc-history-number {{ $campaign->failed_count ? 'cc-result-failure' : '' }}">{{ $campaign->failed_count }}@if($campaign->uncertain_count)<p class="cc-help">{{ $campaign->uncertain_count }} à vérifier</p>@endif @if($campaign->skipped_count)<p class="cc-help">{{ $campaign->skipped_count }} ignoré(s)</p>@endif</td>
                                <td class="cc-history-number {{ $campaign->pending_count ? 'cc-result-pending' : 'cc-result-neutral' }}">{{ $campaign->pending_count }}</td>
                                <td class="cc-history-date">@if($campaign->status === 'scheduled'){{ $campaign->scheduled_at?->utc()->format('d/m/Y H:i') }} UTC<p class="cc-help">Envoi programmé</p>@else{{ $campaign->started_at?->format('d/m/Y H:i') ?? '—' }}@if(!$campaign->started_at)<p class="cc-help">Pas encore envoyé</p>@endif @endif</td>
                                <td><div class="cc-history-actions"><a href="{{ route('manager.campaigns.show', $campaign) }}" class="cc-icon-button" aria-label="Voir les détails" title="Voir les détails">@include('manager.prospects.partials.icon', ['icon' => 'eye'])</a><form method="POST" action="{{ route('manager.campaigns.destroy', $campaign) }}" data-delete-campaign data-campaign-name="{{ $campaign->name }}">@csrf @method('DELETE')<button type="submit" class="cc-icon-button cc-delete-action" aria-label="Supprimer la campagne {{ $campaign->name }}" title="Supprimer la campagne">@include('manager.prospects.partials.icon', ['icon' => 'trash'])</button></form></div></td>
                            </tr>
                        @empty<tr><td colspan="10" class="cc-empty">Aucune campagne pour le moment.</td></tr>@endforelse
                    </tbody>
                </table>
            </div><p class="cc-help cc-tracking-help">Ouverts et cliqués : destinataires uniques, parmi les emails envoyés. Les protections de confidentialité et les scanners email peuvent affecter ces mesures. Le suivi concerne les nouveaux envois.</p><div class="cc-pagination">{{ $campaigns->links() }}</div>
        </section>
    </div>
</div>
<script src="{{ asset('js/crm-campaigns.js') }}" defer></script>
@endsection
