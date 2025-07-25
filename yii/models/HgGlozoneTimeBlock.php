<?php

namespace app\models;

use app\components\HelperComponent;
use NXP\MathExecutor;
use PHPUnit\TextUI\Help;
use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "hg_glozone_time_block".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $display_name
 * @property int|null $hg_glozone_id
 * @property int|null $default_hg_glo_id
 * @property int|null $hg_status_id
 * @property int|null $base_hg_glozone_time_block_id
 * @property int|null $smartOn_switch_behavior
 * @property int|null $smartOn_motion_behavior
 * @property int|null $smartTransition_behavior
 * @property int|null $smartTransition_duration_ms
 * @property string|null $time_start_default_midnightmins
 * @property string|null $time_end_default_midnightmins
 * @property string|null $time_start_sun_midnightmins
 * @property string|null $time_end_sun_midnightmins
 * @property string|null $time_start_mon_midnightmins
 * @property string|null $time_end_mon_midnightmins
 * @property string|null $time_start_tue_midnightmins
 * @property string|null $time_end_tue_midnightmins
 * @property string|null $time_start_wed_midnightmins
 * @property string|null $time_end_wed_midnightmins
 * @property string|null $time_start_thu_midnightmins
 * @property string|null $time_end_thu_midnightmins
 * @property string|null $time_start_fri_midnightmins
 * @property string|null $time_end_fri_midnightmins
 * @property string|null $time_start_sat_midnightmins
 * @property string|null $time_end_sat_midnightmins
 * @property string|null $timezone
 * @property string|null $metadata
 *
 * @property HgGlozoneTimeBlock $baseHgGlozoneTimeBlock
 * @property HgGlo $defaultHgGlo
 * @property HgGlozone $hgGlozone
 * @property HgGlozoneTimeBlock[] $hgGlozoneTimeBlocks
 * @property HgHubActionTrigger[] $hgHubActionTriggers
 * @property HgHubActionTrigger[] $hgHubActionTriggers0
 * @property HgStatus $hgStatus
 */
class HgGlozoneTimeBlock extends \yii\db\ActiveRecord
{
    const SMARTON_SWITCH_INACTIVE = 'inactive';
    const SMARTON_SWITCH_ACTIVE = 'active';

    const SMARTON_MOTION_ACTIVE = 'active';
    const SMARTON_MOTION_INACTIVE = 'inactive';

    const SMARTTRANSITION_CYCLE_ON = 'in_cycle_on';
    const SMARTTRANSITION_HARD_INVOKE = 'hard_invoke_on';
    const SMARTTRANSITION_INACTIVE = 'inactive';
    const SMARTTRANSITION_IF_ON = 'if_on';


    const SMARTON_SWITCH_BEHAVIORS = [
        self::SMARTON_SWITCH_ACTIVE=>'Active',
        self::SMARTON_SWITCH_INACTIVE=>'Inactive'
    ];

    const SMARTON_MOTION_BEHAVIORS = [
        self::SMARTON_MOTION_ACTIVE=>'Active',
        self::SMARTON_MOTION_INACTIVE=>'Inactive'
    ];

    const SMARTTRANSITION_BEHAVIORS = [
        self::SMARTTRANSITION_CYCLE_ON=>'In Cycle On',
        self::SMARTTRANSITION_HARD_INVOKE=>'Hard Invoke On',
        self::SMARTTRANSITION_INACTIVE=>'Inactive',
        self::SMARTTRANSITION_IF_ON => 'If On'
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'hg_glozone_time_block';
    }

