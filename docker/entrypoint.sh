#!/bin/sh
set -e

echo "Starting PHP-FPM..."
php-fpm -c /usr/local/etc/php-fpm.conf -D

echo "Waiting for PHP-FPM to initialize..."
sleep 2

echo "Starting nginx..."
exec nginx -g 'daemon off;'
