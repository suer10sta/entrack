<style>
        /* [MODIFIED] Increased font size slightly for better readability (Zoom effect) */
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px; /* Changed from 10px to 11px */
            color: #333333;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        .report-wrapper {
            width: 100%;
            max-width: 900px; 
            margin: 0 auto;
            padding: 40px 20px 100px 20px;
            position: relative;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        .details {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 10px;
        }

        .details-left {
            width: 40%;
            font-size: 12px;
        }

        .details-right {
            width: 58%;
            font-size: 12px;
            text-align: right;
            position: absolute;
            right: 0;
            top: 0;
        }
        
        .totals-summary {
            margin-top: 10px;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            font-size: 11px;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
        }
        .summary-item {
            margin-right: 15px;
            margin-bottom: 5px;
        }
        .summary-item strong { color: #555; }
        .summary-item .paid { color: #28a745; font-weight: bold; }
        .summary-item .unpaid { color: #dc3545; font-weight: bold; }
        .summary-item .credit { color: #007bff; font-weight: bold; }
        .summary-item .total { color: #333; font-weight: bold; }

        .company-logo {
            width: 100px;
            height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            table-layout: auto;
        }

        table, th, td {
            border: 1px solid #CCCCCC;
        }

        th, td {
            padding: 6px;
            text-align: left;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .footer {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-top: 1px solid #000;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
        }

        .footer-content {
            position: relative;
            width: 100%;
            height: 50px;
            display: flex;
            justify-content: space-between;
        }

        .footer-left { position: absolute; left: 0; text-align: left; }
        .footer-center { position: absolute; left: 50%; transform: translateX(-50%); text-align: center; }
        .footer-right { position: absolute; right: 0; text-align: right; }

        .footer-text {
            color: #333333;
            line-height: 1.4;
            font-size: 10px; /* Footer stays small */
        }

        @media print {
            body { margin: 0; padding: 0; }
            .report-wrapper { width: 100%; max-width: 100%; padding: 20px; margin: 0; border: none; }
            .footer { position: fixed; bottom: 0; width: 100%; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>

<div class="containter">


    <div class="details">
        <div class="details-left">
            @if($entreprise->image)
                        <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
                    @endif
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Invoices Table Reports</h2>
                <h4>Client Name: {{ $client->name }}</h4>
                <h4>Number of Invoices: {{ $invoices->count() }}</h4>
            </div>
        </div>
    </div>
    <br><br><br><br>
    @php 
        $paidTotal = 0;
        $unpaidTotal = 0;
        $totaltva = 0;
        $totalCost = 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th>Invoice N</th>
                <th>Project Name</th>
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
                        {{$entreprise->invoice_prefix}}-{{date('ym', strtotime($invoice->created_at))}}{{$invoice->number}}
                        @if($isOverdue)
                            <span style="color: red;">({{ $daysOverdue }} days overdue)</span>
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
                    <span style="color: {{ $invoice->Payment_status == 1 ? 'green' : 'red' }}">
                        €{{number_format($invoice->total_ttc, 2, ',', '')}}
                    </span>
                    @if($invoice->Payment_status == 1)
                        @php
                            $paidTotal += $invoice->total_ttc;
                        @endphp
                    @else
                        @php
                            $unpaidTotal += $invoice->total_ttc;
                        @endphp
                    @endif
                    @php
                        $totaltva += $invoice->total_tva;
                        $totalCost += $invoice->total_ttc;
                    @endphp
                </td>
            </tr>
            @endforeach
            
        </tbody>
    </table>

    <div class="totals-summary">
            <div class="summary-row">
                <div class="summary-item">
                    <strong>Total Paid:</strong> <span class="paid">{{ number_format($paidTotal, 2, ',', ' ') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>Total Unpaid:</strong> <span class="unpaid">{{ number_format($unpaidTotal, 2, ',', ' ') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>Total TVA:</strong> <span>{{ number_format($totaltva, 2, ',', ' ') }}€</span>
                </div>
                <div class="summary-item">
                    <strong>Total Budget:</strong> <span class="total">{{ number_format($totalCost, 2, ',', ' ') }}€</span>
                </div>
            </div>
    </div>
    <div class="footer">
        <div class='footer-right'>Page Number: <span class="page-number"></span></div>
        <div class='footer-left'>Download Date: {{ date('d-m-Y') }}</div>
    </div>

</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Find all elements that represent pages
    const pages = document.querySelectorAll('.page');

    // Check if pages exist
    if (pages.length === 0) {
        console.error('No pages found. Ensure your HTML structure includes elements with a .page class.');
        return;
    }

    // Loop through each page and set the page number
    pages.forEach((page, index) => {
        // Find the .page-number span within the .footer of each page
        const pageNumberElement = page.querySelector('.footer .page-number');
        if (pageNumberElement) {
            pageNumberElement.textContent = index + 1; // Page numbers should start from 1
        } else {
            console.error('Page number element not found in page ' + (index + 1));
        }
    });
});
</script>
