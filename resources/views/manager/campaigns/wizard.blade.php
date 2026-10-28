@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/crm-campaigns.css') }}">
<div class="cc-page cw-page p-4 md:p-6">
    <a class="cc-back" href="{{ route('manager.campaigns.index') }}">← Toutes les campagnes</a>
    <header class="cc-header"><div><h1>Créer une campagne</h1><p>Préparez votre message et choisissez les prospects à contacter.</p></div><span class="cc-badge">Brouillon</span></header>
    <section class="cw-progress-card" aria-label="Progression de la campagne">
        <div class="cw-step-caption"><strong>Étape {{ $step }} sur 3</strong><span>{{ [1 => 'Informations', 2 => 'Audience cible', 3 => 'Contenu de l’email'][$step] }}</span></div>
        <div class="cw-progress" role="progressbar" aria-label="Étapes de création" aria-valuemin="0" aria-valuemax="3" aria-valuenow="{{ $step }}"><span style="width:{{ round($step / 3 * 100) }}%"></span></div>
        <ol class="cw-steps">@foreach([1 => 'Informations', 2 => 'Audience cible', 3 => 'Contenu de l’email'] as $number => $label)<li class="{{ $step === $number ? 'is-current' : ($step > $number ? 'is-complete' : '') }}" @if($step === $number) aria-current="step" @endif><span>{{ $number }}</span>@if($campaign)<a href="{{ route('manager.campaigns.show', ['campaign' => $campaign, 'step' => $number]) }}">{{ $label }}</a>@else{{ $label }}@endif</li>@endforeach</ol>
    </section>
    @include('manager.prospects.partials.feedback')
    <div class="cw-layout">
        <main class="cc-stack">
            @if($step === 1)
                <section class="cc-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'edit']) Informations de la campagne</h2>
                    <form id="campaign-information" method="POST" action="{{ $campaign ? route('manager.campaigns.setup', $campaign) : route('manager.campaigns.store') }}" class="cc-form" data-wizard-information>
                        @csrf <input type="hidden" name="_wizard" value="1">@if($campaign) <input type="hidden" name="step" value="1"><input type="hidden" name="revision" value="{{ $campaign->revision }}">@endif
                        <label>Nom interne de la campagne *<input name="name" required maxlength="255" value="{{ old('name', $campaign?->name) }}" placeholder="Ex. Présentation DalyTrac" data-summary-field="name"></label>
                        <label>Objet de l’email *<input name="subject" required maxlength="255" value="{{ old('subject', $campaign?->subject) }}" placeholder="L’objet que verront vos destinataires" data-summary-field="subject"></label>
                        <label>Texte de prévisualisation / preheader <span class="cc-help">(facultatif)</span><input name="preheader" maxlength="255" value="{{ old('preheader', $campaign?->preheader) }}" placeholder="Une phrase courte pour compléter l’objet"></label>
                        <label>utm_campaign <span class="cc-help">(facultatif)</span><input name="utm_campaign" maxlength="255" value="{{ old('utm_campaign', $campaign?->utm_campaign) }}" placeholder="Ex. lancement_dalytrac" data-summary-field="utm_campaign"></label>
                        <p class="cc-help">Les paramètres de source et de support sont ajoutés automatiquement au bouton de votre email.</p>
                    </form>
                </section>
                <div class="cw-navigation"><a href="{{ route('manager.campaigns.index') }}" class="cc-button cc-secondary">← Précédent</a><button form="campaign-information" class="cc-button cc-primary">Suivant : audience cible →</button></div>
            @elseif($step === 2)
                @include('manager.campaigns.partials.audience', ['inWizard' => true])
                <div class="cw-navigation"><button form="campaign-audience-selection" name="wizard_next" value="1" class="cc-button cc-secondary">← Précédent</button><button form="campaign-audience-selection" name="wizard_next" value="3" class="cc-button cc-primary">Suivant : contenu de l’email →</button></div>
            @else
                <section class="cc-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'mail']) Contenu de l’email</h2>
                    <form id="campaign-composition" method="POST" enctype="multipart/form-data" action="{{ route('manager.campaigns.setup', $campaign) }}" class="cc-form" data-wizard-composition>
                        @csrf <input type="hidden" name="_wizard" value="1"><input type="hidden" name="step" value="3"><input type="hidden" name="revision" value="{{ $campaign->revision }}">
                        <label>Message de l’email *<textarea name="content" required maxlength="50000" rows="6" placeholder="Rédigez votre message sans formule d’appel…">{{ old('content', $campaign->content) }}</textarea></label>
                        <p class="cc-help">La formule personnalisée « Bonjour + nom du prospect » est automatiquement ajoutée au début de chaque email. Rédigez uniquement votre message ci-dessus ; la signature « L’équipe DalyTrac » est également ajoutée.</p>
                        <div class="cw-field-grid cw-media-grid">
                            @foreach(['image' => 'Image affichée dans l’email', 'attachment' => 'Fichier joint (pièce jointe)'] as $kind => $label)
                                @php($asset = $campaign->media[$kind] ?? null)
                                <fieldset class="cw-media-field"><legend>{{ $label }}</legend>
                                    <label class="cw-file-label"><span class="sr-only">{{ $kind === 'image' ? 'Choisir une image' : 'Choisir un fichier' }}</span><input class="cw-file-input" type="file" name="{{ $kind }}_file" accept="{{ $kind === 'image' ? '.jpg,.jpeg,.png,.gif,.webp' : '.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.odt,.ods,.odp,.rtf,.txt,.csv' }}" data-media-file="{{ $kind }}"></label>
                                    <label><span class="sr-only">URL {{ $kind === 'image' ? 'de l’image' : 'du fichier' }}</span><input type="url" name="{{ $kind }}_url" value="{{ old($kind.'_url') }}" placeholder="… ou collez une URL {{ $kind === 'image' ? 'd’image' : 'de fichier' }}" maxlength="2048" data-media-url="{{ $kind }}"></label>
                                    <p class="cc-help">{{ $kind === 'image' ? 'JPG, PNG, GIF, WEBP · 2 Mo max. Affichée dans le corps de l’email.' : 'PDF, Word, Excel, PowerPoint, OpenDocument, RTF, TXT, CSV · 10 Mo max.' }}</p>
                                    @if($asset)<div class="cw-saved-asset"><span>@include('manager.prospects.partials.icon', ['icon' => 'check']) {{ $asset['name'] }} <small>({{ number_format($asset['size'] / 1024, 0, ',', ' ') }} Ko)</small></span><label class="cc-check"><input type="checkbox" name="remove_{{ $kind }}" value="1" @checked(old('remove_'.$kind))> Retirer</label></div>@endif
                                </fieldset>
                            @endforeach
                        </div>
                        <fieldset class="cw-cta-section"><legend>@include('manager.prospects.partials.icon', ['icon' => 'link']) Bouton d’appel à l’action (CTA) & liens UTM <span class="cc-help">(facultatif)</span></legend>
                            <div class="cw-field-grid"><label>Texte du bouton<input name="cta_text" maxlength="120" value="{{ old('cta_text', $campaign->cta_text) }}" placeholder="Ex. Découvrir DalyTrac"></label><label>URL de destination<input name="cta_url" type="url" maxlength="2048" value="{{ old('cta_url', $campaign->cta_url) }}" placeholder="https://dalytrac.com/produit" data-cta-destination></label></div>
                            <label>Lien final généré avec paramètres UTM<textarea class="cw-utm-output" rows="2" readonly data-cta-generated data-utm-campaign="{{ $campaign->utm_campaign }}" placeholder="Renseignez une URL de destination pour afficher le lien final.">{{ $campaign->ctaLink() }}</textarea></label>
                            <p class="cc-help">Source : dalytrac · Support : email. À l’envoi, chaque destinataire reçoit un lien de suivi unique. Renseignez le texte et l’URL pour afficher le bouton.</p>
                        </fieldset>
                        @php($scheduleEnabled = (bool) old('schedule_enabled', (bool) $campaign->scheduled_at))
                        <fieldset class="cw-schedule"><legend>@include('manager.prospects.partials.icon', ['icon' => 'clock']) Date d’envoi</legend>
                            <input type="hidden" name="schedule_enabled" value="0">
                            <label class="cw-schedule-toggle"><input type="checkbox" role="switch" name="schedule_enabled" value="1" data-schedule-toggle aria-controls="campaign-schedule-fields" @checked($scheduleEnabled)> <span>Programmer l’envoi à une date ultérieure</span></label>
                            <div id="campaign-schedule-fields" class="cw-field-grid" data-schedule-fields @if(!$scheduleEnabled) hidden @endif>
                                <label>Date d’envoi<input type="date" name="scheduled_date" min="{{ now('UTC')->toDateString() }}" value="{{ old('scheduled_date', $campaign->scheduled_at?->utc()->format('Y-m-d')) }}" @disabled(!$scheduleEnabled) @required($scheduleEnabled)></label>
                                <label>Heure d’envoi (UTC)<input type="time" name="scheduled_time" value="{{ old('scheduled_time', $campaign->scheduled_at?->utc()->format('H:i')) }}" @disabled(!$scheduleEnabled) @required($scheduleEnabled)></label>
                            </div>
                            <p class="cc-help" data-schedule-help>{{ $scheduleEnabled ? 'L’envoi commencera à partir de la date et de l’heure choisies (UTC).' : 'Désactivé : l’envoi démarrera après confirmation.' }}</p>
                        </fieldset>
                        <button class="cc-button cc-secondary" formnovalidate>@include('manager.prospects.partials.icon', ['icon' => 'eye']) Enregistrer et afficher l’aperçu</button>
                        <p class="cc-help" data-preview-feedback role="status">L’aperçu correspond au contenu et aux fichiers enregistrés.</p>
                        <div class="cw-send-confirm"><label class="cc-check"><input type="checkbox" name="confirm" value="1"> Je confirme l’envoi de ce message à {{ $selected->count() }} destinataire(s) enregistré(s).</label></div>
                    </form>
                </section>
                <section class="cc-card"><h2>@include('manager.prospects.partials.icon', ['icon' => 'eye']) Aperçu de l’email</h2>
                    @if(trim($campaign->content) !== '')<iframe class="cw-preview" title="Aperçu de l’email enregistré" src="{{ route('manager.campaigns.preview', $campaign) }}" sandbox="" referrerpolicy="no-referrer"></iframe>@else<p class="cc-help">Enregistrez votre message et vos fichiers pour afficher l’aperçu.</p>@endif
                </section>
                <div class="cw-navigation"><button form="campaign-composition" name="back_to_audience" value="1" formnovalidate class="cc-button cc-secondary">← Précédent</button><button form="campaign-composition" formaction="{{ route('manager.campaigns.send', $campaign) }}" name="wizard_final" value="1" class="cc-button cc-primary" @disabled($selected->isEmpty())>@include('manager.prospects.partials.icon', ['icon' => 'send']) <span data-send-label>{{ $scheduleEnabled ? 'Programmer la campagne' : 'Envoyer la campagne' }}</span></button></div>
            @endif
        </main>
        <aside class="cc-card cw-summary"><h2>@include('manager.prospects.partials.icon', ['icon' => 'mail']) Résumé de la campagne</h2>
            <dl><div><dt>Nom de la campagne</dt><dd data-summary-value="name">{{ $campaign?->name ?: 'À renseigner' }}</dd></div><div><dt>Objet de l’email</dt><dd data-summary-value="subject">{{ $campaign?->subject ?: 'À renseigner' }}</dd></div><div><dt>Audience sélectionnée</dt><dd data-wizard-audience-label>{{ \App\Services\CrmCampaignAudience::LABELS[$campaign?->audience ?? 'custom'] ?? 'Sélection personnalisée' }}</dd></div><div class="cw-summary-count"><dt>Nombre de destinataires</dt><dd data-wizard-recipient-count>{{ $selected->count() }}</dd></div><div><dt>UTM campagne</dt><dd data-summary-value="utm_campaign">{{ $campaign?->utm_campaign ?: 'Non définie' }}</dd></div><div><dt>Envoi</dt><dd data-schedule-summary>{{ $campaign?->scheduled_at ? $campaign->scheduled_at->utc()->format('d/m/Y à H:i').' UTC' : 'Dès confirmation' }}</dd></div></dl>
            <p class="cc-help">Envoi par lots via la messagerie DalyTrac. Les destinataires ne sont contactés qu’après confirmation de l’envoi SMTP.</p>
            @if($step === 3 && $selected->isEmpty())<p class="cw-empty-audience">Sélectionnez au moins un destinataire à l’étape 2 avant l’envoi.</p>@endif
        </aside>
    </div>
</div>
<script src="{{ asset('js/crm-campaigns.js') }}" defer></script>
<script src="{{ asset('js/crm-campaign-wizard.js') }}" defer></script>
@endsection
