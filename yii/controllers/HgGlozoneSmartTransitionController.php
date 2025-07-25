<?php

namespace app\controllers;

use app\models\HgGloDeviceLight;
use app\models\HgGlozoneSmartTransition;
use app\models\HgGlozoneSmartTransitionExecute;
use app\models\HgGlozoneSmartTransitionSearch;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgGlozoneSmartTransitionController implements the CRUD actions for HgGlozoneSmartTransition model.
 */
class HgGlozoneSmartTransitionController extends HomeGloBaseController
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
     * Lists all HgGlozoneSmartTransition models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgGlozoneSmartTransitionSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination->pageSize = 1000;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgGlozoneSmartTransition model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $hgGlozoneSmartTransitionExecuteQuery = HgGlozoneSmartTransitionExecute::find()->where([
            'hg_glozone_smart_transition_id'=>$model->id
        ]);

        $hgGlozoneSmartTransitionExecuteProvider = new ActiveDataProvider([
            'query' => $hgGlozoneSmartTransitionExecuteQuery,
            'sort'=>[
                'defaultOrder'=>[
                    'id' => SORT_DESC
                ]
            ]
        ]);

        return $this->render('view', [
            'model' => $model,
            'hgGlozoneSmartTransitionExecuteProvider'=> $hgGlozoneSmartTransitionExecuteProvider
        ]);
    }

    public function actionDeleteExecuteRow($id)
    {
        HgGlozoneSmartTransitionExecute::deleteAll(['id'=>$id]);
        return $this->redirect(\Yii::$app->request->referrer);
    }

    /**
     * Creates a new HgGlozoneSmartTransition model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgGlozoneSmartTransition();

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
     * Updates an existing HgGlozoneSmartTransition model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['/hg-glozone-time-block/view', 'id' => $model->hg_glozone_time_block_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgGlozoneSmartTransition model.
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
     * Finds the HgGlozoneSmartTransition model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgGlozoneSmartTransition the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgGlozoneSmartTransition::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
