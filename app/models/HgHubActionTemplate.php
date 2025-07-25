<?php

namespace app\models;

use app\components\HelperComponent;
use PHPUnit\TextUI\Help;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "hg_hub_action_template".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_hub_id
 * @property int|null $hg_version_id
 * @property int|null $hg_product_sensor_type_name
 * @property int|null $hg_status_id
 * @property int|null $hg_hub_action_map_id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $platform
 * @property int|null $multi_room
 * @property string|null $metadata
 *
 * @property HgHub $hgHub
 * @property HgHubActionTrigger[] $hgHubActionTriggers
 * @property HgStatus $hgStatus
 * @property HgVersion $hgVersion
 */
class HgHubActionTemplate extends \yii\db\ActiveRecord
{
    const TEMPLATE_SMART_ON_SWITCH_NAME = 'smartOn_shortPress';
    const TEMPLATE_SMART_ON_MOTION_NAME = 'smartOn_motion';
    const TEMPLATE_SMART_ON_MOTION_DIM_HALFWAY_NAME = 'smartDim_halfway';
    const TEMPLATE_SMART_ON_MOTION_DIM_RESTORE_NAME= 'smartDim_restore';

    const TEMPLATE_DEFAULT_SMART_ON_SWITCH_ID = 20;
    const TEMPLATE_DEFAULT_SMART_ON_MOTION_ID = 25;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_hub_action_template';
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
            [[ 'hg_hub_id', 'hg_version_id', 'hg_status_id', 'multi_room','hg_hub_action_map_id'], 'integer'],
            [['metadata'], 'safe'],
            [['name', 'display_name', 'platform','hg_product_sensor_type_name'], 'string', 'max' => 255]
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
            'hg_version_id' => 'Hg Version ID',
            'hg_status_id' => 'Hg Status ID',
            'name' => 'Name',
            'display_name' => 'Display Name',
            'platform' => 'Platform',
            'multi_room' => 'Multi Room',
            'metadata' => 'Metadata',
        ];
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
     * Gets query for [[HgHubActionMap]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionMap()
    {
        return $this->hasOne(HgHubActionMap::class, ['id' => 'hg_hub_action_map_id']);
    }

    /**
     * Gets query for [[HgHubActionTriggers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionTriggers()
    {
        return $this->hasMany(HgHubActionTrigger::class, ['hg_hub_action_template_id' => 'id']);
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
     * Gets query for [[HgVersion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgVersion()
    {
        return $this->hasOne(HgVersion::class, ['id' => 'hg_version_id']);
    }

    public static function getDefaultTemplates($sensor_type_name,$hg_version_id=HgVersion::HG_VERSION_MANUAL_ENTRY)
    {
        return static::find()->where(['hg_version_id'=>$hg_version_id,'hg_status_id'=>HgStatus::HG_ACTION_TEMPLATE_HOMEGLO_DEFAULT,'hg_product_sensor_type_name'=>$sensor_type_name])->all();
    }

    /**
     *
     * the if ($hgDeviceSensor) statements that are littered throughout here are RETARDED and
     * I need to refactor this. it is for copying templates. if ($hgDeviceSensor) THEN IT IS AN ACTUAL INIT SWITCH
     * OTHERWISE we are copying a default template
     *
     * @param HgDeviceSensor|null $hgDeviceSensor
     * @return HgHubActionTemplate
     * @throws \Exception
     */
    public function copyEntireTree(HgDeviceSensor $hgDeviceSensor = null)
    {
        //Template
        $hgHubActionTemplate = new HgHubActionTemplate();
        $hgHubActionTemplate->attributes = $this->attributes;
        $hgHubActionTemplate->hg_hub_id = $hgDeviceSensor->hg_hub_id;
        if ($hgDeviceSensor) {
            $hgHubActionTemplate->hg_status_id = HgStatus::HG_ACTION_TEMPLATE_CLIENT;
            $hgHubActionTemplate->hg_hub_action_map_id = $hgDeviceSensor->hg_hub_action_map_id;
        }

        if (!$hgHubActionTemplate->save()) {
            throw new \Exception(HelperComponent::getFirstErrorFromFailedValidation($hgHubActionTemplate));
        }

        $hgHubActionTemplate->refresh();

        //process productized templates -> smartOn 'smartOn_GloTime'

        //if special function, copy special

        if ($hgDeviceSensor) { //the way i did this is actually retarded.
            switch ($this->name) {
                case HgHubActionTemplate::TEMPLATE_SMART_ON_MOTION_NAME:
                case HgHubActionTemplate::TEMPLATE_SMART_ON_SWITCH_NAME:
                    $this->generateSmartOnTemplateLogic($hgHubActionTemplate, $hgDeviceSensor);
                    break;
                default:
                    $this->generateGenericTemplateLogic($hgHubActionTemplate, $hgDeviceSensor);
            }
        } else {
            $this->cloneTemplateLogic($hgHubActionTemplate);
        }


        return $hgHubActionTemplate;
    }


    public function generateSmartOnTemplateLogic(HgHubActionTemplate $hgHubActionTemplate, HgDeviceSensor $hgDeviceSensor)
    {
        // if $hgDeviceSensor is provided, we are writing to a sensor. otherwise we are copying a template
        //Triggers
        foreach (HgHubActionTrigger::find()->where(['hg_hub_action_template_id'=>$this->id])->all() as $hgHubActionTriggerDefinition) {
            foreach ($hgDeviceSensor->hgDeviceGroup->hgGlozone->hgGlozoneTimeBlocks as $hgGlozoneTimeBlock) {
                switch ($hgDeviceSensor->hgProductSensor->type_name) {
                    case HgProductSensor::TYPE_NAME_HUE_SWITCH:
                        $smartOnActivated = ($hgGlozoneTimeBlock->smartOn_switch_behavior == HgGlozoneTimeBlock::SMARTON_SWITCH_ACTIVE);
                        if ($smartOnActivated)
                            $overrides = ['hg_glozone_end_time_block_id' => $hgGlozoneTimeBlock->getNextSequentialTimeBlock(['smartOn_switch_behavior' => HgGlozoneTimeBlock::SMARTON_SWITCH_ACTIVE])->id];
                        break;
                    case HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR:
                        $smartOnActivated = ($hgGlozoneTimeBlock->smartOn_motion_behavior == HgGlozoneTimeBlock::SMARTON_MOTION_ACTIVE);
                        if ($smartOnActivated)
                            $overrides = ['hg_glozone_end_time_block_id' => $hgGlozoneTimeBlock->getNextSequentialTimeBlock(['smartOn_motion_behavior' => HgGlozoneTimeBlock::SMARTON_MOTION_ACTIVE])->id];
                        break;
                    default:
                        $smartOnActivated = false;
                }

                if ($smartOnActivated) {
                    $overrides = ArrayHelper::merge($overrides, [
                        'name' => $hgHubActionTriggerDefinition->name . '__' . ($hgGlozoneTimeBlock->defaultHgGlo->name),
                        'display_name' => 'On ' . ($hgGlozoneTimeBlock->defaultHgGlo->display_name),
                        'hg_hub_action_template_id' => $hgHubActionTemplate->id,
                        'hg_hub_id' => $hgHubActionTemplate->hg_hub_id,
                        'hg_status_id' => HgStatus::HG_ACTION_TEMPLATE_CLIENT,
                        'hg_device_sensor_id' => $hgDeviceSensor->id,
                        'hg_glozone_start_time_block_id' => $hgGlozoneTimeBlock->id
                    ]);
                    $triggerObject = $hgHubActionTriggerDefinition->cloneTrigger($overrides);

                    //Conditions
                    foreach (HgHubActionCondition::find()->where(['hg_hub_action_trigger_id' => $hgHubActionTriggerDefinition->id])->all() as $hgHubActionCondition) {
                        $hgHubActionCondition->cloneCondition([
                            'hg_hub_action_trigger_id' => $triggerObject->id
                        ]);
                    }

                    //Action Items
                    foreach (HgHubActionItem::find()->where(['hg_hub_action_trigger_id' => $hgHubActionTriggerDefinition->id])->all() as $hgHubActionItem) {

                        $hgHubActionItem->cloneActionItem([
                            'operate_hg_device_light_group_id' => $hgDeviceSensor->hg_device_group_id,
                            'hg_glo_id' => $hgGlozoneTimeBlock->default_hg_glo_id,
                            'hg_hub_action_trigger_id' => $triggerObject->id
                        ]);

                        switch ($hgHubActionItem->operation_name) {
                            case 'set_sensor_state':
                            case 'turn_on_temp_motion_scene':
                            case 'storelightstate':
                                //proceed...these are allowed in multiroom
                                continue 2;
                            default:
                                break;
                        }

                        //Go through each room and create an action
                        foreach ($hgDeviceSensor->hgDeviceGroupMultirooms as $hgDeviceGroupMultiroom) {
                            $hgHubActionItem->cloneActionItem([
                                'operate_hg_device_light_group_id' => $hgDeviceGroupMultiroom->hg_device_group_id,
                                'hg_glo_id' => $hgGlozoneTimeBlock->default_hg_glo_id,
                                'hg_hub_action_trigger_id' => $triggerObject->id
                            ]);
                        }
                    }
                }
            }
        }


    }

    /**
     * @param HgHubActionTemplate $hgHubActionTemplate
     * @throws \Exception
     */
    public function cloneTemplateLogic(HgHubActionTemplate $hgHubActionTemplate) {
        //Triggers
        foreach (HgHubActionTrigger::find()->where(['hg_hub_action_template_id'=>$this->id])->all() as $hgHubActionTrigger) {
            $overrides = [
                'hg_hub_action_template_id'=>$hgHubActionTemplate->id,
                'hg_hub_id'=>$hgHubActionTemplate->hg_hub_id
            ];
            $triggerObject = $hgHubActionTrigger->cloneTrigger($overrides);

            //Conditions
            foreach (HgHubActionCondition::find()->where(['hg_hub_action_trigger_id'=>$hgHubActionTrigger->id])->all() as $hgHubActionCondition) {
                $hgHubActionCondition->cloneCondition([
                    'hg_hub_action_trigger_id'=>$triggerObject->id
                ]);
            }

            //Action Items
            foreach (HgHubActionItem::find()->where(['hg_hub_action_trigger_id'=>$hgHubActionTrigger->id])->all() as $hgHubActionItem) {

                $overrides = [
                    'hg_hub_action_trigger_id'=>$triggerObject->id
                ];

                $hgHubActionItem->cloneActionItem($overrides);
            }
        }
    }

    public function generateGenericTemplateLogic(HgHubActionTemplate $hgHubActionTemplate, HgDeviceSensor $hgDeviceSensor)
    {
        //Triggers
        foreach (HgHubActionTrigger::find()->where(['hg_hub_action_template_id'=>$this->id])->all() as $hgHubActionTrigger) {
            $overrides = [
                'hg_hub_action_template_id'=>$hgHubActionTemplate->id,
                'hg_hub_id'=>$hgHubActionTemplate->hg_hub_id,
                'hg_status_id'=>HgStatus::HG_ACTION_TEMPLATE_CLIENT,
                'hg_device_sensor_id'=>$hgDeviceSensor->id
            ];
            $triggerObject = $hgHubActionTrigger->cloneTrigger($overrides);

            //Conditions
            foreach (HgHubActionCondition::find()->where(['hg_hub_action_trigger_id'=>$hgHubActionTrigger->id])->all() as $hgHubActionCondition) {
                $hgHubActionCondition->cloneCondition([
                    'hg_hub_action_trigger_id'=>$triggerObject->id
                ]);
            }

            //Action Items
            foreach (HgHubActionItem::find()->where(['hg_hub_action_trigger_id'=>$hgHubActionTrigger->id])->all() as $hgHubActionItem) {

                $overrides = [
                    'operate_hg_device_light_group_id'=>$hgDeviceSensor->hg_device_group_id,
                    'hg_hub_action_trigger_id'=>$triggerObject->id
                ];

                //only push through a glo if set
                if ($hgHubActionItem->hg_glo_id) {
                    $hg_glo_id = HgGlo::find()
                        ->where(['base_hg_glo_id'=>$hgHubActionItem->hg_glo_id,
                            'hg_glozone_id'=>$hgDeviceSensor->hgDeviceGroup->hg_glozone_id])
                        ->one()
                        ->id;
                    $overrides['hg_glo_id'] = $hg_glo_id;
                } else {
                    $hg_glo_id = null;
                }

                $hgHubActionItem->cloneActionItem($overrides);

                switch ($hgHubActionItem->operation_name) {
                    case 'set_sensor_state':
                    case 'turn_on_temp_motion_scene':
                    case 'storelightstate':
                        //proceed...these are allowed in multiroom
                        continue 2;
                    default:
                        break;
                }

                foreach ($hgDeviceSensor->hgDeviceGroupMultirooms as $hgDeviceGroupMultiroom) {

                    $overrides = [
                        'hg_glo_id'=>$hg_glo_id,
                        'operate_hg_device_light_group_id'=>$hgDeviceGroupMultiroom->hg_device_group_id,
                        'hg_hub_action_trigger_id'=>$triggerObject->id
                        ];
                    $hgHubActionItem->cloneActionItem($overrides);
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function getHasHueRuleErrors()
    {
        foreach ($this->hgHubActionTriggers as $hgHubActionTrigger) {
            if ($hgHubActionTrigger->hasHueError) {
                return true;
            }
        }

        return false;
    }
}
