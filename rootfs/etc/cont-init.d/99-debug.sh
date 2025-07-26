#!/usr/bin/with-contenv bashio
set -e

bashio::log.info "===== DEBUG SNAPSHOT ====="

bashio::log.info "2) /app/homeglo/web contents:"
ls -la /app/homeglo/web || true

bashio::log.info "3) /app/homeglo/runtime permissions:"
ls -ld /app/homeglo/runtime || true

bashio::log.info "4) /data layout:"
ls -la /data || true

bashio::log.info "5) Database file check:"
if [[ -f /data/database.sqlite ]]; then
  ls -l /data/database.sqlite
  bashio::log.info "Database exists in /data"
else
  bashio::log.info "Database NOT found in /data"
fi

su -s /bin/sh nginx -c 'sqlite3 /data/database.sqlite "PRAGMA quick_check;"' \
  && bashio::log.info "SQLite smoke-test: OK" \
  || bashio::log.error "SQLite smoke-test: FAILED for nginx user"

bashio::log.info "===== END DEBUG SNAPSHOT ====="