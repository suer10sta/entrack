<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teams Report</title>
    <style>
        /* [MODIFIED] Consistent Font and Size */
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px;
            color: #333333;
            background-color: #FFFFFF;
            margin: 0;
            padding: 0;
        }

        /* [NEW] Consistent Wrapper */
        .report-wrapper {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px 100px 20px;
            position: relative;
        }

        .header {
            width: 100%;
            text-align: center;
            margin-bottom: 30px;
        }

        .details {
            width: 100%;
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
            padding-bottom: 10px;
        }

        .details-left {
            width: 40%;
            font-size: 12px;
        }

        .details-right {
            width: 58%;
            font-size: 12px;
            text-align: right;
            position: absolute;
            right: 0;
            top: 0;
        }
        
        .header-info h2 {
            margin-top: 0;
            margin-bottom: 5px;
        }
        
        .header-info h4 {
            margin-top: 0;
            margin-bottom: 0;
            font-weight: normal;
        }

        .company-logo {
            width: 100px;
            height: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            margin-top: 20px;
            table-layout: fixed; 
        }

        table, th, td {
            border: 1px solid #CCCCCC;
        }

        th, td {
            padding: 6px 5px;
            text-align: left;
            font-size: 11px;
            vertical-align: top;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        /* [MODIFIED] Fixed Footer */
        .footer {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border-top: 1px solid #000;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: white;
        }

        .footer-content {
            position: relative;
            width: 100%;
            height: 50px;
            display: flex;
            justify-content: space-between;
        }

        .footer-left { position: absolute; left: 0; text-align: left; }
        .footer-center { position: absolute; left: 50%; transform: translateX(-50%); text-align: center; }
        .footer-right { position: absolute; right: 0; text-align: right; }

        .footer-text {
            color: #333333;
            line-height: 1.4;
            font-size: 10px;
        }

        @media print {
            body { margin: 0; padding: 0; }
            .report-wrapper { width: 100%; max-width: 100%; padding: 20px; margin: 0; border: none; }
            .footer { position: fixed; bottom: 0; width: 100%; }
            thead { display: table-header-group; }
            tr { page-break-inside: avoid; }
        }
    </style>
</head>

<body>
    <div class="report-wrapper">

        <div class="details">
            <div class="details-left">
                @if(Auth::user()->entreprise && Auth::user()->entreprise->image)
                    <img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
                @endif
            </div>
            <div class="details-right">
                <div class="header-info">
                    <h2>Teams Table Reports</h2>
                    <h4>Number of Teams: {{ $teams->count() }}</h4>
                </div>
            </div>
        </div>

        <div style="height: 20px;"></div>

        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Name</th>
                    <th style="width: 25%;">Members</th>
                    <th style="width: 25%;">Projects</th>
                    <th style="width: 30%;">Tasks</th>
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
                        <div>- {{ $member->employee_name }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($project_teams->where('team_id', $team->id) as $project)
                        <div>- {{App\Models\Project::where('id',$project->project_id)->first()->name}}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach(App\Models\team_tasks::where('team_id', $team->id)->get() as $task)
                        <div>- {{$task->task_name}}</div>
                        @endforeach
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

    </div>

    <div class="footer">
        <div class="footer-content">
            <div class="footer-left footer-text">
                @if(Auth::user()->entreprise)
                    <b>{{ Auth::user()->entreprise->name }}</b><br>
                    <b>Numéro TVA:</b> {{ Auth::user()->entreprise->tva}}
                @endif
            </div>
            <div class="footer-center footer-text">
                @if(Auth::user()->entreprise)
                    {{ Auth::user()->entreprise->Address }}<br>
                    Tél: 0{{ Auth::user()->entreprise->phone }}
                @endif
            </div>
            <div class="footer-right footer-text">
                @if(Auth::user()->entreprise)
                    <b>BANK</b> : {{ Auth::user()->entreprise->bank_name }}<br>
                    <b>IBAN</b> : {{ Auth::user()->entreprise->iban }}<br>
                    <b>SWIFT/BIC</b> : {{ Auth::user()->entreprise->swift }} / {{ Auth::user()->entreprise->bic }}
                @endif
                <br>
                <span style="font-size: 9px; color: #666;">Printed: {{ date('d-m-Y') }}</span>
            </div>
        </div>
    </div>
</body>
</html>
