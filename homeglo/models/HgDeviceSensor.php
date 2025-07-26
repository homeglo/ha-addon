<?php

namespace app\models;

use app\exceptions\HueApiException;
use app\interfaces\HueApiModelInterface;
// use app\jobs\InitSwitchJob; // REMOVED: No longer initializing Hue switch configurations
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hg_device_sensor".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_hub_id
 * @property string|null $ha_device_id
 * @property string|null $hue_uniqueid
 * @property int|null $hg_glozone_id
 * @property string|null $display_name
 * @property int|null $hg_device_group_id
 * @property int|null $hg_device_group_multiroom_ids
 * @property int|null $hg_product_sensor_id
 * @property int|null $hg_hub_action_map_id
 * @property int|null $hg_device_sensor_placement_id
 * @property int|null $switch_dimmer_increment_percent
 * @property string|null $metadata
 *
 * @property HgDeviceGroup $hgDeviceGroup
 * @property HgDeviceSensorPlacement $hgDeviceSensorPlacement
 * @property HgHub $hgHub
 * @property HgHubActionTrigger[] $hgHubActionTriggers
 * @property HgProductSensor $hgProductSensor
 */
class HgDeviceSensor extends \yii\db\ActiveRecord
{
    const HUE_4BUTTON_SWITCH_IDS = [
        1002 => 'Short Press Top',
        2002 => 'Short Press Brighter',
        3002 => 'Short Press Dimmer',
        4002 => 'Short Press Bottom',

        1001 => 'Long Press Top',
        2001 => 'Long Press Brighter',
        3001 => 'Long Press Dimmer',
        4001 => 'Long Press Bottom'
    ];

    public $hueUpdateableAttributes = ['display_name'];

    public $hue_resource_endpoint = 'sensors';

    public $hg_device_group_multiroom_ids = [];

