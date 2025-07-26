# Running HomeGlo in Standalone Mode

HomeGlo can run outside of Home Assistant in standalone mode. This is useful for development or if you want to run it on a separate server.

## Setup

### 1. Using Environment Variables (Recommended for Docker)

Set these environment variables:

```bash
export HA_WEBSOCKET_URL="ws://homeassistant.local:8123/api/websocket"
export HA_TOKEN="your-long-lived-access-token"
```

Or with Docker:

```bash
docker run -e HA_WEBSOCKET_URL="ws://homeassistant.local:8123/api/websocket" \
           -e HA_TOKEN="your-token" \
           -p 8080:80 \
           homeglo
```

### 2. Using Configuration File

Copy the example config:

```bash
cp homeglo/config/ha-standalone.php homeglo/config/ha-config.php
```

Edit `homeglo/config/ha-config.php`:

```php
return [
    'websocket_url' => 'ws://192.168.1.100:8123/api/websocket',
    'access_token' => 'your-long-lived-access-token',
];
```

### 3. Create a Long-Lived Access Token

1. In Home Assistant, click your profile (bottom left)
2. Go to "Security" tab
3. Scroll to "Long-Lived Access Tokens"
4. Click "Create Token"
5. Give it a name like "HomeGlo"
6. Copy the token (you won't see it again!)

## Access Modes

HomeGlo automatically detects how it's being accessed:

- **Ingress Mode**: Through Home Assistant sidebar (full integration)
- **Standalone-HA Mode**: Direct access with HA connection (sync available)
- **Standalone Mode**: No HA connection (basic features only)

## Testing Connection

Visit: `http://your-server/api/ha/test-connection`

This will show detailed debug information about the connection status.

## Troubleshooting

### Connection Failed

1. Check the WebSocket URL is correct
2. Verify the access token is valid
3. Ensure Home Assistant is accessible from the HomeGlo server
4. Check firewall rules allow WebSocket connections (port 8123)

### Environment Variables Not Working

Some web servers don't pass environment variables to PHP. Try:

1. Using the config file method instead
2. Setting variables in your web server config
3. Using a `.env` file (if supported)

### CORS Issues

If accessing from a browser, you may need to configure CORS in Home Assistant:

```yaml
http:
  cors_allowed_origins:
    - http://your-homeglo-server:port
```