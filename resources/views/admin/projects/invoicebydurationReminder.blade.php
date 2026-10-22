<html xmlns='https://www.w3.org/1999/xhtml' lang="{{ app()->getLocale() }}">

<head>
    <x-favicon />
    <meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
    .logo-right {
        text-align: left;
    }
</style>
<body style='font-family:Tahoma;font-size:12px;color: #333333;background-color:#FFFFFF;'>
    @php
    $invoicenumber = '';
    if(request()->has('invoice_number')) {
        $invoicenumber = request()->input('invoice_number');
    }
    @endphp
    <table align='center' border='0' cellpadding='0' cellspacing='0' style='height:842px; width:595px;font-size:12px;'>
        <tr>
            <td valign='top'>
                <table width='100%' cellspacing='0' cellpadding='0'>
                    <tr>
                        <td valign='bottom'>
                            <div class="header logo-right">
                                @if(filled(Auth::user()->entreprise?->image) && Storage::disk('public')->exists(Auth::user()->entreprise->image))
<img src="{{ Storage::disk('public')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo" style="width: 100px;">
@endif
                            </div><br />
                        </td>

                        <td width='50%'>&nbsp;</td>
                    </tr>
                    <table width='100%' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td valign='top' width='30%' style='font-size:12px;'>{{ __('messages.Reminder Date') }}:
                                {{$last_reminder_date}}<br />
                                {{ __('messages.Due Date') }}: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                                <br>
@if(filled($client->client_number))
                                {{ __('messages.Client Number') }} : {{ $client->client_number }} <br>
