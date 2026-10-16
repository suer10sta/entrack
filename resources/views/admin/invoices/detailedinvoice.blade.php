<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('messages.Detailed Invoice - DalyTrac') }}</title>
    @vite('resources/css/app.css')
    @livewireStyles

    @if(session('error'))
        <script>
            alert(@json(session('error')));
        </script>
    @endif

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

        /* Section icon tile */
        .dt-invoice-icon {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 0.75rem;
        }

        /* Line-item table: horizontal scroll on desktop, stacked cards on small screens */
        @media (max-width: 1024px) {
            #invoiceDetailsTable {
                display: block;
                min-width: 0 !important;
            }
            #invoiceDetailsTable thead {
                display: none;
            }
            #invoiceDetailsTable tbody {
                display: block;
            }
            #invoiceDetailsTable tbody tr.detail-row {
                display: block;
                background: #fff;
                border: 1px solid #e2e8f0;
                border-radius: 12px;
                margin: 0.75rem;
                padding: 0.85rem 1rem;
                box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
            }
            #invoiceDetailsTable tbody td {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0.45rem 0;
                border: 0 !important;
            }
            #invoiceDetailsTable tbody td + td {
                border-top: 1px dashed #eef2f6 !important;
            }
            #invoiceDetailsTable tbody td::before {
                content: attr(data-label);
                flex-shrink: 0;
                font-size: 11px;
                line-height: 1rem;
                font-weight: 700;
                letter-spacing: 0.08em;
                text-transform: uppercase;
                color: #64748b;
                margin: 0;
            }
            #invoiceDetailsTable tbody td[data-label=""]::before {
                content: none;
            }
            #invoiceDetailsTable tbody td input {
                flex: 1;
                min-width: 0;
                width: auto;
            }
            #invoiceDetailsTable tbody tr.detail-row td:first-child {
                padding-top: 0;
            }
            #invoiceDetailsTable tbody tr.detail-row td:last-child {
                padding-bottom: 0;
            }
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

            <div class="w-full">

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
                        <h1 class="text-[22px] sm:text-[24px] font-bold tracking-tight text-[#0B2638] leading-tight truncate">{{ __('messages.Detailed Invoice - DalyTrac') }}</h1>
                        <p class="text-[13.5px] text-slate-500 mt-1">{{ __('messages.Detailed Invoice subtitle') }}</p>
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

                {{-- Invoice + Client information --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

                    {{-- Invoice Information --}}
                    <div class="dt-card overflow-hidden lg:col-span-2">
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
                                    <h6 class="dt-section-title">{{ __('messages.Invoice Information') }}</h6>
                                    <p class="dt-section-subtitle mt-0.5">{{ __('messages.Detailed Invoice Generator') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-5 sm:px-6 py-5">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="md:col-span-1">
                                    <label for="projectSelect" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Select Project') }}</label>
                                    <select id="projectSelect" class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm form-select-custom py-2 px-3" required>
                                        <option value="">{{ __('messages.Select Project') }}</option>
                                        @foreach($projects as $project)
                                        <option value="{{ $project->id }}"
                                                data-cost-per-hour="{{ $project->cost_per_hour }}"
                                                data-tva="{{ $project->tva }}"
                                                data-client="{{ $project->client->name ?? '' }}">
                                            @if(preg_match('/^(\d+)(.*)/', $project->name, $matches)){{ $matches[1] }}{{ $matches[2] }}@else{{ strtoupper($project->name) }}@endif
                                            - @if($project->budget)
                                                @php
                                                    $totalBilled = \App\Models\invoices_history::where('project_id', $project->id)->where('Payment_status', '!=', -1)->sum('total_htva');
                                                    $unbilledBudget = $project->budget - $totalBilled;
                                                @endphp
                                                {{ number_format(max($unbilledBudget, 0), 2, '.', '') }}€
                                            @else<span class="text-red-600">{{ __('messages.No Budget') }}</span>@endif
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="md:col-span-2">
                                    <label for="comment" class="block text-[13px] font-semibold text-slate-700 mb-1.5">{{ __('messages.Comment') }}</label>
                                    <textarea id="comment" name="comment" rows="1"
                                              class="block w-full rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3"
                                              placeholder="{{ __('messages.Add any additional notes...') }}"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Client Information --}}
                    <div class="dt-card overflow-hidden">
                        <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                            <div class="flex items-center gap-3">
                                <div class="dt-invoice-icon bg-[#2D7080]/10 ring-1 ring-[#2D7080]/20 !w-9 !h-9 !rounded-xl">
                                    <svg class="w-5 h-5 text-[#2D7080]" fill="none" stroke="currentColor"
                                         stroke-width="1.8" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-1.13a4 4 0 10-4-4 4 4 0 004 4zm6-4a4 4 0 11-4-4" />
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h6 class="dt-section-title">{{ __('messages.Client Information') }}</h6>
                                    <p class="dt-section-subtitle mt-0.5">{{ __('messages.Client') }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="px-5 sm:px-6 py-5">
                            {{-- Empty state --}}
                            <div id="clientEmpty" class="text-center py-4">
                                <span class="dt-empty-icon mb-3">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                    </svg>
                                </span>
                                <p class="text-[13px] text-slate-500">{{ __('messages.Please select a project') }}</p>
                            </div>

                            {{-- Selected client --}}
                            <div id="clientData" class="hidden space-y-3">
                                <div>
                                    <p class="dt-overline mb-1">{{ __('messages.Client') }}</p>
                                    <p id="clientName" class="text-[15px] font-semibold text-[#0B2638] break-words">—</p>
                                </div>
                                <div class="grid grid-cols-2 gap-3 pt-1">
                                    <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500 mb-1">{{ __('messages.VAT') }}</p>
                                        <p id="clientVat" class="text-[13.5px] font-bold text-[#0B2638]">—</p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 px-3 py-2.5">
                                        <p class="text-[11px] font-semibold uppercase tracking-[0.07em] text-slate-500 mb-1">{{ __('messages.Cost per hour') }}</p>
                                        <p id="clientCost" class="text-[13.5px] font-bold text-[#0B2638]">—</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Main Card: invoice lines --}}
                <div class="dt-card overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="dt-invoice-icon bg-[#E7B84B]/20 ring-1 ring-[#E7B84B]/40 !w-9 !h-9 !rounded-xl">
                                <svg class="w-5 h-5 text-[#0B2638]" fill="none" stroke="currentColor"
                                     stroke-width="1.8" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h6 class="dt-section-title">{{ __('messages.Invoice Details') }}</h6>
                                <p class="dt-section-subtitle mt-0.5">{{ __('messages.Details') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="px-5 sm:px-6 py-5">

                        {{-- Line items table --}}
                        <div class="overflow-x-auto rounded-xl border border-slate-200" style="-webkit-overflow-scrolling: touch;">
                            <table id="invoiceDetailsTable" class="w-full min-w-[640px] table-auto">
                                <thead class="bg-slate-50/80 dt-table-head">
                                    <tr class="border-b border-slate-200">
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Detail') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Quantity') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Unit Price (€)') }}</th>
                                        <th scope="col" class="px-4 py-3 text-left whitespace-nowrap">{{ __('messages.Total') }}</th>
                                        <th scope="col" class="px-4 py-3 text-center whitespace-nowrap">{{ __('messages.Action') }}</th>
                                    </tr>
                                </thead>
                                <tbody id="detailsContainer" class="divide-y divide-slate-100">
                                    {{-- Detail Row Template --}}
                                    <tr class="detail-row">
                                        <td class="px-4 py-2.5" data-label="{{ __('messages.Detail') }}">
                                            <input type="text" class="detail-input block w-full min-w-[220px] rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" placeholder="{{ __('messages.Enter detail') }}">
                                        </td>
                                        <td class="px-4 py-2.5" data-label="{{ __('messages.Quantity') }}">
                                            <input type="number" step="0.01" class="quantity-input block w-full min-w-[100px] rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" placeholder="{{ __('messages.Quantity') }}">
                                        </td>
                                        <td class="px-4 py-2.5" data-label="{{ __('messages.Unit Price (€)') }}">
                                            <input type="number" step="0.01" class="price-input block w-full min-w-[120px] rounded-xl border-slate-200 bg-white text-slate-700 shadow-sm outline-none focus:border-[#2D7080] focus:ring-2 focus:ring-[#2D7080]/25 text-sm py-2 px-3" placeholder="{{ __('messages.Unit Price (€)') }}">
                                        </td>
                                        <td class="px-4 py-2.5 whitespace-nowrap text-sm font-semibold text-[#0B2638]" data-label="{{ __('messages.Total') }}">
                                            <span class="row-total">0.00</span>€
                                        </td>
                                        <td class="px-4 py-2.5 whitespace-nowrap text-center" data-label="{{ __('messages.Action') }}">
                                            <button type="button" class="deleteRowBtn inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 transition-colors" aria-label="{{ __('messages.Remove') }}">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Add Row Button --}}
                        <div class="mt-4">
                            <button type="button" id="addRowBtn" class="dt-btn dt-btn-teal">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                </svg>
                                {{ __('messages.Add Row') }}
                            </button>
                        </div>

                        {{-- Total Amount Bar --}}
                        <div class="mt-5 bg-[#0B2638]/[0.06] rounded-xl px-4 sm:px-5 py-4 flex flex-wrap items-center justify-between gap-3">
                            <span class="text-[14px] font-bold  tracking-[0.07em] text-[#0B2638]">{{ __('messages.Total Amount:') }}</span>
                            <span id="grandTotal" class="text-xl sm:text-xl font-bold text-[#E7B84B] leading-none">0.00€</span>
                        </div>

                        {{-- Generate Button --}}
                        <div class="mt-5 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100 pt-5">
                            <button type="button" id="generateInvoiceBtn" class="dt-btn dt-btn-primary w-full sm:w-auto justify-center !px-5 !py-2.5">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                     stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                          d="M12 10v6m0 0l-3-3m3 3l3-3M3 17v2a2 2 0 002 2h14a2 2 0 002-2v-2" />
                                </svg>
                                {{ __('messages.Generate Invoice') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    @livewireScripts

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {

            // Client information panel follows the selected project
            function updateClientPanel() {
                const $select = $('#projectSelect');
                const $option = $select.find('option:selected');
                if (!$select.val()) {
                    $('#clientEmpty').removeClass('hidden');
                    $('#clientData').addClass('hidden');
                    return;
                }
                $('#clientEmpty').addClass('hidden');
                $('#clientData').removeClass('hidden');

                const client = $option.data('client');
                const tva = $option.data('tva');
                const cost = parseFloat($option.data('cost-per-hour'));

                $('#clientName').text(client ? client : @json(__('messages.No client')));
                $('#clientVat').text((tva === undefined || tva === null || tva === '') ? '—' : tva + '%');
                $('#clientCost').text(isNaN(cost) ? '—' : cost.toFixed(2) + '€');
            }
            $('#projectSelect').on('change', updateClientPanel);
            updateClientPanel();

            // Add new row button
            $('#addRowBtn').click(function() {
                const newRow = $('#detailsContainer .detail-row:first').clone();
                newRow.find('input').val('');
                newRow.find('.row-total').text('0.00');
                $('#detailsContainer').append(newRow);
                attachRowEventListeners(newRow);
            });

            // Delete row button (Event Delegation)
            $('#detailsContainer').on('click', '.deleteRowBtn', function() {
                if ($('#detailsContainer .detail-row').length > 1) {
                    $(this).closest('tr').remove();
                    calculateGrandTotal();
                }
            });

            // Calculate row total
            function calculateRowTotal(row) { // row is a <tr>
                const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const total = quantity * price;
                row.find('.row-total').text(total.toFixed(2)); // Set text of span
                calculateGrandTotal();
            }

            // Calculate grand total
            function calculateGrandTotal() {
                let grandTotal = 0;
                // Read from the text of each row-total span
                $('.row-total').each(function() {
                    grandTotal += parseFloat($(this).text()) || 0;
                });
                // Set text of the new grandTotal span
                $('#grandTotal').text(grandTotal.toFixed(2) + '€');
            }

            function attachRowEventListeners(row) {
                row.find('.quantity-input, .price-input').on('input', function() {
                    calculateRowTotal(row);
                });
            }

            // Generate invoice button
            $('#generateInvoiceBtn').click(function(e) {
                console.log("Generate Invoice Clicked");
                e.preventDefault(); // <-- Stop the button from doing nothing

                const project_id = $('#projectSelect').val(); // <-- Use project_id
                const comment = $('#comment').val();
                const details = [];

                // Collect details
                $('.detail-row').each(function() {
                    const row = $(this);
                    const description = row.find('.detail-input').val();
                    const quantity = parseFloat(row.find('.quantity-input').val());
                    const unit_price = parseFloat(row.find('.price-input').val());

                    if (description && !isNaN(quantity) && !isNaN(unit_price)) {
                        details.push({
                            description: description,
                            quantity: quantity,
                            unit_price: unit_price
                        });
                    }
                });

                // Validation
                if (details.length === 0) {
                    alert(@json(__('messages.Please add at least one valid detail row')));
                    return;
                }
                if (!project_id) {
                    alert(@json(__('messages.Please select a project')));
                    return;
                }

                // --- THIS IS THE AJAX CALL YOU NEED ---
                let ajaxUrl = '';
                @if(Auth::user()->hasRole('Admin'))
                    ajaxUrl = '{{ route("admin.invoices.detailed") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet'))
                    ajaxUrl = '{{ route("Responsable_de_projet.invoices.detailed") }}';
                @elseif(Auth::user()->hasRole('Employee'))
                    ajaxUrl = '{{ route("employee.invoices.detailed") }}';
                @elseif(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    ajaxUrl = '{{ route("Responsable_gestionnaire.invoices.detailed") }}';
                @endif

                $.ajax({
                    url: ajaxUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}', // <-- Uses the token from the <head>
                        project_id: project_id,
                        details: details,
                        comment: comment
                    },
                    success: function(response) {
                        // This worked in your old code:
                        // Replace current page content with the invoice view
                        document.open();
                        document.write(response);
                        document.close();
                    },
                    error: function(xhr) {
                        alert(@json(__('messages.Error generating invoice:')) + ' ' + (xhr.responseJSON?.error || 'Unknown error'));
                    }
                });
                // --- END OF AJAX CALL ---
            });

            // Initial setup for the first row
            attachRowEventListeners($('.detail-row:first'));
            // Initial calculation
            $('.detail-row').each(function() { calculateRowTotal($(this)); });
            // Initial Grand Total Calculation
            calculateGrandTotal();

        }); // End document ready
    </script>

</body>
</html>
