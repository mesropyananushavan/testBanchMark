# Architecture

## High-Level Structure

```text
.
├── backend/                # Laravel application (single source for app code)
├── docker/                 # Infra templates and runtime configs
│   ├── base/
│   ├── local/
│   ├── dev/
│   └── prod/
├── compose.yml             # Base compose topology
├── compose.local.yml       # Local override
├── compose.dev.yml         # Dev override
├── compose.prod.yml        # Prod override
├── Makefile                # Team command interface
├── PROJECT_CONTEXT.md
├── DEV_RULES.md
└── RUNBOOK.md
```

## Service Topology
- `nginx` receives HTTP traffic and forwards PHP requests to `app:9000`.
- `app` runs Laravel web runtime (PHP-FPM).
- `queue` runs background jobs through Laravel queue worker.
- `scheduler` runs scheduled Laravel tasks.
- `workspace` is an interactive utility container for manual operations.
- `app`, `queue`, `scheduler`, `workspace` share the same image/build pipeline.
- `app/queue/scheduler` depend on healthy `db` and `redis`.
- `db` stores relational data (MySQL persistent volume `db_data`).
- `redis` stores cache/session/queue data (persistent volume `redis_data`).
- `vite` serves frontend assets in `local/dev` (port `5173`).

## Compose Layering
- `compose.yml` defines baseline services and shared environment.
- `compose.local.yml` applies local developer behavior.
- `compose.dev.yml` applies shared dev server behavior.
- `compose.prod.yml` applies production hardening and restricted profiles.

Effective command pattern:

```bash
docker compose -f compose.yml -f compose.<env>.yml --env-file .env.<env> ...
```

## Ownership Boundaries
- `backend/**` - application domain, business logic, controllers, models, migrations, tests.
- `docker/**` - runtime images, startup scripts, nginx/php tuning.
- `compose*.yml` - environment orchestration contracts.
- Root env files (`.env.local`, `.env.dev`, `.env.prod`) - compose/runtime configuration only.

## Placement Rules
- New Laravel code goes under `backend/` only.
- New infra code goes under `docker/` only.
- New environment toggles go to env files and compose, not to ad-hoc scripts in root.
- Root should not contain duplicate Laravel runtime dirs (`storage/`, `bootstrap/`, etc.).

