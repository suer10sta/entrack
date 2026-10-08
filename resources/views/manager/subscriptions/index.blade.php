@extends('manager.layouts.app')

@section('content')
@php
    $activePlan   = $activePlan   ?? request('plan');
    $activeStatus = $activeStatus ?? request('status');
@endphp

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div class="min-w-0">
        <p class="dt-overline mb-1">{{ __('messages.Manager area') }}</p>
        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight">
            {{ __('messages.Subscriptions overview') }}
        </h1>
        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Plans distribution and entreprise subscription status.') }}</p>
    </div>
</div>

{{-- Plan stat cards --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @foreach([
        ['solo',    __('messages.Solo plans'),    'bg-[#0B2638]/[0.06] text-[#0B2638] ring-1 ring-[#0B2638]/10'],
        ['premium', __('messages.Premium plans'), 'bg-[#2D7080]/[0.08] text-[#2D7080] ring-1 ring-[#2D7080]/15'],
        ['pro',     __('messages.Pro plans'),     'bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40'],
    ] as [$plan, $label, $iconClass])
        <a href="{{ route('manager.subscriptions.index', ['plan' => $plan]) }}"
           class="dt-card dt-card-hover p-4 flex items-center gap-3.5 no-underline {{ $activePlan === $plan ? 'ring-2 ring-[#E7B84B]' : '' }}">
            <div class="flex items-center justify-center w-11 h-11 rounded-xl flex-shrink-0 {{ $iconClass }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.598 1M12 8V6m0 10v2m8-8a8 8 0 11-16 0 8 8 0 0116 0z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <p class="dt-stat-label">{{ $label }}</p>
                <p class="dt-stat-value">{{ $planCounts[$plan] ?? 0 }}</p>
            </div>
            @if($activePlan === $plan)
                <span class="flex-shrink-0">
                    <svg class="w-4 h-4 text-[#E7B84B]" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </span>
            @endif
        </a>
    @endforeach
</div>

{{-- Revenue --}}
<div class="dt-card p-5 sm:p-6 mb-6 flex items-center gap-4 relative overflow-hidden">
    <span class="absolute left-0 top-0 h-full w-1 bg-[#E7B84B]"></span>
    <div class="flex items-center justify-center w-11 h-11 rounded-xl bg-[#E7B84B]/20 text-[#0B2638] ring-1 ring-[#E7B84B]/40 flex-shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z"/>
        </svg>
    </div>
    <div>
        <p class="dt-stat-label">{{ __('messages.Estimated monthly revenue') }}</p>
        <p class="text-[24px] font-bold tracking-tight text-[#0B2638]">{{ number_format($estimatedMonthlyRevenue, 2) }} EUR</p>
        <p class="text-[12px] text-slate-500 mt-0.5">{{ __('messages.Based on active Stripe subscriptions and normalized recurring intervals.') }}</p>
    </div>
</div>

{{-- Entreprises table --}}
<div class="dt-card overflow-hidden">
    <div class="px-5 sm:px-6 py-4 border-b border-slate-100 bg-white flex flex-col sm:flex-row sm:items-center gap-3 sm:justify-between">
        <div>
            <h2 class="dt-section-title">{{ __('messages.Entreprises and subscriptions') }}</h2>
            @if($activePlan || $activeStatus === 'active')
                @php
                    $planLabel = $activePlan ? ['solo' => __('messages.Solo'), 'premium' => __('messages.Premium'), 'pro' => __('messages.Pro')][$activePlan] ?? ucfirst($activePlan) : null;
                @endphp
                <p class="dt-section-subtitle mt-0.5">
                    {{ __('messages.Filter:') }}
                    @if($planLabel) <strong class="text-slate-700">{{ $planLabel }}</strong> @endif
                    @if($activeStatus === 'active') <strong class="text-emerald-700 ml-1">({{ __('messages.Active') }})</strong> @endif
                </p>
            @endif
        </div>
        @if($activePlan || $activeStatus === 'active')
            <a href="{{ route('manager.subscriptions.index') }}" class="dt-btn dt-btn-ghost flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                {{ __('messages.Clear filter') }}
            </a>
        @endif
    </div>

    <div class="manager-table-wrapper overflow-x-auto">
        <table class="dt-table manager-table min-w-full">
            <thead>
                <tr class="dt-table-head">
                    <th class="px-4 py-2.5">{{ __('messages.Name') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Email') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Plan') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Plan ends at') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Trial ends at') }}</th>
                    <th class="px-4 py-2.5">{{ __('messages.Account status') }}</th>
                    <th class="px-4 py-2.5 text-right">{{ __('messages.Actions') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entreprises as $entreprise)
                    @php
                        $resolvedPlanEndsAt = $planEndsAtByEntrepriseId[$entreprise->id] ?? null;
                        $isActive           = (bool)($subscriptionStatusByEntrepriseId[$entreprise->id] ?? false);
                    @endphp
                    <tr class="manager-card-row" aria-expanded="true">
                        <td data-label="{{ __('messages.Name') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2.5">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="w-8 h-8 flex-shrink-0 rounded-full bg-[#2D7080]/10 text-[#2D7080] flex items-center justify-center text-[12px] font-bold">
                                        {{ strtoupper(substr($entreprise->name, 0, 1)) }}
                                    </div>
                                    <span class="text-[13px] font-semibold text-slate-800 truncate">{{ $entreprise->name }}</span>
                                </div>
                                <span class="shrink-0 mt-0.5 text-slate-400 min-[1366px]:hidden">
                                    <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none"><path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </span>
                            </div>
                        </td>
                        <td data-label="{{ __('messages.Email') }}" class="px-4 py-2.5 text-[13px] text-slate-500">{{ $entreprise->email }}</td>
                        <td data-label="{{ __('messages.Plan') }}" class="px-4 py-2.5">
                            @if($entreprise->type_compt)
                                <span class="dt-badge dt-badge-navy"><span class="dt-dot"></span>{{ $entreprise->type_compt }}</span>
                            @else
                                <span class="text-slate-400 text-[13px]">—</span>
                            @endif
                        </td>
                        <td data-label="{{ __('messages.Plan ends at') }}" class="px-4 py-2.5 text-[13px] text-slate-500 tabular-nums">
                            {{ $resolvedPlanEndsAt ? $resolvedPlanEndsAt->format('Y-m-d H:i') : '—' }}
                        </td>
                        <td data-label="{{ __('messages.Trial ends at') }}" class="px-4 py-2.5 text-[13px] text-slate-500 tabular-nums">
                            {{ $entreprise->trial_ends_at ? $entreprise->trial_ends_at->format('Y-m-d') : '—' }}
                        </td>
                        <td data-label="{{ __('messages.Account status') }}" class="px-4 py-2.5">
                            @if($isActive)
                                <span class="dt-badge dt-badge-success"><span class="dt-dot"></span>{{ __('messages.Active') }}</span>
                            @else
                                <span class="dt-badge dt-badge-danger"><span class="dt-dot"></span>{{ __('messages.Inactive') }}</span>
                            @endif
                        </td>
                        <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-4 py-2.5 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('manager.subscriptions.show', $entreprise) }}" class="dt-btn dt-btn-ghost !py-1.5 !px-3 !text-[12px]">
                                    {{ __('messages.View') }}
                                </a>
                                @if(!method_exists($entreprise, 'isBlockingExempt') || !$entreprise->isBlockingExempt())
                                    <button type="button"
                                            class="dt-btn !py-1.5 !px-3 !text-[12px] bg-red-50 text-red-600 border border-red-200 hover:bg-red-100"
                                            onclick="openDeleteEntrepriseModal({{ $entreprise->id }}, '{{ addslashes($entreprise->name) }}')">
                                        {{ __('messages.Delete') }}
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="dt-empty-icon mx-auto mb-3">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18"/>
                                </svg>
                            </div>
                            <p class="text-[13.5px] font-semibold text-[#0B2638]">{{ __('messages.No entreprises found.') }}</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($entreprises->hasPages())
        <div class="px-5 sm:px-6 py-3 border-t border-slate-100 bg-[#f8fafc]">
            {{ $entreprises->links() }}
        </div>
    @endif
