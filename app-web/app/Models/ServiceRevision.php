<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRevision extends Model
{
    public const UPDATED_AT = null;

    protected $fillable = ['payload', 'created_by'];

    protected function casts(): array
    {
        return ['payload' => 'array'];
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
