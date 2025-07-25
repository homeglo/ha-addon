<?php

namespace app\models;

use app\components\HgEngineComponent;
use app\components\HueComponent;
use app\components\HueSyncComponent;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property-read User|null $user
 *
 */
class HomeGloButtonForm extends Model
{
    /**
     * @var int the hub we are homeglo-ing
     */
    public int $hg_hub_id;

    /**
     * @var int glozone id
     */
    public ?int $hg_glozone_id = null;

    /**
     * @var bool clear the destination hub data
     * Leave only lights, groups, physical sensors
     */
    public bool $clear_hub_data = false;

    /**
     * @var bool pull in glo overrides
     */
    public bool $pull_in_glo_overrides = false;

    /**
     * @var bool create hue variable sensors
     */
    public bool $create_hue_variable_sensors = false;


    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['hg_hub_id'], 'required'],
            [['hg_hub_id','hg_glozone_id'], 'integer'],
            [['clear_hub_data', 'pull_in_glo_overrides','create_hue_variable_sensors'], 'boolean'],
        ];
    }



}
