<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
    body {
        font-family: Tahoma, sans-serif;
        font-size: 12px;
        color: #333333;
        background-color: #FFFFFF;
        margin: 0;
        padding: 0;
        position: relative;
        min-height: 100vh;
    }

    .container {
        width: 595px;
        margin: 0 auto;
        padding: 20px;
        padding-bottom: 100px;
        /* to ensure there's space for the footer */
    }



    .header {
        width: 100%;
        text-align: center;
        margin-bottom: 20px;
    }

    .header img {
        width: 100px;
        height: auto;
    }

    .header div {
        font-size: 14px;
        font-weight: bold;
    }

    .invoice-number {
        text-align: center;
        font-size: 14px;
        font-weight: bold;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }

    table,
    th,
    td {
        border: 1px solid #CCCCCC;
    }

    th,
    td {
        padding: 8px;
        text-align: left;
        font-size: 12px;
    }

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    .totals {
        margin-top: 20px;
        text-align: right;
        font-size: 12px;
    }

    .totals .label,
    .totals .value {
        display: inline-block;
        width: 50%;
    }

    .totals .value {
        font-weight: bold;
    }

    .description {
        width: 100%;
        text-align: justify;
        font-size: 12px;
        border-top: 2px double #CCCCCC;
        border-bottom: 2px double #CCCCCC;
        padding: 10px 0;
        margin-bottom: 20px;
    }



    .payment {
        width: 100%;
        text-align: justify;
        font-size: 12px;
        margin-bottom: 20px;
    }

    .footer {
        position: fixed;
        bottom: 0;
        width: 100%;
        border-top: 1px solid #000;
        padding-top: 20px;
        padding-bottom: 20px;
    }

    .task-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        padding: 0 8px;
    }

    .task-item p {
        margin: 0;
    }

    .task-item .task-name {
        flex: 1;
        position: relative;
    }

    .task-item .task-name::after {
        content: "";
        position: absolute;
        left: 100%;
        width: 100%;
        height: 1px;
        background: repeating-linear-gradient(to right, #333, #333 1px, transparent 1px, transparent 4px);
        margin-left: 10px;
        top: 50%;
        transform: translateY(-50%);
    }

    .task-item .task-budget {
        text-align: right;
        min-width: 100px;
        /* Adjust as needed */
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
    .logo-right {
        text-align: left;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header logo-right">
                <img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
            </div> 
        </div>

        <br><br><br><br>

        <div class="details">
            <div class="details-left">
                <p>Date de la note de frais: <?php echo date('d-m-Y'); ?><br />
                </p>
            </div>
            <div class="details-right">
                Employé : {{ $employee_name }}<br />
            </div>
        </div>

        <div class="invoice-number">
            <h2>Note de Frais : {{$entreprise->notedefraisPrefix}}-{{date('ym')}}{{$note_number}}</h2>
        </div>
<br><br>
@php 
    $Total_quantity = 0;
@endphp
        <table>
            <thead>
                <tr>
                    <th>Details </th>
                    <th>Quantité</th>
                    <th>Frais</th>
                </tr>
            </thead>
            <tbody>
            @foreach ($tasks as $index => $task)
                <tr>
                    <td>{{ $task }}</td>
                    <td>
                        {{$quantity[$index]}}
                        @php 
                            $Total_quantity += $quantity[$index];
                        @endphp
                    </td>
                    <td>{{$taskBudget[$index]}}€</td>
                </tr>
            @endforeach
            <tr>
                <td><strong>Total : </strong></td>
                <td><strong>{{$Total_quantity}}</strong></td>
                <td><strong>{{$budget}}€</strong></td>
            </tr>
            </tbody>
            </table>

            <h3>Description : </h3>

            <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
                @if(Auth::user()->hasRole('Admin'))
                    <form action="{{ route('admin.notedefrais.confirmFraisNote') }}" method="POST" style="display: inline-block;">
                        @csrf
                        <input type="hidden" name="employee_name" value="{{ $employee_name }}">
                        <input type="hidden" name="budget" value="{{ $budget }}">
                        <input type="hidden" name="description" value="{{ $description }}">
                        <input type="hidden" name="entreprise" value="{{ $entreprise }}">
                        <input type="hidden" name="note_number" value="{{ $note_number }}">
                        <input type="hidden" name="tasks" value="{{ json_encode($tasks) }}">
                        <input type="hidden" name="quantity" value="{{ json_encode($quantity) }}">
                        <input type="hidden" name="taskBudget" value="{{ json_encode($taskBudget) }}">
                        <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                            Confirm 
                        </button>
                    </form>
                    <a href="{{route('admin.notedefrais.notedefraishistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                        Decline
                    </a>
                @endif
                @if(Auth::user()->hasRole('Responsable de projet'))
                    <form action="{{ route('Responsable_de_projet.notedefrais.confirmFraisNote') }}" method="POST" style="display: inline-block;">
                        @csrf
                        <input type="hidden" name="employee_name" value="{{ $employee_name }}">
                        <input type="hidden" name="budget" value="{{ $budget }}">
                        <input type="hidden" name="description" value="{{ $description }}">
                        <input type="hidden" name="entreprise" value="{{ $entreprise }}">
                        <input type="hidden" name="note_number" value="{{ $note_number }}">
                        <input type="hidden" name="tasks" value="{{ json_encode($tasks) }}">
                        <input type="hidden" name="quantity" value="{{ json_encode($quantity) }}">
                        <input type="hidden" name="taskBudget" value="{{ json_encode($taskBudget) }}">
                        <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                            Confirm 
                        </button>
                    </form>
                    <a href="{{route('Responsable_de_projet.notedefrais.notedefraishistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                        Decline
                    </a>
                @endif
                @if(Auth::user()->hasRole('Employee'))
                    <form action="{{ route('employee.notedefrais.confirmFraisNote') }}" method="POST" style="display: inline-block;">
                        @csrf
                        <input type="hidden" name="employee_name" value="{{ $employee_name }}">
                        <input type="hidden" name="budget" value="{{ $budget }}">
                        <input type="hidden" name="description" value="{{ $description }}">
                        <input type="hidden" name="entreprise" value="{{ $entreprise }}">
                        <input type="hidden" name="note_number" value="{{ $note_number }}">
                        <input type="hidden" name="tasks" value="{{ json_encode($tasks) }}">
                        <input type="hidden" name="quantity" value="{{ json_encode($quantity) }}">
                        <input type="hidden" name="taskBudget" value="{{ json_encode($taskBudget) }}">
                        <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                            Confirm 
                        </button>
                    </form>
                    <a href="{{route('employee.notedefrais.notedefraishistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                        Decline
                    </a>
                @endif
                @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    <form action="{{ route('Responsable_gestionnaire.notedefrais.confirmFraisNote') }}" method="POST" style="display: inline-block;">
                        @csrf
                        <input type="hidden" name="employee_name" value="{{ $employee_name }}">
                        <input type="hidden" name="budget" value="{{ $budget }}">
                        <input type="hidden" name="description" value="{{ $description }}">
                        <input type="hidden" name="entreprise" value="{{ $entreprise }}">
                        <input type="hidden" name="note_number" value="{{ $note_number }}">
                        <input type="hidden" name="tasks" value="{{ json_encode($tasks) }}">
                        <input type="hidden" name="quantity" value="{{ json_encode($quantity) }}">
                        <input type="hidden" name="taskBudget" value="{{ json_encode($taskBudget) }}">
                        <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                            Confirm 
                        </button>
                    </form>
                    <a href="{{route('Responsable_gestionnaire.notedefrais.notedefraishistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                        Decline
                    </a>
                @endif
            </div>

        <style>
        .detailsTab  {
            border: none;
        }
        </style>
        
        <style>
        .totals  {
            border: none;
        }
        </style>

        <div class="description">
            {{ $description }}
        </div>

        <div class="payment">
           
        </div>
    </div>

    <div class="footer">
        <div style="float: left; width: 30%; height: 80%;text-align: center;">
            <b>{{ $entreprise->name }}</b>
            <br>
            <b>Numéro TVA:</b> {{ $entreprise->tva}}
        </div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
            {{ $entreprise->Address }}
            <br>
            Tél: 0{{ $entreprise->phone }}
        </div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
            <b>BANK</b> : {{ $entreprise->bank_name }} <br>
            <b>IBAN</b> : {{ $entreprise->iban }} <br>
            <b>SWIFT/BIC</b> : {{ $entreprise->swift }} / {{ $entreprise->bic }}
        </div>
    </div>
</body>

</html>
