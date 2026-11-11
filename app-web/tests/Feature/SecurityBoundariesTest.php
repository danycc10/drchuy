<?php

namespace Tests\Feature;

use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SecurityBoundariesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Request::setTrustedHosts([]);
        TrustHosts::flushState();
        parent::tearDown();
    }

    public function test_production_rejects_untrusted_hosts_and_accepts_only_the_configured_domain(): void
    {
        $this->withoutVite();
        config(['app.url' => 'https://clinic.example', 'app.debug' => false]);
        $this->app->instance('env', 'production');
        $this->get('https://clinic.example/admin/login')->assertOk();
        $this->get('https://untrusted.example/admin/login')->assertStatus(400);
        $this->get('https://other.clinic.example/admin/login')->assertStatus(400);
    }

    public function test_private_disk_has_no_generic_download_or_upload_routes(): void
    {
        $this->assertFalse(config('filesystems.disks.local.serve'));
        $this->assertFalse(Route::has('storage.local'));
        $this->assertFalse(Route::has('storage.local.upload'));
        $this->get('/storage/backups/private.zip')->assertNotFound();
        $this->put('/storage/media/unauthorized.webp')->assertNotFound();
    }

    public function test_real_csrf_check_rejects_cross_site_mutations_without_a_token(): void
    {
        $this->app->bind(PreventRequestForgery::class, fn ($app) => new class($app, $app['encrypter']) extends PreventRequestForgery {
            protected function runningUnitTests() { return false; }
        });
        $this->withHeaders(['Sec-Fetch-Site' => 'cross-site'])->post('/admin/login', ['email' => 'unknown@example.test', 'password' => 'irrelevant'])->assertStatus(419);
        $this->withSession(['_token' => 'security-test-token'])->post('/idioma', ['locale' => 'en', 'page' => 'home'])->assertStatus(419);
        $this->withSession(['_token' => 'security-test-token'])->post('/idioma', ['locale' => 'en', 'page' => 'home', '_token' => 'security-test-token'])->assertStatus(303);
    }
}
