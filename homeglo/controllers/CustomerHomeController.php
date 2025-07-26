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
class CustomerHomeController extends Controller
{
    public $layout = 'customer-main';
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(),[
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['customer','admin'],
                        'actions'=>['index']
                    ],
                    [
                        'allow' => false,
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
     * Lists all HgHome models.
     *
     * @return string
     */
    public function actionIndex()
    {
        $hgHome = \Yii::$app->user->identity->hgUserHomes[0]->hgHome;

        //Hub Status
        $hub_status = [];
        $motionSensors = [];
        foreach ($hgHome->hgHubs as $hgHub) {
            $hub_status[$hgHub->id] = [];
            if (!$hgHub->isReachable)
                continue;

            $hueLightSet = $hgHub->getHueComponent()->v1GetRequest('lights');
            $hub_status[$hgHub->id]['hueLightSet'] = $hueLightSet;
        }

        foreach ($hgHome->hgDeviceGroups as $hgDeviceGroup) {
            /* @var \app\models\HgGlozoneTimeBlock $hgGlozoneTimeBlockActive */
            $hgGlozoneTimeBlockActive = $hgDeviceGroup->hgGlozone->activeTimeBlock;
            $details = [];
            $details['lastHgGlozoneSmartTransition'] = $hgGlozoneTimeBlockActive->getHgGlozoneSmartTransitions()->orderBy('id DESC')->one();
            $details['currentHgGlo'] = $hgDeviceGroup->getActiveGloInHue($hub_status[$hgDeviceGroup->hg_hub_id]['hueLightSet']);
            $details['currentHgGlozoneTimeBlock'] = $hgGlozoneTimeBlockActive;
            $details['upcomingHgGlozoneTimeBlock'] = $hgGlozoneTimeBlockActive->getNextSequentialTimeBlock();
            $details['behavior'] = $hgGlozoneTimeBlockActive->smartTransition_behavior;


            /* @var HgDeviceSensor $hgDeviceSensor */
            foreach ($hgDeviceGroup->hgDeviceSensors as $hgDeviceSensor) {
                if ($hgDeviceSensor->hgProductSensor->isMotion) {
                    $motionSensors[] = $hgDeviceSensor;
                }
            }

            $hgDeviceGroups[] = ArrayHelper::merge(['details'=>$details],['hgDeviceGroup'=>$hgDeviceGroup]);
        }


        return $this->render('index', [
            'hgHome'=>$hgHome,
            'hub_status'=>$hub_status,
            'hgDeviceGroupsData'=>$hgDeviceGroups,
            'motionSensors'=>$motionSensors
        ]);
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
