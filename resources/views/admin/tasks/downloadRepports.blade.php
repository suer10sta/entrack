<style>
body {
    font-family: Arial, sans-serif;

}

table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}

th {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 12px;
    font-weight: bold;
    word-wrap: break-word;
}

td {
    border: 1px solid black;
    padding: 8px;
    text-align: left;
    font-family: Arial, sans-serif;
    font-size: 9px;
    font-weight: normal;
    word-wrap: break-word;
    page-break-inside: auto;
}

tr {
    page-break-inside: auto;
}

thead {
    display: table-header-group;
    background-color: #686ce4;
    /* Updated color */
    color: white;
    /* Improve readability */
}



.company-logo {
    width: 80px;
    /* Adjust the width as needed */
}

.header-table {
    width: 100%;
    border: none;
    margin-bottom: 20px;
}

.header-table td {
    border: none;
    vertical-align: middle;
    font-size: 12px;
}

.header-right {
    text-align: right;
}

.footer {
    width: 100%;
    margin-top: 20px;
    font-size: 12px;
}

.footer-right {
    text-align: right;
}

.page-number:before {
    content: counter(page);
}
</style>

<div class="containter">

    @php
        $logoPath = ($entreprise && !empty($entreprise->image)) ? storage_path('app/public/' . $entreprise->image) : null;
        $hasLogo = $logoPath && is_file($logoPath);
    @endphp
    <table class="header-table">
        <tr>
            <td style="width: 20%;">
                @if($hasLogo)
                <img src="{{ $logoPath }}" alt="Company Logo" class="company-logo">
                @endif
            </td>
            <td class="header-right">
                <h2>{{ __('messages.Tasks Table Reports') }}</h2>
                <h5>{{ __('messages.Number of Tasks') }}: {{ $tasks->count() }}</h5>
            </td>
        </tr>
    </table>
    <table>
        <thead>
            <tr>
                <th style="width: 18%;">{{ __('messages.Task Name') }}</th>
                <th style="width: 16%;">{{ __('messages.Project Name') }}</th>
                <th style="width: 10%;">{{ __('messages.Start Date') }}</th>
                <th style="width: 10%;">{{ __('messages.End Date') }}</th>
                <th style="width: 16%;">{{ __('messages.Employees column') }}</th>
                <th style="width: 14%;">{{ __('messages.Teams column header') }}</th>
                <th style="width: 16%;">{{ __('messages.Description') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks as $task)
            <tr>
                <td>{{ $task->title }}</td>
                <td>{{ $task->project_name }}</td>
                <td>{{ $task->start }}</td>
                <td>{{ $task->end }}</td>
                <td>
                    @php
                        // Use preloaded collection from controller to avoid N+1 queries.
                        // Plain comma-separated names (no nested markup) so DomPDF can
                        // lay out large reports without exhausting memory.
                        $taskEmployees = isset($employeesByTask) ? ($employeesByTask[$task->id] ?? collect()) : collect();
                        $employeeNames = $taskEmployees->map(function ($empTask) {
                            return optional($empTask->employee)->name ?? ($empTask->employee_name ?? '');
                        })->filter()->values();
                    @endphp
                    {{ $employeeNames->isNotEmpty() ? $employeeNames->join(', ') : __('messages.No employees') }}
                </td>
                <td>
                    @php
                        // Use preloaded collection from controller to avoid N+1 queries.
                        $taskTeams = isset($teamsByTask) ? ($teamsByTask[$task->id] ?? collect()) : collect();
                        $teamNames = $taskTeams->map(function ($teamTask) {
                            return optional($teamTask->team)->name ?? '';
                        })->filter()->values();
                    @endphp
                    {{ $teamNames->isNotEmpty() ? $teamNames->join(', ') : __('messages.No team') }}
                </td>
                <td>{{ $task->description }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class='footer-right'>{{ __('messages.Page Number') }}: <span class="page-number"></span></div>
        <div>{{ __('messages.Download Date') }}: {{ date('d-m-Y') }}</div>
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