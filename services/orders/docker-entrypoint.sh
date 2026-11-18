#!/bin/sh
set -e

php app.php migrate --force

exec "$@"
