@php
    /** @var \App\Models\Entreprise|null $entreprise */
    /** @var \App\Models\User $user */
    /** @var array $data */
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enterprise Support Ticket</title>
</head>
<body>
    <h2>New Enterprise Support Ticket</h2>

    <p><strong>Enterprise:</strong>
        {{ optional($entreprise)->name ?? 'N/A' }}
    </p>

    @if(!empty($data['ticket_id']))
        <p><strong>Ticket ID:</strong> #{{ $data['ticket_id'] }}</p>
    @endif

    <p><strong>Submitted by:</strong> {{ $user->name }} ({{ $user->email }})</p>

    @if(!empty($data['category']))
        <p><strong>Category:</strong> {{ $data['category'] }}</p>
    @endif

    <p><strong>Priority:</strong> {{ ucfirst($data['priority'] ?? 'medium') }}</p>

    <hr>

    <p><strong>Subject:</strong> {{ $data['subject'] ?? '' }}</p>

    <p><strong>Message:</strong></p>
    <p>{!! nl2br(e($data['message'] ?? '')) !!}</p>

    <hr>

    <p style="font-size: 12px; color: #555;">
        When replying, your response will go to: {{ $user->email }}
    </p>

</body>
</html>


