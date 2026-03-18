#!/bin/sh
set -eu

log() {
  printf '%s [entrypoint-cli] %s\n' "$(date -u '+%Y-%m-%dT%H:%M:%SZ')" "$*"
}

cd /var/www/html || {
  log "ERROR: cannot change directory to /var/www/html"
  exit 1
}

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

if [ "$#" -eq 0 ]; then
  log "ERROR: no command provided"
  exit 1
fi

log "Executing: $*"
exec "$@"
