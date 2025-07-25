<?php

namespace app\controllers;

use app\models\HgHubActionCondition;
use app\models\HgHubActionTemplate;
use app\models\HgHubActionTemplateSearch;
use app\models\HgHubActionTrigger;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgHubActionTemplateController implements the CRUD actions for HgHubActionTemplate model.
 */
class HgHubActionTemplateController extends HomeGloBaseController
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
     * Lists all HgHubActionTemplate models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgHubActionTemplateSearch();
        $dataProvider = $searchModel->search($this->request->queryParams,$this->home_hub_ids);
        $dataProvider->pagination->pageSize=1000;

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Lists all HgHubActionTemplate models.
     *
     * @return string
     */
    public function actionIndexTemplates()
    {
        $searchModel = new HgHubActionTemplateSearch();
        $dataProvider = $searchModel->search($this->request->queryParams);
        $dataProvider->pagination->pageSize=1000;
        $dataProvider->sort->defaultOrder = [
            'display_name'=>SORT_ASC
        ];

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgHubActionTemplate model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        $hgHubActionTemplate = $this->findModel($id);
        $hgHubActionTriggerQuery = HgHubActionTrigger::find()->where(['hg_hub_action_template_id'=>$id]);

        $hgHubActionTriggerDataProvider = new \yii\data\ActiveDataProvider([
            'query' => $hgHubActionTriggerQuery,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('view', [
            'hgHubActionTemplate' => $hgHubActionTemplate,
            'hgHubActionTriggerDataProvider' => $hgHubActionTriggerDataProvider,

        ]);
    }

    /**
     * Clones a single HgHubActionTemplate model.
     * @param int $id ID
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionClone($id)
    {
        $hgHubActionTemplate = $this->findModel($id);
        $hgHubActionTemplate->display_name = $hgHubActionTemplate->display_name.' CLONE';
        $hgHubActionTemplate->copyEntireTree();

        return $this->redirect(\Yii::$app->request->referrer);
    }

    /**
     * Creates a new HgHubActionTemplate model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgHubActionTemplate();

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
     * Updates an existing HgHubActionTemplate model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgHubActionTemplate model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param int $id ID
     * @return \yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $map = $model->hg_hub_action_map_id;
        $model->delete();

        return $this->redirect(['index-templates','HgHubActionTemplateSearch[hg_hub_action_map_id]'=>$map]);
    }

    /**
     * Finds the HgHubActionTemplate model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgHubActionTemplate the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgHubActionTemplate::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }

    public function actionLogicViewer($template_id)
    {
        $hgHubActionTemplate = $this->findModel($template_id);
        $hgHubActionTrigger = HgHubActionTrigger::find()->where(['hg_hub_action_template_id'=>$template_id]);
        return $this->render('logic-viewer',[
            'hgHubActionTemplate' => $hgHubActionTemplate
        ]);
    }
}
