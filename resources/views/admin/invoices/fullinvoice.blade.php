<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Generate Invoice - BizManager</title> {{-- Updated title --}}

    @vite('resources/css/app.css')

    @livewireStyles

    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- @include('dashboard_layouts.head') --}}



    {{-- Custom styles to make <select> match <input> --}}

    <style>

        /* 1. Remove native appearance and add custom dropdown arrow */

        select.form-select-custom {

            -webkit-appearance: none;

            -moz-appearance: none;

            appearance: none;

            /* Tailwind's chevron-down icon as an SVG */

            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");

            background-position: right 0.5rem center;

            background-repeat: no-repeat;

            background-size: 1.5em 1.5em;

            padding-right: 2.5rem; /* Make space for the arrow */

        }



        /* 2. Style the placeholder <option> to look like input placeholder text */

        /* This works when the select has 'required' and the value is "" */

        select:invalid {

            color: #9ca3af; /* Tailwind's gray-400 */

        }

        /* Ensure all other options are the default color */

        option {

            color: #111827; /* Tailwind's gray-900 */

        }

    </style>

</head>

<body class="bg-gray-20 flex h-screen overflow-hidden font-sans">

    {{-- Sidebar Component (Unchanged) --}}
    <x-dashboard.sidebar />
    {{-- Main Wrapper (Navbar + Content) (Unchanged) --}}

    <div class="flex flex-col flex-1 overflow-y-auto">
        {{-- Navbar Component (Unchanged) --}}
        <x-dashboard.navbar />

        {{-- Main Content Area --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-8">

            {{-- Session Message Alert (Moved to top of main area) --}}
            @if(session('message'))
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('message') }}</span>
                <button type="button" onclick="this.parentElement.style.display='none';" class="absolute top-0 bottom-0 right-0 px-4 py-3 font-bold text-xl" aria-label="Close">&times;</button>
            </div>
            @endif
            {{-- Full Invoice Section (New Card) --}}
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="p-6">
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Full Invoice</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Project Filter --}}
                        <div class="md:col-span-1">
                            <label for="projectFilter1" class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                            <select id="projectFilter1" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm form-select-custom py-2 px-3" required>
                                <option value="">Select Project</option>
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
                                        {{ $project->budget - $project->amount_billed }}€
                                    @else
                                        no budget
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="md:col-span-1">
                            <label for="hoursInput" class="block text-sm font-medium text-gray-700 mb-1">Hours (h:mm)</label>
                            <input type="text" id="hoursInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" placeholder="Example: 5:30">
                        </div>
                        <div class="md:col-span-1">
                            <label for="manualBudgetInput" class="block text-sm font-medium text-gray-700 mb-1">Manual Budget (€)</label>
                            <input type="number" step="0.01" id="manualBudgetInput" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3" placeholder="Manual budget">
                        </div>
                        <div class="md:col-span-1">
                            <label for="FullInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                            <textarea name="FullInvComment" id="FullInvComment" placeholder="Add any additional notes..." rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a id="downloadInvoiceBtn" href="#" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">View Invoice</a>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="p-6">
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Period Invoice</h6>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Project Filter --}}
                        <div class="md:col-span-1">
                            <label for="projectFilter2" class="block text-sm font-medium text-gray-700 mb-1">Select Project</label>
                            {{-- Added py-2 px-3 for height --}}
                            <select id="projectFilter2" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm form-select-custom py-2 px-3" required>
                                <option value="">Select Project</option>
                                @foreach($projects as $project)
                                <option value="{{ $project->id }}">
                                    {{ strtoupper($project->name) }} -
                                    @if($project->budget)
                                        {{ $project->budget - $project->amount_billed }}€
                                    @else
                                        no budget
                                    @endif
                                </option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Start Date --}}
                        <div class="md:col-span-1">
                            <label for="startDateInput" class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                            {{-- Added py-2 px-3 for height --}}
                            <input type="date" id="startDateInput" placeholder="jj/mm/aaaa" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                        </div>
                        {{-- End Date --}}
                        <div class="md:col-span-1">
                            <label for="endDateInput" class="block text-sm font-medium text-gray-700 mb-1">End Date</label>
                            {{-- Added py-2 px-3 for height --}}
                            <input type="date" id="endDateInput" placeholder="jj/mm/aaaa" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                        </div>
                        {{-- Comment (Full Width) --}}
                        <div class="md:col-span-3">
                            <label for="PeriodInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                            {{-- Added py-2 px-3 for height --}}
                            <textarea name="PeriodInvComment" id="PeriodInvComment" placeholder="Add any additional notes..." rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a id="downloadDurationInvoiceBtn" href="#" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">View Invoice</a>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="p-6">
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Partial Invoice</h6>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Devis Select --}}
                        <div class="md:col-span-1">
                            <label for="devisSelect3" class="block text-sm font-medium text-gray-700 mb-1">Select Quote</label>
                            {{-- Added py-2 px-3 for height --}}
                            <select id="devisSelect3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm form-select-custom py-2 px-3" required>
                                <option value="">Select Quote</option>
                                {{-- Options loaded by JS --}}
                            </select>
                        </div>
                        {{-- Amount --}}
                        <div class="md:col-span-1">
                            <label for="partialAmountInput" class="block text-sm font-medium text-gray-700 mb-1">Amount (€)</label>
                            {{-- Added py-2 px-3 for height --}}
                            <input type="number" step="0.0001" id="partialAmountInput" placeholder="Amount" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">

                        </div>

                        {{-- Comment (Full Width) --}}
                        <div class="md:col-span-2">
                            <label for="PartialInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                            {{-- Added py-2 px-3 for height --}}
                            <textarea name="PartialInvComment" id="PartialInvComment" placeholder="Add any additional notes..." rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a id="downloadPartialInvoiceBtn" href="#" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">View Invoice</a>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <h6 class="text-lg font-semibold text-gray-900 mb-4">Percentage Invoice </h6>
                    <div classs="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Devis Select --}}
                        <div class="md:col-span-1">
                            <label for="devisSelect4" class="block text-sm font-medium text-gray-700 mb-1">Select Quote </label>
                            <select id="devisSelect4" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm form-select-custom py-2 px-3" required>
                                <option value="">Select Quote</option>
                                {{-- Options loaded by JS --}}
                            </select>
                        </div>
                        {{-- Percentage --}}
                        <div class="md:col-span-1">
                            <label for="percentageInput" class="block text-sm font-medium text-gray-700 mb-1">Percentage (%)</label>
                            {{-- Added py-2 px-3 for height --}}
                            <input type="number" step="0.01" id="percentageInput" placeholder="Percentage" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3">
                        </div>
                        <div class="md:col-span-2">
                            <label for="PercentageInvComment" class="block text-sm font-medium text-gray-700 mb-1">Comment</label>
                            <textarea name="PercentageInvComment" id="PercentageInvComment" placeholder="Add any additional notes..." rows="3" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm py-2 px-3"></textarea>
                        </div>
                    </div>
                    <div class="mt-6">
                        <a id="downloadPercentageInvoiceBtn" href="#" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium px-4 py-2 rounded-lg">View Invoice</a>
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
                    alert('This project has no TVA. Please add TVA to the project before generating an invoice.');
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
                            alert('Please enter a valid time format (e.g., 5:30 or 5h:30m)');
                            return;
                        }
                    } else {
                        alert('Please enter time in format: h:mm (example: 5:30)');
                        return;
                    }
                }

                if (!projectId) {
                    alert('Please select a project.');
                    return;
                }

                if (!hours && !manualBudget) {
                    alert('Please enter either hours or manual budget.');
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
                    alert('Please select a project and enter start/end dates.');
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
                    alert('Please select a devis and enter the Amount.');
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
                    alert('Please select a devis and enter the percentage.');
                }
            });



            function loadAllDevis(selectId) {
                $.ajax({
                    url: '/admin/get-all-devis',
                    type: 'GET',
                    success: function(response) {
                        var devisSelect = $(selectId);
                        devisSelect.empty();
                        devisSelect.append('<option value="">Select Devis</option>');
                        response.devis.forEach(function(devis) {
                            const projectPart = devis.project_name ? ` | Project: ${devis.project_name}` : '';
                            const unbilledPart = (typeof devis.unbilled !== 'undefined') ? ` | Unbilled: €${Number(devis.unbilled).toFixed(2)}` : '';
                            devisSelect.append(`<option value="${devis.id}">Devis: ${response.prefix}-${devis.number}${projectPart}${unbilledPart}</option>`);
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