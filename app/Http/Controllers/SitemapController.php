<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Support\Seo;

class SitemapController extends Controller
{
    public function __invoke()
    {
        $xml = new \DOMDocument('1.0', 'UTF-8');
        $xml->formatOutput = true;
        $root = $xml->appendChild($xml->createElementNS('http://www.sitemaps.org/schemas/sitemap/0.9', 'urlset'));
        $append = function (string $url, ?string $lastmod = null) use ($xml, $root) {
            $entry = $root->appendChild($xml->createElement('url'));
            $entry->appendChild($xml->createElement('loc'))->appendChild($xml->createTextNode($url));
            if ($lastmod) {
                $entry->appendChild($xml->createElement('lastmod'))->appendChild($xml->createTextNode($lastmod));
            }
        };
        foreach (array_keys(config('seo.pages')) as $name) {
            foreach (config('seo.locales') as $locale) {
                $append(Seo::url($name, $locale));
            }
        }
        foreach (Post::published()->orderBy('id')->cursor() as $post) {
            $append(Seo::url('blog.show', null, ['post' => $post->slug]), $post->updated_at?->toAtomString());
        }
        return response($xml->saveXML(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