    public function behaviors()
    {
        return [
            [
                'class'=>\app\behaviors\JsonDataBehavior::class,
                'attribute'=>'metadata'
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['hg_glozone_id', 'default_hg_glo_id', 'hg_status_id', 'base_hg_glozone_time_block_id','smartTransition_duration_ms'], 'integer'],
            [['metadata'], 'safe'],
            ['time_start_default_midnightmins','getCalcStartMidnightmins'],
            [['hg_status_id'],'default','value'=>HgStatus::HG_TIMEBLOCK_ACTIVE],
            [['timezone'],'default','value'=>'America/New_York'],
            [['smartOn_motion_behavior','smartOn_switch_behavior','smartTransition_behavior','name', 'display_name', 'time_start_default_midnightmins', 'time_end_default_midnightmins', 'time_start_sun_midnightmins', 'time_end_sun_midnightmins', 'time_start_mon_midnightmins', 'time_end_mon_midnightmins', 'time_start_tue_midnightmins', 'time_end_tue_midnightmins', 'time_start_wed_midnightmins', 'time_end_wed_midnightmins', 'time_start_thu_midnightmins', 'time_end_thu_midnightmins', 'time_start_fri_midnightmins', 'time_end_fri_midnightmins', 'time_start_sat_midnightmins', 'time_end_sat_midnightmins', 'timezone'], 'string', 'max' => 255],
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
            'display_name' => 'Time Block Name',
            'hg_glozone_id' => 'Hg Glozone ID',
            'default_hg_glo_id' => 'Default Hg Glo ID',
            'hg_status_id' => 'Hg Status ID',
            'smartOn_switch_behavior'=>'Smart On Switch',
            'smartOn_motion_behavior'=>'Smart On Motion',
            'smartTransition_behavior' => 'Smart Transition',
            'smartTransition_duration_ms'=>'Transition Duration',
            'base_hg_glozone_time_block_id' => 'Base Hg Glozone Time Block ID',
            'time_start_default_midnightmins' => 'Time Start Default Midnightmins',
            'time_start_sun_midnightmins' => 'Time Start Sun Midnightmins',
            'time_end_sun_midnightmins' => 'Time End Sun Midnightmins',
            'time_start_mon_midnightmins' => 'Time Start Mon Midnightmins',
            'time_end_mon_midnightmins' => 'Time End Mon Midnightmins',
            'time_start_tue_midnightmins' => 'Time Start Tue Midnightmins',
            'time_end_tue_midnightmins' => 'Time End Tue Midnightmins',
            'time_start_wed_midnightmins' => 'Time Start Wed Midnightmins',
            'time_end_wed_midnightmins' => 'Time End Wed Midnightmins',
            'time_start_thu_midnightmins' => 'Time Start Thu Midnightmins',
            'time_end_thu_midnightmins' => 'Time End Thu Midnightmins',
            'time_start_fri_midnightmins' => 'Time Start Fri Midnightmins',
            'time_end_fri_midnightmins' => 'Time End Fri Midnightmins',
            'time_start_sat_midnightmins' => 'Time Start Sat Midnightmins',
            'time_end_sat_midnightmins' => 'Time End Sat Midnightmins',
            'timezone' => 'Timezone',
            'metadata' => 'Metadata',
        ];
    }

    /**
     * Gets query for [[BaseHgGlozoneTimeBlock]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getBaseHgGlozoneTimeBlock()
    {
        return $this->hasOne(HgGlozoneTimeBlock::class, ['id' => 'base_hg_glozone_time_block_id']);
    }

    /**
     * Gets query for [[DefaultHgGlo]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDefaultHgGlo()
    {
        return $this->hasOne(HgGlo::class, ['id' => 'default_hg_glo_id']);
    }

    /**
     * Gets query for [[HgGlozone]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozone()
    {
        return $this->hasOne(HgGlozone::class, ['id' => 'hg_glozone_id']);
    }

    /**
     * Gets query for [[HgGlozoneTimeBlocks]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneTimeBlocks()
    {
        return $this->hasMany(HgGlozoneTimeBlock::class, ['base_hg_glozone_time_block_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActionTriggers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionTriggers()
    {
        return $this->hasMany(HgHubActionTrigger::class, ['hg_glozone_end_time_block_id' => 'id']);
    }

    /**
     * Gets query for [[HgGlozoneSmartTransition]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgGlozoneSmartTransitions()
    {
        return $this->hasMany(HgGlozoneSmartTransition::class, ['hg_glozone_time_block_id' => 'id']);
    }

    /**
     * Gets query for [[HgHubActionTriggers0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgHubActionTriggers0()
    {
        return $this->hasMany(HgHubActionTrigger::class, ['hg_glozone_start_time_block_id' => 'id']);
    }

    /**
     * Gets query for [[HgStatus]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getHgStatus()
    {
        return $this->hasOne(HgStatus::class, ['id' => 'hg_status_id']);
    }

    public static function getDefaultTimeBlocks()
    {
        return static::find()->where(['hg_glozone_id'=>HgGlozone::HG_DEFAULT_GLOZONE,'hg_status_id'=>HgStatus::HG_TIMEBLOCK_TEMPLATE])->all();
    }

    /**
     * @return HgGlozoneTimeBlock|null
     */
    public function getPreviousSequentialTimeBlock($where=[])
    {
        $q = static::find()->where([
            'hg_glozone_id'=>$this->hg_glozone_id
        ]);

        if ($where) {
            $q->andWhere($where);
        }

        $arr = [];
        foreach ($q->all() as $hgGlozoneTimeBlock) {
            $arr[$hgGlozoneTimeBlock->id] = $hgGlozoneTimeBlock->calcStartMidnightmins;
        }
        asort($arr);

        if (array_key_first($arr) == $this->id) {
            return HgGlozoneTimeBlock::findOne(array_key_last($arr));
        }


        while(key($arr) !== $this->id) next($arr);
        prev($arr);

        return HgGlozoneTimeBlock::findOne(key($arr));
    }

