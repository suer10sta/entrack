@extends('manager.layouts.app')

@section('content')
    @php
        /** @var string|null $activePlan */
        $activePlan = $activePlan ?? request('plan');
    @endphp
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-semibold text-gray-900">Subscriptions overview</h2>
                <p class="mt-1 text-sm text-gray-500">
                    Plans distribution and entreprise subscription status.
                </p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <a href="{{ route('manager.subscriptions.index', ['plan' => 'solo']) }}"
               class="bg-white rounded-lg shadow-sm border p-6 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 {{ $activePlan === 'solo' ? 'border-blue-600 ring-1 ring-blue-600' : 'border-gray-200 hover:border-gray-300' }}">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Solo plans</p>
                <p class="text-3xl font-semibold text-gray-900">{{ $planCounts['solo'] ?? 0 }}</p>
            </a>
            <a href="{{ route('manager.subscriptions.index', ['plan' => 'premium']) }}"
               class="bg-white rounded-lg shadow-sm border p-6 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 {{ $activePlan === 'premium' ? 'border-emerald-600 ring-1 ring-emerald-600' : 'border-gray-200 hover:border-gray-300' }}">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Premium plans</p>
                <p class="text-3xl font-semibold text-emerald-600">{{ $planCounts['premium'] ?? 0 }}</p>
            </a>
            <a href="{{ route('manager.subscriptions.index', ['plan' => 'pro']) }}"
               class="bg-white rounded-lg shadow-sm border p-6 transition hover:shadow-md focus:outline-none focus:ring-2 focus:ring-sky-500 {{ $activePlan === 'pro' ? 'border-sky-600 ring-1 ring-sky-600' : 'border-gray-200 hover:border-gray-300' }}">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Pro plans</p>
                <p class="text-3xl font-semibold text-sky-600">{{ $planCounts['pro'] ?? 0 }}</p>
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-1">Estimated monthly revenue</h3>
            <p class="text-2xl font-semibold text-gray-900">
                {{ number_format($estimatedMonthlyRevenue, 2) }} EUR
            </p>
            <p class="mt-1 text-xs text-gray-500">
                Based on active Stripe subscriptions and normalized recurring intervals.
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Entreprises and subscriptions</h3>
                        @if ($activePlan)
                            <p class="mt-1 text-xs text-gray-500">
                                Filter: <span class="font-semibold text-gray-700">{{ ucfirst($activePlan) }}</span>
                            </p>
                        @endif
                    </div>
                    @if ($activePlan)
                        <a href="{{ route('manager.subscriptions.index') }}"
                           class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded text-gray-700 hover:bg-gray-50">
                            Clear filter
                        </a>
                    @endif
                </div>
            </div>
            <div class="p-6 overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-200 bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 font-semibold text-gray-600">Name</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Email</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Plan</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Plan ends at</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Trial ends at</th>
                        <th class="px-4 py-2 font-semibold text-gray-600">Account status</th>
                        <th class="px-4 py-2 font-semibold text-gray-600 text-right">Actions</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($entreprises as $entreprise)
                            @php
                                $resolvedPlanEndsAt = $planEndsAtByEntrepriseId[$entreprise->id] ?? null;
                                $isActive = (bool) ($subscriptionStatusByEntrepriseId[$entreprise->id] ?? false);
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-900">{{ $entreprise->name }}</td>
                                <td class="px-4 py-2 text-gray-500">{{ $entreprise->email }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $entreprise->type_compt ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-500">
                                    @if ($resolvedPlanEndsAt)
                                        {{ $resolvedPlanEndsAt->format('Y-m-d H:i') }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-gray-500">
                                    {{ $entreprise->trial_ends_at ? $entreprise->trial_ends_at->format('Y-m-d') : '—' }}
                                </td>
                                <td class="px-4 py-2">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $isActive ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-red-50 text-red-700 border-red-200' }}">
                                        {{ $isActive ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 text-right space-x-2">
                                    <a href="{{ route('manager.subscriptions.show', $entreprise) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-blue-600 text-xs font-medium rounded text-blue-600 hover:bg-blue-50">
                                        View
                                    </a>
                                    @if (! method_exists($entreprise, 'isBlockingExempt') || ! $entreprise->isBlockingExempt())
                                        <button
                                            type="button"
                                            class="inline-flex items-center px-3 py-1.5 border border-red-600 text-xs font-medium rounded text-red-600 hover:bg-red-50"
                                            onclick="openDeleteEntrepriseModal({{ $entreprise->id }}, '{{ addslashes($entreprise->name) }}')"
                                        >
                                            Delete
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-4 text-center text-sm text-gray-500">
                                    No entreprises found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $entreprises->links() }}
                </div>
            </div>