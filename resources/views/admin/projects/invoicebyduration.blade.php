<html xmlns='https://www.w3.org/1999/xhtml' lang="{{ app()->getLocale() }}">

<head>
    <x-favicon />
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    @if(session('error'))
        <script>
            alert(@json(session('error')));
        </script>
    @endif
    <style>
    .logo-right {
        text-align: left;
    }
</style>
<body style='font-family:Tahoma;font-size:12px;color: #333333;background-color:#FFFFFF;'>
    <table align='center' border='0' cellpadding='0' cellspacing='0' style='height:842px; width:595px;font-size:12px;'>
        <tr>
            <td valign='top'>
                <table width='100%' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td valign='bottom'>
                        <div class="header logo-right">
                                @if(filled(Auth::user()->entreprise?->image) && Storage::disk('public')->exists(Auth::user()->entreprise->image))
<img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" style="width:130px" class="company-logo">
@endif
                            </div><br />
                        </td>

                        <td width='50%'>&nbsp;</td>
                    </tr>
                    <table width='100%' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td valign='top' width='30%' style='font-size:12px;'>{{ __('messages.Invoice Date') }}:
                                <?php echo date('d-m-Y'); ?><br />
                                {{ __('messages.Due Date') }}:
                                {{ \Carbon\Carbon::now()->addDays(abs((int)filter_var($client->due_date, FILTER_SANITIZE_NUMBER_INT)))->format('d-m-Y') }}
                                <br>
@if(filled($client->client_number))
                                {{ __('messages.Client Number') }} : {{ $client->client_number }} <br>
@endif
                                {{ __('messages.Invoice Number') }} : {{ $invoice->number ?? $invoice_id }}
                            </td>
                            <td valign='top' width='35%'>
                            </td>
                            <td valign='top' width='35%' style='font-size:12px;'>
                                @include('partials.invoice-client-details')
                            </td>
                        </tr>
                    </table>

                    <table width='100%' height='100' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td>
                                @php
                                $invoicenumber = '';
                                if(request()->has('invoice_number')) {
                                $invoicenumber = request()->input('invoice_number');
                                }
                                @endphp
                                <div align='center' style='font-size: 14px;font-weight: bold;'>{{ __('messages.Invoice') }} : {{$entreprise->invoice_prefix}}-{{ $invoice->number ?? $invoice_id }}</div>
                            </td>
                        </tr>
                    </table>
                    <table width='100%' cellspacing='0' cellpadding='2' border='1' bordercolor='#CCCCCC'>
                        <tr>

                            <td width='35%' bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'>
                                <strong>{{ __('messages.Designation') }}
                                </strong>
                            </td>
                            <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Duration') }}</strong>
                            </td>
                            <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Unit Price') }}</strong></td>
                            <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Amount') }}</strong>
                            </td>


                        </tr>
                        <tr style="display:none;">
                            <td colspan="*">
                        <tr>

                            <td valign='top' style='font-size:12px;'>{{strtoupper($projectinfos->name)}}</td>

                            <td valign='top' style='font-size:12px;'>
                                <span
                                    style="display: inline-block; overflow: hidden; text-overflow: ellipsis;">
                                    <?php
                                      // Convert duration from minutes to hours:minutes format
                                      $durationMinutes = $duration;
                                      $hours = floor($durationMinutes / 60);
                                      $minutes = $durationMinutes % 60;
                                      echo $hours . 'h:' . sprintf('%02d', $minutes) . 'm';
                                    ?>
                                </span>
                            </td>

                            <td valign='top' style='font-size:12px;'>
                                <span>{{$projectinfos->cost_per_hour}}€</span>
                            </td>

                            <td valign='top' style='font-size:12px;'>
                                @php
                                $hours = round($duration/60, 2);
                                $prixtotal = $projectinfos->cost_per_hour * $hours;
                                @endphp
                                <span>{{ number_format($prixtotal, 2, ',', '') }}€</span>
                            </td>

                        </tr>
            </td>
        </tr>
    </table>
    <table width='100%' cellspacing='0' cellpadding='2' border='0'>
        <tr>
            <td style='font-size:12px;width:50%;'><strong> </strong></td>
            <td>
                <table width='100%' cellspacing='0' cellpadding='2' border='0'>
                    <tr>
                        <td align='right' style='font-size:12px;'>{{ __('messages.Total Excl VAT') }}</td>
                        <td align='right' style='font-size:12px;'>{{ number_format($prixtotal, 2, ',', '') }}€
                        <td>
                    </tr>
                    <tr>
                        <td align='right' style='font-size:12px;'>{{ __('messages.VAT') }}
                            
                            {{$projectinfos->tva}}%</td>
                        <td align='right' style='font-size:12px;'>{{ number_format($prixtotal*($projectinfos->tva/100), 2, ',', '') }}€
                        <td>
                    </tr>
                    <tr>

                        <td align='right' style='font-size:12px;'><b>{{ __('messages.Total Incl VAT') }}</b></td>
                        <td align='right' style='font-size:12px;'><b>{{number_format($prixtotal + ($prixtotal*($projectinfos->tva/100)), 2, ',', '')}}€</b></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table width='100%' height='50'>
        <tr>
            <td style=' font-size:12px;text-align:justify ;border-top:double;border-bottom:double'>
@if(filled($projectinfos->description))
                <strong> - {{ __('messages.Information') }} :</strong> <br>
                {!! nl2br(e($projectinfos->description)) !!} <br>
