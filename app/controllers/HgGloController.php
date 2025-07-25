<?php

namespace app\controllers;

use app\components\HgEngineComponent;
use app\components\HueComponent;
use app\components\HomeAssistantComponent;
use app\models\HgDeviceLight;
use app\models\HgGlo;
use app\models\HgGloDeviceLight;
use app\models\HgGloSearch;
use app\models\HgGlozone;
use app\models\HgVersion;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgGloController implements the CRUD actions for HgGlo model.
 */
class HgGloController extends GlozoneBaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return array_merge(
            parent::behaviors(),
            [
                'verbs' => [
                    'class' => VerbFilter::className(),
                    'actions' => [
                        'delete' => ['POST'],
                    ],
                ],
            ]
        );
    }

    /**
     * Lists all HgGlo models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgGloSearch();
        if ($this->hg_glozone_id) {
            $glozone_id = $this->hg_glozone_id;
        } else {
            $glozone_id = HgGlozone::HG_DEFAULT_GLOZONE;
        }
        $searchModel->hg_glozone_id = $glozone_id;
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgGlo model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $hgGloDeviceLightQuery = HgGloDeviceLight::find()->where([
            'hg_hub_id'=>$model->hg_hub_id,
            'hg_glo_id'=>$id,
        ]);

        $hgGloDeviceLightDataProvider = new ActiveDataProvider([
            'query' => $hgGloDeviceLightQuery,
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);

        return $this->render('view', [
            'model' => $model,
            'hgGloDeviceLightDataProvider'=>$hgGloDeviceLightDataProvider
        ]);
    }

    /**
     * Creates a new HgGlo model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($hg_glozone_id)
    {
        $model = new HgGlo();
        $model->write_to_hue = 1;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->validate()) {
                $model->hg_glozone_id = $hg_glozone_id;
                $model->hg_hub_id = null;
                $model->hg_version_id = HgVersion::HG_VERSION_2_0_ENGINE;
                if ($model->save())
                    return $this->redirect(['/hg-glo/index', 'hg_glozone_id' => $model->hg_glozone_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgGlo model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = $this->request->post();

        if ($this->request->isPost) {
            foreach ($post['HgGlo'] as $key => $value) {
                if ($model->{$key} == $value) {
                    unset($post['HgGlo'][$key]);
                }
            }
            if ($model->load($post) && $model->validate()) {
                if ($model->save())
                    return $this->redirect(['/hg-glo/index', 'hg_glozone_id' => $model->hg_glozone_id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgGlo model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $hg_glozone_id = $model->hg_glozone_id;
        $model->delete();

        return $this->redirect(['/hg-glo/index', 'hg_glozone_id' => $hg_glozone_id]);
    }

    /**
     * Finds the HgGlo model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgGlo the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgGlo::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionWholeHomeTestGlo($hg_glo_id)
    {
        $hgGlo = $this->findModel($hg_glo_id);
        
        try {
            // Initialize Home Assistant component
            $ha = new HomeAssistantComponent();
            
            // Get entity registry once to avoid multiple API calls
            $entityRegistry = null;
            try {
                $entityRegistry = $ha->getEntityRegistry();
            } catch (\Exception $e) {
                \Yii::warning("Could not get entity registry, will use fallback naming: " . $e->getMessage(), __METHOD__);
            }
            
            // Get all lights in the glozone that are synced from Home Assistant
            $lightEntities = [];
            foreach ($hgGlo->hgGlozone->hgDeviceGroups as $hgDeviceGroup) {
                // Get lights in this device group that have HA device IDs
                $lights = HgDeviceLight::find()
                    ->where(['primary_hg_device_group_id' => $hgDeviceGroup->id])
                    ->andWhere(['not', ['ha_device_id' => null]])
                    ->all();
                
                foreach ($lights as $light) {
                    // Use the unified method from HomeAssistantComponent
                    $entityIds = $ha->getDeviceLightEntities($light->ha_device_id, $entityRegistry);
                    if (!empty($entityIds)) {
                        $lightEntities = array_merge($lightEntities, $entityIds);
                    }
                }
            }
            
            // Remove duplicates
            $lightEntities = array_unique($lightEntities);
            
            if (empty($lightEntities)) {
                \Yii::$app->session->setFlash('warning', 'No Home Assistant lights found in this glozone.');
                return $this->redirect(\Yii::$app->request->referrer);
            }
            
            // Use the unified light control method with elegant transitions
            $response = $ha->turnOnLightsWithGlo($lightEntities, $hgGlo);
            
            $lightCount = count($lightEntities);
            \Yii::$app->session->setFlash('success', "Test sent to {$lightCount} Home Assistant lights with elegant {$ha->defaultTransitionTime}s transition!");
            
        } catch (\Exception $e) {
            \Yii::error("Home Assistant test glo failed: " . $e->getMessage(), __METHOD__);
            \Yii::$app->session->setFlash('error', 'Failed to send test to Home Assistant: ' . $e->getMessage());
        }
        
        return $this->redirect(\Yii::$app->request->referrer);
    }
}
