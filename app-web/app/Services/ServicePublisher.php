<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Service;
use App\Models\ServiceRevision;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ServicePublisher
{
    public function draft(Service $service, array $payload, int $expectedRevision, User $user, ?ServiceRevision $restored = null): void
    {
        Gate::forUser($user)->authorize('update', $service);
        DB::transaction(function () use ($service, $payload, $expectedRevision, $user, $restored) {
            $locked = $this->lock($service, $expectedRevision);
            if ($restored) {
                abort_unless($restored->service_id === $locked->id, 404);
                $payload = $restored->payload;
            }
            $revision = $locked->revisions()->create(['payload' => $payload, 'created_by' => $user->id]);
            $locked->current_revision_id = $revision->id;
            $locked->save();
            AuditEvent::create([
                'user_id' => $user->id, 'service_id' => $locked->id,
                'action' => $restored ? 'service.restored' : 'service.drafted',
                'metadata' => ['revision_id' => $revision->id, 'restored_from' => $restored?->id],
            ]);
        });
    }

    public function publish(Service $service, int $expectedRevision, User $user): void
    {
        Gate::forUser($user)->authorize('publish', $service);
        DB::transaction(function () use ($service, $expectedRevision, $user) {
            $locked = $this->lock($service, $expectedRevision);
            $payload = $locked->currentRevision->payload;
            foreach (['es', 'en'] as $locale) {
                if (blank($payload[$locale]['name'] ?? null) || blank($payload[$locale]['description'] ?? null)) {
                    throw ValidationException::withMessages(['revision_id' => 'Completa ambos idiomas antes de publicar.']);
                }
            }
            if ($locked->published_revision_id === $locked->current_revision_id) {
                return;
            }
            $locked->published_revision_id = $locked->current_revision_id;
            $locked->published_at = now();
            $locked->save();
            AuditEvent::create([
                'user_id' => $user->id, 'service_id' => $locked->id, 'action' => 'service.published',
                'metadata' => ['revision_id' => $locked->published_revision_id],
            ]);
        });
    }

    private function lock(Service $service, int $expectedRevision): Service
    {
        $locked = Service::whereKey($service->id)->lockForUpdate()->firstOrFail();
        if ($locked->current_revision_id !== $expectedRevision) {
            throw ValidationException::withMessages(['revision_id' => 'Otra persona actualizó este servicio. Copia tus cambios y recarga la página antes de continuar.']);
        }

        return $locked;
    }
}
