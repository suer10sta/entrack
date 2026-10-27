@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/prospect-detail.css') }}">
@php
    $activityLabels = ['note' => 'Note', 'manual_contact' => 'Contact', 'call' => 'Appel', 'email' => 'Email · saisie manuelle', 'demo' => 'Démo', 'follow_up' => 'Relance / prochaine action'];
    $linkedUser = $prospect->linkedUser;
    $linkedEntreprise = $prospect->linkedEntreprise;
    $initials = collect(preg_split('/\s+/u', trim($prospect->name), -1, PREG_SPLIT_NO_EMPTY))->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('');
@endphp
<div class="pd-page p-4 md:p-6">
    <a href="{{ route('manager.prospects.index') }}" class="pd-back"><span aria-hidden="true">←</span> Retour aux prospects</a>
    <header class="pd-header">
        <div class="pd-identity"><span class="pd-avatar" aria-hidden="true">{{ $initials }}</span><div><h1>{{ $prospect->name }}</h1><p>{{ $prospect->company_name ?: 'Fiche prospect' }}</p></div></div>
        <div class="pd-header-actions">@include('manager.prospects.partials.status', ['status' => $prospect->status])<a class="pd-button pd-primary" data-open-prospect-modal="edit" aria-haspopup="dialog" aria-controls="prospect-edit-modal" href="{{ route('manager.prospects.edit', $prospect) }}">@include('manager.prospects.partials.icon', ['icon' => 'edit']) Modifier</a></div>
    </header>
    @unless((session('prospect_edit') || request()->query('modal') === 'edit') && old('_prospect_modal') === 'edit') @include('manager.prospects.partials.feedback') @endunless
    <div class="pd-layout">
        <aside class="pd-stack">
            <section class="pd-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'users']) Informations du prospect</h2>
                <dl class="pd-info">@foreach(['email' => ['Email', 'mail'], 'phone' => ['Téléphone', 'phone'], 'company_name' => ['Société', 'building'], 'country' => ['Pays', 'globe']] as $field => [$label, $icon])
                    <div><dt>@include('manager.prospects.partials.icon', ['icon' => $icon]) {{ $label }}</dt><dd>{{ $prospect->$field ?: 'Non renseigné' }}</dd></div>
                @endforeach</dl>
                <div class="pd-card-foot">Ajouté le {{ $prospect->created_at->format('d/m/Y H:i') }} UTC @if($prospect->ownerManager) · {{ $prospect->ownerManager->name }} @endif</div>
            </section>
            <section class="pd-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'reset']) Statut CRM</h2>
                <form method="POST" action="{{ route('manager.prospects.status', $prospect) }}" class="pd-form">@csrf @method('PATCH')
                    <label for="status">Nouveau statut<select id="status" name="status">@foreach(\App\Enums\ProspectStatus::cases() as $status)<option value="{{ $status->value }}" @selected(old('status', $prospect->status->value) === $status->value)>{{ $status->label() }}</option>@endforeach</select></label>
                    <label for="note">Motif facultatif<textarea id="note" name="note" maxlength="5000" rows="2">{{ old('note') }}</textarea></label>
                    <button class="pd-button pd-primary">@include('manager.prospects.partials.icon', ['icon' => 'check']) Mettre à jour le statut</button>
                </form>
            </section>
            <section class="pd-card" @if($prospect->public_token) data-prospect-link @endif><h2>@include('manager.prospects.partials.icon', ['icon' => 'link']) Lien LinkedIn</h2><p class="pd-help">Lien tracké personnel du prospect.</p>
                @if($prospect->public_token)
                    <label for="prospect-link" class="sr-only">Lien LinkedIn du prospect</label><textarea id="prospect-link" readonly rows="3" class="pd-link-value" data-prospect-link-value>{{ $prospect->linkedin_url }}</textarea>
                    <button type="button" data-copy-prospect-link class="pd-button pd-secondary">@include('manager.prospects.partials.icon', ['icon' => 'copy']) Copier le lien</button><p class="pd-copy-feedback" role="status" aria-live="polite" data-copy-prospect-link-status></p>
                @else
                    <p class="pd-help">Lien indisponible.</p>
                @endif
            </section>
            <section class="pd-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'send']) Acquisition</h2><span class="pd-source">{{ $prospect->source ?: 'Source non renseignée' }}</span>
                <dl class="pd-info pd-utm">@foreach(['utm_source' => 'Source UTM', 'utm_medium' => 'Support UTM', 'utm_campaign' => 'Campagne', 'utm_content' => 'Contenu', 'utm_term' => 'Mot-clé'] as $field => $label)@if($prospect->$field)<div><dt>{{ $label }}</dt><dd>{{ $prospect->$field }}</dd></div>@endif @endforeach</dl>
                @if($prospect->source_captured_at)<div class="pd-card-foot">Origine capturée le {{ $prospect->source_captured_at->format('d/m/Y H:i') }} UTC</div>@endif
            </section>
            <details class="pd-card pd-archive"><summary>Archiver ce prospect</summary><p class="pd-help">Le prospect disparaîtra de la liste. Historique, activités et liens au compte seront conservés.</p><form method="POST" action="{{ route('manager.prospects.archive', $prospect) }}">@csrf @method('DELETE')<button class="pd-button pd-danger">Confirmer l’archivage</button></form></details>
        </aside>
        <div class="pd-stack">
            <section class="pd-card"><div class="pd-section-heading"><h2>@include('manager.prospects.partials.icon', ['icon' => 'check']) Compte DalyTrac</h2><span class="pd-account-badge {{ $linkedUser || $linkedEntreprise ? 'pd-is-linked' : '' }}">{{ $linkedUser || $linkedEntreprise ? 'Compte rattaché' : 'Non rattaché' }}</span></div>
                @if($linkedUser || $linkedEntreprise)
                    <dl class="pd-account-grid">
                        @if($linkedUser)<div><dt>Utilisateur</dt><dd>{{ $linkedUser->name }}</dd><p>{{ $linkedUser->email }}</p></div>@endif
                        @if($linkedEntreprise)<div><dt>Entreprise DalyTrac</dt><dd>{{ $linkedEntreprise->name }}</dd></div>@endif
                        @if($prospect->linked_at)<div><dt>Rattachement confirmé</dt><dd>{{ $prospect->linked_at->format('d/m/Y H:i') }} UTC</dd></div>@endif
                        @if($linkedUser?->email_verified_at)<div><dt>Email vérifié</dt><dd>{{ $linkedUser->email_verified_at->format('d/m/Y H:i') }} UTC</dd></div>@endif
                        @if($prospect->trial_started_at)<div><dt>Début de l’essai</dt><dd>{{ $prospect->trial_started_at->format('d/m/Y H:i') }} UTC</dd></div>@endif
                        @if($prospect->first_paid_at)<div><dt>Premier abonnement payé</dt><dd>{{ $prospect->first_paid_at->format('d/m/Y H:i') }} UTC</dd></div>@endif
                    </dl>
                @else<p class="pd-help">Aucun compte DalyTrac n’est actuellement rattaché à ce prospect.</p>@endif
            </section>
            <section class="pd-card"><div class="pd-section-heading"><h2>@include('manager.prospects.partials.icon', ['icon' => 'clock']) Timeline</h2><span class="pd-help">Historique du prospect · UTC</span></div>
                <ol class="pd-timeline">
                    @foreach($timeline as $entry) @include('manager.prospects.partials.timeline-entry') @endforeach
                    @if(!$timeline->hasMorePages())<li class="pd-event pd-event-created"><span class="pd-event-icon">@include('manager.prospects.partials.icon', ['icon' => 'new'])</span><div class="pd-event-content"><h3>Prospect créé</h3><p class="pd-event-meta"><time datetime="{{ $prospect->created_at->toIso8601String() }}">{{ $prospect->created_at->format('d/m/Y H:i') }} UTC</time>@if($prospect->ownerManager)<span aria-hidden="true">·</span>{{ $prospect->ownerManager->name }}@endif</p></div></li>@endif
                </ol><div class="pd-pagination">{{ $timeline->links() }}</div>
            </section>
            <section class="pd-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'edit']) Notes et activités</h2><p class="pd-help">Consignez vos actions. Aucun email ni rappel n’est envoyé automatiquement. Dates en UTC.</p>
                <form method="POST" action="{{ route('manager.prospects.activities.store', $prospect) }}" class="pd-form pd-activity-form">@csrf
                    <label for="type">Type<select id="type" name="type">@foreach($activityLabels as $value => $label)<option value="{{ $value }}" @selected(old('type', 'note') === $value)>{{ $label }}</option>@endforeach</select></label>
                    <label for="due-at">Échéance / date de démo (UTC)<input id="due-at" type="datetime-local" name="due_at" value="{{ old('due_at') }}"><span class="pd-help">Obligatoire pour une relance.</span></label>
                    <label for="subject" class="pd-span">Objet facultatif<input id="subject" name="subject" value="{{ old('subject') }}" maxlength="255"></label>
                    <label for="body" class="pd-span">Description *<textarea id="body" name="body" required maxlength="10000" rows="3">{{ old('body') }}</textarea></label><div class="pd-span"><button class="pd-button pd-primary">@include('manager.prospects.partials.icon', ['icon' => 'new']) Enregistrer l’activité</button></div>
                </form>
            </section>
        </div>
    </div>
</div>
<script src="{{ asset('js/prospect-referral-link.js') }}" defer></script>
@include('manager.prospects.partials.edit-dialog', ['editingProspect' => $prospect])
<link rel="stylesheet" href="{{ asset('css/prospect-modals.css') }}">
<script src="{{ asset('js/prospect-modals.js') }}" defer></script>
@endsection
