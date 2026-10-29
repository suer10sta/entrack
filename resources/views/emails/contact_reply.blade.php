<x-email.layout :title="__('support.reply_email_title')">
    <x-email.text>{{ __('support.reply_email_intro') }}</x-email.text>
    <x-email.details>
        <x-email.detail :label="__('support.ticket')">{{ $ticketNum }}</x-email.detail>
        <x-email.detail :label="__('support.subject')">{{ $originalSubject }}</x-email.detail>
    </x-email.details>
    <x-email.panel>{!! nl2br(e($replyMessage)) !!}</x-email.panel>
</x-email.layout>
