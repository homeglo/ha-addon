<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_type".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property string|null $category_name
 * @property int|null $rank
 *
 * @property HgHubActionCondition[] $hgHubActionConditions
 * @property HgHubActionItem[] $hgHubActionItems
 * @property HgHubAction[] $hgHubActions
 * @property HgHub[] $hgHubs
 */
class HgType extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_type';
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
            'display_name' => 'Display Name',
            'category_name' => 'Category Name',
            'rank' => 'Rank',
        ];
    }

    /**
     * Gets query for [[HgHubActionConditions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionConditions()
    {
        return $this->hasMany(HgHubActionCondition::className(), ['hg_type_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActionItems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionItems()
    {
        return $this->hasMany(HgHubActionItem::className(), ['hg_type_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActions()
    {
        return $this->hasMany(HgHubAction::className(), ['hg_type_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubs()
    {
        return $this->hasMany(HgHub::className(), ['hg_type_id' => 'id']);
    }
}
