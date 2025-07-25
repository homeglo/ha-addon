<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_device_group_type".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $hue_class_name
 * @property int|null $rank
 * @property string|null $metadata
 *
 * @property HgDeviceGroup[] $hgDeviceGroups
 */
class HgDeviceGroupType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_device_group_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rank'], 'integer'],
            [['metadata'], 'safe'],
            [['name', 'display_name', 'hue_class_name'], 'string', 'max' => 255],
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
            'display_name' => 'Group Type',
            'hue_class_name' => 'Hue Class Name',
            'rank' => 'Rank',
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
        return $this->hasMany(HgDeviceGroup::class, ['hg_device_group_type_id' => 'id']);
    }
}
