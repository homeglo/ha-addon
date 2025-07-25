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

class ScenesController extends HomeGloBaseController
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $objectArray = [];
        foreach (HgHub::find()->where(['hg_home_id'=>$this->home_record['id']])->all() as $hub) {
            $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);

            //process all the lights
            $lights = $hueApi->v1GetRequest('lights');
            $lightArray = [];
            foreach ($lights as $key => $l) {
                $lightArray[$key] = $l['name'];
            }

            $objects = $hueApi->v1GetRequest('scenes');

            foreach ($objects as $id => $object) {
                $lightObjects = $object['lights'];
                $object['lights'] = [];
                foreach ($lightObjects as $light_id) {
                    $object['lights'][] = $lightArray[$light_id];
                }

                $object['id'] = $id;
                $object['hub'] = $hub['display_name'];
                $object['hub_id'] = $hub['id'];
                $objectArray[] = $object;

            }
        }

        $provider = new ArrayDataProvider([
            'allModels' => $objectArray,
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);


        return $this->render('index',compact('provider'));
    }

    public function actionView($scene_id,$hub_id)
    {
        $hub = HgHub::findOne($hub_id);

        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $data = $hueApi->v1GetRequest('scenes/'.$scene_id);

        return $this->render('view',compact('data'));

    }

    public function actionDelete($scene_id,$hub_id)
    {
        $hub = HgHub::findOne($hub_id);

        $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
        $data = $hueApi->v1DeleteRequest('scenes/'.$scene_id);

        return $this->redirect(\Yii::$app->request->referrer);
    }
}
