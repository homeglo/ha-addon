<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_room".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_home_id
 * @property int|null $hg_room_type_id
 * @property int|null $hg_status_id
 * @property int|null $hg_glozone_id
 * @property string|null $display_name
 * @property int|null $automatic_transitions
 * @property string|null $metadata
 *
 * @property HgDeviceLight[] $hgDeviceLights
 * @property HgDeviceSensor[] $hgDeviceSensors
 * @property HgGlozone $hgGlozone
 * @property HgHome $hgHome
 * @property HgHubActionItem[] $hgHubActionItems
 * @property HgRoomType $hgRoomType
 * @property HgStatus $hgStatus
 */
class HgRoom extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_room';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hg_home_id', 'hg_room_type_id', 'hg_status_id', 'hg_glozone_id', 'automatic_transitions'], 'integer'],
            [['metadata'], 'string'],
            [['display_name'], 'string', 'max' => 255],
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
            'hg_room_type_id' => 'Hg Room Type ID',
            'hg_status_id' => 'Hg Status ID',
            'hg_glozone_id' => 'Hg Glozone ID',
            'display_name' => 'Display Name',
            'automatic_transitions' => 'Automatic Transitions',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgDeviceLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLights()
    {
        return $this->hasMany(HgDeviceLight::class, ['hg_room_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceSensors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensors()
    {
        return $this->hasMany(HgDeviceSensor::class, ['hg_room_id' => 'id']);
    }

    /**
     * Gets query for [[HgGlozone]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozone()
    {
        return $this->hasOne(HgGlozone::class, ['id' => 'hg_glozone_id']);
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
     * Gets query for [[HgHubActionItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionItems()
    {
        return $this->hasMany(HgHubActionItem::class, ['operate_hg_room_id' => 'id']);
    }

    /**
     * Gets query for [[HgRoomType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgRoomType()
    {
        return $this->hasOne(HgRoomType::class, ['id' => 'hg_room_type_id']);
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
}
