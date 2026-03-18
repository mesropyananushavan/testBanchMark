PROJECT ?= laravel-template

COMPOSE_BASE = docker compose -p $(PROJECT) -f compose.yml
LOCAL = $(COMPOSE_BASE) --env-file .env.local -f compose.local.yml
DEV = $(COMPOSE_BASE) --env-file .env.dev -f compose.dev.yml
PROD = $(COMPOSE_BASE) --env-file .env.prod -f compose.prod.yml

.PHONY: help env-local env-dev env-prod validate-prod-env up-local down-local logs-local bash-local artisan-local composer-local migrate-local fresh-local test-local config-local up-dev down-dev logs-dev bash-dev deploy-dev config-dev up-prod down-prod logs-prod bash-prod deploy-prod config-prod

help:
	@echo "Available targets:"
	@echo "  env-local env-dev env-prod"
	@echo "  validate-prod-env"
	@echo "  up-local down-local logs-local bash-local artisan-local composer-local migrate-local fresh-local test-local config-local"
	@echo "  up-dev down-dev logs-dev bash-dev deploy-dev config-dev"
	@echo "  up-prod down-prod logs-prod bash-prod deploy-prod config-prod"

env-local:
	@test -f .env.local || cp .env.local.example .env.local
	@grep -q '^APP_KEY=base64:replace-with-generated-key$$' .env.local && \
		sed -i "s|^APP_KEY=.*|APP_KEY=base64:$$(openssl rand -base64 32 | tr -d '\n')|" .env.local || true

env-dev:
	@test -f .env.dev || cp .env.dev.example .env.dev
	@grep -q '^APP_KEY=base64:replace-with-generated-key$$' .env.dev && \
		sed -i "s|^APP_KEY=.*|APP_KEY=base64:$$(openssl rand -base64 32 | tr -d '\n')|" .env.dev || true

env-prod:
	@test -f .env.prod || cp .env.prod.example .env.prod

validate-prod-env: env-prod
	@grep -q 'replace-with-generated-key' .env.prod && { echo "Set APP_KEY in .env.prod before production deploy"; exit 1; } || true
	@grep -q 'prod-change-me' .env.prod && { echo "Set DB_PASSWORD in .env.prod before production deploy"; exit 1; } || true
	@grep -q 'prod-root-change-me' .env.prod && { echo "Set DB_ROOT_PASSWORD in .env.prod before production deploy"; exit 1; } || true

up-local: env-local
	$(LOCAL) config > /dev/null
	$(LOCAL) up -d --build --remove-orphans

down-local:
	$(LOCAL) down

logs-local: env-local
	$(LOCAL) logs -f

bash-local: env-local
	$(LOCAL) exec workspace sh

artisan-local: env-local
	$(LOCAL) exec workspace php artisan $(cmd)

composer-local: env-local
	$(LOCAL) exec workspace composer $(cmd)

migrate-local: env-local
	$(LOCAL) exec workspace php artisan migrate

fresh-local: env-local
	$(LOCAL) exec workspace php artisan migrate:fresh --seed

test-local: env-local
	$(LOCAL) exec workspace php artisan test

config-local: env-local
	$(LOCAL) config

up-dev: env-dev
	$(DEV) config > /dev/null
	$(DEV) up -d --build --remove-orphans

down-dev:
	$(DEV) down

logs-dev: env-dev
	$(DEV) logs -f

bash-dev: env-dev
	$(DEV) exec workspace sh

deploy-dev: env-dev
	$(DEV) config > /dev/null
	$(DEV) up -d --build --remove-orphans
	$(DEV) exec workspace php artisan migrate --force
	$(DEV) exec workspace php artisan optimize
	$(DEV) exec queue php artisan queue:restart

config-dev: env-dev
	$(DEV) config

up-prod: validate-prod-env
	$(PROD) config > /dev/null
	$(PROD) up -d --build --remove-orphans

down-prod:
	$(PROD) down

logs-prod: validate-prod-env
	$(PROD) logs -f

bash-prod: validate-prod-env
	$(PROD) --profile tools run --rm workspace sh

deploy-prod: validate-prod-env
	$(PROD) config > /dev/null
	$(PROD) up -d --build --remove-orphans
	$(PROD) --profile tools run --rm workspace php artisan migrate --force
	$(PROD) --profile tools run --rm workspace php artisan optimize
	$(PROD) exec queue php artisan queue:restart

config-prod: validate-prod-env
	$(PROD) config
