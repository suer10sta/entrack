<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $post->title }} | DalyTrac Blog</title>
    <meta name="description" content="{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 150) }}">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('blog.show', $post->slug) }}">

    <meta property="og:title" content="{{ $post->title }} | DalyTrac Blog">
    <meta property="og:description" content="{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 150) }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ route('blog.show', $post->slug) }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $post->title }} | DalyTrac Blog">
    <meta name="twitter:description" content="{{ $post->excerpt ?: Str::limit(strip_tags($post->body), 150) }}">

    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('welcome/css/aos.css') }}" />
  </head>

  <body class="antialiased bg-card text-muted font-figtree min-h-screen flex flex-col">
    <x-public.header />

    <main class="flex-1">
      <section class="pt-16 pb-16 bg-background" style="padding-top: 40px !important;padding-bottom: 8rem;">
        <div class="container mx-auto px-6 max-w-3xl">
          <p class="mb-4 text-xs font-semibold uppercase tracking-wide text-primary">
            {{ __('messages.DalyTrac Blog') }}
          </p>
          <h1 class="text-3xl md:text-4xl font-bold text-foreground mb-3">
            {{ $post->title }}
          </h1>
          <p class="text-xs text-muted-foreground mb-8">
            {{ optional($post->published_at)->format('M d, Y') }}
          </p>

          <article class="prose prose-slate max-w-none dark:prose-invert">
            {!! $post->body !!}
          </article>

          <div class="mt-10">
            <a href="{{ route('blog.index') }}" class="text-sm text-primary hover:text-primary/80">
              ← {{ __('messages.Back to blog') }}
            </a>
          </div>
        </div>
      </section>
    </main>

    <x-public.footer />
  </body>
</html>


