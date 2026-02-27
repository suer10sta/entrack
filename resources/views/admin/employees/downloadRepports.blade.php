<style>
body {
    font-family: Arial, sans-serif;
}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed; /* Force columns to fit within page width */
}

th {
    border: 1px solid black;
    padding: 4px; /* Reduced padding to fit more columns */
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 10px; /* Smaller font for headers */
    font-weight: bold;
    word-wrap: break-word;
}

td {
    border: 1px solid black;
    padding: 4px; /* Reduced padding */
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 9px; /* Smaller font for body cells */
    font-weight: normal;
    word-wrap: break-word;
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
        th { font-size: 9px; }
        td { font-size: 8px; }
    }
</style>

<div class="containter">


    <div class="details">
        <div class="details-left">
            <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>{{ __('messages.Employees Table Reports') }}</h2>
                <h5>{{ __('messages.Number of Employees') }}: {{ $employees->count() }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.Name') }}</th>
                <th>{{ __('messages.Login Email') }}</th>
                <th>{{ __('messages.Contact Email') }}</th>
                <th>{{ __('messages.Phone') }}</th>
                <th>{{ __('messages.Age') }}</th>
                <th>{{ __('messages.CNSS') }}</th>
                <th>{{ __('messages.Bank Account Number') }}</th>
                <th>{{ __('messages.Salary') }}</th>
                <th>{{ __('messages.Cost Per Hour') }}</th>
                <th>{{ __('messages.Work Hours Limit') }}</th>
                <th>{{ __('messages.CNSS Contribution') }}</th>
                <th>{{ __('messages.AMO Contribution') }}</th>
                <th>{{ __('messages.Release Date') }}</th>
        
            </tr>
        </thead>
        <tbody>
            @foreach($employees as $employee)
            <tr>
                <td>{{$employee->name}}</td>
                <td>{{$employee->email}}</td>
                <td>{{$employee->contact_email}}</td>
                <td>{{$employee->phoneNumber}}</td>
                <td>{{$employee->age}}</td>
                <td>{{$employee->cnss}}</td>
                <td>{{$employee->bankAccountNumber}}</td>
                <td>{{$employee->salary}} €</td>
                <td>{{$employee->cost_per_hour}} €</td>
                <td>{{$employee->workHours_limit}} h</td>
                <td>{{$employee->Cotisation_CNSS}} €</td>
                <td>{{$employee->Cotisation_AMO}} €</td>
                <td>{{$employee->Date_Release}}</td>
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