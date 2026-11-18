#!/bin/sh
set -e

# Міграції + демо-дані перед стартом RoadRunner
php app.php migrate --force
php app.php products:seed

exec "$@"
