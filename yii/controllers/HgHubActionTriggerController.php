<?php

namespace app\controllers;

use app\models\HgHubActionCondition;
use app\models\HgHubActionItem;
use app\models\HgHubActionTrigger;
use app\models\HgHubActionTriggerSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgHubActionTriggerController implements the CRUD actions for HgHubActionTrigger model.
 */
class HgHubActionTriggerController extends HomeGloBaseController
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
     * Lists all HgHubActionTrigger models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgHubActionTriggerSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgHubActionTrigger model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $hgHubActionConditionsQuery = HgHubActionCondition::find()->where(['hg_hub_action_trigger_id'=>$id]);

        $hgHubActionConditionDataProvider = new \yii\data\ActiveDataProvider([
            'query' => $hgHubActionConditionsQuery,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $hgHubActionItemQuery = HgHubActionItem::find()->where(['hg_hub_action_trigger_id'=>$id]);
        $hgHubActionItemDataProvider = new \yii\data\ActiveDataProvider([
            'query' => $hgHubActionItemQuery,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        if ($model->hgHub) {
            $ruleHueJson = $model->hgHub->getHueComponent()->v1GetRequest('rules/'.$model->hue_id);
        }



        return $this->render('view', [
            'model' => $model,
            'hgHubActionConditionDataProvider'=> $hgHubActionConditionDataProvider,
            'hgHubActionItemDataProvider'=> $hgHubActionItemDataProvider,
            'ruleHueJson'=>@$ruleHueJson
        ]);
    }

    /**
     * Creates a new HgHubActionTrigger model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgHubActionTrigger();

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
     * Updates an existing HgHubActionTrigger model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            $model->event_data = json_decode($model->event_data);
            if ($model->save()) {
                return $this->redirect(['/hg-hub-action-template/view', 'id' => $model->hg_hub_action_template_id]);
            }

        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgHubActionTrigger model.
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
     * Clones a single HgHubActionTemplate model.
     * @param int $id ID
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionClone($id)
    {
        $hgHubActionTrigger = $this->findModel($id);
        $hgHubActionTrigger->cloneTriggerConditionsItems();

        return $this->redirect(\Yii::$app->request->referrer);
    }

    /**
     * Finds the HgHubActionTrigger model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgHubActionTrigger the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgHubActionTrigger::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }
}
