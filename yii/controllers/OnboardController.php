<?php

namespace app\controllers;


use app\models\HgGlozone;
use app\models\HgHome;
use app\models\HgHub;
use app\models\HgStatus;
use app\models\HgUser;
use app\models\HgUserHome;
use app\models\onboard\CreateHomeForm;
use app\models\onboard\Step1Form;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;

//ONBOARDING FLOW

//-> Signup user / pass
//-> Choose Tier ?
//-> Enter CC
//-> Redirect into HomeGlo UI with nothing to do there



class OnboardController extends Controller
{
    public $layout = 'main-public';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'actions' => [],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    public function beforeAction($event)
    {

        return parent::beforeAction($event);
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

    /**
     * {@inheritdoc}
     */
    public function actionStart()
    {
        $user = Yii::$app->user->identity;

        //if user has a home and a hub -> proceed to dashboard
        if ($user->hgUserHomes) {
            /** @var HgHome $hgHome */
            $hgUserHome = $user->hgUserHomes[0];
            if ($hgUserHome->hgHome->hgHubs) {
                return $this->redirect(['/gui/default/index']);
            }
        }

        //if user has a home -> proceed to connect hub step
        if ($user->hgUserHomes) {
            return $this->redirect(['/onboard/connect-hub']);
        } else {
        //if user has no home -> proceed to create home step
            return $this->redirect(['/onboard/create-home']);
        }
    }

    public function actionCreateHome()
    {
        $user = Yii::$app->user->identity;

        $model = new CreateHomeForm();
        $model->wake_time = '8:00 AM';
        $model->bed_time = '10:00 PM';

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $hgHome = new HgHome();
            $hgHome->display_name = $model->home_name;
            $hgHome->lat = 33.2;
            $hgHome->lng = 75.2;

            if ($hgHome->save()) {

                $hgHub = new HgHub();
                $hgHub->hg_home_id = $hgHome->id;
                $hgHub->display_name = $hgHome->display_name. 'Hub 1';
                $hgHub->save();
                $hgHub->appendJsonData(['onboardFlow'=>1]);

                $defGlozone = HgGlozone::findOne(HgGlozone::HG_DEFAULT_GLOZONE);
                $hgGlozone = new HgGlozone();
                $hgGlozone->attributes = $defGlozone->attributes;
                $hgGlozone->hg_home_id = $hgHome->id;
                $hgGlozone->display_name = $hgHome->display_name.' Glozone';
                $hgGlozone->save();

                HgUserHome::connectHomeToUser($hgHome,$user);
                return $this->redirect(['/onboard/connect-hub','hg_hub_id'=>$hgHub->id]);
            } else {
                Yii::$app->session->setFlash('error','Errors in the form!');
            }
        }

        return $this->render('create-home', ['model' => $model, 'user'=>$user]);
    }

    public function actionConnectHub($hg_hub_id)
    {
        $hgHub = HgHub::findOne($hg_hub_id);
        $user = Yii::$app->user->identity;

        if ($user->hgHome->hgHubs[0]->id != $hgHub->id) {
            Yii::$app->session->setFlash('error','Invalid Hub!');
            return $this->redirect(['/']);
        }

        return $this->render('connect-hub', ['hgHub' => $hgHub, 'user'=>$user]);
    }

    public function actionHubConnected($hg_hub_id)
    {
        $hgHub = HgHub::findOne($hg_hub_id);
        $user = Yii::$app->user->identity;

        if ($user->hgHome->hgHubs[0]->id != $hgHub->id) {
            Yii::$app->session->setFlash('error','Invalid Hub!');
            return $this->redirect(['/']);
        }

        // TODO: Update for Home Assistant - sync down functionality no longer needed
        // Previous logic synced Hue hub state down after hub selection - replace with HA integration if needed
        // SyncDownJob removed - no longer syncing from Hue hub

        return $this->redirect(['/gui/default/index']);
    }

    /**
     * This is the landing page AFTER successful login
     *
     * @return string
     */
    public function actionStep2($hg_user_home)
    {
        $user = HgUserHome::findOne($hg_user_home)->hgUser;
        return $this->render('step_2',['hgUser'=>$user]);
    }

    /**
     * This is the landing page AFTER successful login
     *
     * @return string
     */
    public function actionPostPurchase($session_id)
    {
        $stripe = new \Stripe\StripeClient($_ENV['STRIPE_KEY']);
        $object = $stripe->checkout->sessions->retrieve(
            $session_id,
            []
        );

        $customer_email = $object->customer_details['email'];

        $user = HgUser::find()->where(['email'=>$customer_email])->one();
        $hgHome = $user->hgUserHomes[0]->hgHome;

        /* @var \app\models\HgHub $hgHub */
        $hgHub = $hgHome->hgHubs[0];

        if ($hgHub->isReachable) {
            $hgHome->hg_status_id = HgStatus::HG_HOME_ACTIVE;
            $hgHome->save();
        }

        return $this->render('post_purchase',['hgHub'=>$hgHub]);
    }
}
