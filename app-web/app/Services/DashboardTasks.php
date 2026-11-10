<?php

namespace App\Services;

use App\Models\{ContentBlock, ReviewRequest, Service, User};
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

class DashboardTasks
{
    public function forUser(User $user): array
    {
        $reviews = ReviewRequest::with(['service.currentRevision', 'service.category', 'contentBlock.currentRevision', 'requester'])
            ->whereIn('status', ['pending', 'changes_requested'])
            ->where(fn ($query) => $query->whereHas('service', fn ($q) => $q->whereColumn('services.current_revision_id', 'review_requests.revision_id'))
                ->orWhereHas('contentBlock', fn ($q) => $q->whereColumn('content_blocks.current_revision_id', 'review_requests.revision_id')))
            ->latest('updated_at')->get()->filter(fn ($review) => $review->target()->hasDraft());
        $corrections = $reviews->where('status', 'changes_requested')->map(fn ($review) => [
            'title' => $review->label(), 'description' => Str::limit($review->response, 180),
            'url' => route('admin.reviews.show', $review), 'action' => 'Ver correcciones', 'icon' => 'edit',
        ])->values();
        $pending = $reviews->where('status', 'pending');
        $actionable = $pending->filter(fn ($review) => Gate::forUser($user)->allows('publish', $review->target()));
        $toReview = $actionable->map(fn ($review) => [
            'title' => $review->label(), 'description' => 'Enviado por '.($review->requester?->name ?? 'el equipo').'. Compara los cambios antes de decidir.',
            'url' => route('admin.reviews.show', $review), 'action' => 'Abrir revisión', 'icon' => 'document',
        ])->values();
        $inWorkflow = $reviews->map(fn ($review) => EditorialReview::targetKey($review->target()).':'.$review->target()->id)->all();
        $isDraft = fn ($q) => $q->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id');
        $drafts = Service::with(['currentRevision', 'category'])->where($isDraft)->get()->concat(ContentBlock::with('currentRevision')->where($isDraft)->get())
            ->reject(fn ($target) => in_array(EditorialReview::targetKey($target).':'.$target->id, $inWorkflow, true))
            ->sortByDesc(fn ($target) => [$target->currentRevision->created_by === $user->id, $target->currentRevision->created_at->timestamp])
            ->map(fn ($target) => [
                'title' => $target instanceof Service ? $target->currentRevision->payload['es']['name'] : $target->label(),
                'description' => $target->currentRevision->created_by === $user->id ? 'Continúa con los cambios que guardaste.' : 'Borrador del equipo disponible para preparar.',
                'url' => route($target instanceof Service ? 'admin.services.edit' : 'admin.content.edit', $target),
                'action' => 'Continuar edición', 'icon' => $target instanceof Service ? 'list' : 'document',
            ])->values();

        return ['groups' => [
            ['title' => 'Correcciones por atender', 'description' => 'Empieza por las observaciones que dejó quien revisó.', 'items' => $corrections],
            ['title' => 'Listo para tu revisión', 'description' => 'Puedes resolver estas solicitudes con los permisos de tu cuenta.', 'items' => $toReview],
            ['title' => 'Continuar un borrador', 'description' => 'Tus ediciones recientes aparecen primero.', 'items' => $drafts],
        ], 'waiting' => $pending->count() - $actionable->count()];
    }
}
