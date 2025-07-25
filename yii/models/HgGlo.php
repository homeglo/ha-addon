<?php

namespace app\models;

use Yii;
use yii\helpers\Html;

/**
 * This is the model class for table "hg_glo".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $base_hg_glo_id
 * @property string|null $name
 * @property string|null $hub_name
 * @property string|null $display_name
 * @property int|null $hg_status_id
 * @property int|null $hg_glozone_id
 * @property int|null $hg_hub_id
 * @property int|null $hg_version_id
 * @property int|null $rank
 * @property float|null $ct
 * @property float|null $hue_x
 * @property float|null $hue_y
 * @property int|null $brightness
 * @property string|null $metadata
 *
 * @property HgGlo $baseHgGlo
 * @property HgGloDeviceLight[] $hgGloDeviceLights
 * @property HgGlo[] $hgGlos
 * @property HgGlozone $hgGlozone
 * @property HgGlozoneTimeBlock[] $hgGlozoneTimeBlocks
 * @property HgHub $hgHub
 * @property HgHubActionItem[] $hgHubActionItems
 * @property HgStatus $hgStatus
 * @property HgVersion $hgVersion
 */
class HgGlo extends \yii\db\ActiveRecord
{
    const BASE_HG_GLO_OFF_ID = 5;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_glo';
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
            [['brightness','display_name'],'required'],
            [['base_hg_glo_id', 'hg_status_id', 'hg_glozone_id', 'hg_hub_id', 'hg_version_id', 'rank', 'brightness','ct'], 'integer'],
            [['hue_x', 'hue_y'], 'number'],
            [['hue_x','hue_y'],'compare', 'compareValue'=>0, 'operator'=>'>', 'type'=>'number'],
            [['hue_x','hue_y'],'compare', 'compareValue'=>1, 'operator'=>'<', 'type'=>'number'],

            [['ct'],'compare', 'compareValue'=>153, 'operator'=>'>=', 'type'=>'number'],
            [['ct'],'compare', 'compareValue'=>500, 'operator'=>'<=', 'type'=>'number'],

            [['brightness'],'compare', 'compareValue'=>1, 'operator'=>'>=', 'type'=>'number'],
            [['brightness'],'compare', 'compareValue'=>255, 'operator'=>'<=', 'type'=>'number'],
            [['name'],'default','value'=>function($m) { return strtolower(str_replace(' ','_',$m->display_name)); }],
            [['hub_name'],'default','value'=>function($m) { return $m->display_name; }],
            [['hg_status_id'],'default','value'=>HgStatus::HG_GLO_ACTIVE],
            [['name', 'hub_name', 'display_name'], 'string', 'max' => 255],
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
            'base_hg_glo_id' => 'Base Hg Glo ID',
            'name' => 'Name',
            'hub_name' => 'Hub Name',
            'display_name' => 'Glo Name',
            'hg_status_id' => 'Hg Status ID',
            'hg_glozone_id' => 'Hg Glozone ID',
            'hg_hub_id' => 'Hg Hub ID',
            'hg_version_id' => 'Hg Version ID',
            'rank' => 'Rank',
            'hue_x' => 'Hue X',
            'hue_y' => 'Hue Y',
            'brightness' => 'Brightness',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[BaseHgGlo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaseHgGlo()
    {
        return $this->hasOne(HgGlo::class, ['id' => 'base_hg_glo_id']);
    }

    /**
     * Gets query for [[HgGloDeviceLights]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGloDeviceLights()
    {
        return $this->hasMany(HgGloDeviceLight::class, ['hg_glo_id' => 'id']);
    }

    /**
     * Gets query for [[HgGlos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlos()
    {
        return $this->hasMany(HgGlo::class, ['base_hg_glo_id' => 'id']);
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
     * Gets query for [[HgGlozoneTimeBlocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneTimeBlocks()
    {
        return $this->hasMany(HgGlozoneTimeBlock::class, ['default_hg_glo_id' => 'id']);
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
     * Gets query for [[HgGloDeviceGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGloDeviceGroups()
    {
        return $this->hasMany(HgGloDeviceGroup::class, ['hg_glo_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActionItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionItems()
    {
        return $this->hasMany(HgHubActionItem::class, ['hg_glo_id' => 'id']);
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
     * Gets query for [[HgVersion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgVersion()
    {
        return $this->hasOne(HgVersion::class, ['id' => 'hg_version_id']);
    }

    public static function getDefaultGlos()
    {
        return static::find()->where(['hg_glozone_id'=>HgGlozone::HG_DEFAULT_GLOZONE])->all();
    }

    /**
     * @return bool
     */
    public function getIsOffGlo()
    {
        if ( ($this->base_hg_glo_id == HgGlo::BASE_HG_GLO_OFF_ID) )
            return true;
        else
            return false;
    }

    public function getPillHtml()
    {
        return Html::tag('span',$this->display_name,['class'=>'badge bg-glo-'.$this->name]);
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);
    }
}
