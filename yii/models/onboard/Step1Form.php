<?php

namespace app\models\onboard;

use app\models\HgUser;
use app\models\RegisterForm;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * ContactForm is the model behind the contact form.
 */
class Step1Form extends RegisterForm
{
    public $home_id;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return ArrayHelper::merge(parent::rules(),[
            [['home_id'], 'required'],
        ]);
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return ArrayHelper::merge(parent::attributeLabels(),[
            'home_id' => 'HomeGlo Home ID',
        ]);
    }
}
