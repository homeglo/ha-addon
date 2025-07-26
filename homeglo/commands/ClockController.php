<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\components\AirtableComponent;
use app\components\AmplitudeComponent;
use app\components\HelperComponent;
use app\components\HgEngineComponent;
use app\components\HueComponent;
use app\models\HgGlozoneSmartTransition;
use app\models\HgGlozoneSmartTransitionExecute;
use app\models\HgGlozoneTimeBlock;
use app\models\HgHub;
use app\models\HgStatus;
use NXP\MathExecutor;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ClockController extends Controller
{
    const SMART_TRANSITION_RETRY_ATTEMPTS = 5;

    public function actionIndex($sleep=null)
    {
        while (true){
            try {
                $start = microtime(TRUE);
                $this->actionProcess();
                $end = microtime(TRUE);
                $duration = $end-$start;

                AmplitudeComponent::syncLogBasicEvent('clock_process_time',['duration'=>$duration]);

            } catch (\Throwable $t) {
                \Sentry\captureException($t);
                \Yii::error($t->getMessage(),__METHOD__);
                echo $t->getMessage();
                echo "\n";
            }
            sleep($sleep ?? $_ENV['HG_CLOCK_INTERVAL_SECONDS']);
        }
    }

    public function actionProcess()
    {
        //Get active time blocks
        $timeBlocks = HgGlozoneTimeBlock::getAllActiveClientTimeBlocksQuery()->all();

        //Since timeblocks require realtime calculation we must do this on the fly
        foreach ($timeBlocks as $hgGlozoneTimeBlock) {

            date_default_timezone_set($hgGlozoneTimeBlock->timezone);

            $todayBlockTime = HelperComponent::convertMidnightMinutesToEpochTime($hgGlozoneTimeBlock->getCalcStartMidnightmins());
            $nextBlockStartTime = HelperComponent::convertMidnightMinutesToEpochTime($hgGlozoneTimeBlock->nextSequentialTimeBlock->getCalcStartMidnightmins());

            if ($hgGlozoneTimeBlock->getIsCurrentlyActiveTimeBlockByTime()) { //we are in this timeblock
                //get all transitions related to this timeblock
                foreach (HgGlozoneSmartTransition::getSchedulerQuery()->andWhere(['hg_glozone_time_block_id'=>$hgGlozoneTimeBlock->id])->all() as $hgGlozoneSmartTransitionRoom) {
                    //check if already executed
                    $hgGlozoneSmartTransitionExecute = HgGlozoneSmartTransitionExecute::find()
                        ->where([
                            'time_block_today_time'=>$todayBlockTime,
                            'hg_glozone_smart_transition_id'=>$hgGlozoneSmartTransitionRoom->id
                        ])
                        ->one();


                        //it's failed, give up. if succeed - move on. if the status is retry, keep going
                        switch ($hgGlozoneSmartTransitionExecute->hg_status_id) {
                            case HgStatus::HG_SMARTTRANSITION_EXECUTE_FAIL:
                            case HgStatus::HG_SMARTTRANSITION_EXECUTE_SUCCESS:
                                continue 2;
                                break;
                            default:
                        }

                        //Process
                        \Yii::info('[CLOCK] [EXECUTING] ['.$hgGlozoneTimeBlock->hgGlozone->hgHome->display_name.']['.date('h:i:s a',$todayBlockTime).'-'.date('h:i:s a',$nextBlockStartTime).']['.$hgGlozoneTimeBlock->display_name.']['.$hgGlozoneTimeBlock->defaultHgGlo->display_name.']',__METHOD__);

                        //Create new execution record
                        $hgGlozoneSmartTransitionExecute ??= new HgGlozoneSmartTransitionExecute();
                        $hgGlozoneSmartTransitionExecute->hg_glozone_smart_transition_id = $hgGlozoneSmartTransitionRoom->id;
                        $hgGlozoneSmartTransitionExecute->time_block_today_time = $todayBlockTime;

                        try {
                            $hgGlozoneSmartTransitionRoom->last_trigger_at = time();

                            $engine = new HgEngineComponent($hgGlozoneSmartTransitionRoom->hgDeviceGroup->hg_hub_id);
                            $resulting_status = $engine->processSmartTransition($hgGlozoneSmartTransitionRoom, $hgGlozoneSmartTransitionExecute);

                            $hgGlozoneSmartTransitionRoom->last_trigger_status = $resulting_status;
                            $hgGlozoneSmartTransitionRoom->metadata = [];
                            if (!$hgGlozoneSmartTransitionRoom->save()) {
                                \Yii::error(HelperComponent::getFirstErrorFromFailedValidation($hgGlozoneSmartTransitionRoom),__METHOD__);
                            }

                            $hgGlozoneSmartTransitionExecute->appendJsonData(['trigger_status'=>$resulting_status,'trigger_behavior_name'=>$hgGlozoneSmartTransitionRoom->behavior_name]);
                            $hgGlozoneSmartTransitionExecute->hg_status_id = HgStatus::HG_SMARTTRANSITION_EXECUTE_SUCCESS;


                        } catch (\Throwable $t) {
                            \Sentry\captureException($t);
                            \Yii::error($t->getMessage(),__METHOD__);

                            $max_attempts = $_ENV['SMART_TRANSITION_RETRY_ATTEMPTS'] ?? ClockController::SMART_TRANSITION_RETRY_ATTEMPTS;
                            $current_attempt = $hgGlozoneSmartTransitionExecute->attempt ?? 0;
                            $current_attempt += 1;

                            $hgGlozoneSmartTransitionExecute->appendJsonData(['error'=>$t->getMessage()]);
                            $hgGlozoneSmartTransitionExecute->attempt = $current_attempt;

                            //if special exception, scene does not exists...fail
                            if (get_class($t) == 'app\exceptions\HueSceneDoesNotExistInRoomException') {
                                $hgGlozoneSmartTransitionExecute->hg_status_id = HgStatus::HG_SMARTTRANSITION_EXECUTE_FAIL;
                            } else { //otherwise we retry, assuming the hub internet went out
                                if ($current_attempt >= $max_attempts) {
                                    $hgGlozoneSmartTransitionExecute->hg_status_id = HgStatus::HG_SMARTTRANSITION_EXECUTE_FAIL;
                                } else {
                                    $hgGlozoneSmartTransitionExecute->hg_status_id = HgStatus::HG_SMARTTRANSITION_EXECUTE_RETRY;
                                }
                            }

                            $hgGlozoneSmartTransitionRoom->last_trigger_status = HgGlozoneSmartTransition::RESULT_STATUS_FAILURE;
                            $hgGlozoneSmartTransitionRoom->appendJsonData(['last_error'=>$t->getMessage()]);
                        }

                        AmplitudeComponent::asyncLogDeviceGroupEvent($hgGlozoneSmartTransitionRoom->hgDeviceGroup,
                        'HG_SMART_TRANSITION',
                        [
                            'execute_status'=>HgStatus::findOne($hgGlozoneSmartTransitionExecute->hg_status_id)->name,
                            'behavior_name'=>$hgGlozoneSmartTransitionRoom->behavior_name,
                            'behavior_result'=>$hgGlozoneSmartTransitionRoom->last_trigger_status,
                            'attempt_number'=>$hgGlozoneSmartTransitionExecute->attempt,
                            'target_glo_name'=>$hgGlozoneTimeBlock->defaultHgGlo->name,
                            'time_start'=>$hgGlozoneTimeBlock->time_start_default_midnightmins
                        ]);

                        $hgGlozoneSmartTransitionExecute->save();
                }
            }
        }



    }
}
