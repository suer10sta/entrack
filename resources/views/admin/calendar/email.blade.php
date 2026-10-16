<x-email.layout :title="__('emails.calendar_title')">
    <div style="color:#355460;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:25px;word-wrap:break-word;">{!! $body !!}</div>
    <x-slot:footer><p style="margin:0 0 20px;">{{ __('emails.calendar_footer') }}</p></x-slot:footer>
</x-email.layout>
