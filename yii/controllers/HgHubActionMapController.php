<?php

namespace app\controllers;

use app\components\HelperComponent;
use app\models\HgHubActionMap;
use app\models\HgHubActionMapSearch;
use app\models\HgHubActionTemplate;
use app\models\HgHubActionTemplateSearch;
use app\models\HgHubActionTrigger;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

/**
 * HgHubActionMapController implements the CRUD actions for HgHubActionMap model.
 */
class HgHubActionMapController extends HomeGloBaseController
{
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),[
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],

                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ]);
    }

    /**
     * Lists all HgHubActionMap models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new HgHubActionMapSearch();

        $dataProvider = $searchModel->search($this->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single HgHubActionMap model.
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
     * Displays a single HgHubActionMap model.
     * @param int $id ID
     * @return string
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionViewMapTemplates($id)
    {
        $hgHubActionTemplateQuery = HgHubActionTemplate::find()->where(['hg_hub_action_map_id'=>$id]);

        $hgHubActionTemplateDataProvider = new \yii\data\ActiveDataProvider([
            'query' => $hgHubActionTemplateQuery,
            'pagination' => [
                'pageSize' => 1000,
            ],
        ]);

        return $this->render('/hg-hub-action-template/index', [
            'searchModel' => $hgHubActionTemplateDataProvider,
            'dataProvider' => $hgHubActionTemplateDataProvider,
        ]);
    }


    /**
     * Creates a new HgHubActionMap model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     */
    public function actionCreate()
    {
        $model = new HgHubActionMap();

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
     * Updates an existing HgHubActionMap model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param int $id ID
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($this->request->isPost && $model->load($this->request->post())) {
            if ($model->save()) {
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing HgHubActionMap model.
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
     * Finds the HgHubActionMap model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param int $id ID
     * @return HgHubActionMap the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = HgHubActionMap::findOne(['id' => $id])) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('The requested page does not exist.');
    }


    public function actionCloneMap($id)
    {
        $model = $this->findModel($id);
        $hgHubActionMap = $model->cloneActionMapTemplate();

        \Yii::$app->session->setFlash('success','Map cloned!');
        return $this->redirect(['view','id'=>$hgHubActionMap->id]);
    }

    public function actionExportMap($id)
    {
        $sql = [];
        $model = $this->findModel($id);


        $sql[] = HelperComponent::getInsertSqlFromModel($model);

        foreach ($model->hgHubActionTemplates as $hgHubActionTemplate) {
            $sql[] = HelperComponent::getInsertSqlFromModel($hgHubActionTemplate);

            foreach ($hgHubActionTemplate->hgHubActionTriggers as $hgHubActionTrigger) {
                $sql[] = HelperComponent::getInsertSqlFromModel($hgHubActionTrigger);
                foreach ($hgHubActionTrigger->hgHubActionConditions as $hgHubActionCondition) {
                    $sql[] = HelperComponent::getInsertSqlFromModel($hgHubActionCondition);
                }

                foreach ($hgHubActionTrigger->hgHubActionItems as $hgHubActionItem) {
                    $sql[] = HelperComponent::getInsertSqlFromModel($hgHubActionItem);
                }
            }
        }

        $str = implode("\n",$sql);
        \Yii::$app->response->sendContentAsFile($str,'map.sql');
        \Yii::$app->response->send();
    }

}
