<?php

namespace App\Policies;

use App\Models\Service;
use App\Models\User;

class ServicePolicy
{
    public function update(User $user, Service $service): bool
    {
        return $user->is_active;
    }

    // El consultorio lo administra una sola persona: publicar no requiere revisión ni un rol superior.
    public function publish(User $user, Service $service): bool
    {
        return $user->is_active;
    }
}
