#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
while ! mysqladmin ping -h"$DB_HOST" -u"$DB_USERNAME" -p"$DB_PASSWORD" --silent 2>/dev/null; do
    sleep 2
done
echo "MySQL is ready!"

# Install dependencies if needed
if [ -f "composer.json" ] && [ ! -d "vendor" ]; then
    composer install --no-interaction --optimize-autoloader
fi

# Generate app key if not set
if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "" ]; then
    php artisan key:generate --force 2>/dev/null || true
fi

# Run migrations and seeders
php artisan migrate --force 2>/dev/null || true
php artisan db:seed --force 2>/dev/null || true

# Clear and cache config
php artisan config:clear
php artisan cache:clear
php artisan view:clear

echo "PizzaPlanet is starting..."

exec "$@"
