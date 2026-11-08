<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = ['source_id', 'service_category_id'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }

    public function currentRevision(): BelongsTo
    {
        return $this->belongsTo(ServiceRevision::class, 'current_revision_id');
    }

    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(ServiceRevision::class, 'published_revision_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ServiceRevision::class);
    }

    public function hasDraft(): bool
    {
        return $this->current_revision_id !== $this->published_revision_id;
    }
}
