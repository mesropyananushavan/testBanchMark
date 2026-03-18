<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Support\Auth\PermissionName;
use App\Support\Auth\RoleName;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(static function (User $user): ?bool {
            return $user->hasRole(RoleName::SUPER_ADMIN->value) ? true : null;
        });

        Gate::policy(User::class, UserPolicy::class);

        Gate::define('manage-users', static fn (User $user): bool => $user->hasPermissionTo(PermissionName::USERS_MANAGE->value));
        Gate::define('manage-roles', static fn (User $user): bool => $user->hasPermissionTo(PermissionName::ROLES_MANAGE->value));
        Gate::define('manage-permissions', static fn (User $user): bool => $user->hasPermissionTo(PermissionName::PERMISSIONS_MANAGE->value));
    }
}
