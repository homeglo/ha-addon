#!/bin/sh
# Ensure assets directory has correct permissions at runtime

# Create directories if they don't exist
mkdir -p /app/homeglo/runtime /app/homeglo/web/assets

# Set ownership to nginx user
chown -R nginx:nginx /app/homeglo/runtime /app/homeglo/web/assets

# Set permissions
chmod -R 775 /app/homeglo/runtime /app/homeglo/web/assets

# Ensure parent directory is accessible
chown nginx:nginx /app/homeglo/web
chmod 755 /app/homeglo/web

echo "Fixed permissions for runtime directories"