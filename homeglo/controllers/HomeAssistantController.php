<?php

namespace app\controllers;

use app\services\HomeAssistantSyncService;
use app\models\HgHome;
use app\models\HgHub;
use app\models\HgDeviceGroup;
use app\models\HgDeviceLight;
use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;

/**
 * Home Assistant Controller
 * 
 * Provides web-based endpoints for Home Assistant synchronization
 */
class HomeAssistantController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'sync-devices' => ['POST'],
                    'sync-location' => ['POST'],
                    'sync-all' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Test Home Assistant connection
     * @return Response
     */
    public function actionTestConnection()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        // Create a fresh component to test configuration
        $ha = new \app\components\HomeAssistantComponent();
        
        // Debug environment - more comprehensive
        $debugInfo = [
            'environment' => [
                'supervisor_token' => !empty(getenv('SUPERVISOR_TOKEN')),
                'ha_token_env' => !empty(getenv('HA_TOKEN')),
                'ha_access_token_env' => !empty(getenv('HA_ACCESS_TOKEN')),
                'ha_websocket_url' => getenv('HA_WEBSOCKET_URL') ?: 'not set',
                'ha_rest_url' => getenv('HA_REST_URL') ?: 'not set',
            ],
            'files' => [
                'addon_config' => file_exists('/data/ha-config.php'),
                'standalone_config' => file_exists(Yii::getAlias('@app/config/ha-config.php')),
                'env_file' => file_exists('/app/homeglo/.env')
            ],
            'component' => [
                'url' => $ha->homeAssistantUrl,
                'token_present' => !empty($ha->accessToken),
                'token_length' => strlen($ha->accessToken ?? '')
            ],
            'mode' => \app\helpers\IngressHelper::getDisplayMode()
        ];
        
        error_log("HA Connection Debug: " . json_encode($debugInfo, JSON_PRETTY_PRINT));
        
        try {
            $service = $this->createSyncService();
            $connected = $service->testConnection();
            
            return [
                'success' => $connected,
                'message' => $connected ? 'Connection successful' : 'Connection failed',
                'debug' => $debugInfo
            ];
            
        } catch (\Exception $e) {
            $errorMsg = is_string($e->getMessage()) ? $e->getMessage() : json_encode($e->getMessage());
            Yii::error("HA Connection Test Error: " . $errorMsg, __METHOD__);
            
            // Add error details to debug info
            $debugInfo['error'] = [
                'message' => $errorMsg,
                'class' => get_class($e),
                'file' => basename($e->getFile()),
                'line' => $e->getLine()
            ];
            
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $errorMsg,
                'debug' => $debugInfo
            ];
        }
    }

    /**
     * Sync devices from Home Assistant
     * @return Response
     */
    public function actionSyncDevices()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $deviceTypes = Yii::$app->request->post('device_types', []);
        $dryRun = Yii::$app->request->post('dry_run', false);
        
        try {
            $service = $this->createSyncService($dryRun);
            $stats = $service->syncDevices($deviceTypes);
            
            return [
                'success' => true,
                'message' => 'Device sync completed successfully',
                'data' => $stats
            ];
            
        } catch (\Exception $e) {
            $errorMsg = is_string($e->getMessage()) ? $e->getMessage() : json_encode($e->getMessage());
            Yii::error("HA Device Sync Error: " . $errorMsg, __METHOD__);
            return [
                'success' => false,
                'message' => 'Device sync failed: ' . $errorMsg
            ];
        }
    }


    /**
     * Sync location data from Home Assistant
     * @return Response
     */
    public function actionSyncLocation()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $dryRun = Yii::$app->request->post('dry_run', false);
        
        try {
            $service = $this->createSyncService($dryRun);
            $result = $service->syncLocationData();
            
            return [
                'success' => true,
                'message' => 'Location sync completed successfully',
                'data' => $result
            ];
            
        } catch (\Exception $e) {
            $errorMsg = is_string($e->getMessage()) ? $e->getMessage() : json_encode($e->getMessage());
            Yii::error("HA Location Sync Error: " . $errorMsg, __METHOD__);
            return [
                'success' => false,
                'message' => 'Location sync failed: ' . $errorMsg
            ];
        }
    }

    /**
     * Perform complete sync of both devices and location
     * @return Response
     */
    public function actionSyncAll()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $deviceTypes = Yii::$app->request->post('device_types', []);
        $dryRun = Yii::$app->request->post('dry_run', false);
        
        try {
            $service = $this->createSyncService($dryRun);
            $results = $service->syncAll($deviceTypes);
            
            return [
                'success' => $results['success'],
                'message' => $results['success'] ? 'Complete sync successful' : 'Sync completed with errors',
                'data' => $results
            ];
            
        } catch (\Exception $e) {
            $errorMsg = is_string($e->getMessage()) ? $e->getMessage() : json_encode($e->getMessage());
            Yii::error("HA Complete Sync Error: " . $errorMsg, __METHOD__);
            return [
                'success' => false,
                'message' => 'Complete sync failed: ' . $errorMsg
            ];
        }
    }

    /**
     * Get sync status and statistics
     * @return Response
     */
    public function actionStatus()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            // Get basic statistics about synced data
            $sensorCount = \app\models\HgDeviceSensor::find()
                ->where(['like', 'metadata', '"synced_from_ha":true'])
                ->count();
                
            $lightCount = \app\models\HgDeviceLight::find()
                ->where(['like', 'metadata', '"synced_from_ha":true'])
                ->count();
                
            $areaCount = \app\models\HgDeviceGroup::find()
                ->where(['like', 'metadata', '"synced_from_ha":true'])
                ->andWhere([
                    'or',
                    ['like', 'metadata', '"sync_type":"area"'],
                    ['like', 'metadata', '"sync_type":"area_from_device"']
                ])
                ->count();
                
            $homeAssistantHome = \app\models\HgHome::findOne(HomeAssistantSyncService::HOMEASSISTANT_HOME_ID);
            
            return [
                'success' => true,
                'data' => [
                    'synced_sensors' => (int)$sensorCount,
                    'synced_lights' => (int)$lightCount,
                    'synced_devices' => (int)($sensorCount + $lightCount), // Total for backward compatibility
                    'synced_areas' => (int)$areaCount,
                    'home_assistant_home' => $homeAssistantHome ? [
                        'id' => $homeAssistantHome->id,
                        'name' => $homeAssistantHome->display_name,
                        'latitude' => $homeAssistantHome->lat,
                        'longitude' => $homeAssistantHome->lng,
                        'last_updated' => $homeAssistantHome->updated_at
                    ] : null
                ]
            ];
            
        } catch (\Exception $e) {
            $errorMsg = is_string($e->getMessage()) ? $e->getMessage() : json_encode($e->getMessage());
            Yii::error("HA Status Error: " . $errorMsg, __METHOD__);
            return [
                'success' => false,
                'message' => 'Failed to get sync status: ' . $errorMsg
            ];
        }
    }

    /**
     * Debug endpoint to check sync status
     * @return Response
     */
    public function actionDebug()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        try {
            // Check home 2 setup
            $home2 = HgHome::findOne(2);
            if (!$home2) {
                return [
                    'error' => 'Home ID 2 not found',
                    'success' => false
                ];
            }
            
            // Get all device groups for home 2
            $deviceGroups = HgDeviceGroup::find()
                ->where(['hg_home_id' => 2])
                ->all();
            
            // Get all lights
            $allLights = HgDeviceLight::find()->all();
            $home2Lights = [];
            $otherLights = [];
            
            foreach ($allLights as $light) {
                $lightData = [
                    'id' => $light->id,
                    'display_name' => $light->display_name,
                    'ha_device_id' => $light->ha_device_id,
                    'group_id' => $light->primary_hg_device_group_id,
                    'metadata' => $light->metadata
                ];
                
                // Check if light belongs to home 2
                $belongsToHome2 = false;
                if ($light->primary_hg_device_group_id) {
                    foreach ($deviceGroups as $group) {
                        if ($group->id == $light->primary_hg_device_group_id) {
                            $belongsToHome2 = true;
                            $lightData['group_name'] = $group->display_name;
                            break;
                        }
                    }
                }
                
                if ($belongsToHome2) {
                    $home2Lights[] = $lightData;
                } else {
                    $otherLights[] = $lightData;
                }
            }
            
            return [
                'success' => true,
                'home2' => [
                    'id' => $home2->id,
                    'name' => $home2->display_name,
                    'device_groups_count' => count($deviceGroups),
                    'device_groups' => array_map(function($g) {
                        return [
                            'id' => $g->id,
                            'name' => $g->display_name,
                            'home_id' => $g->hg_home_id
                        ];
                    }, $deviceGroups)
                ],
                'lights' => [
                    'total' => count($allLights),
                    'in_home2' => count($home2Lights),
                    'in_other_homes' => count($otherLights),
                    'home2_lights' => $home2Lights,
                    'other_lights' => array_slice($otherLights, 0, 5) // Just first 5 for brevity
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Create and configure the sync service
     * @param bool $dryRun
     * @return HomeAssistantSyncService
     */
    private function createSyncService($dryRun = false)
    {
        return new HomeAssistantSyncService([
            'dryRun' => $dryRun,
            'logger' => function($message, $level = 'info') {
                // Ensure message is a string to avoid array to string conversion errors
                if (is_array($message) || is_object($message)) {
                    $message = json_encode($message, JSON_PRETTY_PRINT);
                } else {
                    $message = (string)$message;
                }
                
                if ($level === 'error') {
                    Yii::error($message, __METHOD__);
                } else {
                    Yii::info($message, __METHOD__);
                }
            }
        ]);
    }
}