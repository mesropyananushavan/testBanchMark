# Permissions Rules

## Mandatory Rules
1. Do not hardcode role checks in random places (`hasRole('...')` scattered in controllers/views).
2. Use centralized authorization:
- middleware for route-level checks
- policies for model/resource checks
- gates for cross-cutting rules
3. Add new roles and permissions systematically:
- update centralized permission map
- update role-permission assignment seeder
- update matrix/docs if behavior changed
4. Do not duplicate access logic across controller, service, and view.
5. UI checks (`@can`, `@role`) are supplementary; backend authorization is mandatory.

## Development Workflow
1. Define permission names in central map.
2. Assign permissions to roles in auth seeder.
3. Enforce access in policy and/or middleware.
4. Add tests for allowed and forbidden scenarios.
5. Update `AUTH_ARCHITECTURE.md` and `ROLE_MATRIX.md` if model changes.

## Anti-Patterns (Forbidden)
- Inline string checks in multiple files (`if ($user->can('x') || $user->hasRole('y'))` repeated).
- Adding routes without middleware/policy for privileged actions.
- Giving wildcard admin access to non-admin roles without explicit reason.
- Creating role-specific business forks instead of permission-based checks.

