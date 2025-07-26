#!/usr/bin/with-contenv bashio
# Runs Yii DB migrations exactly once per container start

set -Eeuo pipefail

###############################################################################
# Config - adjust if you change your image
###############################################################################
APP_DIR="/app/homeglo"
DB_FILE="/data/database.sqlite"
RUN_USER="nginx"          # php-fpm pool user (see php*-fpm.d/www.conf)

###############################################################################
# Helpers
###############################################################################
log()   { bashio::log.info  "$*"; }
fatal() { bashio::log.error "$*"; exit 1; }

###############################################################################
# 1. Prep writable /data (volume is mounted *after* build, so do it here)
###############################################################################
install -d -o "$RUN_USER" -g "$RUN_USER" -m 775 /data

###############################################################################
# 2. Ensure SQLite file exists with safe perms
###############################################################################
if [[ ! -f "$DB_FILE" ]]; then
  log "Creating fresh database at $DB_FILE"
  install -o "$RUN_USER" -g "$RUN_USER" -m 660 /dev/null "$DB_FILE"
else
  log "Database file already in place"
fi

export DB_PATH="$DB_FILE"

###############################################################################
# 3. Find PHP binary once (8.3 first, else fallback)
###############################################################################
PHP_CMD=$(command -v php83 || command -v php) || fatal "No PHP CLI found"

###############################################################################
# 4. Run Yii migrations; bail hard if they fail
###############################################################################
cd "$APP_DIR" || fatal "Cannot cd to $APP_DIR"
log "Running migrations with $(basename "$PHP_CMD")"
"$PHP_CMD" yii migrate/up --interactive=0

###############################################################################
# 5. Re-own everything under /data (WAL/SHM files may have appeared)
###############################################################################
# Get the actual nginx user/group IDs from system
NGINX_UID=$(id -u nginx 2>/dev/null || echo "100")
NGINX_GID=$(id -g nginx 2>/dev/null || echo "101") 

log "Setting ownership to nginx user (UID:$NGINX_UID, GID:$NGINX_GID)"
chown -R "$NGINX_UID":"$NGINX_GID" /data
# Use more permissive permissions to ensure access
chmod 666 "$DB_FILE" 
chmod 777 /data
log "Database file permissions set to 666, directory to 777"

# Also make sure the nginx user can write to runtime directories
chown -R "$NGINX_UID":"$NGINX_GID" /app/homeglo/runtime /app/homeglo/web/assets
chmod -R 775 /app/homeglo/runtime /app/homeglo/web/assets

###############################################################################
# 6. Smoke-test write access
###############################################################################
log "Verifying SQLite write access…"
"$PHP_CMD" -r "
  \$pdo=new PDO('sqlite:$DB_FILE');
  \$pdo->exec('CREATE TABLE IF NOT EXISTS _test(id INTEGER)');
  \$pdo->exec('INSERT INTO _test VALUES (1)');
  \$pdo->exec('DROP TABLE _test');
"
log 'Migration script completed OK'