<style>
body {
    font-family: Arial, sans-serif;
    margin-bottom: 10px; /* Reduced from 50px to 30px */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 5px;
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
    background-color: #686ce4;
    color: white;
}

.company-logo {
    width: 80px;
    height: auto;
    margin-right: 20px;
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

.footer {
    width: 100%;
    display: flex;
    justify-content: space-between;
    position: absolute;
    bottom: 5px; /* Changed from 0 to 10px */
    left: 0;
    padding: 5px; /* Reduced from 10px to 5px */
    font-size: 12px;
}

.footer-left {
    width: 48%;
}

.footer-right {
    width: 48%;
    text-align: right;
}

.page-number:before {
    content: counter(page);
}

@page {
    margin-bottom: 30px; /* Reduced from 50px to 30px */
}
</style>

<div class="containter">
    <div class="details">
        <div class="details-left">
            <img src="{{ storage_path('app/public/' . $entreprise->image) }}" alt="Company Logo" class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>{{ __('messages.Pointage Table Reports') }}</h2>
                <h5>
                    {{ __('messages.From label') }}
                    {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }}
                    {{ __('messages.To label') }}
                    {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                </h5>
            </div>
        </div>
    </div>
    <br><br><br>
    @php
        $grandTotalDuration = 0;
        $tasksByName = $tasks_timer->groupBy('task_id');
    @endphp

    @foreach($tasksByName as $taskName => $taskRows)
    @php $taskId = is_numeric($taskName) ? $taskName : ($taskRows->first()->task_id ?? $taskName); @endphp
    <h5>{{ __('messages.Task label') }} {{ $taskRows->first()->task_name ?? ('#' . $taskId) }}</h5>
    <table>
        <thead>
            <tr>
                <th>{{ __('messages.Project Name column') }}</th>
                <th>{{ __('messages.Start Date column pointage') }}</th>
                <th>{{ __('messages.End Date column pointage') }}</th>
                <th>{{ __('messages.Duration column pointage') }}</th>
            </tr>
        </thead>
        <tbody>
            @php
                $taskTotalDuration = 0;
            @endphp
            @foreach($taskRows as $task)
            <tr>
                <td>
                    @php
                        // Get the single project_id value instead of a collection
                        $projectId = App\Models\Tasks::where('id', $task->task_id)->value('project_id');
                        $project = $projectId ? App\Models\Project::find($projectId) : null;
                    @endphp
                    {{ $project?->name ?? __('messages.N/A') }}
                </td>
                <td>{{$task->start_date}}</td>
                <td>{{$task->end_date}}</td>
                <td>
                    @php
                        $hours = floor($task->duration / 60);
                        $minutes = $task->duration % 60;
                        $taskTotalDuration += $task->duration;
                        $grandTotalDuration += $task->duration;
                    @endphp
                    {{ sprintf("%02dh:%02dm", $hours, $minutes) }}
                </td>
            </tr>
            @endforeach
            <tr style="background-color: #f8f9fa;">
                <td colspan="3" style="text-align: right; border: none;"><strong>{{ __('messages.Task Total label') }}</strong></td>
                <td style="border: none;">
                    @php
                        $taskHours = floor($taskTotalDuration / 60);
                        $taskMinutes = $taskTotalDuration % 60;
                    @endphp
                    <strong>{{ sprintf("%02dh:%02dm", $taskHours, $taskMinutes) }}</strong>
                </td>
            </tr>
        </tbody>
    </table>
    @endforeach

    <table>
        <tr style="background-color: #e9ecef;">
            <td colspan="3" style="text-align: right; border: none;"><strong>{{ __('messages.Grand Total label') }}</strong></td>
            <td style="border: 1px solid black;">
                @php
                    $grandHours = floor($grandTotalDuration / 60);
                    $grandMinutes = $grandTotalDuration % 60;
                @endphp
                <strong>{{ sprintf("%02dh:%02dm", $grandHours, $grandMinutes) }}</strong>
            </td>
        </tr>
    </table>

    <div class="footer">
        <div class='footer-left'>{{ __('messages.Download Date') }}: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>{{ __('messages.Page Number') }}: <span class="page-number"></span></div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const pages = document.querySelectorAll('.page');
    if (pages.length === 0) {
        console.error('No pages found. Ensure your HTML structure includes elements with a .page class.');
        return;
    }
});
</script>
