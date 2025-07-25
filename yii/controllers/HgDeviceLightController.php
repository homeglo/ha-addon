<?php

namespace app\controllers;

use app\components\HueComponent;
use app\components\HueSyncComponent;
use app\models\CloneBulbForm;
use app\models\CloneSwitchRulesForm;
use app\models\HgDeviceGroup;
use app\models\HgDeviceLight;
use app\models\HgDeviceLightSearch;
use app\models\HgHub;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgDeviceLightController implements the CRUD actions for HgDeviceLight model.
 */
class HgDeviceLightController extends HomeGloBaseController
{
    /**
     * @inheritDoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(
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
     * Lists all HgDeviceLight models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgDeviceLightSearch();
        $dataProvider = $searchModel->search($this->request->queryParams,$this->home_hub_ids);
        $dataProvider->pagination->pageSize = 1000;

        $available_rooms = [''=>''];
        foreach (HgDeviceGroup::find()->where(['hg_hub_id'=>$this->home_hub_ids])->all() as $m) {
            $available_rooms[$m->id] = $m->display_name;
        }

        $available_hubs = [''=>''];
        foreach (HgHub::find()->where(['id'=>$this->home_hub_ids])->all() as $m) {
            $available_hubs[$m->id] = $m->display_name;
        }


        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
            'available_rooms'=>$available_rooms,
            'available_hubs'=>$available_hubs
        ]);
    }

    /**
     * Displays a single HgDeviceLight model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $cloneBulbForm = new CloneBulbForm();
        $model = $this->findModel($id);

        if ($cloneBulbForm->load(\Yii::$app->request->post())) {
            $cloneBulbForm->destination_hg_device_light_id = $id;
            $cloneBulbForm->performClone();
            \Yii::$app->session->setFlash('success','Light Bulb Cloned!');

            return $this->redirect($this->request->referrer);
        }

        return $this->render('view', [
            'model' => $model,
            'cloneBulbForm'=>$cloneBulbForm
        ]);
    }

    /**
     * Creates a new HgDeviceLight model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgDeviceLight();

        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->hg_hub_id = HgDeviceGroup::findOne($model->primary_hg_device_group_id)->hg_hub_id;
                if ($model->save()) {
                    return $this->redirect(['/hg-device-light', 'id' => $model->id]);
                }

            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgDeviceLight model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost) {
            $post = $this->request->post();
            if ($post['HgDeviceLight']['primary_hg_device_group_id'] == $model->primary_hg_device_group_id) {
                unset($post['HgDeviceLight']['primary_hg_device_group_id']);
            }


            if ($model->load($post)) {
                $model->updateHueHub = true;
                if ($model->save())
                    return $this->redirect(['index', 'id' => $model->id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgDeviceLight model.
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

    public function actionTestLight($hg_device_light_id)
    {
        $hgDeviceLight = $this->findModel($hg_device_light_id);
        $hgDeviceLight->flashLight();
        \Yii::$app->session->setFlash('success','Test');
        return $this->redirect(\Yii::$app->request->referrer);
    }

    public function actionSyncLights()
    {
        foreach (HgHub::find()->where(['hg_home_id'=>$this->home_record->id])->all() as $hgHub) {
            $hueSyncComponent = new HueSyncComponent($hgHub);
            $lights = $hueSyncComponent->importLights();
        }

        \Yii::$app->session->setFlash('success',VarDumper::export(count($lights)));
        return $this->redirect(\Yii::$app->request->referrer);
    }

    /**
     * Finds the HgDeviceLight model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgDeviceLight the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgDeviceLight::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
