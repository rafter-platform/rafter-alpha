#!/bin/bash
set -e

# Set up Laravel project
php artisan event:cache
php artisan migrate --force

# Call the Apache2 entrypoint
exec apache2-foreground "$@"
