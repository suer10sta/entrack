<style>
body {
    font-family: Arial, sans-serif;

}

table {
    width: 100%;
    border-collapse: collapse;
}

th {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 12px;
    font-weight: bold;
}

td {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 11px;
    font-weight: normal;
}

thead {
    background-color: #f2f2f2;
}



.company-logo {
    width: 80px;
    /* Adjust the width as needed */
    margin-right: 20px;
    /* Add some space between the image and the header info */
}

.details {
    width: 100%;
    display: flex;
    justify-content: space-between;
    margin-bottom: 20px;
    position: relative;
}

.details-left {
    width: 48%;
    font-size: 12px;
}

.details-right {
    width: 48%;
    font-size: 12px;
    text-align: right;
    position: absolute;
    right: 0;
    top: 0;
}

thead {
    background-color: #686ce4;
    /* Updated color */
    color: white;
    /* Improve readability */
}


.footer {
  width: 100%;
  display: flex;
  justify-content: space-between;
  margin-bottom: 20px;
  position: fixed;
  bottom: 0; /* Ensure footer sticks to the bottom */
  left: 0; /* Align footer to the left */
}

.footer-left {
  width: 48%;
  font-size: 12px;
}

.footer-right {
  width: 48%;
  font-size: 12px;
  text-align: right;
  position: absolute;
  right: 0;
  top: 0;
}
.page-number:before {
        content: counter(page);
    }

    @media print {
        th, td { font-size: 10px; } /* Even smaller font size for printing if necessary */
    }
</style>

<div class="containter">


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
    <br><br><br>
    <div class="totals-summary" style="margin-bottom: 20px; padding: 10px; background-color: #f8f9fa; border-radius: 5px;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
            <div style="flex: 1;">
                <strong>Total Paid:</strong> <span style="color: green;">{{ number_format($totalPaid, 2, ',', '') }}€</span>
            </div>
            <div style="flex: 1;">
                <strong>Total Unpaid:</strong> <span style="color: red;">{{ number_format($totalUnpaid, 2, ',', '')}}€</span>
            </div>
            <div style="flex: 1;">
                <strong>Total TVA:</strong> {{ number_format($totaltva, 2, ',', '') }}€
            </div>
            <div style="flex: 1;">
                <strong>Total Credit Note:</strong> <span style="color: blue;">{{ number_format($totalCreditNote, 2, ',', '')}}€</span>
            </div>
            <div style="flex: 1;">
                <strong>Total:</strong> <span style="color: #4876ff;">{{ number_format($total, 2, ',', '')}}€</span>
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
                        <span style="color: green;">Paid</span>
                    @elseif($invoice->Payment_status == -1)
                        <span style="color: blue;">Credit Note</span>
                    @else
                        <span style="color: red;">Unpaid</span>                    
                    @endif
                </td>
                <td>
                    <span style="color: {{ $invoice->Payment_status == 1 ? 'green' : 'red' }}">
                        €{{number_format($invoice->total_ttc, 2, ',', '')}}
                    </span>
                </td> 
            </tr>
            @endforeach
        </tbody>
    </table>

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