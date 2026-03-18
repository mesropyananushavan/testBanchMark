# ENVIRONMENTS RUNBOOK

## 1. Scope
Документ стандартизирует запуск и эксплуатацию проекта в `local`, `dev`, `prod` для команды разработки, CI/CD и операционного сопровождения.

## 2. Assumptions
1. [ASSUMPTION] Приложение — Laravel (php-fpm + nginx + queue + scheduler + mysql + redis).
2. [ASSUMPTION] Деплой выполняется на single-host Docker Compose, не в Kubernetes.
3. [ASSUMPTION] Внешний секрет-менеджер (Vault/SSM) пока не интегрирован; секреты задаются через `.env.<env>` на хосте.
4. [ASSUMPTION] Очереди и кэш используют Redis.
5. [ASSUMPTION] Нагрузочное масштабирование выполняется горизонтально по сервисам `app/queue` при необходимости.

## 3. Common Architecture

### 3.1 Services
1. `nginx` — reverse proxy / static / fastcgi gateway.
2. `app` — php-fpm runtime.
3. `workspace` — one-off CLI контейнер для artisan/composer/tasks.
4. `queue` — воркер очереди (`php artisan queue:work`).
5. `scheduler` — планировщик (`php artisan schedule:work`).
6. `db` — MySQL 8.4.
7. `redis` — Redis 7.

### 3.2 Required host dependencies
1. Docker Engine с поддержкой Compose v2.
2. GNU Make.
3. Свободные порты:
- local: `8080`, `3307`, `6380`
- dev: `8081`, `3308`
- prod: `80`

### 3.3 Global env variables (must be set per environment)
1. `APP_ENV`
2. `APP_DEBUG`
3. `APP_URL`
4. `APP_KEY`
5. `DB_DATABASE`
6. `DB_USERNAME`
7. `DB_PASSWORD`
8. `DB_ROOT_PASSWORD`
9. `CACHE_STORE`
10. `QUEUE_CONNECTION`
11. `SESSION_DRIVER`
12. `LOG_CHANNEL`
13. `LOG_LEVEL`
14. `APP_IMAGE_TAG`

## 4. Environment Differences (short matrix)

| Topic | Local | Dev | Prod |
|---|---|---|---|
| Purpose | Быстрая разработка | Интеграционное тестирование команды | Боевой трафик |
| Source mount | Да (RW для PHP) | Да (RW для PHP) | Нет |
| Xdebug | Включен | Выключен | Запрещен |
| APP_DEBUG | `true` | `false` | `false` |
| DB/Redis host ports | Открыты | Частично открыты (DB) | Не открывать наружу |
| User hardening | Базовый | Базовый | `www-data`, `no-new-privileges` |
| Nginx FS | Обычный | Обычный | `read_only + tmpfs` |
| Deploy policy | Свободная | Контролируемая | Только проверенный pipeline |
| External integrations | Mock/sandbox | Staging-only | Production-only |

## 5. Local Environment

### 5.1 Purpose
Локальная разработка, быстрый feedback loop, отладка, запуск тестов и миграций.

### 5.2 How to run
1. Создать env:
```bash
cp .env.local.example .env.local
```
2. Заполнить минимум: `APP_KEY`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`.
3. Запуск:
```bash
make up-local
```
4. Полезные команды:
```bash
make logs-local
make bash-local
make migrate-local
make test-local
make artisan-local cmd="key:generate"
```

### 5.3 Required env values
1. `APP_ENV=local`
2. `APP_DEBUG=true`
3. `APP_URL=http://localhost:8080`
4. `APP_KEY` (не placeholder)
5. `DB_*` значения валидные
6. `LOG_LEVEL=debug`

### 5.4 Connected services
1. `nginx` (порт `8080`)
2. `app`
3. `workspace`
4. `queue`
5. `scheduler`
6. `db` (порт `3307`)
7. `redis` (порт `6380`)

### 5.5 Mandatory dependencies
1. Docker/Compose.
2. Доступность портов `8080/3307/6380`.
3. Наличие исходников приложения в каталоге проекта.

### 5.6 Database behavior
1. MySQL запускается в контейнере с volume `db_data`.
2. Данные персистентны между перезапусками.
3. Подключение с хоста: `127.0.0.1:3307`.

### 5.7 Migrations
1. Обычные миграции:
```bash
make migrate-local
```
2. Полный сброс (только local):
```bash
make fresh-local
```

