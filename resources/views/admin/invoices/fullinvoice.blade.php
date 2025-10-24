<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
  a {
    color: blue;
}
</style>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<body class="g-sidenav-show bg-gray-100">
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NKDMSK6" height="0" width="0"
            style="display: none; visibility: hidden"></iframe></noscript>
    <div class="min-height-300 bg-primary position-absolute w-100"></div>

    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif

    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl z-index-sticky"
            id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                @include('dashboard_layouts.navbar')
            </div>
        </nav>
        <div class="container-fluid py-4">
            <div class="row mt-3">
                <div class="col-12">
                    @if(session('message'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="color: white; font-weight: bold;">
                            {{ session('message') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">X</button>
                        </div>
                    @endif
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center p-3 pb-0">
                            <h6>Full Invoice</h6>
                        </div>
                        <div class="row p-4 align-items-end">
                            <div class="col-md-3">
                                <label for="projectFilter1">Select Project</label>
                                <select id="projectFilter1" class="form-control">
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}" 
                                                data-cost-per-hour="{{ $project->cost_per_hour }}"
                                                data-tva="{{ $project->tva }}">
                                            @if(preg_match('/^(\d+)(.*)/', $project->name, $matches))
                                                <strong>{{ $matches[1] }}</strong>{{ $matches[2] }}
                                            @else
                                                {{ strtoupper($project->name) }}
                                            @endif
                                            - 
                                            @if($project->budget) 
                                                {{ $project->budget - $project->amount_billed }}€ 
                                            @else 
                                                <span style="color: red;">no project budget inserted</span>
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="hoursInput">Hours (format: h:mm)</label>
                                <input type="text" id="hoursInput" class="form-control" placeholder="Example: 5:30">
                            </div>
                            <div class="col-md-2">
                                <label for="manualBudgetInput">Manual Budget (€)</label>
                                <input type="number" step="0.01" id="manualBudgetInput" class="form-control" placeholder="Manual budget">
                            </div>
                            <div class="col-md-3">
                                <label for="Comment">Comment : </label>
                                <textarea
                                    class="multisteps-form__inputone form-control"
                                    name="FullInvComment" id="FullInvComment" placeholder="Full Invoice Comment">
                                </textarea>
                            </div>
                            <div class="col-md-2">
                                <a id="downloadInvoiceBtn" class="btn btn-primary" href="#">View Invoice</a>
                            </div>
                        </div>
                        <br>
                        <div class="card-header d-flex justify-content-between align-items-center p-3 pb-0">
                            <h6>Period Invoice</h6>
                        </div>
                        <div class="row p-4 align-items-end">
                            <div class="col-md-2">
                                <label for="projectFilter2">Select Project</label>
                                <select id="projectFilter2" class="form-control">
                                    <option value="">Select Project</option>
                                    @foreach($projects as $project)
                                        <option value="{{ $project->id }}">
                                            {{ strtoupper($project->name) }} - 
                                            @if($project->budget) 
                                                {{ $project->budget - $project->amount_billed }}€ 
                                            @else 
                                                <span style="color: red;">no project budget inserted</span>
                                            @endif
                                        </option>                                    
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="startDateInput">Start Date</label>
                                <input type="date" id="startDateInput" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label for="endDateInput">End Date</label>
                                <input type="date" id="endDateInput" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="Comment">Comment : </label>
                                <textarea
                                    class="multisteps-form__inputone form-control"
                                    name="PeriodInvComment" id="PeriodInvComment" placeholder="Period Invoice Comment">
                                </textarea> 
                            </div>
                            <div class="col-md-3">
                                <a id="downloadDurationInvoiceBtn" class="btn btn-primary" href="#">View Invoice</a>
                            </div>
                        </div>
                        <br>
                        <div class="card-header d-flex justify-content-between align-items-center p-3 pb-0">
                            <h6>Partial Invoice</h6>
                        </div>
                        <div class="row p-4 align-items-end">
                            <div class="col-md-4">
                                <label for="devisSelect3">Select Devis</label>
                                <select id="devisSelect3" class="form-control">
                                    <option value="">Select Devis</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="partialAmountInput">Amount</label>
                                <input type="number" step="0.0001" id="partialAmountInput" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="Comment">Comment : </label>
                                <textarea
                                    class="multisteps-form__inputone form-control"
                                    name="PartialInvComment" id="PartialInvComment" placeholder="Partial Invoice Comment">
                                </textarea> 
                            </div>
                            <div class="col-md-3">
                                <a id="downloadPartialInvoiceBtn" class="btn btn-primary" href="#">View Invoice</a>
                            </div>
                        </div>
                        <br>
                        <div class="card-header d-flex justify-content-between align-items-center p-3 pb-0">
                            <h6>Percentage Invoice</h6>
                        </div>
                        <div class="row p-4 align-items-end">
                            <div class="col-md-4">
                                <label for="devisSelect4">Select Devis</label>
                                <select id="devisSelect4" class="form-control">
                                    <option value="">Select Devis</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="percentageInput">Percentage</label>
                                <input type="number" step="0.0001" id="percentageInput" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label for="Comment">Comment : </label>
                                <textarea
                                    class="multisteps-form__inputone form-control"
                                    name="PercentageInvComment" id="PercentageInvComment" placeholder="Percentage Invoice Comment">
                                </textarea>
                            </div>
                            <div class="col-md-3">
                                <a id="downloadPercentageInvoiceBtn" class="btn btn-primary" href="#">View Invoice</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </main>




    @include('dashboard_layouts.scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.17.4/xlsx.full.min.js"></script>

    <script>
    document.getElementById('exportBtn').addEventListener('click', function() {
        var table = document.getElementById(
        'taskTable'); // Replace 'yourTableId' with the actual ID of your table
        var wb = XLSX.utils.table_to_book(table, {
            sheet: 'Sheet1'
        });
        // Get the start date, end date, and task information
        var startDate = document.getElementById('startDateInput').value;
        var endDate = document.getElementById('endDateInput').value;
        var taskInfo = 'TasksInfo'; // Example task information

        // Format the file name
        var fileName = startDate + '__' + endDate + '_' + taskInfo + '.xlsx';

        // Trigger the file download with the custom file name
        XLSX.writeFile(wb, fileName);
    });
    </script>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        $('#projectFilter1').change(function() {
            // Enable both inputs when project is selected
            if ($(this).val()) {
                $('#hoursInput').prop('disabled', false);
                $('#manualBudgetInput').prop('disabled', false);
            } else {
                $('#hoursInput').prop('disabled', true);
                $('#manualBudgetInput').prop('disabled', true);
            }
        });

        // Format input as user types
        $('#hoursInput').on('input', function() {
            var value = $(this).val().replace(/[^0-9:]/g, ''); // Remove any characters except numbers and colon
            
            // If user enters a number without colon, add colon after first digits
            if (value.length > 0 && !value.includes(':')) {
                if (value.length > 2) {
                    value = value.slice(0, -2) + ':' + value.slice(-2);
                }
            }
            
            $(this).val(value);
        });

        // When manual budget input changes
        $('#manualBudgetInput').on('input', function() {
            if ($(this).val()) {
                $('#hoursInput').prop('disabled', true);
            } else {
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
                params.append('hours', hours.toFixed(2));
            } else if (manualBudget) {
                params.append('manualBudget', manualBudget);
            }
            if (comment) {
                params.append('comment', comment);
            }

            // Append parameters to URL
            downloadUrl += '?' + params.toString();
            window.location.href = downloadUrl;
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#downloadDurationInvoiceBtn').click(function(e) {
            e.preventDefault(); // Prevent the default behavior of the anchor tag

            var projectId = $('#projectFilter2').val(); // Get the selected project ID
            var startDate = $('#startDateInput').val(); // Get the start date
            var endDate = $('#endDateInput').val(); // Get the end date
            var comment = encodeURIComponent($('#PeriodInvComment').val()); // Get the comment

            console.log('id : ', projectId);

            // Check if all required fields are filled
            if (projectId && startDate && endDate) {
                // Construct the download URL with parameters
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
                window.location.href = downloadUrl; // Redirect to the download URL
            } else {
                alert('Please select a project and enter start/end dates.');
            }
        });
    });
    </script>

<script>
    $(document).ready(function() {
        $('#downloadPartialInvoiceBtn').click(function(e) {
            e.preventDefault();

            var projectId = null;
            var amount = $('#partialAmountInput').val();
            // Replace line breaks with spaces and clean up the comment
            var comment = $('#PartialInvComment').val()
                .replace(/\r?\n/g, ' ') // Replace line breaks with spaces
                .replace(/\s+/g, ' ')   // Replace multiple spaces with single space
                .trim();                // Remove leading/trailing spaces
            
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
    });
    </script>

<script>
    $(document).ready(function() {
        $('#downloadPercentageInvoiceBtn').click(function(e) {
            e.preventDefault(); // Prevent the default behavior of the anchor tag

            var projectId = null; // no project selection, we base on devis
            var percentage = $('#percentageInput').val(); // Get the amount
            var comment = encodeURIComponent($('#PercentageInvComment').val());  // Get the comment
            var devisId = $('#devisSelect4').val();

            console.log('id : ', projectId);

            // Check if all required fields are filled
            if (devisId && percentage) {
                // Construct the download URL with parameters
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
                window.location.href = downloadUrl; // Redirect to the download URL
            } else {
                alert('Please select a devis and enter the percentage.');
            }
        });
    });
    </script>

<script>
$(document).ready(function() {
    $('.form-control').select2({
        templateResult: formatProject,
        templateSelection: formatProject
    });
});

function formatProject(project) {
    if (!project.id) {
        return project.text;
    }
    
    var $project = $(project.text);
    var text = $project.text();
    
    // Match numbers at the start of the string
    var match = text.match(/^(\d+)(.*)$/);
    if (match) {
        return $('<span><strong>' + match[1] + '</strong>' + match[2] + '</span>');
    }
    
    return text;
}
</script>

<script>
$(document).ready(function() {
    // Load all accepted devis once
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

    // Function to load devis comment
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

    // Initial load of all devis into selects
    loadAllDevis('#devisSelect3');
    loadAllDevis('#devisSelect4');

    $('#devisSelect3').change(function() {
        loadDevisComment($(this).val(), '#PartialInvComment');
    });

    $('#devisSelect4').change(function() {
        loadDevisComment($(this).val(), '#PercentageInvComment');
    });
});
</script>




</body>

</html>