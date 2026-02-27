<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DalyTrac Dashboard</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">
    {{-- Sidebar --}}
    @if(Auth::user()->hasRole('Admin'))
        <x-dashboard.sidebar />
    @else
        <x-employees.sidebar />
    @endif
    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar --}}
        <x-dashboard.navbar />

        {{-- Main Content --}}

        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
            <div class="max-w-5xl mx-auto">
                
                {{-- Session Message (Tailwind Styled) --}}
                @if(session('message'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">{{ __('messages.Success') }}!</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="{{ __('messages.Close') }}">&times;</button>
                </div>
                @endif

                {{-- Determine Routes (Simplified Logic) --}}
                @php
                    $updateRoute = '';
                    $cancelRoute = '';
                    if (Auth::user()->hasRole('Admin')) {
                        $updateRoute = route('admin.invoices.updateDetailedInvoice', $invoice->id);
                        $cancelRoute = route('admin.invoices.invoicehistory');
                    } elseif (Auth::user()->hasRole('Responsable de projet')) {
                        $updateRoute = route('Responsable_de_projet.invoices.updateDetailedInvoice', $invoice->id);
                        $cancelRoute = route('Responsable_de_projet.invoices.invoicehistory');
                    } elseif (Auth::user()->hasRole('Employee')) {
                        $updateRoute = route('employee.invoices.updateDetailedInvoice', $invoice->id);
                        $cancelRoute = route('employee.invoices.invoicehistory');
                    } elseif (Auth::user()->hasRole('Responsable de projet et gestionnaire')) {
                        $updateRoute = route('Responsable_gestionnaire.invoices.updateDetailedInvoice', $invoice->id);
                        $cancelRoute = route('Responsable_gestionnaire.invoices.invoicehistory');
                    }
                @endphp

                {{-- Form Card (Tailwind Styled) --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <form action="{{ $updateRoute }}" method="POST">
                        @csrf
                        <input type="hidden" name="project_id" value="{{ $invoice->project_id }}">

                        {{-- Card Header --}}
                        <div class="p-6 border-b border-gray-200">
                            <h2 class="text-lg font-semibold text-gray-900">
                                {{ __('messages.Edit Detailed Invoice') }}
                            </h2>
                        </div>

                        {{-- Card Body (Form Fields) --}}
                        <div class="p-6 space-y-6">
                            
                            {{-- Comment --}}
                            <div>
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">{{ __('messages.Comment') }}</label>
                                <textarea name="comment" id="comment" rows="4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ $invoice->comment }}</textarea>
                            </div>

                            {{-- Details Header --}}
                            <div class="grid grid-cols-10 gap-4 mb-2">
                                <div class="col-span-10 md:col-span-4">
                                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Detail') }}</label>
                                </div>
                                <div class="col-span-5 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Quantity') }}</label>
                                </div>
                                <div class="col-span-5 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Unit Price') }} (€)</label>
                                </div>
                                <div class="col-span-10 md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.Total') }}</label>
                                </div>
                            </div>
                            
                            {{-- Dynamic Details Container --}}
                            <div id="detailsContainer" class="space-y-3">
                                @foreach(json_decode($invoice->details, true) as $index => $detail)
                                <div class="grid grid-cols-10 gap-4 items-center detail-row">
                                    <div class="col-span-10 md:col-span-4">
                                        <input type="text" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm detail-input" name="details[{{$index}}][description]" value="{{ $detail['description'] }}" required>
                                    </div>
                                    <div class="col-span-5 md:col-span-2">
                                        <input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm quantity-input" name="details[{{$index}}][quantity]" value="{{ $detail['quantity'] }}" required>
                                    </div>
                                    <div class="col-span-5 md:col-span-2">
                                        <input type="number" step="0.01" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm price-input" name="details[{{$index}}][unit_price]" value="{{ $detail['unit_price'] }}" required>
                                    </div>
                                    <div class="col-span-10 md:col-span-2">
                                        <input type="number" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm bg-gray-50 total-input" value="{{ $detail['quantity'] * $detail['unit_price'] }}" readonly>
                                    </div>
                                </div>
                                @endforeach
                            </div>

                            {{-- Add/Delete Row Buttons --}}
                            <div class="flex items-center gap-3">
                                <button type="button" id="addRowBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm shadow-sm">
                                    {{ __('messages.Add Row') }}
                                </button>
                                <button type="button" id="deleteRowBtn" class="inline-flex items-center bg-red-600 hover:bg-red-700 text-white font-medium px-3 py-1.5 rounded-lg text-sm shadow-sm">
                                    {{ __('messages.Delete Row') }}
                                </button>
                            </div>

                            {{-- Grand Total --}}
                            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-800">{{ __('messages.Total Amount (HTVA)') }}:</span>
                                <span id="grandTotal" class="text-2xl font-bold text-blue-600">0.00€</span>
                            </div>

                        </div>

                        {{-- Card Footer (Buttons) --}}
                        <div class="p-6 bg-gray-50 border-t border-gray-200 flex items-center gap-3">
                            <button type="submit" class="inline-flex justify-center bg-green-600 hover:bg-green-700 text-white font-medium px-4 py-2 rounded-lg text-sm shadow-sm">
                                {{ __('messages.Update Invoice') }}
                            </button>
                            <a href="{{ $cancelRoute }}" class="inline-flex justify-center bg-white hover:bg-gray-50 text-gray-700 font-medium px-4 py-2 rounded-lg text-sm border border-gray-300 shadow-sm">
                                {{ __('messages.Cancel') }}
                            </a>
                        </div>
                        
                    </form>
                </div>
            </div>
        </main>
    </div>
    @livewireScripts

    <script>
        $(document).ready(function() {
            function calculateRowTotal(row) {
                const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const total = quantity * price;
                row.find('.total-input').val(total.toFixed(2));
                calculateGrandTotal();
            }

            function calculateGrandTotal() {
                let grandTotal = 0;
                $('.total-input').each(function() {
                    grandTotal += parseFloat($(this).val()) || 0;
                });
                $('#grandTotal').text(grandTotal.toFixed(2) + '€'); // Added € symbol for consistency
            }

            function attachRowEventListeners(row) {
                row.find('.quantity-input, .price-input').on('input', function() {
                    calculateRowTotal(row);
                });
            }

            $('#addRowBtn').click(function() {
                // Find the last row to clone, or the first if it's the only one
                const lastRow = $('.detail-row:last');
                const newRow = lastRow.clone();
                const newIndex = $('.detail-row').length;
                
                newRow.find('input').val(''); // Clear all input values
                newRow.find('input[name^="details"]').each(function() {
                    const oldName = $(this).attr('name');
                    // Replace the index [0] or [1] etc. with the new index
                    $(this).attr('name', oldName.replace(/\[\d+\]/, '[' + newIndex + ']'));
                });
                
                // Use .after() on the last row to append, or .append() to the container
                lastRow.after(newRow); 
                attachRowEventListeners(newRow);
                calculateRowTotal(newRow); // Calculate total for the new row (which will be 0)
            });

            $('#deleteRowBtn').click(function() {
                if ($('.detail-row').length > 1) {
                    $('.detail-row:last').remove();
                    calculateGrandTotal();
                }
            });

            // Attach listeners to all existing rows on page load
            $('.detail-row').each(function() {
                attachRowEventListeners($(this));
            });

            // Calculate the grand total on page load
            calculateGrandTotal();
        });
    </script>
</body>
</html>
