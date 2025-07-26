<?php
namespace app\components;

use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;


class HelperComponent extends Component
{
    public static function getFirstErrorFromFailedValidation($model) {
        $errors = $model->getErrors();
        foreach ($errors as $attr => $errorArray) {
            $errorStr = $errorArray[0];
        }

        return @$errorStr ?? 'No Invalid Attribute';
    }

    public static function convertMidnightMinutesToHueTime($midnightMinutes,$timezone='America/New_York')
    {
        date_default_timezone_set($timezone);
        $time = strtotime('today midnight');
        $time = $time + ((int) $midnightMinutes)*60;
        $t = date('H:i:s',$time);
        return 'T'.$t;
    }

    public static function convertMidnightMinutesToEpochTime($midnightMinutes,$timezone='America/New_York')
    {
        date_default_timezone_set($timezone);
        $time = strtotime('today midnight');
        $time = $time + ((int) $midnightMinutes)*60;
        return $time;
    }

    public static function convertMidnightMinutesToHhSs($midnightMinutes,$timezone='America/New_York')
    {
        date_default_timezone_set($timezone);
        $time = strtotime('today midnight');
        $time = $time + ((int) $midnightMinutes)*60;
        $t = date('H:i',$time);
        return $t;
    }

    public static function convertHhSsToMidnightMinutes($HhSs)
    {
        $h = (int) explode(":",$HhSs)[0];
        $s = (int) explode(":",$HhSs)[1];
        return ($h*60 + $s);
    }

    public static function convertEpochTimeToMidnightMinutes($time,$timezone='America/New_York')
    {
        date_default_timezone_set($timezone);
        $time = strtotime('today midnight');
        return floor((time() - $time) / 60);
    }

    /**
     * Compare xy values with a tolerance
     * @param $xy1
     * @param $xy2
     */
    public static function compareXyColors($xy1,$xy2)
    {
        $tolerance = .0200;

        if (abs($xy1[0]-$xy2[0]) > $tolerance)
            return false;

        if (abs($xy1[1]-$xy2[1]) > $tolerance)
            return false;

        return true;

    }

    /**
     * Compare ct values with a tolerance
     * @param $ct1
     * @param $ct2
     */
    public static function compareCtColors($ct1,$ct2)
    {
        $tolerance = 10;

        if (abs($ct1-$ct2) > $tolerance)
            return false;

        if (abs($ct1-$ct2) > $tolerance)
            return false;

        return true;

    }

    /**
     * this function is ghetto...hue takes in weird lat/lng params.
     * this is not entirely accurate DMS conversion, should be close enough tho
     * @param $latitude
     * @param $longitude
     * @return string[]
     * [0] = lat
     * [1] = lng
     */
    public static function DECtoDMS($latitude, $longitude)
    {
        $latitudeDirection = $latitude < 0 ? 'S': 'N';
        $longitudeDirection = $longitude < 0 ? 'W': 'E';

        $latitudeInDegrees = floor(abs($latitude));
        $longitudeInDegrees = floor(abs($longitude));

        $latitudeDecimal = abs($latitude)-$latitudeInDegrees;
        $longitudeDecimal = abs($longitude)-$longitudeInDegrees;

        $_precision = 3;
        $latitudeMinutes = (int) str_replace(".","",round($latitudeDecimal*60,$_precision));
        $longitudeMinutes = (int) str_replace(".","",round($longitudeDecimal*60,$_precision));

        return explode(",",sprintf('%s.%s%s,%s.%s%s',
            $latitudeInDegrees,
            $latitudeMinutes,
            $latitudeDirection,
            $longitudeInDegrees,
            $longitudeMinutes,
            $longitudeDirection
        ));

    }

    public static function getInsertSqlFromModel($model)
    {
        $values = $model->attributes;
        $db = $model::getDb();
        $command = $db->createCommand()->insert($model->tableName(), $values);

        $cmd = str_replace("{",'\{',$command->rawSql);
        $cmd = str_replace("}",'\}',$cmd);
        return $cmd;
    }

}
