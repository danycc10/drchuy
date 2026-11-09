<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicLanguageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_language_selection_keeps_the_root_url_and_survives_requests(): void
    {
        $this->get('/')->assertOk()->assertHeader('Content-Language', 'es')->assertSee('Atención personalizada');
        $this->post('/idioma', ['locale' => 'en', 'page' => 'home', 'anchor' => 'hours'])
            ->assertStatus(303)->assertRedirect(route('home').'#hours')->assertSessionHas('public_locale', 'en');
        $this->withCookie(session()->getName(), session()->getId());
        $response = $this->get('/')->assertOk()->assertHeader('Content-Language', 'en')->assertSee('Personalized care');
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $response->assertHeader('Vary', 'Cookie');
        $this->post('/idioma', ['locale' => 'es', 'page' => 'home'])
            ->assertRedirect(route('home'));
        $this->get('/')->assertHeader('Content-Language', 'es');
    }

    public function test_privacy_language_selection_returns_to_a_clean_local_destination(): void
    {
        $this->post('/idioma', ['locale' => 'en', 'page' => 'privacy', 'anchor' => 'hours'])
            ->assertStatus(303)->assertRedirect(route('privacy'))->assertSessionHas('public_locale', 'en');
    }

    public function test_old_language_urls_redirect_and_remember_the_requested_language(): void
    {
        foreach (['es', 'en'] as $locale) {
            $this->get('/'.$locale)->assertRedirect(route('home'))->assertSessionHas('public_locale', $locale);
            $privacy = $locale === 'es' ? '/es/aviso-de-privacidad' : '/en/privacy-policy';
            $this->get($privacy)->assertRedirect(route('privacy'))->assertSessionHas('public_locale', $locale);
        }
        $this->get('/fr')->assertNotFound();
    }

    public function test_language_and_redirect_parameters_are_restricted(): void
    {
        foreach ([
            ['locale' => 'fr', 'page' => 'home'],
            ['locale' => ['en'], 'page' => 'home'],
            ['locale' => 'en', 'page' => 'https://example.com'],
            ['locale' => 'en', 'page' => 'home', 'anchor' => '//example.com'],
        ] as $data) {
            $this->postJson('/idioma', $data)->assertUnprocessable();
        }
        $this->withSession(['public_locale' => 'invalid'])->get('/')->assertOk()->assertHeader('Content-Language', 'es');
    }

    public function test_metadata_and_sitemap_use_one_canonical_url_without_language_parameters(): void
    {
        config(['clinic.indexable' => true]);
        $base = rtrim(config('app.url'), '/');
        foreach (['es', 'en'] as $locale) {
            $response = $this->withSession(['public_locale' => $locale])->get('/')->assertOk()
                ->assertSee('<link rel="canonical" href="'.$base.'/">', false)
                ->assertSee('property="og:url" content="'.$base.'/"', false)
                ->assertDontSee('hreflang=', false)
                ->assertDontSee('href="'.$base.'/es', false)
                ->assertDontSee('href="'.$base.'/en', false)
                ->assertSee('data-language-form', false);
            preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/s', $response->getContent(), $matches);
            $this->assertSame($base.'/', json_decode($matches[1], true, flags: JSON_THROW_ON_ERROR)['url']);
        }
        $this->get('/sitemap.xml')->assertOk()->assertSee('<loc>'.$base.'/</loc>', false)
            ->assertDontSee('/es</loc>', false)->assertDontSee('/en</loc>', false);
    }
}
