#!/bin/bash

# Ensure the right directories exist after the volume mount
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs

# Give the right permissions
chmod -R 777 storage bootstrap/cache

if [ "$1" = "apache2-foreground" ];then
    echo "Running Database Migrations..."
    # Run database migrations automatically
    php artisan migrate --force
fi

sleep 5

# Start Apache in the foreground (keeps container running)
exec "$@"