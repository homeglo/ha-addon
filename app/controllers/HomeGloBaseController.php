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
        if ($home_record = Yii::$app->session->get('home_record',false)) {
            $this->home_record = HgHome::findOne($home_record->id);
        } else {
            // Auto-setup default home (ID 1) for local Home Assistant setup
            $defaultHome = HgHome::findOne(1);
            if ($defaultHome) {
                $this->home_record = $defaultHome;
                Yii::$app->session->set('home_record', $defaultHome);
                
                // Auto-setup hubs for default home
                $hubs = HgHub::find()->where(['hg_home_id' => 1])->all();
                $hubArray = [];
                foreach ($hubs as $h) {
                    $hubArray[$h->id] = $h;
                }
                Yii::$app->session->set('home_hubs', $hubArray);
            } else {
                Yii::$app->session->setFlash('error','Default home (ID 1) not found!');
            }
        }

        if ($home_hubs = Yii::$app->session->get('home_hubs',false)) {
            $this->home_hubs = HgHub::find()->where(['hg_home_id'=>$this->home_record['id']])->all();

            foreach ($this->home_hubs as $h) {
                $this->home_hub_ids[] = $h['id'];
            }
        } else {
            // This should now be handled above
            Yii::$app->session->setFlash('error','No hubs available!');
        }

        // No need to redirect - we auto-setup the default home above
        return parent::beforeAction($event);
    }

    public function actionSyncDown()
    {
        // TODO: Update for Home Assistant - sync down functionality no longer needed
        // Previous logic synced Hue hub state down to local database - replace with HA integration if needed
        // SyncDownJob removed - no longer syncing from Hue hub

        \Yii::$app->session->setFlash('info','Sync functionality temporarily disabled during Home Assistant migration');
        $this->redirect(\Yii::$app->request->referrer);
    }
}
