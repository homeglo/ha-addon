<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_device_sensor_device_group_multiroom".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_device_sensor_id
 * @property int|null $hg_device_group_id
 * @property string|null $metadata
 *
 * @property HgDeviceGroup $hgDeviceGroup
 * @property HgDeviceSensor $hgDeviceSensor
 */
class HgDeviceSensorDeviceGroupMultiroom extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_sensor_device_group_multiroom';
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
            [['hg_device_sensor_id', 'hg_device_group_id'], 'integer'],
            [['metadata'], 'safe'],
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
            'hg_device_sensor_id' => 'Hg Device Sensor ID',
            'hg_device_group_id' => 'Hg Device Group ID',
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
     * Gets query for [[HgDeviceSensor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensor()
    {
        return $this->hasOne(HgDeviceSensor::class, ['id' => 'hg_device_sensor_id']);
    }
}
