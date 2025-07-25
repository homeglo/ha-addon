<?php

namespace app\controllers;

use app\components\HueComponent;
use app\models\CloneSwitchRulesForm;
use app\models\HgHub;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SensorsController extends HomeGloBaseController
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $switchArray = [];
        foreach (HgHub::find()->where(['hg_home_id'=>$this->home_record['id']])->all() as $hub) {
            $hueApi = new HueComponent($hub['access_token'],$hub['bearer_token']);
            $sensors = $hueApi->v1GetRequest('sensors');
            foreach ($sensors as $id => $data) {
                //if (in_array(@$data['type'],['ZLLSwitch','ZLLPresence','ZLLLightLevel'])) {
                    $data['hub'] = $hub['display_name'];
                    $data['hub_id'] = $hub['id'];
                    $data['id'] = $id;
                    $data['ruleCount'] = count($hueApi->getSwitchRules($id));
                    $switchArray[] = $data;
                //}
            }
        }


        $provider = new ArrayDataProvider([
            'allModels' => $switchArray,
            'pagination'=>[
                'pageSize'=>100
            ]
        ]);

        //get rules for switch


        return $this->render('index',compact('provider','cloneSwitchRulesForm'));
    }

    public function actionRules($id,$hub_id)
    {
        $hub = HgHub::findOne($hub_id);
        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $objects = $hueApi->getSwitchRules($id);
        $objectArray = [];
        foreach ($objects as $id => $object) {
            $object['id'] = $id;
            $object['hub_id'] = $hub_id;
            $objectArray[] = $object;
        }

        $provider = new ArrayDataProvider([
            'allModels' => $objectArray,
        ]);

        return $this->render('rules',compact('provider'));
    }

    public function actionAddSensor()
    {
        $array = [];
        $last_scan = [];
        foreach (HgHub::find()->where(['hg_home_id'=>$this->home_record['id']])->all()  as $hub) {
            $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
            $objects = $hueApi->v1GetRequest('sensors/new');

            $last_scan[$hub['display_name']] = $objects['lastscan'];
            unset($objects['lastscan']);

            foreach ($objects as $object) {
                $array[] = $object;
            }

            $provider = new ArrayDataProvider([
                'allModels' => $array,
            ]);
        }

        return $this->render('add-sensor',compact('provider','last_scan'));
    }

    public function actionScanSensors()
    {
        foreach (HgHub::find()->where(['hg_home_id'=>$this->home_record['id']])->all() as $hub) {
            $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
            $scan = $hueApi->v1PostRequest('sensors',(object) []);
        }

        return $this->redirect(['/switches/add-sensor']);
    }

    public function actionDeleteRule($rule_id,$hub_id)
    {
        $hub = HgHub::findOne($hub_id);
        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $hueApi->v1DeleteRequest('rules/'.$rule_id);

        Yii::$app->session->setFlash('success','Rule deleted!');
        return $this->redirect($this->request->referrer);
    }

    public function actionDeleteSensor($sensor_id,$hub_id)
    {
        $hub = HgHub::findOne($hub_id);
        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $hueApi->v1DeleteRequest('sensors/'.$sensor_id);

        Yii::$app->session->setFlash('success','Sensor deleted!');
        return $this->redirect($this->request->referrer);
    }
}
