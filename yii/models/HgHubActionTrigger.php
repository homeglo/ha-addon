<?php

namespace app\models;

use app\components\HelperComponent;
use Yii;
use yii\caching\ArrayCache;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hg_hub_action_trigger".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $source_name
 * @property string|null $event_name short_press, long_press, etc
 * @property string|null $event_data
 * @property int|null $hg_hub_id
 * @property string|null $ha_device_id
 * @property int|null $hg_device_sensor_id
 * @property int|null $hg_glozone_start_time_block_id
 * @property int|null $hg_glozone_end_time_block_id
 * @property int|null $hg_hub_action_template_id
 * @property int|null $hg_status_id
 * @property int|null $rank
 * @property string|null $metadata
 *
 * @property HgDeviceSensor $hgDeviceSensor
 * @property HgGlozoneTimeBlock $hgGlozoneEndTimeBlock
 * @property HgGlozoneTimeBlock $hgGlozoneStartTimeBlock
 * @property HgHub $hgHub
 * @property HgHubActionCondition[] $hgHubActionConditions
 * @property HgHubActionItem[] $hgHubActionItems
 * @property HgHubActionTemplate $hgHubActionTemplate
 * @property HgStatus $hgStatus
 */
class HgHubActionTrigger extends \yii\db\ActiveRecord
{
    const SMARTON_SHORT_PRESS_TOP = 50;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_hub_action_trigger';
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
            [['event_data'], 'safe'],
            [['hg_hub_id', 'hg_device_sensor_id', 'hg_glozone_start_time_block_id', 'hg_glozone_end_time_block_id', 'hg_hub_action_template_id', 'hg_status_id', 'rank'], 'integer'],
            [['ha_device_id'], 'string', 'max' => 255],
            [['name', 'display_name', 'source_name', 'event_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'display_name' => 'Trigger Name',
            'source_name' => 'Source Name',
            'event_name' => 'Event Name',
            'event_data' => 'Event Data',
            'hg_hub_id' => 'Hg Hub ID',
            'ha_device_id' => 'HA Device ID',
            'hg_device_sensor_id' => 'Hg Device Sensor ID',
            'hg_glozone_start_time_block_id' => 'Hg Glozone Start Time Block ID',
            'hg_glozone_end_time_block_id' => 'Hg Glozone End Time Block ID',
            'hg_hub_action_template_id' => 'Hg Hub Action Template ID',
            'hg_status_id' => 'Hg Status ID',
            'rank' => 'Rank',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgDeviceSensor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensor()
    {
        return $this->hasOne(HgDeviceSensor::class, ['id' => 'hg_device_sensor_id']);
    }

    /**
     * Gets query for [[HgGlozoneEndTimeBlock]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneEndTimeBlock()
    {
        return $this->hasOne(HgGlozoneTimeBlock::class, ['id' => 'hg_glozone_end_time_block_id']);
    }

    /**
     * Gets query for [[HgGlozoneStartTimeBlock]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneStartTimeBlock()
    {
        return $this->hasOne(HgGlozoneTimeBlock::class, ['id' => 'hg_glozone_start_time_block_id']);
    }

    /**
     * Gets query for [[HgHub]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHub()
    {
        return $this->hasOne(HgHub::class, ['id' => 'hg_hub_id']);
    }

    /**
     * Gets query for [[HgHubActionConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionConditions()
    {
        return $this->hasMany(HgHubActionCondition::class, ['hg_hub_action_trigger_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActionItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionItems()
    {
        return $this->hasMany(HgHubActionItem::class, ['hg_hub_action_trigger_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActionTemplate]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionTemplate()
    {
        return $this->hasOne(HgHubActionTemplate::class, ['id' => 'hg_hub_action_template_id']);
    }

    /**
     * Gets query for [[HgStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgStatus()
    {
        return $this->hasOne(HgStatus::class, ['id' => 'hg_status_id']);
    }

    public function getGenerateHueRuleName()
    {
        $sensor_hue_id = $this->hgDeviceSensor->hue_id;
        return substr(strtolower(str_replace(" ","_",$sensor_hue_id)).'-'.strtolower(str_replace(" ","_",$this->hgHubActionTemplate->display_name)),0,24);
    }

    /**
     * @return array
     * Create a hue rule ready to be pushed
     */
    public function prepHueRule()
    {
        $data = [
            "name"=> $_ENV['ENV'].'-'.$this->generateHueRuleName,
            'conditions'=>[],
            'actions'=>[]
        ];

        //Populate conditions
        foreach ($this->hgHubActionConditions as $hgHubActionCondition) {
            $event_data = $hgHubActionCondition->populateEventData();
            $c = [];
            $c['address'] = trim(strtr($hgHubActionCondition->property,$event_data));
            $c['operator'] = $hgHubActionCondition->operator;
            if ($hgHubActionCondition->value !== NULL) {
                $c['value'] = trim(strtr($hgHubActionCondition->value,$event_data));
            }

            $data['conditions'][] = $c;

            /* Debating whether or not to save the populated values in the table
                $hgHubActionCondition->property = $c['address'];
                $hgHubActionCondition->value = $c['value'] ?? NULL;
                $hgHubActionCondition->save();
            */

            unset($c);
        }

        //Populate actions
        foreach ($this->hgHubActionItems as $hgHubActionItem) {
            $event_data = $hgHubActionItem->populateEventData();
            Yii::info('Processing Action:'.$hgHubActionItem->id,__METHOD__);
            $a = [];
            $a['address'] = trim(strtr($hgHubActionItem->entity,$event_data));

            switch ($hgHubActionItem->operation_name) {
                default:
                    $a['method'] = 'PUT';
            }

            $overrides = [];

            //sub transition duration ms
            if ($hgHubActionItem->override_transition_duration_ms) {
                if (is_int($hgHubActionItem->override_transition_duration_ms))
                    $overrides['transitiontime'] = $hgHubActionItem->override_transition_duration_ms/100;
                else {
                    $overrides['transitiontime'] = strtr(trim($hgHubActionItem->override_transition_duration_ms),$event_data)/100;
                }
            }
            
            //sub bri
            if ($bri = $hgHubActionItem->override_bri_increment_percent) {
                if (is_int($bri)) {
                    //do nothing
                } else {
                    $bri = (int) strtr($bri,$event_data);
                }
            }



            switch ($hgHubActionItem->operation_name) {
                case 'turn_on_scene':
                    //turn on the "off" scene, which is turning off all the lights
                    if ($event_data['{{hue_scene_id}}'] == HgHubActionItem::ACTION_HG_OFF_NAME) {
                        $a['body'] = [
                            'on'=>false,
                        ];
                    } else {
                        $a['body'] = [
                            'scene'=>$event_data['{{hue_scene_id}}']
                        ];
                    }
                    break;
                case 'adjust_brightness':
                    $a['body'] = [
                        'bri_inc'=>ceil($bri*254/100),
                    ];
                    break;
                case 'set_sensor_state':
                case 'set_deviceGroup_sensor_state':
                    $a['body'] = $hgHubActionItem->operation_value_json;
                    break;
                case 'turn_off_room':
                    $a['body'] = [
                        'on'=>false,
                    ];
                    break;
                case 'storelightstate':
                case 'storelightstate_deviceGroup':
                    $a['body'] = [
                        'storelightstate'=>true,
                    ];
                    break;
                case 'turn_on_temp_motion_scene':
                    // TODO: Update for Home Assistant - motion scene actions no longer used
                    $a['body'] = [];
                    break;
                case 'turn_on_temp_deviceGroup_scene':
                    // TODO: Update for Home Assistant - motion scene actions no longer used  
                    $a['body'] = [];
                    break;
            }
            $a['body'] = ArrayHelper::merge($a['body'],$overrides);
            Yii::info('Action:'.json_encode($a),__METHOD__);
            $data['actions'][] = $a;

            /* Debating whether or not to save the populated values in the table
                $hgHubActionItem->entity = $a['address'];
                $hgHubActionItem->operation_value_json = $a['body'];
                $hgHubActionItem->save();
            */

            unset($a);
        }
        Yii::info('Full Actions + Conditions:'.json_encode($data),__METHOD__);
        return $data;
    }

    /**
     * @return HgHubActionTrigger
     */
    public function cloneTriggerConditionsItems()
    {
        $triggerObject = new HgHubActionTrigger();
        $triggerObject->attributes = $this->attributes;
        $triggerObject->display_name = $this->display_name.' CLONE';
        $triggerObject->save();

        foreach ($this->hgHubActionConditions as $hgHubActionCondition) {
            $hgHubActionConditionObject = new HgHubActionCondition();
            $hgHubActionConditionObject->attributes = $hgHubActionCondition->attributes;
            $hgHubActionConditionObject->hg_hub_action_trigger_id = $triggerObject->id;
            $hgHubActionConditionObject->save();
        }

        foreach ($this->hgHubActionItems as $hgHubActionItem) {
            $hgHubActionItemObject = new HgHubActionItem();
            $hgHubActionItemObject->attributes = $hgHubActionItem->attributes;
            $hgHubActionItemObject->hg_hub_action_trigger_id = $triggerObject->id;
            $hgHubActionItemObject->save();
        }

        $triggerObject->refresh();
        return $triggerObject;

    }

    public function cloneTrigger($overrides = [])
    {
        $triggerObject = new HgHubActionTrigger();
        $triggerObject->attributes = $this->attributes;
        $triggerObject->hg_hub_action_template_id = $this->hg_hub_action_template_id;
        $triggerObject->hg_status_id = HgStatus::HG_ACTION_TEMPLATE_HOMEGLO_DEFAULT;
        $triggerObject->setAttributes($overrides);
        if (!$triggerObject->save()) {
            Yii::error(HelperComponent::getFirstErrorFromFailedValidation($triggerObject),__METHOD__);
            throw new \Exception(HelperComponent::getFirstErrorFromFailedValidation($triggerObject));
        }

        return $triggerObject;
    }

    /**
     * @return bool
     */
    public function getHasVariableTimeVariables()
    {
        if ($this->hgGlozoneStartTimeBlock->hasTimeVariables ||
            $this->hgGlozoneEndTimeBlock->hasTimeVariables)
            return true;

        return false;
    }

    public function getHueButtonId()
    {
        return $this->event_data['{{hue_switch_button_id}}'];
    }

    /**
     * @param $variable_name
     * @return bool
     */
    public function getHasEventDataProperty($variable_name)
    {
        foreach ($this->hgHubActionConditions as $hgHubActionCondition) {
            if (stripos($hgHubActionCondition->property,$variable_name) !== FALSE) {
                return true;
            }

            if (stripos($hgHubActionCondition->value,$variable_name) !== FALSE) {
                return true;
            }
        }

        foreach ($this->hgHubActionItems as $hgHubActionItem) {
            if (stripos($hgHubActionItem->entity,$variable_name) !== FALSE) {
                return true;
            }

            if (stripos(json_encode($hgHubActionItem->operation_value_json),$variable_name) !== FALSE) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function getHasHueError()
    {
        return (bool)$this->getJsonData('error');
    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        return true;
    }

}
