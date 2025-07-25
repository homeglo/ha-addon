#!/usr/bin/with-contenv bashio
# Debug script to check file structure

bashio::log.info "Checking file structure..."
bashio::log.info "Contents of /app/homeglo/web:"
ls -la /app/homeglo/web/ || true
bashio::log.info "Database file location check:"
ls -la /data/database.sqlite 2>/dev/null && bashio::log.info "Database exists in /data" || bashio::log.info "Database not found in /data"