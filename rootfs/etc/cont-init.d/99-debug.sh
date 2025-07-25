#!/usr/bin/with-contenv bashio
# Debug script to check file structure

bashio::log.info "Checking file structure..."
bashio::log.info "Contents of /app:"
ls -la /app/ 2>&1 | head -20
bashio::log.info "Contents of /app/app:"
ls -la /app/app/ 2>&1 | head -20
bashio::log.info "Contents of /app/app/web:"
ls -la /app/app/web/ 2>&1 | head -20
bashio::log.info "PHP-FPM user:"
ps aux | grep php-fpm