### 5.8 Logging
1. Docker logging driver `json-file` с rotation (`10m x 5`).
2. PHP ошибки в `stderr` контейнера.
3. Просмотр:
```bash
make logs-local
```

### 5.9 Allowed / forbidden integrations
1. Разрешено: sandbox/mock интеграции, локальные SMTP/API эмуляторы.
2. Запрещено: доступ к production API/DB/очередям.

### 5.10 Security requirements
1. Секреты не коммитить.
2. Не использовать production credentials.
3. Хранить `.env.local` только локально.

### 5.11 Config specifics
1. Bind-mount исходников для hot reload.
2. Xdebug включен (`docker/local/php/xdebug.ini`).
3. Nginx читает код только в `ro`-режиме, PHP сервисы — `rw`.

### 5.12 Limitations and risks
1. Риск “works on my machine” из-за bind-mount.
2. Возможны различия с prod по файловым правам и производительности.
3. Нельзя использовать local как источник truth для performance.

## 6. Dev Environment

### 6.1 Purpose
Общая интеграционная среда команды: проверка сборки, миграций, фоновых задач, smoke/регрессии до prod.

### 6.2 How to run
1. Создать env:
```bash
cp .env.dev.example .env.dev
```
2. Заполнить `APP_KEY` и все `DB_*`.
3. Запуск:
```bash
make up-dev
```
4. Деплойный сценарий dev:
```bash
make deploy-dev
```

### 6.3 Required env values
1. `APP_ENV=development`
2. `APP_DEBUG=false`
3. `APP_URL=https://dev.example.com`
4. `APP_KEY` (валидный)
5. `DB_*` значения окружения dev
6. `LOG_LEVEL=info`

### 6.4 Connected services
1. `nginx` (порт `8081`)
2. `app`
3. `workspace`
4. `queue`
5. `scheduler`
6. `db` (порт `3308`)
7. `redis` (без внешнего порта)

### 6.5 Mandatory dependencies
1. Docker/Compose на dev-хосте/runner.
2. Сетевой доступ команды к `8081` (или через reverse proxy).
3. CI pipeline для валидации compose и сборки image.

### 6.6 Database behavior
1. Отдельная dev-база, не shared с local/prod.
2. Персистентность через volume.
3. Схема должна обновляться только через миграции.

### 6.7 Migrations
1. На деплое выполнять:
```bash
php artisan migrate --force
```
2. Выполнять до `queue:restart`, чтобы воркеры видели новую схему.
3. Запрещено вручную менять схему SQL-скриптами мимо миграций.

### 6.8 Logging
1. `LOG_LEVEL=info`, контейнерные логи с rotation.
2. Проверка логов обязательна после `deploy-dev`:
```bash
make logs-dev
```

### 6.9 Allowed / forbidden integrations
1. Разрешено: staging/integration endpoints.
2. Запрещено: production API keys, production webhooks, production queues.

### 6.10 Security requirements
1. `APP_DEBUG=false`.
2. Не использовать тестовые пароли из `.env.dev.example`.
3. Ограничить доступ к dev-портам по сети.

### 6.11 Config specifics
1. Xdebug выключен.
2. Source bind-mount сохранен для ускорения цикла изменений.
3. Поведение ближе к prod, но всё ещё с dev-упрощениями.

### 6.12 Limitations and risks
1. Bind-mount и ручной запуск могут давать drift от prod.
2. Нет полноценной централизованной observability из коробки.
3. При параллельных деплоях возможны race conditions без external lock.

## 7. Prod Environment

### 7.1 Purpose
Стабильная эксплуатация боевого трафика с предсказуемыми деплоями и минимальным операционным риском.