    public $hub_validate = []; //this is used to validate sensor rules in the hub

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_sensor';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className(),
            [
                'class'=>\app\behaviors\JsonDataBehavior::class,
                'attribute'=>'metadata'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'hg_glozone_id', 'hg_device_group_id', 'hg_product_sensor_id', 'hg_device_sensor_placement_id', 'switch_dimmer_increment_percent'], 'integer'],
            [['ha_device_id'], 'string', 'max' => 255],
            [['display_name'], 'string', 'max' => 255],
            [['hg_device_group_multiroom_ids'], 'each', 'rule'=>['integer']],
       ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'ha_device_id' => 'HA Device ID',
            'display_name' => 'Sensor Name',
            'hg_device_group_id' => 'Hg Device Group ID',
            'hg_product_sensor_id' => 'Hg Product Sensor ID',
            'hg_device_sensor_placement_id' => 'Hg Device Sensor Placement ID',
            'switch_dimmer_increment_percent' => 'Switch Dimmer Increment Percent',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgDeviceGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceGroup()
    {
        return $this->hasOne(HgDeviceGroup::class, ['id' => 'hg_device_group_id']);
    }

    /**
     * Gets query for [[HgHubActionMap]].
     *
     * @return \yii\db\ActiveQuery|HgHubActionMap
     */
    public function getHgHubActionMap()
    {
        return $this->hasOne(HgHubActionMap::class, ['id' => 'hg_hub_action_map_id']);
    }

    /**
     * Gets query for [[HgDeviceSensorPlacement]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensorPlacement()
    {
        return $this->hasOne(HgDeviceSensorPlacement::class, ['id' => 'hg_device_sensor_placement_id']);
    }

    /**
     * Gets query for [[HgGlozone]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozone()
    {
        return $this->hasOne(HgGlozone::class, ['id' => 'hg_glozone_id']);
    }


    /**
     * Gets query for [[HgHubActionTriggers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionTriggers()
    {
        return $this->hasMany(HgHubActionTrigger::class, ['hg_device_sensor_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceSensorVariable]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensorVariables()
    {
        return $this->hasMany(HgDeviceSensorVariable::class, ['hg_device_sensor_id' => 'id']);
    }

    /**
     * @param $variable_name
     * @return HgDeviceSensorVariable|array|\yii\db\ActiveRecord|null
     */
    public function getHgDeviceSensorVariable($variable_name)
    {
        return HgDeviceSensorVariable::find()->where(['hg_device_sensor_id'=>$this->id,'variable_name'=>$variable_name])->one();
    }

    /**
     * Gets query for [[HgDeviceSensorDeviceGroupMultiroom]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceGroupMultirooms()
    {
        return $this->hasMany(HgDeviceSensorDeviceGroupMultiroom::class, ['hg_device_sensor_id' => 'id']);
    }

    /**
     * Gets query for [[HgProductSensor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgProductSensor()
    {
        return $this->hasOne(HgProductSensor::class, ['id' => 'hg_product_sensor_id']);
    }

    /**
     * Gets query for [[HgProductSensor]].
     *
     * @return HgDeviceSensor
     */
    public function getAmbientHgDeviceSensorOne()
    {
        // TODO: Update ambient sensor logic for Home Assistant integration
        // Previous logic relied on Hue's sequential ID pattern (hue_id+1)
        // Need to implement HA-specific ambient sensor detection
        return null;
    }

    public function getHgDeviceGroupMultiRoomOptions()
    {
        return HgDeviceGroup::find()
            ->andWhere(['!=','hg_device_group.id',$this->hg_device_group_id])
            ->andWhere(['hg_glozone_id'=>$this->hgDeviceGroup->hg_glozone_id])
            ->all();
    }

    public function multiroomSet()
    {
        $arr = [];
        foreach ($this->hgDeviceGroupMultirooms as $multiroom) {
            $arr[] = $multiroom->hg_device_group_id;
        }
        $this->hg_device_group_multiroom_ids = $arr;
    }

    public function processHueApiUpdates(string $attribute)
    {
        try {
            switch ($attribute) {
                case 'display_name':
                    // TODO: Update for Home Assistant integration
                    // $this->hgHub->getHueComponent()->v1PutRequest('sensors/'.$this->ha_device_id,['name'=>$this->display_name]);
                    break;
            }
        } catch (HueApiException $e) {
            $this->addError($attribute,$e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * pull from the hg_device_sensor_variable table the correct variables. also take into account model specific vars
     * @param false $formattedForEventData
     * @return array
     */
    public function getDeviceVariablesForUse($formattedForEventData=false)
    {
        $defaults = [];
        //run through model specific overrides
        foreach ($this->hgDeviceSensorVariables as $hgDeviceSensorVariable) {
            $defaults[$hgDeviceSensorVariable->variable_name] = $hgDeviceSensorVariable->value;
        }

        //grab the ambient sensor variables, if this is a motion sensor
        if ($this->hg_product_sensor_id == HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR) {
            // TODO: Update ambient sensor logic for Home Assistant
            $ambientHgDeviceSensor = $this->ambientHgDeviceSensorOne;

            if ($ambientHgDeviceSensor) {
                foreach ($ambientHgDeviceSensor->hgDeviceSensorVariables as $hgDeviceSensorVariable) {
                    $defaults[$hgDeviceSensorVariable->variable_name] = $hgDeviceSensorVariable->value;
                }
            }
        }

        //add the {{ }} to make it a variable
        if ($formattedForEventData) {
            $arr = [];
            foreach ($defaults as $key => $value) {
                $arr['{{'.$key.'}}'] = $value;
            }
            $defaults = $arr;
        }

        return $defaults;
    }


    /**
     * Sync the default level variables with the local level variables
     * @TODO make sure variables currently in-use get treated properly
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function populateDeviceVariables()
    {
        //Get defaults for this device type
        $defaults = HgDeviceSensorVariable::find()
            ->where(['IS','hg_device_sensor_id',NULL])
            ->andWhere(['sensor_type_name'=>$this->hgProductSensor->type_name])
            ->andWhere(['IS','override_hg_product_sensor_id',NULL])
            ->all();

        $exist = [];
        foreach ($defaults as $d) {
            $exist[$d->variable_name] = $d;
        }

        $override_defaults = HgDeviceSensorVariable::find()
            ->where(['IS','hg_device_sensor_id',NULL])
            ->andWhere(['sensor_type_name'=>$this->hgProductSensor->type_name])
            ->andWhere(['override_hg_product_sensor_id'=>$this->hg_product_sensor_id])
            ->all();

        $overrides = [];
        //this will overwrite the model specific vars
        foreach ($override_defaults as $od) {
            $overrides[$od->variable_name] = $od;
        }

        $arr = ArrayHelper::merge($exist,$overrides);

        foreach ($arr as $var_name => $hgDeviceSensorVariable) {

            $existsQuery = HgDeviceSensorVariable::find()
                ->where([
                    'variable_name'=>$hgDeviceSensorVariable->variable_name,
                    'hg_device_sensor_id'=>$this->id
                ]);

            if ($hgDeviceSensorVariable->override_hg_product_sensor_id) {
                $existsQuery->andWhere(['override_hg_product_sensor_id'=>$hgDeviceSensorVariable->override_hg_product_sensor_id]);
            } else {
                $existsQuery->andWhere(['IS','override_hg_product_sensor_id',NULL]);
            }

            if ($existsQuery->exists()) {
                //if the variable already exists, do nothing
            } else {
                //Create a new variable for this sensor
                $var = new HgDeviceSensorVariable();
                $var->attributes = $hgDeviceSensorVariable->attributes;
                $var->hg_device_sensor_id = $this->id;
                $var->hg_status_id = HgStatus::HG_USER_SENSOR_VARIABLE;
                $var->save();
            }
        }

        //Clean up any vars that are base level, where override is necessary
        foreach ($this->getHgDeviceSensorVariables()
                     ->andWhere(['IS','override_hg_product_sensor_id',NULL])
                     ->all() as $hgDeviceSensorVariable) {

            if (HgDeviceSensorVariable::find()
                ->where([
                    'hg_device_sensor_id'=>$this->id,
                    'variable_name'=>$hgDeviceSensorVariable->variable_name,
                    'override_hg_product_sensor_id'=>$this->hg_product_sensor_id])
                ->one()) { //if a variable exists of the same, without override_product, delete it
                $hgDeviceSensorVariable->delete();
            }
        }
    }

    /**
     * @param array $hueRules
     * @return false|void
     */
    public function validateHueHubData(array $hueRules, array $hueSensorSet, array $hueSceneSet)
    {
        $localRules = $this->hgHubActionMap->localRules;

        $this->hub_validate['rules'] = false;
        $this->hub_validate['sensor_vars'] = false;
        $this->hub_validate['scene_vars'] = false;
        $this->hub_validate['motion_warning_timers'] = false;

        //print_r($hueRules);exit;


        foreach ($localRules as $local_hue_id => $hgHubActionTrigger) {
            $local_rule_name = $hgHubActionTrigger->display_name;

            if (!isset($hueRules[$local_hue_id])) {
                //print_r($hueRules);exit;
                Yii::info('Rule Not Found in Hub:[HgHubActionTrigger:'.$hgHubActionTrigger->id.'HubId:'.$hgHubActionTrigger->hue_id.'-'.$this->display_name.'] -> '.$local_rule_name,__METHOD__);
                $this->hub_validate['rules'] = false;
                break;
            }
            $this->hub_validate['rules'] = true;
        }

        //validate temp sensor vars
        if (isset($hueSensorSet[$this->hue_sensor_variable_id])) {
            $this->hub_validate['sensor_vars'] = true;
        }

        //validate temp scene vars - TODO: Remove for Home Assistant integration
        // if (isset($hueSceneSet[$this->hue_motion_scene_id])) {
        //     $this->hub_validate['scene_vars'] = true;
        // }

        //validate motion warning timer values
        foreach ($localRules as $local_hue_id => $hgHubActionTrigger) {
            foreach ($hgHubActionTrigger->hgHubActionConditions as $hgHubActionCondition) {
                if (stripos($hgHubActionCondition->value,'{{motion_warning_timer}}') !== FALSE) {
                    foreach ($hueRules[$hgHubActionTrigger->hue_id] as $hueKey => $hueValue) {
                        if ($hueKey=='conditions') {
                            foreach ($hueValue as $condition_id => $conditionObject) {
                                if (stripos($conditionObject['value'],'PT') !== FALSE) {
                                    if ($conditionObject['value'] == $this->getHgDeviceSensorVariable(HgDeviceSensorVariable::MOTION_WARNING_TIMER)->value) {
                                        $this->hub_validate['motion_warning_timers'] = true;
                                        break 3;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            $this->populateDeviceVariables();
        }

        if (!$insert) {
            if (array_key_exists('hg_device_group_id', $changedAttributes)) {

                $this->hg_glozone_id = HgDeviceGroup::findOne($this->hg_device_group_id)->hg_glozone_id;
                $this->save(false);

                if (!$this->hg_hub_action_map_id) {
                    return -1; //this switch is not programmed, no need to do anything
                }
                // TODO: Update for Home Assistant - sync down functionality no longer needed
                // Previous logic synced Hue hub state down after device group change - replace with HA integration if needed
                // SyncDownJob removed - no longer syncing from Hue hub

                // TODO: Update for Home Assistant - switch initialization no longer needed
                // Previous logic initialized Hue switch configurations and rules - replace with HA integration if needed
                // InitSwitchJob removed - no longer setting up Hue switch rules
            }
        }



    }
}
