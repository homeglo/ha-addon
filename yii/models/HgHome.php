<?php

namespace app\models;

use app\components\HelperComponent;
use Yii;

/**
 * This is the model class for table "hg_home".
 *
 * @property int $id
 * @property int|null $updated_at
 * @property int|null $created_at
 * @property string|null $name
 * @property float|null $lat
 * @@property float|null $lng
 * @property string|null $display_name
 * @property int|null $hg_version_id
 * @property int|null $hg_status_id
 * @property HgGlozone[] $hgGlozones
 * @property HgHub[] $hgHubs
 * @property HgRoom[] $hgRooms
 * @property HgVersion $hgVersion
 */
class HgHome extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_home';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['display_name','lat','lng'],'required'],
            [['name'],'default','value'=>function($m) { return strtolower(str_replace(' ','_',$m->display_name)); }],
            [[ 'hg_version_id'], 'integer'],
            [[ 'lat','lng'], 'number'],
            [[ 'hg_version_id'], 'default','value'=>HgVersion::HG_VERSION_2_0_ENGINE],
            [[ 'hg_status_id'], 'default','value'=>HgStatus::HG_HOME_ACTIVE],
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
            'updated_at' => 'Updated At',
            'created_at' => 'Created At',
            'name' => 'Name',
            'display_name' => 'Display Name',
            'hg_version_id' => 'Hg Version ID',
        ];
    }

    /**
     * Gets query for [[HgGlozones]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozones()
    {
        return $this->hasMany(HgGlozone::className(), ['hg_home_id' => 'id']);
    }

    /**
     * Gets query for [[HgUserHome]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHomeUsers()
    {
        return $this->hasMany(HgUserHome::className(), ['hg_home_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubs()
    {
        return $this->hasMany(HgHub::className(), ['hg_home_id' => 'id']);
    }

    /**
     * Gets query for [[HgRooms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgRooms()
    {
        return $this->hasMany(HgRoom::className(), ['hg_home_id' => 'id']);
    }

    /**
     * Gets query for [[HgVersion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgVersion()
    {
        return $this->hasOne(HgVersion::className(), ['id' => 'hg_version_id']);
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

    public function getDefaultGlozone()
    {
        return HgGlozone::find()->where(['hg_home_id'=>$this->id])->orderBy('id ASC')->one();
    }

    public function getIsActive()
    {
        return $this->hg_status_id == HgStatus::HG_HOME_ACTIVE;
    }

    /**
     * @return HgDeviceGroup[]|array|\yii\db\ActiveRecord[]
     */
    public function getHgDeviceGroups()
    {
        return HgDeviceGroup::find()
            ->innerJoin('hg_glozone','hg_device_group.hg_glozone_id = hg_glozone.id')
            ->where(['hg_glozone.hg_home_id'=>$this->id])
            ->all();
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if (!$insert) {
            if (array_key_exists('lat', $changedAttributes) || array_key_exists('lng', $changedAttributes)) { //change the name across scenes
                if ($this->hgHubs) {
                    list ($lat ,$long) = HelperComponent::DECtoDMS($this->lat,$this->lng);
                    foreach ($this->hgHubs as $hgHub) {
                        try {
                            $hgHub->getHueComponent()->v1PutRequest('sensors/1/config',['lat'=>$lat,'long'=>$long,'sunriseoffset'=>0,'sunsetoffset'=>0]);
                        } catch (\Throwable $t) {
                            Yii::error($t->getMessage(),__METHOD__);
                        }
                    }
                }
            }
        }
    }
}
