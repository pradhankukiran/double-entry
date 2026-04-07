#!/bin/bash
set -e

echo "=== Running migrations and seeds ==="
php /var/www/bin/setup.php

echo "=== Starting PHP server on port ${PORT:-8080} ==="
exec php -S 0.0.0.0:${PORT:-8080} -t /var/www/public /var/www/public/router.php
