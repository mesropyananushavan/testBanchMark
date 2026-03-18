#!/bin/sh
set -eu

log() {
  printf '%s [entrypoint-fpm] %s\n' "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "$*"
}

fatal() {
  log "ERROR: $*"
  exit 1
}

cd /var/www/html || fatal "Cannot change directory to /var/www/html"

mkdir -p storage/framework/cache \
         storage/framework/sessions \
         storage/framework/views \
         storage/logs \
         bootstrap/cache

if [ "${APP_ENV:-production}" = "local" ] || [ "${APP_ENV:-production}" = "dev" ] || [ "${APP_ENV:-production}" = "development" ]; then
  # Local/dev often use bind mounts from host; allow container process user to write cache/log files.
  if ! chmod -R ugo+rwX storage bootstrap/cache; then
    log "WARN: unable to update storage/bootstrap permissions for local/dev"
  fi
else
  if ! chmod -R ug+rwX storage bootstrap/cache; then
    log "WARN: unable to update storage/bootstrap permissions"
  fi
fi

if [ "${APP_ENV:-production}" = "production" ] && [ ! -f .env ] && [ -z "${APP_KEY:-}" ]; then
  fatal "APP_KEY is missing. Set APP_KEY env var or provide .env file."
fi

if [ -f artisan ] && [ "${SKIP_PACKAGE_DISCOVER:-0}" != "1" ]; then
  log "Running Laravel package discovery"
  php artisan package:discover --ansi || fatal "php artisan package:discover failed"
fi

log "Starting php-fpm"
exec php-fpm