    /**
     * @return HgGlozoneTimeBlock|null
     */
    public function getNextSequentialTimeBlock($where=[])
    {
        $q = HgGlozoneTimeBlock::find()->where([
            'hg_glozone_id'=>$this->hg_glozone_id
        ]);

        if ($where) {
            $q->andWhere($where);
        }

        $arr = [];
        foreach ($q->all() as $hgGlozoneTimeBlock) {
            $arr[$hgGlozoneTimeBlock->id] = $hgGlozoneTimeBlock->calcStartMidnightmins;
        }
        asort($arr);

        if (array_key_last($arr) == $this->id) {
            return HgGlozoneTimeBlock::findOne(array_key_first($arr));
        }

        while(key($arr) !== $this->id) next($arr);
        next($arr);

        return HgGlozoneTimeBlock::findOne(key($arr));

    }

    public function getHasActiveSmartTransition()
    {
        return !(($this->smartTransition_behavior == NULL) || ($this->smartTransition_behavior == self::SMARTTRANSITION_INACTIVE));
    }

    /**
     * Evaluates the logic in the time blocks that specifies wake / end times and things
     * @return float|int
     * @throws \NXP\Exception\IncorrectBracketsException
     * @throws \NXP\Exception\IncorrectExpressionException
     * @throws \NXP\Exception\UnknownOperatorException
     * @throws \NXP\Exception\UnknownVariableException
     */
    public function getCalcStartMidnightmins()
    {
        if (is_int($this->time_start_default_midnightmins))
            return $this->time_start_default_midnightmins;

        date_default_timezone_set($this->timezone);

        $glozone = HgGlozone::getDb()->cache(function ($db) {
            return HgGlozone::find()->joinWith('hgHome')->where(['hg_glozone.id'=>$this->hg_glozone_id])->one();
        });

        //use weekday times for now
        $wake_time = $glozone->wake_time_weekday_midnightmins;
        $bed_time = $glozone->bed_time_weekday_midnightmins;
        $sunInfo = date_sun_info(time(),$glozone->hgHome->lat, $glozone->hgHome->lng);
        $sunrise = ceil($sunInfo['sunrise'] - strtotime('today midnight')) / 60;
        $sunset = ceil($sunInfo['sunset'] - strtotime('today midnight')) / 60;

        $trans = [
            '((wake_time))'=>$wake_time,
            '((bed_time))'=>$bed_time,
            '((sunset))'=>$sunset,
            '((sunrise))'=>$sunrise,
        ];

        try {
            $math = new MathExecutor();
            $translate = strtr($this->time_start_default_midnightmins,$trans);
            $wake_expression_evaluated = ceil($math->execute($translate));
        } catch (\Throwable $t) {
            Yii::error('Processing Formula:'.$translate,__METHOD__);
            $this->addError('time_start_default_midnightmins','Processing Formula:'.$translate);
            \Sentry\captureException($t);
            return null;
        }


        return ( ($wake_expression_evaluated - 1440) > 0 ? $wake_expression_evaluated-1440 : $wake_expression_evaluated);
    }

    public function getTimeStartDefaultFormatted()
    {
        return date('h:i:s A T',HelperComponent::convertMidnightMinutesToEpochTime($this->calcStartMidnightmins));
    }

    public function getTimeStartDefaultHueFormatted()
    {
        return HelperComponent::convertMidnightMinutesToHueTime(
            $this->calcStartMidnightmins,
            $this->timezone);
    }

    public static function getAllActiveClientTimeBlocksQuery()
    {
        return static::find()
            ->innerJoin('hg_glozone','hg_glozone_time_block.hg_glozone_id = hg_glozone.id')
            ->innerJoin('hg_home','hg_home.id = hg_glozone.hg_home_id')
            ->where(['hg_glozone_time_block.hg_status_id'=>HgStatus::HG_TIMEBLOCK_ACTIVE,'hg_home.hg_status_id'=>HgStatus::HG_HOME_ACTIVE]);
    }

