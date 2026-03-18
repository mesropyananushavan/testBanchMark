<?php

namespace Database\Seeders;

use App\Support\Auth\RoleName;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guardName = (string) config('auth.defaults.guard', 'web');

        foreach ((array) config('authorization.permissions', []) as $permissionName) {
            Permission::findOrCreate($permissionName, $guardName);
        }

        foreach ((array) config('authorization.roles', []) as $roleName) {
            $role = Role::findOrCreate($roleName, $guardName);
            $role->syncPermissions((array) config("authorization.role_permissions.{$roleName}", []));
        }

        $superAdmin = Role::findByName(RoleName::SUPER_ADMIN->value, $guardName);
        $superAdmin->syncPermissions(Permission::query()->pluck('name')->all());

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}

