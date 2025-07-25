<?php

namespace app\controllers;

use app\models\HgHubActionItem;
use app\models\HgHubActionItemSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgHubActionItemController implements the CRUD actions for HgHubActionItem model.
 */
class HgHubActionItemController extends HomeGloBaseController
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
     * Lists all HgHubActionItem models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgHubActionItemSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgHubActionItem model.
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
     * Creates a new HgHubActionItem model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate($hg_hub_action_trigger_id)
    {
        $model = new HgHubActionItem();
        $model->hg_hub_action_trigger_id = $hg_hub_action_trigger_id;


        if ($this->request->isPost) {
            if ($model->load($this->request->post())) {
                $model->operation_value_json = json_decode($model->operation_value_json,TRUE);
                if ($model->save())
                    return $this->redirect(['/hg-hub-action-trigger/view', 'id' => $model->hg_hub_action_trigger_id]);
            }
        } else {
            $model->loadDefaultValues();
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing HgHubActionItem model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->operation_value_json = json_decode($model->operation_value_json);
            if ($model->save())
                return $this->redirect(['/hg-hub-action-trigger/view', 'id' => $model->hg_hub_action_trigger_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgHubActionItem model.
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
     * Finds the HgHubActionItem model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgHubActionItem the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgHubActionItem::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
