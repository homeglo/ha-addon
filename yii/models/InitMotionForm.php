<?php

namespace app\models;

use app\components\HgEngineComponent;
use app\components\HueComponent;
// use app\jobs\InitSwitchJob; // REMOVED: No longer initializing Hue switch configurations
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class InitMotionForm extends Model
{
    public $hg_hub_ids;
    public $hg_device_sensor_ids;
    public $template_hg_hub_action_map_id;
    public $async = true;
    public $hg_glozone_id;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['hg_device_sensor_ids', 'template_hg_hub_action_map_id'], 'required'],
            [['hg_device_sensor_ids'], 'each', 'rule'=>['integer']],
            [['template_hg_hub_action_map_id'], 'integer'],
            [['async'],'boolean']
        ];
    }

    public function performInit()
    {
        // TODO: Update for Home Assistant - motion sensor initialization no longer needed
        // Previous logic initialized motion sensors with Hue switch configurations
        // InitSwitchJob removed - no longer setting up Hue sensor rules and action maps
        
        foreach ($this->hg_device_sensor_ids as $hg_device_sensor_id) {
            // Motion sensor initialization disabled during Home Assistant migration
            \Yii::info("Motion sensor initialization skipped for sensor ID: {$hg_device_sensor_id}", __METHOD__);
        }
    }

    public function getModels()
    {
        return HgDeviceSensor::find()
            ->joinWith('hgProductSensor')
            ->andWhere(['hg_product_sensor.type_name'=>HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR])
            ->andWhere(['hg_glozone_id'=>$this->hg_glozone_id])
            ->all();
    }
}
