<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ __('messages.Generate Invoice - DalyTrac') }}</title>

    @vite('resources/css/app.css')

    @livewireStyles

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>

        /* Custom styles to make <select> match <input> */
        select.form-select-custom {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }

        select:invalid {
            color: #9ca3af;
        }

        option {
            color: #111827;
        }

        /* Native date inputs: match the custom control family */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.55;
            cursor: pointer;
        }
        input[type="date"]:hover::-webkit-calendar-picker-indicator {
            opacity: 0.85;
        }

        /* Ensure section icon circle stays compact on mobile */
        .dt-invoice-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
        }
    </style>

</head>

<body class="dt-app-shell bg-[#f8fafc] text-slate-900 antialiased flex h-screen overflow-hidden font-sans">

    {{-- Sidebar Component --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#f8fafc] p-4 sm:p-6 lg:px-20 lg:py-8">

            {{-- Back route computation --}}
            @php
                $backRoute = route('admin.index');
                if (Auth::user()->hasRole('Responsable de projet'))
                    $backRoute = route('Responsable_de_projet.index');
                elseif (Auth::user()->hasRole('Employee'))
                    $backRoute = route('employee.index');
                elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    $backRoute = route('Responsable_gestionnaire.index');
            @endphp

            {{-- Page Header --}}
            <div class="flex items-center gap-3 mb-5">
                <a href="{{ $backRoute }}" title="{{ __('messages.Invoices') }}" aria-label="{{ __('messages.Invoices') }}"
                   class="dt-btn dt-btn-ghost !px-3 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
                    </svg>
                </a>
                <div class="min-w-0">
                    <p class="dt-overline mb-1">{{ __('messages.Invoices') }}</p>
                    <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">{{ __('messages.Generate Invoice - DalyTrac') }}</h1>
                    <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Generate Invoice subtitle') }}</p>
                </div>
            </div>

            {{-- Session Message Alert --}}
            @if(session('message'))
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative flex items-center gap-2" role="alert">
                    <strong class="text-[13px] font-bold">{{ __('messages.Error!') }}</strong>
                    <span class="block sm:inline text-[13px] font-medium">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';"
                            class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl text-red-600 hover:text-red-800" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
            @endif

            {{-- Full Invoice Card --}}
            <div class="dt-card dt-card-hover overflow-hidden mb-5">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="dt-invoice-icon bg-[#0B2638]/[0.06] ring-1 ring-[#0B2638]/10 !w-9 !h-9 !rounded-xl">
                            <svg class="w-5 h-5 text-[#0B2638]" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h6 class="dt-section-title">{{ __('messages.Full Invoice') }} </h6>
                            <p class="dt-section-subtitle mt-0.5">{{ __('messages.Full Invoice description') }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Project Filter --}}
                        <div class="lg:col-span-1">
                            <label for="projectFilter1" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Select Project') }}</label>
                            <select id="projectFilter1"
                                    class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm form-select-custom py-2 px-3"
                                    required>
                                <option value="">{{ __('messages.Select Project') }}</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}"
                                            data-cost-per-hour="{{ $project->cost_per_hour }}"
                                            data-tva="{{ $project->tva }}">
                                        @if(preg_match('/^(\d+)(.*)/', $project->name, $matches))
                                            {{ $matches[1] }}{{ $matches[2] }}
                                        @else
                                            {{ strtoupper($project->name) }}
                                        @endif
                                        -
                                        @if($project->budget)
                                            @php
                                                $totalBilled = \App\Models\invoices_history::where('project_id', $project->id)->where('Payment_status', '!=', -1)->sum('total_htva');
                                                $unbilledBudget = $project->budget - $totalBilled;
                                            @endphp
                                            {{ number_format(max($unbilledBudget, 0), 2, '.', '') }}€
                                        @else
                                            {{ __('messages.No Budget') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Hours --}}
                        <div class="lg:col-span-1">
                            <label for="hoursInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Hours (h:mm)') }}</label>
                            <input type="text" id="hoursInput"
                                   class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                   placeholder="{{ __('messages.Example: 5:30') }}">
                        </div>
                        {{-- Manual Budget --}}
                        <div class="lg:col-span-1">
                            <label for="manualBudgetInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Manual Budget (€)') }}</label>
                            <input type="number" step="0.01" id="manualBudgetInput"
                                   class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                   placeholder="{{ __('messages.Manual budget') }}">
                        </div>
                        {{-- Comment (Full Width) --}}
                        <div class="lg:col-span-3">
                            <label for="FullInvComment" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Comment') }}</label>
                            <textarea name="FullInvComment" id="FullInvComment"
                                      placeholder="{{ __('messages.Add any additional notes...') }}"
                                      rows="3"
                                      class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <a id="downloadInvoiceBtn" href="#"
                           class="dt-btn dt-btn-primary w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            {{ __('messages.View Invoice') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Period Invoice Card --}}
            <div class="dt-card dt-card-hover overflow-hidden mb-5">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="dt-invoice-icon bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 !w-9 !h-9 !rounded-xl">
                            <svg class="w-5 h-5 text-[#2D7080]" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h6 class="dt-section-title">{{ __('messages.Period Invoice') }}</h6>
                            <p class="dt-section-subtitle mt-0.5">{{ __('messages.Period Invoice description') }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        {{-- Project Filter --}}
                        <div class="lg:col-span-1">
                            <label for="projectFilter2" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Select Project') }}</label>
                            <select id="projectFilter2"
                                    class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm form-select-custom py-2 px-3"
                                    required>
                                <option value="">{{ __('messages.Select Project') }}</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}">
                                        {{ strtoupper($project->name) }} -
                                        @if($project->budget)
                                            @php
                                                $totalBilled = \App\Models\invoices_history::where('project_id', $project->id)->where('Payment_status', '!=', -1)->sum('total_htva');
                                                $unbilledBudget = $project->budget - $totalBilled;
                                            @endphp
                                            {{ number_format(max($unbilledBudget, 0), 2, '.', '') }}€
                                        @else
                                            {{ __('messages.No Budget') }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Start Date --}}
                        <div class="lg:col-span-1">
                            <label for="startDateInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Start Date') }}</label>
                            <input type="date" id="startDateInput"
                                   placeholder="{{ __('messages.Start Date placeholder') }}"
                                   class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        </div>
                        {{-- End Date --}}
                        <div class="lg:col-span-1">
                            <label for="endDateInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.End Date') }}</label>
                            <input type="date" id="endDateInput"
                                   placeholder="{{ __('messages.End Date placeholder') }}"
                                   class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        </div>
                        {{-- Comment (Full Width) --}}
                        <div class="lg:col-span-3">
                            <label for="PeriodInvComment" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Comment') }}</label>
                            <textarea name="PeriodInvComment" id="PeriodInvComment"
                                      placeholder="{{ __('messages.Add any additional notes...') }}"
                                      rows="3"
                                      class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <a id="downloadDurationInvoiceBtn" href="#"
                           class="dt-btn dt-btn-primary w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            {{ __('messages.View Invoice') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Partial Invoice Card --}}
            <div class="dt-card dt-card-hover overflow-hidden mb-5">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="dt-invoice-icon bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 !w-9 !h-9 !rounded-xl">
                            <svg class="w-5 h-5 text-[#2D7080]" fill="none" stroke="currentColor"
                                 stroke-width="1.8" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M7 15l8-8 8 8" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h6 class="dt-section-title">{{ __('messages.Partial Invoice') }}</h6>
                            <p class="dt-section-subtitle mt-0.5">{{ __('messages.Partial Invoice description') }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Devis Select --}}
                        <div class="lg:col-span-1">
                            <label for="devisSelect3" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Select Quote') }}</label>
                            <select id="devisSelect3"
                                    class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm form-select-custom py-2 px-3"
                                    required>
                                <option value="">{{ __('messages.Select Quote') }}</option>
                                {{-- Options loaded by JS --}}
                            </select>
                        </div>
                        {{-- Amount --}}
                        <div class="lg:col-span-1">
                            <label for="partialAmountInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Amount (€)') }}</label>
                            <input type="number" step="0.0001" id="partialAmountInput"
                                   placeholder="{{ __('messages.Amount') }}"
                                   class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        </div>
                        {{-- Comment (Full Width) --}}
                        <div class="lg:col-span-2">
                            <label for="PartialInvComment" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Comment') }}</label>
                            <textarea name="PartialInvComment" id="PartialInvComment"
                                      placeholder="{{ __('messages.Add any additional notes...') }}"
                                      rows="3"
                                      class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <a id="downloadPartialInvoiceBtn" href="#"
                           class="dt-btn dt-btn-primary w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            {{ __('messages.View Invoice') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Percentage Invoice Card --}}
            <div class="dt-card dt-card-hover overflow-hidden">
                <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="dt-invoice-icon bg-[#E7B84B]/20 ring-1 ring-[#E7B84B]/40 !w-9 !h-9 !rounded-xl">
                            <span class="text-lg font-bold text-[#0B2638]">%</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h6 class="dt-section-title">{{ __('messages.Percentage Invoice') }}</h6>
                            <p class="dt-section-subtitle mt-0.5">{{ __('messages.Percentage Invoice description') }}</p>
                        </div>
                    </div>
                </div>
                <div class="px-5 sm:px-6 py-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        {{-- Devis Select --}}
                        <div class="lg:col-span-1">
                            <label for="devisSelect4" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Select Quote') }}</label>
                            <select id="devisSelect4"
                                    class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm form-select-custom py-2 px-3"
                                    required>
                                <option value="">{{ __('messages.Select Quote') }}</option>
                                {{-- Options loaded by JS --}}
                            </select>
                        </div>
                        {{-- Percentage --}}
                        <div class="lg:col-span-1">
                            <label for="percentageInput" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Percentage (%)') }}</label>
                            <input type="number" step="0.01" id="percentageInput"
                                   placeholder="{{ __('messages.Percentage') }}"
                                   class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3">
                        </div>
                        <div class="lg:col-span-2">
                            <label for="PercentageInvComment" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Comment') }}</label>
                            <textarea name="PercentageInvComment" id="PercentageInvComment"
                                      placeholder="{{ __('messages.Add any additional notes...') }}"
                                      rows="3"
                                      class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                        <a id="downloadPercentageInvoiceBtn" href="#"
                           class="dt-btn dt-btn-primary w-full sm:w-auto justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                 viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                      d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
                            {{ __('messages.View Invoice') }}
                        </a>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>

        $(document).ready(function() {

            $('#projectFilter1').change(function() {

                if ($(this).val()) {

                    $('#hoursInput').prop('disabled', false);

                    $('#manualBudgetInput').prop('disabled', false);

                } else {

                    $('#hoursInput').prop('disabled', true).val('');

                    $('#manualBudgetInput').prop('disabled', true).val('');

                }

                 $('#hoursInput, #manualBudgetInput').trigger('input');

            }).trigger('change');



            $('#hoursInput').on('input', function() {

                if ($(this).val()) {

                    $('#manualBudgetInput').prop('disabled', true);

                } else if ($('#projectFilter1').val()) {

                    $('#manualBudgetInput').prop('disabled', false);

                }

                var value = $(this).val().replace(/[^0-9:]/g, '');

                if (value.length > 0 && !value.includes(':')) {

                    if (value.length > 2) value = value.slice(0, -2) + ':' + value.slice(-2);

                    else if (value.length === 2) value = value + ':';

                } else if (value.includes(':')) {

                    var parts = value.split(':');

                    if (parts[1] && parts[1].length > 2) parts[1] = parts[1].substring(0, 2);

                    value = parts[0] + ':' + (parts[1] || '');

                }

                $(this).val(value);

            });



             $('#manualBudgetInput').on('input', function() {

                 if ($(this).val()) {

                     $('#hoursInput').prop('disabled', true);

                 } else if ($('#projectFilter1').val()) {

                     $('#hoursInput').prop('disabled', false);

                 }

            });



            $('#downloadInvoiceBtn').click(function(e) {
                e.preventDefault();

                var projectId = $('#projectFilter1').val();
                var hoursInput = $('#hoursInput').val();
                var manualBudget = $('#manualBudgetInput').val();
                var comment = encodeURIComponent($('#FullInvComment').val());
                var selectedProject = $('#projectFilter1 option:selected');
                var projectTva = selectedProject.data('tva');

                // Check if project has TVA
                if (!projectTva) {
                    alert(@json(__('messages.This project has no TVA. Please add TVA to the project before generating an invoice.')));
                    return;
                }

                // Convert time format to decimal hours
                var hours = 0;
                if (hoursInput) {
                    // Remove any 'h' or 'm' if present and split on colon
                    var timeParts = hoursInput.replace(/[hm]/g, '').split(':');

                    if (timeParts.length === 2) {
                        var h = parseInt(timeParts[0]);
                        var m = parseInt(timeParts[1]);

                        if (!isNaN(h) && !isNaN(m) && m < 60) {
                            hours = h + (m / 60);
                        } else {
                            alert(@json(__('messages.Please enter a valid time format (e.g., 5:30 or 5h:30m)')));
                            return;
                        }
                    } else {
                        alert(@json(__('messages.Please enter time in format: h:mm (example: 5:30)')));
                        return;
                    }
                }

                if (!projectId) {
                    alert(@json(__('messages.Please select a project.')));
                    return;
                }

                if (!hours && !manualBudget) {
                    alert(@json(__('messages.Please enter either hours or manual budget.')));
                    return;
                }

                var baseUrl = '';
                @if(Auth::user()->hasRole('Responsable de projet'))
                    baseUrl = "{{ route('Responsable_de_projet.projects.invoice', ['id' => ':id']) }}";
                @endif
                @if(Auth::user()->hasRole('Admin'))
                    baseUrl = "{{ route('admin.projects.invoice', ['id' => ':id']) }}";
                @endif
                @if(Auth::user()->hasRole('Employee'))
                    baseUrl = "{{ route('employee.projects.invoice', ['id' => ':id']) }}";
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    baseUrl = "{{ route('Responsable_gestionnaire.projects.invoice', ['id' => ':id']) }}";
                @endif

                // Replace :id with actual project ID
                var downloadUrl = baseUrl.replace(':id', projectId);

                // Add query parameters
                var params = new URLSearchParams();
                if (hours) {
                    // FIXED: Convert to number using parseFloat
                    params.append('hours', parseFloat(hours.toFixed(2)));
                } else if (manualBudget) {
                    // FIXED: Convert to number using parseFloat
                    params.append('manualBudget', parseFloat(manualBudget));
                }
                if (comment) {
                    params.append('comment', comment);
                }

                // Append parameters to URL
                downloadUrl += '?' + params.toString();
                window.location.href = downloadUrl;
            });

            $('#downloadDurationInvoiceBtn').click(function(e) {
                e.preventDefault();

                var projectId = $('#projectFilter2').val();
                var startDate = $('#startDateInput').val();
                var endDate = $('#endDateInput').val();
                var comment = encodeURIComponent($('#PeriodInvComment').val());

                console.log('id : ', projectId);

                if (projectId && startDate && endDate) {
                    var downloadUrl = '';
                    @if(Auth::user()->hasRole('Responsable de projet'))
                         downloadUrl = "{{ route('Responsable_de_projet.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                     downloadUrl =
                        "{{ route('admin.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                         downloadUrl = "{{ route('employee.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                         downloadUrl = "{{ route('Responsable_gestionnaire.projects.invoicebyduration2', ['id' => ':projectId', 'startdate' => ':startDate', 'enddate' => ':endDate']) }}";
                    @endif
                    downloadUrl = downloadUrl.replace(':projectId', projectId);
                    downloadUrl = downloadUrl.replace(':startDate', startDate);
                    downloadUrl = downloadUrl.replace(':endDate', endDate);
                    var params = new URLSearchParams();
                    if (comment) {
                        params.append('comment', comment);
                    }
                    downloadUrl += '?' + params.toString();
                    window.location.href = downloadUrl;
                } else {
                    alert(@json(__('messages.Please select a project and enter start/end dates.')));
                }
            });

            $('#downloadPartialInvoiceBtn').click(function(e) {
                e.preventDefault();

                var projectId = null;
                // FIXED: Convert to number using parseFloat
                var amount = parseFloat($('#partialAmountInput').val());
                // Replace line breaks with spaces and clean up the comment
                var comment = $('#PartialInvComment').val()
                    .replace(/\r?\n/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();

                comment = encodeURIComponent(comment);
                var devisId = $('#devisSelect3').val();

                console.log('partial devis : ', devisId);
                console.log('comment : ', comment);

                if (devisId && amount) {
                    var downloadUrl = '';
                    @if(Auth::user()->hasRole('Responsable de projet'))
                        downloadUrl = "{{ route('Responsable_de_projet.projects.PartialInvoice', ['projectid' => ':projectId', 'amount' => ':amount']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                        downloadUrl = "{{ route('admin.projects.PartialInvoice', ['projectid' => ':projectId', 'amount' => ':amount']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                        downloadUrl = "{{ route('employee.projects.PartialInvoice', ['projectid' => ':projectId', 'amount' => ':amount']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                        downloadUrl = "{{ route('Responsable_gestionnaire.projects.PartialInvoice', ['projectid' => ':projectId', 'amount' => ':amount']) }}";
                    @endif

                    downloadUrl = downloadUrl
                        .replace(':projectId', 0)
                        .replace(':amount', amount);

                    console.log('Final URL:', downloadUrl);
                    var params = new URLSearchParams();
                    if (comment) {
                        params.append('comment', comment);
                    }
                    params.append('devis_id', devisId);
                    downloadUrl += '?' + params.toString();
                    window.location.href = downloadUrl;
                } else {
                    alert(@json(__('messages.Please select a devis and enter the Amount.')));
                }
            });

            $('#downloadPercentageInvoiceBtn').click(function(e) {
                e.preventDefault();

                var projectId = null;
                // FIXED: Convert to number using parseFloat
                var percentage = parseFloat($('#percentageInput').val());
                var comment = encodeURIComponent($('#PercentageInvComment').val());
                var devisId = $('#devisSelect4').val();

                console.log('id : ', projectId);

                if (devisId && percentage) {
                    var downloadUrl = '';
                    @if(Auth::user()->hasRole('Responsable de projet'))
                         downloadUrl = "{{ route('Responsable_de_projet.projects.PercentageInvoice', ['projectid' => ':projectId', 'percentage' => ':percentage']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Admin'))
                     downloadUrl =
                        "{{ route('admin.projects.PercentageInvoice', ['projectid' => ':projectId', 'percentage' => ':percentage']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Employee'))
                         downloadUrl = "{{ route('employee.projects.PercentageInvoice', ['projectid' => ':projectId', 'percentage' => ':percentage']) }}";
                    @endif
                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                         downloadUrl = "{{ route('Responsable_gestionnaire.projects.PercentageInvoice', ['projectid' => ':projectId', 'percentage' => ':percentage']) }}";
                    @endif
                    downloadUrl = downloadUrl.replace(':projectId', 0);
                    downloadUrl = downloadUrl.replace(':percentage', percentage);

                    var params = new URLSearchParams();
                    if (comment) {
                        params.append('comment', comment);
                    }
                    params.append('devis_id', devisId);
                    downloadUrl += '?' + params.toString();
                    window.location.href = downloadUrl;
                } else {
                    alert(@json(__('messages.Please select a devis and enter the percentage.')));
                }
            });



            function loadAllDevis(selectId) {
                $.ajax({
                    url: '/admin/get-all-devis',
                    type: 'GET',
                    success: function(response) {
                        var devisSelect = $(selectId);
                        devisSelect.empty();
                        devisSelect.append('<option value="">' + @json(__('messages.Select Devis')) + '</option>');
                        response.devis.forEach(function(devis) {
                            const projectPart = devis.project_name ? ` | ` + @json(__('messages.Project:')) + ` ${devis.project_name}` : '';
                            const unbilledPart = (typeof devis.unbilled !== 'undefined') ? ` | ` + @json(__('messages.Unbilled:')) + ` €${Number(devis.unbilled).toFixed(2)}` : '';
                            devisSelect.append(`<option value="${devis.id}">` + @json(__('messages.Devis')) + `: ${response.prefix}-${devis.number}${projectPart}${unbilledPart}</option>`);
                        });
                    },
                    error: function(xhr) {
                        console.error('Error loading all devis:', xhr);
                    }
                });
            }

            function loadDevisComment(devisId, commentId) {
                if (devisId) {
                    $.ajax({
                        url: '/admin/get-devis-comment/' + devisId,
                        type: 'GET',
                        success: function(response) {
                            $(commentId).val(response.comment);
                        },
                        error: function(xhr) {
                            console.error('Error loading devis comment:', xhr);
                        }
                    });
                }
            }

            loadAllDevis('#devisSelect3');

            loadAllDevis('#devisSelect4');

            $('#devisSelect3').change(function() { loadDevisComment($(this).val(), '#PartialInvComment'); });

            $('#devisSelect4').change(function() { loadDevisComment($(this).val(), '#PercentageInvComment'); });



        });

    </script>
</body>
</html>
