#!/usr/bin/env bash
set -euo pipefail

mkdir -p \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    sqlite_path="${DB_DATABASE:-database/database.sqlite}"

    if [[ "$sqlite_path" != /* ]]; then
        sqlite_path="/var/www/html/${sqlite_path}"
    fi

    mkdir -p "$(dirname "$sqlite_path")"
    touch "$sqlite_path"
    chown www-data:www-data "$sqlite_path" "$(dirname "$sqlite_path")"
fi

php artisan config:clear --ansi
php artisan route:clear --ansi
php artisan view:clear --ansi
php artisan event:clear --ansi
php artisan storage:link --force --ansi || true

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    php artisan migrate --force --ansi
fi

exec "$@"
