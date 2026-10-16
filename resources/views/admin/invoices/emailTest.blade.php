<x-email.layout :title="$subject ?? __('emails.document_title')">
    {{-- Keep author-supplied invoice, reminder, subscription and quote HTML intact. --}}
    <div style="color:#355460;font-family:Arial,Helvetica,sans-serif;font-size:15px;line-height:25px;word-wrap:break-word;">{!! $body !!}</div>
</x-email.layout>
