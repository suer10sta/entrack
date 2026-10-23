@props(['title', 'description', 'canonical' => null, 'robots' => 'index,follow', 'type' => 'website', 'image' => null])
@php
    $canonical = $canonical ?? \App\Support\Seo::canonical();
    $image = $image ?? \App\Support\Seo::image();
    $ogLocale = ['fr' => 'fr_FR', 'en' => 'en_GB', 'nl' => 'nl_BE', 'es' => 'es_ES'][app()->getLocale()] ?? 'fr_FR';
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">
@foreach(\App\Support\Seo::alternates() as $language => $url)
<link rel="alternate" hreflang="{{ $language }}" href="{{ $url }}">
@endforeach
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:type" content="{{ $type }}">
<meta property="og:image" content="{{ $image }}">
<meta property="og:site_name" content="DalyTrac">
<meta property="og:locale" content="{{ $ogLocale }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $title }}">
<meta name="twitter:description" content="{{ $description }}">
<meta name="twitter:image" content="{{ $image }}">
