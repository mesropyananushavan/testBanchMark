# Role Matrix

## Action Semantics
- `view` - read/list entities.
- `create` - create new entities.
- `update` - edit existing entities.
- `delete` - remove entities.
- `manage` - full administrative control over resource and related settings.

## Matrix (General Rule Per Resource)

| Role        | view | create | update | delete | manage |
|-------------|------|--------|--------|--------|--------|
| viewer      | yes  | no     | no     | no     | no     |
| editor      | yes  | yes    | yes    | no     | no     |
| manager     | yes  | yes    | yes    | yes    | no     |
| admin       | yes  | yes    | yes    | yes    | yes    |
| super_admin | yes  | yes    | yes    | yes    | yes*   |

`*` super_admin has global bypass through `Gate::before`.

## Concrete Resource Baseline
- `dashboard`: `dashboard.view`
- `users`: `users.view/create/update/delete/manage`
- `content`: `content.view/create/update/delete/manage`
- `roles`: `roles.view/create/update/delete/manage`
- `permissions`: `permissions.view/manage`

## Constraints
- `viewer`: read-only usage.
- `editor`: can manage content edits but cannot delete/manage system entities.
- `manager`: operational management; no role/permission administration.
- `admin`: platform administration via explicit permissions.
- `super_admin`: emergency/full-control role, assigned to minimal trusted accounts only.

