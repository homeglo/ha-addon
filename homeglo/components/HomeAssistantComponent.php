<?php

namespace app\components;

use Amp\ByteStream\StreamException;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketMessage;
use function Amp\Websocket\Client\connect;
use function Amp\async;
use yii\base\Component;
use yii\base\Exception;
use Yii;

/**
 * Generic Home Assistant WebSocket component for interacting with Home Assistant
 * 
 * This component provides reusable methods for:
 * - WebSocket connection management
 * - Authentication handling
 * - Device registry queries
 * - Service calls
 * - Event subscriptions
 * - State queries
 */
class HomeAssistantComponent extends Component
{
    /** @var string Home Assistant WebSocket URL */
    public $homeAssistantUrl;
    
    /** @var string Home Assistant access token */
    public $accessToken = '';
    
    /** @var callable|null Optional callback for logging/output */
    public $logger = null;
    
    /** @var int Connection timeout in seconds */
    public $connectionTimeout = 30;
    
    /** @var int Message ID counter */
    private $messageId = 1;

    public function init()
    {
        parent::init();
        
        // First check if we have a PHP config file (for addon environment)
        $configFile = '/data/ha-config.php';
        if (file_exists($configFile)) {
            include_once $configFile;
            if (defined('HA_WEBSOCKET_URL') && empty($this->homeAssistantUrl)) {
                $this->homeAssistantUrl = HA_WEBSOCKET_URL;
            }
            if (defined('HA_TOKEN') && empty($this->accessToken)) {
                $this->accessToken = HA_TOKEN;
            }
            return; // Use config file values
        }
        
        // Try to get WebSocket URL from environment if not set
        if (empty($this->homeAssistantUrl)) {
            // Check multiple possible environment variables
            $envUrl = $_ENV['HA_WEBSOCKET_URL'] ?? 
                     getenv('HA_WEBSOCKET_URL') ?? 
                     null;
            
            if (!empty($envUrl)) {
                $this->homeAssistantUrl = $envUrl;
            } else {
                // In addon environment, use supervisor endpoint
                if (getenv('SUPERVISOR_TOKEN')) {
                    $this->homeAssistantUrl = 'ws://supervisor/core/api/websocket';
                } else {
                    // Default fallback for development
                    $this->homeAssistantUrl = 'ws://homeassistant.local:8123/api/websocket';
                }
            }
        }
        
        // Try to get access token from environment if not set
        if (empty($this->accessToken)) {
            // Check multiple possible environment variables
            $envToken = $_ENV['HA_TOKEN'] ?? 
                       getenv('HA_TOKEN') ?? 
                       $_ENV['SUPERVISOR_TOKEN'] ?? 
                       getenv('SUPERVISOR_TOKEN') ?? 
                       null;
            
            if (!empty($envToken)) {
                $this->accessToken = $envToken;
            }
        }
    }

    /**
     * Log a message using the configured logger or Yii::info
     * @param string $message
     * @param string $level
     */
    private function log($message, $level = 'info')
    {
        if ($this->logger && is_callable($this->logger)) {
            call_user_func($this->logger, $message, $level);
        } else {
            Yii::info($message, __CLASS__);
        }
    }

