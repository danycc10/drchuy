<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Tests\TestCase;

class ProductionHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Request::setTrustedHosts([]);
        TrustHosts::flushState();
        parent::tearDown();
    }

    public function test_public_pages_send_baseline_headers_and_hsts_only_over_https_in_production(): void
    {
        $this->withoutVite();
        $this->get('/')->assertOk()
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=()')
            ->assertHeaderMissing('Strict-Transport-Security');
        $this->get('/favicon.ico')->assertHeaderMissing('Strict-Transport-Security');

        config(['app.url' => 'https://clinic.example', 'app.debug' => false]);
        $this->app->instance('env', 'production');
        $this->get('https://clinic.example/')->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=15552000');
        $this->get('https://clinic.example/sitemap.xml')->assertOk()->assertHeader('Strict-Transport-Security', 'max-age=15552000');
        // Admin routes keep their stricter policy; the baseline does not override it.
        $this->get('https://clinic.example/admin/login')->assertOk()
            ->assertHeader('X-Frame-Options', 'DENY')->assertHeader('Referrer-Policy', 'same-origin')
            ->assertHeader('Strict-Transport-Security', 'max-age=15552000');
    }
}