@endif
                                {{ __('messages.Invoice Number') }} : {{ $invoice->invoice_reference ?? $invoice->number ?? \App\Support\InvoiceNumbering::preview($entreprise, $invoice_id) }}
                                <br>
                                {{ __('messages.Reminder Number') }} : {{$reminder_number}}
                                @if($isOverdue)
                            <br>
                            <span style="color: red;">{{ __('messages.Days overdue') }} : {{ $days_overdue }}</span>
                        @endif
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
                                <div align='center' style='font-size: 14px;font-weight: bold;'>{{ __('messages.Invoice Reminder') }} : {{ \App\Support\InvoiceNumbering::reference($entreprise, $invoice ?? $invoice_id) }}</div>
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
                                $hours = round($duration / 60, 2);
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
                - {{ __('messages.Please pay the amount of') }}
                <strong>{{number_format($prixtotal + ($prixtotal*($projectinfos->tva/100)), 2, ',', '')}}€</strong> {{ __('messages.on the account mentioned below before') }} 
                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                {{ __('messages.mentioning the invoice number') }} <strong>{{ $invoice->invoice_reference ?? $invoice->number ?? \App\Support\InvoiceNumbering::preview($entreprise, $invoice_id) }}</strong>
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
    

    <div style="display: flex; justify-content: center; align-items: center; flex-direction: column;">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendReminderModal" data-invoice-id="{{ $invoice->id }}"
                style="display: inline-block; padding: 6px 12px; margin-bottom: 0; font-size: 14px; font-weight: 400; 
                       line-height: 1.42857143; text-align: center; white-space: nowrap; vertical-align: middle; 
                       -ms-touch-action: manipulation; touch-action: manipulation; cursor: pointer; 
                       -webkit-user-select: none; -moz-user-select: none; -ms-user-select: none; 
                       user-select: none; background-image: none; border: 1px solid transparent; 
                       border-radius: 4px; color: #fff; background-color: #337ab7; border-color: #2e6da4;">
            {{ __('messages.Send Reminder') }}
        </button>
    </div>

    <!-- Message container -->
    <div id="messageContainer" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1050;" class="alert alert-dismissible fade show">
        <span id="messageText"></span>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'" aria-label="{{ __('messages.Close') }}"></button>
    </div>

    <!-- Send Reminder Modal -->
    <div class="modal fade" id="sendReminderModal" tabindex="-1" aria-labelledby="sendReminderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendReminderModalLabel">{{ __('messages.Send Reminder') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.Close') }}"></button>
                </div>
        @if(Auth::user()->hasRole('Admin'))
                    <form id="sendReminderForm" action="{{route('admin.invoice.SendInvReminder', ['invoice_id' => $invoice->id, 'reminder_number' => $reminder_number])}}" method="post">
        @endif
        @if(Auth::user()->hasRole('Responsable de projet'))
                    <form id="sendReminderForm" action="{{route('Responsable_de_projet.invoice.SendInvReminder', ['invoice_id' => $invoice->id, 'reminder_number' => $reminder_number])}}" method="post">
        @endif
        @if(Auth::user()->hasRole('Employee'))
                    <form id="sendReminderForm" action="{{route('employee.invoice.SendInvReminder', ['invoice_id' => $invoice->id, 'reminder_number' => $reminder_number])}}" method="post">
        @endif
        @if(Auth::user()->hasRole('Responsable de projet et gestionnaire'))
                    <form id="sendReminderForm" action="{{route('Responsable_gestionnaire.invoice.SendInvReminder', ['invoice_id' => $invoice->id, 'reminder_number' => $reminder_number])}}" method="post">
        @endif
                @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6>{{ __('messages.Select Recipients') }}:</h6>
                            <div id="recipientsList" class="mb-3 p-2 border rounded">
                                <!-- Recipients will be populated here -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">{{ __('messages.Upload Additional Files (Optional)') }}:</label>
                            <input type="file" class="form-control" id="fileUpload" name="file[]" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('messages.Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('messages.Send Reminder') }}</button>
                    </div>
            </form>
            </div>
        </div>
    </div>

    <!-- Include Bootstrap CSS and JS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Function to show message
        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const messageText = document.getElementById('messageText');
            
            messageContainer.className = 'alert alert-' + type + ' alert-dismissible fade show';
            messageContainer.style.color = 'white';
            messageContainer.style.fontWeight = 'bold';
            messageText.textContent = message;
            messageContainer.style.display = 'block';
            
            setTimeout(() => {
                messageContainer.style.display = 'none';
            }, 5000);
        }

        var sendReminderModal = document.getElementById('sendReminderModal');
        sendReminderModal.addEventListener('show.bs.modal', function (event) {
            var button = event.relatedTarget;
            var invoiceId = button.getAttribute('data-invoice-id');
            // Fetch recipient information using the dynamic invoiceId
            fetch(`/admin/invoice/${invoiceId}/recipients`)
                .then(response => response.json())
                .then(data => {
                    const recipientsList = document.getElementById('recipientsList');
                    recipientsList.innerHTML = '';
                    
                    if (data.message && (!data.recipients || data.recipients.length === 0)) {
                        recipientsList.innerHTML = '<p class="text-danger">' + data.message + '</p>';
                    } else if (data.recipients && data.recipients.length > 0) {
                        data.recipients.forEach(recipient => {
                            const recipientDiv = document.createElement('div');
                            recipientDiv.className = 'mb-2';
                            
                            // Create checkbox
                            const checkbox = document.createElement('input');
                            checkbox.type = 'checkbox';
                            checkbox.name = 'selected_recipients[]';
                            checkbox.value = recipient.email;
                            checkbox.id = `recipient-${recipient.email}`;
                            checkbox.className = 'form-check-input me-2';
                            
                            // Check by default if recipient is comptable
                            if (recipient.is_comptable === 1) {
                                checkbox.checked = true;
                            }
                            
                            // Create label
                            const label = document.createElement('label');
                            label.className = 'form-check-label';
                            label.htmlFor = `recipient-${recipient.email}`;
                            label.innerHTML = `<strong>${recipient.name}</strong> (${recipient.email})`;
                            
                            // Append elements
                            recipientDiv.appendChild(checkbox);
                            recipientDiv.appendChild(label);
                            recipientsList.appendChild(recipientDiv);
                        });
                    } else {
                        recipientsList.innerHTML = ('<p class="text-muted">' + @js(__('messages.No recipients found')) + '</p>');
                    }
                })
                .catch(error => {
                    console.error('Error fetching recipients:', error);
                    document.getElementById('recipientsList').innerHTML = ('<p class="text-danger">' + @js(__('messages.Error loading recipients')) + '</p>');
                });
        });

        // Handle form submission
        document.getElementById('sendReminderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get selected recipients
            const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedRecipients.length === 0) {
                showMessage(@js(__('messages.Please select at least one recipient')), 'danger');
                return;
            }

            // Create FormData object
            const formData = new FormData(this);
            formData.delete('selected_recipients[]'); // Remove the original checkboxes data
            formData.append('selected_recipients', JSON.stringify(selectedRecipients));

            // Submit the form
            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Close the modal
                    const modal = bootstrap.Modal.getInstance(sendReminderModal);
                    modal.hide();
                    
                    // Show success message
                    showMessage(data.message, 'success');
                    
                    // Wait for the message to be visible before reloading
                    setTimeout(() => {
                        // Store the success message in sessionStorage
                        sessionStorage.setItem('reminderMessage', JSON.stringify({
                            message: data.message,
                            type: 'success'
                        }));
                        
                        // Redirect to the invoice history page
                        window.location.href = '{{ route("admin.invoices.invoicehistory") }}';
                    }, 1000);
                } else {
                    showMessage('Error sending reminder: ' + (data.message || 'Unknown error'), 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                    showMessage(@js(__('messages.An error occurred while sending the reminder')), 'danger');
            });
        });

        // Check for stored message on page load
        const storedMessage = sessionStorage.getItem('reminderMessage');
        if (storedMessage) {
            const { message, type } = JSON.parse(storedMessage);
            showMessage(message, type);
            // Clear the stored message
            sessionStorage.removeItem('reminderMessage');
        }
    });
    </script>
</body>

</html>
