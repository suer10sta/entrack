<form method="POST" action="{{ route('manager.prospects.store') }}" class="overflow-hidden">
            @csrf <input type="hidden" name="_prospect_modal" value="create">
            <div class="grid grid-cols-1 gap-x-5 gap-y-4 p-5 sm:grid-cols-2 sm:p-6">
                @foreach([
                    'name' => ['Prénom et nom *', 'text', 'name', 255],
                    'email' => ['Email', 'email', 'email', 255],
                    'phone' => ['Téléphone', 'tel', 'tel', 64],
                    'company_name' => ['Société', 'text', 'organization', 255],
                ] as $field => [$label, $type, $autocomplete, $limit])
                    <div>
                        <label for="create-{{ $field }}" class="mb-1.5 block text-[13px] font-medium text-[#0B2638]">{{ $label }}</label>
                        <input id="create-{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $autocomplete }}" value="{{ old($field) }}" maxlength="{{ $limit }}" @required($field === 'name') @if($errors->has($field)) aria-invalid="true" @endif
                            class="block h-9 w-full rounded-lg border-slate-300 px-3 py-1.5 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]">
                    </div>
                @endforeach

                @php
                    $prospectCountries = json_decode(file_get_contents(base_path('countries.json')), true) ?? [];
                    $selectedCountry = old('country', '');
                @endphp
                <div class="crm-country-field">
                    <label for="create-country" class="mb-1.5 block text-[13px] font-medium text-[#0B2638]">Pays</label>
                    <select id="create-country" name="country" autocomplete="country-name" data-prospect-country @if($errors->has('country')) aria-invalid="true" @endif class="block h-9 w-full rounded-lg border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]">
                        <option value="">Choisir un pays</option>
                        @if($selectedCountry && !in_array($selectedCountry, array_column($prospectCountries, 'name'), true))<option value="{{ $selectedCountry }}" selected>{{ $selectedCountry }}</option>@endif
                        @foreach($prospectCountries as $country)<option value="{{ $country['name'] }}" @selected($selectedCountry === $country['name'])>{{ __('countries.'.$country['code']) }}</option>@endforeach
                    </select>
                </div>

                <div>
                    <label for="create-source" class="mb-1.5 block text-[13px] font-medium text-[#0B2638]">Source d’acquisition</label>
                    <select id="create-source" name="source" @if($errors->has('source')) aria-invalid="true" @endif class="block h-9 w-full rounded-lg border-slate-300 py-1.5 pl-3 pr-8 text-sm focus:border-[#2D7080] focus:ring-[#2D7080]">
                        <option value="">Choisir une source</option>
                        @foreach(['Google', 'Google Ads', 'LinkedIn', 'Facebook', 'Instagram', 'Email', 'Campagne', 'Salon / Événement', 'Recommandation', 'Site web', 'Autre'] as $source)
                            <option value="{{ $source }}" @selected(old('source', '') === $source)>{{ $source }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-slate-100 bg-slate-50/70 px-5 py-4 sm:px-6">
                <button type="button" data-close-prospect-modal class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-600 hover:bg-slate-50">Annuler</button>
                <button type="submit" class="rounded-lg bg-[#2D7080] px-4 py-2 text-sm font-semibold text-white hover:bg-[#0B2638] focus:outline-none focus:ring-2 focus:ring-[#2D7080] focus:ring-offset-2">Créer le prospect</button>
            </div>
        </form>
