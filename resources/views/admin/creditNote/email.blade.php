<!DOCTYPE html>
<html>
<head>
    <title>Credit Note</title>
</head>
<body>
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2>Credit Note</h2>
        <p>Dear {{ $client->name }},</p>
        
        <p>Please find attached our credit note NC-{{$credit_note->created_at->format('y')}}{{$credit_note->created_at->format('m')}}{{$credit_note->credit_note_number}} for the project {{ $credit_note->project_name }}.</p>
        
        <p>Credit Note Details:</p>
        <ul>
            <li>Credit Note Number: NC-{{$credit_note->created_at->format('y')}}{{$credit_note->created_at->format('m')}}{{$credit_note->credit_note_number}}</li>
            <li>Project: {{ $credit_note->project_name }}</li>
            <li>Amount: -€{{number_format($credit_note->total_ttc, 2, ',', '')}}</li>
        </ul>

        <p>If you have any questions, please do not hesitate to contact us.</p>

        <p>Best regards,<br>
        {{ $entreprise->name }}</p>
    </div>
</body>
</html> 