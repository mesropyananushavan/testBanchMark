<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Support\Auth\RoleName;
use Database\Seeders\AuthUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_and_permission_seeders_create_expected_baseline(): void
    {
        $this->seed(RolePermissionSeeder::class);
        $this->seed(AuthUserSeeder::class);

        $this->assertGreaterThan(0, Permission::query()->count());
        $this->assertCount(5, Role::query()->get());

        $this->assertTrue(User::query()->where('email', 'superadmin@example.com')->exists());
        $this->assertTrue(User::query()->where('email', 'admin@example.com')->exists());
        $this->assertTrue(User::query()->where('email', 'manager@example.com')->exists());
        $this->assertTrue(User::query()->where('email', 'editor@example.com')->exists());
        $this->assertTrue(User::query()->where('email', 'viewer@example.com')->exists());
    }

    public function test_policy_and_gate_checks_use_centralized_permissions(): void
    {
        $this->seed(RolePermissionSeeder::class);

        $viewer = User::factory()->create(['email' => 'viewer-policy@example.com']);
        $viewer->assignRole(RoleName::VIEWER->value);

        $manager = User::factory()->create(['email' => 'manager-policy@example.com']);
        $manager->assignRole(RoleName::MANAGER->value);

        $admin = User::factory()->create(['email' => 'admin-policy@example.com']);
        $admin->assignRole(RoleName::ADMIN->value);

        $target = User::factory()->create(['email' => 'target-policy@example.com']);

        $this->assertFalse($viewer->can('update', $target));
        $this->assertTrue($viewer->can('view', $viewer));

        $this->assertTrue($manager->can('delete', $target));
        $this->assertFalse(Gate::forUser($manager)->allows('manage-users'));
        $this->assertTrue(Gate::forUser($admin)->allows('manage-users'));

        $superAdmin = User::factory()->create(['email' => 'super-admin-policy@example.com']);
        $superAdmin->assignRole(RoleName::SUPER_ADMIN->value);

        $this->assertTrue(Gate::forUser($superAdmin)->allows('any-non-registered-ability'));
    }
}
