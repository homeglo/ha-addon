<?php

namespace app\controllers;

use app\components\HelperComponent;
use app\models\HgDeviceSensor;
use app\models\HgGlozone;
use app\models\HgGlozoneSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgGlozoneController implements the CRUD actions for HgGlozone model.
 */
class HgGlozoneController extends HomeGloBaseController
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
     * Lists all HgGlozone models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgGlozoneSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgGlozone model.
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
     * Creates a new HgGlozone model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgGlozone();
        $model->bed_time_weekday_midnightmins = HelperComponent::convertMidnightMinutesToHhSs(HgGlozone::findOne(HgGlozone::HG_DEFAULT_GLOZONE)->bed_time_weekday_midnightmins);
        $model->wake_time_weekday_midnightmins = HelperComponent::convertMidnightMinutesToHhSs(HgGlozone::findOne(HgGlozone::HG_DEFAULT_GLOZONE)->wake_time_weekday_midnightmins);
        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->bed_time_weekday_midnightmins = (string) HelperComponent::convertHhSsToMidnightMinutes($model->bed_time_weekday_midnightmins);
                $model->wake_time_weekday_midnightmins = (string) HelperComponent::convertHhSsToMidnightMinutes($model->wake_time_weekday_midnightmins);
                $model->hg_home_id = \Yii::$app->session->get('home_record')['id'];

                if ($model->save())
                    return $this->redirect(['update', 'id' => $model->id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgGlozone model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $post = $this->request->post();

        $model->bed_time_weekday_midnightmins = HelperComponent::convertMidnightMinutesToHhSs($model->bed_time_weekday_midnightmins);
        $model->wake_time_weekday_midnightmins = HelperComponent::convertMidnightMinutesToHhSs($model->wake_time_weekday_midnightmins);
        if ($this->request->isPost && $model->load($post)) {
            $model->bed_time_weekday_midnightmins = (string) HelperComponent::convertHhSsToMidnightMinutes($model->bed_time_weekday_midnightmins);
            $model->wake_time_weekday_midnightmins = (string) HelperComponent::convertHhSsToMidnightMinutes($model->wake_time_weekday_midnightmins);
            if ($model->save()){

                \Yii::$app->getCache()->flush();

                \Yii::$app->session->setFlash('success','Glozone settings updated!');
                return $this->redirect(['update', 'id' => $model->id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgGlozone model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model =$this->findModel($id);
        $hg_home_id = $model->hg_home_id;

        if ($model->hgDeviceGroups) {
            \Yii::$app->session->setFlash('danger','Remove all rooms from glozone before deleting!');
            return $this->redirect(\Yii::$app->request->referrer);
        }

        $model->delete();

        return $this->redirect(['/site/dashboard','hg_home_id'=>$hg_home_id]);
    }

    /**
     * Creates a new HgGlozone model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionWipeSwitchRules($id)
    {
        $model = HgGlozone::findOne($id);

        /* @var HgDeviceSensor $hgDeviceSensor */
        foreach ($model->hgDeviceSensors as $hgDeviceSensor) {
            if ($hgDeviceSensor->hg_hub_action_map_id) {
                $hgDeviceSensor->hgHubActionMap->delete();
            }
        }

        \Yii::$app->session->setFlash('success','Wiped!');
        return $this->redirect(\Yii::$app->request->referrer);
    }

    /**
     * Finds the HgGlozone model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgGlozone the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgGlozone::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
