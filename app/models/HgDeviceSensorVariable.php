<?php

namespace app\models;

use app\components\HelperComponent;
use app\components\HgEngineComponent;
use Yii;

/**
 * This is the model class for table "hg_device_sensor_variable".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property string|null $display_name
 * @property int|null $hg_device_sensor_id
 * @property string|null $variable_name
 * @property string|null $value
 * @property int|null $override_hg_product_sensor_id
 * @property string|null $sensor_type_name
 * @property int|null $hg_status_id
 * @property string|null $description
 * @property string|null $json_data
 *
 * @property HgDeviceSensor $hgDeviceSensor
 * @property HgStatus $hgStatus
 */
class HgDeviceSensorVariable extends \yii\db\ActiveRecord
{
    const MOTION_WARNING_TIMER = 'motion_warning_timer';
    const MOTION_FINALIZE_TIMER = 'motion_finalize_timer';
    const MOTION_DEFAULT_SENSITIVITY = 'motion_default_sensitivity';
    const AMBIENT_DEFAULT_DARK_THRESHOLD = 'ambient_default_darkness_threshold';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_sensor_variable';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className(),
            [
                'class'=>\app\behaviors\JsonDataBehavior::class,
                'attribute'=>'json_data'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hg_device_sensor_id', 'hg_status_id', 'json_data','override_hg_product_sensor_id'], 'integer'],
            [['description','variable_name','sensor_type_name'], 'string'],
            [['display_name'], 'string', 'max' => 255],
            [['value'],'safe']
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
            'display_name' => 'Display Name',
            'hg_device_sensor_id' => 'Hg Device Sensor ID',
            'variable_name' => 'Variable Name',
            'value' => 'Value',
            'hg_status_id' => 'Hg Status ID',
            'description' => 'Description',
            'json_data' => 'Json Data',
            'override_hg_product_sensor_id'=>'Model Specific'
        ];
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

    /**
     * Gets query for [[HgProductSensor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOverrideHgProductSensor()
    {
        return $this->hasOne(HgProductSensor::class, ['id' => 'override_hg_product_sensor_id']);
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
     * Get the value from the hub if possible
     * @return $this
     */
    public function refreshValueFromHueHub()
    {
        $engine = new HgEngineComponent($this->hgDeviceSensor->hg_hub_id);
        $value = $engine->getHueValueBySensorVariable($this);
        echo $value."\n";
        if ($value !== NULL && ($this->value != $value)) { //only update if different
            $this->value = $value;
            $this->save();
        }

        return $this;
    }

    public static function syncAllSensorsAndVariables()
    {
        foreach (HgDeviceSensor::find()->orderBy('id desc')->all() as $hgDeviceSensor) {
            $hgDeviceSensor->populateDeviceVariables();
        }
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            //if this is a default variable we are pushing down the chain
            if ($this->hg_device_sensor_id == NULL) {
                HgDeviceSensorVariable::syncAllSensorsAndVariables();
            }
        }

        //push updates to the hue sensor when this is created or updated
        if ($this->hg_device_sensor_id && array_key_exists('value',$changedAttributes)) { //if this is a sensor and not a default
            $engine = new HgEngineComponent($this->hgDeviceSensor->hg_hub_id);
            $engine->processHueRuleUpdatesBySensorVariable($this);
            $engine->processHueSensorConfigUpdatesBySensorVariable($this);
        }

    }
}
