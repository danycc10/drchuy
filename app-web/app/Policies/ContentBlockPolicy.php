<?php

namespace App\Policies;

use App\Models\ContentBlock;
use App\Models\User;

class ContentBlockPolicy
{
    public function update(User $user, ContentBlock $block): bool
    {
        return $user->is_active;
    }

    // El consultorio lo administra una sola persona: publicar no requiere revisión ni un rol superior.
    public function publish(User $user, ContentBlock $block): bool
    {
        return $user->is_active;
    }
}
