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
                <h2>Devis Table Reports</h2>
                <h5>Number of Devis: {{ $devis->count() }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    @php 
        $Total_Accpeted = 0;
        $Total_Declined = 0;
        $Total_Pending = 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th>devis N</th>
                <th>Project Name</th>
                <th>TVA</th>
                <th>Client Name</th>
                <th>Emails</th>
                <th>Acceptance status</th>
                <th>Budget</th>
            </tr>
        </thead>
        <tbody>
            @foreach($devis as $devi)
            <tr>
                <td>{{$entreprise->devis_prefix}}-{{$devi->devis_number}}</td>
                <td>
                    @php 
                    $project = App\Models\Project::find($devi->project_id);
                    @endphp
                    {{$project->name}}
                </td>
                <td>
                    {{$project->tva}}%
                </td>
                <td>
                    @php 
                    $client = App\Models\Client::find($devi->client_id);
                    @endphp
                    {{$client->name}}
                </td>
                <td>{{$devi->emails}}</td>
                <td>
                    @if($devi->acceptanceStatus == 1)
                        Accepted
                    @elseif($devi->acceptanceStatus == 2)
                        Declined
                    @else
                        Pending                    
                    @endif
                </td>
                <td>
                    €{{number_format($devi->budget, 2, ',', '')}}
                    @if($devi->acceptanceStatus == 1)
                        @php
                            $Total_Accpeted += $devi->budget;
                        @endphp
                    @elseif($devi->acceptanceStatus == 2)
                        @php
                            $Total_Declined += $devi->budget;
                        @endphp
                    @else
                        @php
                            $Total_Pending += $devi->budget;
                        @endphp
                    @endif
                </td> 
            </tr>
            @endforeach
            <tr style="border: none;">
              
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
              
                <td style="border: none;"><strong>Totals Accepted : </strong></td>
                <td style="border: none;"> <strong> {{ $Total_Accpeted }}€ </strong> </td>
            </tr>
            <tr style="border: none;">
             
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"><strong>Totals Declined : </strong></td>
                <td style="border: none;"> <strong> {{ $Total_Declined }}€ </strong> </td>
            </tr>
            <tr style="border: none;">
             
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"><strong>Totals Pending : </strong></td>
                <td style="border: none;"> <strong> {{ $Total_Pending }}€ </strong> </td>
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