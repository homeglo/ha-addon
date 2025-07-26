<?php
/**
 * Home Assistant Standalone Configuration
 * 
 * This file is used when running HomeGlo outside of the Home Assistant addon environment.
 * Copy this file to ha-config.php and update with your Home Assistant details.
 */

return [
    // Home Assistant WebSocket URL
    // For local access: ws://homeassistant.local:8123/api/websocket
    // For IP access: ws://192.168.1.100:8123/api/websocket
    'websocket_url' => getenv('HA_WEBSOCKET_URL') ?: 'ws://homeassistant.local:8123/api/websocket',
    
    // Home Assistant Long-Lived Access Token
    // Create one in Home Assistant: Profile -> Security -> Long-Lived Access Tokens
    'access_token' => getenv('HA_ACCESS_TOKEN') ?: '',
    
    // Optional: REST API URL (usually same host as websocket)
    'rest_url' => getenv('HA_REST_URL') ?: 'http://homeassistant.local:8123/api',
];