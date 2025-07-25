<?php

namespace app\controllers;

use app\components\HelperComponent;
use app\components\HueComponent;
use app\components\HueSyncComponent;
use app\jobs\ClearHubJob;
// use app\jobs\SyncDownJob; // REMOVED: No longer syncing from Hue hub
use app\models\HgDeviceSensor;
use app\models\HgHub;
use app\models\HgHubSearch;
use app\models\HgProductSensor;
use app\models\HomeGloButtonForm;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

use Yii;

/**
 * HgHubController implements the CRUD actions for HgHub model.
 */
class HgHubController extends HomeGloBaseController
{
    /**
     * Lists all HgHub models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgHubSearch();
        $params = ArrayHelper::merge($this->request->queryParams,['HgHubSearch'=>['hg_home_id'=>$this->home_record->id]]);
        $dataProvider = $searchModel->search($params);


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgHub model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $hub = $this->findModel($id);
        $array = [];
        $last_scan = [];
        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $objects = $hueApi->v1GetRequest('lights/new');
        $objects2 = $hueApi->v1GetRequest('sensors/new');
        $objects = ArrayHelper::merge($objects,$objects2);

        $last_scan[$hub['display_name']] = $objects['lastscan'];
        unset($objects['lastscan']);

        foreach ($objects as $object) {
            $array[] = $object;
        }

        $provider = new ArrayDataProvider([
            'allModels' => $array,
        ]);

        return $this->render('view', [
            'model' => $this->findModel($id),
            'provider'=>$provider,
            'last_scan'=>$last_scan
        ]);
    }

    /**
     * Creates a new HgHub model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgHub();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->hg_home_id = $this->home_record->id;
                $model->save();
                return $this->redirect(['/site/enter-home', 'id' => $this->home_record->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgHub model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgHub model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the HgHub model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgHub the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgHub::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionRefreshTokens($hub_id)
    {
        $hgHub = HgHub::findOne($hub_id);
        $hgHub->getHueComponent()->refreshToken($hgHub);

        Yii::$app->session->setFlash('success','Token refreshed!');
        return $this->redirect(Yii::$app->request->referrer);
    }

    public function actionProgramHub($id)
    {
        $form = new HomeGloButtonForm();
        $form->hg_hub_id = $id;
        $programRemote = $form->programRemoteHueHub();

        Yii::$app->session->setFlash('success',print_r($programRemote,true));
        return $this->redirect(['/hg-hub/index']);
    }

    public function actionDeleteLocalHubData($id)
    {
        $hubRecord = HgHub::findOne($id);
        $hueSyncComponent = new HueSyncComponent($hubRecord);
        $hueSyncComponent->clearLocalHubData();

        Yii::$app->session->setFlash('success',"cleared");
        return $this->redirect(Yii::$app->request->referrer);
    }


    public function actionScanDevices($id)
    {
        $hub = $this->findModel($id);
        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $scan = $hueApi->v1PostRequest('lights',(object) []);
        $scan = $hueApi->v1PostRequest('sensors',(object) []);

        Yii::$app->session->setFlash('success','Scanning for lights and switches...');
        return $this->redirect(Yii::$app->request->referrer);
    }

    /**
     * PUBLIC OAUTH-RECEIVE
     * @param $code
     * @param $state
     * @return \yii\web\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionOauthReceive($code,$state)
    {
        $tokens = HueComponent::getBearerAccessTokens($code);
        $updateDetails = [
            'Bearer Token'=>$tokens['access_token'],
            'Refresh Token'=>$tokens['refresh_token'],
            'Token Expires At'=>time() + $tokens['expires_in']
        ];

        $hubRecord = HgHub::findOne($state);
        $hubRecord->bearer_token = $tokens['access_token'];
        $hubRecord->refresh_token = $tokens['refresh_token'];
        $hubRecord->token_expires_at = $tokens['expires_in'] + time();

        $access_token = HueComponent::getHueApplicationKey($updateDetails['Bearer Token']);
        $hubRecord->access_token = $access_token;
        $hubRecord->save();

        if ($hubRecord->getJsonData('onboardFlow')) {
            return $this->redirect(['/onboard/hub-connected','hg_hub_id'=>$hubRecord->id]);
        }

        try { //set the hub's location for sunrise / sunset
            list ($lat ,$long) = HelperComponent::DECtoDMS($hubRecord->lat,$hubRecord->lng);
            $hubRecord->getHueComponent()->v1PutRequest('sensors/1/config',['lat'=>$lat,'long'=>$long,'sunriseoffset'=>0,'sunsetoffset'=>0]);
        } catch (\Throwable $t) {
            Yii::$app->session->setFlash('error',$hubRecord->display_name.' Unable to update geo!');
        }


        Yii::$app->session->setFlash('success',$hubRecord->display_name.' Hub Linked!');
        return $this->redirect(['/hg-hub']);
    }

    public function actionClearRules($id,$clear_scenes=false)
    {
        Yii::$app->queue->push(new ClearHubJob(
            [
                'hg_hub_id'=>$id,
                'clear_scenes'=>$clear_scenes
            ]
        ));

        Yii::$app->session->setFlash('success','Clearing hub...');
        return $this->redirect(Yii::$app->request->referrer);

    }

    public function actionSyncDownByHub($hg_hub_id)
    {
        // TODO: Update for Home Assistant - sync down functionality no longer needed
        // Previous logic synced specific Hue hub state down to local database - replace with HA integration if needed
        // SyncDownJob removed - no longer syncing from Hue hub

        Yii::$app->session->setFlash('info','Sync functionality temporarily disabled during Home Assistant migration');
        return $this->redirect(Yii::$app->request->referrer);
    }
}
