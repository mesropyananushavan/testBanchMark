# Project Context

## What This Project Is
This repository is a Laravel application with Docker-based infrastructure in a single repo.

- Application code lives in `backend/` (Laravel 13).
- Infrastructure code lives in `docker/` and `compose*.yml`.
- Root-level files (`Makefile`, env files, compose files) orchestrate environments.

## Source Layout
- `backend/` - full Laravel app (`artisan`, `app`, `routes`, `config`, `database`, `public`, `storage`, `tests`).
- `docker/` - Dockerfiles, entrypoints, nginx/php configs for `local/dev/prod`.
- `compose.yml` - base services and shared defaults.
- `compose.local.yml` - local overrides (ports, mounts, xdebug, vite).
- `compose.dev.yml` - dev overrides (ports, mounts, vite).
- `compose.prod.yml` - prod hardening overrides.
- `Makefile` - standard command entrypoint for team workflows.

## Runtime Services
- `app` - PHP-FPM Laravel runtime.
- `workspace` - utility shell container for artisan/composer operations.
- `queue` - `php artisan queue:work`.
- `scheduler` - `php artisan schedule:work`.
- `nginx` - public HTTP entrypoint.
- `db` - MySQL 8.4.
- `redis` - Redis 7.
- `vite` - frontend dev server (local/dev only).

## How To Run
Local:

```bash
make env-local
make up-local
```

Dev:

```bash
make env-dev
make up-dev
```

Prod (after filling `.env.prod`):

```bash
make up-prod
```

## Key Structural Rule
All Laravel code and Laravel runtime artifacts must stay inside `backend/`.
Do not create Laravel directories (`app`, `routes`, `storage`, `bootstrap`, `public`) in repository root.

