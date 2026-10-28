@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/prospects-list.css') }}">
@php($statusIcons = ['nouveau' => 'new', 'contacte' => 'phone', 'engage' => 'send', 'demo_essai' => 'clock', 'client' => 'check', 'perdu' => 'lost'])
<div class="crm-prospects space-y-6 p-4 md:p-6">
    <header class="flex flex-wrap items-center justify-between gap-4">
        <div><h1 class="text-2xl font-bold text-[#0B2638]">Prospects</h1><p class="text-sm text-slate-500">Suivez vos contacts et leur progression dans le parcours DalyTrac.</p></div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('manager.prospects.import') }}" data-open-prospect-modal="import" aria-haspopup="dialog" aria-controls="prospect-import-modal" class="crm-header-action inline-flex items-center gap-2 rounded-xl border border-[#2D7080] px-4 py-2 font-semibold text-[#2D7080]"><svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 16V4m-4 4 4-4 4 4M4 16v4h16v-4"/></svg>Importer Excel</a>
            <a href="{{ route('manager.prospects.create') }}" data-open-prospect-modal="create" aria-haspopup="dialog" aria-controls="prospect-create-modal" class="crm-header-action inline-flex items-center gap-2 rounded-xl bg-[#2D7080] px-4 py-2 font-semibold text-white"><svg class="h-4 w-4" aria-hidden="true" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>Nouveau prospect</a>
        </div>
    </header>
    @if(!$activeModal || !$errors->any()) @include('manager.prospects.partials.feedback') @endif
    <div class="crm-kpis">
        <a href="{{ route('manager.prospects.index') }}" class="crm-kpi crm-tone-total" @if(empty($filters['status'])) aria-current="page" @endif>
            <span class="crm-kpi-icon">@include('manager.prospects.partials.icon', ['icon' => 'users'])</span>
            <span class="crm-kpi-label">Total prospects</span><strong>{{ number_format($counts->sum(), 0, ',', ' ') }}</strong>
        </a>
        @foreach(\App\Enums\ProspectStatus::cases() as $status)
            <a href="{{ route('manager.prospects.index', ['status' => $status->value]) }}" class="crm-kpi crm-tone-{{ $status->value }}" @if(($filters['status'] ?? '') === $status->value) aria-current="page" @endif>
                <span class="crm-kpi-icon">@include('manager.prospects.partials.icon', ['icon' => $statusIcons[$status->value]])</span>
                <span class="crm-kpi-label">{{ $status->label() }}</span><strong>{{ number_format($counts[$status->value] ?? 0, 0, ',', ' ') }}</strong>
            </a>
        @endforeach
    </div>
    <form method="GET" class="dt-card crm-toolbar">
        <label class="crm-search" for="q">Rechercher<span class="crm-search-input">@include('manager.prospects.partials.icon', ['icon' => 'search'])<input id="q" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="255" placeholder="Nom, email, téléphone, société" class="block w-full rounded-lg border-slate-300 text-sm"></span></label>
        <label class="text-sm" for="filter-status">Statut<select id="filter-status" name="status" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="">Tous</option>@foreach(\App\Enums\ProspectStatus::cases() as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>@endforeach</select></label>
        <label class="text-sm" for="source">Source<select id="source" name="source" class="mt-1 block w-full rounded-lg border-slate-300 text-sm"><option value="">Toutes</option>@foreach($sources as $source)<option value="{{ $source }}" @selected(($filters['source'] ?? '') === $source)>{{ $source }}</option>@endforeach</select></label>
        <label class="text-sm" for="sort">Trier par<select id="sort" name="sort" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">@foreach(['newest' => 'Plus récents', 'oldest' => 'Plus anciens', 'activity' => 'Dernière activité'] as $value => $label)<option value="{{ $value }}" @selected(($filters['sort'] ?? 'newest') === $value)>{{ $label }}</option>@endforeach</select></label>
        <label class="text-sm" for="per-page">Par page<select id="per-page" name="per_page" class="mt-1 block w-full rounded-lg border-slate-300 text-sm">@foreach([15,25,50,100] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 15) === $size)>{{ $size }}</option>@endforeach</select></label>
        <button class="rounded-lg bg-[#0B2638] px-4 py-2 text-sm font-semibold text-white">@include('manager.prospects.partials.icon', ['icon' => 'filter']) Filtrer</button><a href="{{ route('manager.prospects.index') }}" class="px-2 py-2 text-sm text-[#2D7080]">@include('manager.prospects.partials.icon', ['icon' => 'reset']) Réinitialiser</a>
    </form>
    <div class="dt-card overflow-hidden">
        <div class="crm-table-scroll">
            <table class="crm-table w-full text-sm"><thead><tr><th>Prospect / Société</th><th>Coordonnées</th><th>Source</th><th>Statut</th><th>Dernière activité</th><th>Date d’ajout</th><th>Actions</th></tr></thead>
                <tbody>@forelse($prospects as $prospect)
                    @php($lastActivity = collect([$prospect->activities_max_occurred_at, $prospect->status_histories_max_occurred_at])->filter()->max())
                    <tr>
                        <td data-label="Prospect / Société"><div class="crm-contact"><span class="crm-avatar" aria-hidden="true">{{ collect(preg_split('/\s+/u', trim($prospect->name), -1, PREG_SPLIT_NO_EMPTY))->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->implode('') }}</span><div class="crm-contact-name"><a href="{{ route('manager.prospects.show', $prospect) }}">{{ $prospect->name }}</a>@if($prospect->company_name)<p>{{ $prospect->company_name }}</p>@endif</div></div></td>
                        <td data-label="Coordonnées"><div class="crm-contact-details">@if($prospect->email)<span>@include('manager.prospects.partials.icon', ['icon' => 'mail'])<span>{{ $prospect->email }}</span></span>@endif @if($prospect->phone)<span>@include('manager.prospects.partials.icon', ['icon' => 'phone'])<span>{{ $prospect->phone }}</span></span>@endif @if(!$prospect->email && !$prospect->phone)<span class="crm-muted">Non renseignées</span>@endif</div></td>
                        <td data-label="Source">@if($prospect->source)<span class="crm-source">{{ $prospect->source }}</span>@else<span class="crm-muted">—</span>@endif</td>
                        <td data-label="Statut"><span class="crm-status crm-tone-{{ $prospect->status->value }}">@include('manager.prospects.partials.icon', ['icon' => $statusIcons[$prospect->status->value]]){{ $prospect->status->label() }}</span></td>
                        <td data-label="Dernière activité" class="crm-date">{{ $lastActivity ? \Carbon\CarbonImmutable::parse($lastActivity)->format('d/m/Y H:i') : 'Aucune' }}</td>
                        <td data-label="Date d’ajout" class="crm-date">{{ $prospect->created_at->format('d/m/Y') }}</td>
                        <td data-label="Actions"><div class="crm-actions">
                            <a href="{{ route('manager.prospects.show', $prospect) }}" class="crm-icon-button" aria-label="Voir le prospect" title="Voir le prospect">@include('manager.prospects.partials.icon', ['icon' => 'eye'])</a>
                            <a href="{{ route('manager.prospects.edit', $prospect) }}" data-open-prospect-modal="edit" aria-haspopup="dialog" aria-controls="prospect-edit-modal" data-edit-action="{{ route('manager.prospects.update', $prospect) }}" data-edit-prospect="{{ json_encode($prospect->only(['name', 'email', 'phone', 'company_name', 'country', 'source'])) }}" class="crm-icon-button" aria-label="Modifier" title="Modifier">@include('manager.prospects.partials.icon', ['icon' => 'edit'])</a>
                            @if($prospect->public_token)
                                <button type="button" class="crm-icon-button" aria-label="Voir le lien tracké" title="Voir le lien tracké" aria-haspopup="dialog" aria-controls="crm-link-dialog" data-show-tracked-link="{{ $prospect->linkedin_url }}">@include('manager.prospects.partials.icon', ['icon' => 'link'])</button>
                            @endif
                        </div></td>
                    </tr>
                @empty<tr><td colspan="7" class="py-10 text-center text-slate-500">Aucun prospect ne correspond à votre recherche.</td></tr>@endforelse</tbody>
            </table>
        </div><div class="crm-prospects-pagination border-t border-slate-100 p-4">{{ $prospects->onEachSide(1)->links('pagination::bootstrap-4') }}</div>
    </div>
</div>
<dialog id="crm-link-dialog" class="crm-link-dialog" aria-labelledby="crm-link-title" data-prospect-link>
    <div class="crm-dialog-header"><h2 id="crm-link-title">Lien tracké</h2><button type="button" class="crm-icon-button" data-close-tracked-link aria-label="Fermer" title="Fermer">@include('manager.prospects.partials.icon', ['icon' => 'close'])</button></div>
    <label class="sr-only" for="crm-link-value">Lien tracké du prospect</label>
    <textarea id="crm-link-value" readonly rows="3" data-prospect-link-value></textarea>
    <button type="button" class="crm-copy-button" data-copy-prospect-link>@include('manager.prospects.partials.icon', ['icon' => 'copy']) Copier le lien</button>
    <p role="status" aria-live="polite" data-copy-prospect-link-status></p>
</dialog>
<script src="{{ asset('js/prospect-referral-link.js') }}" defer></script>
<script src="{{ asset('js/prospects-list.js') }}" defer></script>
@include('manager.prospects.partials.dialogs')
@include('manager.prospects.partials.edit-dialog')
<link rel="stylesheet" href="{{ asset('css/prospect-modals.css') }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.default.min.css">
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js" defer></script>
<script src="{{ asset('js/prospect-modals.js') }}" defer></script>
@endsection
