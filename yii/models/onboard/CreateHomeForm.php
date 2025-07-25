<?php

namespace app\models\onboard;

use Yii;
use yii\base\Model;

/**
 * ContactForm is the model behind the contact form.
 */
class CreateHomeForm extends Model
{
    public $home_name;
    public $home_address;
    public $bed_time;
    public $wake_time;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // name, email, subject and body are required
            [['home_name', 'home_address', 'bed_time', 'wake_time'], 'required'],

        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'home_address' => 'Postal Code',
        ];
    }
}
