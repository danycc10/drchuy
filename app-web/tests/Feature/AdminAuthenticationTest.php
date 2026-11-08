<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AdminAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_panel_requires_authentication_and_does_not_offer_registration(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertOk()->assertHeader('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $this->get('/register')->assertNotFound();
    }

    public function test_valid_user_can_login_and_logout(): void
    {
        $user = User::factory()->create(['role' => Role::Owner]);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk()->assertSee('Borradores por revisar');
        $this->post('/admin/logout')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_disabled_account_cannot_login_or_keep_an_existing_session(): void
    {
        $user = User::factory()->create(['is_active' => false]);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_login_attempts_are_limited(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_recovery_uses_laravel_tokens_and_does_not_reveal_unknown_emails(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/admin/forgot-password', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
        $message = session('status');
        $this->post('/admin/forgot-password', ['email' => 'unknown@example.test'])->assertSessionHas('status', $message);
        $token = Password::createToken($user);
        $this->post('/admin/reset-password', [
            'email' => $user->email, 'token' => $token,
            'password' => 'New-secure-password-2026', 'password_confirmation' => 'New-secure-password-2026',
        ])->assertRedirect('/admin/login');
        $this->assertTrue(Hash::check('New-secure-password-2026', $user->fresh()->password));
        $this->post('/admin/reset-password', [
            'email' => $user->email, 'token' => $token,
            'password' => 'Another-password-2026', 'password_confirmation' => 'Another-password-2026',
        ])->assertSessionHasErrors('email');
    }
}
