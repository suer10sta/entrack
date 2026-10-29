@extends('manager.layouts.app')
@section('content')
<link rel="stylesheet" href="{{ asset('css/contact-support.css') }}">
<div class="cs-page cs-list-page" data-network-error="{{ __('support.quick_reply_error') }}" data-session-error="{{ __('support.quick_reply_session_expired') }}">
    @include('manager.support.feedback')
    <header><h1>{{ __('support.title') }}</h1><p class="cs-subtitle">{{ __('support.description') }}</p></header>
    <div class="cs-counts">@foreach(['all','pending','replied','closed'] as $status)<div class="cs-count"><span>{{ __('support.counts.'.$status) }}</span><strong>{{ number_format($counts[$status] ?? 0) }}</strong></div>@endforeach</div>
    <form method="GET" action="{{ route('manager.support.index') }}" class="cs-filters dt-card">
        <label class="cs-search">{{ __('support.search') }}<input type="search" name="q" value="{{ $filters['q'] ?? '' }}" maxlength="255" placeholder="{{ __('support.search_hint') }}"></label>
        <label>{{ __('support.status') }}<select name="status"><option value="">{{ __('support.all') }}</option>@foreach(\App\Enums\ContactStatus::cases() as $status)<option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ __('support.statuses.'.$status->value) }}</option>@endforeach</select></label>
        <label>{{ __('support.source') }}<select name="source"><option value="">{{ __('support.all_sources') }}</option>@foreach(\App\Enums\ContactSource::cases() as $source)<option value="{{ $source->value }}" @selected(($filters['source'] ?? '') === $source->value)>{{ __('support.sources.'.$source->value) }}</option>@endforeach</select></label>
        <label>{{ __('support.sort') }}<select name="sort">@foreach(['newest','oldest'] as $sort)<option value="{{ $sort }}" @selected(($filters['sort'] ?? 'newest') === $sort)>{{ __('support.'.$sort) }}</option>@endforeach</select></label>
        <label>{{ __('support.per_page') }}<select name="per_page">@foreach([15,25,50,100] as $size)<option value="{{ $size }}" @selected((int)($filters['per_page'] ?? 15) === $size)>{{ $size }}</option>@endforeach</select></label>
        <button class="cs-button" type="submit">{{ __('support.filter') }}</button><a class="cs-link" href="{{ route('manager.support.index') }}">{{ __('support.reset') }}</a>
    </form>

    <section class="cs-ticket-grid" aria-label="{{ __('support.title') }}">
        @forelse($contacts as $contact)
            @php
                $canReply = $contact->status !== \App\Enums\ContactStatus::Closed;
                $preview = \Illuminate\Support\Str::limit($contact->message_preview ?? '', 260);
            @endphp
            <article class="cs-ticket-card" data-contact-card="{{ $contact->id }}">
                <div class="cs-card-top"><span class="cs-badge cs-{{ $contact->status->value }}">{{ __('support.statuses.'.$contact->status->value) }}</span><span class="cs-ticket-reference">{{ $contact->ticket_num }}</span><span class="cs-card-source">{{ __('support.sources.'.$contact->source->value) }}</span></div>
                <h2 class="cs-card-title" title="{{ $contact->subject }}">{{ $contact->subject }}</h2>
                <div class="cs-card-requester"><span>{{ __('support.from') }} <strong>{{ $contact->name }}</strong></span><span class="cs-card-email">{{ $contact->email }}</span></div>
                <div class="cs-card-message"><p data-ticket-preview data-truncated="{{ mb_strlen($contact->message_preview ?? '') > 260 ? '1' : '0' }}">{{ $preview }}</p><a class="cs-preview-more" data-preview-more href="{{ route('manager.support.show', $contact) }}" hidden>{{ __('support.see_more') }}</a></div>
                <footer class="cs-card-footer"><time datetime="{{ $contact->created_at->utc()->toIso8601String() }}">{{ $contact->created_at->utc()->format('d/m/Y H:i') }} UTC</time><div class="cs-card-actions"><a class="cs-card-view" href="{{ route('manager.support.show', $contact) }}">{{ __('support.view') }}</a>@if($canReply)<button type="button" class="cs-card-reply" data-open-reply="reply-dialog-{{ $contact->id }}" aria-haspopup="dialog" aria-controls="reply-dialog-{{ $contact->id }}"><svg viewBox="0 0 24 24" width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 5h14v11H9l-4 4V5Z"/></svg>{{ __('support.quick_reply') }}</button>@endif</div></footer>
            </article>
            @if($canReply)
                <dialog class="cs-quick-dialog" id="reply-dialog-{{ $contact->id }}" aria-labelledby="reply-title-{{ $contact->id }}" data-quick-reply>
                    <form method="POST" action="{{ route('manager.support.reply', $contact) }}" class="cs-quick-form">
                        @csrf
                        <input type="hidden" name="submission_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                        <header class="cs-quick-header"><div><span class="cs-quick-eyebrow">{{ $contact->ticket_num }}</span><h2 id="reply-title-{{ $contact->id }}">{{ __('support.quick_reply') }}</h2></div><button type="button" class="cs-quick-dismiss" data-close-reply aria-label="{{ __('support.cancel') }}">×</button></header>
                        <div class="cs-quick-body"><div class="cs-quick-context"><span class="cs-quick-label">{{ __('support.from') }}</span><strong>{{ $contact->name }}</strong><span>{{ $contact->email }}</span><span class="cs-quick-label">{{ __('support.subject') }}</span><strong>{{ $contact->subject }}</strong><p class="cs-quick-original">{{ \Illuminate\Support\Str::limit($contact->message_preview ?? '', 600) }}</p></div>
                            <div class="cs-quick-error" role="alert" hidden></div>
                            <label for="quick-message-{{ $contact->id }}">{{ __('support.your_reply') }}</label><textarea id="quick-message-{{ $contact->id }}" name="message" rows="6" maxlength="50000" required></textarea>
                        </div>
                        <footer class="cs-quick-footer"><button type="button" class="cs-quick-cancel" data-close-reply>{{ __('support.cancel') }}</button><button type="submit" class="cs-button">{{ __('support.send_reply') }}</button></footer>
                    </form>
                </dialog>
            @endif
        @empty<div class="dt-card cs-empty cs-grid-empty">{{ __('support.empty') }}</div>@endforelse
    </section>
    <div class="cs-pagination cs-grid-pagination">{{ $contacts->links() }}</div>
    <noscript><p class="cs-muted">{{ __('support.quick_reply_no_js') }}</p></noscript>
    <script src="{{ asset('js/contact-support-list.js') }}" defer></script>

</div>
@endsection
