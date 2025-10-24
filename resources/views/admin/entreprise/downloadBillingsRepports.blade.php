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
            <img src="{{ Storage::disk('do_spaces')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2> Table de dépense</h2>
                <h3>De date : {{ $startDate }} a {{ $endDate }}</h3>
                <h5>Nombre de dépense: {{ $billings->count() }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    @foreach($billings as $billing)
    @php 
        $Total = 0;
    @endphp
    <h3>Budget : {{ $billing->budget }}€ de date : {{ $billing->created_at->format('d-m-Y') }}</h3>
    <table>
        <thead>
            <tr>
                <th>Objet de la dépense </th>
                <th>Date de la dépense </th>
                <th>Prix de la dépense </th>
            </tr>
        </thead>
        <tbody>
                @php 
                    $entrepriseCharges = App\Models\entrepriseCharges::where('entreprise_id', $entreprise->id)->where('Billings_budget_id', $billing->id)->where('validated', 1)->orderBy('created_at')->get();
                @endphp
                @foreach($entrepriseCharges as $entrepriseCharge)
                <tr>
                    <td>{{$entrepriseCharge->charge_name}}</td>
                    <td>{{ $entrepriseCharge->created_at->format('d-m-Y') }}</td>
                    <td>
                        {{$entrepriseCharge->price}}€
                        @php
                            $Total += $entrepriseCharge->price;
                        @endphp
                    </td>
                </tr>
                @endforeach
                <tr style="border: none;">
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"> <strong> Total :{{ number_format($Total, 3, ',', '')}}€ </strong> </td> 
                </tr>
        </tbody>
    </table>
    @endforeach

    <div class="footer">
        <div class='footer-right'>Page : <span class="page-number"></span></div>
        <div class='footer-left'>Date de telechargement: {{ date('d-m-Y') }}</div>
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