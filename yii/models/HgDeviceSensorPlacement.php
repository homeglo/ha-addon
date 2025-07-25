<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_device_sensor_placement".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property int|null $rank
 *
 * @property HgDeviceSensor[] $hgDeviceSensors
 */
class HgDeviceSensorPlacement extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_sensor_placement';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rank'], 'integer'],
            [['name', 'display_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'display_name' => 'Placement',
            'rank' => 'Rank',
        ];
    }

    /**
     * Gets query for [[HgDeviceSensors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensors()
    {
        return $this->hasMany(HgDeviceSensor::className(), ['hg_device_sensor_placement_id' => 'id']);
    }
}
