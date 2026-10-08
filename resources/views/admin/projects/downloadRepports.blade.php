<style>
body {
    font-family: Arial, sans-serif;

}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px; 
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
            <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>{{ __('messages.Projects Table Reports') }}</h2>
                <h5>{{ __('messages.Number of Projects') }}: {{ $projects->count() }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.Project Name') }}</th>
                <th>{{ __('messages.Budget') }}</th>
                <th>{{ __('messages.Cost Per Hour') }}</th>
                <th>{{ __('messages.Estimated Hours') }}</th>
                <th>{{ __('messages.VAT') }}</th>
                <th>{{ __('messages.Client Name') }}</th>
                <th>{{ __('messages.Informations') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $project)
            <tr>
                <td>{{$project->name}}</td>
                <td>{{$project->budget}}€</td>
                <td>{{$project->cost_per_hour}}€</td>
                <td>{{$project->estimated_hours}}h</td>
                <td>{{$project->TVA}}</td>
                @php
                $client = $project->client_id ? App\Models\Client::where('id', $project->client_id)->first() : null;
                @endphp
                <td>{{$client ? $client->name : 'No client'}}</td>
                <td>{{$project->description}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class='footer-right'>{{ __('messages.Page Number') }}: <span class="page-number"></span></div>
        <div class='footer-left'>{{ __('messages.Download Date') }}: {{ date('d-m-Y') }}</div>
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