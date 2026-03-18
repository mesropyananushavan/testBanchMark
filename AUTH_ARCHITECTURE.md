# Auth Architecture

## Goal
Build scalable authorization with explicit roles and granular permissions for Laravel application code in `backend/`.

## Core Entities
- `User` (`App\Models\User`) - authenticated principal.
- `Role` (`Spatie\Permission\Models\Role`) - coarse-grained grouping of access rights.
- `Permission` (`Spatie\Permission\Models\Permission`) - atomic access capability.

## Implementation Choice
- Use `spatie/laravel-permission` as RBAC foundation.
- Keep permission names centralized to avoid hardcoded strings spread across code.

## Access Control Layers

### 1) Route / HTTP layer
- Route middleware aliases:
- `role`
- `permission`
- `role_or_permission`
- Purpose: block requests early for route-level access rules.

### 2) Domain / model layer
- Policies (`app/Policies/*Policy.php`) enforce resource-level checks.
- Example: `UserPolicy` controls who can view/create/update/delete users.

### 3) Global authorization layer
- Gates for cross-cutting checks (`Gate::define(...)`).
- `Gate::before(...)` for super-admin bypass (single controlled place).

### 4) View layer
- Blade directives:
- `@can(...)`, `@cannot(...)`
- `@role(...)`, `@hasanyrole(...)`, `@hasallroles(...)`
- Purpose: hide unauthorized UI actions while backend checks remain mandatory.

## Permission Naming Convention
- Pattern: `<resource>.<action>`.
- Examples:
- `users.view`
- `users.create`
- `users.update`
- `users.delete`
- `users.manage`

## Expansion Strategy
- Add new resource by adding permissions in centralized map.
- Assign new permissions to roles in one place (seeder/config map).
- Add/update policy methods for resource-specific constraints.
- Use middleware only for route coarse checks, policy for resource ownership/business rules.

## Non-Goals
- No direct inline `if ($user->role === ...)` checks in controllers/views.
- No duplicated permission rules in multiple files.

