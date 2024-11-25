#!/bin/bash

TARGET_PATH="/home/u309746653/domains/lapinou.tech/public_html/api"

if [ ! -d "$TARGET_PATH" ]; then
  echo "Error: Target path $TARGET_PATH does not exist. Aborting."
  exit 1
fi

echo "Starting post-deployment tasks..."

composer install --no-dev --optimize-autoloader --working-dir="$TARGET_PATH"

php "$TARGET_PATH/bin/console" doctrine:migrations:migrate --env=prod --no-interaction 

# Effacer et préchauffer le cache
php "$TARGET_PATH/bin/console" cache:clear --env=prod --no-debug
php "$TARGET_PATH/bin/console" cache:warmup --env=prod

echo "Post-deployment tasks completed successfully!"
