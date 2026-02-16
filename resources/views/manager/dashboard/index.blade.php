@extends('manager.layouts.app')

@section('content')
    <div class="space-y-6">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900">Overview</h2>
            <p class="mt-1 text-sm text-gray-500">
                High-level metrics about entreprises and subscriptions.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total entreprises</p>
                <p class="text-3xl font-semibold text-gray-900">{{ $totalEntreprises }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Active subscriptions</p>
                <p class="text-3xl font-semibold text-emerald-600">{{ $activeEntreprises }}</p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total users</p>
                <p class="text-3xl font-semibold text-sky-600">{{ $totalUsers }}</p>
            </div>
        </div>

        {{-- Earnings summary cards (from Stripe invoices) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total invoiced</p>
                <p class="text-2xl font-semibold text-gray-900">
                    {{ number_format($totalInvoiced, 2) }} EUR
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Total paid</p>
                <p class="text-2xl font-semibold text-rose-600">
                    {{ number_format($totalPaid, 2) }} EUR
                </p>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Outstanding</p>
                <p class="text-2xl font-semibold text-emerald-600">
                    {{ number_format($totalOutstanding, 2) }} EUR
                </p>
            </div>
        </div>

        {{-- Modern invoiced vs paid chart --}}
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-semibold text-gray-900">Invoiced vs. paid (last 12 months)</h3>
                    <p class="mt-1 text-xs text-gray-500">
                        Visual overview of Stripe invoice amounts compared to payments.
                    </p>
                </div>
            </div>
            <div class="h-72">
                <canvas id="manager-budget-chart"></canvas>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const canvas = document.getElementById('manager-budget-chart');
            if (!canvas) {
                return;
            }

            const labels = @json($chartMonths);
            const invoiced = @json($chartInvoiced);
            const paid = @json($chartPaid);

            const renderChart = function () {
                const ctx = canvas.getContext('2d');

                const gradientInvoiced = ctx.createLinearGradient(0, 0, 0, 300);
                gradientInvoiced.addColorStop(0, 'rgba(59, 130, 246, 0.4)');
                gradientInvoiced.addColorStop(1, 'rgba(59, 130, 246, 0)');

                const gradientPaid = ctx.createLinearGradient(0, 0, 0, 300);
                gradientPaid.addColorStop(0, 'rgba(34, 197, 94, 0.5)');
                gradientPaid.addColorStop(1, 'rgba(34, 197, 94, 0)');

                new window.Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Invoiced',
                                data: invoiced,
                                tension: 0.4,
                                borderColor: '#3b82f6',
                                backgroundColor: gradientInvoiced,
                                fill: true,
                                borderWidth: 2,
                                pointRadius: 3,
                            },
                            {
                                label: 'Paid',
                                data: paid,
                                tension: 0.4,
                                borderColor: '#22c55e',
                                backgroundColor: gradientPaid,
                                fill: true,
                                borderWidth: 2,
                                pointRadius: 3,
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                labels: {
                                    boxWidth: 12,
                                    usePointStyle: true,
                                },
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    label: function (context) {
                                        const value = context.parsed.y || 0;
                                        return `${context.dataset.label}: ${value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })} EUR`;
                                    },
                                },
                            },
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        scales: {
                            x: {
                                grid: {
                                    display: false,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: 'rgba(148, 163, 184, 0.2)',
                                },
                            },
                        },
                    },
                });
            };

            const loadScript = function (src) {
                return new Promise(function (resolve, reject) {
                    const script = document.createElement('script');
                    script.src = src;
                    script.async = true;
                    script.onload = function () { resolve(true); };
                    script.onerror = function () { reject(new Error('Failed to load ' + src)); };
                    document.head.appendChild(script);
                });
            };

            const ensureChartLibrary = async function () {
                if (typeof window.Chart !== 'undefined') {
                    return true;
                }

                try {
                    await loadScript("{{ asset('js/plugins/chartjs.min.js') }}");
                    if (typeof window.Chart !== 'undefined') {
                        return true;
                    }
                } catch (e) {
                    // Ignore and try CDN fallback.
                }

                try {
                    await loadScript('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js');
                    return typeof window.Chart !== 'undefined';
                } catch (e) {
                    return false;
                }
            };

            ensureChartLibrary().then(function (ok) {
                if (!ok) {
                    const fallbackMessage = document.createElement('p');
                    fallbackMessage.className = 'text-sm text-red-600';
                    fallbackMessage.textContent = 'Unable to load chart library.';
                    canvas.parentElement.appendChild(fallbackMessage);
                    return;
                }

                renderChart();
            });
        });
    </script>
@endpush




