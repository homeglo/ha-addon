<?php

namespace app\commands;

use app\services\HomeAssistantSyncService;
use yii\console\Controller;
use yii\console\ExitCode;
use Yii;

/**
 * Home Assistant sync controller using the generic HomeAssistantComponent
 */
class HomeAssistantSyncV2Controller extends Controller
{
    public $homeAssistantUrl;
    public $accessToken = '';
    public $dryRun = false;

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
        return array_merge(parent::options($actionID), ['homeAssistantUrl', 'accessToken', 'dryRun']);
    }

    public function optionAliases()
    {
        return array_merge(parent::optionAliases(), [
            'url' => 'homeAssistantUrl',
            'token' => 'accessToken',
            'dry' => 'dryRun'
        ]);
    }

    /**
     * Sync devices from Home Assistant device registry to hg_device_sensor
     * @return int Exit code
     */
    public function actionSync()
    {
        $syncService = $this->createSyncService();

        try {
            $stats = $syncService->syncDevices();
            $this->displaySyncSummary($stats);
            return ExitCode::OK;

        } catch (\Exception $e) {
            $this->stdout("✗ Sync failed: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Sync specific device types only
     * @param string $deviceTypes Comma-separated list of device types to sync
     * @return int Exit code
     */
    public function actionSyncTypes($deviceTypes)
    {
        $types = array_map('trim', explode(',', $deviceTypes));
        $syncService = $this->createSyncService();

        try {
            $stats = $syncService->syncDevices($types);
            $this->displaySyncSummary($stats);
            return ExitCode::OK;

        } catch (\Exception $e) {
            $this->stdout("✗ Sync failed: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Show devices that would be synced without actually syncing
     * @return int Exit code
     */
    public function actionPreview()
    {
        $this->dryRun = true;
        return $this->actionSync();
    }

    /**
     * Sync location data (lat/lng) from Home Assistant to hg_home
     * @return int Exit code
     */
    public function actionSyncLocation()
    {
        $syncService = $this->createSyncService();

        try {
            $result = $syncService->syncLocationData();
            $this->displayLocationSyncResult($result);
            return ExitCode::OK;

        } catch (\Exception $e) {
            $this->stdout("✗ Location sync failed: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }


    /**
     * Perform complete sync of both devices and location data
     * @param string $deviceTypes Optional comma-separated list of device types to sync
     * @return int Exit code
     */
    public function actionSyncAll($deviceTypes = '')
    {
        $types = !empty($deviceTypes) ? array_map('trim', explode(',', $deviceTypes)) : [];
        $syncService = $this->createSyncService();

        try {
            $results = $syncService->syncAll($types);
            
            if ($results['location']) {
                $this->stdout("\n=== LOCATION SYNC RESULTS ===\n");
                $this->displayLocationSyncResult($results['location']);
            }


            if ($results['devices']) {
                $this->stdout("\n=== DEVICE SYNC RESULTS ===\n");
                $this->displaySyncSummary($results['devices']);
            }

            if (!empty($results['errors'])) {
                $this->stdout("\n=== ERRORS ===\n");
                foreach ($results['errors'] as $error) {
                    $this->stdout("✗ $error\n");
                }
            }

            $this->stdout("\n=== OVERALL RESULT ===\n");
            if ($results['success']) {
                $this->stdout("✓ Complete sync successful\n");
                return ExitCode::OK;
            } else {
                $this->stdout("✗ Sync completed with errors\n");
                return ExitCode::UNSPECIFIED_ERROR;
            }

        } catch (\Exception $e) {
            $this->stdout("✗ Complete sync failed: " . $e->getMessage() . "\n");
            return ExitCode::UNSPECIFIED_ERROR;
        }
    }

    /**
     * Display sync summary
     * @param array $stats
     */
    private function displaySyncSummary($stats)
    {
        $this->stdout("Total devices found: {$stats['total_devices']}\n");
        
        // Show sensor stats if present
        if (isset($stats['matched_sensors'])) {
            $this->stdout("Sensors matched: {$stats['matched_sensors']}\n");
            $this->stdout("Sensors created: {$stats['created_sensors']}\n");
            if (isset($stats['updated_sensors']) && $stats['updated_sensors'] > 0) {
                $this->stdout("Sensors updated: {$stats['updated_sensors']}\n");
            }
            if (isset($stats['removed_sensors']) && $stats['removed_sensors'] > 0) {
                $this->stdout("Sensors removed: {$stats['removed_sensors']}\n");
            }
        }
        
        // Show light stats if present  
        if (isset($stats['matched_lights'])) {
            $this->stdout("Lights matched: {$stats['matched_lights']}\n");
            $this->stdout("Lights created: {$stats['created_lights']}\n");
            if (isset($stats['updated_lights']) && $stats['updated_lights'] > 0) {
                $this->stdout("Lights updated: {$stats['updated_lights']}\n");
            }
            if (isset($stats['removed_lights']) && $stats['removed_lights'] > 0) {
                $this->stdout("Lights removed: {$stats['removed_lights']}\n");
            }
        }
        
        // Show area cleanup stats
        if (isset($stats['removed_areas']) && $stats['removed_areas'] > 0) {
            $this->stdout("Empty areas removed: {$stats['removed_areas']}\n");
        }
        
        // Show legacy stats for backward compatibility
        if (isset($stats['matched']) && !isset($stats['matched_sensors'])) {
            $this->stdout("Devices matched: {$stats['matched']}\n");
            $this->stdout("Devices created: {$stats['created']}\n");
        }
        
        $this->stdout("Devices skipped (unchanged): {$stats['skipped']}\n");
        if ($stats['errors'] > 0) {
            $this->stdout("Errors: {$stats['errors']}\n");
        }
        
        // Show unknown light entities if any
        if (isset($stats['unknown_light_entities']) && !empty($stats['unknown_light_entities'])) {
            $this->stdout("\n=== UNKNOWN LIGHT ENTITIES ===\n");
            $this->stdout("Found " . count($stats['unknown_light_entities']) . " light entities without matching devices:\n");
            foreach ($stats['unknown_light_entities'] as $entity) {
                $platform = $entity['platform'] ? " ({$entity['platform']})" : "";
                $area = $entity['area_id'] ? " [Area: {$entity['area_id']}]" : "";
                $disabled = $entity['disabled_by'] ? " [DISABLED]" : "";
                $this->stdout("  - {$entity['entity_id']}{$platform}{$area}{$disabled}\n");
            }
            $this->stdout("These entities may need corresponding product definitions in hg_product_light.\n");
        }
    }

    /**
     * Display location sync result
     * @param array $result
     */
    private function displayLocationSyncResult($result)
    {
        switch ($result['action']) {
            case 'create':
                if (isset($result['dry_run'])) {
                    $this->stdout("[DRY RUN] Would create Home Assistant home with location: lat={$result['latitude']}, lng={$result['longitude']}\n");
                } else {
                    $this->stdout("✓ Created Home Assistant home with location: lat={$result['latitude']}, lng={$result['longitude']}\n");
                }
                break;
                
            case 'update':
                if (isset($result['dry_run'])) {
                    $this->stdout("[DRY RUN] Would update location: lat={$result['old_latitude']} -> {$result['new_latitude']}, lng={$result['old_longitude']} -> {$result['new_longitude']}\n");
                } else {
                    $this->stdout("✓ Updated location: lat={$result['old_latitude']} -> {$result['new_latitude']}, lng={$result['old_longitude']} -> {$result['new_longitude']}\n");
                }
                break;
                
            case 'no_change':
                $this->stdout("✓ Location data unchanged: lat={$result['latitude']}, lng={$result['longitude']}\n");
                break;
        }
    }


    /**
     * Create and configure the sync service
     * @return HomeAssistantSyncService
     */
    private function createSyncService()
    {
        $service = new HomeAssistantSyncService([
            'dryRun' => $this->dryRun,
            'logger' => function($message, $level = 'info') {
                if ($level === 'error') {
                    $this->stderr("✗ $message\n");
                } else {
                    $this->stdout("$message\n");
                }
            }
        ]);
        
        // Set custom configuration if provided via command line
        $service->setHomeAssistantConfig($this->homeAssistantUrl, $this->accessToken);
        
        return $service;
    }
}