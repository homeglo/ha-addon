<?php

namespace app\jobs;

use app\components\HgEngineComponent;
use app\models\HgHome;
use app\models\HgHub;
use app\models\HgHubActionTemplate;

class AmplitudeLogJob extends \yii\base\BaseObject implements \yii\queue\JobInterface
{
    public $hg_home_id;
    public $event_name;
    public $data = [];

    public function execute($queue)
    {
        try {
            $hgHome = HgHome::findOne($this->hg_home_id);

            $amplitude = \Zumba\Amplitude\Amplitude::getInstance();
            $amplitude->init($_ENV['AMPLITUDE_API_KEY'], $hgHome->name);
            $amplitude->logEvent($this->event_name,$this->data);
        } catch (\Throwable $t) {
            \Sentry\captureException($t);
            \Yii::error($t->getMessage(),__METHOD__);
        }

    }
}
