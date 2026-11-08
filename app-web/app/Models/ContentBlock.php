<?php

namespace App\Models;

use App\Support\ContentSchema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentBlock extends Model
{
    protected $fillable = ['key'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function currentRevision(): BelongsTo
    {
        return $this->belongsTo(ContentRevision::class, 'current_revision_id');
    }

    public function publishedRevision(): BelongsTo
    {
        return $this->belongsTo(ContentRevision::class, 'published_revision_id');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(ContentRevision::class);
    }

    public function hasDraft(): bool
    {
        return $this->current_revision_id !== $this->published_revision_id;
    }

    public function label(): string
    {
        return ContentSchema::definitions()[$this->key]['label'];
    }
}
