<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Customer Report Preview Title') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles
    <link rel="stylesheet" href="{{ asset('css/dalytrac-calendar-preview.css') }}">
    @include('partials.edit-layout-styles')
    <style>
        .customer-report-preview .crp-composer { position: sticky; top: 20px; }
        .customer-report-preview .crp-composer > .crp-composer-body { padding: 20px; }
        .customer-report-preview .cep-fields { padding: 0; }
        .customer-report-preview .crp-actions { display: grid; gap: 10px; padding-top: 18px; border-top: 1px solid #e8eef2; }
        .customer-report-preview .crp-cancel { display: block; text-align: center; color: #64748b; font-size: 12px; padding: 8px; }
        .customer-report-preview .crp-cancel:hover { color: #2d7080; }
        .customer-report-preview .crp-meta { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .customer-report-preview .crp-meta span { border: 1px solid #dce6eb; border-radius: 8px; padding: 7px 10px; background: #fff; color: #526777; font-size: 12px; overflow-wrap: anywhere; }
        #customerSkippedTasksModal > div { border-radius: 14px; max-height: 90vh; overflow-y: auto; }
        @media (max-width: 1000px) { .customer-report-preview .crp-composer { position: static; } }
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main wrapper --}}
    <div class="flex flex-col flex-1 min-w-0 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Content --}}
        <main class="calendar-email-preview customer-report-preview flex-1 min-w-0 overflow-x-hidden overflow-y-auto">
            <div class="cep-container space-y-6">
                <div class="cep-header">
                    <div>
                        <p class="cep-eyebrow">{{ __('messages.Tasks breadcrumb') }}</p>
                        <h1 class="text-[#0B2638]">{{ __('messages.Customer report preview') }}</h1>
                        <div class="crp-meta">
                            <span>{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} – {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</span>
                            @if(filled($projectName))<span>{{ $projectName }}</span>@endif
                        </div>
                    </div>
                    <a href="{{ route('admin.tasks.pointage') }}" class="dt-btn dt-btn-ghost shrink-0">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12l7.5-7.5M3 12h18"/></svg>
                        {{ __('messages.Back to tasks') }}
                    </a>
                </div>

                @php
                    $hasSkippedTasks = isset($skippedTasks) && is_array($skippedTasks) && count($skippedTasks) > 0;
                @endphp

                @if($hasSkippedTasks)
                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.007v.008H12v-.008zm9-3.75a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-amber-800">
                            <p class="font-medium">
                                {{ __('messages.Customer preview warning line1') }}
                            </p>
                            <p class="mt-1">
                                {{ __('messages.Customer preview warning line2') }}
                            </p>
                            <button type="button"
                                    onclick="window.showCustomerSkippedTasksModal()"
                                    class="mt-2 inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-md text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-200">
                            {{ __('messages.View skipped tasks') }}
                            </button>
                        </div>
                    </div>
                @endif

                <div class="cep-layout">
                    {{-- PDF preview --}}
                    <div>
                        <div class="cep-card">
                            <div class="cep-card-heading">
                                <div class="cep-heading-group">
                                    <span class="cep-icon" aria-hidden="true">PDF</span>
                                    <div>
                                        <h2>{{ __('messages.PDF preview') }}</h2>
                                        <p>{{ __('messages.PDF preview subtitle') }}</p>
                                    </div>
                                </div>
                                <a class="cep-open" href="{{ $pdfUrl }}" target="_blank" rel="noopener">{{ __('messages.calendar_preview_open') }} <span aria-hidden="true">↗</span></a>
                            </div>
                            <div class="cep-pdf-frame">
                                <iframe src="{{ $pdfUrl }}" title="{{ __('messages.PDF preview') }}"></iframe>
                            </div>
                        </div>
                    </div>

                    {{-- Email form --}}
                    <div>
                        <div class="cep-card crp-composer">
                            <div class="cep-card-heading">
                                <div class="cep-heading-group">
                                    <span class="cep-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="5" width="18" height="14" rx="3"/><path d="m3 7 9 6 9-6"/></svg></span>
                                    <div>
                                        <h2>{{ __('messages.Send by email') }}</h2>
                                        <p>{{ __('messages.Send by email subtitle') }}</p>
                                    </div>
                                </div>
                            </div>
                            <div class="crp-composer-body space-y-6">
                                @if(session('success'))
                                    <div class="flex items-start gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                                        <svg class="w-5 h-5 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                                        <div class="text-sm font-medium">{{ session('success') }}</div>
                                    </div>
                                @endif

                                <form action="{{ route('admin.CustomerPointagerepports.send') }}" method="POST" class="cep-fields">
                                    @csrf
                                    <input type="hidden" name="start_date" value="{{ $startDate }}">
                                    <input type="hidden" name="end_date" value="{{ $endDate }}">
                                    <input type="hidden" name="project_id" value="{{ $projectId }}">
                                    <input type="hidden" name="employee_id" value="{{ $employeeId }}">
                                    <input type="hidden" name="task_ids" value="{{ json_encode($taskIds) }}">
                                    <input type="hidden" name="client_id" value="{{ $clientId }}">
                                    <input type="hidden" name="redirect_to" value="{{ route('admin.index') }}">

                                    <div class="cep-field">
                                        <label for="recipients" class="text-sm font-medium text-gray-700">{{ __('messages.Recipients label') }}</label>
                                        <textarea id="recipients" name="recipients" rows="3" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                            placeholder="{{ __('messages.Recipients placeholder') }}">{{ implode(',', $projectContacts) }}</textarea>
                                    </div>

                                    <div class="cep-field">
                                        <label for="subject" class="text-sm font-medium text-gray-700">{{ __('messages.Subject label') }}</label>
                                        <input type="text" id="subject" name="subject" required
                                            value="{{ __('messages.Worksheet subject prefix') }} {{ $projectName }} - {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}"
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                    </div>

                                    <div class="cep-field">
                                        <label for="body" class="text-sm font-medium text-gray-700">{{ __('messages.Message label') }}</label>
                                        <textarea id="body" name="body" rows="8" required
                                            class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ __('messages.Customer preview default body') }}</textarea>
                                    </div>

                                    <div class="crp-actions">
                                        <button type="submit" class="cep-send">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                            {{ __('messages.Send email button') }}
                                        </button>
                                        <a href="{{ route('admin.tasks.pointage') }}" class="crp-cancel">
                                            {{ __('messages.Cancel') }}
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    @livewireScripts

    @if($hasSkippedTasks)
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Optionally auto-open the modal on load
                // window.showCustomerSkippedTasksModal();
            });

            window.showCustomerSkippedTasksModal = function() {
                var modal = document.getElementById('customerSkippedTasksModal');
                if (modal) {
                    modal.classList.remove('hidden');
                }
            };

            window.closeCustomerSkippedTasksModal = function() {
                var modal = document.getElementById('customerSkippedTasksModal');
                if (modal) {
                    modal.classList.add('hidden');
                }
            };
        </script>

        <!-- Skipped tasks modal for customer report -->
        <div id="customerSkippedTasksModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4">
                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">
                        {{ __('messages.Skipped tasks modal title') }}
                    </h3>
                    <button type="button"
                            onclick="closeCustomerSkippedTasksModal()"
                            class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <span class="sr-only">{{ __('messages.Close') }}</span>
                        &times;
                    </button>
                </div>
                <div class="px-4 py-3 text-sm text-gray-700 border-b border-gray-100">
                    {{ $skippedMessage ?? __('messages.Skipped tasks modal description') }}
                </div>
                <div class="px-4 py-3 max-h-96 overflow-y-auto">
                    <table class="min-w-full border border-gray-200 rounded-lg overflow-hidden">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                    {{ __('messages.Task header') }}
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                    {{ __('messages.Project header') }}
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                    {{ __('messages.Employee header') }}
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                    {{ __('messages.Start date header') }}
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                    {{ __('messages.End date header') }}
                                </th>
                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider border-b border-gray-200">
                                    {{ __('messages.Duration header') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white">
                            @foreach($skippedTasks as $task)
                                @php
                                    $durationMinutes = $task['duration'] ?? 0;
                                    $hours = floor($durationMinutes / 60);
                                    $minutes = $durationMinutes % 60;
                                    $durationText = sprintf('%02dh:%02dm', $hours, $minutes);
                                @endphp
                                <tr>
                                    <td class="px-3 py-2 text-sm text-gray-700 border-b border-gray-200">
                                        {{ $task['task_name'] ?? '' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700 border-b border-gray-200">
                                        {{ $task['project_name'] ?? '' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700 border-b border-gray-200">
                                        {{ $task['employee_name'] ?? '' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700 border-b border-gray-200">
                                        {{ $task['start_date'] ?? '' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700 border-b border-gray-200">
                                        {{ $task['end_date'] ?? '' }}
                                    </td>
                                    <td class="px-3 py-2 text-sm text-gray-700 border-b border-gray-200">
                                        {{ $durationText }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-4 py-3 flex justify-end border-t border-gray-200">
                    <button type="button"
                            onclick="closeCustomerSkippedTasksModal()"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        {{ __('messages.Close') }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</body>
</html>
