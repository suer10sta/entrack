<html xmlns='https://www.w3.org/1999/xhtml' lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-favicon />
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <style>
    .logo-right {
        text-align: left;
    }

    .payment {
            margin: 20px 0;
            padding: 10px;
            border-top: 1px solid #CCCCCC;
            border-bottom: 1px solid #CCCCCC;
        }
</style>
<body style='font-family:Tahoma;font-size:12px;color: #333333;background-color:#FFFFFF;'>
    @if(session('error'))
    <div role="alert" style="width: 555px; margin: 20px auto; background-color: #fdecea; border: 1px solid #f5c6cb; color: #b91c1c; padding: 10px 14px; border-radius: 6px; font-size: 13px; font-weight: bold;">
        {{ session('error') }}
    </div>
    @endif
    <table align='center' border='0' cellpadding='0' cellspacing='0' style='height:842px; width:595px;font-size:12px;'>
        <tr>
            <td valign='top'>
                <table width='100%' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td valign='bottom'>
                            <div class="header logo-right">
                            @if($entreprise->image)
                            <img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo" style="width: 100px;">
                    @endif
                            </div><br />
                        </td>

                        <td width='50%'>&nbsp;</td>
                    </tr>
                    <table width='100%' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td valign='top' width='30%' style='font-size:12px;'>{{ __('messages.Credit Note Date') }} :
                                <?php echo date('d-m-Y'); ?><br />
                                
                            </td>
                            <td valign='top' width='35%'>
                            </td>
                            <td valign='top' width='35%' style='font-size:12px;'>
                                <strong>{{ $client->name }}</strong><br />
                                {{ $client->billing_street }} <br />
                                {{ $client->billing_zip }}-{{ $client->billing_city }} <br />
                                {{ $client->billing_country }}<br/>
                                {{ __('messages.Client VAT') }}: {{ $client->TVA }}
                            </td>
                        </tr>
                    </table>
                    <br><br><br><br>

                    <table width='100%' height='100' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td>
                                @php
                                $invoicenumber = '';
                                if(request()->has('invoice_number')) {
                                $invoicenumber = request()->input('invoice_number');
                                }
                                @endphp
                                <div align='center' style='font-size: 14px;font-weight: bold;'> {{ __('messages.Credit Note') }} : NC-{{date('ym')}}{{ str_pad($last_credit_note_number + 1, 3, '0', STR_PAD_LEFT) }} 
                                      <br> {{ __('messages.Ref') }}: @if(!empty($invoice->invoice_reference)){{ $invoice->invoice_reference }}@else{{$entreprise->invoice_prefix}}-{{date('ym')}}@if($invoicenumber){{$invoicenumber}} @else{{$invoice_id}} @endif@endif</div>
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
                            @if($partial == 1)
                            <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Original Amount') }}</strong>
                            </td>
                            @endif


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
                                <span>-{{$projectinfos->cost_per_hour}}€</span>
                            </td>

                            <td valign='top' style='font-size:12px;'>
                                <span>-{{ number_format(abs($prixtotal), 2, ',', '') }}€</span>
                            </td>
                            @if($partial == 1)
                            <td valign='top' style='font-size:12px;'>
                                @php
                                    $hours = round($duration / 60, 2);
                                    $originalHtva = $projectinfos->cost_per_hour * $hours;
                                    $originalTva = $originalHtva * ($projectinfos->tva / 100);
                                @endphp
                                <span>{{ number_format(abs($originalHtva + $originalTva), 2, ',', '') }}€</span>
                            </td>
                            @endif

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
                        <td align='right' style='font-size:12px;'>-{{ number_format(abs($prixtotal), 2, ',', '') }}€
                        <td>
                    </tr>
                    <tr>
                        <td align='right' style='font-size:12px;'>{{ __('messages.VAT') }}
                            
                            {{$projectinfos->tva}}%</td>
                        <td align='right' style='font-size:12px;'>-{{ number_format(abs($prixtotal*($projectinfos->tva/100)), 2, ',', '') }}€
                        <td>
                    </tr>
                    <tr>

                        <td align='right' style='font-size:12px;'><b>{{ __('messages.Total Incl VAT') }}</b></td>
                        <td align='right' style='font-size:12px;'><b>-{{number_format(abs($prixtotal + (abs($prixtotal)*($projectinfos->tva/100))), 2, ',', '')}}€</b></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    @if($projectinfos->description || $comment)
    <table width='100%' height='50'>
        <tr>
            <td style=' font-size:12px;text-align:justify ;border-top:double;border-bottom:double'>
                @if($projectinfos->description)
                <strong> - {{ __('messages.Information') }} :</strong><br>
                {!! nl2br(e($projectinfos->description)) !!}<br>
                @endif
                @if($comment)
                <strong>{{ __('messages.Comment') }}:</strong><br>
                {!! nl2br(e($comment)) !!}
                @endif
            </td>
        </tr>
    </table>
    @endif

    <div class="payment">
            - {{ __('messages.This credit note cancels') }} @if($partial){{ __('messages.partially') }} @else {{ __('messages.totally') }} @endif {{ __('messages.the invoice') }} {{ $invoice->invoice_reference ?? (($entreprise->invoice_prefix ?? '').'-'.date('ym').$invoice->number) }}
            <br>
            - {{ __('messages.Intracommunity delivery services note') }}
            <br>
            - {{ __('messages.See general conditions in annex') }}
        </div>
    <br>

    <table width='100%' cellspacing='0' cellpadding='2' border='1' bordercolor='#CCCCCC' '>
  <thead>
    <td bordercolor=' #ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Task Name') }}</strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>{{ __('messages.Project / Subproject') }}</strong></td>
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
                <td colspan="4" style='font-size:12px; text-align: right;'><strong>{{ __('messages.Total Duration') }}:</strong></td>
                <td valign='top' style='font-size:12px;'>
                    {{ floor($totalMinutes / 60) }}h:{{ sprintf('%02d', $totalMinutes % 60) }}m</td>
            </tr>
        </tbody>


    </table>

    <table width='100%' height='50'>
        <tr>
            <td style=' font-size:12px;text-align:justify ;'>
                
            </td>
        </tr>
    </table>


    <table width='100%' cellspacing='0' cellpadding='2'>
        <tr>
            <td width='33%' style='border-top:double medium #CCCCCC;font-size:12px;' valign='top'>
                <b>{{$entreprise->name}}</b><br />
                <b>{{ __('messages.VAT Number') }}:</b> {{ $entreprise->tva}} <br>

            </td>
            <td width='33%' style='border-top:double medium #CCCCCC; font-size:12px;' align='center' valign='top'>
                {{$entreprise->Address}}<br />
                {{ __('messages.Phone') }}: 0{{$entreprise->phone}}<br />
            </td>

            <td valign='top' width='34%' style='border-top:double medium #CCCCCC;font-size:12px;' align='right'>
                {{ __('messages.BANK') }} : {{$entreprise->bank_name}}<br /> {{ __('messages.IBAN') }} : {{$entreprise->iban}} <br />{{ __('messages.SWIFT/BIC') }}:
                {{$entreprise->swift}} / {{$entreprise->bic}} <br />
            </td>
        </tr>
    </table>
    </td>
    </tr>
    

    <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
            @if(Auth::user()->hasRole('Admin'))
            <form action="{{ route('admin.creditNote.confirm', ['id' => $invoice->id, 'noteType' => $type, 'noteAmount' => $prixtotal]) }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }} 
                </button>
            </form>
            <a href="{{route('admin.invoices.invoicehistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                {{ __('messages.Decline') }}
            </a>
            @endif

            @if(Auth::user()->hasRole('Responsable de projet'))
            <form action="{{ route('Responsable_de_projet.creditNote.confirm', ['id' => $invoice->id, 'noteType' => $type, 'noteAmount' => $prixtotal]) }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }}
                </button>
            </form>
            <a href="{{route('Responsable_de_projet.invoices.invoicehistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                {{ __('messages.Decline') }}
            </a>
            @endif

            @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
            <form action="{{ route('Responsable_gestionnaire.creditNote.confirm', ['id' => $invoice->id, 'noteType' => $type, 'noteAmount' => $prixtotal]) }}" method="POST" style="display: inline-block;">
                @csrf
                <button type="submit" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: green; border-color: green;">
                    {{ __('messages.Confirm') }}
                </button>
            </form>
            <a href="{{route('Responsable_gestionnaire.invoices.invoicehistory')}}" style="padding: 6px 12px; margin-bottom: 6px; font-size: 14px; font-weight: 400; line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; user-select: none; background-image: none; border: 1px solid transparent; border-radius: 4px; color: #fff; background-color: red; border-color: red;">
                {{ __('messages.Decline') }}
            </a>
            @endif
        </div>    </table>
</body>

</html>
