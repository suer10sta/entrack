<x-email.layout :title="__('emails.credit_title')">
    <x-email.text>{{ __('emails.hello') }} {{ $client->name }},</x-email.text>
    <x-email.text>{{ __('emails.credit_intro', ['number' => 'NC-'.$credit_note->created_at->format('y').$credit_note->created_at->format('m').$credit_note->credit_note_number, 'project' => $credit_note->project_name]) }}</x-email.text>
    <x-email.text><strong>{{ __('emails.details') }}</strong></x-email.text>
    <x-email.details>
        <x-email.detail :label="__('emails.credit_number')">NC-{{$credit_note->created_at->format('y')}}{{$credit_note->created_at->format('m')}}{{$credit_note->credit_note_number}}</x-email.detail>
        <x-email.detail :label="__('emails.project')">{{ $credit_note->project_name }}</x-email.detail>
        <x-email.detail :label="__('emails.amount')">-€{{number_format($credit_note->total_ttc, 2, ',', '')}}</x-email.detail>
    </x-email.details>
    <x-email.text>{{ __('emails.questions') }}</x-email.text>
    <x-email.text>{{ __('emails.regards') }}<br>{{ $entreprise->name }}</x-email.text>
</x-email.layout>
