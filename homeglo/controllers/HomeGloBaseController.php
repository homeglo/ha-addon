<?php

namespace app\controllers;

use app\components\HueComponent;
use app\components\HueSyncComponent;
use app\models\HgGlozoneSmartTransition;
use app\models\HgGlozoneTimeBlock;
use app\models\HgHome;
use app\models\HgHub;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\filters\VerbFilter;

class HomeGloBaseController extends Controller
{
    /**
     * @var array
     * [
     *  [
     *      'id'=>
     *      'token'=>
     *  ]
     * ]
     */
    public array $home_hubs = [];

    /**
     * @var
     * See airtable getNormalizedHomes
     */
    public $home_record = null;

    public array $home_hub_ids = [];

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
        error_log("HomeGloBaseController::beforeAction - Controller: " . $this->id . ", Action: " . $this->action->id);
        error_log("HomeGloBaseController::beforeAction - Current URI: " . Yii::$app->request->url);
        
        if ($home_record = Yii::$app->session->get('home_record',false)) {
            error_log("HomeGloBaseController::beforeAction - Found home_record in session: " . $home_record->id);
            $this->home_record = HgHome::findOne($home_record->id);
        } else {
            error_log("HomeGloBaseController::beforeAction - No home_record in session, auto-setting default home");
            // Auto-setup default home (ID 2) for local Home Assistant setup
            $defaultHome = HgHome::findOne(2);
            if ($defaultHome) {
                $this->home_record = $defaultHome;
                Yii::$app->session->set('home_record', $defaultHome);
                error_log("HomeGloBaseController::beforeAction - Set default home in session");
            } else {
                error_log("HomeGloBaseController::beforeAction - Default home (ID 2) not found!");
                Yii::$app->session->setFlash('error','Default home (ID 2) not found!');
            }
        }

        if ($home_hubs = Yii::$app->session->get('home_hubs',false)) {
            error_log("HomeGloBaseController::beforeAction - Found home_hubs in session");
            $this->home_hubs = HgHub::find()->where(['hg_home_id'=>$this->home_record['id']])->all();

            foreach ($this->home_hubs as $h) {
                $this->home_hub_ids[] = $h['id'];
            }
        } else {
            error_log("HomeGloBaseController::beforeAction - No home_hubs in session, loading from DB");
            // Load hubs from database
            if ($this->home_record) {
                $this->home_hubs = HgHub::find()->where(['hg_home_id'=>$this->home_record['id']])->all();
                foreach ($this->home_hubs as $h) {
                    $this->home_hub_ids[] = $h['id'];
                }
                if (count($this->home_hubs) > 0) {
                    Yii::$app->session->set('home_hubs', $this->home_hubs);
                    error_log("HomeGloBaseController::beforeAction - Loaded " . count($this->home_hubs) . " hubs from DB and set in session");
                } else {
                    error_log("HomeGloBaseController::beforeAction - No hubs found in DB for home " . $this->home_record['id']);
                    Yii::$app->session->setFlash('error','No hubs available!');
                }
            }
        }

        // No need to redirect - we auto-setup the default home above
        return parent::beforeAction($event);
    }
}
