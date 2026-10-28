@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/crm-campaigns.css') }}">
<div class="cc-page p-4 md:p-6">
    <a class="cc-back" href="{{ route('manager.campaigns.show', $campaign) }}">← Retour à la campagne</a>
    <header class="cc-header"><div><h1>Email envoyé</h1><p>{{ $campaign->name }} · {{ $campaign->subject }}</p></div><span class="cc-badge cc-sent">Aperçu en lecture seule</span></header>
    @include('manager.prospects.partials.feedback')
    <section class="cc-card cc-sent-email-toolbar">
        <form method="GET" action="{{ route('manager.campaigns.sent-email', $campaign) }}" class="cc-filters">
            <label for="sent-email-recipient">Destinataire<select id="sent-email-recipient" name="recipient">@foreach($recipients as $option)<option value="{{ $option->id }}" @selected($option->id === $recipient->id)>{{ $option->name }} — {{ $option->email }}</option>@endforeach</select></label>
            <button class="cc-button cc-secondary">@include('manager.prospects.partials.icon', ['icon' => 'eye']) Afficher l’email</button>
        </form>
        <p class="cc-help">Envoyé le {{ $recipient->sent_at->format('d/m/Y à H:i') }} · {{ $recipient->email }}</p>
        <p class="cc-help">Aucun email n’est renvoyé. Aucun suivi d’ouverture ou de clic n’est déclenché ; les liens sont désactivés. Aperçu reconstitué à partir des données enregistrées et du modèle actuel.</p>
    </section>
    <section class="cc-card cc-sent-email-card" aria-label="Aperçu de l’email envoyé">
        <iframe class="cc-sent-email-frame" title="Email envoyé à {{ $recipient->name }}" src="{{ route('manager.campaigns.sent-email.content', ['campaign' => $campaign, 'recipient' => $recipient->id]) }}" sandbox="" referrerpolicy="no-referrer"></iframe>
    </section>
</div>
@endsection
