@if($recipient->status === 'sent')
    <div class="cc-recipient-tracking">
        @if($recipient->first_clicked_at)<span class="cc-badge cc-completed">Cliqué</span>@elseif($recipient->first_opened_at)<span class="cc-badge cc-sending">Ouvert</span>@endif
        <ol class="cc-journey">
            <li class="is-observed"><strong>Envoyé</strong><span>{{ $recipient->sent_at?->format('d/m/Y H:i') ?? '—' }}</span></li>
            <li class="{{ $recipient->first_opened_at ? 'is-observed' : '' }}"><strong>Ouvert</strong><span>{{ $recipient->first_opened_at?->format('d/m/Y H:i') ?? 'Non détecté' }}@if($recipient->open_count) · {{ $recipient->open_count }} ouverture(s)@endif</span></li>
            <li class="{{ $recipient->first_clicked_at ? 'is-observed' : '' }}"><strong>Cliqué</strong><span>{{ $recipient->first_clicked_at?->format('d/m/Y H:i') ?? 'Non détecté' }}@if($recipient->click_count) · {{ $recipient->click_count }} clic(s)@endif</span></li>
        </ol>
        @if($recipient->links->isNotEmpty())<details class="cc-clicked-links"><summary>Liens cliqués</summary>@foreach($recipient->links as $link)<div><p>{{ $link->destination }}</p><small>{{ $link->first_clicked_at?->format('d/m/Y H:i') }} · {{ $link->click_count }} clic(s)</small></div>@endforeach</details>@endif
    </div>
@else
    <span class="cc-help">Disponible après envoi</span>
@endif