    public function getIsCurrentlyActiveTimeBlockByTime($time=null)
    {
        if (!$time)
            $time = time();

        $now = HelperComponent::convertEpochTimeToMidnightMinutes($time);
        $thisBlockStart = $this->calcStartMidnightmins;
        $nextBlockStart = $this->nextSequentialTimeBlock->calcStartMidnightmins;
        if ($now >= $thisBlockStart) { // next block is in sequence
            if ( ($now < $nextBlockStart) || ($thisBlockStart > $nextBlockStart) )
                return true;
        }

        return false;

        /* Something is wrong with this
        else if ( $now < $thisBlockStart ) { //this is the last timeblock
            if ( ($nextBlockStart < $thisBlockStart) && ($now < $nextBlockStart) )
                return true;
        }*/
    }

    /**
     * @return bool
     */
    public function getHasTimeVariables()
    {
        if (stripos($this->time_start_default_midnightmins,'sunset') !== false)
            return true;

        if (stripos($this->time_start_default_midnightmins,'sunrise') !== false)
            return true;

        if (stripos($this->time_start_default_midnightmins,'wake_time') !== false)
            return true;

        if (stripos($this->time_start_default_midnightmins,'bed_time') !== false)
            return true;

        return false;
    }

    /**
     * @param HgGlozone $hgGlozone
     */
    public static function syncTimeBlocks(HgGlozone $hgGlozone)
    {
        foreach ($hgGlozone->hgGlozoneTimeBlocks as $hgGlozoneTimeBlock) {
            HgGlozoneSmartTransition::createSmartTransitions($hgGlozoneTimeBlock);
        }
    }

    public function afterSave($insert, $changedAttributes) {
        parent::afterSave($insert, $changedAttributes);

        if ($insert) {
            //this is clear to run now, because it won't do anything to switches that aren't properly tied to rooms
            $this->hgGlozone->processTimeBlockModifications();
        }

        if ($insert || (array_key_exists('smartTransition_behavior',$changedAttributes))) {
            switch ($this->smartTransition_behavior) {
                case HgGlozoneTimeBlock::SMARTTRANSITION_CYCLE_ON:
                case HgGlozoneTimeBlock::SMARTTRANSITION_HARD_INVOKE:
                case HgGlozoneTimeBlock::SMARTTRANSITION_IF_ON:
                    foreach ($this->hgGlozoneSmartTransitions as $hgGlozoneSmartTransition) {
                        $hgGlozoneSmartTransition->delete();
                    }
                    HgGlozoneSmartTransition::createSmartTransitions($this);
                    break;
                default:
                    foreach ($this->hgGlozoneSmartTransitions as $hgGlozoneSmartTransition) {
                        $hgGlozoneSmartTransition->delete();
                    }
            }
        }

        //If smartOn trigger is changed, process. not on insert, cause that is usually batch process
        if (!$insert) {
            if (array_key_exists('smartOn_switch_behavior',$changedAttributes) || array_key_exists('smartOn_motion_behavior',$changedAttributes)) {
                $this->hgGlozone->processTimeBlockModifications();
            } else if ( ($this->smartOn_switch_behavior == 'active' || $this->smartOn_motion_behavior == 'active') && //if time is changed, and smartOn is active, process
                ( array_key_exists('time_start_default_midnightmins',$changedAttributes)
                    || array_key_exists('time_end_default_midnightmins',$changedAttributes)
                    || array_key_exists('default_hg_glo_id',$changedAttributes)
                )
            ) {

                $this->hgGlozone->processTimeBlockModifications();
            }
        }

    }

    public function beforeDelete()
    {
        if (!parent::beforeDelete()) {
            return false;
        }

        // if home is not active, bail
        if (!$this->hgGlozone->hgHome->isActive) {
            return true;
        }

        // we need to do this processTimeBlockModifications twice
        // first time is to delete the rules in the hub INCLUDING THIS TIME BLOCK (so we must do it before we delete this timeblock)
        // then AFTER DELETE we must recalculate and write the rules without this time block present
        if ($this->smartOn_switch_behavior == 'active' || $this->smartOn_motion_behavior == 'active') {
            HgGlozone::findOne($this->hg_glozone_id)->processTimeBlockModifications($writeNewRules=false);
        }

        return true;
    }

    public function afterDelete()
    {
        parent::afterDelete();
        if (!$this->hgGlozone->hgHome->isActive) {
            return true;
        }

        if ($this->smartOn_switch_behavior == 'active' || $this->smartOn_motion_behavior == 'active') {
            HgGlozone::findOne($this->hg_glozone_id)->processTimeBlockModifications();
        }


    }
}
