<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')
<style>
    a {
        color: blue;
    }
</style>

<body class="g-sidenav-show bg-gray-100">
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
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center p-3 pb-0">
                            <h6>Detailed Invoice Generator</h6>
                        </div>
                        <div class="row p-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="projectSelect">Select Project</label>
                                    <select id="projectSelect" class="form-control">
                                        <option value="">Select Project</option>
                                        @foreach($projects as $project)
                                            <option value="{{ $project->id }}" 
                                                    data-cost-per-hour="{{ $project->cost_per_hour }}"
                                                    data-tva="{{ $project->tva }}">
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

                                <div class="col-md-8">
                                    <label for="comment">Comment</label>
                                    <textarea id="comment" name="comment" class="form-control" rows="3" 
                                              placeholder="Add any additional notes or comments here..."></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-12 mt-4">
                                <div id="detailsContainer">
                                    <div class="row detail-row mb-3">
                                        <div class="col-md-4">
                                            <label>Detail</label>
                                            <input type="text" class="form-control detail-input" placeholder="Enter detail">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Quantity</label>
                                            <input type="number" step="0.01" class="form-control quantity-input" placeholder="Quantity">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Unit Price (€)</label>
                                            <input type="number" step="0.01" class="form-control price-input" placeholder="Unit price">
                                        </div>
                                        <div class="col-md-2">
                                            <label>Total</label>
                                            <input type="number" class="form-control total-input" readonly>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary btn-sm" id="addRowBtn">Add Row</button>
                                    <button type="button" class="btn btn-danger btn-sm" id="deleteRowBtn">Delete Row</button>
                                </div>

                                <div class="col-md-12 mt-4 text-right">
                                    <h5>Total Amount: <span id="grandTotal">0.00</span>€</h5>
                                </div>

                                <div class="col-md-12 mt-4">
                                    <button type="button" class="btn btn-success" id="generateInvoiceBtn">Generate Invoice</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('dashboard_layouts.scripts')

    <script>
        $(document).ready(function() {
            // Add new row button
            $('#addRowBtn').click(function() {
                const newRow = $('.detail-row:first').clone();
                newRow.find('input').val(''); // Clear inputs
                newRow.find('.total-input').val('0.00');
                $('#detailsContainer').append(newRow);
                attachRowEventListeners(newRow);
            });

            // Delete row button
            $('#deleteRowBtn').click(function() {
                if ($('.detail-row').length > 1) {
                    $('.detail-row:last').remove();
                    calculateGrandTotal();
                }
            });

            // Function to calculate row total
            function calculateRowTotal(row) {
                const quantity = parseFloat(row.find('.quantity-input').val()) || 0;
                const price = parseFloat(row.find('.price-input').val()) || 0;
                const total = quantity * price;
                row.find('.total-input').val(total.toFixed(2));
                calculateGrandTotal();
            }

            // Function to calculate grand total
            function calculateGrandTotal() {
                let grandTotal = 0;
                $('.total-input').each(function() {
                    grandTotal += parseFloat($(this).val()) || 0;
                });
                $('#grandTotal').text(grandTotal.toFixed(2));
            }

            // Function to attach event listeners to row inputs
            function attachRowEventListeners(row) {
                row.find('.quantity-input, .price-input').on('input', function() {
                    calculateRowTotal(row);
                });
            }

            // Generate invoice button
            $('#generateInvoiceBtn').click(function(e) {
                e.preventDefault();
                
                let details = [];
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

                if (details.length === 0) {
                    alert('Please add at least one valid detail row');
                    return;
                }

                const project_id = $('#projectSelect').val();
                if (!project_id) {
                    alert('Please select a project');
                    return;
                }

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
                        _token: '{{ csrf_token() }}',
                        project_id: project_id,
                        details: details,
                        comment: $('#comment').val()
                    },
                    success: function(response) {
                        // Replace current page content with the invoice view
                        document.open();
                        document.write(response);
                        document.close();
                    },
                    error: function(xhr) {
                        alert('Error generating invoice: ' + (xhr.responseJSON?.error || 'Unknown error'));
                    }
                });
            });

            // Initial setup
            attachRowEventListeners($('.detail-row:first'));

            // Initial calculation of totals
            $('.detail-row').each(function() {
                calculateRowTotal($(this));
            });
        });
    </script>
</body>
</html>
