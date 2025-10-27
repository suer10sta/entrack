<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clients - BizManager</title> 
    @vite('resources/css/app.css')
    @livewireStyles
    
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
            {{-- Keep Bootstrap container-fluid structure if needed for spacing --}}
            {{-- <div class="container-fluid py-4"> --}}
                <div class="row mt-3"> {{-- Keep Bootstrap row --}}
                    <div class="col-12"> {{-- Keep Bootstrap col --}}
                        @if(session('message'))
                            {{-- Tailwind Alert --}}
                            <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                <strong class="font-bold">Error!</strong>
                                <span class="block sm:inline">{{ session('message') }}</span>
                                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
                            </div>
                        @endif
                        {{-- Keep Bootstrap card, style inside --}}
                        <div class="card bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                            <div class="card-header flex justify-between items-center p-3 pb-0 border-b border-gray-200">
                                <h6 class="text-lg font-semibold text-gray-900">Detailed Invoice Generator</h6>
                            </div>
                            {{-- Keep Bootstrap row for layout --}}
                            <div class="row p-4">
                                {{-- Project and Comment Row --}}
                                <div class="row mb-4 items-end">
                                    <div class="col-md-4">
                                        <label for="projectSelect" class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                                        {{-- Apply Tailwind form classes --}}
                                        <select id="projectSelect" class="form-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                                            <option value="">Select Project</option>
                                            @foreach($projects as $project)
                                            <option value="{{ $project->id }}" data-cost-per-hour="{{ $project->cost_per_hour }}" data-tva="{{ $project->tva }}">
                                                @if(preg_match('/^(\d+)(.*)/', $project->name, $matches))<strong>{{ $matches[1] }}</strong>{{ $matches[2] }}@else{{ strtoupper($project->name) }}@endif
                                                - @if($project->budget){{ $project->budget - $project->amount_billed }}€ @else<span class="text-red-600">no budget</span>@endif
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8">
                                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                                        {{-- Apply Tailwind form classes --}}
                                        <textarea id="comment" name="comment" class="form-control block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" rows="3" placeholder="Add any additional notes or comments here..."></textarea>
                                    </div>
                                </div>

                                {{-- Details Section --}}
                                <div class="col-md-12 mt-4">
                                    <label class="block text-base font-semibold text-gray-800 mb-2">Invoice Details</label>
                                    <div id="detailsContainer" class="space-y-3"> {{-- Use space-y for vertical spacing --}}
                                        {{-- Detail Row Template --}}
                                        <div class="row detail-row items-end"> {{-- Keep Bootstrap row --}}
                                            <div class="col-md-5"> {{-- Adjusted column size --}}
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Detail</label>
                                                <input type="text" class="form-control detail-input block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-1.5 px-2" placeholder="Enter detail">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                                <input type="number" step="0.01" class="form-control quantity-input block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-1.5 px-2" placeholder="Qty">
                                            </div>
                                            <div class="col-md-2">
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (€)</label>
                                                <input type="number" step="0.01" class="form-control price-input block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-1.5 px-2" placeholder="Price">
                                            </div>
                                            <div class="col-md-3"> {{-- Adjusted column size --}}
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Total (€)</label>
                                                {{-- Readonly input with Tailwind styles --}}
                                                <input type="number" class="form-control total-input block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm sm:text-sm py-1.5 px-2" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Add/Delete Row Buttons --}}
                                    <div class="mt-3 space-x-2"> {{-- Use space-x for horizontal spacing --}}
                                        {{-- Apply Tailwind button classes --}}
                                        <button type="button" id="addRowBtn" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-3 py-1 rounded-md">Add Row</button>
                                        <button type="button" id="deleteRowBtn" class="bg-red-600 hover:bg-red-700 text-white text-xs font-medium px-3 py-1 rounded-md">Delete Row</button>
                                    </div>

                                    {{-- Grand Total --}}
                                    <div class="col-md-12 mt-4 text-right"> {{-- Keep Bootstrap text-right --}}
                                         <h5 class="text-lg font-semibold text-gray-800">Total Amount: <span id="grandTotal" class="text-green-600">0.00</span>€</h5>
                                    </div>

                                    {{-- Generate Button --}}
                                    <div class="col-md-12 mt-4">
                                        {{-- Apply Tailwind button classes --}}
                                        <button type="button" id="generateInvoiceBtn" class="bg-green-600 hover:bg-green-700 text-white font-medium px-5 py-2.5 rounded-lg">Generate Invoice</button>
                                    </div>
                                </div>
                            </div> {{-- End row p-4 --}}
                        </div> {{-- End Card --}}
                    </div> {{-- End Col --}}
                </div> {{-- End Row --}}
            {{-- </div> --}} {{-- End Container --}}
        </main>
    </div> {{-- End Main Wrapper --}}

    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Keep JS libraries --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Keep existing scripts --}}
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#projectSelect').select2({ // Target only the project select
                 width: '100%', // Ensure it takes full width
                 templateResult: formatProjectSelection, // Use the formatting function
                 templateSelection: formatProjectSelection
            });

            // Add new row button
            $('#addRowBtn').click(function() {
                 const newRow = $('.detail-row:first').clone();
                 newRow.find('input').val(''); // Clear inputs
                 newRow.find('.total-input').val('0.00'); // Reset total
                 $('#detailsContainer').append(newRow);
                 attachRowEventListeners(newRow); // Attach listeners to new row
            });

            // Delete row button
            $('#deleteRowBtn').click(function() {
                 if ($('.detail-row').length > 1) {
                     $('.detail-row:last').remove();
                     calculateGrandTotal(); // Recalculate total after deletion
                 }
            });

            // Calculate row total
            function calculateRowTotal(row) {
                 const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                 const price = parseFloat(row.find('.price-input').val()) || 0;
                 const total = quantity * price;
                 row.find('.total-input').val(total.toFixed(2));
                 calculateGrandTotal(); // Recalculate grand total whenever a row total changes
            }

            // Calculate grand total
            function calculateGrandTotal() {
                 let grandTotal = 0;
                 $('.total-input').each(function() {
                     grandTotal += parseFloat($(this).val()) || 0;
                 });
                 $('#grandTotal').text(grandTotal.toFixed(2));
            }

            // Attach event listeners to quantity/price inputs
            function attachRowEventListeners(row) {
                 row.find('.quantity-input, .price-input').on('input', function() {
                     calculateRowTotal(row);
                 });
            }

            // Generate invoice button logic (kept as is)
            $('#generateInvoiceBtn').click(function(e) { /* ... Your existing AJAX logic ... */ });

            // Initial setup for the first row
            attachRowEventListeners($('.detail-row:first'));
            // Initial calculation
            $('.detail-row').each(function() { calculateRowTotal($(this)); });

             // Select2 formatting function (kept as is)
             function formatProjectSelection(project) { /* ... Your existing formatting logic ... */ }

        }); // End document ready
    </script>

</body>
</html>