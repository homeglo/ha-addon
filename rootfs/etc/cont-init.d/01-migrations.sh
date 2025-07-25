#!/usr/bin/with-contenv bashio
# Run database migrations on startup

bashio::log.info "=== MIGRATION SCRIPT STARTING ==="
bashio::log.info "Current directory: $(pwd)"
bashio::log.info "Changing to /app/homeglo..."
cd /app/homeglo
bashio::log.info "New directory: $(pwd)"

# Create runtime directory if it doesn't exist
bashio::log.info "Creating runtime directory..."
mkdir -p /app/homeglo/runtime
chmod -R 777 /app/homeglo/runtime

# Ensure /data directory exists (Home Assistant persistent storage)
bashio::log.info "Setting up /data directory..."
mkdir -p /data
chmod -R 777 /data

# Create database file in /data if it doesn't exist
if [ ! -f /data/database.sqlite ]; then
    bashio::log.info "Creating database file in /data..."
    touch /data/database.sqlite
    chmod 666 /data/database.sqlite
else
    bashio::log.info "Database file already exists in /data"
fi

# Set DB_PATH environment variable for Yii
export DB_PATH="/data/database.sqlite"
bashio::log.info "DB_PATH set to: $DB_PATH"

# Check if yii command exists
if [ ! -f "yii" ]; then
    bashio::log.error "Yii command not found! Contents of /app/homeglo:"
    ls -la /app/homeglo/
    exit 1
fi

# Check which PHP command is available
PHP_CMD=""
if command -v php82 >/dev/null 2>&1; then
    PHP_CMD="php82"
    bashio::log.info "Using php82 command"
elif command -v php >/dev/null 2>&1; then
    PHP_CMD="php"
    bashio::log.info "Using php command"
else
    bashio::log.error "No PHP command found!"
    exit 1
fi

# Run migrations
bashio::log.info "Running database migrations with $PHP_CMD..."
bashio::log.info "Migration command output:"
$PHP_CMD yii migrate --interactive=0 2>&1 || {
    bashio::log.error "Migration failed, but continuing startup..."
    bashio::log.error "Error code: $?"
}

bashio::log.info "=== MIGRATION SCRIPT COMPLETED ==="