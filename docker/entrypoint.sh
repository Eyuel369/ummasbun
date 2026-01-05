#!/usr/bin/env sh
set -e

if [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    if [ -z "${APP_URL:-}" ]; then
        export APP_URL="${RENDER_EXTERNAL_URL}"
    fi
    if [ -z "${ASSET_URL:-}" ]; then
        export ASSET_URL="${RENDER_EXTERNAL_URL}"
    fi
fi

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    tries="${DB_WAIT_TRIES:-10}"
    delay="${DB_WAIT_DELAY:-3}"
    attempt=1
    while ! php artisan migrate --force --no-interaction; do
        if [ "$attempt" -ge "$tries" ]; then
            echo "Migrations failed after ${tries} attempts." >&2
            exit 1
        fi
        echo "Migrations failed, retrying in ${delay}s (${attempt}/${tries})..."
        attempt=$((attempt + 1))
        sleep "$delay"
    done
fi

if [ "${RUN_SEED:-true}" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

exec docker-php-entrypoint "$@"
