<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MediaAsset extends Model
{
    protected $guarded = ['id'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function revisions(): BelongsToMany
    {
        return $this->belongsToMany(ContentRevision::class, 'content_revision_media');
    }

    public function path(string $variant = 'display'): string
    {
        return $this->directory.'/'.($variant === 'original' ? 'original.'.$this->extension : $variant.'.webp');
    }

    public function url(bool $preview = false, string $variant = 'display'): string
    {
        return route($preview ? 'admin.media.image' : 'media.image', [$this, $variant]);
    }

    public function isPublished(): bool
    {
        return ContentBlock::where('key', 'media')->whereHas('publishedRevision.mediaAssets', fn ($query) => $query->whereKey($this->id))->exists();
    }
}
