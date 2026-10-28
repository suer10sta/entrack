@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/crm-campaigns.css') }}">
<div class="cc-page p-4 md:p-6">
    <a class="cc-back" href="{{ route('manager.campaigns.index') }}">← Toutes les campagnes</a>
    <header class="cc-header"><div><h1>{{ $campaign->name }}</h1><p>{{ $campaign->subject }}</p></div><span class="cc-badge cc-{{ $campaign->status }}">{{ $campaign->statusLabel() }}</span></header>
    @include('manager.prospects.partials.feedback')
    @include('manager.campaigns.partials.result-summary')
    <div class="cc-kpis cc-detail-kpis">@foreach(['Total destinataires' => $selected->count(), 'Envoyés avec succès' => $counts['sent'] ?? 0, 'En attente' => ($counts['selected'] ?? 0) + ($counts['pending'] ?? 0) + ($counts['sending'] ?? 0), 'Échecs' => $counts['failed'] ?? 0, 'Ouverts' => $campaign->opened_count, 'Cliqués' => $campaign->clicked_count] as $label => $number)<div class="cc-card"><span>{{ $label }}</span><strong>{{ $number }}</strong></div>@endforeach</div>
    <p class="cc-help cc-tracking-help">Ouverts et cliqués : destinataires uniques. Les compteurs individuels incluent les répétitions. Les protections de confidentialité et scanners email peuvent affecter ces mesures ; une ouverture ne change jamais le statut CRM. Suivi disponible pour les nouveaux envois.</p>
    <div class="cc-layout">
        <aside class="cc-stack">
            <section class="cc-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'mail']) Message</h2>
                @if($campaign->status === 'draft')<form method="POST" action="{{ route('manager.campaigns.update', $campaign) }}" class="cc-form" data-campaign-content>@csrf @method('PATCH') @include('manager.campaigns.partials.content-fields')<button class="cc-button cc-secondary">@include('manager.prospects.partials.icon', ['icon' => 'check']) Enregistrer le message</button></form>
                @else<div class="cc-message">{{ $campaign->content }}</div>@endif
            </section>
            <section class="cc-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'send']) Envoi</h2>
                @if($campaign->status === 'draft')
                    <p class="cc-help">Enregistrez le message et votre sélection avant de confirmer. La campagne sera envoyée par lots ; chaque adresse ne recevra cet email qu’une fois.</p>
                    <p class="cc-selection-count"><strong>{{ $selected->count() }}</strong> destinataire(s) enregistré(s)</p>
                    <form method="POST" action="{{ route('manager.campaigns.send', $campaign) }}" class="cc-form" data-campaign-send>@csrf<input type="hidden" name="revision" value="{{ $campaign->revision }}"><label class="cc-check"><input type="checkbox" name="confirm" value="1" required> Je confirme l’envoi du message enregistré à ce groupe.</label><button class="cc-button cc-primary" @disabled($selected->isEmpty())>@include('manager.prospects.partials.icon', ['icon' => 'send']) Envoyer à {{ $selected->count() }} destinataire(s)</button></form>
                @else
                    <dl class="cc-dates"><div><dt>Demandé le</dt><dd>{{ $campaign->queued_at?->format('d/m/Y H:i') ?? '—' }}</dd></div><div><dt>Début de l’envoi</dt><dd>{{ $campaign->started_at?->format('d/m/Y H:i') ?? ($campaign->completed_at ? 'Aucun envoi' : 'En attente') }}</dd></div><div><dt>Terminé le</dt><dd>{{ $campaign->completed_at?->format('d/m/Y H:i') ?? '—' }}</dd></div></dl>
                    <a class="cc-button cc-secondary" href="{{ request()->fullUrl() }}">@include('manager.prospects.partials.icon', ['icon' => 'reset']) Actualiser les résultats</a>
                    <p class="cc-help">Envois traités en arrière-plan. Un email « envoyé » a été accepté par le serveur de messagerie ; sa réception en boîte de réception n’est pas mesurée.</p>
                @endif
            </section>
        </aside>
        <div class="cc-stack">
            @if($campaign->status === 'draft')
            @include('manager.campaigns.partials.audience')
            @endif
            <section class="cc-card" id="campaign-results"><h2>@include('manager.prospects.partials.icon', ['icon' => 'clock']) {{ $campaign->status === 'draft' ? 'Destinataires enregistrés' : 'Résultats par destinataire' }}</h2>
                <div class="cc-scroll"><table class="cc-table"><thead><tr><th>Nom du prospect</th><th>Email</th><th>Statut d’envoi</th><th>Date / heure d’envoi</th><th>Suivi après envoi</th><th>Erreur / détail</th></tr></thead><tbody>
                    @forelse($results as $recipient)<tr><td>{{ $recipient->name }}</td><td>{{ $recipient->email }}</td><td><span class="cc-badge cc-{{ $recipient->status }}">{{ $recipient->status === 'sending' ? 'En attente' : (\App\Models\CrmCampaignRecipient::labels()[$recipient->status] ?? 'À vérifier') }}</span>@if($recipient->status === 'sending')<p class="cc-help">Transmission en cours</p>@endif</td><td class="cc-nowrap">{{ ($recipient->sent_at ?? $recipient->attempted_at)?->format('d/m/Y H:i') ?? '—' }}@if(!$recipient->sent_at && $recipient->attempted_at)<p class="cc-help">Tentative d’envoi</p>@endif</td><td>@include('manager.campaigns.partials.recipient-tracking')</td><td class="cc-help">{{ $recipient->error ?? $recipient->crm_error ?? '—' }}</td></tr>
                    @empty<tr><td colspan="6" class="cc-empty">Aucun destinataire enregistré.</td></tr>@endforelse
                </tbody></table></div><div class="cc-pagination">{{ $results->links() }}</div>
            </section>
        </div>
    </div>
</div>
<script src="{{ asset('js/crm-campaigns.js') }}" defer></script>
@endsection
