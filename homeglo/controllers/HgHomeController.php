<?php

namespace app\controllers;

use app\components\HgEngineComponent;
use app\models\HgDeviceSensor;
use app\models\HgGlo;
use app\models\HgGlozone;
use app\models\HgGlozoneTimeBlock;
use app\models\HgHome;
use app\models\HgHomeSearch;
use app\models\HgHub;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgHomeController implements the CRUD actions for HgHome model.
 */
class HgHomeController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),[
            // REMOVED: Access control - no authentication needed for local Home Assistant setup
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ]);
    }

    public function beforeAction($event)
    {
        return parent::beforeAction($event);
    }

    /**
     * Lists all HgHome models.
     *
     * @return string
     */
    public function actionIndex()
    {
        error_log("HgHomeController::actionIndex - Current URI: " . \Yii::$app->request->url);
        
        // For Home Assistant setup, auto-redirect to home ID 1
        $defaultHome = HgHome::findOne(1);
        if ($defaultHome) {
            error_log("HgHomeController::actionIndex - Found default home, redirecting to enter-home");
            return $this->redirect(['/site/enter-home', 'id' => 1]);
        }
        
        // If no default home, show the list
        error_log("HgHomeController::actionIndex - No default home found, showing list");
        
        \Yii::$app->session->set('home_record',null);
        \Yii::$app->session->set('home_hubs',null);

        $searchModel = new HgHomeSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->sort->defaultOrder = [
            'id'=> SORT_DESC
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgHome model.
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
     * Creates a new HgHome model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgHome();

        if ($this->request->isPost) {
            if ($model->load($this->request->post()) && $model->save()) {

                //create first glozone
                //get glozone defaults
                $defGlozone = HgGlozone::findOne(HgGlozone::HG_DEFAULT_GLOZONE);
                $hgGlozone = new HgGlozone();
                $hgGlozone->attributes = $defGlozone->attributes;
                $hgGlozone->hg_home_id = $model->id;
                $hgGlozone->display_name = $model->display_name.' Glozone';
                $hgGlozone->save();

                //create the first hub
                $hgHub = new HgHub();
                $hgHub->display_name = $model->display_name.' (Hub 1)';
                $hgHub->hg_home_id = $model->id;
                $hgHub->save();

                return $this->redirect(['/hg-home']);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgHome model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            \Yii::$app->session->setFlash('success','Home settings updated!');
            return $this->redirect(['site/enter-home', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgHome model.
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
     * Finds the HgHome model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgHome the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgHome::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
