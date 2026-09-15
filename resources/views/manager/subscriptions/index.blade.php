@extends('manager.layouts.app')

@section('content')
    @php
        /** @var string|null $activePlan */
        $activePlan = $activePlan ?? request('plan');
        /** @var string|null $activeStatus */
        $activeStatus = $activeStatus ?? request('status');
    @endphp
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">{{ __('messages.Subscriptions overview') }}</h2>
                <p class="mt-1 text-sm text-gray-500">
                    {{ __('messages.Plans distribution and entreprise subscription status.') }}
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('manager.subscriptions.index', ['plan' => 'solo']) }}"
               class="bg-white rounded-lg shadow-sm border p-6 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $activePlan === 'solo' ? 'border-blue-600 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300' }}">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('messages.Solo plans') }}</p>
                <p class="text-3xl font-semibold text-gray-900">{{ $planCounts['solo'] ?? 0 }}</p>
            </a>
            <a href="{{ route('manager.subscriptions.index', ['plan' => 'premium']) }}"
               class="bg-white rounded-lg shadow-sm border p-6 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ $activePlan === 'premium' ? 'border-emerald-600 ring-1 ring-emerald-600' : 'border-gray-200 hover:border-gray-300' }}">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('messages.Premium plans') }}</p>
                <p class="text-3xl font-semibold text-emerald-600">{{ $planCounts['premium'] ?? 0 }}</p>
            </a>
            <a href="{{ route('manager.subscriptions.index', ['plan' => 'pro']) }}"
               class="bg-white rounded-lg shadow-sm border p-6 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $activePlan === 'pro' ? 'border-sky-600 ring-1 ring-sky-600' : 'border-gray-200 hover:border-gray-300' }}">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">{{ __('messages.Pro plans') }}</p>
                <p class="text-3xl font-semibold text-sky-600">{{ $planCounts['pro'] ?? 0 }}</p>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">{{ __('messages.Estimated monthly revenue') }}</h3>
            <p class="text-2xl font-semibold text-gray-900">
                {{ number_format($estimatedMonthlyRevenue, 2) }} EUR
            </p>
            <p class="mt-1 text-xs text-gray-500">
                {{ __('messages.Based on active Stripe subscriptions and normalized recurring intervals.') }}
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">{{ __('messages.Entreprises and subscriptions') }}</h3>
                        @if ($activePlan || $activeStatus === 'active')
                            @php
                                $planLabel = $activePlan ? [
                                    'solo' => __('messages.Solo'),
                                    'premium' => __('messages.Premium'),
                                    'pro' => __('messages.Pro'),
                                ][$activePlan] ?? ucfirst($activePlan) : null;
                            @endphp
                            <p class="mt-1 text-xs text-gray-500">
                                {{ __('messages.Filter:') }}
                                @if ($planLabel)
                                    <span class="font-semibold text-gray-700">{{ $planLabel }}</span>
                                @endif
                                @if ($activeStatus === 'active')
                                    <span class="font-semibold text-emerald-700 ml-1">({{ __('messages.Active') }})</span>
                                @endif
                            </p>
                        @endif
                    </div>
                    @if ($activePlan || $activeStatus === 'active')
                        <a href="{{ route('manager.subscriptions.index') }}"
                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                            {{ __('messages.Clear filter') }}
                        </a>
                    @endif
                </div>
            </div>
            <div class="manager-table-wrapper p-6 overflow-x-auto">
                <table class="manager-table min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Name') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Email') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Plan') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Plan ends at') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Trial ends at') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">{{ __('messages.Account status') }}</th>
                        <th class="px-4 py-2 font-semibold text-gray-600 text-right">{{ __('messages.Actions') }}</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($entreprises as $entreprise)
                            @php
                                $resolvedPlanEndsAt = $planEndsAtByEntrepriseId[$entreprise->id] ?? null;
                                $isActive = (bool) ($subscriptionStatusByEntrepriseId[$entreprise->id] ?? false);
                            @endphp
                            <tr class="manager-card-row hover:bg-gray-50" aria-expanded="true">
                                <td data-label="{{ __('messages.Name') }}" data-span="2" data-manager-card-toggle class="manager-toggle-cell px-4 py-2 text-gray-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            {{ $entreprise->name }}
                                        </div>
                                        <span class="shrink-0 mt-0.5 text-slate-400 lg:hidden" aria-hidden="true">
                                            <svg class="manager-chevron w-5 h-5" viewBox="0 0 24 24" fill="none">
                                                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                                <td data-label="{{ __('messages.Email') }}" data-span="2" class="px-4 py-2 text-gray-500">{{ $entreprise->email }}</td>
                                <td data-label="{{ __('messages.Plan') }}" class="px-4 py-2 text-gray-700">{{ $entreprise->type_compt ?? '—' }}</td>
                                <td data-label="{{ __('messages.Plan ends at') }}" class="px-4 py-2 text-gray-500">
                                    @if ($resolvedPlanEndsAt)
                                        {{ $resolvedPlanEndsAt->format('Y-m-d H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td data-label="{{ __('messages.Trial ends at') }}" class="px-4 py-2 text-gray-500">
                                    {{ $entreprise->trial_ends_at ? $entreprise->trial_ends_at->format('Y-m-d') : '—' }}
                                </td>
                                <td data-label="{{ __('messages.Account status') }}" class="px-4 py-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        {{ $isActive ? __('messages.Active') : __('messages.Inactive') }}
                                    </span>
                                </td>
                                <td data-label="{{ __('messages.Actions') }}" data-span="2" class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('manager.subscriptions.show', $entreprise) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-xs font-medium rounded text-blue-600 hover:bg-blue-50">
                                        {{ __('messages.View') }}
                                    </a>
                                    @if (! method_exists($entreprise, 'isBlockingExempt') || ! $entreprise->isBlockingExempt())
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-1.5 border border-red-600 text-xs font-medium rounded text-red-600 hover:bg-red-50"
                                            onclick="openDeleteEntrepriseModal({{ $entreprise->id }}, '{{ addslashes($entreprise->name) }}')"
                                        >
                                            {{ __('messages.Delete') }}
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">
                                    {{ __('messages.No entreprises found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $entreprises->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- Delete entreprise confirmation modal --}}
    <div
        id="delete-entreprise-modal"
        class="fixed inset-0 z-50 hidden items-center justify-center bg-gray-900/50"
        aria-hidden="true"
    >
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full mx-4">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ __('messages.Delete entreprise') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500" id="delete-entreprise-step-1">
                    {{ __('messages.Step 1 of 2 — Please confirm you want to permanently delete this entreprise.') }}
                </p>
                <p class="mt-1 text-sm text-red-600 hidden" id="delete-entreprise-step-2">
                    {!! __('messages.Step 2 of 2 — Type <span class="font-semibold">DELETE</span> to confirm. This action will remove the entreprise and <span class="font-semibold">all its records</span> (employees, projects, clients, invoices, tasks, timers, calendars, permissions, etc.). This cannot be undone.') !!}
                </p>
            </div>

            <div class="px-6 py-4 space-y-3">
                <div>
                    <p class="text-sm text-gray-700">
                        {{ __('messages.You are about to delete entreprise:') }}
                        <span id="delete-entreprise-name" class="font-semibold"></span>
                    </p>
                </div>

                <div id="delete-entreprise-warning" class="rounded-md bg-red-50 border border-red-200 px-3 py-2 text-xs text-red-700">
                    {{ __('messages.All entreprise data will be deleted: employees, projects, clients, invoices, tasks, timers, calendars, budgets, roles, permissions and related configuration.') }}
                </div>

                <div id="delete-entreprise-confirm-input-wrapper" class="hidden">
                    <label for="delete-entreprise-confirm-input" class="block text-xs font-medium text-gray-700 mb-1">
                        {!! __('messages.Type <span class="font-semibold">DELETE</span> to confirm') !!}
                    </label>
                    <input
                        id="delete-entreprise-confirm-input"
                        type="text"
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 text-sm"
                        autocomplete="off"
                    >
                </div>

                <form
                    id="delete-entreprise-form"
                    method="POST"
                    class="hidden"
                >
                    @csrf
                    @method('DELETE')
                </form>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <button
                    type="button"
                    class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50"
                    onclick="closeDeleteEntrepriseModal()"
                >
                    {{ __('messages.Cancel') }}
                </button>

                <div class="space-x-2">
                    <button
                        type="button"
                        id="delete-entreprise-next-btn"
                        class="inline-flex items-center px-3 py-1.5 border border-amber-500 text-xs font-medium rounded text-amber-700 bg-amber-50 hover:bg-amber-100"
                        onclick="goToDeleteEntrepriseStep2()"
                    >
                        {{ __('messages.I understand, continue') }}
                    </button>
                    <button
                        type="button"
                        id="delete-entreprise-submit-btn"
                        class="inline-flex items-center px-3 py-1.5 border border-red-600 text-xs font-medium rounded text-white bg-red-600 hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed hidden"
                        onclick="submitDeleteEntreprise()"
                        disabled
                    >
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

            const modal = document.getElementById('delete-entreprise-modal');
            const nameSpan = document.getElementById('delete-entreprise-name');
            const form = document.getElementById('delete-entreprise-form');
            const confirmInput = document.getElementById('delete-entreprise-confirm-input');
            const confirmWrapper = document.getElementById('delete-entreprise-confirm-input-wrapper');
            const nextBtn = document.getElementById('delete-entreprise-next-btn');
            const submitBtn = document.getElementById('delete-entreprise-submit-btn');
            const step1 = document.getElementById('delete-entreprise-step-1');
            const step2 = document.getElementById('delete-entreprise-step-2');

            nameSpan.textContent = name;

            // Reset to step 1
            step1.classList.remove('hidden');
            step2.classList.add('hidden');
            confirmWrapper.classList.add('hidden');
            confirmInput.value = '';
            submitBtn.classList.add('hidden');
            submitBtn.disabled = true;
            nextBtn.classList.remove('hidden');

            const actionUrl = "{{ route('manager.subscriptions.destroy', ['entreprise' => '__ID__']) }}".replace('__ID__', id);
            form.setAttribute('action', actionUrl);

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeDeleteEntrepriseModal() {
            const modal = document.getElementById('delete-entreprise-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            deleteEntrepriseId = null;
        }

        function goToDeleteEntrepriseStep2() {
            const step1 = document.getElementById('delete-entreprise-step-1');
            const step2 = document.getElementById('delete-entreprise-step-2');
            const confirmWrapper = document.getElementById('delete-entreprise-confirm-input-wrapper');
            const nextBtn = document.getElementById('delete-entreprise-next-btn');
            const submitBtn = document.getElementById('delete-entreprise-submit-btn');

            step1.classList.add('hidden');
            step2.classList.remove('hidden');
            confirmWrapper.classList.remove('hidden');
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');

            const confirmInput = document.getElementById('delete-entreprise-confirm-input');
            confirmInput.focus();

            function onInput() {
                submitBtn.disabled = confirmInput.value.trim().toUpperCase() !== 'DELETE';
            }

            confirmInput.removeEventListener('input', onInput);
            confirmInput.addEventListener('input', onInput);
        }

        function submitDeleteEntreprise() {
            if (!deleteEntrepriseId) {
                return;
            }

            const form = document.getElementById('delete-entreprise-form');
            form.submit();
        }
    </script>
@endsection