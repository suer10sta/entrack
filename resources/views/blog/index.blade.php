<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Blog | DalyTrac</title>
    <meta name="description" content="Practical articles on e-invoicing, project management, time tracking and running a modern services business with DalyTrac.">
    <meta name="robots" content="index,follow">
    <link rel="canonical" href="{{ route('blog.index') }}">

    <meta property="og:title" content="Blog | DalyTrac">
    <meta property="og:description" content="Tips and best practices for using DalyTrac to manage invoices, projects and teams.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ route('blog.index') }}">
    <meta property="og:image" content="{{ asset('img/background_hero.png') }}">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Blog | DalyTrac">
    <meta name="twitter:description" content="Guides and updates about DalyTrac and business operations.">

    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('welcome/css/aos.css') }}" />
  </head>

  <body class="antialiased bg-card text-muted font-figtree min-h-screen flex flex-col">
    <x-public.header />

    <main class="flex-1">
      <section class="pt-16 pb-16 bg-background" style="padding-top: 30px !important; padding-bottom: 8rem !important;">
        <div class="container mx-auto px-6 max-w-5xl">
          <div class="text-center mb-10">
            <span class="inline-flex items-center px-4 py-1.5 mb-4 rounded-full bg-secondary border border-border text-primary text-xs font-semibold uppercase tracking-wide">
              {{ __('messages.Blog') }}
            </span>
            <h1 class="text-4xl md:text-5xl font-bold text-foreground mb-3">
              {{ __('messages.Latest insights from DalyTrac') }}
            </h1>
            <p class="text-muted max-w-2xl mx-auto">
              {{ __('messages.Practical tips on invoicing, projects, and time tracking to help your team work smarter.') }}
            </p>
          </div>

          @if ($posts->isEmpty())
            <p class="text-center text-muted">{{ __('messages.No blog posts yet. Check back soon!') }}</p>
          @else
            <div class="grid gap-8 md:grid-cols-2">
              @foreach ($posts as $post)
                <article class="bg-card border border-border rounded-2xl p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all">
                  <h2 class="text-xl font-semibold text-foreground mb-2">
                    <a href="{{ route('blog.show', $post->slug) }}" class="hover:text-primary transition-colors">
                      {{ $post->title }}
                    </a>
                  </h2>
                  <p class="text-xs text-muted-foreground mb-3">
                    {{ optional($post->published_at)->format('M d, Y') }}
                  </p>
                  @if ($post->excerpt)
                    <p class="text-sm text-muted-foreground mb-4">
                      {{ $post->excerpt }}
                    </p>
                  @endif
                  <a href="{{ route('blog.show', $post->slug) }}" class="text-sm font-semibold text-primary hover:text-primary/80">
                    {{ __('messages.Read more') }} →
                  </a>
                </article>
              @endforeach
            </div>

            <div class="mt-10">
              {{ $posts->links() }}
            </div>
          @endif
        </div>
      </section>
    </main>

    <x-public.footer />
  </body>
</html>


