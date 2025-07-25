<?php

namespace app\controllers;

use app\components\HueComponent;
use app\models\CloneSwitchRulesForm;
use app\models\HgDeviceLight;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class LightsController extends HomeGloBaseController
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $lightArray = [];
        foreach ($this->home_hubs as $hub) {
            $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
            $lights = $hueApi->v1GetRequest('lights');
            foreach ($lights as $id => $light) {
                $light['id'] = $id;
                $light['hub_id'] = $hub['id'];
                $light['hub'] = $hub['display_name'];
                $lightArray[] = $light;
            }
        }

        $provider = new ArrayDataProvider([
            'allModels' => $lightArray,
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);

        //get rules for switch


        return $this->render('index',compact('provider'));
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionDbData()
    {
        $provider = new ActiveDataProvider([
            'query' => HgDeviceLight::find()->where(['IN','hg_hub_id',$this->home_hub_ids]),
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);

        return $this->render('db-data',compact('provider'));
    }

    public function actionDeleteLight($light_id,$hub_id)
    {
        $hub = $this->home_hubs[$hub_id];
        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $hueApi->v1DeleteRequest('lights/'.$light_id);

        Yii::$app->session->setFlash('success','Light deleted!');
        return $this->redirect($this->request->referrer);
    }
}
