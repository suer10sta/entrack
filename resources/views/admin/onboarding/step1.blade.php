@extends('layouts.onboarding')

@section('title', __('messages.onboarding_step1_title') . ' — DalyTrac')

@section('content')
@php
    $countriesList = json_decode(file_get_contents(base_path('countries.json')), true) ?? [];
@endphp
<div class="max-w-5xl mx-auto w-full">
    <p class="text-center text-[11.5px] font-bold uppercase tracking-[0.08em] text-slate-400">
        {{ __('messages.onboarding_progress', ['current' => 1, 'total' => 2]) }} — {{ __('messages.onboarding_step1_title') }}
    </p>
    <div class="mt-2 mb-5 flex items-center gap-1.5 max-w-xs mx-auto" aria-hidden="true">
        <span class="h-1.5 flex-1 rounded-full bg-[#2D7080]"></span>
        <span class="h-1.5 flex-1 rounded-full bg-slate-200"></span>
    </div>

                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-3">
                    <div class="flex items-start gap-3 rounded-xl bg-[#2D7080]/[0.07] border border-[#2D7080]/20 px-4 py-3.5 mb-4">
                        <span class="flex-shrink-0 mt-0.5 flex h-8 w-8 items-center justify-center rounded-full bg-[#2D7080]/15 text-[#2D7080]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div class="min-w-0">
                            <h1 class="text-[15px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.onboarding_company_why_title') }}</h1>
                            <p class="mt-1 text-[12.5px] text-slate-600 leading-relaxed">{{ __('messages.onboarding_company_why_body') }}</p>
                        </div>
                    </div>
                    <h2 class="text-[19px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.onboarding_step1_title') }}</h2>
        <p class="mt-1 text-[12.5px] text-slate-500">{{ __('messages.onboarding_step1_desc') }}</p>
        <div id="company-form-errors" class="hidden mt-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-[12.5px]"></div>

        <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-3">
            <div class="rounded-xl border border-slate-200 p-4 min-w-0">
                <h2 class="text-[13px] font-bold text-[#0B2638] mb-3">{{ __('messages.General Information') }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div class="sm:col-span-2">
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Company name') }}</label>
                        <input type="text" data-col="name" value="{{ $entreprise->name ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                    </div>
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Phone') }}</label>
                        <input type="text" data-col="phone" value="{{ $entreprise->phone ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                    </div>
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Company email') }}</label>
                        <input type="email" data-col="email" value="{{ $entreprise->email ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Address') }}</label>
                        <input type="text" data-col="Address" value="{{ $entreprise->Address ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                    </div>
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Country') }}</label>
                        <select data-col="country" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none bg-white">
                            <option value="">—</option>
                            @foreach($countriesList as $country)
                                <option value="{{ $country['name'] }}" {{ ($entreprise->country ?? '') === $country['name'] ? 'selected' : '' }}>{{ $country['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.City') }}</label>
                        <input type="text" data-col="city" value="{{ $entreprise->city ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                    </div>
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Zip Code') }}</label>
                        <input type="text" data-col="zip_code" value="{{ $entreprise->zip_code ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                    </div>
                    <div>
                        <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.TVA') }}</label>
                        <input type="text" data-col="tva" value="{{ $entreprise->tva ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                    </div>
                </div>
            </div>
            <div class="space-y-3 min-w-0">
                <div class="rounded-xl border border-slate-200 p-4">
                    <h2 class="text-[13px] font-bold text-[#0B2638] mb-3">{{ __('messages.Logo') }}</h2>
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0 w-16 h-16 border-2 border-dashed border-slate-200 rounded-xl flex items-center justify-center overflow-hidden bg-slate-50">
                            @if(!empty($entreprise->image))
                                <img id="logo-preview" src="{{ Storage::disk('public')->url($entreprise->image) }}" alt="Company Logo" class="w-full h-full object-contain p-1">
                            @else
                                <div id="logo-placeholder" class="text-center">
                                    <svg class="mx-auto h-6 w-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                                    <p class="mt-1 text-[10px] text-slate-400">{{ __('messages.No logo') }}</p>
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <input type="file" id="logo-upload" accept="image/*" class="hidden">
                            <label for="logo-upload" class="inline-flex items-center gap-1.5 px-3 py-2 text-[12.5px] font-semibold text-slate-600 bg-white border border-slate-300 rounded-lg hover:bg-slate-50 transition-colors cursor-pointer">
                                {{ __('messages.Choose file') }}
                            </label>
                            <button type="button" id="logo-save" class="ml-2 inline-flex items-center px-3 py-2 text-[12.5px] font-semibold text-white bg-[#2D7080] rounded-lg hover:bg-[#245f6d] transition-colors disabled:opacity-40" disabled>{{ __('messages.Update Image') }}</button>
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <h2 class="text-[13px] font-bold text-[#0B2638] mb-3">{{ __('messages.Banking Information') }}</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Bank Name') }}</label>
                            <input type="text" data-col="bank_name" value="{{ $entreprise->bank_name ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.IBAN') }}</label>
                            <input type="text" data-col="iban" value="{{ $entreprise->iban ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">Swift</label>
                            <input type="text" data-col="swift" value="{{ $entreprise->swift ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">BIC</label>
                            <input type="text" data-col="bic" value="{{ $entreprise->bic ?? '' }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                    </div>
                </div>
                <div class="rounded-xl border border-slate-200 p-4">
                    <h2 class="text-[13px] font-bold text-[#0B2638] mb-3">{{ __('messages.Billing Information') }}</h2>
                    <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Billit API key') }}</label>
                    <input type="password" data-col="billit_api_key" value="" placeholder="••••••••" autocomplete="new-password" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                </div>
                
                <!-- <div class="rounded-xl border border-slate-200 p-4">
                    <h2 class="text-[13px] font-bold text-[#0B2638] mb-3">{{ __('messages.Emails Backup') }}</h2>
                    <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Emails Backup label') }}</label>
                    <textarea data-col="emails_backup" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">{{ $entreprise->emails_backup ?? '' }}</textarea>
                    <label class="block text-[12.5px] font-medium text-slate-700 mb-1 mt-3">{{ __('messages.WorkDays Emails label') }}</label>
                    <textarea data-col="workdays_emails" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">{{ $entreprise->workdays_emails ?? '' }}</textarea>
                </div> -->
            </div>
        </div>

        <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-between gap-2">
            <a href="{{ route('admin.onboarding.step2') }}" class="inline-flex items-center justify-center h-10 px-5 rounded-xl bg-white border border-slate-300 text-slate-600 text-[13.5px] font-semibold hover:bg-slate-50 transition-colors no-underline">
                {{ __('messages.onboarding_skip') }}
            </a>
            <button type="button" id="company-save" class="inline-flex items-center justify-center h-10 px-5 rounded-xl bg-[#0B2638] text-white text-[13.5px] font-semibold hover:bg-[#14364d] transition-colors disabled:opacity-40">
                {{ __('messages.onboarding_save_continue') }}
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var saveBtn = document.getElementById('company-save');
    var errBox = document.getElementById('company-form-errors');
    if (!saveBtn) return;
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var updateUrl = @json(route('admin.Enterprise_infos.updateInfos'));
    var entrepriseId = @json($entreprise->id ?? null);

    saveBtn.addEventListener('click', async function () {
        errBox.classList.add('hidden');
        errBox.textContent = '';
        saveBtn.disabled = true;
        try {
            var inputs = document.querySelectorAll('[data-col]');
            for (const input of inputs) {
                if (input.getAttribute('data-col') === 'billit_api_key' && !(input.value || '').trim()) continue;
                await fetch(updateUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id: entrepriseId, column: input.getAttribute('data-col'), content: input.value })
                }).then(async function (r) {
                    if (!r.ok) {
                        const d = await r.json().catch(() => ({}));
                        throw new Error(d.error || ('Request failed: ' + r.status));
                    }
                });
            }
            try { if (window.AppToast) window.AppToast.success(@json(__('messages.onboarding_company_saved'))); } catch {}
            window.location.href = @json(route('admin.onboarding.step2'));
        } catch (e) {
            console.error('Company save:', e);
            errBox.textContent = e.message;
            errBox.classList.remove('hidden');
        } finally {
            saveBtn.disabled = false;
        }
    });

    /* Logo upload → existing endpoint, stay on the page */
    var logoInput = document.getElementById('logo-upload');
    var logoSave = document.getElementById('logo-save');
    if (logoInput && logoSave) {
        logoInput.addEventListener('change', function () {
            logoSave.disabled = !(logoInput.files && logoInput.files.length);
            var f = logoInput.files && logoInput.files[0];
            if (f) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    var prev = document.getElementById('logo-preview');
                    if (prev) { prev.src = e.target.result; }
                };
                reader.readAsDataURL(f);
            }
        });
        logoSave.addEventListener('click', async function () {
            if (!logoInput.files || !logoInput.files.length) return;
            logoSave.disabled = true;
            try {
                var fd = new FormData();
                fd.append('entreprise_image', logoInput.files[0]);
                const r = await fetch(@json(route('admin.entreprise.updateImage')), {
                    method: 'POST',
                    body: fd,
                    headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!r.ok) throw new Error('Request failed: ' + r.status);
                try { if (window.AppToast) window.AppToast.success(@json(__('messages.onboarding_company_saved'))); } catch {}
            } catch (e) {
                console.error('Logo upload:', e);
                errBox.textContent = e.message;
                errBox.classList.remove('hidden');
            } finally {
                logoSave.disabled = !(logoInput.files && logoInput.files.length);
            }
        });
    }
});
</script>
@endpush
