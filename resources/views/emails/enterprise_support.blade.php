<x-email.layout :title="__('emails.support_title')">
    <x-email.details>
        <x-email.detail :label="__('emails.enterprise')">{{ optional($entreprise)->name ?? 'N/A' }}</x-email.detail>
        @if(!empty($data['ticket_id']))
            <x-email.detail :label="__('emails.ticket_id')">#{{ $data['ticket_id'] }}</x-email.detail>
        @endif
        <x-email.detail :label="__('emails.submitted_by')">{{ $user->name }} ({{ $user->email }})</x-email.detail>
        @if(!empty($data['category']))
            <x-email.detail :label="__('emails.category')">{{ $data['category'] }}</x-email.detail>
        @endif
        <x-email.detail :label="__('emails.priority')">{{ ucfirst($data['priority'] ?? 'medium') }}</x-email.detail>
        <x-email.detail :label="__('emails.subject')">{{ $data['subject'] ?? '' }}</x-email.detail>
    </x-email.details>
    <x-email.text><strong>{{ __('emails.message') }}</strong></x-email.text>
    <x-email.panel>{!! nl2br(e($data['message'] ?? '')) !!}</x-email.panel>
    <x-slot:footer><p style="margin:0 0 20px;">{{ __('emails.reply_to', ['email' => $user->email]) }}</p></x-slot:footer>
</x-email.layout>
