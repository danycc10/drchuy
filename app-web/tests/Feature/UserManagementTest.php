<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_only_owner_can_view_create_update_or_generate_setup_links(): void
    {
        $target = User::factory()->create();
        foreach ([Role::Editor, Role::Administrator] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get('/admin/users')->assertForbidden();
            $this->get('/admin/users/create')->assertForbidden();
            $this->get(route('admin.users.edit', $target))->assertForbidden();
            $this->post('/admin/users', [])->assertForbidden();
            $this->put(route('admin.users.update', $target), [])->assertForbidden();
            $this->post(route('admin.users.setup-link', $target), [])->assertForbidden();
        }
    }

    public function test_owner_creates_account_and_generates_a_trusted_single_use_link_without_sending_mail(): void
    {
        Notification::fake();
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner);
        $this->get('/admin/users/create')->assertOk();
        $data = ['name' => 'Nueva editora', 'email' => ' EDITOR@EXAMPLE.TEST ', 'role' => 'editor', 'current_password' => 'password'];
        $this->post('/admin/users', $data)->assertSessionHasNoErrors();
        $target = User::where('email', 'editor@example.test')->firstOrFail();
        $this->assertSame(Role::Editor, $target->role);
        $this->get(route('admin.users.edit', $target))->assertOk()->assertSee('Nueva editora');
        $this->post(route('admin.users.setup-link', $target), ['current_password' => 'password'])->assertSessionHas('setup_link');
        $url = decrypt(session('setup_link'));
        $this->assertStringStartsWith(rtrim(config('app.url'), '/').'/admin/reset-password/', $url);
        $token = basename(parse_url($url, PHP_URL_PATH));
        $this->assertTrue(Password::tokenExists($target, $token));
        $this->post(route('admin.users.setup-link', $target), ['current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertFalse(Password::tokenExists($target, $token));
        Notification::assertNothingSent();
        $this->assertDatabaseHas('audit_events', ['user_id' => $owner->id, 'action' => 'user.created']);
        $this->get('/admin/users?q=Nueva')->assertSee('Nueva editora');
        $this->get('/admin/users?q=Unmatched')->assertDontSee('Nueva editora');
    }

    public function test_password_validation_and_self_protection_preserve_owner_access(): void
    {
        $owner = User::factory()->create(['role' => Role::Owner]);
        $this->actingAs($owner);
        $this->post('/admin/users', ['name' => 'A', 'email' => 'a@example.test', 'role' => 'owner', 'current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->assertFalse(session()->hasOldInput('current_password'));
        $this->put(route('admin.users.update', $owner), ['name' => $owner->name, 'role' => 'editor', 'is_active' => true, 'current_password' => 'password'])->assertSessionHasErrors('role');
        $this->put(route('admin.users.update', $owner), ['name' => $owner->name, 'role' => 'owner', 'is_active' => false, 'current_password' => 'password'])->assertSessionHasErrors('role');
        $this->assertTrue($owner->fresh()->is_active);
        $this->assertSame(Role::Owner, $owner->fresh()->role);
        $this->post(route('admin.users.setup-link', $owner), ['current_password' => 'password'])->assertForbidden();
    }

    public function test_changes_invalidate_sessions_and_tokens_and_reactivation_does_not_restore_old_sessions(): void
    {
        $owner = User::factory()->create(['role' => Role::Owner]);
        $target = User::factory()->create();
        $token = Password::createToken($target);
        $this->actingAs($owner)->put(route('admin.users.update', $target), ['name' => $target->name, 'role' => 'administrator', 'is_active' => false, 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertSame(1, $target->fresh()->auth_version);
        $this->assertFalse(Password::tokenExists($target, $token));
        $this->post(route('admin.users.setup-link', $target), ['current_password' => 'password'])->assertForbidden();
        $this->put(route('admin.users.update', $target), ['name' => $target->name, 'role' => 'administrator', 'is_active' => true, 'current_password' => 'password'])->assertSessionHasNoErrors();
        $this->actingAs($target->fresh())->withSession(['auth.version' => 0])->get('/admin')->assertRedirect('/admin/login');
        $this->assertGuest();
        $this->post('/admin/login', ['email' => $target->email, 'password' => 'password'])->assertRedirect('/admin');
        $this->get('/admin')->assertOk();
    }
}
