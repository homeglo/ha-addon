<?php

namespace app\models;

use app\components\HueComponent;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class CloneBulbForm extends Model
{
    public $source_hg_device_light_id;
    public $destination_hg_device_light_id;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['source_hg_device_light_id'], 'required'],
            [['source_hg_device_light_id', 'destination_hg_device_light_id'], 'integer'],
        ];
    }

    public function performClone()
    {
        $sourceHgDeviceLight = HgDeviceLight::findOne($this->source_hg_device_light_id);
        $destinationHgDeviceLight = HgDeviceLight::findOne($this->destination_hg_device_light_id);

        $hueApi = $sourceHgDeviceLight->hgHub->getHueComponent();

        //Add dest bulb to source room
        $destinationHgDeviceLight->primary_hg_device_group_id = $sourceHgDeviceLight->primary_hg_device_group_id;
        $destinationHgDeviceLight->save();


    }
}
