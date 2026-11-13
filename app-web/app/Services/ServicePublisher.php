<?php

namespace App\Services;

use App\Models\AuditEvent;
use App\Models\Service;
use App\Models\ServiceRevision;
use App\Models\User;
use App\Models\MediaAsset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ServicePublisher
{
    /** Guarda la versión y la publica en el mismo paso: el sitio cambia al instante. */
    public function save(Service $service, array $payload, int $expectedRevision, User $user, ?ServiceRevision $restored = null): void
    {
        DB::transaction(function () use ($service, $payload, $expectedRevision, $user, $restored) {
            $this->draft($service, $payload, $expectedRevision, $user, $restored);
            $fresh = $service->fresh();
            $this->publish($fresh, $fresh->current_revision_id, $user);
        });
    }

    public function draft(Service $service, array $payload, int $expectedRevision, User $user, ?ServiceRevision $restored = null): void
    {
        Gate::forUser($user)->authorize('update', $service);
        DB::transaction(function () use ($service, $payload, $expectedRevision, $user, $restored) {
            $user = app(AccountSecurity::class)->lockActor($user);
            Gate::forUser($user)->authorize('update', $service);
            $locked = $this->lock($service, $expectedRevision);
            if ($restored) {
                abort_unless($restored->service_id === $locked->id, 404);
                $payload = $restored->payload;
            }
            $imageId = $restored ? $restored->image_id : (array_key_exists('image_id', $payload) ? $payload['image_id'] : $locked->currentRevision->image_id);
            unset($payload['image_id']);
            if (! $restored) {
                foreach (['es', 'en'] as $locale) {
                    foreach (['preparation', 'duration', 'bring'] as $field) {
                        if (! array_key_exists($field, $payload[$locale]) && array_key_exists($field, $locked->currentRevision->payload[$locale])) {
                            $payload[$locale][$field] = $locked->currentRevision->payload[$locale][$field];
                        }
                    }
                }
            }
            if (! $restored && ! array_key_exists('image_position', $payload) && isset($locked->currentRevision->payload['image_position'])) {
                $payload['image_position'] = $locked->currentRevision->payload['image_position'];
            }
            $imageId = validator(['image_id' => $imageId], ['image_id' => 'nullable|integer|min:1'])->validate()['image_id'];
            if ($imageId && ! MediaAsset::whereKey($imageId)->lockForUpdate()->first()) {
                throw ValidationException::withMessages(['image_id' => 'La imagen ya no está disponible. Selecciona otra de la biblioteca.']);
            }
            $revision = $locked->revisions()->create(['payload' => $payload, 'image_id' => $imageId, 'created_by' => $user->id]);
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
            $user = app(AccountSecurity::class)->lockActor($user);
            Gate::forUser($user)->authorize('publish', $service);
            $locked = $this->lock($service, $expectedRevision);
            $payload = $locked->currentRevision->payload;
            foreach (['es', 'en'] as $locale) {
                if (blank($payload[$locale]['name'] ?? null) || blank($payload[$locale]['description'] ?? null)) {
                    throw ValidationException::withMessages(['revision_id' => 'Completa ambos idiomas antes de publicar.']);
                }
            }
            foreach (['preparation', 'duration', 'bring'] as $field) {
                if (filled($payload['es'][$field] ?? null) !== filled($payload['en'][$field] ?? null)) {
                    throw ValidationException::withMessages(['revision_id' => 'Completa la información para la visita en ambos idiomas antes de publicar.']);
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
