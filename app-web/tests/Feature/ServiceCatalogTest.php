<?php

namespace Tests\Feature;

use App\Models\Service;
use Database\Seeders\ServiceCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceCatalogTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_is_repeatable_and_does_not_overwrite_editorial_work(): void
    {
        $this->seed(ServiceCatalogSeeder::class);
        $service = Service::firstOrFail();
        $payload = $service->currentRevision->payload;
        $payload['es']['name'] = 'Texto editado';
        $revision = $service->revisions()->create(['payload' => $payload]);
        $service->current_revision_id = $revision->id;
        $service->save();

        $this->seed(ServiceCatalogSeeder::class);

        $this->assertDatabaseCount('service_categories', 5);
        $this->assertDatabaseCount('services', 14);
        $this->assertDatabaseCount('service_revisions', 15);
        $this->assertSame('Texto editado', $service->fresh()->currentRevision->payload['es']['name']);
        $this->assertSame(0, Service::whereNotNull('published_revision_id')->count());
        foreach (Service::with('currentRevision')->get() as $item) {
            $this->assertNotEmpty($item->currentRevision->payload['en']['description']);
        }
    }
}
