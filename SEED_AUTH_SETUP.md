# Seed Auth Setup

## Seeded Roles
- `super_admin`
- `admin`
- `manager`
- `editor`
- `viewer`

## Seeded Permissions (Baseline)
- `dashboard.view`
- `users.view`
- `users.create`
- `users.update`
- `users.delete`
- `users.manage`
- `content.view`
- `content.create`
- `content.update`
- `content.delete`
- `content.manage`
- `roles.view`
- `roles.create`
- `roles.update`
- `roles.delete`
- `roles.manage`
- `permissions.view`
- `permissions.manage`

## Role to Permission Mapping
- `viewer`:
- `dashboard.view`
- `users.view`
- `content.view`

- `editor`:
- viewer permissions
- `content.create`
- `content.update`

- `manager`:
- editor permissions
- `content.delete`
- `users.create`
- `users.update`
- `users.delete`

- `admin`:
- manager permissions
- `users.manage`
- `content.manage`
- `roles.view/create/update/delete/manage`
- `permissions.view/manage`

- `super_admin`:
- all permissions
- plus global bypass via gate.

## Seeded Test Users
- `superadmin@example.com` -> `super_admin`
- `admin@example.com` -> `admin`
- `manager@example.com` -> `manager`
- `editor@example.com` -> `editor`
- `viewer@example.com` -> `viewer`

Default password for seeded users (local/dev only): `password`.

## Initialization After Deploy
1. Run migrations:

```bash
php artisan migrate --force
```

2. Run seeders:

```bash
php artisan db:seed --force
```

3. Rebuild permission cache if needed:

```bash
php artisan permission:cache-reset
```

## Safety Notes
- In production, replace seeded default passwords immediately or disable test-user seeding by environment gate.
- Keep role assignment idempotent in seeders (`syncPermissions`, `firstOrCreate`).

