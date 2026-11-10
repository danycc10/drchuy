<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewRequest extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['reviewed_at' => 'datetime'];
    }

    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function contentBlock(): BelongsTo { return $this->belongsTo(ContentBlock::class); }
    public function requester(): BelongsTo { return $this->belongsTo(User::class, 'requested_by'); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }

    public function target(): Service|ContentBlock
    {
        return $this->service ?? $this->contentBlock;
    }

    public function label(): string
    {
        return $this->service_id ? $this->service->currentRevision->payload['es']['name'] : $this->contentBlock->label();
    }

    public static function statuses(): array
    {
        return ['pending' => 'Pendiente', 'changes_requested' => 'Cambios solicitados', 'published' => 'Publicado', 'superseded' => 'Versión reemplazada'];
    }
}
