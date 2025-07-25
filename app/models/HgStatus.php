<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_status".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $category_name
 * @property int|null $rank
 *
 * @property HgGlo[] $hgGlos
 * @property HgHubActionCondition[] $hgHubActionConditions
 * @property HgHub[] $hgHubs
 * @property HgRoom[] $hgRooms
 */
class HgStatus extends \yii\db\ActiveRecord
{
    const HG_GLO_ACTIVE = 100;
    const HG_GLO_INACTIVE = 110;

    const HG_HOME_ACTIVE = 200;
    const HG_HOME_INACTIVE = 210;


    const HG_ACTION_TEMPLATE_HOMEGLO_DEFAULT = 300;
    const HG_ACTION_TEMPLATE_CLIENT = 310;
    const HG_ACTION_TEMPLATE_INACTIVE = 305;

    const HG_SMARTTRANSITION_ACTIVE = 500;
    const HG_SMARTTRANSITION_INACTIVE = 510;

    const HG_SMARTTRANSITION_EXECUTE_SUCCESS = 550;
    const HG_SMARTTRANSITION_EXECUTE_FAIL = 560;
    const HG_SMARTTRANSITION_EXECUTE_RETRY = 555;

    const HG_TIMEBLOCK_ACTIVE = 420;
    const HG_TIMEBLOCK_INACTIVE = 430;
    const HG_TIMEBLOCK_TEMPLATE = 400;

    const CATEGORY_SMART_TRANSITION = 'smart_transition';
    const CATEGORY_GLOZONE_STARTUPMODE = 'glozone_startup_mode';

    const HG_DEFAULT_SENSOR_VARIABLE = 600;
    const HG_USER_SENSOR_VARIABLE = 605;

    const HG_GLOZONE_STARTUPMODE_HUE_WARM_WHITE = 700;
    const HG_GLOZONE_STARTUPMODE_LAST_STATE = 705;

    const HG_USER_HOME_ACTIVE = 800;
    const HG_USER_HOME_INACTIVE = 810;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_status';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rank'], 'integer'],
            [['name', 'display_name', 'category_name'], 'string', 'max' => 255],
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
            'display_name' => 'Status Name',
            'category_name' => 'Category Name',
            'rank' => 'Rank',
        ];
    }

    /**
     * Gets query for [[HgGlos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlos()
    {
        return $this->hasMany(HgGlo::className(), ['hg_status_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActionConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionConditions()
    {
        return $this->hasMany(HgHubActionCondition::className(), ['hg_status_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActions()
    {
        return $this->hasMany(HgHubAction::className(), ['hg_status_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubs()
    {
        return $this->hasMany(HgHub::className(), ['hg_status_id' => 'id']);
    }

    /**
     * Gets query for [[HgRooms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgRooms()
    {
        return $this->hasMany(HgRoom::className(), ['hg_status_id' => 'id']);
    }
}