    /**
     * Create and authenticate a WebSocket connection to Home Assistant
     * @return WebsocketConnection
     * @throws Exception
     */
    public function createConnection()
    {
        $future = async(function () {
            if (empty($this->accessToken)) {
                throw new Exception('Access token is required for Home Assistant connection');
            }

            $this->log("Connecting to Home Assistant at: {$this->homeAssistantUrl}");
            
            /** @var WebsocketConnection $connection */
            $connection = connect($this->homeAssistantUrl);
            $this->log("✓ WebSocket connection established");

            // Handle authentication
            $message = $connection->receive();
            $payload = $message->buffer();
            $decoded = json_decode($payload, true);
            
            if (!$decoded || !isset($decoded['type'])) {
                throw new Exception('Invalid response from Home Assistant');
            }

            if ($decoded['type'] !== 'auth_required') {
                throw new Exception('Expected auth_required message, got: ' . $decoded['type']);
            }

            // Send authentication
            $authMessage = json_encode([
                'type' => 'auth',
                'access_token' => $this->accessToken
            ]);
            $connection->sendText($authMessage);
            $this->log("Sent authentication credentials");

            // Wait for auth response
            $authMessage = $connection->receive();
            $authPayload = $authMessage->buffer();
            $authDecoded = json_decode($authPayload, true);
            
            if (!$authDecoded || !isset($authDecoded['type'])) {
                throw new Exception('Invalid authentication response');
            }

            if ($authDecoded['type'] === 'auth_invalid') {
                throw new Exception('Authentication failed: Invalid access token');
            }

            if ($authDecoded['type'] !== 'auth_ok') {
                throw new Exception('Authentication failed: ' . json_encode($authDecoded));
            }

            $this->log("✓ Authentication successful");
            return $connection;
        });

        return $future->await();
    }

