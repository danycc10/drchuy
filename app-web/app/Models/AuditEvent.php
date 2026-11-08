<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'service_id', 'content_block_id', 'action', 'metadata'];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function label(): string
    {
        return match ($this->action) {
            'service.drafted' => 'Borrador guardado',
            'service.published' => 'Versión publicada',
            'service.restored' => 'Versión recuperada como borrador',
            'content.drafted' => 'Contenido guardado como borrador',
            'content.published' => 'Contenido publicado',
            'content.restored' => 'Contenido recuperado como borrador',
            'user.created' => 'Cuenta creada',
            default => $this->action,
        };
    }

    public function subjectLabel(): string
    {
        return isset($this->metadata['block'])
            ? (\App\Support\ContentSchema::definitions()[$this->metadata['block']]['label'] ?? 'Contenido')
            : ($this->service?->currentRevision?->payload['es']['name'] ?? 'Administración');
    }
}
