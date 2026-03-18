# Dev Rules

## 1) Placement Discipline
- Write Laravel code only in `backend/`.
- Do not place Laravel files in repo root.
- Infrastructure changes belong to `docker/`, `compose*.yml`, or `Makefile`.

## 2) When Infra Can Be Changed
- Change infra only if feature/runtime requirements demand it.
- Any infra change must include reason for change.
- Any infra change must include validation command(s).
- Any infra change must include doc update in `PROJECT_CONTEXT.md`, `ARCHITECTURE.md`, or `RUNBOOK.md` when behavior changed.

## 3) Feature Development Rules
- Controllers: `backend/app/Http/Controllers`.
- Domain models: `backend/app/Models`.
- Routes: `backend/routes`.
- Migrations/seeders/factories: `backend/database`.
- Views/assets: `backend/resources`.
- Tests: `backend/tests`.

## 4) Naming Conventions
- Classes: PascalCase (`OrderService`, `CreateUserAction`).
- Methods/variables: camelCase (`createOrder`, `$retryCount`).
- Config/env keys: UPPER_SNAKE_CASE (`APP_ENV`, `DB_HOST`).
- Migration names: timestamped snake_case (`2026_03_18_000001_create_orders_table.php`).
- Route names: dot notation (`orders.index`, `orders.store`).

## 5) Structure Change Rules
- Do not create duplicate top-level app directories.
- Do not create ad-hoc utility folders in root.
- Before moving/removing directories, run `rg` to find references.
- Before moving/removing directories, run `docker compose config` for affected environment.
- Before moving/removing directories, verify app health (`curl -I`, `artisan`, `ps`).

## 6) No-Noise Policy
- No duplicated files in multiple locations.
- No temporary JSON/log artifacts in root.
- Keep root focused on orchestration, docs, and project metadata.

## 7) Decision Rule for Uncertainty
- If unsure where a change belongs, update this file first with a clear rule, then implement.
