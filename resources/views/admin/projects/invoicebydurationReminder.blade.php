<html xmlns='https://www.w3.org/1999/xhtml'>

<head>
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
                                <img src="{{ Storage::disk('do_spaces')->url(Auth::user()->entreprise->image) }}" alt="Company Logo" class="company-logo" style="width: 100px;">
                            </div><br />
                        </td>

                        <td width='50%'>&nbsp;</td>
                    </tr>
                    <table width='100%' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td valign='top' width='30%' style='font-size:12px;'>Date du rappel:
                                {{$last_reminder_date}}<br />
                                Échéance: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                                <br>
                                Numéro client : {{ $client->client_number }} <br>
                                Numéro de facture : {{date('ym')}}@if($invoicenumber){{$invoicenumber}} @else{{$invoice_id}} @endif
                                <br>
                                Rappel numéro : {{$reminder_number}}
                                @if($isOverdue)
                            <br>
                            <span style="color: red;">Jours en retard : {{ $days_overdue }}</span>
                        @endif
                            </td>
                            <td valign='top' width='35%'>
                            </td>
                            <td valign='top' width='35%' style='font-size:12px;'>
                                <strong>{{ $client->name }}</strong><br />
                                {{ $client->billing_street }} <br />
                                {{ $client->billing_zip }}-{{ $client->billing_city }} <br />
                                {{ $client->billing_country }}<br />
                                TVA Client: {{ $client->TVA }}
                            </td>
                        </tr>
                    </table>

                    <table width='100%' height='100' cellspacing='0' cellpadding='0'>
                        <tr>
                            <td>
                                <div align='center' style='font-size: 14px;font-weight: bold;'>Rappel Facture : {{$entreprise->invoice_prefix}}-{{date('ym')}}@if($invoicenumber){{$invoicenumber}} @else{{$invoice_id}} @endif</div>
                            </td>
                        </tr>
                    </table>
                    <table width='100%' cellspacing='0' cellpadding='2' border='1' bordercolor='#CCCCCC'>
                        <tr>

                            <td width='35%' bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'>
                                <strong>Désignation
                                </strong>
                            </td>
                            <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>Duration</strong>
                            </td>
                            <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>Prix Unitaire</strong></td>
                            <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>Montant</strong>
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
                                $prixtotal = $projectinfos->cost_per_hour * ($duration/60);
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
                        <td align='right' style='font-size:12px;'>Total HTVA</td>
                        <td align='right' style='font-size:12px;'>{{ number_format($prixtotal, 2, ',', '') }}€
                        <td>
                    </tr>
                    <tr>
                        <td align='right' style='font-size:12px;'>TVA
                            
                            {{$projectinfos->tva}}%</td>
                        <td align='right' style='font-size:12px;'>{{ number_format($prixtotal*($projectinfos->tva/100), 2, ',', '') }}€
                        <td>
                    </tr>
                    <tr>

                        <td align='right' style='font-size:12px;'><b>Total TTC</b></td>
                        <td align='right' style='font-size:12px;'><b>{{number_format($prixtotal + ($prixtotal*($projectinfos->tva/100)), 2, ',', '')}}€</b></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table width='100%' height='50'>
        <tr>
            <td style=' font-size:12px;text-align:justify ;border-top:double;border-bottom:double'>
                <strong> - Informations :</strong> <br>
                {!! nl2br(e($projectinfos->description)) !!} <br>
                <strong> - Commentaire : </strong> <br>
               <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
            </td>
        </tr>
    </table>
    <br>

    <table width='100%' cellspacing='0' cellpadding='2' border='1' bordercolor='#CCCCCC' '>
  <thead>
    <td bordercolor=' #ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>Nom de Tache</strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>Date de debut </strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>Date de Fin</strong></td>
        <td bordercolor='#ccc' bgcolor='#f2f2f2' style='font-size:12px;'><strong>Duree</strong></td>

        </thead>
        <tbody>
            @php
            $totalMinutes = 0; // Initialize total duration in minutes
            @endphp
            @foreach($collection as $coll)
            <tr>
                <td valign='top' style='font-size:12px;'>{{$coll->task_name}}</td>
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
                <td colspan="3" style='font-size:12px; text-align: right;'><strong>Duree Total:</strong></td>
                <td valign='top' style='font-size:12px;'>
                    {{ floor($totalMinutes / 60) }}h:{{ sprintf('%02d', $totalMinutes % 60) }}m</td>
            </tr>
        </tbody>


    </table>

    <table width='100%' height='50'>
        <tr>
            <td style=' font-size:12px;text-align:justify ;'>
                - Veuillez payer le montant de
                <strong>{{number_format($prixtotal + ($prixtotal*($projectinfos->tva/100)), 2, ',', '')}}€</strong> sur le compte mentionné
                ci-dessous avant le 
                {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                en mentionnant le numéro Facture <strong>{{date('ym')}}@if($invoicenumber){{$invoicenumber}} @else{{$invoice_id}} @endif</strong>
                <br>
                - Dans le cas de livraison intracommunautaire pour les services :  "Autoliquidation Art 21 § 2 du code TVA belge"
                <br>
                - Voir tableau des taches en annexe
                <br>
                - Voir conditions générales en annexe
                <br>
                - Merci de votre confiance

            </td>
        </tr>
    </table>


    <table width='100%' cellspacing='0' cellpadding='2'>
        <tr>
            <td width='33%' style='border-top:double medium #CCCCCC;font-size:12px;' valign='top'>
                <b>{{$entreprise->name}}</b><br />
                <b>Numéro TVA:</b> {{ $entreprise->tva}}<br />

            </td>
            <td width='33%' style='border-top:double medium #CCCCCC; font-size:12px;' align='center' valign='top'>
                {{$entreprise->Address}}<br />
                Tél: 0{{$entreprise->phone}}<br />
            </td>

            <td valign='top' width='34%' style='border-top:double medium #CCCCCC;font-size:12px;' align='right'>
                BANK : {{$entreprise->bank_name}}<br /> IBAN : {{$entreprise->iban}} <br />SWIFT/BIC:
                {{$entreprise->swift}} / {{$entreprise->bic}} <br />
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
            Send Rappel
        </button>
    </div>

    <!-- Message container -->
    <div id="messageContainer" style="display: none; position: fixed; top: 20px; left: 50%; transform: translateX(-50%); z-index: 1050;" class="alert alert-dismissible fade show">
        <span id="messageText"></span>
        <button type="button" class="btn-close" onclick="this.parentElement.style.display='none'" aria-label="Close"></button>
    </div>

    <!-- Send Reminder Modal -->
    <div class="modal fade" id="sendReminderModal" tabindex="-1" aria-labelledby="sendReminderModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendReminderModalLabel">Send Reminder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <h6>Select Recipients:</h6>
                            <div id="recipientsList" class="mb-3 p-2 border rounded">
                                <!-- Recipients will be populated here -->
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">Upload Additional Files (Optional):</label>
                            <input type="file" class="form-control" id="fileUpload" name="file[]" multiple>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Send Reminder</button>
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
                    
                    if (data.recipients && data.recipients.length > 0) {
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
                        recipientsList.innerHTML = '<p class="text-muted">No recipients found</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching recipients:', error);
                    document.getElementById('recipientsList').innerHTML = '<p class="text-danger">Error loading recipients</p>';
                });
        });

        // Handle form submission
        document.getElementById('sendReminderForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Get selected recipients
            const selectedRecipients = Array.from(document.querySelectorAll('input[name="selected_recipients[]"]:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedRecipients.length === 0) {
                showMessage('Please select at least one recipient', 'danger');
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
                showMessage('An error occurred while sending the reminder. Please try again.', 'danger');
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