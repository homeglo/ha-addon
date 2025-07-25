#!/usr/bin/with-contenv bashio
# Run database migrations on startup

bashio::log.info "Preparing database..."
cd /app/homeglo

# Create runtime directory if it doesn't exist
mkdir -p /app/homeglo/runtime
chmod -R 777 /app/homeglo/runtime

# Ensure /data directory exists (Home Assistant persistent storage)
mkdir -p /data
chmod -R 777 /data

# Create database file in /data if it doesn't exist
if [ ! -f /data/database.sqlite ]; then
    bashio::log.info "Creating database file in /data..."
    touch /data/database.sqlite
    chmod 666 /data/database.sqlite
fi

# Set DB_PATH environment variable for Yii
export DB_PATH="/data/database.sqlite"

# Run migrations
bashio::log.info "Running database migrations..."
php yii migrate --interactive=0 || {
    bashio::log.error "Migration failed, but continuing startup..."
    exit 0  # Exit with 0 to allow container to continue
}

bashio::log.info "Database setup completed."