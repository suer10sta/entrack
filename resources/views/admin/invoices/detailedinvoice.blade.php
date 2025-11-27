<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detailed Invoice - DailyTrac</title> 
    @vite('resources/css/app.css')
    @livewireStyles
    
    {{-- Custom styles to make <select> match <input> --}}
    <style>
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
    </style>
</head>
<body class="bg-gray-50 flex h-screen overflow-hidden">

    {{-- Sidebar Component --}}
    <x-dashboard.sidebar />

    {{-- Main Wrapper (Navbar + Content) --}}
    <div class="flex flex-col flex-1 overflow-y-auto">

        {{-- Navbar Component --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">
            
            <div class="w-full"> 
                @if(session('message'))
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline">{{ session('message') }}</span>
                        <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
                    </div>
                @endif
                
                {{-- Main Card --}}
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="flex justify-between items-center p-6 pb-0 border-b border-gray-200">
                        <h6 class="text-lg font-semibold text-gray-900">Detailed Invoice Generator</h6>
                    </div>
                    
                    <div class="p-6">
                        {{-- Project and Comment Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="md:col-span-1">
                                <label for="projectSelect" class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                                <select id="projectSelect" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3 form-select-custom" required>
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                    <option value="{{ $project->id }}" data-cost-per-hour="{{ $project->cost_per_hour }}" data-tva="{{ $project->tva }}">
                                        @if(preg_match('/^(\d+)(.*)/', $project->name, $matches)){{ $matches[1] }}{{ $matches[2] }}@else{{ strtoupper($project->name) }}@endif
                                        - @if($project->budget){{ $project->budget - $project->amount_billed }}€ @else<span class="text-red-600">no budget</span>@endif
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                                <textarea id="comment" name="comment" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" rows="3" placeholder="Add any additional notes or comments here..."></textarea>
                            </div>
                        </div>

                        {{-- Details Section --}}
                        <div class="mt-4">
                            <label class="block text-base font-semibold text-gray-800 mb-2">Invoice Details</label>
                            
                            {{-- NEW Table Structure --}}
                            <div class="overflow-x-auto rounded-lg border border-gray-200">
                                <table class="w-full min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3 px-4 text-left text-sm font-semibold text-gray-700 w-5/12">Detail</th>
                                            <th scope="col" class="py-3 px-4 text-left text-sm font-semibold text-gray-700 w-2/12">Quantity</th>
                                            <th scope="col" class="py-3 px-4 text-left text-sm font-semibold text-gray-700 w-2/12">Unit Price (€)</th>
                                            <th scope="col" class="py-3 px-4 text-left text-sm font-semibold text-gray-700 w-2/12">Total</th>
                                            <th scope="col" class="py-3 px-4 text-center text-sm font-semibold text-gray-700 w-1/12">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="detailsContainer" class="bg-white divide-y divide-gray-200">
                                        {{-- Detail Row Template --}}
                                        <tr class="detail-row">
                                            <td class="p-2 px-4">
                                                <input type="text" class="detail-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" placeholder="Enter detail">
                                            </td>
                                            <td class="p-2 px-4">
                                                <input type="number" step="0.01" class="quantity-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" placeholder="Quantity">
                                            </td>
                                            <td class="p-2 px-4">
                                                <input type="number" step="0.01" class="price-input block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" placeholder="Unit price">
                                            </td>
                                            <td class="p-2 px-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                {{-- Total is now a span, not an input --}}
                                                <span class="row-total">0.00</span>€
                                            </td>
                                            <td class="p-2 px-4 whitespace-nowrap text-center">
                                                {{-- Delete button is now an icon --}}
                                                <button type="button" class="deleteRowBtn text-red-600 hover:text-red-800">
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
                                <button type="button" id="addRowBtn" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-medium px-4 py-2 rounded-lg text-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd" />
                                    </svg>
                                    Add Row
                                </button>
                            </div>

                            {{-- NEW Total Amount Bar --}}
                            <div class="mt-6 bg-gray-50 border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                <span class="text-lg font-semibold text-gray-800">Total Amount:</span>
                                <span id="grandTotal" class="text-2xl font-bold text-blue-600">0.00€</span>
                            </div>

                            {{-- Generate Button --}}
                            <div class="mt-6 border-t pt-6">
                                <button type="button" id="generateInvoiceBtn" class="bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2.5 rounded-lg">Generate Invoice</button>
                            </div>
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
                    alert('Please add at least one valid detail row');
                    return;
                }
                if (!project_id) {
                    alert('Please select a project');
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
                        alert('Error generating invoice: ' + (xhr.responseJSON?.error || 'Unknown error'));
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

