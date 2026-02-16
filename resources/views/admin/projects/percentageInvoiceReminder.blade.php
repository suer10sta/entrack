<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <!-- Add Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <p>Date du rappel: {{$last_reminder_date}}<br />
                    Échéance: {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
                    <br>
                    Numéro client : {{ $client->client_number }} <br>
                    Numéro de facture : {{$invoice->created_at->format('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }} @endif
                    <br>
                    Rappel numéro : {{$reminder_number}}
                    @if($isOverdue)
                            <br>
                            <span style="color: red;">Jours en retard : {{ $days_overdue }}</span>
                        @endif
                </p>
            </div>
            <div class="details-right">
                <strong>{{ $client->name }}</strong><br />
                {{ $client->billing_street }} <br />
                {{ $client->billing_zip }}-{{ $client->billing_city }} <br />
                {{ $client->billing_country }}<br />
                TVA Client: {{ $client->TVA }}
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
        Rappel Facture : {{$entreprise->invoice_prefix}}-{{$invoice->created_at->format('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }} @endif
        </div>

        <table>
            <thead>
                <tr>
                    <th>Désignation</th>
                    <th>Quantite</th>
                    <th>Pourcentage</th>
                    <th>Montant Principal</th>
                    <th>Acompte</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{strtoupper($projectinfos->name)}}</td>
                    <td>
                        1
                    </td>
                    <td>
                        {{$invoice->percentage}}%
                    </td>
                    <td>{{$projectinfos->budget}}€</td>
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
                        <div class="label">Total HTVA</div>
                        <div class="value">{{$amount}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">TVA {{ $projectinfos->tva }}%
                        </div>
                        <div class="value">{{number_format($amount* ($projectinfos->tva / 100), 2, ',', '')}}€</div>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="totals">
                        <div class="label">Total TTC</div>
                        <div class="value"><b>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</b></div>
                    </td>
                </tr>
            </tbody>
        </table>

        <div class="description">
            <strong> - Informations :</strong> <br>
            {!! nl2br(e($projectinfos->description)) !!} <br>
            <strong> - Commentaire : </strong> <br>
           <p style="white-space: pre-wrap;">{!! nl2br(e($comment)) !!}</p>
        </div>

        <div class="payment">
            - Veuillez payer le montant de
            <strong>{{number_format($amount + ($amount*($projectinfos->tva/100)), 2, ',', '')}}€</strong> sur le compte mentionné
            ci-dessous avant le
            {{ \Carbon\Carbon::parse($invoice->due_date)->format('d-m-Y') }}
            en mentionnant le numéro Facture <strong>{{$invoice->created_at->format('ym')}}@if(request()->has('invoice_number')){{request()->input('invoice_number')}}@else{{$invoice_id }} @endif</strong>
            <br>
            - Dans le cas de livraison intracommunautaire pour les services :  "Autoliquidation Art 21 § 2 du code TVA belge"
            <br>
            - Voir conditions générales en annexe
            <br>
            - Merci de votre confiance

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

        <!-- Update the Send Rappel button to trigger modal -->
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

    <!-- Add Bootstrap JS -->
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
