<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_glozone_smart_transition_execute".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $hg_glozone_smart_transition_id
 * @property int|null $time_block_today_time
 * @property int|null $attempt
 * @property int|null $hg_status_id
 * @property string|null $metadata
 *
 * @property HgGlozoneSmartTransition $hgGlozoneSmartTransition
 */
class HgGlozoneSmartTransitionExecute extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_glozone_smart_transition_execute';
    }

    public function behaviors()
    {
        return [
            'timestamp' => \yii\behaviors\TimestampBehavior::className(),
            [
                'class'=>\app\behaviors\JsonDataBehavior::class
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hg_glozone_smart_transition_id', 'time_block_today_time', 'hg_status_id','attempt'], 'integer'],
            [['metadata'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'created_at' => 'Executed at Time',
            'updated_at' => 'Updated At',
            'hg_glozone_smart_transition_id' => 'Hg Glozone Smart Transition ID',
            'time_block_today_time' => 'Expected Execute Time',
            'hg_status_id' => 'Hg Status ID',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgGlozoneSmartTransition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneSmartTransition()
    {
        return $this->hasOne(HgGlozoneSmartTransition::class, ['id' => 'hg_glozone_smart_transition_id']);
    }

    /**
     * Gets query for [[HgGlozoneSmartTransition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgStatus()
    {
        return $this->hasOne(HgStatus::class, ['id' => 'hg_status_id']);
    }

    public function getIsMostRecent()
    {
        $mostRecentRecordId = HgGlozoneSmartTransitionExecute::find()
            ->where(['hg_glozone_smart_transition_id'=>$this->hg_glozone_smart_transition_id])
            ->orderBy('id DESC')
            ->one()
            ->id;

        if ($this->id == $mostRecentRecordId)
            return true;
        else
            return false;
    }
}
