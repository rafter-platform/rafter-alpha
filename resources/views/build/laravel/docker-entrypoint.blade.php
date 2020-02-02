#!/bin/bash
set -e

# Set up Laravel project
php artisan event:cache

if [ -z "$DB_DATABASE" ]
then
    php artisan migrate --force
fi

# Call the Apache2 entrypoint
exec apache2-foreground "$@"
