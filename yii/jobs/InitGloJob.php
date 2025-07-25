<?php

namespace app\jobs;

use app\components\HgEngineComponent;
use app\components\HueSyncComponent;
use app\models\HgDeviceGroup;
use app\models\HgDeviceSensor;
use app\models\HgGlo;
use app\models\HgGloDeviceGroup;
use app\models\HgGlozone;
use app\models\HgHub;
use app\models\HgHubActionMap;
use app\models\HgHubActionTemplate;

class InitGloJob extends \yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $hg_glozone_id;
    public $hg_device_group_id;
    public $state;

    public function execute($queue)
    {
        try {
            $hgGlozone = HgGlozone::findOne($this->hg_glozone_id);
            $hgDeviceGroup = HgDeviceGroup::findOne($this->hg_device_group_id);
            $hgGlozone->reInitGlosInHue($this->state,[$hgDeviceGroup]);

            \Yii::$app->queue->push(new \app\jobs\SyncGlozoneJob(
                [
                    'hg_glozone_id'=>$hgGlozone->id
                ]
            ));

        } catch (\Throwable $t) {
            \Sentry\captureException($t);
            \Yii::error($t->getMessage(),__METHOD__);
        }

    }
}
