<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\ContentBlock;
use App\Models\ContentRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ContentPublisher
{
    public function __construct(private ContentValidator $validator) {}

    public function draft(ContentBlock $block, array $payload, int $expectedRevision, User $user, ?ContentRevision $restored = null): void
    {
        Gate::forUser($user)->authorize('update', $block);
        if ($restored) {
            abort_unless($restored->content_block_id === $block->id, 404);
            $payload = $restored->payload;
        }
        $payload = $this->validator->validate($block->key, $payload);
        DB::transaction(function () use ($block, $payload, $expectedRevision, $user, $restored) {
            $locked = $this->lock($block, $expectedRevision);
            $revision = $locked->revisions()->create(['payload' => $payload, 'created_by' => $user->id]);
            $locked->current_revision_id = $revision->id;
            $locked->save();
            AuditEvent::create([
                'user_id' => $user->id, 'content_block_id' => $locked->id,
                'action' => $restored ? 'content.restored' : 'content.drafted',
                'metadata' => ['block' => $locked->key, 'revision_id' => $revision->id, 'restored_from' => $restored?->id],
            ]);
        });
    }

    public function publish(ContentBlock $block, int $expectedRevision, User $user): void
    {
        Gate::forUser($user)->authorize('publish', $block);
        DB::transaction(function () use ($block, $expectedRevision, $user) {
            $locked = $this->lock($block, $expectedRevision);
            $this->validator->validate($block->key, $locked->currentRevision->payload, true);
            if ($locked->published_revision_id === $locked->current_revision_id) {
                return;
            }
            $locked->published_revision_id = $locked->current_revision_id;
            $locked->published_at = now();
            $locked->save();
            AuditEvent::create([
                'user_id' => $user->id, 'content_block_id' => $locked->id, 'action' => 'content.published',
                'metadata' => ['block' => $locked->key, 'revision_id' => $locked->published_revision_id],
            ]);
        });
    }

    private function lock(ContentBlock $block, int $expectedRevision): ContentBlock
    {
        $locked = ContentBlock::whereKey($block->id)->lockForUpdate()->firstOrFail();
        if ($locked->current_revision_id !== $expectedRevision) {
            throw ValidationException::withMessages(['revision_id' => 'Otra persona actualizó este contenido. Copia tus cambios y recarga antes de continuar.']);
        }

        return $locked;
    }
}
