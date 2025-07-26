<?php

namespace app\controllers;

use app\components\AirtableComponent;
use app\components\HueComponent;
use app\models\HgDeviceSensor;
use app\models\HgHome;
use app\models\HgHub;
use app\models\HgUser;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
// use app\models\LoginForm; // REMOVED: No longer needed for local setup
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            // REMOVED: Access control - no authentication needed for local Home Assistant setup
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    // REMOVED: actionAuth - no authentication needed for local Home Assistant setup

    /**
     * Landing page - redirects directly to default home (ID 1) for local Home Assistant setup
     *
     * @return \yii\web\Response
     */
    public function actionIndex()
    {
        if (!HgHome::findOne(2)) {
            return $this->redirect(['/hg-home/create']);
        }
        // For Home Assistant ingress, go directly to enter-home without redirect
        return $this->actionEnterHome(1);
    }

    public function actionEnterHome($id)
    {
        $home = HgHome::findOne($id);
        $hubs = HgHub::find()->where(['hg_home_id'=>$id])->all();

        Yii::$app->session->set('home_record',$home);

        return $this->redirect(['/hg-home/update','id'=>$id]);
    }

    public function actionDashboard($hg_home_id)
    {
        $hgHome = HgHome::findOne($hg_home_id);

        //Hub Status
        $hub_status = [];
        foreach ($hgHome->hgHubs as $hgHub) {
            $hub_status[$hgHub->id] = [];
            if (!$hgHub->isReachable) {
                $hub_status[$hgHub->id] = false;
                continue;
            }

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

        return $this->render('enter-home',compact('hub_status','hgHome','hgDeviceGroupProvider'));
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    public function actionDebugHome($hg_home_id)
    {
        $hgHome = HgHome::findOne($hg_home_id);
        $hgDeviceSensorsSwitch = [];
        $hgDeviceSensorsMotion = [];
        $hgDeviceGroups = [];

        //Hub Status
        $hub_status = [];
        foreach ($hgHome->hgHubs as $hgHub) {
            if (!$hgHub->isReachable) {
                Yii::$app->session->setFlash('error','At least one hub is unreachable');
                return $this->redirect(Yii::$app->request->referrer);
            }


            $hueRuleSet = $hgHub->getHueComponent()->v1GetRequest('rules');
            $hueSensorSet = $hgHub->getHueComponent()->v1GetRequest('sensors');
            $hueSceneSet = $hgHub->getHueComponent()->v1GetRequest('scenes');
            $hueLightSet = $hgHub->getHueComponent()->v1GetRequest('lights');

            $hub_status[$hgHub->id]['hueRuleSet'] = $hueRuleSet;
            $hub_status[$hgHub->id]['hueSensorSet'] = $hueSceneSet;
            $hub_status[$hgHub->id]['hueLightSet'] = $hueLightSet;

            foreach ($hgHub->hgDeviceSensors as $hgDeviceSensor) {
                $hgDeviceSensor->validateHueHubData($hueRuleSet,$hueSensorSet,$hueSceneSet);

                if (in_array(
                    $hgDeviceSensor->hgProductSensor->type_name,
                    [\app\models\HgProductSensor::TYPE_NAME_HUE_SWITCH])
                ) {
                    $hgDeviceSensorsSwitch[] = $hgDeviceSensor;
                }

                if (in_array(
                    $hgDeviceSensor->hgProductSensor->type_name,
                    [\app\models\HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR])
                ) {
                    $hgDeviceSensorsMotion[] = $hgDeviceSensor;
                }
            }
        }

        $hgDeviceSensorSwitchProvider = new ArrayDataProvider([
            'allModels' => $hgDeviceSensorsSwitch,
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);

        $hgDeviceSensorMotionProvider = new ArrayDataProvider([
            'allModels' => $hgDeviceSensorsMotion,
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);

        $hgDeviceGroupProvider = new ArrayDataProvider([
            'allModels' => $hgHome->getHgDeviceGroups(),
            'pagination'=>[
                'pageSize'=>1000
            ]
        ]);


        return $this->render('debug-home',compact('hgDeviceSensorSwitchProvider','hgDeviceSensorMotionProvider','hgDeviceGroupProvider'));
    }
}
