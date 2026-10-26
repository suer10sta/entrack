<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Seo;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PublicSeoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        $this->withoutVite();
        Schema::create('posts', function ($table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->text('body');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    private function xpath(string $html): \DOMXPath
    {
        $dom = new \DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML('<?xml encoding="utf-8" ?>'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return new \DOMXPath($dom);
    }

    public function test_all_localized_pages_render_complete_unique_metadata_and_reciprocal_alternates(): void
    {
        foreach (config('seo.locales') as $locale) {
            $titles = $descriptions = [];
            foreach (config('seo.pages') as $name => $path) {
                $url = '/'.$locale.($path ? '/'.$path : '');
                $response = $this->withSession(['locale' => 'es'])->get($url)->assertOk();
                $x = $this->xpath($response->getContent());
                $this->assertSame(1, $x->query('//head/script[@src="https://www.googletagmanager.com/gtag/js?id=G-MDJNVZY2WF"]')->length, $url);
                $this->assertSame(1, substr_count($response->getContent(), "gtag('config', 'G-MDJNVZY2WF')"), $url);
                $this->assertSame($locale, $x->evaluate('string(/html/@lang)'), $url);
                $this->assertSame(1, $x->query('//head/title')->length, $url);
                $this->assertSame(1, $x->query('//h1')->length, $url);
                $title = $x->evaluate('string(//head/title)');
                $description = $x->evaluate('string(//meta[@name="description"]/@content)');
                $this->assertNotEmpty($title);
                $this->assertNotEmpty($description);
                $this->assertStringNotContainsString('messages.', $title.$description);
                $this->assertStringNotContainsString('}}">', $response->getContent());
                $titles[] = $title;
                $descriptions[] = $description;
                $this->assertSame(Seo::url($name, $locale), $x->evaluate('string(//link[@rel="canonical"]/@href)'));
                $this->assertSame('index,follow', $x->evaluate('string(//meta[@name="robots"]/@content)'));
                $this->assertFalse($response->headers->has('X-Robots-Tag'));
                foreach (['fr', 'en', 'nl', 'es', 'x-default'] as $alternate) {
                    $target = $alternate === 'x-default' ? 'fr' : $alternate;
                    $this->assertSame(Seo::url($name, $target), $x->evaluate('string(//link[@hreflang="'.$alternate.'"]/@href)'));
                }
                foreach (['og:title', 'og:description', 'og:url', 'og:type', 'og:image', 'og:site_name', 'og:locale'] as $property) {
                    $this->assertSame(1, $x->query('//meta[@property="'.$property.'"]')->length);
                    $this->assertNotEmpty($x->evaluate('string(//meta[@property="'.$property.'"]/@content)'));
                }
                foreach (['card', 'title', 'description', 'image'] as $property) {
                    $this->assertSame(1, $x->query('//meta[@name="twitter:'.$property.'"]')->length);
                }
                foreach ($x->query('//script[@type="application/ld+json"]') as $schema) {
                    $data = json_decode($schema->textContent, true, 512, JSON_THROW_ON_ERROR);
                    $this->assertSame('https://schema.org', $data['@context']);
                }
                foreach ($x->query('//link[@rel="stylesheet"]/@href | //img/@src') as $asset) {
                    $host = parse_url($asset->nodeValue, PHP_URL_HOST);
                    if (!$host || in_array($host, ['localhost', 'dalytrac.com'], true)) {
                        $this->assertFileExists(public_path(ltrim(parse_url($asset->nodeValue, PHP_URL_PATH), '/')), $asset->nodeValue);
                    }
                }
                foreach ($x->query('//a[@href]/@href') as $link) {
                    $host = parse_url($link->nodeValue, PHP_URL_HOST);
                    $path = parse_url($link->nodeValue, PHP_URL_PATH);
                    if ((!$host || in_array($host, ['localhost', 'dalytrac.com'], true)) && $path && in_array(parse_url($link->nodeValue, PHP_URL_SCHEME), [null, 'http', 'https'], true)) {
                        $route = app('router')->getRoutes()->match(\Illuminate\Http\Request::create($link->nodeValue));
                        $this->assertNotNull($route, $link->nodeValue);
                    }
                }
                $socialPath = parse_url($x->evaluate('string(//meta[@property="og:image"]/@content)'), PHP_URL_PATH);
                $this->assertFileExists(public_path(ltrim($socialPath, '/')));
            }
            $this->assertCount(count($titles), array_unique($titles));
            $this->assertCount(count($descriptions), array_unique($descriptions));
        }
    }

    public function test_legacy_paths_redirect_once_and_localized_urls_ignore_session_preferences(): void
    {
        foreach (config('seo.pages') as $name => $path) {
            $this->get('/'.$path)->assertStatus(301)->assertRedirect(route($name, ['locale' => 'fr']));
        }
        // Laravel's test URL builder strips trailing slashes before sending requests.
        $request = \Illuminate\Http\Request::create('http://localhost/en/produit/');
        $route = app('router')->getRoutes()->match($request);
        $request->setRouteResolver(fn () => $route);
        $redirect = (new \App\Http\Middleware\SeoPolicy)->handle($request, fn () => response('unexpected'));
        $this->assertSame(301, $redirect->getStatusCode());
        $this->assertSame('http://localhost/en/produit', $redirect->headers->get('Location'));
        $this->get('/de/produit')->assertNotFound();
        $this->from('/en/produit')->get('/lang/change?lang=nl')->assertRedirect('/nl/produit');
        $this->from('/produit')->get('/lang/change?lang=es')->assertRedirect('/es/produit');
    }

    public function test_blog_pagination_and_publication_and_sitemap(): void
    {
        foreach (range(1, 11) as $i) {
            Post::create(['title' => 'Article '.$i, 'slug' => 'article-'.$i, 'body' => '<p>Real article content</p>', 'is_published' => true, 'published_at' => now()->subDay()]);
        }
        foreach (['future' => now()->addDay(), 'undated' => null] as $slug => $date) {
            Post::create(['title' => $slug, 'slug' => $slug, 'body' => '<p>Hidden</p>', 'is_published' => true, 'published_at' => $date]);
        }
        Post::create(['title' => 'Draft', 'slug' => 'draft', 'body' => '<p>Draft</p>', 'is_published' => false, 'published_at' => now()->subDay()]);
        foreach (['future', 'undated', 'draft', 'missing'] as $slug) {
            $this->get('/blog/'.$slug)->assertNotFound();
        }
        $response = $this->get('/en/blog?page=2&utm_source=test')->assertOk();
        $x = $this->xpath($response->getContent());
        $this->assertSame('https://dalytrac.com/en/blog?page=2', $x->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame('https://dalytrac.com/nl/blog?page=2', $x->evaluate('string(//link[@hreflang="nl"]/@href)'));
        $invalidPage = $this->xpath($this->get('/en/blog?page=2abc')->assertOk()->getContent());
        $this->assertSame('https://dalytrac.com/en/blog', $invalidPage->evaluate('string(//link[@rel="canonical"]/@href)'));
        $article = $this->get('/blog/article-1')->assertOk();
        $x = $this->xpath($article->getContent());
        $this->assertSame('https://dalytrac.com/blog/article-1', $x->evaluate('string(//link[@rel="canonical"]/@href)'));
        $this->assertSame(0, $x->query('//link[@hreflang]')->length);
        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();
        $dom = new \DOMDocument;
        $this->assertTrue($dom->loadXML($xml));
        $locations = $dom->getElementsByTagName('loc');
        $this->assertCount(count(config('seo.pages')) * 4 + 11, $locations);
        $urls = [];
        foreach ($locations as $loc) {
            $urls[] = $loc->textContent;
            $this->assertStringStartsWith('https://dalytrac.com/', $loc->textContent);
            $this->get(parse_url($loc->textContent, PHP_URL_PATH))->assertOk();
        }
        $this->assertCount(count($urls), array_unique($urls));
        foreach (['login', 'register', 'future', 'undated', 'draft', 'dashboard', 'payment', 'lang/change'] as $excluded) {
            $this->assertStringNotContainsString('/'.$excluded, $xml);
        }
    }

    public function test_utility_pages_are_noindex_and_missing_videos_are_not_emitted(): void
    {
        foreach (['/login', '/register', '/forgot-password', '/reset-password/token', '/manager/login'] as $url) {
            $response = $this->get($url)->assertOk()->assertHeader('X-Robots-Tag', 'noindex, follow');
            $x = $this->xpath($response->getContent());
            $this->assertSame('noindex,follow', $x->evaluate('string(//meta[@name="robots"]/@content)'));
        }
        $x = $this->xpath($this->get('/fr/demo')->assertOk()->getContent());
        foreach ($x->query('//video/source/@src') as $source) {
            $this->assertFileExists(public_path(ltrim(parse_url($source->nodeValue, PHP_URL_PATH), '/')));
        }
        foreach ($x->query('//script[not(@src)]') as $script) {
            if (preg_match('/const playlistVideos = (.*);/', $script->textContent, $matches)) {
                $playlist = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
                $this->assertCount(count(config('demo.videos')), $playlist);
                foreach ($playlist as $video) {
                    if ($video['src']) {
                        $this->assertFileExists(public_path(ltrim(parse_url($video['src'], PHP_URL_PATH), '/')));
                    }
                }
            }
        }
        $this->assertStringContainsString('Sitemap: https://dalytrac.com/sitemap.xml', file_get_contents(public_path('robots.txt')));
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_article_social_image_uses_an_existing_image_and_rejects_missing_assets(): void
    {
        $post = new Post(['body' => '<img src="/missing.png"><img src="/assets/images/dalytrac-pour-qui-pme-services.webp">']);
        $this->assertSame('https://dalytrac.com/assets/images/dalytrac-pour-qui-pme-services.webp', Seo::postImage($post));
        $post->body = '<img src="/missing.png">';
        $this->assertSame(Seo::image(), Seo::postImage($post));
    }

    public function test_production_public_redirect_combines_origin_and_slash_without_changing_signed_urls(): void
    {
        $this->app['env'] = 'production';
        $request = \Illuminate\Http\Request::create('http://www.dalytrac.com/en/produit/?utm_source=test');
        $request->setRouteResolver(fn () => app('router')->getRoutes()->match($request));
        $redirect = (new \App\Http\Middleware\SeoPolicy)->handle($request, fn () => response('unexpected'));
        $this->assertSame(301, $redirect->getStatusCode());
        $this->assertSame('https://dalytrac.com/en/produit?utm_source=test', $redirect->headers->get('Location'));
        $signed = \Illuminate\Http\Request::create('http://dalytrac.com/verify-email/1/hash?signature=example');
        $signed->setRouteResolver(fn () => app('router')->getRoutes()->match($signed));
        $response = (new \App\Http\Middleware\SeoPolicy)->handle($signed, fn () => response('signed middleware still applies'));
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('noindex, follow', $response->headers->get('X-Robots-Tag'));
    }
    public function test_analytics_tracks_registration_but_not_login_or_private_head(): void
    {
        $this->get('/register')->assertOk()->assertSee('https://www.googletagmanager.com/gtag/js?id=G-MDJNVZY2WF', false);
        $this->get('/login')->assertOk()->assertDontSee('googletagmanager.com')->assertDontSee('gtag(');
        $html = view('dashboard_layouts.head')->render();
        $this->assertStringNotContainsString('googletagmanager.com', $html);
        $this->assertStringNotContainsString('google-analytics.com', $html);
        foreach (['manager/seo', 'admin/projects', 'admin/tasks', 'admin/invoices'] as $uri) {
            $request = \Illuminate\Http\Request::create('/'.$uri);
            $route = new \Illuminate\Routing\Route('GET', $uri, fn () => '');
            $route->name(str_replace('/', '.', $uri));
            $request->setRouteResolver(fn () => $route);
            $this->app->instance('request', $request);
            $this->assertStringNotContainsString('googletagmanager.com', view('components.public.head')->render());
        }
    }
}
