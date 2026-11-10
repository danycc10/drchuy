<?php

namespace App\Services;

use App\Models\{AuditEvent, ContentBlock, ReviewRequest, Service, User};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class EditorialReview
{
    public static function targetKey(Service|ContentBlock $target): string
    {
        return $target instanceof Service ? 'service_id' : 'content_block_id';
    }

    public function submit(Service|ContentBlock $target, int $revision, ?string $note, User $user): ReviewRequest
    {
        Gate::forUser($user)->authorize('update', $target);

        return DB::transaction(function () use ($target, $revision, $note, $user) {
            $locked = $target->newQuery()->whereKey($target->id)->lockForUpdate()->firstOrFail();
            if ($locked->current_revision_id !== $revision || ! $locked->hasDraft()) {
                throw ValidationException::withMessages(['revision_id' => 'El borrador cambió o ya fue publicado. Recarga antes de solicitar revisión.']);
            }
            $key = self::targetKey($locked);
            if (ReviewRequest::where($key, $locked->id)->where('revision_id', $revision)->exists()) {
                throw ValidationException::withMessages(['revision_id' => 'Esta versión ya tiene una solicitud. Guarda un nuevo borrador si necesitas enviarla otra vez.']);
            }
            $review = ReviewRequest::create([
                $key => $locked->id, 'revision_id' => $revision, 'base_revision_id' => $locked->published_revision_id,
                'requested_by' => $user->id, 'note' => $note, 'status' => 'pending',
            ]);
            $this->audit($locked, $user, 'review.requested', $review->id);

            return $review;
        });
    }

    public function decide(ReviewRequest $review, User $user, string $decision, ?string $response): void
    {
        $target = $review->target();
        Gate::forUser($user)->authorize('publish', $target);
        DB::transaction(function () use ($review, $target, $user, $decision, $response) {
            $locked = $target->newQuery()->whereKey($target->id)->lockForUpdate()->firstOrFail();
            $request = ReviewRequest::whereKey($review->id)->lockForUpdate()->firstOrFail();
            if ($request->status !== 'pending' || $locked->current_revision_id !== $request->revision_id || ! $locked->hasDraft()) {
                throw ValidationException::withMessages(['review' => 'Esta solicitud ya fue atendida o el borrador cambió. Revisa la versión actual.']);
            }
            if ($decision === 'publish') {
                $publisher = app($locked instanceof Service ? ServicePublisher::class : ContentPublisher::class);
                $publisher->publish($locked, $request->revision_id, $user);
                $request->refresh();
                $request->response = $response;
                $request->save();
            } else {
                $request->update(['status' => 'changes_requested', 'response' => $response, 'reviewed_by' => $user->id, 'reviewed_at' => now()]);
                $this->audit($locked, $user, 'review.changes_requested', $request->id);
            }
        });
    }

    // Publishers already hold the target lock; keep this lock order everywhere.
    public static function onDraft(Service|ContentBlock $target): void
    {
        ReviewRequest::where(self::targetKey($target), $target->id)->where('status', 'pending')
            ->where('revision_id', '!=', $target->current_revision_id)->update(['status' => 'superseded']);
    }

    public static function onPublish(Service|ContentBlock $target, User $user): void
    {
        ReviewRequest::where(self::targetKey($target), $target->id)->where('revision_id', $target->current_revision_id)
            ->whereIn('status', ['pending', 'changes_requested'])->update(['status' => 'published', 'reviewed_by' => $user->id, 'reviewed_at' => now()]);
    }

    private function audit(Service|ContentBlock $target, User $user, string $action, int $id): void
    {
        AuditEvent::create(['user_id' => $user->id, self::targetKey($target) => $target->id, 'action' => $action,
            'metadata' => ['review_id' => $id, 'block' => $target instanceof ContentBlock ? $target->key : null]]);
    }
}
