<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_glo_device_light".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_glo_id
 * @property int|null $hg_device_light_id
 * @property int|null $hg_device_group_id
 * @property int|null $hg_hub_id
 * @property string|null $hue_scene_id
 * @property int|null $on
 * @property float|null $ct
 * @property float|null $hue_x
 * @property float|null $hue_y
 * @property int|null $bri_absolute
 * @property string|null $metadata
 *
 * @property HgDeviceGroup $hgDeviceGroup
 * @property HgDeviceGroupLight[] $hgDeviceGroupLights
 * @property HgDeviceLight $hgDeviceLight
 * @property HgGlo $hgGlo
 * @property HgHub $hgHub
 */
class HgGloDeviceLight extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_glo_device_light';
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
            [['hg_glo_id', 'hg_device_light_id', 'hg_device_group_id', 'hg_hub_id', 'on', 'bri_absolute','ct'], 'integer'],
            [['hue_scene_id'],'string'],
            [['hue_x', 'hue_y'], 'number'],
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
            'hg_glo_id' => 'Hg Glo ID',
            'hg_device_light_id' => 'Hg Device Light ID',
            'hg_device_group_id' => 'Hg Device Group ID',
            'hg_hub_id' => 'Hg Hub ID',
            'on' => 'On',
            'hue_x' => 'Hue X',
            'hue_y' => 'Hue Y',
            'bri_absolute' => 'Bri Absolute',
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
     * Gets query for [[HgDeviceGroupLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceGroupLights()
    {
        return $this->hasMany(HgDeviceGroupLight::class, ['hg_device_light_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceLight]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLight()
    {
        return $this->hasOne(HgDeviceLight::class, ['id' => 'hg_device_light_id']);
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
     * Gets query for [[HgHub]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHub()
    {
        return $this->hasOne(HgHub::class, ['id' => 'hg_hub_id']);
    }

    /**
     * return default if not present
     * @return float|null
     */
    public function getHueX()
    {
        return $this->hue_x ?? $this->hgGlo->hue_x;
    }

    /**
     * return default if not present
     * @return float|null
     */
    public function getHueY()
    {
        return $this->hue_y ?? $this->hgGlo->hue_y;
    }

    /**
     * return default if not present
     * @return float|null
     */
    public function getHueCt()
    {
        return $this->ct ?? $this->hgGlo->ct;
    }

    /**
     * return default if not present
     * @return float|null
     */
    public function getHueBri()
    {
        return $this->bri_absolute ?? $this->hgGlo->brightness;
    }

    /**
     * @return float|null
     */
    public function getParentHueCt()
    {
        return $this->hgGlo->ct;
    }

    /**
     * @return float|null
     */
    public function getParentHueY()
    {
        return $this->hgGlo->hue_y;
    }

    /**
     * @return float|null
     */
    public function getParentHueX()
    {
        return $this->hgGlo->hue_x;
    }

    /**
     * return default if not present
     * @return float|null
     */
    public function getParentHueBri()
    {
        return $this->hgGlo->brightness;
    }

    /**
     * @return array
     */
    public function getHueLightstate($state)
    {
        $arr['on'] = (bool) $this->on;
        switch ($state) {
            case HgGloDeviceGroup::REBRIGHT_LIGHTSTATE:
                $arr['bri'] = $this->parentHueBri;

                if ($this->hgDeviceLight->isAmbiance) { //if ambiance bulb, we can only use CT
                    $arr['ct'] = $this->hueCt;
                } else {
                    if ($this->hueX && $this->hueY) {
                        $arr['xy'] = [(float)$this->hueX,(float)$this->hueY];
                    } else {
                        $arr['ct'] = $this->hueCt;
                    }
                }

                break;
            case HgGloDeviceGroup::RECOLOR_LIGHTSTATE:
                $arr['bri'] = $this->hueBri;

                if ($this->hgDeviceLight->isAmbiance) { //if ambiance bulb, we can only use CT
                    $arr['ct'] = $this->parentHueCt;
                } else {
                    if ($this->hueX && $this->hueY) {
                        $arr['xy'] = [(float)$this->parentHueX,(float)$this->parentHueY];
                    } else {
                        $arr['ct'] = $this->parentHueCt;
                    }
                }

                break;
            case HgGloDeviceGroup::GLOZONE_LIGHTSTATE:
                $arr['bri'] = $this->parentHueBri;

                if ($this->hgDeviceLight->isAmbiance) { //if ambiance bulb, we can only use CT
                    $arr['ct'] = $this->parentHueCt;
                } else {
                    if ($this->hueX && $this->hueY) {
                        $arr['xy'] = [(float)$this->parentHueX,(float)$this->parentHueY];
                    } else {
                        $arr['ct'] = $this->parentHueCt;
                    }
                }

                break;

            case HgGloDeviceGroup::RESTORE_LIGHTSTATE:
                $arr['bri'] = $this->hueBri;

                if ($this->hgDeviceLight->isAmbiance) { //if ambiance bulb, we can only use CT
                    $arr['ct'] = $this->hueCt;
                } else {
                    if ($this->hueX && $this->hueY) {
                        $arr['xy'] = [(float)$this->hueX,(float)$this->hueY];
                    } else {
                        $arr['ct'] = $this->hueCt;
                    }
                }

                break;
        }

        return $arr;
    }

}
