<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "hg_version".
 *
 * @property int $id
 * @property string|null $version
 * @property string|null $display_name
 *
 * @property HgHome[] $hgHomes
 */
class HgVersion extends \yii\db\ActiveRecord
{
    const HG_VERSION_MANUAL_ENTRY = 1;
    const HG_VERSION_2_0_ENGINE = 2;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_version';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'display_name'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'display_name' => 'Display Name',
        ];
    }

    /**
     * Gets query for [[HgHomes]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHomes()
    {
        return $this->hasMany(HgHome::className(), ['hg_version_id' => 'id']);
    }
}
