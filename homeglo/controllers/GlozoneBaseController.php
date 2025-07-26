<?php

namespace app\controllers;

use app\components\HueComponent;
use app\components\HueSyncComponent;
use app\models\HgGlozone;
use app\models\HgGlozoneSmartTransition;
use app\models\HgGlozoneTimeBlock;
use app\models\HgHome;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;

class GlozoneBaseController extends HomeGloBaseController
{
    public $hg_glozone_id = null;
    public $hgGlozone = null;

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
        if ($hg_glozone_id = Yii::$app->request->getQueryParam('hg_glozone_id')) {
            Yii::$app->session->set('hg_glozone_id',$hg_glozone_id);
        } else {
            Yii::$app->session->set('hg_glozone_id',NULL);
        }

        if ($hg_glozone_id = Yii::$app->session->get('hg_glozone_id',false)) {
            $this->hg_glozone_id = $hg_glozone_id;
            $this->hgGlozone = HgGlozone::findOne($hg_glozone_id);

            Yii::$app->getView()->params['breadcrumbs'][] = $this->hgGlozone->display_name;
        } else {
            Yii::$app->session->setFlash('error','No glozone specified!');
        }



        return parent::beforeAction($event);
    }
}
