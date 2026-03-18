<?php

use App\Support\Auth\PermissionName;
use App\Support\Auth\RoleName;

return [
    'roles' => array_map(
        static fn (RoleName $role) => $role->value,
        RoleName::cases(),
    ),

    'permissions' => array_map(
        static fn (PermissionName $permission) => $permission->value,
        PermissionName::cases(),
    ),

    'role_permissions' => [
        RoleName::VIEWER->value => [
            PermissionName::DASHBOARD_VIEW->value,
            PermissionName::USERS_VIEW->value,
            PermissionName::CONTENT_VIEW->value,
        ],

        RoleName::EDITOR->value => [
            PermissionName::DASHBOARD_VIEW->value,
            PermissionName::USERS_VIEW->value,
            PermissionName::CONTENT_VIEW->value,
            PermissionName::CONTENT_CREATE->value,
            PermissionName::CONTENT_UPDATE->value,
        ],

        RoleName::MANAGER->value => [
            PermissionName::DASHBOARD_VIEW->value,
            PermissionName::USERS_VIEW->value,
            PermissionName::USERS_CREATE->value,
            PermissionName::USERS_UPDATE->value,
            PermissionName::USERS_DELETE->value,
            PermissionName::CONTENT_VIEW->value,
            PermissionName::CONTENT_CREATE->value,
            PermissionName::CONTENT_UPDATE->value,
            PermissionName::CONTENT_DELETE->value,
        ],

        RoleName::ADMIN->value => [
            PermissionName::DASHBOARD_VIEW->value,
            PermissionName::USERS_VIEW->value,
            PermissionName::USERS_CREATE->value,
            PermissionName::USERS_UPDATE->value,
            PermissionName::USERS_DELETE->value,
            PermissionName::USERS_MANAGE->value,
            PermissionName::CONTENT_VIEW->value,
            PermissionName::CONTENT_CREATE->value,
            PermissionName::CONTENT_UPDATE->value,
            PermissionName::CONTENT_DELETE->value,
            PermissionName::CONTENT_MANAGE->value,
            PermissionName::ROLES_VIEW->value,
            PermissionName::ROLES_CREATE->value,
            PermissionName::ROLES_UPDATE->value,
            PermissionName::ROLES_DELETE->value,
            PermissionName::ROLES_MANAGE->value,
            PermissionName::PERMISSIONS_VIEW->value,
            PermissionName::PERMISSIONS_MANAGE->value,
        ],
    ],

    'seed_users' => [
        [
            'name' => 'Super Admin',
            'email' => 'superadmin@example.com',
            'role' => RoleName::SUPER_ADMIN->value,
        ],
        [
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'role' => RoleName::ADMIN->value,
        ],
        [
            'name' => 'Manager',
            'email' => 'manager@example.com',
            'role' => RoleName::MANAGER->value,
        ],
        [
            'name' => 'Editor',
            'email' => 'editor@example.com',
            'role' => RoleName::EDITOR->value,
        ],
        [
            'name' => 'Viewer',
            'email' => 'viewer@example.com',
            'role' => RoleName::VIEWER->value,
        ],
    ],

    'default_seed_password' => env('AUTH_SEED_DEFAULT_PASSWORD', 'password'),

    'seed_test_users_in_production' => env('AUTH_SEED_PRODUCTION_USERS', false),
];

