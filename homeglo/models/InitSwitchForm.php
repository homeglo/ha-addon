<?php

namespace app\models;

use app\components\HgEngineComponent;
use app\components\HueComponent;
// use app\jobs\InitSwitchJob; // REMOVED: No longer initializing Hue switch configurations
use Yii;
use yii\base\Model;
use yii\helpers\VarDumper;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class InitSwitchForm extends Model
{
    public $hg_hub_ids;
    public $hg_device_sensor_ids;
    public $template_hg_hub_action_map_id;
    public $preserve_buttons;
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
            [['preserve_buttons'],'safe'],
            [['async'],'boolean']
        ];
    }

    public function performInit()
    {
        // TODO: Update for Home Assistant - switch initialization no longer needed
        // Previous logic initialized switches with Hue configurations and preserved button mappings
        // InitSwitchJob removed - no longer setting up Hue sensor rules and action maps
        
        foreach ($this->hg_device_sensor_ids as $hg_device_sensor_id) {
            $hgHubActionMap = HgHubActionMap::findOne($this->template_hg_hub_action_map_id);
            $hgDeviceSensor = HgDeviceSensor::findOne($hg_device_sensor_id);

            $hgHubActionMap->createLocalActionMapFromTemplate($hgDeviceSensor,$this->preserve_buttons);
            $this->copySensorActionTemplates([$hgDeviceSensor->id]);
        }
    }

    public function copySensorActionTemplates($hg_device_sensor_ids=[],$preserve_buttons=[])
    {
        $hgDeviceSensorQuery = HgDeviceSensor::find()
            ->where(['hg_product_sensor.type_name'=>[HgProductSensor::TYPE_NAME_HUE_SWITCH,HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR]])
            ->andWhere(['IS NOT','hg_device_group_id',NULL])
            ->innerJoinWith('hgProductSensor');

        if ($hg_device_sensor_ids) {
            $hgDeviceSensorQuery->andWhere(['hg_device_sensor.id'=>$hg_device_sensor_ids]);
        }

        $array = [];
        foreach ($hgDeviceSensorQuery->all() as $hgDeviceSensor) {

            foreach (HgHubActionTemplate::find()
                         ->where([
                                 'hg_hub_action_map_id'=>$hgDeviceSensor->hgHubActionMap->base_hg_hub_action_map_id,
                                 'hg_status_id'=>HgStatus::HG_ACTION_TEMPLATE_HOMEGLO_DEFAULT]
                         )->all() as $hgHubActionTemplate) {

                $array[] = $hgHubActionTemplate->copyEntireTree($hgDeviceSensor);
            }
        }
        return $array;
    }

    /**
     * @param HgDeviceSensor $hgDeviceSensor
     * @return int
     */
    public function deleteLocalActionMap(HgDeviceSensor $hgDeviceSensor)
    {
        $map_id = $hgDeviceSensor->hg_hub_action_map_id;
        HgHubActionTemplate::deleteAll(['hg_hub_action_map_id'=>$hgDeviceSensor->hg_hub_action_map_id]);
        HgHubActionMap::deleteAll(['id'=>$map_id]);
    }

    public function getModels()
    {
        return HgDeviceSensor::find()
            ->joinWith('hgProductSensor')
            ->andWhere(['hg_product_sensor.type_name'=>HgProductSensor::TYPE_NAME_HUE_SWITCH])
            ->andWhere(['hg_device_sensor.hg_glozone_id'=>$this->hg_glozone_id])
            ->all();
    }
}
