<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function update(User $user, Service $service): bool
    {
        return $user->is_active;
    }

    public function publish(User $user, Service $service): bool
    {
        return $user->is_active && ($user->role === Role::Owner ||
            ($user->role === Role::Administrator && $service->category->slug !== 'procedures'));
    }
}
