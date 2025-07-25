<?php

namespace app\models;

use app\components\HelperComponent;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hg_hub_action_condition".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_hub_action_trigger_id
 * @property int|null $hg_status_id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $property
 * @property string|null $operator
 * @property string|null $value
 * @property string|null $metadata
 *
 * @property HgHubActionTrigger $hgHubActionTrigger
 * @property HgStatus $hgStatus
 */
class HgHubActionCondition extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_hub_action_condition';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'hg_hub_action_trigger_id', 'hg_status_id'], 'integer'],
            [['name', 'display_name', 'property', 'operator', 'value'], 'string', 'max' => 255],
            [['value'],'default','value'=>NULL],
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'hg_hub_action_trigger_id' => 'Hg Hub Action Trigger ID',
            'hg_status_id' => 'Hg Status ID',
            'name' => 'Name',
            'display_name' => 'Display Name',
            'property' => 'Property',
            'operator' => 'Operator',
            'value' => 'Value',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgHubActionTrigger]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionTrigger()
    {
        return $this->hasOne(HgHubActionTrigger::class, ['id' => 'hg_hub_action_trigger_id']);
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

    public function cloneCondition($overrides=[])
    {
        $conditionObject = new HgHubActionCondition();
        $conditionObject->attributes = $this->attributes;
        $conditionObject->hg_hub_action_trigger_id = $this->hg_hub_action_trigger_id;
        $conditionObject->setAttributes($overrides);
        if (!$conditionObject->save()) {
            Yii::error(HelperComponent::getFirstErrorFromFailedValidation($conditionObject),__METHOD__);
            throw new \Exception(HelperComponent::getFirstErrorFromFailedValidation($conditionObject));
        }
    }

    public function populateEventData()
    {
        $hgDeviceSensor = $this->hgHubActionTrigger->hgDeviceSensor;
        $arr = ArrayHelper::merge([
                '{{hg_device_group[0].hue_id}}'  => $this->hgHubActionTrigger->hgHubActionItems[0]->operateHgDeviceLightGroup->hue_id,
                '{{hg_device_group.hue_motion_variable_id}}'  => $hgDeviceSensor->hgDeviceGroup->hue_motion_variable_id,
                // TODO: Update for Home Assistant - hue_motion_scene_id no longer used

                '{{hg_device_sensor.hue_id}}' =>$hgDeviceSensor->hue_id,
                // TODO: Update for Home Assistant - hue_sensor_variable_id no longer used
                '{{hg_device_sensor.hue_ambient_sensor_id}}'=>$hgDeviceSensor->ambientHgDeviceSensorOne->hue_id,

                '{{hgGlozoneTimeBlockStartTime}}'=>$this->hgHubActionTrigger->hgGlozoneStartTimeBlock->timeStartDefaultHueFormatted,
                '{{hgGlozoneTimeBlockEndTime}}'=>$this->hgHubActionTrigger->hgGlozoneEndTimeBlock->timeStartDefaultHueFormatted
            ],
            (array)$this->hgHubActionTrigger->event_data,
            $hgDeviceSensor->getDeviceVariablesForUse($formattedForEventData=true));

        return $arr;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $n = strtolower(str_replace(' ','_',explode('/',$this->property)[sizeof(explode('/',$this->property))-1]));
            $this->name = 'condition_'.$n;
            $this->display_name = 'Condition '.$n;

            return true;
        } else {
            return false;
        }
    }

}