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
                <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo"
                    class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Pointage Table Reports</h2>
                <h5>For : <strong>{{$employeeName}}</strong> From : {{ $startDate }} To {{ $endDate }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    @php
        $totalDuration = 0;
    @endphp
    <table>
        <thead>
            <tr>
                <th>Project Name</th>
                <th>Task Name</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Duration</th>
            </tr>
        </thead>
        <tbody>
            @foreach($tasks_timer as $task)
            <tr>
                <td>
                    @php
                        $projectid = App\Models\Tasks::where('id', $task->task_id)->pluck('project_id');
                        $project = App\Models\Project::where('id', $projectid)->first();
                    @endphp
                    {{$project->name}}
                </td>
                <td>{{$task->task_name}}</td>
                <td>{{$task->start_date}}</td>
                <td>{{$task->end_date}}</td>
                <td>
                    @php
                    $hours = floor($task->duration / 60);
                    $minutes = $task->duration % 60;
                    $totalDuration += $task->duration;
                    @endphp
                    {{ sprintf("%02dh:%02dm", $hours, $minutes) }}
                </td>
            </tr>
            @endforeach
            <tr style="border: none;">
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"><strong>Totals : </strong></td>
                <td style="border: none;"> 
                    @php
                        $hours = floor($totalDuration / 60);
                        $minutes = $totalDuration % 60;
                    @endphp
                    <strong> {{ sprintf("%02dh:%02dm", $hours, $minutes) }} </strong>  
                </td>
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
