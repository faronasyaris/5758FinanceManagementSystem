#!/bin/sh
set -e

echo "Starting 5758inc Finance Management System on Render..."

# Configure port dynamically if provided by Render ($PORT)
PORT=${PORT:-80}
echo "Configuring Nginx to listen on port ${PORT}..."
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/http.d/default.conf
sed -i "s/listen \[::\]:80;/listen [::]:${PORT};/g" /etc/nginx/http.d/default.conf

# Run database migrations & seeding if DB_HOST is configured
if [ -n "$DB_HOST" ]; then
    echo "Running database migrations..."
    php artisan migrate --force --isolated || true

    echo "Running database seeders for users and master data..."
    php artisan db:seed --force || true
fi

# Cache configuration, routes, and views for optimal production performance
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start PHP-FPM in background
php-fpm -D

# Start Nginx in foreground
echo "5758inc Finance Management System is ready!"
exec nginx -g "daemon off;"
