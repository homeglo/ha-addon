<?php

namespace app\models;

use app\exceptions\HueApiException;
use app\interfaces\HueApiModelInterface;
use app\components\HomeAssistantComponent;
// use app\jobs\AsyncHueRequestJob; // REMOVED: No longer pushing async updates to Hue hub
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\UnsetArrayValue;

/**
 * This is the model class for table "hg_device_light".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_hub_id
 * @property string|null $ha_device_id
 * @property string|null $hue_uniqueid
 * @property string|null $serial
 * @property string|null $display_name
 * @property int|null $primary_hg_device_group_id
 * @property int|null $hg_product_light_id
 * @property int|null $hg_device_light_fixture
 * @property string|null $metadata
 *
 * @property HgDeviceLightFixture $hgDeviceLightFixture
 * @property HgGloDeviceLight[] $hgGloDeviceLights
 * @property HgHub $hgHub
 * @property HgProductLight $hgProductLight
 * @property HgDeviceGroup $primaryHgDeviceGroup
 */
class HgDeviceLight extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_light';
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
            [['hg_hub_id', 'primary_hg_device_group_id', 'hg_product_light_id', 'hg_device_light_fixture'], 'integer'],
            [['ha_device_id'], 'string', 'max' => 255],
            [['display_name','serial'], 'string', 'max' => 255],
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
            'display_name' => 'Display Name',
            'primary_hg_device_group_id' => 'Primary Hg Device Group ID',
            'hg_product_light_id' => 'Hg Product Light ID',
            'hg_device_light_fixture' => 'Hg Device Light Fixture',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgDeviceLightFixture]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLightFixture()
    {
        return $this->hasOne(HgDeviceLightFixture::class, ['id' => 'hg_device_light_fixture']);
    }

    /**
     * Gets query for [[HgGloDeviceLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGloDeviceLights()
    {
        return $this->hasMany(HgGloDeviceLight::class, ['hg_device_light_id' => 'id']);
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
     * Gets query for [[HgProductLight]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgProductLight()
    {
        return $this->hasOne(HgProductLight::class, ['id' => 'hg_product_light_id']);
    }

    /**
     * Gets query for [[PrimaryHgDeviceGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPrimaryHgDeviceGroup()
    {
        return $this->hasOne(HgDeviceGroup::class, ['id' => 'primary_hg_device_group_id']);
    }

    public static function findByHaDeviceId($hub_id,$ha_device_id)
    {
        return static::find()->where(['hg_hub_id'=>$hub_id,'ha_device_id'=>$ha_device_id])->one();
    }

    public function processHueApiUpdates(string $attribute)
    {
        try {
            switch ($attribute) {
                case 'primary_hg_device_group_id':
                    if ($this->oldAttributes['primary_hg_device_group_id'] !== NULL) {
                        $oldPrimaryHgDeviceGroup = HgDeviceGroup::findOne($this->oldAttributes['primary_hg_device_group_id']);
                        $old_ids = $oldPrimaryHgDeviceGroup->arrayOfHueIds;

                        if (($key = array_search($this->hue_id, $old_ids)) !== false) {
                            unset($old_ids[$key]);
                        }

                        $this->hgHub->getHueComponent()->v1PutRequest('groups/'.$oldPrimaryHgDeviceGroup->hue_id,
                            ['lights'=>array_values($old_ids)]);
                    }
                    $newPrimaryHgDeviceGroup = HgDeviceGroup::findOne($this->primary_hg_device_group_id);
                    $this->hgHub->getHueComponent()->v1PutRequest('groups/'.$newPrimaryHgDeviceGroup->hue_id,
                        ['lights'=>ArrayHelper::merge($newPrimaryHgDeviceGroup->arrayOfHueIds,[(string)$this->hue_id])]);

                    // TODO: Update for Home Assistant - sync down functionality no longer needed
                    // Previous logic synced Hue hub state down after room change - replace with HA integration if needed
                    // SyncDownJob removed - no longer syncing from Hue hub

                    break;
                case 'display_name':
                    $this->hgHub->getHueComponent()->v1PutRequest('lights/'.$this->hue_id,['name'=>$this->display_name]);
                    break;
            }
        } catch (HueApiException $e) {
            $this->addError($attribute,$e->getMessage());
            return false;
        }

        return true;
    }

    public function flashLight(HgGlo $hgGlo = null, $hueBody=[])
    {
        // Skip if this light doesn't have Home Assistant device ID
        if (empty($this->ha_device_id)) {
            Yii::warning("Cannot flash light {$this->id}: no ha_device_id", __METHOD__);
            return false;
        }

        try {
            $ha = new HomeAssistantComponent();
            $entityIds = $ha->getDeviceLightEntities($this->ha_device_id);
            
            if (empty($entityIds)) {
                Yii::warning("No light entities found for device {$this->ha_device_id}", __METHOD__);
                return false;
            }

            // Flash with quick on/off/on sequence using Home Assistant
            // First, turn on with glo settings
            if ($hgGlo) {
                $ha->turnOnLightsWithGlo($entityIds, $hgGlo, 0.1); // Very fast transition
            } else {
                $ha->setLightsBrightness($entityIds, 255, 0.1); // Full brightness, fast
            }

            // Wait briefly, then flash off and back on
            // Note: HA doesn't have a direct "alert" equivalent, so we simulate it
            // You might want to use a different approach like calling a HA script
            $ha->callService('light', 'turn_on', [
                'entity_id' => $entityIds,
                'flash' => 'short' // Some lights support flash parameter
            ]);

            return true;
            
        } catch (\Exception $e) {
            Yii::error("Flash light failed for device {$this->ha_device_id}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    public function turnOnLight(HgGlo $hgGlo = null, $hueBody=[])
    {
        // Skip if this light doesn't have Home Assistant device ID
        if (empty($this->ha_device_id)) {
            Yii::warning("Cannot turn on light {$this->id}: no ha_device_id", __METHOD__);
            return false;
        }

        try {
            $ha = new HomeAssistantComponent();
            $entityIds = $ha->getDeviceLightEntities($this->ha_device_id);
            
            if (empty($entityIds)) {
                Yii::warning("No light entities found for device {$this->ha_device_id}", __METHOD__);
                return false;
            }

            // Use default glo if not provided
            if (!$hgGlo) {
                $hgGlo = HgGlo::findOne(90);
            }

            // Use the unified light control method with elegant transitions
            if ($hgGlo) {
                $ha->turnOnLightsWithGlo($entityIds, $hgGlo);
            } else {
                // Fallback: just turn on with default brightness
                $ha->setLightsBrightness($entityIds, 255);
            }

            return true;
            
        } catch (\Exception $e) {
            Yii::error("Turn on light failed for device {$this->ha_device_id}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    public function turnOffLight(HgGlo $hgGlo = null, $hueBody=[])
    {
        // Skip if this light doesn't have Home Assistant device ID
        if (empty($this->ha_device_id)) {
            Yii::warning("Cannot turn off light {$this->id}: no ha_device_id", __METHOD__);
            return false;
        }

        try {
            $ha = new HomeAssistantComponent();
            $entityIds = $ha->getDeviceLightEntities($this->ha_device_id);
            
            if (empty($entityIds)) {
                Yii::warning("No light entities found for device {$this->ha_device_id}", __METHOD__);
                return false;
            }

            // Use the unified light control method with elegant transitions
            $ha->turnOffLights($entityIds);

            return true;
            
        } catch (\Exception $e) {
            Yii::error("Turn off light failed for device {$this->ha_device_id}: " . $e->getMessage(), __METHOD__);
            return false;
        }
    }

    /**
     * return if this is a bulb. sometimes it is a PLUG that turns on a bulb
     * @return bool
     */
    public function getIsBulb()
    {
        if ($this->hgProductLight && $this->hgProductLight->maxlumen)
            return true;

        $d = $this->getJsonData('hue_hub_data');
        if (isset($d['state']['ct']) || isset($d['state']['xy'])) {
            return true;
        }

        return false;
    }

    public function getIsAmbiance()
    {
        if ($capability = json_decode($this->hgProductLight->capability_json,TRUE)) {
            if (isset($capability['control']['colorgamut'])) //not ambiance bulb if color gamut exists
                return FALSE;
        }

        return true;
    }

    public function updateBulbStartupMode($bulb_startup_mode_hg_status_id)
    {
        // TODO: Update for Home Assistant - bulb startup mode configuration no longer needed
        // Previous logic pushed async Hue bulb startup config updates - replace with HA integration if needed
        switch ($bulb_startup_mode_hg_status_id) {
            case HgStatus::HG_GLOZONE_STARTUPMODE_HUE_WARM_WHITE:
                // AsyncHueRequestJob removed - no longer updating Hue hub startup mode to 'safety'
                break;
            case HgStatus::HG_GLOZONE_STARTUPMODE_LAST_STATE:
                // AsyncHueRequestJob removed - no longer updating Hue hub startup mode to 'powerfail'
                break;
        }
        return true;
    }
}
