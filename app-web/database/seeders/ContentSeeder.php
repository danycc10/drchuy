<?php

namespace Database\Seeders;

use App\Models\ContentBlock;
use App\Support\ContentSchema;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            foreach (ContentSchema::definitions() as $key => $schema) {
                $block = ContentBlock::firstOrCreate(['key' => $key]);
                if ($block->wasRecentlyCreated) {
                    $revision = $block->revisions()->create(['payload' => ContentSchema::initial($key)]);
                    $block->current_revision_id = $revision->id;
                    $block->save();
                }
            }
        });
    }
}
