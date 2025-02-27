#!/bin/sh

# Wait for the database to be ready
sleep 10

# Run Laravel migrations
php artisan migrate --force

# Run the custom command to dispatch the job
php artisan app:run-fetch-crypto-prices

# Execute the original command
exec "$@"
