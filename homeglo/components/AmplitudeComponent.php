<?php

namespace app\components;

use app\models\HgDeviceGroup;
use app\models\HgDeviceSensor;
use app\models\HgHome;
use TANIOS\Airtable\Airtable;
use Yii;
use yii\base\Component;
use yii\helpers\ArrayHelper;

class AmplitudeComponent extends Component {

    /**
     * Async log to amplitude
     * @param HgDeviceGroup $hgDeviceGroup
     * @param $event_name
     * @param array $data
     */
    public static function asyncLogDeviceGroupEvent(HgDeviceGroup $hgDeviceGroup,$event_name,$data=[])
    {
        $hg_glozone_name = $hgDeviceGroup->hgGlozone->name;
        $hg_device_group_name = $hgDeviceGroup->display_name;

        $hgData = [
            'hg_device_group_name'=>$hg_device_group_name,
            'hg_glozone_name'=>$hg_glozone_name,
        ];

        $data = ArrayHelper::merge($hgData,$data);

        Yii::$app->queue->push(new \app\jobs\AmplitudeLogJob(
            [
                'hg_home_id'=>$hgDeviceGroup->hgGlozone->hg_home_id,
                'event_name'=>$event_name,
                'data'=>$data
            ]
        ));
    }

    /**
     * @param $event_name
     * @param $data
     */
    public static function syncLogBasicEvent($event_name,$data)
    {
        $amplitude = \Zumba\Amplitude\Amplitude::getInstance();
        $amplitude->init($_ENV['AMPLITUDE_API_KEY'], 'DEFAULT');
        $amplitude->logEvent($event_name,$data);
    }

    public function asyncLogDeviceSensorEvent(HgDeviceSensor $hgDeviceSensor)
    {

    }

}