### 7.2 How to run
1. Создать env:
```bash
cp .env.prod.example .env.prod
```
2. Обязательно заменить placeholders (`APP_KEY`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`).
3. Проверка env встроена в `make validate-prod-env`.
4. Запуск:
```bash
make up-prod
```
5. Деплой:
```bash
make deploy-prod
```

### 7.3 Required env values
1. `APP_ENV=production`
2. `APP_DEBUG=false`
3. `APP_URL` на боевой домен
4. `APP_KEY` валидный и секретный
5. `DB_*` только production
6. `LOG_LEVEL=warning` (или выше по политике)

### 7.4 Connected services
1. `nginx` (порт `80`)
2. `app`
3. `queue`
4. `scheduler`
5. `db`
6. `redis`
7. `workspace` только через `tools` profile для one-off задач

### 7.5 Mandatory dependencies
1. Docker/Compose на прод-хосте.
2. Стабильное хранилище для volumes (`db_data`, `redis_data`).
3. Бэкапы БД и процедура restore (обязательны, см. gap в разделе 9).

### 7.6 Database behavior
1. MySQL без host-port публикации (доступ только внутри backend network).
2. Персистентный volume.
3. Charset/collation: `utf8mb4`.
4. Любые изменения схемы только миграциями через controlled deploy.

### 7.7 Migrations
1. Выполнять строго во время деплоя (`migrate --force`).
2. Миграции должны быть backward-compatible для zero/minimal downtime.
3. После миграций перезапуск воркеров (`queue:restart`) обязателен.

### 7.8 Logging
1. Контейнерные логи с ротацией `json-file`.
2. PHP логирует в `stderr`, сбор — через `docker compose logs`/агент.
3. Минимум: ошибки приложений и инфраструктурные события должны попадать в централизованный лог-стек.

### 7.9 Allowed / forbidden integrations
1. Разрешено: только production endpoints через TLS.
2. Запрещено: sandbox/test endpoints, debug hooks, xdebug.
3. Запрещено подключение к prod DB из local/dev окружений.

### 7.10 Security requirements
1. `APP_DEBUG=false`.
2. `app/queue/scheduler/workspace` запускаются от `www-data`.
3. `no-new-privileges:true` в prod сервисах.
4. `nginx` в `read_only` + `tmpfs`.
5. Секреты вне git и с ротацией по регламенту.

### 7.11 Config specifics
1. Нет bind-mount кода из host.
2. Жесткие php/nginx настройки для production профиля.
3. Healthchecks включены для ключевых сервисов (`app/queue/scheduler/nginx/db/redis`).

### 7.12 Limitations and risks
1. Single-host Compose — SPOF без failover.
2. Нет встроенного autoscaling.
3. Без внешних backup/monitoring систем RTO/RPO не гарантируются.

## 8. What is allowed in local but forbidden in prod
1. `APP_DEBUG=true`.
2. Xdebug.
3. `migrate:fresh --seed`.
4. Bind-mount исходников в runtime.
5. Использование mock/sandbox интеграций.

## 9. Current gaps / misconfigurations and target state

### 9.1 Secret management is incomplete
1. Проблема: `.env.prod` хранится на хосте, без централизованного менеджера секретов.
2. Как должно быть: хранить секреты в Vault/AWS SSM/GCP Secret Manager; в CI/CD передавать как runtime secrets.

### 9.2 Deploy builds image on target host
1. Проблема: `make up-prod/deploy-prod` используют `--build` на прод-хосте.
2. Как должно быть: сборка immutable image в CI, push в registry, deploy по digest/tag без пересборки в prod.

### 9.3 CI is minimal
1. Проблема: CI проверяет только compose config и build, без тестов/линтеров/security scans.
2. Как должно быть: добавить этапы `unit/integration tests`, `static analysis`, `container scan (Trivy/Grype)`, `SAST`.

### 9.4 Missing backup/restore runbook
1. Проблема: нет зафиксированного процесса backup/restore MySQL/Redis.
2. Как должно быть: регламентные backup jobs + периодические restore drills.

### 9.5 Observability is insufficient
1. Проблема: нет стандартизированной интеграции с централизованным logging/metrics/alerts.
2. Как должно быть: подключить минимум Prometheus + Alertmanager + log stack (ELK/Loki).

### 9.6 TLS termination is not documented
1. Проблема: в текущем compose прод открыт `80`, но не описан TLS termination.
2. Как должно быть: TLS на ingress/LB (или в nginx) с автоматической ротацией сертификатов.

### 9.7 Resource policy not fixed
1. Проблема: нет hard лимитов CPU/RAM на сервисы.
2. Как должно быть: установить профили ресурсов минимум для `app`, `queue`, `db`, `redis`.

## 10. Standard operating checklist

### 10.1 Before deploy (dev/prod)
1. Актуальный `.env.<env>` без placeholders.
2. `docker compose ... config` проходит без ошибок.
3. CI pipeline green.
4. Есть rollback plan.

### 10.2 Deploy sequence
1. `up -d` новой версии.
2. `php artisan migrate --force`.
3. `php artisan optimize`.
4. `php artisan queue:restart`.
5. Smoke checks: `/health`, ключевые бизнес-эндпоинты, очередь.

### 10.3 Rollback trigger criteria
1. Рост 5xx.
2. Ошибки миграций.
3. Нездоровые healthchecks дольше порога.
4. Неконсистентность очередей/БД.

