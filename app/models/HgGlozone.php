<?php

namespace app\models;

use app\components\HgEngineComponent;
// use app\jobs\AsyncHueRequestJob; // REMOVED: No longer pushing async updates to Hue hub
use app\jobs\SyncGlozoneJob;
// use app\jobs\WriteSmartOnHueRulesJob; // REMOVED: No longer writing Hue rules
use Yii;

/**
 * This is the model class for table "hg_glozone".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_home_id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $bed_time_weekday_midnightmins
 * @property string|null $wake_time_weekday_midnightmins
 * @property string|null $bed_time_weekend_midnightmins
 * @property string|null $wake_time_weekend_midnightmins
 * @property string|null $bulb_startup_mode_hg_status_id
 * @property string|null $metadata
 *
 * @property HgDeviceGroup[] $hgDeviceGroups
 * @property HgGlo[] $hgGlos
 * @property HgGlozoneTimeBlock[] $hgGlozoneTimeBlocks
 * @property HgHome $hgHome
 */
class HgGlozone extends \yii\db\ActiveRecord
{
    const HG_DEFAULT_GLOZONE = 1;

    /**
     * glozone template to copy if we are doing that
     * @var null
     */
    public $template_glozone_id = null;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_glozone';
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
            [['hg_home_id','bulb_startup_mode_hg_status_id','template_glozone_id'], 'integer'],
            [['name', 'display_name', 'bed_time_weekday_midnightmins', 'wake_time_weekday_midnightmins', 'bed_time_weekend_midnightmins', 'wake_time_weekend_midnightmins'], 'string', 'max' => 255],
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
            'hg_home_id' => 'Hg Home ID',
            'name' => 'Name',
            'display_name' => 'Glozone Name',
            'bed_time_weekday_midnightmins' => 'Bed Time Weekday Midnightmins',
            'wake_time_weekday_midnightmins' => 'Wake Time Weekday Midnightmins',
            'bed_time_weekend_midnightmins' => 'Bed Time Weekend Midnightmins',
            'wake_time_weekend_midnightmins' => 'Wake Time Weekend Midnightmins',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgDeviceGroups]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceGroups()
    {
        return $this->hasMany(HgDeviceGroup::class, ['hg_glozone_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceSensors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensors()
    {
        return $this->hasMany(HgDeviceSensor::class, ['hg_glozone_id' => 'id']);
    }

    /**
     * Gets query for [[HgGlos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlos()
    {
        return $this->hasMany(HgGlo::class, ['hg_glozone_id' => 'id']);
    }

    /**
     * Gets query for [[HgGlozoneTimeBlocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneTimeBlocks()
    {
        return $this->hasMany(HgGlozoneTimeBlock::class, ['hg_glozone_id' => 'id']);
    }

    /**
     * Gets query for [[HgHome]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHome()
    {
        return $this->hasOne(HgHome::class, ['id' => 'hg_home_id']);
    }

    /**
     * Gets query for [[HgStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgBulbStartupMode()
    {
        return $this->hasOne(HgStatus::class, ['id' => 'bulb_startup_mode_hg_status_id']);
    }

    public function getHgHubs()
    {
        return HgHub::find()
            ->innerJoin('hg_device_group','hg_device_group.hg_hub_id = hg_hub.id')
            ->where(['hg_device_group.hg_glozone_id'=>$this->id])
            ->all();
    }

    /**
     * @param bool $writeNewRules
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function processTimeBlockModifications()
    {
        $hgGlozoneTimeBlocks = $this->hgGlozoneTimeBlocks;
        $hgHubActionTemplates = [];

        //Delete existing smartOn rules
        foreach ($hgGlozoneTimeBlocks as $hgGlozoneTimeBlock) {
            //only smartON templates have time blocks
            $hgHubActionTriggers = HgHubActionTrigger::find()
                ->where(['hg_glozone_start_time_block_id'=>$hgGlozoneTimeBlock->id])
                ->orWhere(['hg_glozone_end_time_block_id'=>$hgGlozoneTimeBlock->id])
                ->all();

            foreach ($hgHubActionTriggers as $hgHubActionTrigger) {
                $hgHubActionTemplates[$hgHubActionTrigger->hgHubActionTemplate->id] = $hgHubActionTrigger->hgHubActionTemplate;
                $hgHubActionTrigger->delete();
            }
        }

        foreach ($hgHubActionTemplates as $hgHubActionTemplate) {
            $hgHubActionTemplate->delete();
        }
    }

    /**
     * @return HgGlo[]|array|\yii\db\ActiveRecord[]
     *
     * Copy base glo templates for a hub
     *
     * @TODO: this might not need to be a static function
     */
    public static function populateGlos(int $target_glozone_id, int $source_glozone_id=null)
    {
        $hgGlos = HgGlozone::findOne($source_glozone_id)->hgGlos ?? HgGlo::getDefaultGlos();
        foreach ($hgGlos as $hgGlo) {
            $newGlo = new HgGlo();
            $newGlo->attributes = $hgGlo->attributes;
            $newGlo->base_hg_glo_id = $hgGlo->id;
            $newGlo->hg_glozone_id = $target_glozone_id;
            $newGlo->save(false); //no validation needed for these default glos (OFF glo fails)
        }

        return HgGlo::find()->where(['hg_glozone_id'=>$target_glozone_id])->all();
    }

    /**
     * @return array
     * Copy the time blocks and run the formulas
     *
     * @TODO: this might not need to be a static function
     */
    public static function populateGlozoneTimeBlocks(int $target_glozone_id, int $source_glozone_id=null)
    {
        $array = [];
        
        // Fix: Check if source_glozone_id is provided and exists before accessing its timeBlocks
        if ($source_glozone_id && ($sourceGlozone = HgGlozone::findOne($source_glozone_id))) {
            $hgGlozoneTimeBlocks = $sourceGlozone->hgGlozoneTimeBlocks;
        } else {
            $hgGlozoneTimeBlocks = HgGlozoneTimeBlock::getDefaultTimeBlocks();
        }
        
        foreach ($hgGlozoneTimeBlocks as $hgGlozoneTimeBlock) {

            $timeBlock = new HgGlozoneTimeBlock();
            $timeBlock->attributes = $hgGlozoneTimeBlock->attributes;
            $timeBlock->hg_glozone_id = $target_glozone_id;
            $timeBlock->base_hg_glozone_time_block_id = $hgGlozoneTimeBlock->id;
            $timeBlock->hg_status_id = HgStatus::HG_TIMEBLOCK_ACTIVE;
            
            // Fix: Check if default_hg_glo_id exists and find corresponding glo safely
            if ($hgGlozoneTimeBlock->default_hg_glo_id) {
                $correspondingGlo = HgGlo::find()->where([
                    'base_hg_glo_id'=>$hgGlozoneTimeBlock->default_hg_glo_id,
                    'hg_glozone_id'=>$target_glozone_id
                ])->one();
                
                if ($correspondingGlo) {
                    $timeBlock->default_hg_glo_id = $correspondingGlo->id;
                }
            }
            
            $timeBlock->save();
            if (!$timeBlock->save()) {
                print_r($timeBlock->getErrors());exit;
            }
            $array[] = $timeBlock;
        }

        return $array;
    }

    /**
     * @return HgGlozoneTimeBlock|void
     */
    public function getActiveTimeBlock()
    {
        foreach ($this->hgGlozoneTimeBlocks as $hgGlozoneTimeBlock) {
            if ($hgGlozoneTimeBlock->getIsCurrentlyActiveTimeBlockByTime()) {
                return $hgGlozoneTimeBlock;
            }
        }
    }

    /**
     *  Update the bulbs across the glozone
     */
    public function updateBulbStartupModeAcrossGlozone(): void
    {
        foreach ($this->hgDeviceGroups as $hgDeviceGroup) {
            foreach ($hgDeviceGroup->hgDeviceLights as $hgDeviceLight) {
                $hgDeviceLight->updateBulbStartupMode($this->bulb_startup_mode_hg_status_id);
            }
        }
    }


    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            //populate factory glotimes / glozones
            HgGlozone::populateGlos($this->id,$this->template_glozone_id);
            HgGlozone::populateGlozoneTimeBlocks($this->id,$this->template_glozone_id);
        }

        if (!$insert) {
            if (array_key_exists('bed_time_weekday_midnightmins', $changedAttributes) ||
                array_key_exists('wake_time_weekday_midnightmins', $changedAttributes)) { //change the name across scenes
                $this->processTimeBlockModifications();
            }

            if (array_key_exists('bulb_startup_mode_hg_status_id', $changedAttributes)) {
                $this->updateBulbStartupModeAcrossGlozone();
            }
        }

    }
}