</div>

{{-- Delete confirmation modal (2-step) --}}
<div id="delete-entreprise-modal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-[#0B2638]/50 backdrop-blur-sm"
     aria-hidden="true">
    <div class="dt-card w-full max-w-lg mx-4">
        <div class="px-6 py-4 border-b border-slate-100 flex items-start gap-3">
            <div id="delete-modal-icon" class="flex-shrink-0 w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center text-red-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/>
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="dt-section-title">{{ __('messages.Delete entreprise') }}</h3>
                <p class="dt-section-subtitle mt-0.5" id="delete-entreprise-step-1">
                    {{ __('messages.Step 1 of 2 — Please confirm you want to permanently delete this entreprise.') }}
                </p>
                <p class="dt-section-subtitle mt-0.5 hidden text-red-600" id="delete-entreprise-step-2">
                    {!! __('messages.Step 2 of 2 — Type <span class="font-semibold">DELETE</span> to confirm. This action will remove the entreprise and <span class="font-semibold">all its records</span> (employees, projects, clients, invoices, tasks, timers, calendars, permissions, etc.). This cannot be undone.') !!}
                </p>
            </div>
        </div>

        <div class="px-6 py-4 space-y-4">
            <p class="text-[13px] text-slate-700">
                {{ __('messages.You are about to delete entreprise:') }}
                <span id="delete-entreprise-name" class="font-bold text-[#0B2638]"></span>
            </p>

            <div class="rounded-xl bg-red-50 border border-red-100 px-4 py-3 text-[12.5px] text-red-700">
                {{ __('messages.All entreprise data will be deleted: employees, projects, clients, invoices, tasks, timers, calendars, budgets, roles, permissions and related configuration.') }}
            </div>

            <div id="delete-entreprise-confirm-input-wrapper" class="hidden">
                <label for="delete-entreprise-confirm-input" class="block text-[12px] font-semibold text-slate-600 mb-1.5">
                    {!! __('messages.Type <span class="font-semibold">DELETE</span> to confirm') !!}
                </label>
                <input id="delete-entreprise-confirm-input" type="text" autocomplete="off"
                       class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-[13px] focus:border-red-500 focus:ring-1 focus:ring-red-500 outline-none">
            </div>

            <form id="delete-entreprise-form" method="POST" class="hidden">
                @csrf
                @method('DELETE')
            </form>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between">
            <button type="button" class="dt-btn dt-btn-ghost" onclick="closeDeleteEntrepriseModal()">
                {{ __('messages.Cancel') }}
            </button>
            <div class="flex items-center gap-2">
                <button type="button" id="delete-entreprise-next-btn"
                        class="dt-btn bg-amber-50 text-amber-700 border border-amber-300 hover:bg-amber-100"
                        onclick="goToDeleteEntrepriseStep2()">
                    {{ __('messages.I understand, continue') }}
                </button>
                <button type="button" id="delete-entreprise-submit-btn"
                        class="dt-btn bg-red-600 text-white hover:bg-red-700 hidden disabled:opacity-50 disabled:cursor-not-allowed"
                        onclick="submitDeleteEntreprise()" disabled>
                    {{ __('messages.Permanently delete') }}
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let deleteEntrepriseId = null;

    function openDeleteEntrepriseModal(id, name) {
        deleteEntrepriseId = id;
        document.getElementById('delete-entreprise-name').textContent = name;
        const form = document.getElementById('delete-entreprise-form');
        form.action = "{{ route('manager.subscriptions.destroy', ['entreprise' => '__ID__']) }}".replace('__ID__', id);
        // reset to step 1
        document.getElementById('delete-entreprise-step-1').classList.remove('hidden');
        document.getElementById('delete-entreprise-step-2').classList.add('hidden');
        document.getElementById('delete-entreprise-confirm-input-wrapper').classList.add('hidden');
        document.getElementById('delete-entreprise-confirm-input').value = '';
        document.getElementById('delete-entreprise-next-btn').classList.remove('hidden');
        document.getElementById('delete-entreprise-submit-btn').classList.add('hidden');
        document.getElementById('delete-entreprise-submit-btn').disabled = true;
        const modal = document.getElementById('delete-entreprise-modal');
        modal.classList.remove('hidden'); modal.classList.add('flex');
    }

    function closeDeleteEntrepriseModal() {
        const modal = document.getElementById('delete-entreprise-modal');
        modal.classList.add('hidden'); modal.classList.remove('flex');
        deleteEntrepriseId = null;
    }

    function goToDeleteEntrepriseStep2() {
        document.getElementById('delete-entreprise-step-1').classList.add('hidden');
        document.getElementById('delete-entreprise-step-2').classList.remove('hidden');
        document.getElementById('delete-entreprise-confirm-input-wrapper').classList.remove('hidden');
        document.getElementById('delete-entreprise-next-btn').classList.add('hidden');
        document.getElementById('delete-entreprise-submit-btn').classList.remove('hidden');
        const input = document.getElementById('delete-entreprise-confirm-input');
        input.focus();
        input.addEventListener('input', function () {
            document.getElementById('delete-entreprise-submit-btn').disabled = input.value.trim().toUpperCase() !== 'DELETE';
        });
    }

    function submitDeleteEntreprise() {
        if (deleteEntrepriseId) document.getElementById('delete-entreprise-form').submit();
    }

    document.getElementById('delete-entreprise-modal').addEventListener('click', function (e) {
        if (e.target === this) closeDeleteEntrepriseModal();
    });
</script>
@endsection
