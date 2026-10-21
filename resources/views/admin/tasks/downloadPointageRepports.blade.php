<style>
body {
    font-family: Arial, sans-serif;
    margin-bottom: 30px; /* Added margin-bottom to control space */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 10px;
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
    margin-bottom: 10px; /* Reduced from 20px to 10px */
    position: fixed;
    bottom: 10px; /* Changed from 0 to 10px */
    left: 0;
    padding: 5px; /* Added padding */
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

@page {
    margin-bottom: 30px; /* Added page margin control */
}
</style>

<div class="containter">
    <div class="details">
        <div class="details-left">
            @if(isset($entreprise) && $entreprise->image && file_exists(storage_path('app/public/' . $entreprise->image)))
                <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
            @endif
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>{{ __('messages.Pointage Table Reports') }}</h2>
                <h5>{{ __('messages.From label') }} {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} {{ __('messages.To label') }} {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</h5>
            </div>
        </div>
    </div>
    <br><br><br>
    @php
        $grandTotalDuration = 0;
        $grandTotalOperatingCost = 0;
        $grandTotalRecette = 0;
        
        // Group tasks by date
        $groupedTasks = $tasks_timer->groupBy(function($task) {
            return \Carbon\Carbon::parse($task->start_date)->format('Y-m-d');
        });
    @endphp

    @foreach($groupedTasks as $date => $dayTasks)
        <h4>{{ \Carbon\Carbon::parse($date)->format('l, d-m-Y') }}</h4>
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.Project Name column') }}</th>
                    <th>{{ __('messages.Task Name column') }}</th>
                    <th>{{ __('messages.Employee Name column') }}</th>
                    <th>{{ __('messages.Comment') }}</th>
                    <th>{{ __('messages.Start Date column pointage') }}</th>
                    <th>{{ __('messages.End Date column pointage') }}</th>
                    <th>{{ __('messages.Duration column pointage') }}</th>
                    <th>{{ __('messages.Operating Cost') }}</th>
                    <th>{{ __('messages.Sales column') }}</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $dailyTotalDuration = 0;
                    $dailyTotalOperatingCost = 0;
                    $dailyTotalRecette = 0;
                @endphp

                @foreach($dayTasks as $task)
                    <tr>
                        <td>
                            @php
                                $projectId = $task->project_id ?: App\Models\Tasks::where('id', $task->task_id)->value('project_id');
                                $project = $projectId ? App\Models\Project::find($projectId) : null;
                            @endphp
                            {{ $project ? $project->name : '' }}
                        </td>
                        <td>{{$task->task_name}}</td>
                        <td>
                            @php
                                $employee = App\Models\Employees::where('id', $task->employee_id)->first();
                            @endphp
                            {{ $employee ? $employee->name : '-' }}
                        </td>
                        <td>{{ $task->comment ?? '-' }}</td>
                        <td>{{$task->start_date}}</td>
                        <td>{{$task->end_date}}</td>
                        <td>
                            @php
                                $duration = (int) ($task->duration ?? 0);
                                $hours = floor($duration / 60);
                                $minutes = $duration % 60;
                                $dailyTotalDuration += $duration;
                                $grandTotalDuration += $duration;
                            @endphp
                            {{ sprintf("%02dh:%02dm", $hours, $minutes) }}
                        </td>
                        <td>
                            @php
                                $taskCost = (float) ($task->cost ?? 0);
                                $dailyTotalOperatingCost += $taskCost;
                                $grandTotalOperatingCost += $taskCost;
                            @endphp
                            {{ number_format($taskCost, 2, ',', ' ') }}€
                        </td>
                        <td>
                            @php
                                // Safely handle missing project or missing cost_per_hour
                                $costPerHour = (float) ($project && isset($project->cost_per_hour) ? $project->cost_per_hour : 0);
                                $dailyRecette = ($duration / 60) * $costPerHour;
                                $dailyTotalRecette += $dailyRecette;
                                $grandTotalRecette += $dailyRecette;
                            @endphp
                            {{ number_format((float) $dailyRecette, 2, ',', ' ') }}€
                        </td>
                    </tr>
                @endforeach
                
                <!-- Daily Totals -->
                <tr style="background-color: #f8f9fa;">
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="border: none;"></td>
                    <td style="text-align: right; border: none;"><strong>{{ __('messages.Daily Totals:') }}</strong></td>
                    <td>
                        @php
                            $dailyHours = floor($dailyTotalDuration / 60);
                            $dailyMinutes = $dailyTotalDuration % 60;
                        @endphp
                        <strong>{{ sprintf("%02dh:%02dm", $dailyHours, $dailyMinutes) }}</strong>
                    </td>
                    <td><strong>{{ number_format((float) $dailyTotalOperatingCost, 2, ',', ' ') }}€</strong></td>
                    <td><strong>{{ number_format((float) $dailyTotalRecette, 2, ',', ' ') }}€</strong></td>
                </tr>
            </tbody>
        </table>
    @endforeach

    <!-- Grand Totals -->
    <table>
        <tbody>
            <tr style="background-color: #e9ecef;">
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="border: none;"></td>
                <td style="text-align: right; border: none;"><strong>{{ __('messages.Grand Totals:') }}</strong></td>
                <td style="border: 1px solid black;">
                    @php
                        $grandTotalHours = floor($grandTotalDuration / 60);
                        $grandTotalMinutes = $grandTotalDuration % 60;
                    @endphp
                    <strong>{{ sprintf("%02dh:%02dm", $grandTotalHours, $grandTotalMinutes) }}</strong>
                </td>
                <td style="border: 1px solid black;"><strong>{{ number_format((float) $grandTotalOperatingCost, 2, ',', ' ') }}€</strong></td>
                <td style="border: 1px solid black;"><strong>{{ number_format((float) $grandTotalRecette, 2, ',', ' ') }}€</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <div class='footer-right'>{{ __('messages.Page Number:') }} <span class="page-number"></span></div>
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
