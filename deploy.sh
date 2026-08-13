#!/usr/bin/env bash

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/crumbls-wins}"
PHP_BIN="${PHP_BIN:-/usr/bin/php}"
RELEASES_TO_KEEP="${RELEASES_TO_KEEP:-2}"
RELEASE_NAME="$(date +%Y%m%d%H%M%S)"
RELEASE="$APP_DIR/releases/$RELEASE_NAME"
SHA="${1:-}"
PREVIOUS_RELEASE="$(readlink -f "$APP_DIR/current" 2>/dev/null || true)"

if [ -z "$SHA" ]; then
    echo "Usage: deploy.sh <commit-sha>"
    exit 1
fi

if [ ! -x "$PHP_BIN" ]; then
    echo "PHP binary not found: $PHP_BIN"
    exit 1
fi

if [ ! -f "$APP_DIR/shared/.env" ]; then
    echo "Missing shared environment file: $APP_DIR/shared/.env"
    exit 1
fi

umask 027

mkdir -p \
    "$APP_DIR/releases" \
    "$APP_DIR/incoming" \
    "$APP_DIR/shared/database" \
    "$APP_DIR/shared/storage/app" \
    "$APP_DIR/shared/storage/app/public" \
    "$APP_DIR/shared/storage/framework/cache/data" \
    "$APP_DIR/shared/storage/framework/sessions" \
    "$APP_DIR/shared/storage/framework/views" \
    "$APP_DIR/shared/storage/logs" \
    "$APP_DIR/shared/bootstrap/cache"

chmod 2770 "$APP_DIR/shared/database"
touch "$APP_DIR/shared/database/database.sqlite"
chmod 0660 "$APP_DIR/shared/database/database.sqlite"

SOURCE_ARCHIVE="$APP_DIR/incoming/$SHA/source.tar.gz"

if [ ! -f "$SOURCE_ARCHIVE" ]; then
    echo "Missing source archive: $SOURCE_ARCHIVE"
    exit 1
fi

echo ">>> Deploying $SHA as $RELEASE_NAME"

mkdir -p "$RELEASE"
tar -xzf "$SOURCE_ARCHIVE" -C "$RELEASE"
cd "$RELEASE"

ln -nfs "$APP_DIR/shared/.env" "$RELEASE/.env"
rm -rf "$RELEASE/storage" "$RELEASE/bootstrap/cache"
ln -nfs "$APP_DIR/shared/storage" "$RELEASE/storage"
ln -nfs "$APP_DIR/shared/bootstrap/cache" "$RELEASE/bootstrap/cache"

composer install --no-dev --optimize-autoloader --no-interaction --no-progress

PREBUILT="$APP_DIR/incoming/$SHA/public/build"

if [ ! -d "$PREBUILT" ]; then
    echo "Missing pre-built frontend assets: $PREBUILT"
    exit 1
fi

mkdir -p "$RELEASE/public"
rm -rf "$RELEASE/public/build"
cp -a "$PREBUILT" "$RELEASE/public/build"
rm -rf "$APP_DIR/incoming/$SHA"

"$PHP_BIN" artisan storage:link --force --no-interaction

if [ ! -f "$APP_DIR/shared/storage/oauth-private.key" ] || [ ! -f "$APP_DIR/shared/storage/oauth-public.key" ]; then
    "$PHP_BIN" artisan passport:keys --force --no-interaction
fi

"$PHP_BIN" artisan migrate --force --no-interaction
"$PHP_BIN" artisan optimize

find "$RELEASE" "$APP_DIR/shared" -user "$(id -u)" -exec chgrp apache {} +
find "$RELEASE" -user "$(id -u)" -exec chmod g+rX {} +
find "$APP_DIR/shared/storage" "$APP_DIR/shared/bootstrap/cache" "$APP_DIR/shared/database" -user "$(id -u)" -exec chmod g+rwX {} +

cat > "$APP_DIR/shared/storage/app/deployment.json" <<EOF
{
    "release": "$RELEASE_NAME",
    "sha": "$SHA",
    "deployed_at": "$(date -u +%Y-%m-%dT%H:%M:%SZ)"
}
EOF

ln -nfs "$RELEASE" "$APP_DIR/current"

sudo -n systemctl reload php-fpm || true

sleep 2

if [ -n "${HEALTH_CHECK_URL:-}" ]; then
    HEALTH_CHECK_COMMAND=(curl -fsS --max-time 10 "$HEALTH_CHECK_URL")
else
    HEALTH_CHECK_COMMAND=("$PHP_BIN" "$APP_DIR/current/artisan" about)
fi

if ! "${HEALTH_CHECK_COMMAND[@]}" > /dev/null; then
    echo "Health check failed."

    if [ -n "$PREVIOUS_RELEASE" ] && [ -d "$PREVIOUS_RELEASE" ]; then
        ln -nfs "$PREVIOUS_RELEASE" "$APP_DIR/current"
        sudo -n systemctl reload php-fpm || true
        echo "Rolled back to $PREVIOUS_RELEASE"
    fi

    exit 1
fi

cd "$APP_DIR/releases"
ls -t | tail -n +"$((RELEASES_TO_KEEP + 1))" | xargs -r -I{} rm -rf "{}"

echo ">>> Deployed $RELEASE_NAME"
