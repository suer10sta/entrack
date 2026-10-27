@foreach(['create' => ['Nouveau prospect', 'Ajoutez les coordonnées de votre contact. * Champ obligatoire.', 'new'], 'import' => ['Importer des prospects', 'Ajoutez vos contacts depuis un classeur Excel.', 'users']] as $kind => [$title, $description, $icon])
<dialog id="prospect-{{ $kind }}-modal" class="crm-form-modal crm-form-modal-{{ $kind }}" aria-labelledby="prospect-{{ $kind }}-title" aria-describedby="prospect-{{ $kind }}-description" data-prospect-modal="{{ $kind }}" @if($activeModal === $kind) data-auto-open @endif>
    <header class="crm-modal-header">
        <span class="crm-modal-heading-icon">@include('manager.prospects.partials.icon', ['icon' => $icon])</span>
        <div><h2 id="prospect-{{ $kind }}-title">{{ $title }}</h2><p id="prospect-{{ $kind }}-description">{{ $description }}</p></div>
        <button type="button" class="crm-icon-button" data-close-prospect-modal aria-label="Fermer" title="Fermer">@include('manager.prospects.partials.icon', ['icon' => 'close'])</button>
    </header>
    @if($activeModal === $kind && $errors->any())<div class="crm-modal-feedback">@include('manager.prospects.partials.feedback')</div>@endif
    <div class="crm-modal-content">@include('manager.prospects.partials.'.($kind === 'create' ? 'create-form' : 'import-content'))</div>
</dialog>
@endforeach
