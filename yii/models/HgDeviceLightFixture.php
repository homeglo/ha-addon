<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_device_light_fixture".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $hue_archetype_name
 * @property int|null $rank
 * @property string|null $targeting
 * @property int|null $nl_sensitivity
 * @property int|null $brightness_mask_percent
 *
 * @property HgDeviceLight[] $hgDeviceLights
 */
class HgDeviceLightFixture extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_light_fixture';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rank', 'nl_sensitivity', 'brightness_mask_percent'], 'integer'],
            [['name', 'display_name', 'targeting','hue_archetype_name'], 'string', 'max' => 255],
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
            'display_name' => 'Fixture Name',
            'rank' => 'Rank',
            'targeting' => 'Targeting',
            'nl_sensitivity' => 'Nl Sensitivity',
            'brightness_mask_percent' => 'Brightness Mask Percent',
        ];
    }

    /**
     * Gets query for [[HgDeviceLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgDeviceLights()
    {
        return $this->hasMany(HgDeviceLight::className(), ['hg_device_light_fixture' => 'id']);
    }
}
