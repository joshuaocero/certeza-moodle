#!/bin/bash
set -e

CONFIG_FILE=/var/www/html/config.php
CONFIG_BACKUP=/var/moodleconfig/config.php

# Restore config if container was recreated but volumes persist
if [ -f "$CONFIG_BACKUP" ] && [ ! -f "$CONFIG_FILE" ]; then
    echo "[moodle] Restoring config from volume..."
    cp "$CONFIG_BACKUP" "$CONFIG_FILE"
fi

if [ ! -f "$CONFIG_FILE" ]; then
    echo "[moodle] First-time install — this takes several minutes..."

    php /var/www/html/admin/cli/install.php \
        --lang=en \
        --wwwroot="${MOODLE_WWWROOT:-http://localhost:8080}" \
        --dataroot=/var/moodledata \
        --dbtype=pgsql \
        --dbhost="${DB_HOST:-db}" \
        --dbname="${DB_NAME:-moodle}" \
        --dbuser="${DB_USER:-moodle}" \
        --dbpass="${DB_PASS:-moodlepass}" \
        --adminuser="${MOODLE_ADMIN_USER:-admin}" \
        --adminpass="${MOODLE_ADMIN_PASS:-Admin1234!}" \
        --adminemail="${MOODLE_ADMIN_EMAIL:-admin@example.com}" \
        --fullname="${MOODLE_SITE_NAME:-My Moodle}" \
        --shortname="moodle" \
        --agree-license \
        --non-interactive

    cp "$CONFIG_FILE" "$CONFIG_BACKUP"
    echo "[moodle] Installation complete."
fi

chown www-data:www-data "$CONFIG_FILE"
chmod 644 "$CONFIG_FILE"

exec apache2-foreground
