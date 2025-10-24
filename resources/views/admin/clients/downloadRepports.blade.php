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
</style>

<div class="containter">


    <div class="details">
        <div class="details-left">
        <img src="{{ Storage::disk('do_spaces')->url($entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Clients Table Reports</h2>
                <h5>Number of Clients: {{ $clients->count() }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    <table>
        <thead>
            <tr>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Number</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Name</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Email</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Phone</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Billing Address</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">TVA</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Due Date</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Address</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">City</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Zip</th>
                <th class="text-center text-uppercase text-secondary text-xs font-weight-bolder opacity-10">Country</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr>
                <td>{{$client->client_number}}</td>
                <td>{{$client->name}}</td>
                <td>{{$client->email}}</td>
                <td>{{$client->phone}}</td>
                <td>{{$client->Billing_address}}</td>
                <td>{{$client->TVA}}</td>
                <td>{{$client->due_date}}</td>
                <td>{{$client->address}}</td>
                <td>{{$client->city}}</td>
                <td>{{$client->zip}}</td>
                <td>{{$client->country}}</td>
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