<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $catalog = json_decode(file_get_contents(database_path('content/services.json')), true, 512, JSON_THROW_ON_ERROR);
        DB::transaction(function () use ($catalog) {
            foreach ($catalog['categories'] as $category) {
                ServiceCategory::firstOrCreate(['slug' => $category['slug']], $category);
            }
            foreach ($catalog['services'] as $item) {
                $service = Service::firstOrCreate(['source_id' => $item['source_id']], [
                    'service_category_id' => ServiceCategory::where('slug', $item['category'])->firstOrFail()->id,
                ]);
                if ($service->wasRecentlyCreated) {
                    $revision = $service->revisions()->create(['payload' => $item['payload']]);
                    $service->current_revision_id = $revision->id;
                    $service->save();
                }
            }
        });
    }
}
