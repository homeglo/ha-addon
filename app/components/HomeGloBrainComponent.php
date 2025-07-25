<?php

namespace app\components;

use app\models\HgDeviceGroup;
use app\models\HgDeviceSensor;
use app\models\HgGlo;
use app\models\HgGloDeviceGroup;
use app\models\HgHubActionCondition;
use app\models\HgHubActionItem;
use app\models\HgHubActionMap;
use app\models\HgHubActionTemplate;
use app\models\HgHubActionTrigger;
use yii\base\Component;
use yii\base\Exception;
use yii\helpers\ArrayHelper;
use Yii;

/**
 * HomeGloBrainComponent - Central event processor for HomeGlo automation
 * 
 * This component listens to Home Assistant events (particularly ZHA events) and
 * uses the action map templates to determine what actions to execute.
 */
class HomeGloBrainComponent extends Component
{
    /** @var HomeAssistantComponent */
    public $homeAssistant;
    
    /** @var callable|null Optional callback for logging */
    public $logger = null;
    
    /** @var array Cache for entity registry to avoid repeated API calls */
    private $entityRegistryCache = null;
    
    /** @var array Cache for active triggers by event type */
    private $triggerCache = [];
    
    /** @var bool Whether to cache trigger lookups */
    public $enableTriggerCache = true;
    
    /**
     * Initialize the component
     */
    public function init()
    {
        parent::init();
        
        if (!$this->homeAssistant) {
            $this->homeAssistant = new HomeAssistantComponent();
        }
        
        // Preload active triggers if caching is enabled
        if ($this->enableTriggerCache) {
            $this->preloadTriggers();
        }
    }
    
    /**
     * Log a message using the configured logger or Yii::info
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
     * Start listening to Home Assistant events and process them
     * @param array|string $eventTypes Event types to listen to (default: 'zha_event')
     * @throws Exception
     */
    public function startListening($eventTypes = 'zha_event')
    {
        $this->log("HomeGlo Brain starting up...");
        $this->log("Listening for events: " . (is_array($eventTypes) ? implode(', ', $eventTypes) : $eventTypes));
        
        // Set up event handler
        $eventHandler = function($event) {
            try {
                $this->processEvent($event);
            } catch (\Exception $e) {
                $this->log("Error processing event: " . $e->getMessage(), 'error');
                $this->log("Event data: " . json_encode($event), 'debug');
            }
        };
        
        // Subscribe to events
        $this->homeAssistant->subscribeToEvents($eventTypes, $eventHandler);
    }
    
    /**
     * Process a single event from Home Assistant
     * @param array $event
     */
    public function processEvent($event)
    {
        $eventType = $event['event_type'] ?? '';
        $eventData = $event['data'] ?? [];
        
        $this->log("Processing {$eventType} event");
        
        // Find matching triggers
        $triggers = $this->findMatchingTriggers($event);
        
        if (empty($triggers)) {
            $this->log("No matching triggers found for event");
            return;
        }
        
        $this->log("Found " . count($triggers) . " matching trigger(s)");
        
        // Process each matching trigger
        foreach ($triggers as $trigger) {
            $this->processTrigger($trigger, $event);
        }
    }
    
    /**
     * Find triggers that match the given event
     * @param array $event
     * @return HgHubActionTrigger[]
     */
    protected function findMatchingTriggers($event)
    {
        $eventType = $event['event_type'] ?? '';
        $eventData = $event['data'] ?? [];
        
        // For ZHA events, match on device_id and event/command
        if ($eventType === 'zha_event') {
            $deviceId = $eventData['device_id'] ?? null;
            $command = $eventData['command'] ?? null;
            $params = $eventData['params'] ?? [];
            
            if (!$deviceId) {
                return [];
            }
            
            // Build query for ZHA event triggers
            $query = HgHubActionTrigger::find()
                ->joinWith(['hgHubActionTemplate.hgHubActionMap'])
                ->where(['ha_device_id' => $deviceId])
                ->andWhere(['event_name' => $command]);
            
            // Add additional event data matching if needed
            if (!empty($params)) {
                // For button events, check button ID
                if (isset($params['button'])) {
                    $query->andWhere(['event_data->{{hue_switch_button_id}}' => $params['button']]);
                }
            }
            
            return $query->all();
        }
        
        // For state_changed events
        if ($eventType === 'state_changed') {
            $entityId = $eventData['entity_id'] ?? null;
            $oldState = $eventData['old_state']['state'] ?? null;
            $newState = $eventData['new_state']['state'] ?? null;
            
            // TODO: Implement state change trigger matching
            return [];
        }
        
        // For other event types, do a general search
        return HgHubActionTrigger::find()
            ->where(['source_name' => $eventType])
            ->all();
    }
    
