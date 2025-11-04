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
    background-color: #1E9FD8;
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
            <tr style="border: none;">
                <td style="border: none;"></td>
                
                <td style="border: none;"><strong>Totals Unpaid : </strong></td>
                <td style="border: none;"> <strong> {{ number_format($unpaidTotal, 2, ',', '')}}€ </strong> </td> 
            </tr>
            <tr style="border: none;">
                <td style="border: none;"></td>
              
                <td style="border: none;"><strong>Totals Paid : </strong></td>
                <td style="border: none;"> <strong> {{  number_format($paidTotal, 2, ',', '') }}€ </strong> </td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"></td>
    
                <td style="border: none;"><strong>Totals TVA : </strong></td>
                <td style="border: none;"> <strong> {{ number_format($totaltva, 2, ',', '') }}€ </strong> </td>
            </tr>
            <tr style="border: none;">
                <td style="border: none;"></td>
    
                <td style="border: none;"><strong>Totals Budget : </strong></td>
                <td style="border: none;"> <strong> {{ number_format($totalCost, 2, ',', '') }}€ </strong> </td>
            </tr>
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