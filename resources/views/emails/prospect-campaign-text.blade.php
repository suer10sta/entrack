{!! $greetingName !== '' ? 'Bonjour '.$greetingName.',' : 'Bonjour,' !!}

{!! $content !!}
@if($ctaText && $ctaUrl)
{!! $ctaText !!} : {!! $ctaUrl !!}
@endif
L’équipe DalyTrac
DalyTrac — Simplifiez votre quotidien professionnel.
@if($document)

Pièce jointe : {!! $document['name'] !!}
@endif
@if($unsubscribeUrl)

{!! __('crm-unsubscribe.link', [], 'fr') !!} : {!! $unsubscribeUrl !!}
@endif
