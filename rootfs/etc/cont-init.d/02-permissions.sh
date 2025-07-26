#!/bin/sh
# Ensure assets directory has correct permissions at runtime

echo "Fixing permissions for runtime directories..."

# Create directories if they don't exist
mkdir -p /app/homeglo/runtime /data/assets

# Set ownership to nginx user (both nginx and php-fpm run as nginx)
chown -R nginx:nginx /app/homeglo/runtime /data/assets

# Set permissions (make sure directories are writable)
chmod -R 777 /app/homeglo/runtime /data/assets

# Create symlink from web/assets to /data/assets for compatibility
if [ ! -L /app/homeglo/web/assets ]; then
    rm -rf /app/homeglo/web/assets
    ln -s /data/assets /app/homeglo/web/assets
fi

# Verify permissions
echo "Permissions after fix:"
ls -la /data/ | grep assets
ls -la /app/homeglo/ | grep runtime
ls -la /app/homeglo/web/ | grep assets

echo "Fixed permissions for runtime directories"