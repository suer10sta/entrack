<!DOCTYPE html>
<html lang="en">

@include('dashboard_layouts.head')

<body class="g-sidenav-show bg-gray-100">
    <div class="min-height-300 bg-primary position-absolute w-100"></div>
    @if(Auth::user()->hasRole('Responsable de projet') || Auth::user()->hasRole('Employee') || Auth::user()->hasRole('Responsable de projet et gestionnaire'))
        @include('employees.sidebar')
    @endif
    @if(Auth::user()->hasRole('Admin'))
        @include('admin.sidebar')
    @endif
    <main class="main-content position-relative border-radius-lg">
        <nav class="navbar navbar-main navbar-expand-lg px-0 mx-4 shadow-none border-radius-xl" id="navbarBlur" data-scroll="false">
            <div class="container-fluid py-1 px-3">
                @include('dashboard_layouts.navbar')
            </div>
        </nav>
        <div class="container-fluid py-4">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header pb-0">
                            <h6>Edit Detailed Invoice</h6>
                        </div>
                        <div class="card-body">
                            @if(Auth::user()->hasRole('Admin'))
                            <form action="{{ route('admin.invoices.updateDetailedInvoice', $invoice->id) }}" method="POST">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet'))
                            <form action="{{ route('Responsable_de_projet.invoices.updateDetailedInvoice', $invoice->id) }}" method="POST">
                            @endif
                            @if(Auth::user()->hasRole('Employee'))
                            <form action="{{ route('employee.invoices.updateDetailedInvoice', $invoice->id) }}" method="POST">
                            @endif
                            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                            <form action="{{ route('Responsable_gestionnaire.invoices.updateDetailedInvoice', $invoice->id) }}" method="POST">
                            @endif
                                @csrf
                                <input type="hidden" name="project_id" value="{{ $invoice->project_id }}">

                                <div class="form-group mb-3">
                                    <label for="comment">Comment</label>
                                    <textarea name="comment" id="comment" class="form-control">{{ $invoice->comment }}</textarea>
                                </div>

                                <div id="detailsContainer">
                                    @foreach(json_decode($invoice->details, true) as $index => $detail)
                                    <div class="row detail-row mb-3">
                                        <div class="col-md-4">
                                            <label>Detail</label>
                                            <input type="text" class="form-control detail-input" name="details[{{$index}}][description]" value="{{ $detail['description'] }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Quantity</label>
                                            <input type="number" step="0.01" class="form-control quantity-input" name="details[{{$index}}][quantity]" value="{{ $detail['quantity'] }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Unit Price (€)</label>
                                            <input type="number" step="0.01" class="form-control price-input" name="details[{{$index}}][unit_price]" value="{{ $detail['unit_price'] }}" required>
                                        </div>
                                        <div class="col-md-2">
                                            <label>Total</label>
                                            <input type="number" class="form-control total-input" value="{{ $detail['quantity'] * $detail['unit_price'] }}" readonly>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>

                                <div class="mt-3">
                                    <button type="button" class="btn btn-primary btn-sm" id="addRowBtn">Add Row</button>
                                    <button type="button" class="btn btn-danger btn-sm" id="deleteRowBtn">Delete Row</button>
                                </div>

                                <div class="col-md-12 mt-4 text-right">
                                    <h5>Total Amount: <span id="grandTotal">0.00</span>€</h5>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-success">Update Invoice</button>
                                    @if(Auth::user()->hasRole('Admin'))
                                    <a href="{{ route('admin.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet'))
                                    <a href="{{ route('Responsable_de_projet.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Employee'))
                                    <a href="{{ route('employee.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                    @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                                    <a href="{{ route('Responsable_gestionnaire.invoices.invoicehistory') }}" class="btn btn-secondary">Cancel</a>
                                    @endif
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    @include('dashboard_layouts.scripts')

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
                $('#grandTotal').text(grandTotal.toFixed(2));
            }

            function attachRowEventListeners(row) {
                row.find('.quantity-input, .price-input').on('input', function() {
                    calculateRowTotal(row);
                });
            }

            $('#addRowBtn').click(function() {
                const newRow = $('.detail-row:first').clone();
                const newIndex = $('.detail-row').length;
                newRow.find('input').val('');
                newRow.find('input[name^="details"]').each(function() {
                    const oldName = $(this).attr('name');
                    $(this).attr('name', oldName.replace(/\[\d+\]/, '[' + newIndex + ']'));
                });
                $('#detailsContainer').append(newRow);
                attachRowEventListeners(newRow);
            });

            $('#deleteRowBtn').click(function() {
                if ($('.detail-row').length > 1) {
                    $('.detail-row:last').remove();
                    calculateGrandTotal();
                }
            });

            $('.detail-row').each(function() {
                attachRowEventListeners($(this));
            });

            calculateGrandTotal();
        });
    </script>
</body>
</html>