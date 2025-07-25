<?php

namespace app\services;

use app\components\HomeAssistantComponent;
use app\models\HgProductSensor;
use app\models\HgDeviceSensor;
use app\models\HgProductLight;
use app\models\HgDeviceLight;
use app\models\HgDeviceGroup;
use app\models\HgHome;
use app\models\HgHub;
use app\models\HgGlozone;
use yii\base\Component;
use yii\base\Exception;
use Yii;

/**
 * Home Assistant Sync Service
 * 
 * This service handles syncing data between Home Assistant and the local database.
 * It can be used from web controllers, CLI commands, and API endpoints.
 */
class HomeAssistantSyncService extends Component
{
    /** @var HomeAssistantComponent */
    private $homeAssistant;
    
    /** @var callable|null Optional callback for logging/output */
    public $logger = null;
    
    /** @var bool Whether to perform a dry run without saving data */
    public $dryRun = false;
    
    const TEMPLATE_HOME_ID = 1;
    const HOMEASSISTANT_HOME_ID = 2;
    const DEFAULT_DEVICE_GROUP_TYPE_ID = 225; // "other" type

    public function init()
    {
        parent::init();
        
        // Initialize HomeAssistant component
        $this->homeAssistant = new HomeAssistantComponent([
            'logger' => $this->logger
        ]);
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
     * Set custom Home Assistant configuration
     * @param string $url WebSocket URL
     * @param string $token Access token
     */
    public function setHomeAssistantConfig($url = null, $token = null)
    {
        if ($url !== null) {
            $this->homeAssistant->homeAssistantUrl = $url;
        }
        if ($token !== null) {
            $this->homeAssistant->accessToken = $token;
        }
    }

    /**
     * Test connection to Home Assistant
     * @return bool
     */
    public function testConnection()
    {
        try {
            return $this->homeAssistant->testConnection();
        } catch (\Exception $e) {
            $this->log("Connection test failed: " . $e->getMessage(), 'error');
            return false;
        }
    }

    /**
     * Sync devices from Home Assistant device registry to hg_device_sensor and hg_device_light
     * @param array $deviceTypeFilter Optional filter for specific device types
     * @return array Sync statistics
     * @throws Exception
     */
    public function syncDevices($deviceTypeFilter = [])
    {
        $this->log("Starting Home Assistant device sync" . ($this->dryRun ? ' (DRY RUN)' : ''));
        
        // Test connection first
        if (!$this->testConnection()) {
            throw new Exception('Failed to connect to Home Assistant');
        }

        // Get device registry
        $devices = $this->homeAssistant->getDeviceRegistry();
        $this->log("Found " . count($devices) . " devices in Home Assistant");

        // Filter devices if specified
        if (!empty($deviceTypeFilter)) {
            $devices = $this->filterDevicesByType($devices, $deviceTypeFilter);
            $this->log("Filtered to " . count($devices) . " devices matching types: " . implode(', ', $deviceTypeFilter));
        }

        // Get product sensors for matching
        $productSensors = HgProductSensor::find()->all();
        $sensorModelIdMap = [];
        foreach ($productSensors as $sensor) {
            if (!empty($sensor->model_id)) {
                $sensorModelIdMap[$sensor->model_id] = $sensor;
            }
        }

        // Get product lights for matching
        $productLights = HgProductLight::find()->all();
        $lightModelIdMap = [];
        foreach ($productLights as $light) {
            if (!empty($light->model_id)) {
                $lightModelIdMap[$light->model_id] = $light;
            }
        }

        $this->log("Found " . count($sensorModelIdMap) . " product sensors with model IDs");
        $this->log("Found " . count($lightModelIdMap) . " product lights with model IDs");

        return $this->processDeviceSync($devices, $sensorModelIdMap, $lightModelIdMap);
    }

    /**
     * Sync latitude and longitude from Home Assistant to hg_home table
     * @return array Sync result
     * @throws Exception
     */
    public function syncLocationData()
    {
        $this->log("Starting Home Assistant location sync" . ($this->dryRun ? ' (DRY RUN)' : ''));
        
        // Test connection first
        if (!$this->testConnection()) {
            throw new Exception('Failed to connect to Home Assistant');
        }

        try {
            // Get Home Assistant configuration which includes location data
            $response = $this->homeAssistant->sendRawMessage([
                'type' => 'get_config'
            ]);

            if (!isset($response['success']) || !$response['success']) {
                throw new Exception('Failed to get Home Assistant configuration: ' . json_encode($response));
            }

            $config = $response['result'];
            $latitude = $config['latitude'] ?? null;
            $longitude = $config['longitude'] ?? null;

            if ($latitude === null || $longitude === null) {
                throw new Exception('Home Assistant location data not available');
            }

            $this->log("Retrieved location from Home Assistant: lat={$latitude}, lng={$longitude}");

            // Get or create Home Assistant home record (ID = 2)
            $home = HgHome::findOne(self::HOMEASSISTANT_HOME_ID);
            
            if (!$home) {
                if ($this->dryRun) {
                    $this->log("[DRY RUN] Would create Home Assistant home record");
                    return [
                        'action' => 'create',
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'dry_run' => true
                    ];
                }

                // Create new home record
                $home = new HgHome();
                $home->id = self::HOMEASSISTANT_HOME_ID;
                $home->name = 'homeassistant';
                $home->display_name = 'Home Assistant';
                $home->lat = $latitude;
                $home->lng = $longitude;
                
                if ($home->save()) {
                    $this->log("Created Home Assistant home record with location data");
                    return [
                        'action' => 'create',
                        'latitude' => $latitude,
                        'longitude' => $longitude,
                        'success' => true
                    ];
                } else {
                    throw new Exception('Failed to create Home Assistant home record: ' . implode(', ', $home->getFirstErrors()));
                }
                
            } else {
                // Update existing home record if location changed
                $locationChanged = ($home->lat != $latitude || $home->lng != $longitude);
                
                if (!$locationChanged) {
                    $this->log("Location data unchanged, no update needed");
                    return [
                        'action' => 'no_change',
                        'latitude' => $latitude,
                        'longitude' => $longitude
                    ];
                }

                if ($this->dryRun) {
                    $this->log("[DRY RUN] Would update location: lat={$home->lat} -> {$latitude}, lng={$home->lng} -> {$longitude}");
                    return [
                        'action' => 'update',
                        'old_latitude' => $home->lat,
                        'old_longitude' => $home->lng,
                        'new_latitude' => $latitude,
                        'new_longitude' => $longitude,
                        'dry_run' => true
                    ];
                }

                $oldLat = $home->lat;
                $oldLng = $home->lng;
                
                $home->lat = $latitude;
                $home->lng = $longitude;
                
                if ($home->save()) {
                    $this->log("Updated Home Assistant home location: lat={$oldLat} -> {$latitude}, lng={$oldLng} -> {$longitude}");
                    return [
                        'action' => 'update',
                        'old_latitude' => $oldLat,
                        'old_longitude' => $oldLng,
                        'new_latitude' => $latitude,
                        'new_longitude' => $longitude,
                        'success' => true
                    ];
                } else {
                    throw new Exception('Failed to update Home Assistant home record: ' . implode(', ', $home->getFirstErrors()));
                }
            }

        } catch (\Exception $e) {
            $this->log("Location sync failed: " . $e->getMessage(), 'error');
            throw $e;
        }
    }


    /**
     * Perform a complete sync of devices, areas, and location data
     * @param array $deviceTypeFilter Optional filter for specific device types
     * @return array Complete sync results
     * @throws Exception
     */
    public function syncAll($deviceTypeFilter = [])
    {
        $results = [
            'devices' => null,
            'location' => null,
            'success' => false,
            'errors' => []
        ];

        try {
            // Sync location data first (needed for areas sync)
            $results['location'] = $this->syncLocationData();
            $this->log("Location sync completed successfully");
        } catch (\Exception $e) {
            $results['errors'][] = "Location sync failed: " . $e->getMessage();
            $this->log("Location sync failed: " . $e->getMessage(), 'error');
        }


        try {
            // Sync devices
            $results['devices'] = $this->syncDevices($deviceTypeFilter);
            $this->log("Device sync completed successfully");
        } catch (\Exception $e) {
            $results['errors'][] = "Device sync failed: " . $e->getMessage();
            $this->log("Device sync failed: " . $e->getMessage(), 'error');
        }

        $results['success'] = empty($results['errors']);
        
        return $results;
    }

    /**
     * Filter devices by manufacturer or model type
     * @param array $devices
     * @param array $types
     * @return array
     */
    private function filterDevicesByType($devices, $types)
    {
        $filteredDevices = [];
        
        foreach ($devices as $device) {
            $manufacturer = strtolower($device['manufacturer'] ?? '');
            $model = strtolower($device['model'] ?? '');
            
            foreach ($types as $type) {
                $type = strtolower($type);
                if (strpos($manufacturer, $type) !== false || strpos($model, $type) !== false) {
                    $filteredDevices[] = $device;
                    break;
                }
            }
        }

        return $filteredDevices;
    }

    /**
     * Process device synchronization
     * @param array $devices
     * @param array $sensorModelIdMap
     * @param array $lightModelIdMap
     * @return array
     */
    private function processDeviceSync($devices, $sensorModelIdMap, $lightModelIdMap)
    {
        $stats = [
            'total_devices' => count($devices),
            'matched_sensors' => 0,
            'matched_lights' => 0,
            'created_sensors' => 0,
            'created_lights' => 0,
            'updated_sensors' => 0,
            'updated_lights' => 0,
            'removed_sensors' => 0,
            'removed_lights' => 0,
            'removed_areas' => 0,
            'skipped' => 0,
            'errors' => 0,
            'unknown_light_entities' => []
        ];

        foreach ($devices as $device) {
            $deviceModel = $device['model'] ?? null;
            
            if (empty($deviceModel)) {
                continue;
            }

            $deviceProcessed = false;

            // Check if this device model matches any product sensor
            if (isset($sensorModelIdMap[$deviceModel])) {
                $productSensor = $sensorModelIdMap[$deviceModel];
                $stats['matched_sensors']++;
                $deviceProcessed = true;

                $this->log("✓ Matched sensor device: {$device['name_by_user']} (Model: {$deviceModel})");

                // Check if device already exists
                $existingDevice = HgDeviceSensor::find()
                    ->where(['ha_device_id' => $device['id']])
                    ->one();

                if ($existingDevice) {
                    // Update existing device if needed
                    if ($this->updateDeviceSensor($existingDevice, $device, $productSensor)) {
                        $stats['updated_sensors']++;
                    } else {
                        $stats['skipped']++;
                    }
                } else {
                    if ($this->dryRun) {
                        $this->log("  - [DRY RUN] Would create device sensor");
                        $stats['created_sensors']++;
                    } else {
                        // Create new device sensor entry
                        if ($this->createDeviceSensor($device, $productSensor)) {
                            $stats['created_sensors']++;
                        } else {
                            $stats['errors']++;
                        }
                    }
                }
            }

            // Check if this device model matches any product light
            if (isset($lightModelIdMap[$deviceModel])) {
                $productLight = $lightModelIdMap[$deviceModel];
                $stats['matched_lights']++;
                $deviceProcessed = true;

                $this->log("✓ Matched light device: {$device['name_by_user']} (Model: {$deviceModel})");

                // Check if device already exists
                $existingDevice = HgDeviceLight::find()
                    ->where(['ha_device_id' => $device['id']])
                    ->one();

                if ($existingDevice) {
                    // Update existing device if needed
                    if ($this->updateDeviceLight($existingDevice, $device, $productLight)) {
                        $stats['updated_lights']++;
                    } else {
                        $stats['skipped']++;
                    }
                } else {
                    if ($this->dryRun) {
                        $this->log("  - [DRY RUN] Would create device light");
                        $stats['created_lights']++;
                    } else {
                        // Create new device light entry
                        if ($this->createDeviceLight($device, $productLight)) {
                            $stats['created_lights']++;
                        } else {
                            $stats['errors']++;
                        }
                    }
                }
            }

            if (!$deviceProcessed) {
                $this->log("  - No matching product found for device: {$device['name_by_user']} (Model: {$deviceModel})");
            }
        }

        // After processing all devices, clean up orphaned devices and empty areas
        $cleanupStats = $this->cleanupOrphanedDevicesAndAreas($devices);
        $stats = array_merge($stats, $cleanupStats);

        // Find unknown light entities
        $unknownLightEntities = $this->findUnknownLightEntities($devices);
        $stats['unknown_light_entities'] = $unknownLightEntities;

        return $stats;
    }

    /**
     * Create device group for area if it doesn't exist and device has area_id
     * @param string $areaId
     * @param HgHub $hub
     * @param HgGlozone $glozone
     * @return HgDeviceGroup|null
     */
    private function createOrFindAreaDeviceGroup($areaId, $hub, $glozone)
    {
        if (empty($areaId)) {
            return null;
        }

        // Check if area already exists as device group
        $existingGroup = HgDeviceGroup::find()
            ->where(['ha_device_id' => $areaId])
            ->andWhere(['hg_hub_id' => $hub->id])
            ->one();

        if ($existingGroup) {
            return $existingGroup;
        }

        // Get area information from Home Assistant
        try {
            $areas = $this->homeAssistant->getAreaRegistry();
            $areaInfo = null;
            
            foreach ($areas as $area) {
                if ($area['area_id'] === $areaId) {
                    $areaInfo = $area;
                    break;
                }
            }

            if (!$areaInfo) {
                $this->log("  - Area info not found for area_id: {$areaId}");
                return null;
            }

            if ($this->dryRun) {
                $this->log("  - [DRY RUN] Would create device group for area: {$areaInfo['name']}");
                return null;
            }

            // Create new device group entry for the area
            $deviceGroup = new HgDeviceGroup();
            $deviceGroup->display_name = $areaInfo['name'] ?? 'Unknown Area';
            $deviceGroup->ha_device_id = $areaId;
            $deviceGroup->hg_hub_id = $hub->id;
            $deviceGroup->hg_glozone_id = $glozone->id;
            $deviceGroup->hg_device_group_type_id = self::DEFAULT_DEVICE_GROUP_TYPE_ID;
            $deviceGroup->room_invoke_order = 0;
            
            // Store comprehensive area metadata
            $deviceGroup->metadata = [
                'ha_area_id' => $areaId,
                'ha_name' => $areaInfo['name'] ?? null,
                'ha_aliases' => $areaInfo['aliases'] ?? [],
                'ha_picture' => $areaInfo['picture'] ?? null,
                'synced_from_ha' => true,
                'sync_timestamp' => time(),
                'sync_version' => '2.0',
                'sync_type' => 'area_from_device'
            ];

            if ($deviceGroup->save()) {
                $this->log("  ✓ Created area device group: {$deviceGroup->display_name}");
                return $deviceGroup;
            } else {
                $this->log("  ✗ Failed to create area device group: " . implode(', ', $deviceGroup->getFirstErrors()), 'error');
                return null;
            }

        } catch (\Exception $e) {
            $this->log("  ✗ Failed to get area info: " . $e->getMessage(), 'error');
            return null;
        }
    }

    /**
     * Create a new device sensor entry
     * @param array $device
     * @param HgProductSensor $productSensor
     * @return bool
     */
    private function createDeviceSensor($device, $productSensor)
    {
        // Get required Home Assistant home setup
        $homeAssistantHome = HgHome::findOne(self::HOMEASSISTANT_HOME_ID);
        if (!$homeAssistantHome) {
            $this->log("  ✗ Home Assistant home not found for sensor device", 'error');
            return false;
        }

        $hub = HgHub::find()->where(['hg_home_id' => $homeAssistantHome->id])->one();
        if (!$hub) {
            $this->log("  ✗ No hub found for Home Assistant home", 'error');
            return false;
        }

        $glozone = $homeAssistantHome->getDefaultGlozone();
        if (!$glozone) {
            $this->log("  ✗ No default glozone found for Home Assistant home", 'error');
            return false;
        }

        // Create or find area device group if device has area_id
        $deviceGroup = null;
        if (!empty($device['area_id'])) {
            $deviceGroup = $this->createOrFindAreaDeviceGroup($device['area_id'], $hub, $glozone);
        }

        $deviceSensor = new HgDeviceSensor();
        $deviceSensor->display_name = $device['name_by_user'] ?? 'Unknown Device';
        $deviceSensor->ha_device_id = $device['id'];
        $deviceSensor->hg_product_sensor_id = $productSensor->id;
        
        // Set device group if found
        if ($deviceGroup) {
            $deviceSensor->hg_device_group_id = $deviceGroup->id;
            $this->log("  - Assigned to area: {$deviceGroup->display_name}");
        }
        
        // Store comprehensive device metadata
        $deviceSensor->metadata = [
            'ha_device_id' => $device['id'],
            'ha_manufacturer' => $device['manufacturer'] ?? null,
            'ha_model' => $device['model'] ?? null,
            'ha_name' => $device['name_by_user'] ?? null,
            'ha_sw_version' => $device['sw_version'] ?? null,
            'ha_hw_version' => $device['hw_version'] ?? null,
            'ha_via_device_id' => $device['via_device_id'] ?? null,
            'ha_area_id' => $device['area_id'] ?? null,
            'ha_configuration_url' => $device['configuration_url'] ?? null,
            'ha_connections' => $device['connections'] ?? [],
            'ha_identifiers' => $device['identifiers'] ?? [],
            'ha_entry_type' => $device['entry_type'] ?? null,
            'ha_disabled_by' => $device['disabled_by'] ?? null,
            'synced_from_ha' => true,
            'sync_timestamp' => time(),
            'sync_version' => '2.0'
        ];

        if ($deviceSensor->save()) {
            $this->log("  ✓ Created device sensor (ID: {$deviceSensor->id})");
            return true;
        } else {
            $this->log("  ✗ Failed to save device sensor: " . implode(', ', $deviceSensor->getFirstErrors()), 'error');
            return false;
        }
    }

    /**
     * Create a new device light entry
     * @param array $device
     * @param HgProductLight $productLight
     * @return bool
     */
    private function createDeviceLight($device, $productLight)
    {
        // We need to find a hub and default device group for lights
        $homeAssistantHome = HgHome::findOne(self::HOMEASSISTANT_HOME_ID);
        if (!$homeAssistantHome) {
            $this->log("  ✗ Home Assistant home not found for light device", 'error');
            return false;
        }

        $hub = HgHub::find()->where(['hg_home_id' => $homeAssistantHome->id])->one();
        if (!$hub) {
            $this->log("  ✗ No hub found for Home Assistant home", 'error');
            return false;
        }

        $glozone = $homeAssistantHome->getDefaultGlozone();
        if (!$glozone) {
            $this->log("  ✗ No default glozone found for Home Assistant home", 'error');
            return false;
        }

        // Create or find area device group if device has area_id
        $deviceGroup = null;
        if (!empty($device['area_id'])) {
            $deviceGroup = $this->createOrFindAreaDeviceGroup($device['area_id'], $hub, $glozone);
        }

        $deviceLight = new HgDeviceLight();
        $deviceLight->display_name = $device['name_by_user'] ?? 'Unknown Light';
        $deviceLight->ha_device_id = $device['id'];
        $deviceLight->hg_hub_id = $hub->id;
        $deviceLight->hg_product_light_id = $productLight->id;
        
        // Set device group if found
        if ($deviceGroup) {
            $deviceLight->primary_hg_device_group_id = $deviceGroup->id;
            $this->log("  - Assigned to area: {$deviceGroup->display_name}");
        }
        
        // Store comprehensive device metadata
        $deviceLight->metadata = [
            'ha_device_id' => $device['id'],
            'ha_manufacturer' => $device['manufacturer'] ?? null,
            'ha_model' => $device['model'] ?? null,
            'ha_name' => $device['name_by_user'] ?? null,
            'ha_sw_version' => $device['sw_version'] ?? null,
            'ha_hw_version' => $device['hw_version'] ?? null,
            'ha_via_device_id' => $device['via_device_id'] ?? null,
            'ha_area_id' => $device['area_id'] ?? null,
            'ha_configuration_url' => $device['configuration_url'] ?? null,
            'ha_connections' => $device['connections'] ?? [],
            'ha_identifiers' => $device['identifiers'] ?? [],
            'ha_entry_type' => $device['entry_type'] ?? null,
            'ha_disabled_by' => $device['disabled_by'] ?? null,
            'synced_from_ha' => true,
            'sync_timestamp' => time(),
            'sync_version' => '2.0'
        ];

        if ($deviceLight->save()) {
            $this->log("  ✓ Created device light (ID: {$deviceLight->id})");
            return true;
        } else {
            $this->log("  ✗ Failed to save device light: " . implode(', ', $deviceLight->getFirstErrors()), 'error');
            return false;
        }
    }

    /**
     * Update an existing device sensor if area or other properties changed
     * @param HgDeviceSensor $existingDevice
     * @param array $device
     * @param HgProductSensor $productSensor
     * @return bool True if device was updated, false if no changes needed
     */
    private function updateDeviceSensor($existingDevice, $device, $productSensor)
    {
        $updated = false;
        $changes = [];

        // Get required Home Assistant home setup for glozone
        $homeAssistantHome = HgHome::findOne(self::HOMEASSISTANT_HOME_ID);
        if (!$homeAssistantHome) {
            $this->log("  ✗ Home Assistant home not found for sensor update", 'error');
            return false;
        }

        $hub = HgHub::find()->where(['hg_home_id' => $homeAssistantHome->id])->one();
        if (!$hub) {
            $this->log("  ✗ No hub found for Home Assistant home", 'error');
            return false;
        }

        $glozone = $homeAssistantHome->getDefaultGlozone();
        if (!$glozone) {
            $this->log("  ✗ No default glozone found for Home Assistant home", 'error');
            return false;
        }

        // Check if area assignment changed
        $currentAreaId = $device['area_id'] ?? null;
        $existingAreaId = $existingDevice->metadata['ha_area_id'] ?? null;

        if ($currentAreaId !== $existingAreaId) {
            // Area changed - find or create new area device group
            $deviceGroup = null;
            if (!empty($currentAreaId)) {
                $deviceGroup = $this->createOrFindAreaDeviceGroup($currentAreaId, $hub, $glozone);
            }

            $existingDevice->hg_device_group_id = $deviceGroup ? $deviceGroup->id : null;
            $changes[] = "area: {$existingAreaId} -> {$currentAreaId}";
            $updated = true;
        }

        // Check if display name changed
        $currentName = $device['name_by_user'] ?? 'Unknown Device';
        if ($existingDevice->display_name !== $currentName) {
            $existingDevice->display_name = $currentName;
            $changes[] = "name: {$existingDevice->display_name} -> {$currentName}";
            $updated = true;
        }

        // Always update metadata to keep it current
        $existingDevice->metadata = [
            'ha_device_id' => $device['id'],
            'ha_manufacturer' => $device['manufacturer'] ?? null,
            'ha_model' => $device['model'] ?? null,
            'ha_name' => $device['name_by_user'] ?? null,
            'ha_sw_version' => $device['sw_version'] ?? null,
            'ha_hw_version' => $device['hw_version'] ?? null,
            'ha_via_device_id' => $device['via_device_id'] ?? null,
            'ha_area_id' => $device['area_id'] ?? null,
            'ha_configuration_url' => $device['configuration_url'] ?? null,
            'ha_connections' => $device['connections'] ?? [],
            'ha_identifiers' => $device['identifiers'] ?? [],
            'ha_entry_type' => $device['entry_type'] ?? null,
            'ha_disabled_by' => $device['disabled_by'] ?? null,
            'synced_from_ha' => true,
            'sync_timestamp' => time(),
            'sync_version' => '2.0'
        ];

        if ($updated || !empty($changes)) {
            if ($this->dryRun) {
                $changeStr = implode(', ', $changes);
                $this->log("  - [DRY RUN] Would update sensor: {$changeStr}");
                return true;
            } else {
                if ($existingDevice->save()) {
                    $changeStr = implode(', ', $changes);
                    $this->log("  ✓ Updated sensor device: {$changeStr}");
                    return true;
                } else {
                    $this->log("  ✗ Failed to update sensor device: " . implode(', ', $existingDevice->getFirstErrors()), 'error');
                    return false;
                }
            }
        } else {
            $this->log("  - Sensor device unchanged, skipping");
            return false;
        }
    }

    /**
     * Update an existing device light if area or other properties changed
     * @param HgDeviceLight $existingDevice
     * @param array $device
     * @param HgProductLight $productLight
     * @return bool True if device was updated, false if no changes needed
     */
    private function updateDeviceLight($existingDevice, $device, $productLight)
    {
        $updated = false;
        $changes = [];

        // Get required Home Assistant home setup for glozone
        $homeAssistantHome = HgHome::findOne(self::HOMEASSISTANT_HOME_ID);
        if (!$homeAssistantHome) {
            $this->log("  ✗ Home Assistant home not found for light update", 'error');
            return false;
        }

        $hub = HgHub::find()->where(['hg_home_id' => $homeAssistantHome->id])->one();
        if (!$hub) {
            $this->log("  ✗ No hub found for Home Assistant home", 'error');
            return false;
        }

        $glozone = $homeAssistantHome->getDefaultGlozone();
        if (!$glozone) {
            $this->log("  ✗ No default glozone found for Home Assistant home", 'error');
            return false;
        }

        // Check if area assignment changed
        $currentAreaId = $device['area_id'] ?? null;
        $existingAreaId = $existingDevice->metadata['ha_area_id'] ?? null;

        if ($currentAreaId !== $existingAreaId) {
            // Area changed - find or create new area device group
            $deviceGroup = null;
            if (!empty($currentAreaId)) {
                $deviceGroup = $this->createOrFindAreaDeviceGroup($currentAreaId, $hub, $glozone);
            }

            $existingDevice->primary_hg_device_group_id = $deviceGroup ? $deviceGroup->id : null;
            $changes[] = "area: {$existingAreaId} -> {$currentAreaId}";
            $updated = true;
        }

        // Check if display name changed
        $currentName = $device['name_by_user'] ?? 'Unknown Light';
        if ($existingDevice->display_name !== $currentName) {
            $existingDevice->display_name = $currentName;
            $changes[] = "name: {$existingDevice->display_name} -> {$currentName}";
            $updated = true;
        }

        // Always update metadata to keep it current
        $existingDevice->metadata = [
            'ha_device_id' => $device['id'],
            'ha_manufacturer' => $device['manufacturer'] ?? null,
            'ha_model' => $device['model'] ?? null,
            'ha_name' => $device['name_by_user'] ?? null,
            'ha_sw_version' => $device['sw_version'] ?? null,
            'ha_hw_version' => $device['hw_version'] ?? null,
            'ha_via_device_id' => $device['via_device_id'] ?? null,
            'ha_area_id' => $device['area_id'] ?? null,
            'ha_configuration_url' => $device['configuration_url'] ?? null,
            'ha_connections' => $device['connections'] ?? [],
            'ha_identifiers' => $device['identifiers'] ?? [],
            'ha_entry_type' => $device['entry_type'] ?? null,
            'ha_disabled_by' => $device['disabled_by'] ?? null,
            'synced_from_ha' => true,
            'sync_timestamp' => time(),
            'sync_version' => '2.0'
        ];

        if ($updated || !empty($changes)) {
            if ($this->dryRun) {
                $changeStr = implode(', ', $changes);
                $this->log("  - [DRY RUN] Would update light: {$changeStr}");
                return true;
            } else {
                if ($existingDevice->save()) {
                    $changeStr = implode(', ', $changes);
                    $this->log("  ✓ Updated light device: {$changeStr}");
                    return true;
                } else {
                    $this->log("  ✗ Failed to update light device: " . implode(', ', $existingDevice->getFirstErrors()), 'error');
                    return false;
                }
            }
        } else {
            $this->log("  - Light device unchanged, skipping");
            return false;
        }
    }

    /**
     * Clean up devices that no longer exist in Home Assistant and empty area device groups
     * @param array $currentDevices Current devices from Home Assistant
     * @return array Cleanup statistics
     */
    private function cleanupOrphanedDevicesAndAreas($currentDevices)
    {
        $stats = [
            'removed_sensors' => 0,
            'removed_lights' => 0,
            'removed_areas' => 0
        ];

        $this->log("Starting cleanup of orphaned devices and empty areas");

        // Create a lookup of current HA device IDs
        $currentDeviceIds = [];
        foreach ($currentDevices as $device) {
            $currentDeviceIds[] = $device['id'];
        }

        // Find and remove orphaned sensors
        $orphanedSensors = HgDeviceSensor::find()
            ->where(['like', 'metadata', '"synced_from_ha":true'])
            ->andWhere(['not in', 'ha_device_id', $currentDeviceIds])
            ->all();

        foreach ($orphanedSensors as $sensor) {
            if ($this->dryRun) {
                $this->log("  - [DRY RUN] Would remove orphaned sensor: {$sensor->display_name}");
                $stats['removed_sensors']++;
            } else {
                $this->log("  ✓ Removing orphaned sensor: {$sensor->display_name}");
                if ($sensor->delete()) {
                    $stats['removed_sensors']++;
                }
            }
        }

        // Find and remove orphaned lights
        $orphanedLights = HgDeviceLight::find()
            ->where(['like', 'metadata', '"synced_from_ha":true'])
            ->andWhere(['not in', 'ha_device_id', $currentDeviceIds])
            ->all();

        foreach ($orphanedLights as $light) {
            if ($this->dryRun) {
                $this->log("  - [DRY RUN] Would remove orphaned light: {$light->display_name}");
                $stats['removed_lights']++;
            } else {
                $this->log("  ✓ Removing orphaned light: {$light->display_name}");
                if ($light->delete()) {
                    $stats['removed_lights']++;
                }
            }
        }

        // Find and remove empty area device groups
        $areaGroups = HgDeviceGroup::find()
            ->where(['like', 'metadata', '"synced_from_ha":true'])
            ->andWhere([
                'or',
                ['like', 'metadata', '"sync_type":"area"'],
                ['like', 'metadata', '"sync_type":"area_from_device"']
            ])
            ->all();

        $this->log("Found " . count($areaGroups) . " Home Assistant synced area groups to check");

        foreach ($areaGroups as $areaGroup) {
            $this->log("  - Checking area: {$areaGroup->display_name} (ID: {$areaGroup->id})");
            
            // Check if this area has any devices
            $hasLights = HgDeviceLight::find()
                ->where(['primary_hg_device_group_id' => $areaGroup->id])
                ->exists();

            $hasSensors = HgDeviceSensor::find()
                ->where(['hg_device_group_id' => $areaGroup->id])
                ->exists();

            $this->log("    Lights: " . ($hasLights ? 'found' : 'none') . ", Sensors: " . ($hasSensors ? 'found' : 'none'));

            if (!$hasLights && !$hasSensors) {
                if ($this->dryRun) {
                    $this->log("  - [DRY RUN] Would remove empty area: {$areaGroup->display_name}");
                    $stats['removed_areas']++;
                } else {
                    $this->log("  ✓ Removing empty area: {$areaGroup->display_name}");
                    if ($areaGroup->delete()) {
                        $stats['removed_areas']++;
                    }
                }
            }
        }

        if ($stats['removed_sensors'] > 0 || $stats['removed_lights'] > 0 || $stats['removed_areas'] > 0) {
            $this->log("Cleanup completed: {$stats['removed_sensors']} sensors, {$stats['removed_lights']} lights, {$stats['removed_areas']} areas removed");
        } else {
            $this->log("Cleanup completed: No orphaned devices or empty areas found");
        }

        return $stats;
    }

    /**
     * Find light entities in Home Assistant that don't have corresponding devices in the device registry
     * @param array $devices Current devices from device registry
     * @return array Array of unknown light entities
     */
    private function findUnknownLightEntities($devices)
    {
        $this->log("Checking for unknown light entities...");
        
        try {
            // Get entity registry to find all light entities
            $entities = $this->homeAssistant->getEntityRegistry();
            $this->log("Found " . count($entities) . " entities in registry");
            
            // Filter to light entities only
            $lightEntities = array_filter($entities, function($entity) {
                return strpos($entity['entity_id'], 'light.') === 0;
            });
            
            $this->log("Found " . count($lightEntities) . " light entities");
            
            // Create a lookup of known device IDs
            $knownDeviceIds = [];
            foreach ($devices as $device) {
                $knownDeviceIds[] = $device['id'];
            }
            
            // Find light entities without corresponding devices
            $unknownLightEntities = [];
            foreach ($lightEntities as $entity) {
                $deviceId = $entity['device_id'] ?? null;
                
                // Skip entities without device_id (these are typically service entities)
                if (!$deviceId) {
                    continue;
                }
                
                // Check if this device ID exists in our device registry
                if (!in_array($deviceId, $knownDeviceIds)) {
                    $unknownLightEntities[] = [
                        'entity_id' => $entity['entity_id'],
                        'device_id' => $deviceId,
                        'name' => $entity['name'] ?? null,
                        'platform' => $entity['platform'] ?? null,
                        'disabled_by' => $entity['disabled_by'] ?? null,
                        'area_id' => $entity['area_id'] ?? null,
                        'config_entry_id' => $entity['config_entry_id'] ?? null
                    ];
                }
            }
            
            if (!empty($unknownLightEntities)) {
                $this->log("Found " . count($unknownLightEntities) . " unknown light entities:");
                foreach ($unknownLightEntities as $entity) {
                    $this->log("  - {$entity['entity_id']} (Device: {$entity['device_id']}, Platform: {$entity['platform']})");
                }
            } else {
                $this->log("No unknown light entities found");
            }
            
            return $unknownLightEntities;
            
        } catch (\Exception $e) {
            $this->log("Failed to check for unknown light entities: " . $e->getMessage(), 'error');
            return [];
        }
    }


}