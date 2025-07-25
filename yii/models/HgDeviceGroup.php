<?php

namespace app\models;

use app\components\HgEngineComponent;
// use app\components\HueSyncComponent; // REMOVED: Sync component no longer needed
use app\exceptions\HueApiException;
use app\interfaces\HueApiModelInterface;
use app\jobs\InitGloJob;
// use app\jobs\InitSwitchJob; // REMOVED: No longer initializing Hue switch configurations
use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "hg_device_light_group".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_hub_id
 * @property int|null $hg_device_group_type_id
 * @property int|null $hg_glozone_id
 * @property string|null $hue_motion_variable_id
 * @property string|null $ha_device_id
 * @property int|null $room_invoke_order
 * @property string|null $display_name
 * @property string|null $metadata
 *
 * @property HgDeviceGroupLight[] $hgDeviceLightGroupLights
 * @property HgHub $hgHub
 * @property HgHubActionItem[] $hgHubActionItems
 */
class HgDeviceGroup extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_group';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className(),
            [
                'class'=>\app\behaviors\JsonDataBehavior::class,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hg_hub_id','hg_device_group_type_id','hg_glozone_id','room_invoke_order'], 'integer'],
            [['ha_device_id'], 'string', 'max' => 255],
            [['room_invoke_order'],'default','value'=>0],
            [['display_name'], 'string', 'max' => 255],
            [['hue_motion_variable_id'],'safe']
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
            'hg_hub_id' => 'Hg Hub ID',
            'ha_device_id' => 'HA Device ID',
            'display_name' => 'Group/Room Name',
            'metadata' => 'Metadata',
        ];
    }

    public static function findByHgHubId($id)
    {
        return static::find()->where(['hg_hub_id'=>$id])->all();
    }

    /**
     * Gets query for [[HgDeviceLightGroupLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLightGroupLights()
    {
        return $this->hasMany(HgDeviceGroupLight::className(), ['hg_device_group_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceLightGroupLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLights()
    {
        return $this->hasMany(HgDeviceLight::className(), ['primary_hg_device_group_id' => 'id']);
    }

    /**
     * Gets query for [[HgHub]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceGroupType()
    {
        return $this->hasOne(HgDeviceGroupType::className(), ['id' => 'hg_device_group_type_id']);
    }

    /**
     * Gets query for [[HgHub]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHub()
    {
        return $this->hasOne(HgHub::className(), ['id' => 'hg_hub_id']);
    }

    /**
     * Gets query for [[HgHub]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozone()
    {
        return $this->hasOne(HgGlozone::className(), ['id' => 'hg_glozone_id']);
    }

    /**
     * @return HgGlozone[]|array|\yii\db\ActiveRecord[]
     */
    public function getAvailableGlozones()
    {
        return HgGlozone::find()->where(['hg_home_id'=>$this->hgHub->hg_home_id])->all();
    }

    /**
     * Gets query for [[HgHubActionItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionItems()
    {
        return $this->hasMany(HgHubActionItem::className(), ['operate_hg_device_light_group_id' => 'id']);
    }

    /**
     * Gets query for [[HgGloDeviceGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGloDeviceGroups()
    {
        return $this->hasMany(HgGloDeviceGroup::class, ['hg_device_group_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceSensors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensors()
    {
        return $this->hasMany(HgDeviceSensor::class, ['hg_device_group_id' => 'id']);
    }

    public function getArrayOfHueIds()
    {
        $arr = [];
        foreach ($this->hgDeviceLights as $hgDeviceLight) {
            $arr[] = (string) $hgDeviceLight->hue_id;
        }

        return $arr;
    }

    public function getActiveGloInHue($hueLightsData)
    {
        $engine = new HgEngineComponent($this->hg_hub_id,$this->id);
        $glos = [];

        /* @var HgGlozoneTimeBlock $hgGlozoneTimeBlockActive */
        $hgGlozoneTimeBlockActive = $this->hgGlozone->activeTimeBlock;

        /* @var HgGlo $hgGlo */
        foreach ($this->hgGlozone->hgGlos as $hgGlo) {
            if ($hgGlo->getIsOffGlo()) {
                $offGlo = $hgGlo;
                continue;
            }


            $devices = HgGloDeviceLight::find()->where([
                'hg_device_group_id'=>$this->id,
                'hg_glo_id'=>$hgGlo->id
            ])->all();

            if ($r = $engine->compareInCycleColors($devices,$hueLightsData)) {
                if ($r!==0) { //if $r===0 lights are off
                    if ($hgGlozoneTimeBlockActive->default_hg_glo_id == $hgGlo->id) {
                        return $hgGlo;
                    } else {
                        $glos[] = $hgGlo;
                    }
                }
            }
        }

        if (empty($glos) && $this->hgHub->getIsReachable()) { //see if the room is off
            $state = $this->hgHub->getHueComponent()->v1GetRequest('groups/'.$this->hue_id);
            if ($state['state']['any_on'] === FALSE)
                return $offGlo;
        }

        return $glos[0];
    }


    /**
     * this is beforesave
     * @param string $attribute
     * @return bool
     */
    public function processHueApiUpdates(string $attribute)
    {
        try {
            switch ($attribute) {
                case 'display_name':
                    $this->hgHub->getHueComponent()->v1PutRequest('groups/'.$this->hue_id,['name'=>$this->display_name]);
                    break;
                case 'hg_device_group_type_id':
                    $hgDeviceGroupType = HgDeviceGroupType::findOne($this->hg_device_group_type_id);
                    if ($hgDeviceGroupType) {
                        $this->hgHub->getHueComponent()->v1PutRequest('groups/'.$this->hue_id,['class'=>$hgDeviceGroupType->hue_class_name]);
                    }

                    break;
            }
        } catch (HueApiException $e) {
            $this->addError($attribute,$e->getMessage());
            return false;
        }

        return true;
    }

    public function afterSave($insert, $changedAttributes) {

        if ($insert) {
            //sync timeblocks on insert, create smart transitions for this room
            HgGlozoneTimeBlock::syncTimeBlocks($this->hgHub->hgHome->getDefaultGlozone());
        }

        if (!$insert) {
            if (array_key_exists('hg_glozone_id', $changedAttributes)) {

                $this->hgGlozone->updateBulbStartupModeAcrossGlozone();

                //we have to add/remove transitions here
                HgGlozoneTimeBlock::syncTimeBlocks(HgGlozone::findOne($this->hg_glozone_id));
                HgGlozoneTimeBlock::syncTimeBlocks(HgGlozone::findOne($changedAttributes['hg_glozone_id']));

                /* @var \app\models\HgDeviceSensor $hgDeviceSensor */
                foreach ($this->hgDeviceSensors as $hgDeviceSensor) { //re initialize switches

                    $hgDeviceSensor->hg_glozone_id = $this->hg_glozone_id;
                    $hgDeviceSensor->save();

                    if (!$hgDeviceSensor->hg_hub_action_map_id) {
                        continue; //this switch is not programmed, no need to do anything
                    }

                }
            }
        }
    }
}
