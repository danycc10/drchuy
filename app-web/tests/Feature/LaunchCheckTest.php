<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Artisan, Notification};
use Tests\TestCase;

class LaunchCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_readiness_reports_missing_requirements_without_secrets_or_side_effects(): void
    {
        Notification::fake();
        $this->seed(ContentSeeder::class);
        $owner = User::factory()->create(['role' => Role::Owner]);
        $result = Artisan::call('app:launch-check', ['--json' => true]);
        $output = Artisan::output();
        $checks = collect(json_decode($output, true))->keyBy('check');
        $this->assertSame(1, $result);
        $this->assertFalse($checks['owner_two_factor']['passed']);
        $this->assertFalse($checks['privacy_published']['passed']);
        $this->assertFalse($checks['restore_tested']['passed']);
        $this->assertStringNotContainsString($owner->email, $output);
        $this->assertStringNotContainsString(config('app.key'), $output);
        $this->assertDatabaseCount('backup_runs', 0);
        Notification::assertNothingSent();
    }
}
