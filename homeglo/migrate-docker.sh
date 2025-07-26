#!/bin/bash

echo "Running migrations in Docker container..."
docker exec -it homeglo-php-container php yii migrate --interactive=0

echo "Migration completed!"