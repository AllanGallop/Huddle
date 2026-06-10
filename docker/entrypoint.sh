#!/bin/sh
set -e

cd /var/www/html

if [ ! -f vendor/autoload.php ]; then
    echo "Installing Composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

mkdir -p storage/app/private/livewire-tmp storage/app/public storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache database

if [ "$HUDDLE_FRESH_INSTALL" = "1" ]; then
    echo "Resetting install state for first-time setup test..."
    rm -f .env
    rm -f database/database.sqlite
    if [ -f public/.htaccess.setup ]; then
        cp public/.htaccess.setup public/.htaccess
    fi
fi

chown -R www-data:www-data storage bootstrap/cache database public 2>/dev/null || true
chmod -R 775 storage bootstrap/cache database public 2>/dev/null || true

exec "$@"
