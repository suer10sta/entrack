<x-email.layout :title="__('emails.contact_title')">
    <x-email.details>
        <x-email.detail :label="__('emails.name')">{{ $name }}</x-email.detail>
        <x-email.detail :label="__('emails.email')">{{ $email }}</x-email.detail>
        <x-email.detail :label="__('emails.subject')">{{ $subjectLine }}</x-email.detail>
    </x-email.details>
    <x-email.text><strong>{{ __('emails.message') }}</strong></x-email.text>
    <x-email.panel>{!! nl2br(e($messageBody)) !!}</x-email.panel>
</x-email.layout>
