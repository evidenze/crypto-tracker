#!/bin/sh

sleep 10

# Run Laravel migrations
php artisan migrate --force

# Execute the original command
exec "$@"
