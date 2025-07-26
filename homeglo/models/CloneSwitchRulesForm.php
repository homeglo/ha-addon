<?php

namespace app\models;

use app\components\HueComponent;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class CloneSwitchRulesForm extends Model
{
    public $source_switch_id;
    public $destination_switch_id;
    public $hub_record;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['source_switch_id', 'destination_switch_id'], 'required'],
            [['source_switch_id', 'destination_switch_id'], 'integer'],
        ];
    }

    public function performClone()
    {
        //delete rules
        $hueApi = new HueComponent( $this->hub_record['access_token'], $this->hub_record['bearer_token']);
        $hueApi->deleteSwitchRules($this->destination_switch_id);

        //Perform clone operation
        $sourceRules = $hueApi->getSwitchRules($this->source_switch_id);
        foreach ($sourceRules as $rule) {
            foreach ($rule['conditions'] as &$c) {
                $c['address'] = str_replace('/'.$this->source_switch_id.'/','/'.$this->destination_switch_id.'/',$c['address']);
            }
            $hueApi->createRuleBasedOnRule($rule);
        }
    }
}
