#!/bin/bash
set -e

# Wait for MySQL to be ready
echo "Waiting for MySQL..."
until php -r "try { new PDO('mysql:host='.\$_SERVER['DB_HOST'].';port='.(\$_SERVER['DB_PORT']??3306).';dbname='.\$_SERVER['DB_DATABASE'], \$_SERVER['DB_USERNAME'], \$_SERVER['DB_PASSWORD']); echo 'ok'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
    echo "  MySQL not ready yet, retrying in 2s..."
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
