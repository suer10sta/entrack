<style>
    /* --- Base & Typography --- */
    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        font-size: 12px;
        line-height: 1.5;
        margin: 0;
        padding: 0;
        color: #333;
    }

    .container {
        width: 100%;
        padding: 20px;
    }

    h2,
    h5 {
        margin: 0 0 5px 0;
    }

    h2 {
        font-size: 18px;
    }

    h5 {
        font-size: 14px;
        font-weight: normal;
        color: #555;
    }

    /* --- Header / Details --- */
    .details {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 30px;
        /* Replaces the <br> tags */
    }

    .company-logo {
        width: 80px;
        height: auto;
    }

    .details-right {
        text-align: right;
    }

    /* --- Table Styles --- */
    table {
        width: 100%;
        border-collapse: collapse;
        /* Key for boxed borders */
        margin-bottom: 60px;
        /* Space before footer */
    }

    /* Table Header */
    thead {
        background-color: #007bff;
        /* Blue color */
        color: white;
    }

    th {
        border: 1px solid #0056b3;
        /* Darker blue border */
        padding: 10px;
        font-size: 11px;
        font-weight: 600;
        text-align: left;
        /* As in original */
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Table Body */
    td {
        border: 1px solid #ddd;
        /* Light gray border for cells */
        padding: 10px;
        text-align: left;
        font-size: 11px;
        vertical-align: top;
        /* Aligns list content to the top */
    }

    /* --- Footer --- */
    .footer {
        position: fixed;
        /* Sticks to the bottom of the page */
        bottom: 0;
        left: 0;
        width: 100%;
        display: flex;
        justify-content: space-between;
        font-size: 12px;
        color: #777;
        padding: 15px 20px;
        box-sizing: border-box;
        /* Include padding in width */
        border-top: 1px solid #eee;
        background: #fff;
    }

    /* CSS counter for print/PDF page numbers */
    .page-number::before {
        content: counter(page);
    }
</style>

<div class="container">

    <div class="details">
        <div class="details-left">
            <img src="{{ Storage::disk('do_spaces')->url(Auth::user()->entreprise->image) }}" alt="Company Logo"
                class="company-logo">
        </div>
        <div class="details-right">
            <div class="header-info">
                <h2>Teams Table Reports</h2>
                <h5>Number of Teams: {{ $teams->count() }}</h5>
            </div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>members</th>
                <th>Projects</th>
                <th>Tasks</th>
            </tr>
        </thead>
        <tbody>
            @foreach($teams as $team)
            <tr>
                <td>{{$team->name}}</td>
                <td>
                    @php
                    $members = App\Models\team_members::where('team_id', $team->id)->get();
                    @endphp
                    @foreach($members as $member)
                    <div>{{ $member->employee_name }}</div>
                    @endforeach
                </td>
                <td>
                    @foreach($project_teams->where('team_id', $team->id) as $project)

                    <div>{{App\Models\Project::where('id',$project->project_id)->first()->name}}</div>

                    @endforeach
                </td>
                <td>
                    @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                    <div>{{$task->task_name}}</div>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class='footer-left'>Download Date: {{ date('d-m-Y') }}</div>
        <div class='footer-right'>Page Number: <span class="page-number"></span></div>
    </div>

</div>