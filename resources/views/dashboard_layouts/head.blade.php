<head>
    <x-favicon />
    @include('partials.password-visibility')
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, initial-scale=1, shrink-to-fit=no"
    />
    <meta name="description" content="{{ __('messages.DalyTrac - E-invoicing, Project Management & Time Tracking') }}">
    <meta property="og:title" content="{{ config('app.name', 'DalyTrac') }}">
    <meta property="og:description" content="{{ __('messages.DalyTrac - E-invoicing, Project Management & Time Tracking') }}">
    <meta property="og:type" content="website">



    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Figtree:400,500,600,700&display=swap" rel="stylesheet">

    <title>{{ config('app.name', 'DalyTrac') }}</title>

    <link rel="canonical" href="{{ route('welcome') }}">
    <link rel="stylesheet" href="{{ asset('css/dalytrac-dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/dalytrac-readability.css') }}">

  </head>
