<?php

namespace Tests\Feature;

use App\Models\ContentBlock;
use Database\Seeders\ContentSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentSeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_initial_content_is_private_and_reimport_preserves_edits(): void
    {
        $this->seed(ContentSeeder::class);
        $block = ContentBlock::where('key', 'home')->firstOrFail();
        $payload = $block->currentRevision->payload;
        $payload['es']['hero'] = 'Cambio editorial';
        $revision = $block->revisions()->create(['payload' => $payload]);
        $block->current_revision_id = $revision->id;
        $block->save();
        $this->seed(ContentSeeder::class);
        $this->assertDatabaseCount('content_blocks', 8);
        $this->assertDatabaseCount('content_revisions', 9);
        $this->assertSame('Cambio editorial', $block->fresh()->currentRevision->payload['es']['hero']);
        $this->assertSame(0, ContentBlock::whereNotNull('published_revision_id')->count());
        $this->assertSame('', ContentBlock::where('key', 'legal')->firstOrFail()->currentRevision->payload['es']['privacy_content']);
    }
}
