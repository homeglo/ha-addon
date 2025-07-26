#!/bin/sh
# Set up Home Assistant connection environment variables

echo "Setting up Home Assistant connection..."

# Get supervisor token from environment
if [ -n "$SUPERVISOR_TOKEN" ]; then
    echo "Found supervisor token"
    export HA_TOKEN="$SUPERVISOR_TOKEN"
    
    # Write to PHP environment file for persistence
    echo "HA_TOKEN=$SUPERVISOR_TOKEN" >> /app/homeglo/.env
else
    echo "Warning: No supervisor token found"
fi

# Set Home Assistant WebSocket URL
# Inside addon container, use supervisor/core endpoint
export HA_WEBSOCKET_URL="ws://supervisor/core/api/websocket"
echo "HA_WEBSOCKET_URL=ws://supervisor/core/api/websocket" >> /app/homeglo/.env

# Also set REST API URL
export HA_REST_URL="http://supervisor/core/api"
echo "HA_REST_URL=http://supervisor/core/api" >> /app/homeglo/.env

echo "Home Assistant connection environment configured"