<?php

namespace app\controllers;

use app\models\HgDeviceSensor;
use app\models\HgDeviceSensorVariable;
use app\models\HgDeviceSensorVariableSearch;
use app\models\HgStatus;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgDeviceSensorVariableController implements the CRUD actions for HgDeviceSensorVariable model.
 */
class HgDeviceSensorVariableController extends Controller
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),[
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all HgDeviceSensorVariable models.
     *
     * @return string
     */
    public function actionIndex($hg_device_sensor_id=null)
    {
        $hgDeviceSensor = HgDeviceSensor::findOne($hg_device_sensor_id);
        if ($hg_device_sensor_id) {
            $filter = ['HgDeviceSensorVariableSearch'=>['hg_device_sensor_id'=>$hg_device_sensor_id]];
        } else {
            $filter = ['HgDeviceSensorVariableSearch'=>['hg_status_id'=>HgStatus::HG_DEFAULT_SENSOR_VARIABLE]];
        }

        $searchModel = new HgDeviceSensorVariableSearch();
        $dataProvider = $searchModel->search(ArrayHelper::merge(
            $filter,$this->request->queryParams));

        return $this->render('index', [
            'hgDeviceSensor'=>$hgDeviceSensor,
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgDeviceSensorVariable model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new HgDeviceSensorVariable model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($hg_device_sensor_id=null)
    {
        $model = new HgDeviceSensorVariable();
        $model->hg_device_sensor_id = $hg_device_sensor_id;
        $model->hg_status_id = $hg_device_sensor_id ? HgStatus::HG_USER_SENSOR_VARIABLE : HgStatus::HG_DEFAULT_SENSOR_VARIABLE;

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {
                return $this->redirect(['index','hg_device_sensor_id'=>$hg_device_sensor_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgDeviceSensorVariable model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['index', 'hg_device_sensor_id' => $model->hg_device_sensor_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgDeviceSensorVariable model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(\Yii::$app->request->referrer);
    }

    /**
     * Finds the HgDeviceSensorVariable model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgDeviceSensorVariable the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgDeviceSensorVariable::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
