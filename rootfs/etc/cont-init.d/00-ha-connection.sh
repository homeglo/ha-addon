#!/usr/bin/with-contenv bashio
# Set up Home Assistant connection environment variables

echo "Setting up Home Assistant connection..."

# Get supervisor token from environment
if bashio::var.has_value "SUPERVISOR_TOKEN"; then
    SUPERVISOR_TOKEN="${SUPERVISOR_TOKEN}"
    echo "Found supervisor token"
    
    # Write to a PHP-readable config file
    cat > /data/ha-config.php << EOF
<?php
// Home Assistant connection configuration
define('HA_TOKEN', '${SUPERVISOR_TOKEN}');
define('HA_WEBSOCKET_URL', 'ws://supervisor/core/api/websocket');
define('HA_REST_URL', 'http://supervisor/core/api');
EOF
    
    chmod 644 /data/ha-config.php
    echo "Created HA config file"
else
    echo "Warning: No supervisor token found"
fi

# Copy config.yaml to data directory for PHP access
if [ -f /app/config.yaml ]; then
    cp /app/config.yaml /data/addon-config.yaml
    chmod 644 /data/addon-config.yaml
    echo "Copied addon config to /data"
fi

echo "Home Assistant connection environment configured"