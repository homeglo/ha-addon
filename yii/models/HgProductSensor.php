<?php

namespace app\models;

use app\components\HelperComponent;
use Yii;

/**
 * This is the model class for table "hg_product_sensor".
 *
 * @property int $id
 * @property string|null $display_name
 * @property string|null $manufacturer_name
 * @property string|null $product_name
 * @property string|null $type_name
 * @property string|null $archetype
 * @property string|null $model_id
 * @property string|null $description
 * @property string|null $action_map_type
 * @property int|null $rank
 * @property int|null $button_count
 *
 * @property HgDeviceSensor[] $hgDeviceSensors
 */
class HgProductSensor extends \yii\db\ActiveRecord
{
    const TYPE_NAME_HUE_SWITCH = 'hue_dimmer_switch';
    const TYPE_NAME_HUE_MOTION_SENSOR = 'hue_motion_sensor';
    const TYPE_NAME_HUE_AMBIENT_SENSOR = 'hue_ambient_sensor';
    const TYPE_NAME_HUE_TEMPERATURE_SENSOR = 'hue_temperature_sensor';

    const SENSOR_TYPE_MAP = [
        HgProductSensor::TYPE_NAME_HUE_MOTION_SENSOR => 'Hue Presence Sensor',
        HgProductSensor::TYPE_NAME_HUE_SWITCH => 'Hue Dimmer Switch',
        HgProductSensor::TYPE_NAME_HUE_AMBIENT_SENSOR => 'Hue Ambient Sensor',
        HgProductSensor::TYPE_NAME_HUE_TEMPERATURE_SENSOR => 'Hue Temperature Sensor',
    ];


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_product_sensor';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['description','type_name'], 'string'],
            [['rank', 'button_count'], 'integer'],
            [['display_name', 'manufacturer_name', 'product_name', 'archetype', 'model_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'display_name' => 'Product Name',
            'manufacturer_name' => 'Manufacturer Name',
            'product_name' => 'Product Name',
            'archetype' => 'Archetype',
            'model_id' => 'Model ID',
            'description' => 'Description',
            'rank' => 'Rank',
            'button_count' => 'Button Count',
        ];
    }

    /**
     * Gets query for [[HgDeviceSensors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensors()
    {
        return $this->hasMany(HgDeviceSensor::class, ['hg_product_sensor_id' => 'id']);
    }

    public function getIsMotion()
    {
        if (in_array($this->archetype,['ZLLPresence']))
            return true;
        else
            return false;
    }

    public static function triageHueSensor(array $data)
    {
        $skipTypes = ['CLIPGenericStatus','Daylight'];
        if (in_array($data['type'],$skipTypes))
            return false;

        $hgProductSensor = HgProductSensor::find()->where(['model_id'=>$data['modelid'],'archetype'=>$data['type']])->one() ?? new HgProductSensor();
        $hgProductSensor->display_name = $data['productname'];
        $hgProductSensor->manufacturer_name = $data['manufacturername'];
        $hgProductSensor->product_name = $data['productname'];
        $hgProductSensor->model_id = $data['modelid'];
        $hgProductSensor->archetype = $data['type'];
        if (!$hgProductSensor->save()) {
            Yii::error(HelperComponent::getFirstErrorFromFailedValidation($hgProductSensor));
        }

        return $hgProductSensor;
    }
}
