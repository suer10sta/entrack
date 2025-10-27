<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Generate Invoice - BizManager</title> {{-- Updated title --}}
    @vite('resources/css/app.css')
    @livewireStyles
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    {{-- Custom Select2 Tailwind theme (Optional but recommended) --}}
    {{-- You might need to find or create a Select2 theme compatible with Tailwind --}}
    {{-- <link rel="stylesheet" href="path/to/select2-tailwind.css"> --}}
    {{-- @include('dashboard_layouts.head') --}}

    {{-- Remove internal styles, let Tailwind handle it --}}
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

            @php
                // Keep PHP variables if needed globally, otherwise move closer to usage
                // $year = request()->query('year', date('Y'));
                // $month = request()->query('month', date('m'));
            @endphp

            {{-- Container for Invoice Forms --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                 {{-- Session Message Alert --}}
                @if(session('message'))
                <div class="m-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Error!</strong>
                    <span class="block sm:inline">{{ session('message') }}</span>
                    <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
                </div>
                @endif

                {{-- Full Invoice Section --}}
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Full Invoice</h6>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-4">
                            <label for="projectFilter1" class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                            {{-- Add 'select2-tailwind' class if using a theme --}}
                            <select id="projectFilter1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}"
                                        data-cost-per-hour="{{ $project->cost_per_hour }}"
                                        data-tva="{{ $project->tva }}">
                                    @if(preg_match('/^(\d+)(.*)/', $project->name, $matches))
                                        {{-- Keep strong tag for Select2 formatting --}}
                                        <strong>{{ $matches[1] }}</strong>{{ $matches[2] }}
                                    @else
                                        {{ strtoupper($project->name) }}
                                    @endif
                                    -
                                    @if($project->budget)
                                        {{ $project->budget - $project->amount_billed }}€
                                    @else
                                        <span class="text-red-600">no budget</span> {{-- Tailwind color --}}
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="hoursInput" class="block text-sm font-medium text-gray-700 mb-1">Hours (h:mm)</label>
                            <input type="text" id="hoursInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="e.g., 5:30">
                        </div>
                        <div class="md:col-span-2">
                            <label for="manualBudgetInput" class="block text-sm font-medium text-gray-700 mb-1">Manual Budget (€)</label>
                            <input type="number" step="0.01" id="manualBudgetInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" placeholder="Manual budget">
                        </div>
                         <div class="md:col-span-2">
                             <label for="FullInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                             <textarea name="FullInvComment" id="FullInvComment" placeholder="Full Invoice Comment" rows="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                         </div>
                        <div class="md:col-span-2">
                            <a id="downloadInvoiceBtn" href="#" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg inline-flex justify-center items-center">View Invoice</a>
                        </div>
                    </div>
                </div>

                {{-- Period Invoice Section --}}
                <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Period Invoice</h6>
                     <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-3">
                            <label for="projectFilter2" class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                            {{-- Add 'select2-tailwind' class if using a theme --}}
                            <select id="projectFilter2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}">
                                     {{ strtoupper($project->name) }} -
                                     @if($project->budget)
                                         {{ $project->budget - $project->amount_billed }}€
                                     @else
                                         <span class="text-red-600">no budget</span>
                                     @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="startDateInput" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            <input type="date" id="startDateInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label for="endDateInput" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            <input type="date" id="endDateInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                         <div class="md:col-span-3">
                             <label for="PeriodInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                             <textarea name="PeriodInvComment" id="PeriodInvComment" placeholder="Period Invoice Comment" rows="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                         </div>
                        <div class="md:col-span-2">
                            <a id="downloadDurationInvoiceBtn" href="#" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg inline-flex justify-center items-center">View Invoice</a>
                        </div>
                    </div>
                </div>

                {{-- Partial Invoice Section --}}
                 <div class="p-6 border-b border-gray-200">
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Partial Invoice (Based on Quote/Devis)</h6>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-4">
                            <label for="devisSelect3" class="block text-sm font-medium text-gray-700 mb-1">Select Quote (Devis)</label>
                             {{-- Add 'select2-tailwind' class if using a theme --}}
                            <select id="devisSelect3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Quote</option>
                                {{-- Options loaded by JS --}}
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="partialAmountInput" class="block text-sm font-medium text-gray-700 mb-1">Amount (€)</label>
                            <input type="number" step="0.0001" id="partialAmountInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                         <div class="md:col-span-3">
                             <label for="PartialInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                             <textarea name="PartialInvComment" id="PartialInvComment" placeholder="Partial Invoice Comment" rows="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                         </div>
                        <div class="md:col-span-3">
                            <a id="downloadPartialInvoiceBtn" href="#" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg inline-flex justify-center items-center">View Invoice</a>
                        </div>
                    </div>
                </div>

                {{-- Percentage Invoice Section --}}
                <div class="p-6"> {{-- No bottom border on the last section --}}
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Percentage Invoice (Based on Quote/Devis)</h6>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-4">
                            <label for="devisSelect4" class="block text-sm font-medium text-gray-700 mb-1">Select Quote (Devis)</label>
                             {{-- Add 'select2-tailwind' class if using a theme --}}
                            <select id="devisSelect4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Select Quote</option>
                                 {{-- Options loaded by JS --}}
                            </select>
                        </div>
                        <div class="md:col-span-2">
                            <label for="percentageInput" class="block text-sm font-medium text-gray-700 mb-1">Percentage (%)</label>
                            <input type="number" step="0.01" id="percentageInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        </div>
                         <div class="md:col-span-3">
                             <label for="PercentageInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                             <textarea name="PercentageInvComment" id="PercentageInvComment" placeholder="Percentage Invoice Comment" rows="1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"></textarea>
                         </div>
                        <div class="md:col-span-3">
                            <a id="downloadPercentageInvoiceBtn" href="#" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg inline-flex justify-center items-center">View Invoice</a>
                        </div>
                    </div>
                </div>

            </div> {{-- End Card --}}
        </main>
    </div> {{-- End Main Wrapper --}}

    @livewireScripts
    {{-- @include('dashboard_layouts.scripts') --}}

    {{-- Keep JS libraries --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- Keep existing scripts --}}
    <script>
        // Keep script for projectFilter1 change, hoursInput formatting, manualBudgetInput logic, downloadInvoiceBtn click
        $(document).ready(function() {
             // Initialize Select2 first
             // Use a class selector if you apply a Tailwind theme, e.g., $('.select2-tailwind')
            $('#projectFilter1, #projectFilter2, #devisSelect3, #devisSelect4').select2({
                 // Apply Tailwind theme settings here if available
                 // theme: "tailwind", 
                 // width: '100%', // Ensure it takes full width
                 templateResult: formatProjectOrDevis, // Reuse formatting function if needed
                 templateSelection: formatProjectOrDevis
            });

            // Keep original logic, ensure selectors are correct
            $('#projectFilter1').change(function() {
                if ($(this).val()) {
                    $('#hoursInput').prop('disabled', false);
                    $('#manualBudgetInput').prop('disabled', false);
                } else {
                    $('#hoursInput').prop('disabled', true).val(''); // Clear value on disable
                    $('#manualBudgetInput').prop('disabled', true).val(''); // Clear value on disable
                }
                 $('#hoursInput, #manualBudgetInput').trigger('input'); // Trigger input event to re-evaluate disabled state
            }).trigger('change'); // Trigger on load

            $('#hoursInput').on('input', function() {
                if ($(this).val()) {
                    $('#manualBudgetInput').prop('disabled', true);
                } else if ($('#projectFilter1').val()) { // Only enable if project selected
                    $('#manualBudgetInput').prop('disabled', false);
                }
                // Keep formatting logic
                var value = $(this).val().replace(/[^0-9:]/g, ''); 
                if (value.length > 0 && !value.includes(':')) {
                    if (value.length > 2) value = value.slice(0, -2) + ':' + value.slice(-2);
                    else if (value.length === 2) value = value + ':'; // Add colon automatically
                } else if (value.includes(':')) {
                    var parts = value.split(':');
                    if (parts[1] && parts[1].length > 2) parts[1] = parts[1].substring(0, 2); // Limit minutes
                    value = parts[0] + ':' + (parts[1] || '');
                }
                $(this).val(value);

            });

             $('#manualBudgetInput').on('input', function() {
                 if ($(this).val()) {
                     $('#hoursInput').prop('disabled', true);
                 } else if ($('#projectFilter1').val()) { // Only enable if project selected
                     $('#hoursInput').prop('disabled', false);
                 }
            });

            $('#downloadInvoiceBtn').click(function(e) { /* ... Keep original logic, adjust route definition ... */ });
            $('#downloadDurationInvoiceBtn').click(function(e) { /* ... Keep original logic, adjust route definition ... */ });
            $('#downloadPartialInvoiceBtn').click(function(e) { /* ... Keep original logic, adjust route definition ... */ });
            $('#downloadPercentageInvoiceBtn').click(function(e) { /* ... Keep original logic, adjust route definition ... */ });

             // Keep Select2 formatting function (adjust as needed)
            function formatProjectOrDevis(item) {
                 if (!item.id) { return item.text; }
                 var $item = $('<span>' + item.text + '</span>'); // Wrap text
                 var text = $item.text();
                 var match = text.match(/^(\d+)(.*)$/);
                 if (match) {
                     return $('<span><strong>' + match[1] + '</strong>' + match[2] + '</span>');
                 }
                 // Handle Devis formatting if different
                 if (text.startsWith('Devis:')) {
                    // Apply specific formatting for Devis options if needed
                 }
                 return text;
            }
            
            // Keep Devis loading functions, ensure URLs are correct
            function loadAllDevis(selectId) { /* ... Keep original logic ... */ }
            function loadDevisComment(devisId, commentId) { /* ... Keep original logic ... */ }
            loadAllDevis('#devisSelect3');
            loadAllDevis('#devisSelect4');
            $('#devisSelect3').change(function() { loadDevisComment($(this).val(), '#PartialInvComment'); });
            $('#devisSelect4').change(function() { loadDevisComment($(this).val(), '#PercentageInvComment'); });

        });
    </script>

</body>
</html>