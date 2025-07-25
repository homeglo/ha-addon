<?php

namespace app\controllers;

use app\components\HelperComponent;
use app\components\HgEngineComponent;
use app\models\HgDeviceSensor;
use app\models\HgDeviceSensorDeviceGroupMultiroom;
use app\models\HgDeviceSensorSearch;
use app\models\HgGlozone;
use app\models\HgProductSensor;
use app\models\InitMotionForm;
use app\models\InitSwitchForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgDeviceSensorController implements the CRUD actions for HgDeviceSensor model.
 */
class HgDeviceSensorController extends GlozoneBaseController
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
     * Lists all HgDeviceSensor models.
     *
     * @return string
     */
    public function actionSwitch()
    {
        if ($this->hg_glozone_id) {
            $glozone_id = $this->hg_glozone_id;
        } else {
            $glozone_id = HgGlozone::HG_DEFAULT_GLOZONE;
        }

        $searchModel = new HgDeviceSensorSearch();
        $searchModel->hg_glozone_id = $glozone_id;
        $searchModel->product_type_name = HgProductSensor::TYPE_NAME_HUE_SWITCH;
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination->pageSize = 1000;

        $initSwitchForm = new \app\models\InitSwitchForm();
        $initSwitchForm->hg_glozone_id = $glozone_id;

        /* @var HgDeviceSensor $model */
        $missing = false;
        foreach ($dataProvider->getModels() as $model) {
            if (in_array($model->hgProductSensor->type_name,[
                HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR,
                HgProductSensor::TYPE_NAME_HUE_SWITCH])) {
                if (!$model->hg_device_group_id) {
                    $missing = true;
                }
            }

        }

        return $this->render('switch', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'initSwitchForm'=>$initSwitchForm,
            'missing'=>$missing
        ]);
    }


    /**
     * Lists all HgDeviceSensor models.
     *
     * @return string
     */
    public function actionMotion()
    {
        if ($this->hg_glozone_id) {
            $glozone_id = $this->hg_glozone_id;
        } else {
            $glozone_id = HgGlozone::HG_DEFAULT_GLOZONE;
        }

        $searchModel = new HgDeviceSensorSearch();
        $searchModel->hg_glozone_id = $glozone_id;
        $searchModel->product_type_name = HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR;

        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination->pageSize = 1000;

        $initMotionForm = new \app\models\InitMotionForm();
        $initMotionForm->hg_hub_ids = $this->home_hub_ids;
        $initMotionForm->hg_glozone_id = $glozone_id;

        /* @var HgDeviceSensor $model */
        //this detects if a motion is missing a room assignment
        $missing = false;
        foreach ($dataProvider->getModels() as $model) {
            if (in_array($model->hgProductSensor->type_name,[
                HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR])) {
                if (!$model->hg_device_group_id) {
                    $missing = true;
                }
            }

        }

        return $this->render('motion', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'searchModelAux'=>$searchModelAux,
            'dataProviderAux'=>$dataProviderAux,
            'initMotionForm'=>$initMotionForm,
            'missing'=>$missing
        ]);
    }

    /**
     * Displays a single HgDeviceSensor model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $searchModelAux = new HgDeviceSensorSearch();
        $searchModelAux->id = [$model->ambientHgDeviceSensorOne->id];

        $dataProviderAux = $searchModelAux->search([]);

        return $this->render('view', [
            'model' => $model,
            'searchModelAux'=>$searchModelAux,
            'dataProviderAux'=>$dataProviderAux
        ]);
    }

    /**
     * Creates a new HgDeviceSensor model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgDeviceSensor();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgDeviceSensor model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        /* @var $model HgDeviceSensor */
        $model = $this->findModel($id);
        $model->multiroomSet();

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            HgDeviceSensorDeviceGroupMultiroom::deleteAll(['hg_device_sensor_id'=>$model->id]);
            foreach ($model->hg_device_group_multiroom_ids as $hg_device_group_id) {
                $hgDeviceSensorDeviceGroupMultiroom = new HgDeviceSensorDeviceGroupMultiroom();
                $hgDeviceSensorDeviceGroupMultiroom->hg_device_sensor_id = $model->id;
                $hgDeviceSensorDeviceGroupMultiroom->hg_device_group_id = $hg_device_group_id;
                $hgDeviceSensorDeviceGroupMultiroom->save();
            }
            return $this->redirect([$model->hgProductSensor->isMotion ? 'motion' : 'switch', 'hg_glozone_id' => $model->hgDeviceGroup->hg_glozone_id]);
        }

        return $this->render('update', [
            'model' => $model
        ]);
    }

    public function actionInitSwitches()
    {
        $initSwitchForm = new InitSwitchForm();
        if ($initSwitchForm->load(\Yii::$app->request->post()) && $initSwitchForm->validate()) {
            $initSwitchForm->performInit();

            \Yii::$app->session->setFlash('success','Switch Initialized!');
            return $this->redirect($this->request->referrer);
        } else {
            \Yii::$app->session->setFlash('error',HelperComponent::getFirstErrorFromFailedValidation($initSwitchForm));
            return $this->redirect($this->request->referrer);
        }
    }

    public function actionInitMotion()
    {
        $initMotionForm = new InitMotionForm();
        if ($initMotionForm->load(\Yii::$app->request->post()) && $initMotionForm->validate()) {
            $initMotionForm->performInit();

            \Yii::$app->session->setFlash('success','Motion Initialized!');
            return $this->redirect($this->request->referrer);
        } else {
            \Yii::$app->session->setFlash('error',HelperComponent::getFirstErrorFromFailedValidation($initMotionForm));
            return $this->redirect($this->request->referrer);
        }
    }

    /**
     * Deletes an existing HgDeviceSensor model.
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
     * Finds the HgDeviceSensor model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgDeviceSensor the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgDeviceSensor::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
