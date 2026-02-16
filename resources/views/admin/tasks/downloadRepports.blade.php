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
    font-size: 9px;
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
    bottom: 0;
    /* Ensure footer sticks to the bottom */
    left: 0;
    /* Align footer to the left */
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
                <h2>Tasks Table Reports</h2>
                <h5>Number of Tasks: {{ $tasks->count() }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    <table>
        <thead>
            <tr>
                <th>Task Name</th>
                <th>Project Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Employees</th>
                <th>Teams</th>
                <th>Description</th>
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
                        // Use preloaded collection from controller to avoid N+1 queries
                        $taskEmployees = isset($employeesByTask) ? ($employeesByTask[$task->id] ?? collect()) : collect();
                    @endphp
                    @forelse($taskEmployees as $empTask)
                        <span class="badge badge-dot me-4">
                            <i class="bg-info"></i>
                            <span class="text-dark text-xs">
                                {{ optional($empTask->employee)->name ?? $empTask->employee_name ?? '' }}
                            </span>
                        </span>
                        <br>
                    @empty
                        <span class="badge badge-dot me-4">
                            <i class="bg-info"></i>
                            <span class="text-dark text-xs">No employees</span>
                        </span>
                    @endforelse
                </td>
                <td>
                    @php
                        // Use preloaded collection from controller to avoid N+1 queries
                        $taskTeams = isset($teamsByTask) ? ($teamsByTask[$task->id] ?? collect()) : collect();
                    @endphp
                    @forelse($taskTeams as $teamTask)
                        <span class="badge badge-dot me-4">
                            <i class="bg-info"></i>
                            <span class="text-dark text-xs">
                                {{ optional($teamTask->team)->name ?? '' }}
                            </span>
                        </span>
                        <br>
                    @empty
                        <span class="badge badge-dot me-4">
                            <i class="bg-info"></i>
                            <span class="text-dark text-xs">No team</span>
                        </span>
                    @endforelse
                </td>
                <td>{{ $task->description }}</td>
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