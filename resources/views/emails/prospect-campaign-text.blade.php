{!! $content !!}
@if($ctaText && $ctaUrl)
{!! $ctaText !!} : {!! $ctaUrl !!}
@endif

@if($document)
Pièce jointe : {{ $document['name'] }}
@endif
