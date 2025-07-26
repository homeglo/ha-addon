<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_device_light_group_light".
 *
 * @property int $id
 * @property int|null $hg_device_light_group_id
 * @property int|null $hg_device_light_id
 * @property string|null $metadata
 *
 * @property HgGloDeviceLight $hgDeviceLight
 * @property HgDeviceLightGroup $hgDeviceLightGroup
 */
class HgDeviceGroupLight extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_group_light';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hg_device_group_id', 'hg_device_light_id'], 'integer'],
            [['metadata'], 'string']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'hg_device_light_group_id' => 'Hg Device Light Group ID',
            'hg_device_light_id' => 'Hg Device Light ID',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgDeviceLight]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLight()
    {
        return $this->hasOne(HgDeviceLight::className(), ['id' => 'hg_device_light_id']);
    }

    /**
     * Gets query for [[HgDeviceLightGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceGroup()
    {
        return $this->hasOne(HgDeviceGroup::className(), ['id' => 'hg_device_light_group_id']);
    }
}
