<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    public function view(User $user, MediaAsset $media): bool
    {
        return $user->is_active;
    }

    public function create(User $user): bool
    {
        return $user->is_active;
    }

    public function delete(User $user, MediaAsset $media): bool
    {
        return $user->is_active && in_array($user->role, [Role::Owner, Role::Administrator], true);
    }

    public function download(User $user, MediaAsset $media): bool
    {
        return $this->delete($user, $media);
    }
}
