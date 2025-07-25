<?php
namespace app\formatters;

use app\components\HelperComponent;
use app\models\HgDeviceSensor;

class HgFormatter extends \yii\i18n\Formatter
{
    public function asJsonPrettyPrint($value) {
        if (is_array($value)) {
            return '<pre>'.json_encode($value,JSON_PRETTY_PRINT).'</pre>';
        } else {
            return '<pre>'.json_encode(json_decode($value,TRUE),JSON_PRETTY_PRINT).'</pre>';
        }

    }

    public function asHgGlozoneTimeBlockSmartBehaviorIcon($value)
    {
        switch ($value) {
            case 'inactive':
                return '<i class="fas fa-times"></i>';
                break;
            case 'active':
                return '<i class="fas fa-check"></i>';
                break;
            case 'in_cycle_on':
                return '<i class="fas fa-play"></i> In-Cycle';
                break;
            case 'hard_invoke_on':
                return '<i class="fas fa-fast-forward"></i> Hard Invoke';
                break;
            case 'if_on':
                return '<i class="fas fa-step-forward"></i> If Lights On';
                break;
            default:
                return $value;
                break;

        }
    }

    /**
     * $value in milliseconds
     * @param $value
     */
    public function asMsToS($value)
    {
        return ($value/1000).' seconds';
    }

    public function asSwitchButtonNames($value)
    {
        $map = HgDeviceSensor::HUE_4BUTTON_SWITCH_IDS;
        $str = '';
        foreach ($value as $id) {
            $str .= '<span class="badge badge-primary">'.$map[$id].'</span>';
            $str .= '<br/>';
        }

        return $str;
    }

    public function asCheckOrX($value)
    {
        if ($value)
            return '<i class="fa fa-check"></i>';
        else
            return '<i class="fa fa-times"></i>';
    }
}

?>