@endif
@if(filled($comment ?? null))
                <strong> - {{ __('messages.Comment') }} : </strong> <br>
               <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>   
@endif
            </td>
        </tr>
    </table>
    <br>

    <table width='100%' cellspacing='0' cellpadding='2' border='1' bordercolor='#CCCCCC'>
  <thead>
        <td bordercolor=' #ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Task Name') }}</strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Project / Subproject') }}</strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Comment') }}</strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Start Date') }} </strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.End Date') }}</strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Duration') }}</strong></td>

        </thead>
        <tbody>
            @php
            $totalMinutes = 0; // Initialize total duration in minutes
            @endphp
            @foreach($collection as $coll)
            <tr>
                <td valign='top' style='font-size:12px;'>{{$coll->task_name}}</td>
                <td valign='top' style='font-size:12px;'>{{ $coll->project_name ?? $projectinfos->name }}</td>
                <td valign='top' style='font-size:12px;'>{{ $coll->comment }}</td>
                <td valign='top' style='font-size:12px;'>{{ \Carbon\Carbon::parse($coll->start_date)->format('d-m-Y H:i') }}</td>
                <td valign='top' style='font-size:12px;'>{{ \Carbon\Carbon::parse($coll->end_date)->format('d-m-Y H:i') }}</td>
                <td valign='top' style='font-size:12px;'>
                    <?php
                // Convert duration from minutes to hours:minutes format
                $durationMinutes = $coll->duration;
                $totalMinutes += $durationMinutes; // Add current duration to total
                $hours = floor($durationMinutes / 60);
                $minutes = $durationMinutes % 60;
                echo $hours . 'h:' . sprintf('%02d', $minutes) . 'm';
            ?>
                </td>
            </tr>
            @endforeach
            <tr>
                <td colspan="5" style='font-size:12px; text-align: right;'><strong>{{ __('messages.Total Duration') }}:</strong></td>
                <td valign='top' style='font-size:12px;'>
                    {{ floor($totalMinutes / 60) }}h:{{ sprintf('%02d', $totalMinutes % 60) }}m</td>
            </tr>
        </tbody>


    </table>

    <table width='100%' height='50'>
        <tr>
            <td style=' font-size:12px;text-align:justify ;'>
                - {{ __('messages.Please pay the amount of') }}
                <strong>{{number_format($prixtotal + ($prixtotal*($projectinfos->tva/100)), 2, ',', '')}}€</strong> {{ __('messages.on the account mentioned below before') }}
                {{ \Carbon\Carbon::now()->addDays(abs((int)filter_var($client->due_date, FILTER_SANITIZE_NUMBER_INT)))->format('d-m-Y') }}
                {{ __('messages.mentioning the invoice number') }} <strong>{{ $invoice->number ?? $invoice_id }}</strong>
                <br>
@if(\App\Support\InvoicePresentation::showReverseChargeNotice($entreprise))
                - {{ __('messages.Intracommunity delivery services note') }}
@endif
                <br>
                - {{ __('messages.See tasks table in annex') }}
                <br>
                - {{ __('messages.See general conditions in annex') }}
                <br>
                - {{ __('messages.Thank you for your trust') }}
            </td>
        </tr>
    </table>
    <div style="display: flex; justify-content: center; align-items: center; flex-direction: row; gap: 12px;">
        @php
            $taskIdsParam = request()->query('taskIds');
            $devisIdParam = request()->query('devis_id');
            $querySuffix = '';
            if ($taskIdsParam) {
                $querySuffix .= ($querySuffix === '' ? '?' : '&') . 'taskIds=' . urlencode($taskIdsParam);
            }
            if ($devisIdParam) {
                $querySuffix .= ($querySuffix === '' ? '?' : '&') . 'devis_id=' . urlencode($devisIdParam);
            }
        @endphp
        @if(Auth::user()->hasRole('Admin'))
            <form action="{{ route('admin.projects.confirmPeriodInvoice', ['projectID' => $projectinfos->id, 'startdate' => $startdate, 'enddate' => $enddate, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
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
            <form action="{{ route('Responsable_de_projet.projects.confirmPeriodInvoice', ['projectID' => $projectinfos->id, 'startdate' => $startdate, 'enddate' => $enddate, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
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
            <form action="{{ route('employee.projects.confirmPeriodInvoice', ['projectID' => $projectinfos->id, 'startdate' => $startdate, 'enddate' => $enddate, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
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
            <form action="{{ route('Responsable_gestionnaire.projects.confirmPeriodInvoice', ['projectID' => $projectinfos->id, 'startdate' => $startdate, 'enddate' => $enddate, 'comment' => base64_encode($comment)]) }}{{ $querySuffix }}" method="POST" style="display: inline-block;">
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


    <table width='100%' cellspacing='0' cellpadding='2'>
        <tr>
            <td width='33%' style='border-top:double medium #CCCCCC;font-size:12px;' valign='top'>
@include('partials.invoice-company-details', ['section' => 'identity'])
</td>
            <td width='33%' style='border-top:double medium #CCCCCC; font-size:12px;' align='center' valign='top'>
@include('partials.invoice-company-details', ['section' => 'contact'])
</td>

            <td valign='top' width='34%' style='border-top:double medium #CCCCCC;font-size:12px;' align='right'>
@include('partials.invoice-company-details', ['section' => 'bank'])
</td>
        </tr>
    </table>
    </td>
    </tr>
    

       
    </table>
</body>

</html>