    /**
     * Test connection to Home Assistant
     * @return bool
     */
    public function testConnection()
    {
        try {
            $connection = $this->createConnection();
            $connection->close();
            $this->log("✓ Connection test successful");
            return true;
        } catch (\Exception $e) {
            $this->log("✗ Connection test failed: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Send a message and wait for response
     * @param WebsocketConnection $connection
     * @param array $message
     * @return array|null
     * @throws Exception
     */
    private function sendMessageAndWaitForResponse($connection, $message)
    {
        $message['id'] = $this->messageId++;
        $messageJson = json_encode($message);
        
        $connection->sendText($messageJson);
        $this->log("Sent message: {$message['type']} (ID: {$message['id']})");

        // Wait for response
        $responseMessage = $connection->receive();
        $responsePayload = $responseMessage->buffer();
        $responseDecoded = json_decode($responsePayload, true);
        
        if (!$responseDecoded) {
            throw new Exception('Invalid JSON response from Home Assistant');
        }

        if (isset($responseDecoded['id']) && $responseDecoded['id'] !== $message['id']) {
            // This might be an event or other message, not our response
            // In a real implementation, you might want to handle this better
            $this->log("Received message with different ID, might be an event");
        }

        return $responseDecoded;
    }

    /**
     * Get the device registry from Home Assistant
     * @return array
     * @throws Exception
     */
    public function getDeviceRegistry()
    {
        $future = async(function () {
            $connection = $this->createConnection();
            
            try {
                $response = $this->sendMessageAndWaitForResponse($connection, [
                    'type' => 'config/device_registry/list'
                ]);

                if (!isset($response['success']) || !$response['success']) {
                    throw new Exception('Device registry request failed: ' . json_encode($response));
                }

                $devices = $response['result'] ?? [];
                $this->log("✓ Retrieved " . count($devices) . " devices from registry");
                
                return $devices;
                
            } finally {
                $connection->close();
            }
        });

        return $future->await();
    }

    /**
     * Get the entity registry from Home Assistant
     * @return array
     * @throws Exception
     */
    public function getEntityRegistry()
    {
        $future = async(function () {
            $connection = $this->createConnection();
            
            try {
                $response = $this->sendMessageAndWaitForResponse($connection, [
                    'type' => 'config/entity_registry/list'
                ]);

                if (!isset($response['success']) || !$response['success']) {
                    throw new Exception('Entity registry request failed: ' . json_encode($response));
                }

                $entities = $response['result'] ?? [];
                $this->log("✓ Retrieved " . count($entities) . " entities from registry");
                
                return $entities;
                
            } finally {
                $connection->close();
            }
        });

        return $future->await();
    }

    /**
     * Get the area registry from Home Assistant
     * @return array
     * @throws Exception
     */
    public function getAreaRegistry()
    {
        $future = async(function () {
            $connection = $this->createConnection();
            
            try {
                $response = $this->sendMessageAndWaitForResponse($connection, [
                    'type' => 'config/area_registry/list'
                ]);

                if (!isset($response['success']) || !$response['success']) {
                    throw new Exception('Area registry request failed: ' . json_encode($response));
                }

                $areas = $response['result'] ?? [];
                $this->log("✓ Retrieved " . count($areas) . " areas from registry");
                
                return $areas;
                
            } finally {
                $connection->close();
            }
        });

        return $future->await();
    }

    /**
     * Get all states from Home Assistant
     * @return array
     * @throws Exception
     */
    public function getStates()
    {
        $future = async(function () {
            $connection = $this->createConnection();
            
            try {
                $response = $this->sendMessageAndWaitForResponse($connection, [
                    'type' => 'get_states'
                ]);

                if (!isset($response['success']) || !$response['success']) {
                    throw new Exception('Get states request failed: ' . json_encode($response));
                }

                $states = $response['result'] ?? [];
                $this->log("✓ Retrieved " . count($states) . " entity states");
                
                return $states;
                
            } finally {
                $connection->close();
            }
        });

        return $future->await();
    }

    /**
     * Get the state of a specific entity
     * @param string $entityId
     * @return array|null
     * @throws Exception
     */
    public function getEntityState($entityId)
    {
        $states = $this->getStates();
        
        foreach ($states as $state) {
            if ($state['entity_id'] === $entityId) {
                return $state;
            }
        }
        
        return null;
    }

    /**
     * Call a service in Home Assistant
     * @param string $domain Service domain (e.g., 'light', 'switch')
     * @param string $service Service name (e.g., 'turn_on', 'turn_off')
     * @param array $serviceData Service data including entity_id
     * @return array Response from Home Assistant
     * @throws Exception
     */
    public function callService($domain, $service, $serviceData = [])
    {
        $future = async(function () use ($domain, $service, $serviceData) {
            $connection = $this->createConnection();
            
            try {
                $this->log("Calling service: {$domain}.{$service}");
                
                $response = $this->sendMessageAndWaitForResponse($connection, [
                    'type' => 'call_service',
                    'domain' => $domain,
                    'service' => $service,
                    'service_data' => $serviceData
                ]);

                if (!isset($response['success']) || !$response['success']) {
                    throw new Exception("Service call failed: " . json_encode($response));
                }

                $this->log("✓ Service call successful");
                return $response;
                
            } finally {
                $connection->close();
            }
        });

        return $future->await();
    }

    /**
     * Subscribe to Home Assistant events
     * @param string|array $eventTypes Event type(s) to subscribe to ('state_changed', 'call_service', etc.)
     * @param callable $eventHandler Callback function to handle events: function($event) {}
     * @param string $entityFilter Optional entity filter (supports wildcards *)
     * @return void
     * @throws Exception
     */
    public function subscribeToEvents($eventTypes, $eventHandler, $entityFilter = '')
    {
        if (!is_callable($eventHandler)) {
            throw new Exception('Event handler must be callable');
        }

        $eventTypes = is_array($eventTypes) ? $eventTypes : [$eventTypes];

        $future = async(function () use ($eventTypes, $eventHandler, $entityFilter) {
            $connection = $this->createConnection();
            
            try {
                $this->log("Subscribing to events: " . implode(', ', $eventTypes));
                
                // Subscribe to each event type
                foreach ($eventTypes as $eventType) {
                    $subscribeMessage = [
                        'type' => 'subscribe_events'
                    ];
                    
                    if ($eventType !== 'all') {
                        $subscribeMessage['event_type'] = $eventType;
                    }
                    
                    $response = $this->sendMessageAndWaitForResponse($connection, $subscribeMessage);
                    
                    if (!isset($response['success']) || !$response['success']) {
                        throw new Exception("Failed to subscribe to {$eventType}: " . json_encode($response));
                    }
                    
                    $this->log("✓ Subscribed to {$eventType} events");
                }

                // Listen for events
                while ($message = $connection->receive()) {
                    /** @var WebsocketMessage $message */
                    $payload = $message->buffer();
                    $decoded = json_decode($payload, true);
                    
                    if (!$decoded || !isset($decoded['type'])) {
                        continue;
                    }

                    if ($decoded['type'] === 'event' && isset($decoded['event'])) {
                        $event = $decoded['event'];
                        
                        // Apply entity filter if specified
                        if (!empty($entityFilter) && isset($event['data']['entity_id'])) {
                            $entityId = $event['data']['entity_id'];
                            if (!$this->matchesEntityFilter($entityId, $entityFilter)) {
                                continue;
                            }
                        }
                        
                        // Call the event handler
                        try {
                            call_user_func($eventHandler, $event);
                        } catch (\Exception $e) {
                            $this->log("Error in event handler: " . $e->getMessage(), 'error');
                        }
                    }
                }
                
            } finally {
                $connection->close();
            }
        });

        $future->await();
    }

    /**
     * Send a raw message to Home Assistant WebSocket API
     * @param array $message Message to send
     * @return array Response from Home Assistant
     * @throws Exception
     */
    public function sendRawMessage($message)
    {
        $future = async(function () use ($message) {
            $connection = $this->createConnection();
            
            try {
                return $this->sendMessageAndWaitForResponse($connection, $message);
            } finally {
                $connection->close();
            }
        });

        return $future->await();
    }

    /**
     * Check if entity ID matches the filter pattern
     * @param string $entityId Entity ID to check
     * @param string $filter Filter pattern (supports wildcards *)
     * @return bool
     */
    private function matchesEntityFilter($entityId, $filter)
    {
        if (empty($filter)) {
            return true;
        }
        
        // Convert wildcard pattern to regex
        $pattern = '/^' . str_replace(['*', '.'], ['.*', '\.'], preg_quote($filter, '/')) . '$/';
        return preg_match($pattern, $entityId);
    }

    /**
     * Get next message ID for requests
     * @return int
     */
    public function getNextMessageId()
    {
        return $this->messageId++;
    }

    /**
     * Helper method to turn on a light with optional parameters
     * @param string $entityId Light entity ID
     * @param array $options Optional parameters (brightness, color, etc.)
     * @return array
     * @throws Exception
     */
    public function turnOnLight($entityId, $options = [])
    {
        $serviceData = array_merge(['entity_id' => $entityId], $options);
        return $this->callService('light', 'turn_on', $serviceData);
    }

    /**
     * Helper method to turn off a light
     * @param string $entityId Light entity ID
     * @return array
     * @throws Exception
     */
    public function turnOffLight($entityId)
    {
        return $this->callService('light', 'turn_off', ['entity_id' => $entityId]);
    }

    /**
     * Helper method to turn on a switch
     * @param string $entityId Switch entity ID
     * @return array
     * @throws Exception
     */
    public function turnOnSwitch($entityId)
    {
        return $this->callService('switch', 'turn_on', ['entity_id' => $entityId]);
    }

    /**
     * Helper method to turn off a switch
     * @param string $entityId Switch entity ID
     * @return array
     * @throws Exception
     */
    public function turnOffSwitch($entityId)
    {
        return $this->callService('switch', 'turn_off', ['entity_id' => $entityId]);
    }

    // ========================================================================
    // UNIFIED LIGHT CONTROL METHODS WITH ELEGANT TRANSITIONS
    // ========================================================================

    /** @var float Default transition time in seconds for elegant light changes */
    public $defaultTransitionTime = 1.0;

    /**
     * Unified method to control Home Assistant lights with elegant transitions
     * This is the main entry point for ALL light control operations
     * 
     * @param array|string $entityIds Single entity ID or array of entity IDs
     * @param array $params Light parameters (brightness, color_temp, xy_color, etc.)
     * @param float|null $transitionTime Transition time in seconds (null for default)
     * @param bool $turnOn Whether to turn lights on (true) or off (false)
     * @return array Response from Home Assistant
     * @throws Exception
     */
    public function controlLights($entityIds, $params = [], $transitionTime = null, $turnOn = true)
    {
        // Ensure entityIds is an array
        if (!is_array($entityIds)) {
            $entityIds = [$entityIds];
        }

        // Remove any empty entity IDs
        $entityIds = array_filter($entityIds);
        
        if (empty($entityIds)) {
            throw new Exception('No valid entity IDs provided for light control');
        }

        // Set default transition time if not specified
        if ($transitionTime === null) {
            $transitionTime = $this->defaultTransitionTime;
        }

        // Prepare service data
        $serviceData = [
            'entity_id' => $entityIds,
            'transition' => $transitionTime
        ];

        // Add light parameters if turning on
        if ($turnOn) {
            $serviceData = array_merge($serviceData, $params);
            $service = 'turn_on';
        } else {
            $service = 'turn_off';
        }

        $this->log("Controlling " . count($entityIds) . " lights with {$transitionTime}s transition");

        return $this->callService('light', $service, $serviceData);
    }

    /**
     * Turn on lights with glo parameters and elegant transition
     * @param array|string $entityIds Entity IDs to control
     * @param \app\models\HgGlo $hgGlo Glo model with light parameters
     * @param float|null $transitionTime Transition time in seconds
     * @return array
     * @throws Exception
     */
    public function turnOnLightsWithGlo($entityIds, $hgGlo, $transitionTime = null)
    {
        $params = $this->convertGloToHaParams($hgGlo);
        return $this->controlLights($entityIds, $params, $transitionTime, true);
    }

    /**
     * Turn off lights with elegant transition
     * @param array|string $entityIds Entity IDs to control
     * @param float|null $transitionTime Transition time in seconds
     * @return array
     * @throws Exception
     */
    public function turnOffLights($entityIds, $transitionTime = null)
    {
        return $this->controlLights($entityIds, [], $transitionTime, false);
    }

    /**
     * Set lights to specific brightness with elegant transition
     * @param array|string $entityIds Entity IDs to control
     * @param int $brightness Brightness (0-255)
     * @param float|null $transitionTime Transition time in seconds
     * @return array
     * @throws Exception
     */
    public function setLightsBrightness($entityIds, $brightness, $transitionTime = null)
    {
        $params = ['brightness' => $this->validateHaBrightness($brightness)];
        return $this->controlLights($entityIds, $params, $transitionTime, true);
    }

    /**
     * Set lights to specific color temperature with elegant transition
     * @param array|string $entityIds Entity IDs to control
     * @param int $colorTemp Color temperature in mireds
     * @param int|null $brightness Optional brightness (0-255)
     * @param float|null $transitionTime Transition time in seconds
     * @return array
     * @throws Exception
     */
    public function setLightsColorTemp($entityIds, $colorTemp, $brightness = null, $transitionTime = null)
    {
        $params = ['color_temp' => $this->validateHaColorTemp($colorTemp)];
        if ($brightness !== null) {
            $params['brightness'] = $this->validateHaBrightness($brightness);
        }
        return $this->controlLights($entityIds, $params, $transitionTime, true);
    }

    /**
     * Set lights to specific XY color with elegant transition
     * @param array|string $entityIds Entity IDs to control
     * @param float $x X coordinate (0.0-1.0)
     * @param float $y Y coordinate (0.0-1.0)
     * @param int|null $brightness Optional brightness (0-255)
     * @param float|null $transitionTime Transition time in seconds
     * @return array
     * @throws Exception
     */
    public function setLightsXyColor($entityIds, $x, $y, $brightness = null, $transitionTime = null)
    {
        $params = ['xy_color' => [(float)$x, (float)$y]];
        if ($brightness !== null) {
            $params['brightness'] = $this->validateHaBrightness($brightness);
        }
        return $this->controlLights($entityIds, $params, $transitionTime, true);
    }

    // ========================================================================
    // CONVERTER METHODS - HUE TO HOME ASSISTANT
    // ========================================================================

    /**
     * Convert HgGlo model parameters to Home Assistant light parameters
     * @param \app\models\HgGlo $hgGlo
     * @return array HA-compatible parameters
     */
    public function convertGloToHaParams($hgGlo)
    {
        $params = [];

        // Convert brightness (always included)
        if ($hgGlo->brightness !== null) {
            $params['brightness'] = $this->convertHueBrightnessToHa($hgGlo->brightness);
        }

        // Color temperature mode
        if ($hgGlo->ct) {
            $params['color_temp'] = $this->convertHueCtToHa($hgGlo->ct);
        }
        // XY color mode
        else if ($hgGlo->hue_x && $hgGlo->hue_y) {
            $params['xy_color'] = [(float)$hgGlo->hue_x, (float)$hgGlo->hue_y];
        }

        return $params;
    }

    /**
     * Convert Hue brightness (0-254) to Home Assistant brightness (0-255)
     * @param int $hueBrightness
     * @return int
     */
    public function convertHueBrightnessToHa($hueBrightness)
    {
        // Convert from Hue scale (0-254) to HA scale (0-255)
        return $this->validateHaBrightness(round(($hueBrightness / 254) * 255));
    }

    /**
     * Convert Hue color temperature (153-500 mireds) to HA color temp (153-500 mireds)
     * @param int $hueCt
     * @return int
     */
    public function convertHueCtToHa($hueCt)
    {
        // Hue and HA use the same mired scale, so direct conversion with validation
        return $this->validateHaColorTemp($hueCt);
    }

    /**
     * Convert Home Assistant device ID to light entity IDs
     * Queries entity registry to find light entities for the device
     * @param string $haDeviceId
     * @param array|null $entityRegistry Pre-fetched entity registry to avoid multiple API calls
     * @return array Array of light entity IDs
     */
    public function getDeviceLightEntities($haDeviceId, $entityRegistry = null)
    {
        $lightEntityIds = [];
        
        // Get entity registry if not provided
        if ($entityRegistry === null) {
            try {
                $entityRegistry = $this->getEntityRegistry();
            } catch (\Exception $e) {
                $this->log("Could not get entity registry for device {$haDeviceId}: " . $e->getMessage(), 'error');
                // Fall through to fallback method
            }
        }

        if ($entityRegistry) {
            // Find all light entities belonging to this device
            foreach ($entityRegistry as $entity) {
                if (isset($entity['device_id']) && $entity['device_id'] === $haDeviceId) {
                    // Check if this is a light entity
                    if (strpos($entity['entity_id'], 'light.') === 0) {
                        $lightEntityIds[] = $entity['entity_id'];
                    }
                }
            }
        }
        
        // If no entities found in registry or registry not available, use fallback
        if (empty($lightEntityIds)) {
            // Fallback: construct entity ID from device ID
            $entityId = "light." . strtolower(str_replace([' ', '-'], '_', $haDeviceId));
            $lightEntityIds[] = $entityId;
            $this->log("Using fallback entity ID for device {$haDeviceId}: {$entityId}");
        }
        
        return $lightEntityIds;
    }

    // ========================================================================
    // VALIDATION METHODS
    // ========================================================================

    /**
     * Validate and clamp Home Assistant brightness value
     * @param int $brightness
     * @return int
     */
    private function validateHaBrightness($brightness)
    {
        return max(0, min(255, (int)$brightness));
    }

    /**
     * Validate and clamp Home Assistant color temperature value
     * @param int $colorTemp
     * @return int
     */
    private function validateHaColorTemp($colorTemp)
    {
        return max(153, min(500, (int)$colorTemp));
    }
}