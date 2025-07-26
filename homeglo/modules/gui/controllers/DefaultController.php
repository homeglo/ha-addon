<?php

namespace app\modules\gui\controllers;

use app\models\HgHome;
use app\models\HgHub;
use yii\data\ArrayDataProvider;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

class DefaultController extends Controller
{

    public function beforeAction($event)
    {
        if ($id = \Yii::$app->user->identity->hgHome->id ??  \Yii::$app->request->getQueryParam('id')) {
            $home = HgHome::findOne($id);
            $hubs = HgHub::find()->where(['hg_home_id'=>$id])->all();
            $hubArray = [];
            foreach ($hubs as $h) {
                $hubArray[$h->id] = $h;
            }

            \Yii::$app->session->set('home_record',$home);
            \Yii::$app->session->set('home_hubs',$hubArray);
        }


        return parent::beforeAction($event);
    }


    public function actionIndex()
    {
        $hgHome = HgHome::findOne(\Yii::$app->session->get('home_record')->id);

        //Hub Status
        $hub_status = [];
        foreach ($hgHome->hgHubs as $hgHub) {
            $hub_status[$hgHub->id] = [];
            $hub_status[$hgHub->id]['display_name'] = $hgHub->display_name;
            if (!$hgHub->isReachable) {
                $hub_status[$hgHub->id] += ['reachable'=>false];
                continue;
            }
            $hub_status[$hgHub->id] += ['reachable'=>true];

            $hueLightSet = $hgHub->getHueComponent()->v1GetRequest('lights');
            $hub_status[$hgHub->id]['hueLightSet'] = $hueLightSet;
        }

        foreach ($hgHome->hgDeviceGroups as $hgDeviceGroup) {
            /* @var \app\models\HgGlozoneTimeBlock $hgGlozoneTimeBlockActive */
            $hgGlozoneTimeBlockActive = $hgDeviceGroup->hgGlozone->activeTimeBlock;
            if (!$hgGlozoneTimeBlockActive)
                continue;

            $details = [];
            $currentGlos = $hgDeviceGroup->getActiveGloInHue($hub_status[$hgDeviceGroup->hg_hub_id]['hueLightSet']);
            $details['last_time_block_transition'] = $hgGlozoneTimeBlockActive->getHgGlozoneSmartTransitions()->andWhere(['hg_device_group_id'=>$hgDeviceGroup->id])->orderBy('id DESC')->one();
            $details['current_glo'] = $currentGlos; //need to compareBulb situation
            $details['expected_glo'] = $hgGlozoneTimeBlockActive->defaultHgGlo->display_name;
            $details['upcoming_glo'] = $hgGlozoneTimeBlockActive->getNextSequentialTimeBlock()->defaultHgGlo->display_name;
            $details['upcoming_transition_time'] = $hgGlozoneTimeBlockActive->getNextSequentialTimeBlock()->getTimeStartDefaultFormatted();
            $details['behavior'] = $hgGlozoneTimeBlockActive->smartTransition_behavior;
            $hgDeviceGroups[] = ArrayHelper::merge(['details'=>$details],['hgDeviceGroup'=>$hgDeviceGroup]);
        }

        $hgDeviceGroupProvider = new ArrayDataProvider([
            'allModels' => $hgDeviceGroups,
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);

        return $this->render('index',compact('hub_status','hgHome','hgDeviceGroupProvider'));
    }

    public function actionBilling()
    {
        return $this->render('billing');
    }
}
