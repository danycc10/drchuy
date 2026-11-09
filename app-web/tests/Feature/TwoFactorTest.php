<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\TwoFactor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function protectedUser(): array
    {
        $user = User::factory()->create();
        $secret = app(TwoFactor::class)->engine()->createSecret();
        $user->two_factor_secret = $secret;
        $user->two_factor_confirmed_at = now();
        $codes = app(TwoFactor::class)->recoveryCodes($user);
        $user->save();

        return [$user, $secret, $codes];
    }

    private function code(string $secret): string
    {
        return app(TwoFactor::class)->engine()->getCode($secret, now()->timestamp);
    }

    public function test_enrollment_requires_password_and_confirmation_and_keeps_secrets_private(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/admin/security')->assertOk();
        $this->post('/admin/security/begin', ['current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->post('/admin/security/begin', ['current_password' => 'password'])->assertSessionHasNoErrors();
        $secret = decrypt(session('two_factor.setup.secret'));
        $this->assertNull($user->fresh()->two_factor_secret);
        $this->get('/admin/security')->assertOk()->assertSee('data:image/svg+xml;base64,', false)->assertSee($secret);
        $this->post('/admin/security/confirm', ['code' => $this->code($secret)])->assertSessionHasNoErrors()->assertSessionHas('recovery_codes');
        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertSame($secret, $user->two_factor_secret);
        $this->assertNotSame($secret, DB::table('users')->where('id', $user->id)->value('two_factor_secret'));
        $this->assertArrayNotHasKey('two_factor_secret', $user->toArray());
        $codes = decrypt(session('recovery_codes'));
        $this->assertCount(10, $codes);
        $this->assertStringNotContainsString($codes[0], DB::table('users')->where('id', $user->id)->value('two_factor_recovery_codes'));
        $this->get('/admin/security')->assertOk()->assertSee($codes[0]);
        $this->get('/admin/security')->assertOk()->assertDontSee($codes[0])->assertDontSee($secret);
        $this->assertDatabaseHas('audit_events', ['action' => 'security.enabled']);
    }

    public function test_password_alone_cannot_access_any_panel_route_and_totp_cannot_be_replayed(): void
    {
        [$user, $secret] = $this->protectedUser();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/admin/two-factor');
        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/preview/es')->assertRedirect('/admin/login');
        $this->get('/admin/two-factor')->assertOk();
        $this->post('/admin/two-factor', ['code' => $this->code($secret)])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->get('/admin/security')->assertOk();
        $this->post('/admin/logout');
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/admin/two-factor', ['code' => $this->code($secret)])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertFalse(session()->hasOldInput('code'));
        $this->travel(31)->seconds();
        $this->post('/admin/two-factor', ['code' => $this->code($secret)])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_is_single_use_and_password_reset_never_removes_second_factor(): void
    {
        [$user, $secret, $codes] = $this->protectedUser();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/admin/two-factor', ['code' => strtolower($codes[0])])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
        $this->assertCount(9, $user->fresh()->two_factor_recovery_codes);
        $this->post('/admin/logout');
        $token = Password::createToken($user);
        $this->post('/admin/reset-password', ['email' => $user->email, 'token' => $token, 'password' => 'Changed-password-2026', 'password_confirmation' => 'Changed-password-2026'])->assertRedirect('/admin/login');
        $this->assertNotNull($user->fresh()->two_factor_confirmed_at);
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'Changed-password-2026'])->assertRedirect('/admin/two-factor');
        $this->post('/admin/two-factor', ['code' => $codes[0]])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->post('/admin/two-factor', ['code' => $codes[1]])->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_expires_and_changed_accounts_cannot_finish_pending_login(): void
    {
        [$user, $secret] = $this->protectedUser();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password']);
        $this->travel(6)->minutes();
        $this->post('/admin/two-factor', ['code' => $this->code($secret)])->assertRedirect('/admin/login');
        $this->assertGuest();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password']);
        $user->increment('auth_version');
        $this->post('/admin/two-factor', ['code' => $this->code($secret)])->assertRedirect('/admin/login');
        $this->assertGuest();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password']);
        $user->is_active = false;
        $user->save();
        $this->post('/admin/two-factor', ['code' => $this->code($secret)])->assertRedirect('/admin/login');
        $this->assertGuest();
    }

    public function test_failed_codes_are_rate_limited_across_new_password_challenges(): void
    {
        [$user, $secret, $codes] = $this->protectedUser();
        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', ['email' => $user->email, 'password' => 'password']);
            $this->post('/admin/two-factor', ['code' => 'not-a-valid-code'])->assertSessionHasErrors('code');
        }
        $this->post('/admin/two-factor', ['code' => $codes[0]])->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertCount(10, $user->fresh()->two_factor_recovery_codes);
    }

    public function test_rotation_and_disabling_require_both_factors_and_invalidate_previous_sessions(): void
    {
        [$user, $secret, $codes] = $this->protectedUser();
        $this->post('/admin/login', ['email' => $user->email, 'password' => 'password']);
        $this->post('/admin/two-factor', ['code' => $codes[0]]);
        $this->post('/admin/security/recovery', ['current_password' => 'wrong', 'code' => $codes[1]])->assertSessionHasErrors('current_password');
        $this->post('/admin/security/recovery', ['current_password' => 'password', 'code' => $codes[1]])->assertSessionHasNoErrors();
        $newCodes = decrypt(session('recovery_codes'));
        $this->assertSame(1, $user->fresh()->auth_version);
        $this->get('/admin/security')->assertOk();
        $this->delete('/admin/security', ['current_password' => 'password', 'code' => $codes[2]])->assertSessionHasErrors('code');
        $this->delete('/admin/security', ['current_password' => 'password', 'code' => $newCodes[0]])->assertSessionHasNoErrors();
        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_recovery_codes);
        $this->get('/admin/security')->assertOk();
        $this->withSession(['auth.version' => 0, 'auth.2fa_version' => 0])->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_existing_unverified_sessions_cannot_bypass_two_factor_and_setup_expires(): void
    {
        [$user] = $this->protectedUser();
        $this->actingAs($user)->get('/admin')->assertRedirect('/admin/login');
        $plain = User::factory()->create();
        $this->actingAs($plain)->post('/admin/security/begin', ['current_password' => 'password']);
        $secret = decrypt(session('two_factor.setup.secret'));
        $this->travel(11)->minutes();
        $this->post('/admin/security/confirm', ['code' => $this->code($secret)])->assertSessionHasErrors('code');
        $this->assertNull($plain->fresh()->two_factor_secret);
        $this->get('/admin/security')->assertDontSee($secret);
    }

    public function test_console_recovery_requires_confirmation_and_invalidates_all_credentials_except_password(): void
    {
        [$user] = $this->protectedUser();
        $this->artisan('app:reset-two-factor')->expectsQuestion('Correo de la cuenta', $user->email)
            ->expectsConfirmation('¿Verificaste la identidad del titular por un medio independiente y autorizas el restablecimiento?', 'no')->assertFailed();
        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->artisan('app:reset-two-factor')->expectsQuestion('Correo de la cuenta', $user->email)
            ->expectsConfirmation('¿Verificaste la identidad del titular por un medio independiente y autorizas el restablecimiento?', 'yes')->assertSuccessful();
        $this->assertNull($user->fresh()->two_factor_secret);
        $this->assertSame(1, $user->fresh()->auth_version);
        $this->assertDatabaseHas('audit_events', ['action' => 'security.reset_console']);
    }
}
