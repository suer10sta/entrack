<!DOCTYPE html>
<html lang="en">

<head>
<style>
    /* --- Base & Typography --- */
    * {
        box-sizing: border-box;
    }
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 14px; /* Increased base font */
        line-height: 1.6;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        width: 100%;
        padding: 30px; /* More padding */
        box-sizing: border-box;
    }

    h2 {
        font-size: 22px;
        font-weight: 600;
        color: #111;
        margin: 0 0 5px 0;
    }

    h5 {
        font-size: 14px;
        font-weight: normal;
        color: #555;
        margin: 0 0 5px 0;
    }

    /* --- Header / Details --- */
    .details {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .company-logo {
        width: 100px; 
        height: auto;
    }

    .details-right {
        text-align: right;
    }
    
    /* --- Totals Summary Box --- */
    .totals-summary {
        margin-top: 30px; /* Replaced <br> tags */
        margin-bottom: 20px;
        padding: 15px 20px;
        background-color: #f8f9fa;
        border-radius: 8px;
        border: 1px solid #e9ecef;
    }
    .summary-row {
        display: flex;
        justify-content: space-between;
    }
    .summary-item {
        flex: 1;
        font-size: 14px;
    }
    .summary-item strong {
        color: #555;
        font-weight: 600;
    }
    .summary-item span {
        font-weight: 700;
    }
    .summary-item .paid { color: #28a745; }
    .summary-item .unpaid { color: #dc3545; }
    .summary-item .credit { color: #007bff; }
    .summary-item .total { color: #343a40; }


    /* --- Table Styles --- */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 60px; /* Space before footer */
    }

    thead {
        background-color: #1E9FD8 !important; /* Kept your header color */
        color: white;
    }
    
    /* Unified borders for a clean look */
    th, td {
        border: 1px solid #e9ecef; /* Light gray border */
        padding: 12px 15px; /* Increased padding */
        text-align: left;
    }

    th {
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    td {
        font-size: 13px; 
    }
    
    tbody tr:nth-of-type(even) {
        background-color: #f8f9fa;
    }

    /* --- Footer --- */
    .footer {
        width: 100%;
        display: flex;
        justify-content: space-between;
        position: fixed;
        bottom: 0;
        left: 0;
        padding: 15px 30px;
        box-sizing: border-box;
        border-top: 1px solid #eee;
        background: #fff;
        font-size: 12px;
        color: #777;
    }
    .footer-left { text-align: left; }
    .footer-right { text-align: right; }

    .page-number:before {
        content: counter(page);
    }

    @media print {
        th, td { font-size: 10px; }
    }
</style>
</head>
<body>

<div class="container">

    <div class="details">
        <div class="details-left">
            <img src="{{ Storage::disk('do_spaces')->url($entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Invoices Table Reports</h2>
                <h5>Number of Invoices: {{ $invoices->count() }}</h5>
            </div>
        </div>
    </div>
    
    <div class="totals-summary">
        <div class="summary-row">
            <div class="summary-item">
                <strong>Total Paid:</strong> <span class="paid">{{ number_format($totalPaid, 2, ',', '') }}€</span>
            </div>
            <div class="summary-item">
                <strong>Total Unpaid:</strong> <span class="unpaid">{{ number_format($totalUnpaid, 2, ',', '')}}€</span>
            </div>
            <div class="summary-item">
                <strong>Total TVA:</strong> <span>{{ number_format($totaltva, 2, ',', '') }}€</span>
            </div>
            <div class="summary-item">
                <strong>Total Credit Note:</strong> <span class="credit">{{ number_format($totalCreditNote, 2, ',', '')}}€</span>
            </div>
            <div class="summary-item">
                <strong>Total:</strong> <span class="total">{{ number_format($total, 2, ',', '')}}€</span>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Invoice N</th>
                <th>Project Name</th>
                <th>TVA</th>
                <th>Client Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Invoice Type</th>
                <th style="width: 12%;">Created At</th>
                <th style="width: 5%;">Payment</th>
                <th>Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
            <tr>
                <td>
                    @php 
                        $dueDate = \Carbon\Carbon::parse($invoice->due_date);
                        $today = \Carbon\Carbon::now();
                        $isOverdue = $dueDate->isPast() && !$invoice->Payment_status;
                        $daysOverdue = $isOverdue ? $today->diffInDays($dueDate) : 0;
                    @endphp
                    <span style="{{ $isOverdue ? 'color: red;' : '' }}">
                        <b>{{$entreprise->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}</b>
                        @if($isOverdue)
                            <br><span style="color: red; font-size: 11px;">({{ $daysOverdue }} days overdue)</span>
                        @endif
                    </span>
                </td>
                <td>
                    @php 
                    $project = App\Models\Project::find($invoice->project_id);
                    @endphp
                    {{$project->name}}
                </td>
                <td>
                    {{$project->tva}}%
                </td>
                <td>
                    @php 
                    $client = App\Models\Client::find($invoice->client_id);
                    @endphp
                    {{$client->name}}
                </td>
                <td>{{$invoice->start_date}}</td>
                <td>{{$invoice->end_date}}</td>
                <td>{{$invoice->type}}</td>
                <td>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d-m-Y') }}</td>
                <td>
                    @if($invoice->Payment_status == 1)
                        <span style="color: #28a745; font-weight: 600;">Paid</span>
                    @elseif($invoice->Payment_status == -1)
                        <span style="color: #007bff; font-weight: 600;">Credit Note</span>
                    @else
                        <span style="color: #dc3545; font-weight: 600;">Unpaid</span>                     
                    @endif
                </td>
                <td>
                    <span style="font-weight: 600; color: {{ $invoice->Payment_status == 1 ? '#28a745' : ($invoice->Payment_status == -1 ? '#007bff' : '#dc3545') }}">
                        €{{number_format($invoice->total_ttc, 2, ',', '')}}
                    </span>
                </td> 
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class='footer-left'>Download Date: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>Page Number: <span class="page-number"></span></div>
    </div>

</div>

</body>
</html>