<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <x-favicon />
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(session('error'))
        <script>
            alert(@json(session('error')));
        </script>
    @endif
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
    .logo-right {
        text-align: left;
    }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <div class="header logo-right">
@if(filled(Auth::user()->entreprise?->image) && Storage::disk('public')->exists(Auth::user()->entreprise->image))
@if(filled(Auth::user()->entreprise?->image) && Storage::disk('public')->exists(Auth::user()->entreprise->image))
            <img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo">
@endif
@endif
            </div>
        </div>

        <br><br><br><br>

        <div class="details">
            <div class="details-left">
                <p>{{ __('messages.Invoice Date') }}: <?php echo date('d-m-Y'); ?><br />
                    {{ __('messages.Due Date') }}:
                    {{ \Carbon\Carbon::now()->addDays(abs((int)filter_var($client->due_date, FILTER_SANITIZE_NUMBER_INT)))->format('d-m-Y') }}
                    <br>
@if(filled($client->client_number))
                    {{ __('messages.Client Number') }} : {{ $client->client_number }} <br>
@endif
                    {{ __('messages.Invoice Number') }} : {{ $invoice->invoice_reference ?? $invoice->number ?? \App\Support\InvoiceNumbering::preview($entreprise, $invoice_id) }}
                    @php
                        $devis_number = null;
                        if(request()->has('devis_id') && !empty(request()->query('devis_id'))){
                            $dv = \App\Models\devis_history::find((int) request()->query('devis_id'));
                            $devis_number = $dv ? $dv->devis_number : null;
                        }
                    @endphp
                    @if(!empty($devis_number))<br>
                    {{ __('messages.Quote Balance') }} : {{$entreprise->devis_prefix}}-{{$devis_number}}
                    @endif
                </p>
            </div>
            <div class="details-right">
                @include('partials.invoice-client-details')
            </div>
        </div>

        <br><br>
        @php
        $invoicenumber = '';
        if(request()->has('invoice_number')) {
        $invoicenumber = request()->input('invoice_number');
        }
        @endphp
        <div class="invoice-number">
            {{ __('messages.Invoice') }} : {{ \App\Support\InvoiceNumbering::reference($entreprise, $invoice ?? $invoice_id) }}
        </div>

        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.Designation') }}</th>
                    <th>{{ __('messages.Quantity') }}</th>
                    <th>{{ __('messages.Principal Amount') }}</th>
                    <th>{{ __('messages.Advance Payment') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{strtoupper($projectinfos->name)}}</td>
                    <td>
                        {{ __('messages.public_pricing_users_solo') }}
                    </td>
                    <td>{{ isset($principalBudget) ? $principalBudget : $projectinfos->budget }}€</td>
                    <td>
                        {{$amount}}€
                    </td>
                </tr>
            </tbody>
        </table>

        <style>
        .totals {
            border: none;
        }
        </style>

        <table class="totals">
            <tbody>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">{{ __('messages.Total Excl VAT') }}</div>
                        <div class="value">{{$amount}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">{{ __('messages.VAT') }} {{ $projectinfos->tva }}%
                        </div>
                        <div class="value">{{number_format($amount* ($projectinfos->tva / 100), 2, ',', '')}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">{{ __('messages.Total Incl VAT') }}</div>
                        <div class="value"><b>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</b></div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="description">
@if(filled($projectinfos->description))
            <strong> - {{ __('messages.Information') }} :</strong> <br>
            {!! nl2br(e($projectinfos->description)) !!} <br>
@endif
@if(filled($comment ?? null))
            <strong> - {{ __('messages.Comment') }} : </strong> <br>
           <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
@endif
        </div>

        <div class="payment">
            - {{ __('messages.Please pay the amount of') }}
            <strong>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</strong> {{ __('messages.on the account mentioned below before') }}
            {{ \Carbon\Carbon::now()->addDays(abs((int)filter_var($client->due_date, FILTER_SANITIZE_NUMBER_INT)))->format('d-m-Y') }}
            {{ __('messages.mentioning the invoice number') }} <strong>{{ $invoice->invoice_reference ?? $invoice->number ?? \App\Support\InvoiceNumbering::preview($entreprise, $invoice_id) }}</strong>
            <br>
@if(\App\Support\InvoicePresentation::showReverseChargeNotice($entreprise))
@if(\App\Support\InvoicePresentation::showReverseChargeNotice($entreprise))
            - {{ __('messages.In case of intra-community delivery for services') }}:  "Autoliquidation Art 21 § 2 du code TVA belge"
@endif
@endif
            <br>
            - {{ __('messages.See general conditions in annex') }}
            <br>
            - {{ __('messages.Thank you for your trust') }}

        </div>
        <div style="display: flex; justify-content: center; align-items: center; flex-direction: row; gap: 12px;">
        @php
            $devisIdParam = request()->query('devis_id');
            $querySuffix = $devisIdParam ? ('?devis_id=' . urlencode($devisIdParam)) : '';
        @endphp
        @if(Auth::user()->hasRole('Admin'))
            <form action="{{ route('admin.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }} 
                </button>
            </form>
            <form action="{{ route('admin.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    {{ __('messages.Decline') }} 
                </button>
            </form>
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
            <form action="{{ route('Responsable_de_projet.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }} 
                </button>
            </form>
            <form action="{{ route('Responsable_de_projet.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    {{ __('messages.Decline') }} 
                </button>
            </form>
        @endif
        @if(Auth::user()->hasRole('Employee'))
            <form action="{{ route('employee.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }} 
                </button>
            </form>
            <form action="{{ route('employee.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    {{ __('messages.Decline') }} 
                </button>
            </form>
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            <form action="{{ route('Responsable_gestionnaire.projects.confirmPartialInvoice', ['projectID' => $projectinfos->id, 'amount' => $amount, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }} 
                </button>
            </form>
            <form action="{{ route('Responsable_gestionnaire.projects.declineInvoice') }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                    {{ __('messages.Decline') }} 
                </button>
            </form>
        @endif
        </div>

    </div>


    <div class="footer">
        <div style="float: left; width: 30%; height: 80%;text-align: center;">
@include('partials.invoice-company-details', ['section' => 'identity'])
</div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
@include('partials.invoice-company-details', ['section' => 'contact'])
</div>
        <div style="float: left; margin-left: 5%; width: 30%; height: 80%; text-align: center;">
@include('partials.invoice-company-details', ['section' => 'bank'])
</div>
    </div>
</body>

</html>
rgin-left: 5%; width: 30%; height: 80%; text-align: center;">
            <b>{{ __('messages.BANK') }}</b> : {{ $entreprise->bank_name }} <br>
            <b>{{ __('messages.IBAN') }}</b> : {{ $entreprise->iban }} <br>
            <b>{{ __('messages.SWIFT/BIC') }}</b> : {{ $entreprise->swift }} / {{ $entreprise->bic }}
        </div>
    </div>
</body>

</html>