    /**
     * Process a trigger that matched an event
     * @param HgHubActionTrigger $trigger
     * @param array $event
     */
    protected function processTrigger($trigger, $event)
    {
        $this->log("Processing trigger: {$trigger->display_name} (ID: {$trigger->id})");
        
        // Evaluate all conditions
        if (!$this->evaluateConditions($trigger, $event)) {
            $this->log("Conditions not met for trigger");
            return;
        }
        
        $this->log("All conditions met, executing actions");
        
        // Execute all actions
        $this->executeActions($trigger, $event);
    }
    
    /**
     * Evaluate all conditions for a trigger
     * @param HgHubActionTrigger $trigger
     * @param array $event
     * @return bool True if all conditions pass
     */
    protected function evaluateConditions($trigger, $event)
    {
        $conditions = $trigger->hgHubActionConditions;
        
        if (empty($conditions)) {
            return true; // No conditions means always execute
        }
        
        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $trigger, $event)) {
                $this->log("Condition failed: {$condition->display_name}");
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Evaluate a single condition
     * @param HgHubActionCondition $condition
     * @param HgHubActionTrigger $trigger
     * @param array $event
     * @return bool
     */
    protected function evaluateCondition($condition, $trigger, $event)
    {
        // Get the event data with variable substitution
        $eventData = $condition->populateEventData();
        
        // Resolve the property value
        $propertyValue = $this->resolvePropertyValue($condition->property, $eventData, $event);
        
        // Get the expected value
        $expectedValue = $condition->value;
        if ($expectedValue !== null) {
            $expectedValue = strtr($expectedValue, $eventData);
        }
        
        // Perform comparison based on operator
        switch ($condition->operator) {
            case 'eq':
                return $propertyValue == $expectedValue;
            case 'neq':
                return $propertyValue != $expectedValue;
            case 'gt':
                return $propertyValue > $expectedValue;
            case 'lt':
                return $propertyValue < $expectedValue;
            case 'gte':
                return $propertyValue >= $expectedValue;
            case 'lte':
                return $propertyValue <= $expectedValue;
            case 'in':
                $values = is_array($expectedValue) ? $expectedValue : explode(',', $expectedValue);
                return in_array($propertyValue, $values);
            case 'ddx': // Day/time check
                return $this->evaluateDayTimeCondition($propertyValue, $expectedValue);
            default:
                $this->log("Unknown operator: {$condition->operator}", 'warning');
                return false;
        }
    }
    
    /**
     * Resolve a property value from the event or Home Assistant state
     * @param string $property
     * @param array $eventData
     * @param array $event
     * @return mixed
     */
    protected function resolvePropertyValue($property, $eventData, $event)
    {
        // First, substitute any variables in the property path
        $property = strtr($property, $eventData);
        
        // Check if this is a state query (e.g., /sensors/{{device_id}}/state)
        if (strpos($property, '/sensors/') === 0 || strpos($property, '/lights/') === 0) {
            return $this->queryHomeAssistantState($property);
        }
        
        // Check if this is an event data path
        if (strpos($property, '/event/') === 0) {
            $path = substr($property, 7); // Remove '/event/'
            return ArrayHelper::getValue($event, $path);
        }
        
        // Check if this is a config query (e.g., /config/localtime)
        if (strpos($property, '/config/') === 0) {
            return $this->queryConfig($property);
        }
        
        // Default: return the property as-is
        return $property;
    }
    
    /**
     * Query Home Assistant for current state
     * @param string $path
     * @return mixed
     */
    protected function queryHomeAssistantState($path)
    {
        // Extract entity type and ID from path
        if (preg_match('#^/(sensors|lights)/([^/]+)/(.+)$#', $path, $matches)) {
            $entityType = $matches[1];
            $entityId = $matches[2];
            $attribute = $matches[3];
            
            try {
                // Convert to Home Assistant entity ID format
                $haEntityId = $entityType === 'sensors' ? "sensor.{$entityId}" : "light.{$entityId}";
                $state = $this->homeAssistant->getEntityState($haEntityId);
                
                if ($state) {
                    if ($attribute === 'state') {
                        return $state['state'];
                    } else {
                        return ArrayHelper::getValue($state, "attributes.{$attribute}");
                    }
                }
            } catch (\Exception $e) {
                $this->log("Failed to query state for {$path}: " . $e->getMessage(), 'warning');
            }
        }
        
        return null;
    }
    
    /**
     * Query configuration values
     * @param string $path
     * @return mixed
     */
    protected function queryConfig($path)
    {
        if ($path === '/config/localtime') {
            return date('Y-m-d\TH:i:s');
        }
        
        // Add more config queries as needed
        return null;
    }
    
    /**
     * Evaluate day/time condition
     * @param string $currentTime
     * @param string $timeRange
     * @return bool
     */
    protected function evaluateDayTimeCondition($currentTime, $timeRange)
    {
        // Parse time range format (e.g., "W127/T06:00:00/T23:00:00")
        if (preg_match('#W(\d+)/T(\d{2}:\d{2}:\d{2})/T(\d{2}:\d{2}:\d{2})#', $timeRange, $matches)) {
            $dayMask = $matches[1];
            $startTime = $matches[2];
            $endTime = $matches[3];
            
            // Check day of week (1=Monday, 7=Sunday)
            $currentDay = date('N');
            $dayBit = pow(2, $currentDay);
            
            if (!($dayMask & $dayBit)) {
                return false;
            }
            
            // Check time
            $current = date('H:i:s');
            return $current >= $startTime && $current <= $endTime;
        }
        
        return false;
    }
    
    /**
     * Execute all actions for a trigger
     * @param HgHubActionTrigger $trigger
     * @param array $event
     */
    protected function executeActions($trigger, $event)
    {
        $actions = $trigger->hgHubActionItems;
        
        foreach ($actions as $action) {
            try {
                $this->executeAction($action, $trigger, $event);
            } catch (\Exception $e) {
                $this->log("Failed to execute action {$action->display_name}: " . $e->getMessage(), 'error');
            }
        }
    }
    
    /**
     * Execute a single action
     * @param HgHubActionItem $action
     * @param HgHubActionTrigger $trigger
     * @param array $event
     */
    protected function executeAction($action, $trigger, $event)
    {
        $this->log("Executing action: {$action->display_name} ({$action->operation_name})");
        
        // Get populated event data
        $eventData = $action->populateEventData();
        
        // Resolve the entity target
        $entity = strtr($action->entity, $eventData);
        
        switch ($action->operation_name) {
            case 'turn_on_scene':
                $this->executeTurnOnScene($action, $entity, $eventData);
                break;
                
            case 'turn_off_room':
                $this->executeTurnOffRoom($action, $entity, $eventData);
                break;
                
            case 'adjust_brightness':
                $this->executeAdjustBrightness($action, $entity, $eventData);
                break;
                
            case 'set_sensor_state':
            case 'set_deviceGroup_sensor_state':
                $this->executeSetSensorState($action, $entity, $eventData);
                break;
                
            case 'storelightstate':
            case 'storelightstate_deviceGroup':
                $this->executeStoreLightState($action, $entity, $eventData);
                break;
                
            case 'turn_on_temp_motion_scene':
            case 'turn_on_temp_deviceGroup_scene':
                $this->executeTurnOnTempScene($action, $entity, $eventData);
                break;
                
            default:
                $this->log("Unknown action operation: {$action->operation_name}", 'warning');
        }
    }
    
    /**
     * Execute turn on scene action
     * @param HgHubActionItem $action
     * @param string $entity
     * @param array $eventData
     */
    protected function executeTurnOnScene($action, $entity, $eventData)
    {
        // Check if this is an "off" scene
        if ($eventData['{{hue_scene_id}}'] === HgHubActionItem::ACTION_HG_OFF_NAME) {
            $this->executeTurnOffRoom($action, $entity, $eventData);
            return;
        }
        
        // Get the Glo parameters
        if ($action->hg_glo_id) {
            $glo = HgGlo::findOne($action->hg_glo_id);
            if (!$glo) {
                $this->log("Glo not found: {$action->hg_glo_id}", 'error');
                return;
            }
            
            // Get light entities for the device group
            $lightEntities = $this->getDeviceGroupLightEntities($action->operate_hg_device_light_group_id);
            
            if (empty($lightEntities)) {
                $this->log("No light entities found for device group", 'warning');
                return;
            }
            
            // Calculate transition time
            $transitionTime = $this->calculateTransitionTime($action, $eventData);
            
            // Turn on lights with Glo settings
            $this->homeAssistant->turnOnLightsWithGlo($lightEntities, $glo, $transitionTime);
            
            $this->log("Turned on " . count($lightEntities) . " lights with Glo: {$glo->display_name}");
        }
    }
    
    /**
     * Execute turn off room action
     * @param HgHubActionItem $action
     * @param string $entity
     * @param array $eventData
     */
    protected function executeTurnOffRoom($action, $entity, $eventData)
    {
        // Get light entities for the device group
        $lightEntities = $this->getDeviceGroupLightEntities($action->operate_hg_device_light_group_id);
        
        if (empty($lightEntities)) {
            $this->log("No light entities found for device group", 'warning');
            return;
        }
        
        // Calculate transition time
        $transitionTime = $this->calculateTransitionTime($action, $eventData);
        
        // Turn off lights
        $this->homeAssistant->turnOffLights($lightEntities, $transitionTime);
        
        $this->log("Turned off " . count($lightEntities) . " lights");
    }
    
    /**
     * Execute adjust brightness action
     * @param HgHubActionItem $action
     * @param string $entity
     * @param array $eventData
     */
    protected function executeAdjustBrightness($action, $entity, $eventData)
    {
        // Get brightness increment
        $briIncrement = $action->override_bri_increment_percent;
        if (!is_numeric($briIncrement)) {
            $briIncrement = strtr($briIncrement, $eventData);
        }
        $briIncrement = (int)$briIncrement;
        
        // Get light entities
        $lightEntities = $this->getDeviceGroupLightEntities($action->operate_hg_device_light_group_id);
        
        foreach ($lightEntities as $lightEntity) {
            try {
                // Get current brightness
                $state = $this->homeAssistant->getEntityState($lightEntity);
                $currentBrightness = $state['attributes']['brightness'] ?? 0;
                
                // Calculate new brightness (convert increment from percentage)
                $newBrightness = $currentBrightness + ceil($briIncrement * 255 / 100);
                $newBrightness = max(0, min(255, $newBrightness));
                
                // Set new brightness
                $transitionTime = $this->calculateTransitionTime($action, $eventData);
                $this->homeAssistant->setLightsBrightness($lightEntity, $newBrightness, $transitionTime);
                
            } catch (\Exception $e) {
                $this->log("Failed to adjust brightness for {$lightEntity}: " . $e->getMessage(), 'error');
            }
        }
    }
    
    /**
     * Execute set sensor state action
     * @param HgHubActionItem $action
     * @param string $entity
     * @param array $eventData
     */
    protected function executeSetSensorState($action, $entity, $eventData)
    {
        // Parse entity path to get sensor ID
        if (preg_match('#/sensors/([^/]+)/#', $entity, $matches)) {
            $sensorId = $matches[1];
            
            // TODO: Implement sensor state setting via Home Assistant
            // This might involve input_boolean or input_select entities
            $this->log("Set sensor state not yet implemented for: {$sensorId}");
        }
    }
    
    /**
     * Execute store light state action
     * @param HgHubActionItem $action
     * @param string $entity
     * @param array $eventData
     */
    protected function executeStoreLightState($action, $entity, $eventData)
    {
        // TODO: Implement light state storage
        // This would store current light states for later restoration
        $this->log("Store light state not yet implemented");
    }
    
    /**
     * Execute turn on temporary scene action
     * @param HgHubActionItem $action
     * @param string $entity
     * @param array $eventData
     */
    protected function executeTurnOnTempScene($action, $entity, $eventData)
    {
        // TODO: Implement temporary scene activation
        // This is used for motion-triggered scenes that revert after a timeout
        $this->log("Temporary scene activation not yet implemented");
    }
    
    /**
     * Calculate transition time for an action
     * @param HgHubActionItem $action
     * @param array $eventData
     * @return float Transition time in seconds
     */
    protected function calculateTransitionTime($action, $eventData)
    {
        if ($action->override_transition_duration_ms) {
            $transitionMs = $action->override_transition_duration_ms;
            if (!is_numeric($transitionMs)) {
                $transitionMs = strtr($transitionMs, $eventData);
            }
            return (float)$transitionMs / 1000;
        }
        
        return $this->homeAssistant->defaultTransitionTime;
    }
    
    /**
     * Get light entity IDs for a device group
     * @param int $deviceGroupId
     * @return array
     */
    protected function getDeviceGroupLightEntities($deviceGroupId)
    {
        $deviceGroup = HgDeviceGroup::findOne($deviceGroupId);
        if (!$deviceGroup) {
            return [];
        }
        
        $entities = [];
        
        // Get all lights in the device group
        foreach ($deviceGroup->hgDeviceGroupLights as $groupLight) {
            if ($groupLight->hgDeviceLight && $groupLight->hgDeviceLight->ha_device_id) {
                // Get light entities for this device
                $lightEntities = $this->homeAssistant->getDeviceLightEntities(
                    $groupLight->hgDeviceLight->ha_device_id,
                    $this->getEntityRegistryCache()
                );
                $entities = array_merge($entities, $lightEntities);
            }
        }
        
        return array_unique($entities);
    }
    
    /**
     * Get cached entity registry
     * @return array|null
     */
    protected function getEntityRegistryCache()
    {
        if ($this->entityRegistryCache === null) {
            try {
                $this->entityRegistryCache = $this->homeAssistant->getEntityRegistry();
            } catch (\Exception $e) {
                $this->log("Failed to load entity registry: " . $e->getMessage(), 'warning');
                $this->entityRegistryCache = [];
            }
        }
        
        return $this->entityRegistryCache;
    }
    
    /**
     * Preload triggers for faster lookup
     */
    protected function preloadTriggers()
    {
        $this->log("Preloading active triggers...");
        
        // Load all active triggers with their relations
        $triggers = HgHubActionTrigger::find()
            ->joinWith(['hgHubActionTemplate.hgHubActionMap', 'hgHubActionConditions', 'hgHubActionItems'])
            ->where(['hg_hub_action_trigger.hg_status_id' => 1]) // Assuming 1 = active
            ->all();
        
        // Group by event type for faster lookup
        foreach ($triggers as $trigger) {
            $eventName = $trigger->event_name ?: 'unknown';
            if (!isset($this->triggerCache[$eventName])) {
                $this->triggerCache[$eventName] = [];
            }
            $this->triggerCache[$eventName][] = $trigger;
        }
        
        $this->log("Preloaded " . count($triggers) . " triggers");
    }
    
    /**
     * Clear trigger cache
     */
    public function clearTriggerCache()
    {
        $this->triggerCache = [];
        $this->entityRegistryCache = null;
    }
}