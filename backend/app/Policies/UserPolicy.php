<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Auth\PermissionName;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAny([
            PermissionName::USERS_VIEW->value,
            PermissionName::USERS_MANAGE->value,
        ]);
    }

    public function view(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return true;
        }

        return $user->canAny([
            PermissionName::USERS_VIEW->value,
            PermissionName::USERS_MANAGE->value,
        ]);
    }

    public function create(User $user): bool
    {
        return $user->canAny([
            PermissionName::USERS_CREATE->value,
            PermissionName::USERS_MANAGE->value,
        ]);
    }

    public function update(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return true;
        }

        return $user->canAny([
            PermissionName::USERS_UPDATE->value,
            PermissionName::USERS_MANAGE->value,
        ]);
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->id === $target->id) {
            return false;
        }

        return $user->canAny([
            PermissionName::USERS_DELETE->value,
            PermissionName::USERS_MANAGE->value,
        ]);
    }
}

