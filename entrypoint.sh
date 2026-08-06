#!/bin/bash

# Ensure the right directories exist after the volume mount
mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache storage/logs

# Give the right permissions
chmod -R 777 storage bootstrap/cache

# Wait for Psql to connect to the database
echo "Waiting for Postgres"
while ! nc -z $DB_HOST $DB_PORT; do
    sleep 1
done
echo "Postgres is ready"

sleep 5

# Run database migrations automatically
php artisan migrate --force

# Start Apache in the foreground (keeps container running)
exec "$@"