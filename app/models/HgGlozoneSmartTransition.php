<?php

namespace app\models;

use app\components\HelperComponent;
use Yii;

/**
 * This is the model class for table "hg_glozone_smartTransition".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_glozone_time_block_id
 * @property int|null $hg_device_group_id
 * @property int|null $hg_status_id
 * @property int|null $rank
 * @property string|null $behavior_name
 * @property int|null $last_trigger_at
 * @property string|null $last_trigger_status
 * @property string|null $metadata
 *
 * @property HgDeviceGroup $hgDeviceGroup
 * @property HgGlozoneTimeBlock $hgGlozoneTimeBlock
 * @property HgStatus $hgStatus
 */
class HgGlozoneSmartTransition extends \yii\db\ActiveRecord
{
    const RESULT_STATUS_NOT_INCYCLE = 'RESULT_NOT_IN_CYCLE';
    const RESULT_STATUS_LIGHTS_NOT_ON = 'RESULT_LIGHTS_NOT_ON';
    const RESULT_STATUS_GLO_CHANGE = 'RESULT_STATUS_GLO_CHANGE';
    const RESULT_STATUS_FAILURE = 'RESULT_STATUS_FAILURE';
    const RESULT_STATUS_NO_GLODEVICELIGHTS_FOUND_TO_COMPARE = 'RESULT_STATUS_NO_GLODEVICELIGHTS_FOUND_TO_COMPARE';

    const RESULT_STATUS = [
        self::RESULT_STATUS_NOT_INCYCLE => 'Lights not in cycle',
        self::RESULT_STATUS_LIGHTS_NOT_ON=> 'Lights not on',
        self::RESULT_STATUS_GLO_CHANGE=>'Glo change executed',
        self::RESULT_STATUS_FAILURE=>'Transition failed',
        self::RESULT_STATUS_NO_GLODEVICELIGHTS_FOUND_TO_COMPARE=>'No previous glo lights to compare'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_glozone_smart_transition';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className(),
            [
                'class'=>\app\behaviors\JsonDataBehavior::class
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hg_glozone_time_block_id', 'hg_device_group_id', 'hg_status_id', 'rank', 'last_trigger_at'], 'integer'],
            [['hg_status_id'],'default','value'=>HgStatus::HG_SMARTTRANSITION_ACTIVE],
            [['behavior_name', 'last_trigger_status'], 'string', 'max' => 255],
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
            'hg_glozone_time_block_id' => 'Hg Glozone Time Block ID',
            'hg_device_group_id' => 'Hg Device Group ID',
            'hg_status_id' => 'Hg Status ID',
            'rank' => 'Rank',
            'behavior_name' => 'Behavior Name',
            'last_trigger_at' => 'Last Trigger At',
            'last_trigger_status' => 'Last Trigger Status',
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
     * Gets query for [[HgGlozoneTimeBlock]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneTimeBlock()
    {
        return $this->hasOne(HgGlozoneTimeBlock::class, ['id' => 'hg_glozone_time_block_id']);
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

    /**
     * Gets query for [[HgStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneSmartTransitionExecutes()
    {
        return $this->hasMany(HgGlozoneSmartTransitionExecute::class, ['id' => 'hg_glozone_smart_transition_id']);
    }

    /**
     * Gets query for [[HgStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneSmartTransitionExecuteLast()
    {
        return $this->hasOne(HgGlozoneSmartTransitionExecute::class, ['hg_glozone_smart_transition_id'=>'id'])->orderBy('id DESC');
    }

    public function getTimeStartDefaultFormatted()
    {
        return date('h:i:s A T',$this->updated_at);
    }
    /**
     * Add smart transitions to the db
     * @param HgGlozoneTimeBlock $hgGlozoneTimeBlock
     * @return HgGlozoneSmartTransition[]
     */
    public static function createSmartTransitions(HgGlozoneTimeBlock $hgGlozoneTimeBlock)
    {
        $array = [];
        foreach ($hgGlozoneTimeBlock->hgGlozone->hgHome->hgDeviceGroups as $hgDeviceGroup) {
            if ($hgDeviceGroup->is_room) {

                //don't add if already exist
                if ($existingHgGlozoneSmartTransition = HgGlozoneSmartTransition::find()
                    ->where(['hg_device_group_id'=>$hgDeviceGroup->id,'hg_glozone_time_block_id'=>$hgGlozoneTimeBlock->id])
                    ->one()) {

                    Yii::info($hgGlozoneTimeBlock->display_name.'-'.$hgDeviceGroup->display_name,__METHOD__);

                    //If this room does not exist in the glozone anymore, remove it
                    if ($hgGlozoneTimeBlock->hg_glozone_id != $hgDeviceGroup->hg_glozone_id) {
                        $existingHgGlozoneSmartTransition->delete();
                    }

                    //if it exists and is in the right glozone, continue anyway
                    continue;
                }




                if ($hgGlozoneTimeBlock->smartTransition_behavior == 'inactive') {
                    continue;
                }

                //only create if in this glozone
                if ($hgGlozoneTimeBlock->hg_glozone_id == $hgDeviceGroup->hg_glozone_id) {
                    $hgGLozoneSmartTransition = new HgGlozoneSmartTransition();
                    $hgGLozoneSmartTransition->hg_glozone_time_block_id = $hgGlozoneTimeBlock->id;
                    $hgGLozoneSmartTransition->hg_device_group_id = $hgDeviceGroup->id;
                    $hgGLozoneSmartTransition->rank = $hgDeviceGroup->room_invoke_order;
                    $hgGLozoneSmartTransition->behavior_name = $hgGlozoneTimeBlock->smartTransition_behavior;
                    if (!$hgGLozoneSmartTransition->save()) {
                        Yii::error(HelperComponent::getFirstErrorFromFailedValidation($hgGLozoneSmartTransition),__METHOD__);
                    } else {
                        $array[] = $hgGLozoneSmartTransition;
                    }
                }

            }
        }
        return $array;
    }

    public static function getSchedulerQuery()
    {
        $query = HgGlozoneSmartTransition::find()
            ->joinWith(['hgGlozoneTimeBlock','hgDeviceGroup'])
            ->where(['hg_glozone_smart_transition.hg_status_id' => HgStatus::HG_SMARTTRANSITION_ACTIVE])
            ->orderBy('hg_device_group.room_invoke_order');
        return $query;
    }


}
