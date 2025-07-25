<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_glo_device_group".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_glo_id
 * @property int|null $hg_glozone_id
 * @property int|null $hg_device_group_id
 * @property int|null $hg_hub_id
 * @property string|null $hub_display_name
 * @property string|null $hue_scene_id
 * @property string|null $metadata
 *
 * @property HgDeviceGroup $hgDeviceGroup
 * @property HgGlo $hgGlo
 * @property HgHub $hgHub
 */
class HgGloDeviceGroup extends \yii\db\ActiveRecord
{
    const RECOLOR_LIGHTSTATE = 'RECOLOR_LIGHTSTATE'; // set scenes color value only to glo value
    const REBRIGHT_LIGHTSTATE = 'REBRIGHT_LIGHTSTATE'; // set scenes brightness value only to glo value
    const RESTORE_LIGHTSTATE = 'RESTORE_LIGHTSTATE'; //set scenes to overrides + glo values
    const GLOZONE_LIGHTSTATE = 'GLOZONE_LIGHTSTATE'; //set scenes to glozone glo values


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_glo_device_group';
    }

    /**
     * @return array
     */
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
            [['hg_glo_id', 'hg_device_group_id', 'hg_hub_id', 'hg_glozone_id'], 'integer'],
            [['metadata'], 'safe'],
            [['hub_display_name', 'hue_scene_id'], 'string', 'max' => 255],
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
            'hg_glo_id' => 'Hg Glo ID',
            'hg_device_group_id' => 'Hg Device Group ID',
            'hg_hub_id' => 'Hg Hub ID',
            'hub_display_name' => 'Hub Display Name',
            'hue_scene_id' => 'Hue Scene ID',
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
     * Gets query for [[HgGlo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlo()
    {
        return $this->hasOne(HgGlo::class, ['id' => 'hg_glo_id']);
    }

    /**
     * Gets query for [[HgGlo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozone()
    {
        return $this->hasOne(HgGlozone::class, ['id' => 'hg_glozone_id']);
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

    public function refreshAndUpdateHue()
    {
        $newName = $this->hgDeviceGroup->display_name.' '.$this->hgGlo->display_name;
        $this->hub_display_name = $newName;
        $this->save();

        $this->hgHub->getHueComponent()->v1PutRequest('scenes/'.$this->hue_scene_id,['name'=>$newName]);
    }


    /**
     * Generate the state of lights for this glo<>room pair
     * @param $state
     * $param $saveState - whether or not to basically perform a "sync"
     * @return array
     */
    public function generateHueLightstates($state,$saveState=true)
    {
        $lightstate = [];
        $sceneArrayToPost = [];
        $hgGloDeviceLights = HgGloDeviceLight::find()->where([
                'hg_glo_id'=>$this->hg_glo_id,
                'hg_device_group_id'=>$this->hg_device_group_id]
        )->all();

        /* @var \app\models\HgGloDeviceLight $hgGloDeviceLight */
        foreach ($hgGloDeviceLights as $hgGloDeviceLight) {

            if (!$hgGloDeviceLight->hgDeviceLight->isBulb) { //do not process non-bulbs rn
                continue;
            }

            $lightstate = $hgGloDeviceLight->getHueLightstate($state);
            $sceneArrayToPost[(string) $hgGloDeviceLight->hgDeviceLight->hue_id] = $lightstate;

            if ($saveState) {
                switch ($state) {
                    case HgGloDeviceGroup::REBRIGHT_LIGHTSTATE:
                        $hgGloDeviceLight->bri_absolute = ($lightstate['bri'] === $hgGloDeviceLight->bri_absolute ? NULL : $lightstate['bri']);
                        break;
                    case HgGloDeviceGroup::RECOLOR_LIGHTSTATE:
                        $hgGloDeviceLight->ct = $lightstate['ct'] == $hgGloDeviceLight->ct ? NULL : $lightstate['ct'];
                        $hgGloDeviceLight->hue_x = $lightstate['xy'][0] == $hgGloDeviceLight->hue_x ? NULL : $lightstate['xy'][0];
                        $hgGloDeviceLight->hue_y = $lightstate['xy'][1] == $hgGloDeviceLight->hue_y ? NULL : $lightstate['xy'][1];
                        break;
                    case HgGloDeviceGroup::GLOZONE_LIGHTSTATE:
                    case HgGloDeviceGroup::RESTORE_LIGHTSTATE:
                    default:
                        $hgGloDeviceLight->bri_absolute = ($lightstate['bri'] == $hgGloDeviceLight->bri_absolute ? NULL : $lightstate['bri']);
                        $hgGloDeviceLight->ct = $lightstate['ct'] == $hgGloDeviceLight->ct ? NULL : $lightstate['ct'];
                        $hgGloDeviceLight->hue_x = $lightstate['xy'][0] == $hgGloDeviceLight->hue_x ? NULL : $lightstate['xy'][0];
                        $hgGloDeviceLight->hue_y = $lightstate['xy'][1] == $hgGloDeviceLight->hue_y ? NULL : $lightstate['xy'][1];
                        break;
                }
            }

            $hgGloDeviceLight->save();


        }

        return $sceneArrayToPost;
    }

}
