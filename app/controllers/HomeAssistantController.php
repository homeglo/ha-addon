<?php

namespace app\controllers;

use app\services\HomeAssistantSyncService;
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
        
        try {
            $service = $this->createSyncService();
            $connected = $service->testConnection();
            
            return [
                'success' => $connected,
                'message' => $connected ? 'Connection successful' : 'Connection failed'
            ];
            
        } catch (\Exception $e) {
            Yii::error("HA Connection Test Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
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
            Yii::error("HA Device Sync Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Device sync failed: ' . $e->getMessage()
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
            Yii::error("HA Location Sync Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Location sync failed: ' . $e->getMessage()
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
            Yii::error("HA Complete Sync Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Complete sync failed: ' . $e->getMessage()
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
            Yii::error("HA Status Error: " . $e->getMessage(), __METHOD__);
            return [
                'success' => false,
                'message' => 'Failed to get sync status: ' . $e->getMessage()
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
                if ($level === 'error') {
                    Yii::error($message, __METHOD__);
                } else {
                    Yii::info($message, __METHOD__);
                }
            }
        ]);
    }
}