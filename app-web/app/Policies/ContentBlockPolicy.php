<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\ContentBlock;
use App\Models\User;
use App\Support\ContentSchema;

class ContentBlockPolicy
{
    public function update(User $user, ContentBlock $block): bool
    {
        return $user->is_active;
    }

    public function publish(User $user, ContentBlock $block): bool
    {
        return $user->is_active && ($user->role === Role::Owner ||
            ($user->role === Role::Administrator && ! (ContentSchema::definitions()[$block->key]['owner_only'] ?? false)));
    }
}
