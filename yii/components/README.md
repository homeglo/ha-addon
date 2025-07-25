# HomeAssistantComponent

A generic, reusable component for interacting with Home Assistant WebSocket API.

## Features

- **Connection Management**: Automatic WebSocket connection and authentication
- **Device Registry**: Get all devices from Home Assistant device registry
- **Entity Registry**: Get all entities from Home Assistant entity registry  
- **State Queries**: Get current states of all entities or specific entities
- **Service Calls**: Call any Home Assistant service (turn on lights, etc.)
- **Event Subscriptions**: Subscribe to Home Assistant events with filtering
- **Helper Methods**: Convenience methods for common operations (lights, switches)
- **Error Handling**: Comprehensive error handling with logging support
- **Async Support**: Built on ReactPHP/Amp for high performance

## Configuration

```php
use app\components\HomeAssistantComponent;

$ha = new HomeAssistantComponent([
    'homeAssistantUrl' => 'ws://homeassistant.local:8123/api/websocket',
    'accessToken' => 'your_long_lived_access_token',
    'logger' => function($message, $level = 'info') {
        echo "[$level] $message\n";
    },
    'connectionTimeout' => 30
]);
```

## Environment Variables

The component automatically reads the `HA_TOKEN` environment variable if no access token is provided:

```bash
export HA_TOKEN="your_long_lived_access_token_here"
```

## Basic Usage

### Test Connection
```php
if ($ha->testConnection()) {
    echo "Connected successfully!";
} else {
    echo "Connection failed!";
}
```

### Get Device Registry
```php
try {
    $devices = $ha->getDeviceRegistry();
    foreach ($devices as $device) {
        echo "Device: " . $device['name'] . " (Model: " . $device['model'] . ")\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Get Entity States
```php
// Get all entity states
$states = $ha->getStates();

// Get specific entity state
$lightState = $ha->getEntityState('light.living_room');
if ($lightState) {
    echo "Light state: " . $lightState['state'];
}
```

### Control Devices

#### Lights
```php
// Turn on a light
$ha->turnOnLight('light.living_room');

// Turn on with brightness
$ha->turnOnLight('light.living_room', ['brightness' => 128]);

// Turn on with color
$ha->turnOnLight('light.living_room', [
    'brightness' => 255,
    'rgb_color' => [255, 0, 0]  // Red
]);

// Turn off
$ha->turnOffLight('light.living_room');
```

#### Switches
```php
$ha->turnOnSwitch('switch.coffee_maker');
$ha->turnOffSwitch('switch.coffee_maker');
```

#### Generic Service Calls
```php
// Call any service
$ha->callService('climate', 'set_temperature', [
    'entity_id' => 'climate.living_room',
    'temperature' => 72
]);

// Multiple entities
$ha->callService('light', 'turn_on', [
    'entity_id' => ['light.living_room', 'light.kitchen'],
    'brightness' => 200
]);
```

### Event Subscriptions

```php
// Subscribe to state changes
$ha->subscribeToEvents('state_changed', function($event) {
    echo "Entity: " . $event['data']['entity_id'] . "\n";
    echo "New State: " . $event['data']['new_state']['state'] . "\n";
});

// Subscribe with entity filter
$ha->subscribeToEvents('state_changed', function($event) {
    // Handle light events only
    echo "Light changed: " . $event['data']['entity_id'] . "\n";
}, 'light.*');

// Subscribe to multiple event types
$ha->subscribeToEvents(['state_changed', 'call_service'], function($event) {
    echo "Event: " . $event['event_type'] . "\n";
});
```

### Raw WebSocket Messages

```php
// Send custom message
$response = $ha->sendRawMessage([
    'type' => 'config/area_registry/list'
]);

$areas = $response['result'];
```

## Advanced Usage in Controllers

### Console Controller Example

```php
use app\components\HomeAssistantComponent;

class MyController extends Controller
{
    private function createHA()
    {
        return new HomeAssistantComponent([
            'homeAssistantUrl' => 'ws://homeassistant.local:8123/api/websocket',
            'accessToken' => $_ENV['HA_TOKEN'],
            'logger' => function($message, $level = 'info') {
                $this->stdout("[$level] $message\n");
            }
        ]);
    }
    
    public function actionSyncDevices()
    {
        $ha = $this->createHA();
        
        try {
            $devices = $ha->getDeviceRegistry();
            
            foreach ($devices as $device) {
                // Process each device
                $this->processDevice($device);
            }
            
        } catch (Exception $e) {
            $this->stderr("Error: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
        
        return ExitCode::OK;
    }
}
```

### Web Controller Example

```php
use app\components\HomeAssistantComponent;

class LightController extends Controller
{
    public function actionToggle($entityId)
    {
        $ha = new HomeAssistantComponent([
            'homeAssistantUrl' => Yii::$app->params['homeAssistantUrl'],
            'accessToken' => Yii::$app->params['homeAssistantToken']
        ]);
        
        try {
            $currentState = $ha->getEntityState($entityId);
            
            if ($currentState['state'] === 'on') {
                $ha->turnOffLight($entityId);
                $newState = 'off';
            } else {
                $ha->turnOnLight($entityId);
                $newState = 'on';
            }
            
            return $this->asJson([
                'success' => true,
                'entity_id' => $entityId,
                'state' => $newState
            ]);
            
        } catch (Exception $e) {
            return $this->asJson([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

## Command Line Usage

The package includes example controllers:

```bash
# Test connection
./yii home-assistant-example/test --token=your_token

# Get devices
./yii home-assistant-example/devices --token=your_token

# Get entity states
./yii home-assistant-example/states --token=your_token

# Filter entity states
./yii home-assistant-example/states light --token=your_token

# Control lights
./yii home-assistant-example/turn-on-light light.living_room --token=your_token
./yii home-assistant-example/turn-on-light light.living_room 128 --token=your_token

# Watch events
./yii home-assistant-example/watch --token=your_token
./yii home-assistant-example/watch "light.*" --token=your_token

# Sync devices (v2)
./yii home-assistant-sync-v2/sync --token=your_token
./yii home-assistant-sync-v2/preview --token=your_token
./yii home-assistant-sync-v2/sync-types "philips,hue" --token=your_token
```

## Error Handling

The component throws exceptions for all error conditions:

```php
try {
    $ha->turnOnLight('light.nonexistent');
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    // Handle error appropriately
}
```

## Available Service Domains

Common Home Assistant service domains you can use with `callService()`:

- `light` - Control lights (turn_on, turn_off, toggle)
- `switch` - Control switches (turn_on, turn_off, toggle)
- `climate` - Control thermostats (set_temperature, set_hvac_mode)
- `media_player` - Control media players (play_media, pause, stop)
- `notify` - Send notifications
- `automation` - Control automations (turn_on, turn_off, trigger)
- `script` - Run scripts
- `scene` - Activate scenes
- `input_boolean` - Control input booleans
- `input_number` - Set input numbers
- `input_select` - Set input selects

## Event Types

Common Home Assistant event types for subscriptions:

- `state_changed` - Entity state changes
- `call_service` - Service calls
- `automation_triggered` - Automation triggers
- `script_started` - Script executions
- `lovelace_updated` - UI updates
- `component_loaded` - Component loads
- `service_registered` - Service registrations
- `platform_discovered` - Platform discoveries

## Performance Notes

- The component uses async/await for all WebSocket operations
- Connection pooling is not implemented - each operation creates a new connection
- For high-frequency operations, consider implementing connection reuse
- Event subscriptions maintain persistent connections
- All operations include proper timeout handling