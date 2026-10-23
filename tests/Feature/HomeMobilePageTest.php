<?php

namespace Tests\Feature;

use Tests\TestCase;

class HomeMobilePageTest extends TestCase
{
    public function test_home_keeps_its_content_and_language_links_in_every_locale(): void
    {
        foreach (['fr', 'en', 'nl', 'es'] as $locale) {
            $response = $this->withSession(['locale' => $locale])->get(route('welcome', ['locale' => $locale]));
            $response->assertOk()->assertSee('dalytrac-home-mobile.css', false);
            $html = $response->getContent();
            $dom = new \DOMDocument;
            @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
            $xpath = new \DOMXPath($dom);
            $this->assertSame(10, $xpath->query('//main/section[@data-home-section]')->length);
            $this->assertSame(4, $xpath->query('//nav[@id="home-mobile-nav"]//div[@class="dt-home-mobile-languages"]/a')->length);
            $current = $xpath->query('//nav[@id="home-mobile-nav"]//a[@aria-current="true"]')->item(0);
            $this->assertNotNull($current);
            $this->assertSame(route('welcome', ['locale' => $locale]), $current->getAttribute('href'));
            $response->assertSee(trans('messages.public_home_title', [], $locale));
        }
    }

    public function test_other_public_pages_keep_their_existing_header(): void
    {
        foreach (['produit', 'tarifs'] as $route) {
            $this->get(route($route, ['locale' => 'fr']))->assertOk()
                ->assertDontSee('dalytrac-home-mobile.css', false)
                ->assertDontSee('dt-home-mobile-languages', false)
                ->assertSee('aria-controls="main-nav"', false);
        }
    }
}
