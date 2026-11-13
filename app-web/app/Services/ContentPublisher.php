<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\ContentBlock;
use App\Models\ContentRevision;
use App\Models\User;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ContentPublisher
{
    public function __construct(private ContentValidator $validator) {}

    /** Guarda la versión y la publica en el mismo paso; valida con las reglas de publicación para que los errores lleguen al campo. */
    public function save(ContentBlock $block, array $payload, int $expectedRevision, User $user, ?ContentRevision $restored = null): void
    {
        if ($restored) {
            abort_unless($restored->content_block_id === $block->id, 404);
        }
        $this->validator->validate($block->key, $restored ? $restored->payload : $payload, true);
        DB::transaction(function () use ($block, $payload, $expectedRevision, $user, $restored) {
            $this->draft($block, $payload, $expectedRevision, $user, $restored);
            $fresh = $block->fresh();
            $this->publish($fresh, $fresh->current_revision_id, $user);
        });
    }

    public function draft(ContentBlock $block, array $payload, int $expectedRevision, User $user, ?ContentRevision $restored = null): void
    {
        Gate::forUser($user)->authorize('update', $block);
        if ($restored) {
            abort_unless($restored->content_block_id === $block->id, 404);
            $payload = $restored->payload;
        }
        $payload = $this->validator->validate($block->key, $payload);
        DB::transaction(function () use ($block, $payload, $expectedRevision, $user, $restored) {
            $user = app(AccountSecurity::class)->lockActor($user);
            Gate::forUser($user)->authorize('update', $block);
            $locked = $this->lock($block, $expectedRevision);
            $mediaIds = $block->key === 'media' ? array_values(array_unique(array_filter($payload['shared']))) : [];
            if ($mediaIds && MediaAsset::whereIn('id', $mediaIds)->orderBy('id')->lockForUpdate()->get()->count() !== count($mediaIds)) {
                throw ValidationException::withMessages(['shared' => 'Una fotografía dejó de estar disponible. Selecciona otra de la biblioteca.']);
            }
            $revision = $locked->revisions()->create(['payload' => $payload, 'created_by' => $user->id]);
            $revision->mediaAssets()->attach($mediaIds);
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
            $user = app(AccountSecurity::class)->lockActor($user);
            Gate::forUser($user)->authorize('publish', $block);
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
