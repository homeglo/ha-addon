<?php

namespace app\controllers;

use app\components\HueComponent;
use app\models\CloneSwitchRulesForm;
use Yii;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class GroupsController extends HomeGloBaseController
{
    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionIndex()
    {
        $objectArray = [];
        foreach ($this->home_hubs as $hub) {
            $hueApi = new HueComponent( $hub['access_token'], $hub['bearer_token']);
            $objects = $hueApi->v1GetRequest('groups');
            foreach ($objects as $id => $object) {
                $object['id'] = $id;
                $object['hub'] = $hub['display_name'];
                $objectArray[] = $object;
            }
        }


        $provider = new ArrayDataProvider([
            'allModels' => $objectArray,
        ]);



        return $this->render('index',compact('provider'));
    }
}
