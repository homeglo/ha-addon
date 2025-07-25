<?php

namespace app\commands;

use app\components\HomeAssistantComponent;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * Example controller showing how to use the HomeAssistantComponent
 */
class HomeAssistantExampleController extends Controller
{
    public $homeAssistantUrl;
    public $accessToken = '';

    public function init()
    {
        parent::init();
        
        // Initialize from environment variables if not set via command line
        if (empty($this->homeAssistantUrl)) {
            $this->homeAssistantUrl = $_ENV['HA_WEBSOCKET_URL'] ?? 'ws://homeassistant.local:8123/api/websocket';
        }
        if (empty($this->accessToken)) {
            $this->accessToken = $_ENV['HA_TOKEN'] ?? '';
        }
    }

    public function options($actionID)
    {
        return array_merge(parent::options($actionID), ['homeAssistantUrl', 'accessToken']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'url' => 'homeAssistantUrl',
            'token' => 'accessToken'
        ]);
    }

    /**
     * Test the Home Assistant connection
     */
    public function actionTest()
    {
        $ha = $this->createHomeAssistantComponent();
        
        if ($ha->testConnection()) {
            $this->stdout("✓ Home Assistant connection successful!\n");
            return ExitCode::OK;
        } else {
            $this->stdout("✗ Home Assistant connection failed!\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Get and display device registry
     */
    public function actionDevices()
    {
        $ha = $this->createHomeAssistantComponent();
        
        try {
            $devices = $ha->getDeviceRegistry();
            
            $this->stdout("=== HOME ASSISTANT DEVICES ===\n");
            $this->stdout("Found " . count($devices) . " devices:\n\n");
            
            foreach ($devices as $device) {
                $this->stdout("Device: " . ($device['name'] ?? 'Unknown') . "\n");
                $this->stdout("  ID: " . $device['id'] . "\n");
                $this->stdout("  Model: " . ($device['model'] ?? 'Unknown') . "\n");
                $this->stdout("  Manufacturer: " . ($device['manufacturer'] ?? 'Unknown') . "\n");
                $this->stdout("  Area: " . ($device['area_id'] ?? 'None') . "\n");
                $this->stdout("\n");
            }
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            $this->stdout("✗ Error getting devices: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Get and display entity states
     */
    public function actionStates($filter = '')
    {
        $ha = $this->createHomeAssistantComponent();
        
        try {
            $states = $ha->getStates();
            
            $this->stdout("=== HOME ASSISTANT ENTITY STATES ===\n");
            
            $filteredStates = [];
            foreach ($states as $state) {
                if (empty($filter) || strpos($state['entity_id'], $filter) !== false) {
                    $filteredStates[] = $state;
                }
            }
            
            $this->stdout("Found " . count($filteredStates) . " entities");
            if (!empty($filter)) {
                $this->stdout(" matching '{$filter}'");
            }
            $this->stdout(":\n\n");
            
            foreach ($filteredStates as $state) {
                $this->stdout("Entity: " . $state['entity_id'] . "\n");
                $this->stdout("  State: " . $state['state'] . "\n");
                if (!empty($state['attributes']['friendly_name'])) {
                    $this->stdout("  Name: " . $state['attributes']['friendly_name'] . "\n");
                }
                $this->stdout("  Last Changed: " . $state['last_changed'] . "\n");
                $this->stdout("\n");
            }
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            $this->stdout("✗ Error getting states: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Turn on a light with optional brightness
     */
    public function actionTurnOnLight($entityId, $brightness = null)
    {
        $ha = $this->createHomeAssistantComponent();
        
        try {
            $options = [];
            if ($brightness !== null) {
                $options['brightness'] = (int)$brightness;
            }
            
            $result = $ha->turnOnLight($entityId, $options);
            $this->stdout("✓ Light {$entityId} turned on successfully\n");
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            $this->stdout("✗ Error turning on light: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Turn off a light
     */
    public function actionTurnOffLight($entityId)
    {
        $ha = $this->createHomeAssistantComponent();
        
        try {
            $result = $ha->turnOffLight($entityId);
            $this->stdout("✓ Light {$entityId} turned off successfully\n");
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            $this->stdout("✗ Error turning off light: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Call a custom service
     */
    public function actionCallService($domain, $service, $entityId, $data = '{}')
    {
        $ha = $this->createHomeAssistantComponent();
        
        try {
            $serviceData = json_decode($data, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->stdout("✗ Invalid JSON data: " . json_last_error_msg() . "\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }
            
            $serviceData['entity_id'] = $entityId;
            
            $result = $ha->callService($domain, $service, $serviceData);
            $this->stdout("✓ Service {$domain}.{$service} called successfully on {$entityId}\n");
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            $this->stdout("✗ Error calling service: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Subscribe to state changes
     */
    public function actionWatch($entityFilter = '')
    {
        $ha = $this->createHomeAssistantComponent();
        
        $this->stdout("Watching Home Assistant events...\n");
        if (!empty($entityFilter)) {
            $this->stdout("Entity filter: {$entityFilter}\n");
        }
        $this->stdout("Press Ctrl+C to stop\n\n");
        
        try {
            $ha->subscribeToEvents('state_changed', function($event) {
                $this->displayEvent($event);
            }, $entityFilter);
            
            return ExitCode::OK;
            
        } catch (\Exception $e) {
            $this->stdout("✗ Error watching events: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Create and configure the Home Assistant component
     * @return HomeAssistantComponent
     */
    private function createHomeAssistantComponent()
    {
        $ha = new HomeAssistantComponent([
            'homeAssistantUrl' => $this->homeAssistantUrl,
            'accessToken' => $this->accessToken,
            'logger' => function($message, $level = 'info') {
                $this->stdout("[$level] $message\n");
            }
        ]);
        
        return $ha;
    }

    /**
     * Display a formatted event
     * @param array $event
     */
    private function displayEvent($event)
    {
        $this->stdout("=== EVENT: " . ($event['event_type'] ?? 'unknown') . " ===\n");
        $this->stdout("Time: " . date('Y-m-d H:i:s') . "\n");
        
        if (isset($event['data']['entity_id'])) {
            $this->stdout("Entity: " . $event['data']['entity_id'] . "\n");
        }
        
        if (isset($event['data']['new_state']['state'])) {
            $this->stdout("New State: " . $event['data']['new_state']['state'] . "\n");
        }
        
        if (isset($event['data']['old_state']['state'])) {
            $this->stdout("Old State: " . $event['data']['old_state']['state'] . "\n");
        }
        
        $this->stdout("========================\n\n");
    }
}