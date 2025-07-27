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
            }
        }

        // No need to redirect - we auto-setup the default home above
        return parent::beforeAction($event);
    }
}
