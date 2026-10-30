@php
    $sent = (int) ($counts['sent'] ?? 0);
    $failed = (int) ($counts['failed'] ?? 0);
    $pending = (int) ($counts['pending'] ?? 0) + (int) ($counts['sending'] ?? 0);
    $uncertain = (int) ($counts['uncertain'] ?? 0);
    $skipped = (int) ($counts['skipped'] ?? 0);
    $finished = $campaign->completed_at && in_array($campaign->status, ['completed', 'completed_with_errors', 'failed'], true);
@endphp
@if($campaign->status !== 'draft')
    <div class="cc-result-summary {{ $finished ? ($failed || $uncertain || $skipped ? 'cc-summary-warning' : 'cc-summary-success') : '' }}" role="status" aria-live="polite" @if(in_array($campaign->status, ['queued', 'sending'], true)) data-campaign-refresh @endif>
        <span class="cc-summary-icon">@include('manager.prospects.partials.icon', ['icon' => $finished ? ($failed || $uncertain || $skipped ? 'lost' : 'check') : 'clock'])</span>
        <div>
            @if($finished)
                <strong>Campagne terminée : {{ $sent }} {{ $sent === 1 ? 'email envoyé' : 'emails envoyés' }}, {{ $failed }} {{ $failed === 1 ? 'échec' : 'échecs' }}.</strong>
                <p>Traitement terminé le {{ $campaign->completed_at->format('d/m/Y H:i') }}.</p>
            @elseif($campaign->status === 'scheduled')
                <strong>Campagne programmée.</strong><p>Envoi programmé le {{ $campaign->scheduled_at?->utc()->format('d/m/Y à H:i') }} UTC · {{ $pending }} destinataire(s) en attente.</p><p>L’envoi par lots démarrera à la date prévue.</p>
            @elseif($campaign->status === 'queued')
                <strong>Campagne en attente d’envoi.</strong><p>{{ $pending }} destinataire(s) en attente. Les résultats apparaîtront après traitement par le serveur de messagerie.</p>
            @else
                <strong>Envoi en cours : {{ $sent }} email(s) envoyé(s), {{ $failed }} échec(s), {{ $pending }} en attente.</strong><p>Les résultats s’actualisent automatiquement pendant l’envoi.</p>
            @endif
            @if($uncertain || $skipped)<p>@if($uncertain){{ $uncertain }} envoi(s) à vérifier, sans renvoi automatique. @endif @if($skipped){{ $skipped }} destinataire(s) ignoré(s).@endif Consultez les détails ci-dessous.</p>@endif
            @if($campaign->status === 'queued')<p>Actualisation automatique toutes les 15 secondes.</p>@endif
            @if(in_array($campaign->status, ['queued', 'sending'], true) && $pending)
                <p>Les campagnes partagent une limite de 150 emails par heure et par compte SMTP. Les destinataires en attente sont repris automatiquement ; une limite atteinte ne constitue pas un échec.</p>
            @endif
        </div>
        <a href="#campaign-results" class="cc-button cc-secondary">Voir les détails</a>
    </div>
@endif
