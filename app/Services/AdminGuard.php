<?php

namespace App\Services;

use App\Models\User;

class AdminGuard
{
    public function adminCount(): int
    {
        return User::query()->where('is_admin', true)->count();
    }

    public function canDemote(User $target): bool
    {
        if (! $target->isAdmin()) {
            return true;
        }

        return $this->adminCount() > 1;
    }

    public function canDelete(User $target): bool
    {
        return $this->canDemote($target);
    }
}
