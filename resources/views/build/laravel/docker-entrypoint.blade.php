#!/bin/bash
set -e

# Set up Laravel project
php artisan event:cache

if [ -z "$DB_DATABASE" ]
then
    echo "No database present; not migrating"
else
    php artisan migrate --force
fi

# Call the Apache2 entrypoint
exec apache2-foreground "$@"
