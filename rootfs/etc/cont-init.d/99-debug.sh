#!/usr/bin/with-contenv bashio
set -e

bashio::log.info "===== DEBUG SNAPSHOT ====="

bashio::log.info "1) php-fpm workers (user/group):"
ps -o user,group,args | grep -E '[p]hp.-fpm' || bashio::log.info "  (php-fpm not started yet)"

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

bashio::log.info "===== END DEBUG SNAPSHOT ====="