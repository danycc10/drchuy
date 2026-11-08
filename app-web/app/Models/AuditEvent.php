<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditEvent extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['user_id', 'service_id', 'action', 'metadata'];

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
            'user.created' => 'Cuenta creada',
            default => $this->action,
        };
    }
}
