<?php

namespace app\models;

use app\components\HelperComponent;
use app\exceptions\HueSceneDoesNotExistInRoomException;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hg_hub_action_item".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_hub_action_trigger_id
 * @property string|null $entity
 * @property string|null $operation_name
 * @property string|null $operation_value_json
 * @property int|null $operate_hg_device_light_group_id
 * @property int|null $hg_glo_id
 * @property string|null $display_name
 * @property float|null $override_hue_x
 * @property float|null $override_hue_y
 * @property int|null $override_bri_absolute
 * @property int|null $override_bri_increment_percent
 * @property int|null $override_transition_duration_ms
 * @property int|null $override_transition_at_time
 * @property string|null $metadata
 *
 * @property HgGlo $hgGlo
 * @property HgHubActionTrigger $hgHubActionTrigger
 * @property HgDeviceGroup $operateHgDeviceLightGroup
 */
class HgHubActionItem extends \yii\db\ActiveRecord
{
    const OPERATION_NAMES = [
        'turn_on_scene'=>'Turn On Scene',
        'turn_off_room'=>'Turn Off Room',
        'adjust_brightness'=>'Adjust Brightness',

        'set_sensor_state'=>'Set Device Sensor State',
        'set_deviceGroup_sensor_state'=>'Set Device Group State',

        'storelightstate'=>'Store Light State (one room)',
        'storelightstate_deviceGroup'=>'Store Light State (each room)',

        'turn_on_temp_motion_scene'=>'Turn on Temp Motion Scene',
        'turn_on_temp_deviceGroup_scene'=>'Turn on deviceGroup Motion Scene'
    ];

    const ACTION_HG_OFF_NAME = 'hg_off';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_hub_action_item';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[ 'hg_hub_action_trigger_id', 'operate_hg_device_light_group_id', 'hg_glo_id', 'override_bri_absolute', 'override_transition_at_time'], 'integer'],
            [['override_hue_x', 'override_hue_y'], 'number'],
            [['entity', 'operation_name', 'display_name'], 'string', 'max' => 255],
            [['operation_value_json'],'default','value'=>null],
            [[ 'override_transition_duration_ms','override_bri_increment_percent','operation_value_json'],'safe']
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
            'entity' => 'Entity',
            'operation_name' => 'Operation Name',
            'operation_value_json' => 'Operation Value Json',
            'operate_hg_device_light_group_id' => 'Operate Hg Device Light Group ID',
            'hg_glo_id' => 'Hg Glo ID',
            'display_name' => 'Display Name',
            'override_hue_x' => 'Override Hue X',
            'override_hue_y' => 'Override Hue Y',
            'override_bri_absolute' => 'Override Bri Absolute',
            'override_bri_increment_percent' => 'Override Bri Increment Percent',
            'override_transition_duration_ms' => 'Override Transition Duration Ms',
            'override_transition_at_time' => 'Override Transition At Time',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgGlo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlo()
    {
        return $this->hasOne(HgGlo::class, ['id' => 'hg_glo_id']);
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
     * Gets query for [[OperateHgDeviceLightGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOperateHgDeviceLightGroup()
    {
        return $this->hasOne(HgDeviceGroup::class, ['id' => 'operate_hg_device_light_group_id']);
    }

    public function cloneActionItem($overrides=[])
    {
        $actionItemObject = new HgHubActionItem();
        $actionItemObject->attributes = $this->attributes;
        $actionItemObject->hg_hub_action_trigger_id = $this->hg_hub_action_trigger_id;
        $actionItemObject->setAttributes($overrides);
        if (!$actionItemObject->save()) {
            Yii::error(HelperComponent::getFirstErrorFromFailedValidation($actionItemObject),__METHOD__);
            throw new \Exception(HelperComponent::getFirstErrorFromFailedValidation($actionItemObject));
        }

        return $actionItemObject;
    }

    public function populateEventData()
    {
        if ($this->hgGlo->isOffGlo) {
            $hue_scene_id = HgHubActionItem::ACTION_HG_OFF_NAME;
        } else if ($this->hg_glo_id) { //this action requires a glo
            $hue_scene_id = HgGloDeviceGroup::find()
                ->where(['hg_device_group_id'=>$this->operate_hg_device_light_group_id,'hg_glo_id'=>$this->hg_glo_id,'hg_hub_id'=>$this->hgHubActionTrigger->hg_hub_id])
                ->one()
                ->hue_scene_id;

            if (!$hue_scene_id)
                throw new HueSceneDoesNotExistInRoomException('Unable to find (Glo ID:'.$this->hgGlo->display_name.') id for room: '.$this->operateHgDeviceLightGroup->display_name);
        }

        $hgDeviceSensor = $this->hgHubActionTrigger->hgDeviceSensor;
        $arr = ArrayHelper::merge(
            [
                '{{hg_device_group.hue_id}}'  => $this->operateHgDeviceLightGroup->hue_id,
                '{{hg_device_group.hue_motion_variable_id}}'  => $this->operateHgDeviceLightGroup->hue_motion_variable_id,
                // TODO: Update for Home Assistant - hue_motion_scene_id no longer used

                '{{hg_device_sensor.hue_id}}' =>$hgDeviceSensor->hue_id,
                // TODO: Update for Home Assistant - hue_sensor_variable_id no longer used
                // TODO: Update for Home Assistant - hue_motion_scene_id no longer used
                '{{hg_device_sensor.hue_ambient_sensor_id}}'=>$hgDeviceSensor->ambientHgDeviceSensorOne->hue_id,

                '{{hgGlozoneTimeBlockStartTime}}'=>$this->hgHubActionTrigger->hgGlozoneStartTimeBlock->timeStartDefaultHueFormatted,
                '{{hgGlozoneTimeBlockEndTime}}'=>$this->hgHubActionTrigger->hgGlozoneStartTimeBlock->nextSequentialTimeBlock->timeStartDefaultHueFormatted,
                '{{hue_scene_id}}'=> $hue_scene_id
            ],
            (array)$this->hgHubActionTrigger->event_data,
            $hgDeviceSensor->getDeviceVariablesForUse($formattedForEventData=true));

        return $arr;
    }

    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            $this->display_name = ucwords(str_replace('_',' ',$this->operation_name));

            return true;
        } else {
            return false;
        }
    }
}
