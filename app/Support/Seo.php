<?php

namespace App\Support;

class Seo
{
    private static function page(): int
    {
        return filter_var(request()->query('page'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
    }

    public static function languageLink(string $locale): string
    {
        $name = request()->route()?->getName();
        if (array_key_exists($name ?? '', config('seo.pages'))) {
            $url = route($name, ['locale' => $locale]);
            if ($name === 'blog.index' && static::page() > 1) {
                $url .= '?page='.static::page();
            }
            return $url;
        }

        return route('changeLang', ['lang' => $locale]);
    }

    public static function url(string $name, ?string $locale = null, array $parameters = []): string
    {
        $parameters = array_merge(['locale' => $locale ?? app()->getLocale()], $parameters);
        // URL defaults must not leak a locale query parameter onto language-neutral articles.
        if ($name === 'blog.show') {
            $path = '/blog/'.rawurlencode((string) $parameters['post']);
        } else {
            $path = route($name, $parameters, false);
        }

        return rtrim(config('seo.url'), '/').$path;
    }

    public static function canonical(): string
    {
        $name = request()->route()->getName();
        $parameters = [];
        if ($name === 'blog.show') {
            $parameters['post'] = request()->route('post')->slug;
        }
        $url = static::url($name, null, $parameters);
        if ($name === 'blog.index' && static::page() > 1) {
            $url .= '?page='.static::page();
        }

        return $url;
    }

    public static function alternates(): array
    {
        $name = request()->route()->getName();
        if (!array_key_exists($name, config('seo.pages'))) {
            return [];
        }
        $links = [];
        foreach (config('seo.locales') as $locale) {
            $links[$locale] = static::url($name, $locale);
            if ($name === 'blog.index' && static::page() > 1) {
                $links[$locale] .= '?page='.static::page();
            }
        }
        $links['x-default'] = $links[config('seo.default_locale')];

        return $links;
    }

    public static function image(?string $path = null): string
    {
        // Only publish a URL for a local asset that actually exists.
        $path = ltrim($path ?: 'img/dalytrac-social.png', '/');
        if (str_contains($path, '..') || !is_file(public_path($path))) {
            $path = 'img/dalytrac-social.png';
        }

        return rtrim(config('seo.url'), '/').'/'.$path;
    }

    public static function postImage(\App\Models\Post $post): string
    {
        // There is currently no image column. Use a real local image from the article body if present.
        $document = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="utf-8" ?>'.$post->body);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        foreach ($document->getElementsByTagName('img') as $image) {
            $source = $image->getAttribute('src');
            $host = parse_url($source, PHP_URL_HOST);
            if ($host && $host !== parse_url(config('seo.url'), PHP_URL_HOST)) {
                continue;
            }
            $path = rawurldecode(parse_url($source, PHP_URL_PATH) ?: '');
            if ($path && !str_contains($path, '..') && is_file(public_path(ltrim($path, '/')))) {
                return static::image($path);
            }
        }

        return static::image();
    }
}
