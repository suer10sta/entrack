@php
    $editingProspect = $editingProspect ?? null;
    $editOpen = $editingProspect && (session('prospect_edit') || request()->query('modal') === 'edit');
    $editErrors = $editOpen && old('_prospect_modal') === 'edit';
    $acquisitionSources = ['Google', 'Google Ads', 'LinkedIn', 'Facebook', 'Instagram', 'Email', 'Campagne', 'Salon / Événement', 'Recommandation', 'Site web', 'Autre'];
    $selectedSource = $editErrors ? old('source', '') : ($editingProspect?->source ?? '');
@endphp
<dialog id="prospect-edit-modal" class="crm-form-modal" aria-labelledby="prospect-edit-title" aria-describedby="prospect-edit-description" data-prospect-modal="edit" @if($editOpen) data-auto-open @endif>
    <header class="crm-modal-header">
        <span class="crm-modal-heading-icon">@include('manager.prospects.partials.icon', ['icon' => 'edit'])</span>
        <div><h2 id="prospect-edit-title">Modifier le prospect</h2><p id="prospect-edit-description">Mettez à jour ses coordonnées. * Champ obligatoire.</p></div>
        <button type="button" class="crm-icon-button" data-close-prospect-modal aria-label="Fermer" title="Fermer">@include('manager.prospects.partials.icon', ['icon' => 'close'])</button>
    </header>
    @if($editErrors && $errors->any())<div class="crm-modal-feedback">@include('manager.prospects.partials.feedback')</div>@endif
    <form method="POST" action="{{ $editingProspect ? route('manager.prospects.update', $editingProspect) : '' }}" data-prospect-edit-form>
        @csrf @method('PATCH')<input type="hidden" name="_prospect_modal" value="edit">
        <div class="grid grid-cols-1 gap-x-5 gap-y-4 p-5 sm:grid-cols-2 sm:p-6">
            @foreach(['name' => ['Prénom et nom *', 'text', 'name', 255], 'email' => ['Email', 'email', 'email', 255], 'phone' => ['Téléphone', 'tel', 'tel', 64], 'company_name' => ['Société', 'text', 'organization', 255], 'country' => ['Pays', 'text', 'country-name', 100]] as $field => [$label, $type, $autocomplete, $limit])
                <div><label for="edit-{{ $field }}" class="mb-1.5 block text-[13px] font-medium text-[#0B2638]">{{ $label }}</label>
                    <input id="edit-{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" maxlength="{{ $limit }}" value="{{ $editErrors ? old($field) : $editingProspect?->$field }}" @required($field === 'name') @if($editErrors && $errors->has($field)) aria-invalid="true" @endif class="block h-9 w-full rounded-lg border-slate-300 px-3 py-1.5 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]">
                </div>
            @endforeach
            <div><label for="edit-source" class="mb-1.5 block text-[13px] font-medium text-[#0B2638]">Source d’acquisition</label>
                <select id="edit-source" name="source" class="block h-9 w-full rounded-lg border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]">
                    <option value="">Choisir une source</option>
                    @if($selectedSource && !in_array($selectedSource, $acquisitionSources, true))<option value="{{ $selectedSource }}" selected data-custom-source>{{ $selectedSource }}</option>@endif
                    @foreach($acquisitionSources as $sourceOption)<option value="{{ $sourceOption }}" @selected($selectedSource === $sourceOption)>{{ $sourceOption }}</option>@endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
            <button type="button" class="crm-modal-cancel" data-close-prospect-modal>Annuler</button>
            <button type="submit" class="crm-modal-submit">@include('manager.prospects.partials.icon', ['icon' => 'check']) Enregistrer les modifications</button>
        </div>
    </form>
</dialog>
