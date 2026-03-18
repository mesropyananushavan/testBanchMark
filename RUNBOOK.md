# Runbook

## Prerequisites
- Docker and Docker Compose plugin installed.
- Local ports: `8080`, `3307`, `6380`, `5173`.
- Dev ports: `8081`, `3308`, `5173`.

## 1) First-Time Setup

```bash
make env-local
make up-local
```

Optional first migration run:

```bash
make migrate-local
```

## 2) Daily Local Workflow
Start:

```bash
make up-local
```

Open shell in workspace:

```bash
make bash-local
```

Run artisan command:

```bash
make artisan-local cmd="route:list"
```

Run tests:

```bash
make test-local
```

Stop:

```bash
make down-local
```

## 3) Dev Environment Workflow

```bash
make env-dev
make up-dev
make logs-dev
```

Deploy-like dev flow:

```bash
make deploy-dev
```

## 4) Production Workflow
Prepare env and validate secrets:

```bash
make env-prod
make validate-prod-env
```

Start:

```bash
make up-prod
```

Deploy:

```bash
make deploy-prod
```

## 5) Health Checks
Container status:

```bash
docker compose --env-file .env.local -p laravel-template -f compose.yml -f compose.local.yml ps
```

HTTP check:

```bash
curl -I http://localhost:8080
```

Laravel check:

```bash
docker compose --env-file .env.local -p laravel-template -f compose.yml -f compose.local.yml exec -T app php artisan --version
```

## 6) Debugging
Tail all local logs:

```bash
make logs-local
```

Tail only app logs:

```bash
docker compose --env-file .env.local -p laravel-template -f compose.yml -f compose.local.yml logs -f app
```

Check mounts in app container:

```bash
docker inspect laravel-template-app-1 --format '{{json .Mounts}}'
```

Rebuild and recreate local stack:

```bash
docker compose --env-file .env.local -p laravel-template -f compose.yml -f compose.local.yml down -v
docker compose --env-file .env.local -p laravel-template -f compose.yml -f compose.local.yml up --build -d
```

## 7) Common Incidents
- `500 Unsupported cipher or incorrect key length`:
- verify `APP_KEY` in `.env.local` and `backend/.env`.
- restart stack after key fix.

- `502 Bad Gateway`:
- verify `app` container is healthy.
- check nginx/app logs.
- restart `nginx` if needed after recreate.

- Laravel cannot write cache/views/logs:
- check permissions under `backend/storage` and `backend/bootstrap/cache`.
- recreate app service.

## 8) Structure Safety Checks Before Any Refactor

```bash
find . -maxdepth 2 -mindepth 1 | sort
rg -n "storage/|bootstrap/|files/" -g'!backend/**' .
docker compose --env-file .env.local -p laravel-template -f compose.yml -f compose.local.yml config >/dev/null
```
