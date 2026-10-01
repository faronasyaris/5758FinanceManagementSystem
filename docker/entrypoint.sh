#!/bin/sh
set -e

echo "Starting 5758inc Finance Management System..."

# Run database migrations if database connection is configured
if [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --isolated || true
fi

# Cache configuration, routes, and views for optimal production performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
exec nginx -g "daemon off;"
