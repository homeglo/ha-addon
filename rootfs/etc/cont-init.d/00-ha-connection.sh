#!/usr/bin/with-contenv bashio

echo "Setting up Home Assistant connection..."

TOKEN="$(bashio::var.json SUPERVISOR_TOKEN 2>/dev/null || echo '')"

if [[ -n "$TOKEN" ]]; then
    echo "Found supervisor token"

    cat > /data/ha-config.php << EOF
<?php
define('HA_TOKEN', '${TOKEN}');
define('HA_WEBSOCKET_URL', 'ws://supervisor/core/api/websocket');
define('HA_REST_URL', 'http://supervisor/core/api');
EOF

    chmod 644 /data/ha-config.php
    echo "Created HA config file"
else
    echo "Warning: No supervisor token found"
fi

# Copy config.yaml to data directory for PHP access
if [ -f /config.yaml ]; then
    cp /config.yaml /data/addon-config.yaml
    chmod 644 /data/addon-config.yaml
    echo "Copied addon config to /data"
fi