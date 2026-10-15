@extends('layouts.onboarding')

@section('title', __('messages.onboarding_step2_title') . ' — DalyTrac')

@push('styles')
<style>
    .expander { display: grid; grid-template-rows: 0fr; opacity: 0; transition: grid-template-rows .32s ease, opacity .25s ease, margin-top .32s ease; margin-top: 0; }
    .expander.open { grid-template-rows: 1fr; opacity: 1; margin-top: 12px; }
    .expander > div { overflow: hidden; min-height: 0; }
    .tag-pick { position: relative; display: inline-flex; }
    .tag-pick input { position: absolute; opacity: 0; pointer-events: none; }
    .tag-pick span {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 5px 12px 5px 10px; border-radius: 9999px;
        border: 1px solid #e2e8f0; background: #f8fafc;
        font-size: 12.5px; font-weight: 500; color: #475569;
        cursor: pointer; transition: background-color .15s, border-color .15s, color .15s;
        max-width: 100%;
    }
    .tag-pick span::before {
        content: ''; width: 7px; height: 7px; border-radius: 9999px;
        background: #cbd5e1; flex-shrink: 0; transition: background-color .15s;
    }
    .tag-pick input:checked + span { background: #2D7080; border-color: #2D7080; color: #fff; }
    .tag-pick input:checked + span::before { background: #fff; }
    fieldset:disabled .tag-pick span { cursor: not-allowed; opacity: .55; }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto w-full">
    <p class="text-center text-[11.5px] font-bold uppercase tracking-[0.08em] text-slate-400">
        {{ __('messages.onboarding_progress', ['current' => 2, 'total' => 2]) }} — {{ __('messages.onboarding_step2_title') }}
    </p>
    <div class="mt-2 mb-5 flex items-center gap-1.5 max-w-xs mx-auto" aria-hidden="true">
        <span class="h-1.5 flex-1 rounded-full bg-[#2D7080]"></span>
        <span class="h-1.5 flex-1 rounded-full bg-[#2D7080]"></span>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 mb-3">
        <h1 class="text-[19px] font-bold tracking-tight text-[#0B2638]">{{ __('messages.onboarding_step2_title') }}</h1>
        <p class="mt-1 text-[12.5px] text-slate-500">{{ __('messages.onboarding_step2_desc') }}</p>

        @if(session('message'))
            <div class="mt-3 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-[12.5px] font-medium text-emerald-800">{{ session('message') }}</div>
        @endif

        {{-- ── Section 1: client (always open, Create on the right) ── --}}
        <section class="mt-4 rounded-xl border border-slate-200">
            <div class="flex items-center justify-between gap-2 px-4 py-3">
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-purple-50 text-purple-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h2 class="text-[13.5px] font-bold text-[#0B2638]">{{ __('messages.onboarding_sec_client') }}</h2>
                </div>
                
            </div>
            <div id="ob-client-panel" class="expander open" aria-hidden="false"><div>
                <div class="px-4 pb-4">
                    <div id="ob-client-created" class="hidden mb-3 rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2 text-[12.5px] font-medium text-emerald-800"></div>
                    <div id="ob-client-errors" class="hidden mb-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-[12.5px]"></div>
                    <form id="ob-client-form" autocomplete="off">
                        <input type="hidden" name="from" value="onboarding" />
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Client Number') }}</label>
                                <input type="text" name="client_number" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Email') }}</label>
                                <input type="email" name="email" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Phone') }}</label>
                                <input type="text" name="phone" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Street') }}</label>
                                <input type="text" name="address" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.City') }}</label>
                                <input type="text" name="city" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Zip Code') }}</label>
                                <input type="text" name="zip" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Country') }}</label>
                                <input type="text" name="country" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Due Date (days)') }}</label>
                                <input type="text" name="due_date" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.TVA number') }}</label>
                                <input type="text" name="TVA" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                        </div>
                        <div class="mt-3 flex justify-end">
                            <button type="button" id="ob-client-save"
                                class="inline-flex flex-shrink-0 items-center gap-1.5 rounded-lg px-3.5 py-1.5 text-[12.5px] font-semibold bg-purple-600 hover:bg-purple-600/90 text-white">
                                {{ __('messages.Create') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div></div>
        </section>

        {{-- ── Section 2: project (open by default) ── --}}
        <section class="mt-3 rounded-xl border border-slate-200">
            <div class="px-4 pt-3.5">
                <div class="flex items-center gap-2">
                     <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <h2 class="text-[13.5px] font-bold text-[#0B2638]">{{ __('messages.onboarding_sec_project') }}</h2>
                </div>
            </div>
            <div class="px-4 pb-4">
                <div id="ob-project-errors" class="hidden mb-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-[12.5px]"></div>
                <form id="ob-project-form" autocomplete="off">
                    <input type="hidden" name="from" value="onboarding" />
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Project Name') }} <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Budget label') }}</label>
                            <input type="number" step="0.01" min="0" name="budget" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Cost per Hour') }}</label>
                            <input type="number" step="0.01" min="0" name="cost_per_hour" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Estimated Hours') }}</label>
                            <input type="number" step="0.01" min="0" name="estimated_hours" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                        <div>
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.TVA %') }}</label>
                            <input type="number" step="0.01" min="0" name="tva" value="0" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                        </div>
                    </div>
                    <div class="mt-3 flex justify-end">
                        <button type="button" id="ob-project-save" class="inline-flex items-center justify-center h-9 px-5 rounded-xl bg-blue-600 text-white text-[13px] font-semibold hover:bg-[#14364d] transition-colors disabled:opacity-40">
                            {{ __('messages.Create') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- ── Section 3: task (disabled until a project exists) ── --}}
        <section class="mt-3 rounded-xl border border-slate-200" id="ob-task-section">
            <div class="px-4 pt-3.5">
                <div class="flex items-center gap-2"> 
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-50 text-green-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7a2 2 0 012-2h4l2 2h8a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7z"/></svg>
                   </div>
                   <h2 class="text-[13.5px] font-bold text-[#0B2638]">{{ __('messages.onboarding_sec_task') }}</h2>
                </div>
                <p id="ob-task-hint" class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.onboarding_task_locked') }}</p>
            </div>
            <div class="px-4 pb-4">
                @if($errors->has('project'))
                    <div class="mb-3 bg-red-50 border border-red-200 text-red-700 px-3 py-2 rounded-lg text-[12.5px]">{{ $errors->first('project') }}</div>
                @endif
                <form id="ob-task-form" method="POST" action="#" autocomplete="off">
                    @csrf
                    <input type="hidden" name="from" value="onboarding" />
                    <fieldset id="ob-task-fields" disabled class="disabled:opacity-60">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Task Name') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Estimated Hours') }}</label>
                                <input type="number" step="0.01" min="0" name="estimated_hours" value="{{ old('estimated_hours') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Consumed Hours') }}</label>
                                <input type="number" step="0.01" min="0" name="consumed_hours" value="{{ old('consumed_hours', '0') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Start Date') }}</label>
                                <input type="date" name="start" value="{{ old('start') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                            <div>
                                <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.End Date') }}</label>
                                <input type="date" name="end" value="{{ old('end') }}" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none" />
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="block text-[12.5px] font-medium text-slate-700 mb-1">{{ __('messages.Task Description') }}</label>
                            <textarea name="description" rows="2" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#2D7080]/25 focus:border-[#2D7080] outline-none">{{ old('description') }}</textarea>
                        </div>
                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <p class="text-[12.5px] font-medium text-slate-700 mb-1.5">{{ __('messages.Assign Employees') }}</p>
                                <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto rounded-lg border border-slate-200 p-2">
                                    @foreach($employee_users as $employee)
                                        <label class="tag-pick" title="{{ $employee->name }}">
                                            <input type="checkbox" name="employees[]" value="{{ $employee->id }}" {{ in_array($employee->id, old('employees', [])) ? 'checked' : '' }}>
                                            <span class="truncate max-w-[150px]">{{ $employee->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                            <div>
                                <p class="text-[12.5px] font-medium text-slate-700 mb-1.5">{{ __('messages.Assign Teams') }}</p>
                                <div class="flex flex-wrap gap-1.5 max-h-32 overflow-y-auto rounded-lg border border-slate-200 p-2">
                                    @foreach($teams as $team)
                                        <label class="tag-pick" title="{{ $team->name }}">
                                            <input type="checkbox" name="teams[]" value="{{ $team->id }}" {{ in_array($team->id, old('teams', [])) ? 'checked' : '' }}>
                                            <span class="truncate max-w-[150px]">{{ $team->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center">
                            <input id="ob-planning" name="add_to_planning" type="checkbox" value="1" {{ old('add_to_planning') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-[#2D7080] focus:ring-[#2D7080]">
                            <label for="ob-planning" class="ml-2 text-[12.5px] text-slate-700">{{ __('messages.Add this task to planning') }}</label>
                        </div>
                    </fieldset>
                    <div class="mt-3 flex justify-end">
                        <button type="submit" id="ob-task-submit" disabled class="inline-flex items-center justify-center h-9 px-5 rounded-xl bg-green-600 text-white text-[13px] font-semibold hover:bg-[#14364d] transition-colors disabled:opacity-40 disabled:cursor-not-allowed">
                            {{ __('messages.Create Task') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <div class="mt-4 flex flex-col-reverse sm:flex-row sm:justify-between gap-2">
            <form method="POST" action="{{ route('admin.onboarding.complete') }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center h-10 px-5 rounded-xl bg-white border border-slate-300 text-slate-600 text-[13.5px] font-semibold hover:bg-slate-50 transition-colors">
                    {{ __('messages.onboarding_skip') }}
                </button>
            </form>
            <form method="POST" action="{{ route('admin.onboarding.complete') }}">
                @csrf
                <button type="submit" class="inline-flex items-center justify-center h-10 px-5 rounded-xl bg-[#0B2638] text-white text-[13.5px] font-semibold hover:bg-[#14364d] transition-colors">
                    {{ __('messages.onboarding_finish') }}
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    var clientsStoreUrl = @json(route('admin.clients.store'));
    var projectsStoreTemplate = @json(route('admin.projects.store', ['client_id' => '__CID__']));
    var taskStoreTemplate = @json(route('admin.tasks.store', ['project_id' => '__PID__']));
    var searchUrl = @json(route('search'));
    var createdClient = { id: null, name: '' };
    var createdProject = { id: null, name: '' };
    var norm = (s) => (s ?? '').toString().toLowerCase().trim();

    function showErrors(boxId, errors) {
        var box = document.getElementById(boxId);
        box.innerHTML = '';
        var msgs = [];
        Object.values(errors || {}).forEach((v) => (Array.isArray(v) ? v : [v]).forEach((m) => { if (m) msgs.push(m); }));
        msgs.forEach((m) => {
            var div = document.createElement('div');
            div.textContent = m;
            box.appendChild(div);
        });
        box.classList.toggle('hidden', !msgs.length);
    }
    function toast(type, msg) {
        try { if (window.AppToast) window.AppToast[type === 'error' ? 'error' : 'success'](msg); } catch {}
    }
    async function discoverId(name, type) {
        var q = (name || '').trim();
        if (searchUrl && q.length >= 2) {
            try {
                const r = await fetch(searchUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (r.ok) {
                    const d = await r.json();
                    const hit = (d.results || []).find((x) => x.type === type && norm(x.title) === norm(q));
                    if (hit) return hit.id;
                }
            } catch (e) { console.error('Discover:', e); }
        }
        return null;
    }

    /* Section 1: client form is always open; the header Create button saves */
    var clientSave = document.getElementById('ob-client-save');
    if (clientSave) {
        clientSave.addEventListener('click', async function () {
            var form = document.getElementById('ob-client-form');
            showErrors('ob-client-errors', null);
            clientSave.disabled = true;
            try {
                const r = await fetch(clientsStoreUrl, {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (r.status === 422) {
                    const d = await r.json().catch(() => ({}));
                    showErrors('ob-client-errors', d.errors);
                    toast('error', @json(__('messages.toast_failed_client')));
                    return;
                }
                if (!r.ok) throw new Error('Request failed: ' + r.status);
                var created = await r.json().catch(() => ({}));
                var name = form.querySelector('[name="name"]').value.trim();
                // Prefer the persisted ID returned by the backend; fall back to
                // a name lookup only if the response carries no ID.
                var id = (created.client && created.client.id) || await discoverId(name, 'client');
                createdClient = { id: id, name: name };
                var okBox = document.getElementById('ob-client-created');
                okBox.textContent = name;
                okBox.classList.remove('hidden');
                var boundLine = document.getElementById('ob-project-client');
                if (boundLine && name) boundLine.textContent = name;
                toast('success', @json(__('messages.toast_created_client')));
                /* Keep the entered values visible: no form.reset() here. A new
                   record is created only if the user explicitly clicks Create again. */
            } catch (e) {
                console.error('Create client:', e);
                showErrors('ob-client-errors', { error: [e.message] });
                toast('error', @json(__('messages.toast_failed_client')));
            } finally {
                clientSave.disabled = false;
            }
        });
    }

    /* Section 2 save → existing project store (client via URL when known) */
    var projectSave = document.getElementById('ob-project-save');
    if (projectSave) {
        projectSave.addEventListener('click', async function () {
            var form = document.getElementById('ob-project-form');
            showErrors('ob-project-errors', null);
            projectSave.disabled = true;
            try {
                var url = projectsStoreTemplate.replace('__CID__', encodeURIComponent(createdClient.id || ''));
                const r = await fetch(url.replace(/\/$/, ''), {
                    method: 'POST',
                    body: new FormData(form),
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }
                });
                if (r.status === 422) {
                    const d = await r.json().catch(() => ({}));
                    showErrors('ob-project-errors', d.errors);
                    toast('error', @json(__('messages.toast_failed_project')));
                    return;
                }
                if (!r.ok) throw new Error('Request failed: ' + r.status);
                var created = await r.json().catch(() => ({}));
                var name = form.querySelector('[name="name"]').value.trim();
                // Prefer the persisted ID returned by the backend; fall back to
                // a name lookup only if the response carries no ID.
                var id = (created.project && created.project.id) || await discoverId(name, 'project');
                createdProject = { id: id, name: name };
                /* Enable the task form, bound to the new project */
                var taskForm = document.getElementById('ob-task-form');
                if (id && taskStoreTemplate) {
                    taskForm.setAttribute('action', taskStoreTemplate.replace('__PID__', encodeURIComponent(id)));
                }
                document.getElementById('ob-task-fields').disabled = false;
                document.getElementById('ob-task-submit').disabled = false;
                document.getElementById('ob-task-hint').textContent = name;
                toast('success', @json(__('messages.toast_created_project')));
                /* Keep the entered values visible: no form.reset() here. A new
                   record is created only if the user explicitly clicks Create again. */
            } catch (e) {
                console.error('Create project:', e);
                showErrors('ob-project-errors', { error: [e.message] });
                toast('error', @json(__('messages.toast_failed_project')));
            } finally {
                projectSave.disabled = false;
            }
        });
    }

    /* Task form posts normally to the existing store (returns to step 2 via flag) */
    var taskForm = document.getElementById('ob-task-form');
    if (taskForm) {
        taskForm.addEventListener('submit', function (e) {
            if (document.getElementById('ob-task-fields').disabled || !createdProject.id) {
                e.preventDefault();
            }
        });
    }

    /* Keep the bound-client line fresh */
    var boundLine = document.getElementById('ob-project-client');
    if (boundLine) {
        boundLine.textContent = @json(__('messages.No client'));
    }
});
</script>
@endpush
