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
            
            <img src="{{ Storage::disk('do_spaces')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">

        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Credit Note Table Reports</h2>
                <h5>Number of Credit Note : {{ $creditNote->count() }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    @php 
        $totalCost = 0;
        $totaltva = 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Credit Note N</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Invoice N</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Project Name</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">TVA</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Client Name</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Start Date</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">End Date</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Credit note Type</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Total Cost</th>
            </tr>
        </thead>
        <tbody>
            @foreach($creditNote as $cn)
            <tr>
                <td>
                    {{$entreprise->credit_note_prefix}}-{{date('ym')}}{{$cn->number}}
                </td>
                <td>
                    @php
                        $invoice = App\Models\invoices_history::where('id', $cn->invoice_id)->first();
                    @endphp
                    {{$entreprise->invoice_prefix}}-{{date('ym')}}{{$invoice->number}}
                </td>
                <td>
                    @php 
                    $project = App\Models\Project::find($cn->project_id);
                    @endphp
                    {{$project->name}}
                </td>
                <td>
                    {{$project->tva}}%
                </td>
                <td>
                    @php 
                    $client = App\Models\Client::find($cn->client_id);
                    @endphp
                    {{$client->name}}
                </td>
                <td>{{$cn->start_date}}</td>
                <td>{{$cn->end_date}}</td>
                <td>{{$cn->credit_note_type}}</td>
                <td>
                    -{{number_format($cn->total_ttc, 2, ',', '')}}€
                    
                        @php
                            $totalCost += $cn->total_ttc;
                            $totaltva += $cn->total_tva;
                        @endphp
                    
                </td> 
            </tr>
            @endforeach
            <tr style="border: none;">
                
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"><strong>Totals Cost : </strong></td>
                <td style="border: none;"> <strong> -{{  number_format($totalCost, 3, ',', '') }}€ </strong> </td>
            </tr>
            <tr style="border: none;">
              
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"><strong>Totals TVA : </strong></td>
                <td style="border: none;"> <strong> -{{ number_format($totaltva, 3, ',', '') }}€ </strong> </td>
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