<?php

namespace app\models;

use app\components\HueComponent;
use Yii;

/**
 * This is the model class for table "hg_hub".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_home_id
 * @property int|null $hg_type_id
 * @property int|null $hg_status_id
 * @property string $display_name
 * @property string $access_token
 * @property string|null $bearer_token
 * @property string|null $refresh_token
 * @property int|null $token_expires_at
 * @property string|null $hue_email
 * @property string|null $hue_random
 * @property string|null $notes
 * @property string|null $metadata
 *
 * @property HgDeviceLightGroup[] $hgDeviceLightGroups
 * @property HgDeviceLight[] $hgDeviceLights
 * @property HgDeviceSensor[] $hgDeviceSensors
 * @property HgGlo[] $hgGlos
 * @property HgHome $hgHome
 * @property HgHubAction[] $hgHubActions
 * @property HgStatus $hgStatus
 * @property HgType $hgType
 */
class HgHub extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_hub';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className(),
            [
                'class'=>\app\behaviors\JsonDataBehavior::class,
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['display_name'],'required'],
            [['hg_home_id', 'hg_status_id', 'token_expires_at'], 'integer'],
            [['notes', 'metadata'], 'string'],
            [['display_name', 'access_token', 'bearer_token', 'refresh_token', 'hue_email', 'hue_random'], 'string', 'max' => 255],
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
            'hg_type_id' => 'Hg Type ID',
            'hg_status_id' => 'Hg Status ID',
            'display_name' => 'Hub Name',
            'access_token' => 'Access Token',
            'bearer_token' => 'Bearer Token',
            'refresh_token' => 'Refresh Token',
            'token_expires_at' => 'Token Expires At',
            'hue_email' => 'Hue Email',
            'hue_random' => 'Hue Random',
            'notes' => 'Notes',
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
        return $this->hasMany(HgDeviceGroup::className(), ['hg_hub_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLights()
    {
        return $this->hasMany(HgDeviceLight::className(), ['hg_hub_id' => 'id']);
    }

    /**
     * Gets query for [[HgDeviceSensors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceSensors()
    {
        return $this->hasMany(HgDeviceSensor::className(), ['hg_hub_id' => 'id']);
    }

    /**
     * Gets query for [[HgGlos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlos()
    {
        return $this->hasMany(HgGlo::className(), ['hg_hub_id' => 'id']);
    }

    /**
     * Gets query for [[HgHome]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHome()
    {
        return $this->hasOne(HgHome::className(), ['id' => 'hg_home_id']);
    }

    /**
     * Gets query for [[HgHubActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActions()
    {
        return $this->hasMany(HgHubAction::className(), ['hg_hub_id' => 'id']);
    }

    /**
     * Gets query for [[HgStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgStatus()
    {
        return $this->hasOne(HgStatus::className(), ['id' => 'hg_status_id']);
    }

    /**
     * Gets query for [[HgType]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgType()
    {
        return $this->hasOne(HgType::className(), ['id' => 'hg_type_id']);
    }

    /**
     * @return HueComponent
     */
    public function getHueComponent(): HueComponent
    {
        return new HueComponent($this->access_token,$this->bearer_token);
    }

    public function getHueHubCapabilities()
    {
        return $this->getHueComponent()->v1GetRequest('capabilities');
    }

    public function getIsReachable()
    {
        if ($this->access_token && $this->bearer_token && $this->token_expires_at) {

            try {
                $this->getHueComponent()->v1GetRequest('capabilities');
            } catch (\Throwable $t) {
                return false;
            }

            if ($this->token_expires_at > time())
                return true;
        }

        return false;
    }
}
