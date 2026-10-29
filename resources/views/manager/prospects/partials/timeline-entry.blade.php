@php
    $isStatus = $entry->kind === 'status';
    $technicalTitles = [
        'subscription_paid' => 'Abonnement DalyTrac payé',
        'subscription_success' => 'Abonnement DalyTrac payé',
        'account.registered' => 'Compte DalyTrac créé',
        'email_verified' => 'Email vérifié',
        'email.verified' => 'Email vérifié',
        'Compte DalyTrac créé / email vérifié' => 'Compte DalyTrac créé et email vérifié',
    ];
    $titles = ['note' => 'Note ajoutée', 'manual_contact' => 'Contact enregistré', 'call' => 'Appel consigné',
        'email' => 'Email consigné', 'demo' => 'Démo consignée', 'follow_up' => 'Relance planifiée', 'campaign' => 'Email de campagne envoyé'];
    $icons = ['note' => 'edit', 'manual_contact' => 'users', 'call' => 'phone', 'email' => 'mail',
        'demo' => 'eye', 'follow_up' => 'clock', 'campaign' => 'send'];
    if ($isStatus) {
        $title = 'Statut passé de '.\App\Enums\ProspectStatus::from($entry->from_status)->label().' à '.\App\Enums\ProspectStatus::from($entry->to_status)->label();
        $eventIcon = $entry->to_status === 'client' ? 'check' : 'reset';
    } elseif (in_array($entry->kind, ['campaign_opened', 'campaign_clicked'], true)) {
        $title = ($entry->kind === 'campaign_opened' ? 'Email de la campagne « ' : 'Lien de la campagne « ').$entry->subject.($entry->kind === 'campaign_opened' ? ' » ouvert' : ' » cliqué');
        $eventIcon = $entry->kind === 'campaign_opened' ? 'mail' : 'link';
    } elseif ($entry->type === 'automatic') {
        $subject = trim($entry->subject ?? '');
        $title = $technicalTitles[$subject] ?? ($subject && !preg_match('/^[a-z][a-z0-9_.]+$/', $subject) ? $subject : \Illuminate\Support\Str::limit($entry->body ?: 'Modification enregistrée dans DalyTrac', 110));
        $eventIcon = $title === 'Abonnement DalyTrac payé' ? 'check' : ($title === 'Lien LinkedIn consulté' ? 'link' : 'users');
    } else {
        $title = $titles[$entry->type] ?? 'Activité consignée';
        $eventIcon = $icons[$entry->type] ?? 'edit';
    }
    $origin = $authors[$entry->manager_id] ?? match($entry->source ?? null) {
        'stripe' => 'Paiement Stripe confirmé', 'registration' => 'Inscription DalyTrac', 'linkedin_click' => 'Lien LinkedIn', 'campaign' => 'Campagne email', 'campaign_unsubscribe' => 'Désabonnement campagne', 'manual' => 'Manager', default => ($entry->type === 'campaign' ? 'Campagne email' : ($entry->type === 'automatic' ? 'DalyTrac' : 'Manager')),
    };
@endphp
<li class="pd-event {{ $isStatus ? 'pd-event-status' : '' }}">
    <span class="pd-event-icon">@include('manager.prospects.partials.icon', ['icon' => $eventIcon])</span>
    <div class="pd-event-content">
        <h3>{{ $title }}</h3>
        <p class="pd-event-meta"><time datetime="{{ \Carbon\CarbonImmutable::parse($entry->occurred_at, 'UTC')->utc()->toIso8601String() }}">{{ \Carbon\CarbonImmutable::parse($entry->occurred_at, 'UTC')->utc()->format('d/m/Y H:i') }} UTC</time><span aria-hidden="true">·</span>{{ $origin }}</p>
        @if(!$isStatus && $entry->from_status && $entry->to_status)<p class="pd-event-body">Statut passé de <strong>{{ \App\Enums\ProspectStatus::from($entry->from_status)->label() }}</strong> à <strong>{{ \App\Enums\ProspectStatus::from($entry->to_status)->label() }}</strong></p>@endif
        @if($entry->subject && $entry->type !== 'automatic' && !in_array($entry->kind, ['campaign_opened', 'campaign_clicked'], true))<p class="pd-event-subject">{{ $technicalTitles[$entry->subject] ?? $entry->subject }}</p>@endif
        @if($entry->body)<p class="pd-event-body">{{ $entry->body }}</p>@endif
        @if($entry->due_at)<p class="pd-event-due">@include('manager.prospects.partials.icon', ['icon' => 'clock']) Échéance : <time datetime="{{ \Carbon\CarbonImmutable::parse($entry->due_at, 'UTC')->utc()->toIso8601String() }}">{{ \Carbon\CarbonImmutable::parse($entry->due_at, 'UTC')->utc()->format('d/m/Y H:i') }} UTC</time></p>@endif
    </div>
</li>
