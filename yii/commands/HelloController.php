<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\AirtableComponent;
use app\components\HelperComponent;
use app\components\HgEngineComponent;
use app\components\HueComponent;
use app\components\HueSyncComponent;
use app\models\HgDeviceLight;
use app\models\HgGlo;
use app\models\HgHome;
use app\models\HgHub;
use app\models\HgHubActionMap;
use app\models\HomeGloButtonForm;
use NXP\MathExecutor;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{
    /**
     * This command echoes what you have entered as the message.
     * @param string $message the message to be echoed.
     * @return int Exit code
     */
    public function actionIndex($message = 'hello world')
    {
        echo $message . "\n";

        \Yii::error('Log');

        return ExitCode::OK;
    }

    public function actionProgramHub($hub_id)
    {
        $form = new HomeGloButtonForm();
        $form->hg_hub_id = $hub_id;
        $programRemote = $form->programRemoteHueHub();
        echo "done!\n";
    }

    public function actionSyncDown($home_id)
    {
        foreach (HgHub::find()->where(['hg_home_id'=>$home_id])->all() as $hgHub) {
            $hueSyncComponent = new HueSyncComponent($hgHub);

            $lights = $hueSyncComponent->importLights();
            $sensors = $hueSyncComponent->importSensors();

            foreach ($hgHub->hgHome->hgGlozones as $hgGlozone) {
                $scenes = $hueSyncComponent->importScenes($hgGlozone);
                $groups = $hueSyncComponent->importGroups($hgGlozone);
            }

        }

        echo "Synced!";
    }

    public function actionCarousel()
    {
        $hgHome = HgHome::findOne(3);
        foreach ($hgHome->hgHubs as $hgHub) {
            $hgHub->getHueComponent()->turnOnAllLights(500);
            $hgHub->getHueComponent()->turnOffAllLights();
        }

        $lights = [3438,3437,3436,3429,3434,3428,3430,3435,3431,3432,3433];

        for ($x=0;$x<sizeof($lights);$x++) {

            $l = HgDeviceLight::findOne($lights[$x]);
            $l->flashLight();
            sleep(.2);

            if ($x == (sizeof($lights)-1)) {
                $x=-1;
            }
        }
    }
}
