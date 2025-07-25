#!/usr/bin/with-contenv bashio
# Run database migrations on startup

bashio::log.info "Running database migrations..."
cd /app/app

# Create runtime directory if it doesn't exist
mkdir -p /app/app/runtime
chmod -R 777 /app/app/runtime

# Run migrations
php yii migrate --interactive=0

bashio::log.info "Database migrations completed."