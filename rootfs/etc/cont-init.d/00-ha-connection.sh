#!/usr/bin/with-contenv bashio
# Set up Home Assistant connection environment variables

echo "Setting up Home Assistant connection..."

# Try to get supervisor token (addon mode)
TOKEN="$(bashio::var.json SUPERVISOR_TOKEN 2>/dev/null || echo '')"

if [[ -n "$TOKEN" ]]; then
    # Addon mode
    echo "Running in ADDON mode - found supervisor token"
    cat > /data/ha-config.php <<EOF
<?php
// Home Assistant connection configuration (Addon mode)
define('HA_TOKEN', '${TOKEN}');
define('HA_WEBSOCKET_URL', 'ws://supervisor/core/api/websocket');
define('HA_REST_URL', 'http://supervisor/core/api');
EOF
    chmod 644 /data/ha-config.php
    echo "Created addon HA config file"
else
    # Standalone mode - check for environment variables
    echo "Running in STANDALONE mode - no supervisor token"
    
    if [[ -n "$HA_TOKEN" || -n "$HA_ACCESS_TOKEN" ]]; then
        # Use provided environment variables
        STANDALONE_TOKEN="${HA_TOKEN:-$HA_ACCESS_TOKEN}"
        STANDALONE_WS_URL="${HA_WEBSOCKET_URL:-ws://homeassistant.local:8123/api/websocket}"
        STANDALONE_REST_URL="${HA_REST_URL:-http://homeassistant.local:8123/api}"
        
        cat > /data/ha-config.php <<EOF
<?php
// Home Assistant connection configuration (Standalone mode)
define('HA_TOKEN', '${STANDALONE_TOKEN}');
define('HA_WEBSOCKET_URL', '${STANDALONE_WS_URL}');
define('HA_REST_URL', '${STANDALONE_REST_URL}');
EOF
        chmod 644 /data/ha-config.php
        echo "Created standalone HA config file with environment variables"
    else
        echo "No HA token found - config file will use defaults or manual configuration"
    fi
fi

# Copy config.yaml to data directory for PHP access
if [ -f /app/config.yaml ]; then
    cp /app/config.yaml /data/addon-config.yaml
    chmod 644 /data/addon-config.yaml
    echo "Copied addon config to /data"
fi

echo "Home Assistant connection environment configured"