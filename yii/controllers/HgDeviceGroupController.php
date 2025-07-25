<?php

namespace app\controllers;

use app\components\HgEngineComponent;
use app\components\HueSyncComponent;
use app\jobs\InitGloJob;
use app\models\HgDeviceGroup;
use app\models\HgDeviceGroupSearch;
use app\models\HgGlo;
use app\models\HgGloDeviceGroup;
use app\models\HgGloDeviceLight;
use app\models\HgGlozone;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgDeviceGroupController implements the CRUD actions for HgDeviceGroup model.
 */
class HgDeviceGroupController extends GlozoneBaseController
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
     * Lists all HgDeviceGroup models.
     *
     * @return string
     */
    public function actionIndex()
    {
        if ($this->hg_glozone_id) {
            $glozone_id = $this->hg_glozone_id;
        } else {
            $glozone_id = HgGlozone::HG_DEFAULT_GLOZONE;
        }
        $searchModel = new HgDeviceGroupSearch();
        $searchModel->hg_glozone_id = $glozone_id;
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    public function actionInitGlo($id,$state)
    {
        $hg_glozone_id = HgDeviceGroup::findOne($id)->hg_glozone_id;

        $job = new InitGloJob(
            [
                'hg_glozone_id'=>$hg_glozone_id,
                'hg_device_group_id'=>$id,
                'state'=>$state
            ]
        );
        \Yii::$app->queue->push($job);

        \Yii::$app->session->setFlash('success','Re-gloing...');
        return $this->redirect(\Yii::$app->request->referrer);
    }

    /**
     * Displays a single HgDeviceGroup model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $hgGloDeviceLightQuery = HgGloDeviceLight::find()->where([
            'hg_hub_id'=>$model->hg_hub_id,
            'hg_device_group_id'=>$id
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
     * Creates a new HgDeviceGroup model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgDeviceGroup();

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
     * Updates an existing HgDeviceGroup model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $oldModel = $model;
        $post = $this->request->post();

        if ($this->request->isPost) {
            foreach ($post['HgDeviceGroup'] as $key => $value) {
                if ($model->{$key} == $value) {
                    unset($post['HgDeviceGroup'][$key]);
                }
            }
        }

        if ($model->load($post) && $model->save()) {
            return $this->redirect(['/hg-device-group', 'hg_glozone_id' => $oldModel->hg_glozone_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgDeviceGroup model.
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
     * Finds the HgDeviceGroup model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgDeviceGroup the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgDeviceGroup::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
