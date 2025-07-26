<?php

namespace app\models;

use app\components\HelperComponent;
use Yii;
use yii\web\User;

/**
 * This is the model class for table "hg_user_home".
 *
 * @property int $id
 * @property int|null $created_at
 * @property int|null $updated_at
 * @property int|null $user_id
 * @property int|null $hg_home_id
 * @property int|null $hg_status_id
 * @property string|null $metadata
 *
 * @property HgHome $hgHome
 * @property User $user
 */
class HgUserHome extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_user_home';
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
            [['user_id', 'hg_home_id', 'hg_status_id'], 'integer'],
            [['metadata'], 'safe']
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
            'user_id' => 'User ID',
            'hg_home_id' => 'Hg Home ID',
            'hg_status_id' => 'Hg Status ID',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[HgHome]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHome()
    {
        return $this->hasOne(HgHome::class, ['id' => 'hg_home_id']);
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgUser()
    {
        return $this->hasOne(HgUser::class, ['id' => 'user_id']);
    }

    /**
     * @param $boxId
     * @param $userObject
     * @return HgUserHome | bool
     * @throws \Exception
     */
    public static function connectBoxToUser($boxId,$userObject)
    {
        $hgHome = HgHome::find()->where(['like','display_name',$boxId])->one();
        if ($hgHome) {
            $hgUserHome = new HgUserHome();
            $hgUserHome->hg_home_id = $hgHome->id;
            $hgUserHome->user_id = $userObject->id;
            $hgUserHome->hg_status_id = HgStatus::HG_USER_HOME_ACTIVE;
            if (!$hgUserHome->save()) {
                Yii::error(HelperComponent::getFirstErrorFromFailedValidation($hgUserHome),__METHOD__);
                return false;
            }

            $auth = Yii::$app->authManager;
            $role = $auth->getRole('cloud_user');
            $auth->assign($role,$userObject->id);

            return $hgUserHome;
        }

        Yii::error('Invalid Box ID: '.$boxId,__METHOD__);
        return false;
    }

    /**
     * @param HgHome $hgHome
     * @param HgUser $userObject
     * @return HgUserHome | bool
     * @throws \Exception
     */
    public static function connectHomeToUser($hgHome,$userObject)
    {
        $hgUserHome = new HgUserHome();
        $hgUserHome->hg_home_id = $hgHome->id;
        $hgUserHome->user_id = $userObject->id;
        $hgUserHome->hg_status_id = HgStatus::HG_USER_HOME_ACTIVE;
        if (!$hgUserHome->save()) {
            Yii::error(HelperComponent::getFirstErrorFromFailedValidation($hgUserHome),__METHOD__);
            return false;
        }

        $auth = Yii::$app->authManager;
        $role = $auth->getRole('cloud_user');
        if (!$userObject->hasRole('cloud_user'))
            $auth->assign($role,$userObject->id);

        return $hgUserHome;
    }
}
