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
chmod 777 /data
chown -R nginx:nginx /data 2>/dev/null || chown -R 82:82 /data 2>/dev/null || true

# Create database file in /data if it doesn't exist
if [ ! -f /data/database.sqlite ]; then
    bashio::log.info "Creating database file in /data..."
    touch /data/database.sqlite
    chmod 777 /data/database.sqlite
    chown nginx:nginx /data/database.sqlite 2>/dev/null || chown 82:82 /data/database.sqlite 2>/dev/null || true
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

# Fix database permissions after migrations
if [ -f /data/database.sqlite ]; then
    bashio::log.info "Database file exists, checking current permissions:"
    ls -la /data/database.sqlite
    bashio::log.info "Setting database file permissions and ownership..."
    chmod 777 /data/database.sqlite
    chown nginx:nginx /data/database.sqlite 2>/dev/null || chown 82:82 /data/database.sqlite 2>/dev/null || true
    bashio::log.info "Permissions after chmod/chown:"
    ls -la /data/database.sqlite
    bashio::log.info "Directory permissions for /data:"
    ls -ld /data/
else
    bashio::log.warning "Database file not found after migrations"
fi

# Test database write access
bashio::log.info "Testing database write access..."
export DB_PATH="/data/database.sqlite"
$PHP_CMD -r "
try {
    \$pdo = new PDO('sqlite:$DB_PATH');
    \$pdo->exec('CREATE TABLE IF NOT EXISTS test_write (id INTEGER)');
    \$pdo->exec('INSERT INTO test_write (id) VALUES (1)');
    \$pdo->exec('DROP TABLE test_write');
    echo 'Database write test: SUCCESS\n';
} catch (Exception \$e) {
    echo 'Database write test FAILED: ' . \$e->getMessage() . '\n';
}
" 2>&1

bashio::log.info "=== MIGRATION SCRIPT COMPLETED ==="