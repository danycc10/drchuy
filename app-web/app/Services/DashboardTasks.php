<?php

namespace App\Services;

use App\Models\{ContentBlock, Service, User};

class DashboardTasks
{
    /**
     * Con la publicación en un solo paso, lo único pendiente son versiones guardadas que todavía
     * no se muestran en el sitio (contenido inicial sin publicar o un guardado interrumpido).
     */
    public function forUser(User $user): array
    {
        $isDraft = fn ($q) => $q->whereNull('published_revision_id')->orWhereColumn('current_revision_id', '!=', 'published_revision_id');
        $pending = Service::with(['currentRevision', 'category'])->where($isDraft)->get()->concat(ContentBlock::with('currentRevision')->where($isDraft)->get())
            ->sortByDesc(fn ($target) => [$target->currentRevision->created_by === $user->id, $target->currentRevision->created_at->timestamp])
            ->map(fn ($target) => [
                'title' => $target instanceof Service ? $target->currentRevision->payload['es']['name'] : $target->label(),
                'description' => $target->published_revision_id ? 'La última versión guardada todavía no se muestra en el sitio.' : 'Este contenido aún no aparece en el sitio.',
                'url' => route($target instanceof Service ? 'admin.services.edit' : 'admin.content.edit', $target),
                'action' => 'Abrir y publicar', 'icon' => $target instanceof Service ? 'list' : 'document',
            ])->values();

        return ['groups' => [
            ['title' => 'Pendiente de publicar', 'description' => 'Abre cada elemento y pulsa Publicar ahora, o guarda tus cambios para publicarlo.', 'items' => $pending],
        ]];
    }
}
