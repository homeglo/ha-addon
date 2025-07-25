<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_room_type".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property int|null $rank
 *
 * @property HgRoom[] $hgRooms
 */
class HgRoomType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_room_type';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['rank'], 'integer'],
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
            'name' => 'Name',
            'display_name' => 'Display Name',
            'rank' => 'Rank',
        ];
    }

    /**
     * Gets query for [[HgRooms]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgRooms()
    {
        return $this->hasMany(HgRoom::className(), ['hg_room_type_id' => 'id']);
    }
}
