@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/contact-support.css') }}">
<div class="cs-page">
    @include('manager.support.feedback')
    <a class="cs-link" href="{{ route('manager.support.index') }}">← {{ __('support.back') }}</a>
    <h1 class="mt-4">{{ $contact->ticket_num }}</h1>
    <div class="cs-meta"><span class="cs-badge cs-{{ $contact->status->value }}">{{ __('support.statuses.'.$contact->status->value) }}</span><span class="cs-badge">{{ __('support.sources.'.$contact->source->value) }}</span><time class="cs-muted">{{ $contact->created_at->utc()->format('d/m/Y H:i') }} UTC</time></div>
    <div class="cs-grid">
        <aside><section class="dt-card cs-section"><h2>{{ __('support.requester') }}</h2><dl class="cs-info">
            <dt>{{ __('support.requester') }}</dt><dd>{{ $contact->name }}<span class="cs-secondary">{{ $contact->email }}</span></dd>
            <dt>{{ __('support.account') }}</dt><dd>{{ $contact->is_active_acc ? __('support.yes') : __('support.no') }}</dd>
            <dt>{{ __('support.user') }}</dt><dd>{{ $contact->user?->name ?? __('support.none') }}@if($contact->user)<span class="cs-secondary">{{ $contact->user->email }}</span>@endif</dd>
            <dt>{{ __('support.entreprise') }}</dt><dd>{{ $contact->entreprise?->name ?? __('support.none') }}</dd>
            @if($contact->ip)<dt>{{ __('support.ip') }}</dt><dd>{{ $contact->ip }}</dd>@endif
        </dl></section>
        <section class="dt-card cs-section"><h2>{{ __('support.history') }}</h2><div class="cs-timeline">
            @forelse($events as $event)<div class="cs-event"><strong>{{ __('support.events.'.$event->event_type->value) }}</strong><span class="cs-secondary">{{ $event->created_at->utc()->format('d/m/Y H:i') }} UTC · {{ $event->manager?->name ?? __('support.system') }}</span></div>@empty<p class="cs-muted">{{ __('support.no_events') }}</p>@endforelse
        </div>{{ $events->links() }}</section></aside>
        <div><section class="dt-card cs-section"><h2>{{ __('support.request') }}</h2><h3>{{ $contact->subject }}</h3><div class="cs-message">{{ $contact->message }}</div></section>
        <section class="dt-card cs-section"><h2>{{ __('support.actions') }}</h2>
            @if($contact->status !== \App\Enums\ContactStatus::Closed)
                <form method="POST" action="{{ route('manager.support.reply', $contact) }}" class="cs-reply-form">
                    @csrf
                    <input type="hidden" name="submission_key" value="{{ old('submission_key', $submissionKey) }}">
                    <label for="reply-message">{{ __('support.reply_label') }}</label>
                    <textarea id="reply-message" name="message" rows="6" maxlength="50000" required>{{ old('message') }}</textarea>
                    <button type="submit" class="cs-button">{{ __('support.send_reply') }}</button>
                </form>
                <form method="POST" action="{{ route('manager.support.close', $contact) }}" class="cs-status-action" onsubmit="return confirm(@js(__('support.confirm_close')))">
                    @csrf<input type="hidden" name="confirmed" value="1"><button type="submit" class="cs-button cs-secondary-button">{{ __('support.close') }}</button>
                </form>
            @else
                <p class="cs-muted">{{ __('support.closed_reply') }}</p>
                <form method="POST" action="{{ route('manager.support.reopen', $contact) }}" class="cs-status-action">@csrf<button type="submit" class="cs-button">{{ __('support.reopen') }}</button></form>
            @endif
            <details class="cs-danger-zone"><summary>{{ __('support.delete') }}</summary>
                <form method="POST" action="{{ route('manager.support.destroy', $contact) }}" onsubmit="return confirm(@js(__('support.confirm_delete')))">
                    @csrf @method('DELETE')
                    <label for="delete-confirmation">{{ __('support.delete_hint', ['ticket' => $contact->ticket_num]) }}</label>
                    <input id="delete-confirmation" name="confirmation" required autocomplete="off" placeholder="{{ __('support.delete_placeholder') }}">
                    <button type="submit" class="cs-button cs-danger-button">{{ __('support.delete') }}</button>
                </form>
            </details>
        </section>
        <section class="dt-card cs-section"><h2>{{ __('support.emails') }}</h2>
            @if($emails->isEmpty())<p class="cs-muted">{{ __('support.no_emails') }}</p>@else<div class="cs-scroll" tabindex="0" role="region" aria-label="{{ __('support.emails') }}"><table class="cs-table cs-email-table"><thead><tr>@foreach(['recipient','subject','status','attempted','sent_date'] as $column)<th scope="col">{{ __('support.'.$column) }}</th>@endforeach</tr></thead><tbody>
            @foreach($emails as $email)<tr><td>{{ $email->recipient_email }}</td><td>{{ $email->subject }}</td><td><span class="cs-badge cs-{{ $email->status->value }}">{{ __('support.email_statuses.'.$email->status->value) }}</span></td><td>{{ $email->attempted_at?->utc()->format('d/m/Y H:i') ?? '—' }}@if($email->attempted_at) UTC @endif</td><td>{{ $email->sent_at?->utc()->format('d/m/Y H:i') ?? '—' }}@if($email->sent_at) UTC @endif</td></tr>@endforeach
            </tbody></table></div>{{ $emails->links() }}@endif
        </section>
        <section class="dt-card cs-section"><h2>{{ __('support.replies') }}</h2>
            @forelse($replies as $reply)<article class="cs-reply"><p class="cs-muted">{{ $reply->manager?->name ?? __('support.system') }} · {{ $reply->created_at->utc()->format('d/m/Y H:i') }} UTC</p><div class="cs-message">{{ $reply->message }}</div></article>@empty<p class="cs-muted">{{ __('support.no_replies') }}</p>@endforelse
            {{ $replies->links() }}
        </section></div>
    </div>
</div>
@endsection
