#!/bin/sh
# Ensure assets directory has correct permissions at runtime

echo "Fixing permissions for runtime directories..."

# Create directories if they don't exist
mkdir -p /app/homeglo/runtime /app/homeglo/web/assets

# Set ownership to nginx user (both nginx and php-fpm run as nginx)
chown -R nginx:nginx /app/homeglo/runtime /app/homeglo/web/assets

# Set permissions (make sure directories are writable)
chmod -R 775 /app/homeglo/runtime /app/homeglo/web/assets

# Ensure parent directory is accessible
chown nginx:nginx /app/homeglo/web
chmod 755 /app/homeglo/web

# Verify permissions
echo "Permissions after fix:"
ls -la /app/homeglo/web/ | grep assets
ls -la /app/homeglo/ | grep runtime

echo "Fixed permissions for runtime directories"